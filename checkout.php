<?php
require_once __DIR__ . '/includes/auth.php';

require_login();

$conn = db_connect();

$bookingSession = $_SESSION['booking'] ?? null;

if (
    !$bookingSession ||
    empty($bookingSession['show_id']) ||
    empty($bookingSession['selected_seat_ids']) ||
    empty($bookingSession['selected_seats'])
) {
    set_flash_message('error', 'Không có dữ liệu đặt vé. Vui lòng chọn ghế trước.');
    redirect('movies.php');
}

$showId = (int) $bookingSession['show_id'];
$selectedSeatIds = array_map('intval', $bookingSession['selected_seat_ids']);
$selectedSeats = $bookingSession['selected_seats'];
$seatSubtotal = (float) ($bookingSession['seat_subtotal'] ?? 0);
$userId = (int) $_SESSION['user_id'];

$showSql = "
    SELECT
        s.show_id,
        s.start_at,
        s.end_at,
        s.base_price,
        s.screen_format,
        s.subtitle_type,
        m.movie_id,
        m.title AS movie_title,
        m.poster_url,
        r.room_name,
        l.name AS location_name,
        l.address
    FROM showtimes s
    INNER JOIN movies m ON s.movie_id = m.movie_id
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    WHERE s.show_id = ?
    LIMIT 1
";
$showStmt = $conn->prepare($showSql);
$showStmt->bind_param('i', $showId);
$showStmt->execute();
$showResult = $showStmt->get_result();
$showtime = $showResult->fetch_assoc();
$showStmt->close();

if (!$showtime) {
    unset($_SESSION['booking']);
    set_flash_message('error', 'Suất chiếu không tồn tại.');
    redirect('movies.php');
}

$comboSql = "
    SELECT combo_id, name, description, price
    FROM combo_products
    WHERE status = 'active'
    ORDER BY combo_id DESC
";
$comboResult = $conn->query($comboSql);

$combos = [];
if ($comboResult && $comboResult->num_rows > 0) {
    while ($row = $comboResult->fetch_assoc()) {
        $combos[(int) $row['combo_id']] = $row;
    }
}

$paymentMethods = [
    'cash' => 'Cash',
    'bank_transfer' => 'Bank Transfer',
    'e_wallet' => 'E-Wallet'
];

