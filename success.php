<?php
require_once __DIR__ . '/includes/auth.php';

require_login();

$conn = db_connect();
$bookingId = (int) get('booking_id');

if ($bookingId <= 0) {
    set_flash_message('error', 'Không tìm thấy đơn đặt vé.');
    redirect('movies.php');
}

$userId = (int) $_SESSION['user_id'];

$bookingSql = "
    SELECT
        b.booking_id,
        b.booking_code,
        b.status,
        b.subtotal,
        b.discount_total,
        b.total,
        b.created_at,
        m.title AS movie_title,
        m.poster_url,
        s.start_at,
        s.end_at,
        s.screen_format,
        s.subtitle_type,
        r.room_name,
        l.name AS location_name,
        l.address
    FROM bookings b
    INNER JOIN showtimes s ON b.show_id = s.show_id
    INNER JOIN movies m ON s.movie_id = m.movie_id
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    WHERE b.booking_id = ?
      AND b.user_id = ?
    LIMIT 1
";
$bookingStmt = $conn->prepare($bookingSql);
$bookingStmt->bind_param('ii', $bookingId, $userId);
$bookingStmt->execute();
$bookingResult = $bookingStmt->get_result();
$booking = $bookingResult->fetch_assoc();
$bookingStmt->close();

if (!$booking) {
    set_flash_message('error', 'Đơn đặt vé không tồn tại hoặc không thuộc tài khoản này.');
    redirect('movies.php');
}

$seatSql = "
    SELECT
        bs.unit_price,
        se.seat_row,
        se.seat_number,
        sc.name AS category_name
    FROM booking_seats bs
    INNER JOIN show_seats ss ON bs.show_seat_id = ss.show_seat_id
    INNER JOIN seats se ON ss.seat_id = se.seat_id
    INNER JOIN seat_categories sc ON se.category_id = sc.category_id
    WHERE bs.booking_id = ?
    ORDER BY se.seat_row ASC, CAST(se.seat_number AS UNSIGNED) ASC, se.seat_number ASC
";
$seatStmt = $conn->prepare($seatSql);
$seatStmt->bind_param('i', $bookingId);
$seatStmt->execute();
$seatResult = $seatStmt->get_result();

$comboSql = "
    SELECT
        bc.quantity,
        bc.unit_price,
        cp.name
    FROM booking_combos bc
    INNER JOIN combo_products cp ON bc.combo_id = cp.combo_id
    WHERE bc.booking_id = ?
";
$comboStmt = $conn->prepare($comboSql);
$comboStmt->bind_param('i', $bookingId);
$comboStmt->execute();
$comboResult = $comboStmt->get_result();

$paymentSql = "
    SELECT method, amount, status, paid_at, transaction_code
    FROM payments
    WHERE booking_id = ?
    ORDER BY payment_id DESC
    LIMIT 1
";
$paymentStmt = $conn->prepare($paymentSql);
$paymentStmt->bind_param('i', $bookingId);
$paymentStmt->execute();
$paymentResult = $paymentStmt->get_result();
$payment = $paymentResult->fetch_assoc();
$paymentStmt->close();

require_once __DIR__ . '/includes/header.php';
?>

<section class="success-section">
    <div class="success-box">
        <span class="badge">Đặt vé thành công</span>
        <h1 class="page-title">Xác nhận đặt vé</h1>
        <p class="page-subtitle">Thông tin đơn vé đã được lưu thành công trong hệ thống.</p>

        <div class="success-code">
            Mã đặt vé: <strong><?= e($booking['booking_code']) ?></strong>
        </div>

        <div class="checkout-layout" style="margin-top: 24px;">
            <div class="summary-card">
                <div class="checkout-movie">
                    <img src="<?= e($booking['poster_url'] ?: 'https://via.placeholder.com/300x420?text=Movie') ?>" alt="<?= e($booking['movie_title']) ?>">
                    <div>
                        <h3 style="font-size: 28px; margin-bottom: 10px;"><?= e($booking['movie_title']) ?></h3>
                        <p><strong>Rạp:</strong> <?= e($booking['location_name']) ?></p>
                        <p><strong>Phòng:</strong> <?= e($booking['room_name']) ?></p>
                        <p><strong>Suất chiếu:</strong> <?= date('d/m/Y H:i', strtotime($booking['start_at'])) ?></p>
                        <p><strong>Định dạng:</strong> <?= e($booking['screen_format'] ?: '2D') ?></p>
                        <p><strong>Phụ đề:</strong> <?= e($booking['subtitle_type'] ?: 'Không có') ?></p>
                        <p><strong>Trạng thái booking:</strong> <?= e($booking['status']) ?></p>
                    </div>
                </div>

                <div class="order-box" style="margin-top: 20px;">
                    <h3>Ghế đã đặt</h3>
                    <?php while ($seat = $seatResult->fetch_assoc()): ?>
                        <div class="order-line">
                            <span><?= e($seat['seat_row'] . $seat['seat_number']) ?> <small>(<?= e($seat['category_name']) ?>)</small></span>
                            <strong><?= format_currency($seat['unit_price']) ?></strong>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="order-box" style="margin-top: 20px;">
                    <h3>Combo</h3>
                    <?php if ($comboResult && $comboResult->num_rows > 0): ?>
                        <?php while ($combo = $comboResult->fetch_assoc()): ?>
                            <div class="order-line">
                                <span><?= e($combo['name']) ?> x <?= (int) $combo['quantity'] ?></span>
                                <strong><?= format_currency($combo['unit_price'] * $combo['quantity']) ?></strong>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">Không có combo đi kèm.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="checkout-card">
                <div class="order-box">
                    <h3 style="margin-bottom: 14px;">Thanh toán</h3>

                    <div class="order-line">
                        <span>Tạm tính</span>
                        <strong><?= format_currency($booking['subtotal']) ?></strong>
                    </div>

                    <div class="order-line">
                        <span>Giảm giá</span>
                        <strong><?= format_currency($booking['discount_total']) ?></strong>
                    </div>

                    <div class="order-line order-total">
                        <span>Tổng thanh toán</span>
                        <strong><?= format_currency($booking['total']) ?></strong>
                    </div>
                </div>

                <div class="order-box" style="margin-top: 20px;">
                    <h3 style="margin-bottom: 14px;">Thông tin giao dịch</h3>

                    <?php if ($payment): ?>
                        <div class="order-line">
                            <span>Phương thức</span>
                            <strong><?= e($payment['method']) ?></strong>
                        </div>

                        <div class="order-line">
                            <span>Trạng thái</span>
                            <strong><?= e($payment['status']) ?></strong>
                        </div>

                        <div class="order-line">
                            <span>Mã giao dịch</span>
                            <strong><?= e($payment['transaction_code']) ?></strong>
                        </div>

                        <div class="order-line">
                            <span>Thời gian thanh toán</span>
                            <strong><?= !empty($payment['paid_at']) ? date('d/m/Y H:i', strtotime($payment['paid_at'])) : 'Đang cập nhật' ?></strong>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">Chưa có dữ liệu thanh toán.</div>
                    <?php endif; ?>
                </div>

                <div class="movie-actions" style="margin-top: 22px;">
                    <a class="btn" href="<?= BASE_URL ?>">Về trang chủ</a>
                    <a class="btn btn-outline" href="<?= BASE_URL ?>movies.php">Đặt vé tiếp</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$seatStmt->close();
$comboStmt->close();
require_once __DIR__ . '/includes/footer.php';
?>