<?php
require_once __DIR__ . '/includes/auth.php';

require_login();

$conn = db_connect();
$showId = (int) get('show_id');

if ($showId <= 0) {
    set_flash_message('error', 'Suất chiếu không hợp lệ.');
    redirect('movies.php');
}

$showtimeSql = "
    SELECT 
        s.show_id,
        s.start_at,
        s.end_at,
        s.base_price,
        s.screen_format,
        s.subtitle_type,
        s.status,
        m.movie_id,
        m.title AS movie_title,
        m.poster_url,
        m.duration_min,
        r.room_id,
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

$showtimeStmt = $conn->prepare($showtimeSql);
$showtimeStmt->bind_param('i', $showId);
$showtimeStmt->execute();
$showtimeResult = $showtimeStmt->get_result();
$showtime = $showtimeResult->fetch_assoc();
$showtimeStmt->close();

if (!$showtime) {
    set_flash_message('error', 'Không tìm thấy suất chiếu.');
    redirect('movies.php');
}

$seatSql = "
    SELECT
        ss.show_seat_id,
        ss.status AS show_seat_status,
        ss.final_price,
        se.seat_id,
        se.seat_row,
        se.seat_number,
        se.is_aisle,
        se.is_accessible,
        sc.name AS category_name,
        sc.price_multiplier
    FROM show_seats ss
    INNER JOIN seats se ON ss.seat_id = se.seat_id
    INNER JOIN seat_categories sc ON se.category_id = sc.category_id
    WHERE ss.show_id = ?
    ORDER BY se.seat_row ASC, CAST(se.seat_number AS UNSIGNED) ASC, se.seat_number ASC
";

$seatStmt = $conn->prepare($seatSql);
$seatStmt->bind_param('i', $showId);
$seatStmt->execute();
$seatResult = $seatStmt->get_result();

$seatMap = [];
$allSeats = [];
$availableCount = 0;