$error = '';
$selectedComboQty = [];
$comboSubtotal = 0;
$grandTotal = $seatSubtotal;
$paymentMethod = 'cash';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = post('payment_method', 'cash');
    $postedComboQty = $_POST['combo_qty'] ?? [];

    if (!array_key_exists($paymentMethod, $paymentMethods)) {
        $error = 'Phương thức thanh toán không hợp lệ.';
    } else {
        foreach ($postedComboQty as $comboId => $qty) {
            $comboId = (int) $comboId;
            $qty = max(0, (int) $qty);

            if ($qty > 0 && isset($combos[$comboId])) {
                $selectedComboQty[$comboId] = $qty;
                $comboSubtotal += ((float) $combos[$comboId]['price'] * $qty);
            }
        }

        $grandTotal = $seatSubtotal + $comboSubtotal;

        if ($grandTotal <= 0) {
            $error = 'Tổng tiền không hợp lệ.';
        } else {
            $seatIdsSql = implode(',', array_map('intval', $selectedSeatIds));

            try {
                $conn->begin_transaction();

                $verifySeatSql = "
                    SELECT
                        ss.show_seat_id,
                        ss.status,
                        ss.final_price,
                        se.seat_row,
                        se.seat_number
                    FROM show_seats ss
                    INNER JOIN seats se ON ss.seat_id = se.seat_id
                    WHERE ss.show_id = ?
                      AND ss.show_seat_id IN ($seatIdsSql)
                    FOR UPDATE
                ";
                $verifyStmt = $conn->prepare($verifySeatSql);
                $verifyStmt->bind_param('i', $showId);
                $verifyStmt->execute();
                $verifyResult = $verifyStmt->get_result();

                $verifiedSeats = [];
                while ($seat = $verifyResult->fetch_assoc()) {
                    $verifiedSeats[(int) $seat['show_seat_id']] = $seat;
                }
                $verifyStmt->close();

                if (count($verifiedSeats) !== count($selectedSeatIds)) {
                    throw new Exception('Một hoặc nhiều ghế không tồn tại.');
                }

                foreach ($selectedSeatIds as $seatId) {
                    if (!isset($verifiedSeats[$seatId])) {
                        throw new Exception('Ghế không hợp lệ.');
                    }

                    if ($verifiedSeats[$seatId]['status'] !== 'available') {
                        $seatLabel = $verifiedSeats[$seatId]['seat_row'] . $verifiedSeats[$seatId]['seat_number'];
                        throw new Exception('Ghế ' . $seatLabel . ' không còn trống.');
                    }
                }

                $seatTotalFromDb = 0;
                foreach ($verifiedSeats as $seat) {
                    $seatTotalFromDb += (float) $seat['final_price'];
                }

                $comboTotalFromDb = 0;
                foreach ($selectedComboQty as $comboId => $qty) {
                    $comboTotalFromDb += ((float) $combos[$comboId]['price'] * $qty);
                }

                $subtotal = $seatTotalFromDb + $comboTotalFromDb;
                $discountTotal = 0;
                $total = $subtotal - $discountTotal;

                $bookingCode = generate_booking_code();
                $bookingStatus = 'confirmed';

                $insertBookingSql = "
                    INSERT INTO bookings (
                        user_id, show_id, booking_code, status,
                        hold_expires_at, subtotal, discount_total, total
                    )
                    VALUES (?, ?, ?, ?, NULL, ?, ?, ?)
                ";
                $bookingStmt = $conn->prepare($insertBookingSql);
                $bookingStmt->bind_param(
                    'iissddd',
                    $userId,
                    $showId,
                    $bookingCode,
                    $bookingStatus,
                    $subtotal,
                    $discountTotal,
                    $total
                );

                if (!$bookingStmt->execute()) {
                    throw new Exception('Không thể tạo đơn đặt vé.');
                }

                $bookingId = (int) $bookingStmt->insert_id;
                $bookingStmt->close();

                $insertSeatSql = "
                    INSERT INTO booking_seats (booking_id, show_seat_id, unit_price)
                    VALUES (?, ?, ?)
                ";
                $insertSeatStmt = $conn->prepare($insertSeatSql);

                foreach ($verifiedSeats as $showSeatId => $seat) {
                    $unitPrice = (float) $seat['final_price'];
                    $insertSeatStmt->bind_param('iid', $bookingId, $showSeatId, $unitPrice);

                    if (!$insertSeatStmt->execute()) {
                        throw new Exception('Không thể lưu ghế vào đơn đặt vé.');
                    }
                }
                $insertSeatStmt->close();

                if (!empty($selectedComboQty)) {
                    $insertComboSql = "
                        INSERT INTO booking_combos (booking_id, combo_id, quantity, unit_price)
                        VALUES (?, ?, ?, ?)
                    ";
                    $insertComboStmt = $conn->prepare($insertComboSql);

                    foreach ($selectedComboQty as $comboId => $qty) {
                        $unitPrice = (float) $combos[$comboId]['price'];
                        $insertComboStmt->bind_param('iiid', $bookingId, $comboId, $qty, $unitPrice);

                        if (!$insertComboStmt->execute()) {
                            throw new Exception('Không thể lưu combo vào đơn đặt vé.');
                        }
                    }
                    $insertComboStmt->close();
                }

                $paymentStatus = 'paid';
                $transactionCode = 'TXN' . date('YmdHis') . rand(100, 999);
                $paidAt = date('Y-m-d H:i:s');

                $insertPaymentSql = "
                    INSERT INTO payments (booking_id, method, amount, status, paid_at, transaction_code)
                    VALUES (?, ?, ?, ?, ?, ?)
                ";
                $paymentStmt = $conn->prepare($insertPaymentSql);
                $paymentStmt->bind_param(
                    'isdsss',
                    $bookingId,
                    $paymentMethod,
                    $total,
                    $paymentStatus,
                    $paidAt,
                    $transactionCode
                );

                if (!$paymentStmt->execute()) {
                    throw new Exception('Không thể lưu thanh toán.');
                }
                $paymentStmt->close();

                $updateSeatSql = "
                    UPDATE show_seats
                    SET status = 'booked',
                        hold_until = NULL,
                        hold_token = NULL
                    WHERE show_seat_id IN ($seatIdsSql)
                ";
                if (!$conn->query($updateSeatSql)) {
                    throw new Exception('Không thể cập nhật trạng thái ghế.');
                }

                $conn->commit();

                unset($_SESSION['booking']);

                redirect('success.php?booking_id=' . $bookingId);
            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="checkout-page-wrap">
    <div class="checkout-page-inner">
        <div class="checkout-page-head">
            <div>
                <span class="movie-status-badge showing">CHECKOUT</span>
                <h1 class="checkout-page-title">Confirm Your Booking</h1>
                <p class="checkout-page-subtitle">
                    Review your movie, seats, combos, and complete payment.
                </p>
            </div>
        </div>

        <div class="checkout-layout-dark">
            <div class="checkout-left-dark">
                <div class="checkout-show-card-dark">
                    <div class="checkout-poster-dark">
                        <img
                            src="<?= e($showtime['poster_url'] ?: 'https://via.placeholder.com/400x600?text=Movie') ?>"
                            alt="<?= e($showtime['movie_title']) ?>"
                        >
                    </div>

                    <div class="checkout-show-info-dark">
                        <h2><?= e($showtime['movie_title']) ?></h2>

                        <div class="checkout-show-meta-dark">
                            <span><?= e($showtime['location_name']) ?></span>
                            <span><?= e($showtime['room_name']) ?></span>
                            <span><?= date('d/m/Y H:i', strtotime($showtime['start_at'])) ?></span>
                            <span><?= e($showtime['screen_format'] ?: '2D') ?></span>
                            <span><?= e($showtime['subtitle_type'] ?: 'No subtitle') ?></span>
                        </div>
                    </div>
                </div>

                <div class="checkout-block-dark">
                    <div class="checkout-block-head-dark">
                        <h3>Selected Seats</h3>
                    </div>

                    <div class="checkout-seat-list-dark">
                        <?php foreach ($selectedSeats as $seat): ?>
                            <div class="checkout-line-dark">
                                <div>
                                    <strong><?= e($seat['label']) ?></strong>
                                    <span><?= e($seat['category_name']) ?></span>
                                </div>
                                <strong><?= format_currency($seat['price']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="checkout-block-dark">
                    <div class="checkout-block-head-dark">
                        <h3>Combo Products</h3>
                    </div>

                    <?php if ($error !== ''): ?>
                        <div class="auth-error" style="margin-bottom:18px;">
                            <?= e($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="checkoutForm">
                        <?php if (!empty($combos)): ?>
                            <div class="checkout-combo-list-dark">
                                <?php foreach ($combos as $comboId => $combo): ?>
                                    <?php $qtyValue = (int) ($selectedComboQty[$comboId] ?? 0); ?>
                                    <div class="checkout-combo-item-dark">
                                        <div class="checkout-combo-info-dark">
                                            <h4><?= e($combo['name']) ?></h4>
                                            <p><?= e($combo['description'] ?? '') ?></p>
                                            <span><?= format_currency($combo['price']) ?></span>
                                        </div>

                                        <input
                                            type="number"
                                            name="combo_qty[<?= $comboId ?>]"
                                            min="0"
                                            value="<?= $qtyValue ?>"
                                            class="checkout-combo-qty-dark"
                                            data-price="<?= (float) $combo['price'] ?>"
                                        >
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">Hiện chưa có combo đang hoạt động.</div>
                        <?php endif; ?>

                        <div class="form-group" style="margin-top: 20px;">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="movies-status-select" required>
                                <?php foreach ($paymentMethods as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= $paymentMethod === $value ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <div class="checkout-right-dark">
                <div class="checkout-summary-card-dark">
                    <h3>Order Summary</h3>

                    <div class="checkout-summary-line-dark">
                        <span>Seats</span>
                        <strong id="seat-total-text"><?= format_currency($seatSubtotal) ?></strong>
                    </div>

                    <div class="checkout-summary-line-dark">
                        <span>Combos</span>
                        <strong id="combo-total-text"><?= format_currency($comboSubtotal) ?></strong>
                    </div>

                    <div class="checkout-summary-total-dark">
                        <span>Total</span>
                        <strong id="grand-total-text"><?= format_currency($grandTotal) ?></strong>
                    </div>

                    <div class="checkout-summary-actions-dark">
                        <a class="movie-detail-outline-btn" href="<?= BASE_URL ?>select_seats.php?show_id=<?= (int) $showId ?>">
                            Back
                        </a>

                        <button type="submit" form="checkoutForm" class="movie-book-btn-dark checkout-confirm-btn-dark">
                            Confirm Booking
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const seatSubtotal = <?= json_encode((float) $seatSubtotal) ?>;
    const qtyInputs = document.querySelectorAll('.checkout-combo-qty-dark');
    const comboTotalText = document.getElementById('combo-total-text');
    const grandTotalText = document.getElementById('grand-total-text');

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + ' đ';
    }

    function updateCheckoutTotals() {
        let comboTotal = 0;

        qtyInputs.forEach(function (input) {
            const qty = parseInt(input.value || 0, 10);
            const price = parseFloat(input.dataset.price || 0);
            comboTotal += qty * price;
        });

        comboTotalText.textContent = formatCurrency(comboTotal);
        grandTotalText.textContent = formatCurrency(seatSubtotal + comboTotal);
    }

    qtyInputs.forEach(function (input) {
        input.addEventListener('input', updateCheckoutTotals);
    });

    updateCheckoutTotals();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>