<?php
$pageTitle = 'Dashboard Admin';
require_once __DIR__ . '/../includes/admin_layout_start.php';

$conn = db_connect();

$totalMovies = 0;
$totalRooms = 0;
$totalSeats = 0;
$totalNowShowing = 0;
$totalComingSoon = 0;
$totalLocations = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM movies");
if ($result) {
    $row = $result->fetch_assoc();
    $totalMovies = (int) $row['total'];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM rooms");
if ($result) {
    $row = $result->fetch_assoc();
    $totalRooms = (int) $row['total'];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM seats");
if ($result) {
    $row = $result->fetch_assoc();
    $totalSeats = (int) $row['total'];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM movies WHERE status = 'now_showing'");
if ($result) {
    $row = $result->fetch_assoc();
    $totalNowShowing = (int) $row['total'];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM movies WHERE status = 'coming_soon'");
if ($result) {
    $row = $result->fetch_assoc();
    $totalComingSoon = (int) $row['total'];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM locations");
if ($result) {
    $row = $result->fetch_assoc();
    $totalLocations = (int) $row['total'];
}

$latestMovies = $conn->query("
    SELECT movie_id, title, language, duration_min, status, created_at
    FROM movies
    ORDER BY created_at DESC
    LIMIT 5
");

$latestRooms = $conn->query("
    SELECT r.room_id, r.room_name, r.capacity, r.status, l.name AS location_name
    FROM rooms r
    INNER JOIN locations l ON r.location_id = l.location_id
    ORDER BY r.created_at DESC
    LIMIT 5
");
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Tổng quan hệ thống</h2>
    </div>

    <div style="display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:16px;">
        <div class="admin-card" style="margin-bottom:0;">
            <h3 style="font-size:16px; color:#94a3b8; margin-bottom:8px;">Tổng phim</h3>
            <p style="font-size:34px; font-weight:800;"><?= $totalMovies ?></p>
        </div>

        <div class="admin-card" style="margin-bottom:0;">
            <h3 style="font-size:16px; color:#94a3b8; margin-bottom:8px;">Tổng phòng</h3>
            <p style="font-size:34px; font-weight:800;"><?= $totalRooms ?></p>
        </div>

        <div class="admin-card" style="margin-bottom:0;">
            <h3 style="font-size:16px; color:#94a3b8; margin-bottom:8px;">Tổng ghế</h3>
            <p style="font-size:34px; font-weight:800;"><?= $totalSeats ?></p>
        </div>

        <div class="admin-card" style="margin-bottom:0;">
            <h3 style="font-size:16px; color:#94a3b8; margin-bottom:8px;">Phim đang chiếu</h3>
            <p style="font-size:34px; font-weight:800; color:#86efac;"><?= $totalNowShowing ?></p>
        </div>

        <div class="admin-card" style="margin-bottom:0;">
            <h3 style="font-size:16px; color:#94a3b8; margin-bottom:8px;">Phim sắp chiếu</h3>
            <p style="font-size:34px; font-weight:800; color:#fde68a;"><?= $totalComingSoon ?></p>
        </div>

        <div class="admin-card" style="margin-bottom:0;">
            <h3 style="font-size:16px; color:#94a3b8; margin-bottom:8px;">Tổng rạp / chi nhánh</h3>
            <p style="font-size:34px; font-weight:800; color:#93c5fd;"><?= $totalLocations ?></p>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Truy cập nhanh</h2>
    </div>

    <div class="admin-actions">
        <a class="admin-btn" href="<?= BASE_URL ?>admin/movies/index.php">Quản lý phim</a>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/rooms/index.php">Quản lý phòng</a>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/seats/index.php">Quản lý ghế</a>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>">Trang user</a>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Phim mới thêm</h2>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tên phim</th>
                        <th>Ngôn ngữ</th>
                        <th>Thời lượng</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($latestMovies && $latestMovies->num_rows > 0): ?>
                    <?php while ($movie = $latestMovies->fetch_assoc()): ?>
                        <tr>
                            <td><?= e($movie['title']) ?></td>
                            <td><?= e($movie['language']) ?></td>
                            <td><?= (int) $movie['duration_min'] ?> phút</td>
                            <td>
                                <span class="admin-badge <?= $movie['status'] === 'now_showing' ? 'showing' : 'coming' ?>">
                                    <?= e($movie['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4"><div class="admin-empty">Chưa có dữ liệu phim.</div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Phòng mới thêm</h2>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tên phòng</th>
                        <th>Rạp</th>
                        <th>Sức chứa</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($latestRooms && $latestRooms->num_rows > 0): ?>
                    <?php while ($room = $latestRooms->fetch_assoc()): ?>
                        <tr>
                            <td><?= e($room['room_name']) ?></td>
                            <td><?= e($room['location_name']) ?></td>
                            <td><?= (int) $room['capacity'] ?></td>
                            <td><span class="admin-badge active"><?= e($room['status']) ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4"><div class="admin-empty">Chưa có dữ liệu phòng.</div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_layout_end.php'; ?>