while ($seat = $seatResult->fetch_assoc()) {
    $row = $seat['seat_row'];
    $seatMap[$row][] = $seat;
    $allSeats[(int)$seat['show_seat_id']] = $seat;

    if ($seat['show_seat_status'] === 'available') {
        $availableCount++;
    }
}
$seatStmt->close();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedSeats = $_POST['selected_seats'] ?? [];

    if (empty($selectedSeats)) {
        $error = 'Vui lòng chọn ít nhất một ghế.';
    } else {
        $selectedSeatIds = [];
        $selectedSeatDetails = [];
        $subtotal = 0;

        foreach ($selectedSeats as $seatId) {
            $seatId = (int) $seatId;

            if (!isset($allSeats[$seatId])) {
                continue;
            }

            if ($allSeats[$seatId]['show_seat_status'] !== 'available') {
                continue;
            }

            $seatData = $allSeats[$seatId];
            $selectedSeatIds[] = $seatId;
            $selectedSeatDetails[] = [
                'show_seat_id' => $seatId,
                'label' => $seatData['seat_row'] . $seatData['seat_number'],
                'price' => (float) $seatData['final_price'],
                'category_name' => $seatData['category_name']
            ];
            $subtotal += (float) $seatData['final_price'];
        }

        if (empty($selectedSeatIds)) {
            $error = 'Các ghế đã chọn không hợp lệ hoặc không còn trống.';
        } else {
            $_SESSION['booking'] = [
                'show_id' => $showId,
                'movie_id' => (int) $showtime['movie_id'],
                'movie_title' => $showtime['movie_title'],
                'room_name' => $showtime['room_name'],
                'location_name' => $showtime['location_name'],
                'start_at' => $showtime['start_at'],
                'selected_seat_ids' => $selectedSeatIds,
                'selected_seats' => $selectedSeatDetails,
                'seat_subtotal' => $subtotal
            ];

            redirect('checkout.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="seat-page-wrap">
    <div class="seat-page-inner">
        <div class="seat-page-head">
            <div>
                <span class="movie-status-badge showing">SELECT SEATS</span>
                <h1 class="seat-page-title"><?= e($showtime['movie_title']) ?></h1>
                <p class="seat-page-subtitle">
                    Choose your seats for the selected showtime.
                </p>
            </div>
        </div>

        <div class="seat-layout-dark">
            <div class="seat-left-dark">
                <div class="seat-show-card-dark">
                    <div class="seat-show-poster">
                        <img
                            src="<?= e($showtime['poster_url'] ?: 'https://via.placeholder.com/400x600?text=Movie') ?>"
                            alt="<?= e($showtime['movie_title']) ?>"
                        >
                    </div>

                    <div class="seat-show-info">
                        <h2><?= e($showtime['movie_title']) ?></h2>

                        <div class="seat-show-meta">
                            <span><?= e($showtime['location_name']) ?></span>
                            <span><?= e($showtime['room_name']) ?></span>
                            <span><?= date('d/m/Y H:i', strtotime($showtime['start_at'])) ?></span>
                            <span><?= e($showtime['screen_format'] ?: '2D') ?></span>
                            <span><?= e($showtime['subtitle_type'] ?: 'No subtitle') ?></span>
                        </div>

                        <div class="seat-legend-dark">
                            <div class="legend-item-dark">
                                <span class="legend-box available"></span>
                                <span>Available</span>
                            </div>
                            <div class="legend-item-dark">
                                <span class="legend-box booked"></span>
                                <span>Booked</span>
                            </div>
                            <div class="legend-item-dark">
                                <span class="legend-box selected"></span>
                                <span>Selected</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="seat-map-card-dark">
                    <div class="screen-box-dark">SCREEN</div>

                    <?php if ($error !== ''): ?>
                        <div class="auth-error" style="margin-bottom: 18px;">
                            <?= e($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($seatMap)): ?>
                        <form method="POST" action="" id="seatForm">
                            <div class="seat-map-dark">
                                <?php foreach ($seatMap as $row => $seats): ?>
                                    <div class="seat-row-dark">
                                        <div class="seat-row-label-dark"><?= e($row) ?></div>

                                        <div class="seat-row-items-dark">
                                            <?php foreach ($seats as $seat): ?>
                                                <?php
                                                $seatLabel = $seat['seat_row'] . $seat['seat_number'];
                                                $status = $seat['show_seat_status'];
                                                $disabled = $status !== 'available';
                                                ?>
                                                <label
                                                    class="seat-item-dark <?= $disabled ? 'booked' : 'available' ?>"
                                                    title="<?= e($seatLabel . ' - ' . $seat['category_name'] . ' - ' . format_currency($seat['final_price'])) ?>"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        name="selected_seats[]"
                                                        value="<?= (int) $seat['show_seat_id'] ?>"
                                                        data-label="<?= e($seatLabel) ?>"
                                                        data-price="<?= (float) $seat['final_price'] ?>"
                                                        <?= $disabled ? 'disabled' : '' ?>
                                                    >
                                                    <span><?= e($seatLabel) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="empty-state">
                            Chưa có dữ liệu ghế cho suất chiếu này.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="seat-right-dark">
                <div class="seat-summary-card-dark">
                    <h3>Booking Summary</h3>

                    <div class="seat-summary-line">
                        <span>Movie</span>
                        <strong><?= e($showtime['movie_title']) ?></strong>
                    </div>

                    <div class="seat-summary-line">
                        <span>Location</span>
                        <strong><?= e($showtime['location_name']) ?></strong>
                    </div>

                    <div class="seat-summary-line">
                        <span>Room</span>
                        <strong><?= e($showtime['room_name']) ?></strong>
                    </div>

                    <div class="seat-summary-line">
                        <span>Showtime</span>
                        <strong><?= date('d/m H:i', strtotime($showtime['start_at'])) ?></strong>
                    </div>

                    <div class="seat-summary-line">
                        <span>Available seats</span>
                        <strong><?= (int) $availableCount ?></strong>
                    </div>

                    <div class="seat-summary-block">
                        <span class="seat-summary-label">Selected Seats</span>
                        <div id="selected-seat-labels" class="seat-selected-tags">No seats selected</div>
                    </div>

                    <div class="seat-summary-total">
                        <span>Total</span>
                        <strong id="selected-seat-total">0 đ</strong>
                    </div>

                    <div class="seat-summary-actions">
                        <a class="movie-detail-outline-btn" href="<?= BASE_URL ?>movie_detail.php?id=<?= (int) $showtime['movie_id'] ?>">
                            Back
                        </a>

                        <?php if (!empty($seatMap)): ?>
                            <button type="submit" form="seatForm" class="movie-book-btn-dark seat-continue-btn">
                                Continue
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('input[name="selected_seats[]"]');
    const totalEl = document.getElementById('selected-seat-total');
    const labelsEl = document.getElementById('selected-seat-labels');

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + ' đ';
    }

    function updateSummary() {
        let total = 0;
        let labels = [];

        checkboxes.forEach(function (checkbox) {
            const seatItem = checkbox.closest('.seat-item-dark');

            if (checkbox.checked) {
                total += Number(checkbox.dataset.price || 0);
                labels.push(checkbox.dataset.label || '');
                seatItem.classList.add('selected');
            } else {
                seatItem.classList.remove('selected');
            }
        });

        totalEl.textContent = formatCurrency(total);

        if (labels.length) {
            labelsEl.innerHTML = labels.map(label => '<span class="seat-tag">' + label + '</span>').join('');
        } else {
            labelsEl.textContent = 'No seats selected';
        }
    }

    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', updateSummary);
    });

    updateSummary();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>