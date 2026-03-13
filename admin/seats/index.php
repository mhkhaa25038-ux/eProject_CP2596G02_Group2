<?php
$pageTitle = 'Quản lý ghế';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$roomFilter = (int) get('room_id');
$rooms = $conn->query("
    SELECT r.room_id, r.room_name, l.name AS location_name
    FROM rooms r
    INNER JOIN locations l ON r.location_id = l.location_id
    ORDER BY l.name ASC, r.room_name ASC
");

$where = '';
if ($roomFilter > 0) {
    $where = "WHERE s.room_id = " . $roomFilter;
}

$sql = "
    SELECT
        s.seat_id,
        s.seat_row,
        s.seat_number,
        s.is_aisle,
        s.is_accessible,
        r.room_name,
        l.name AS location_name,
        sc.name AS category_name
    FROM seats s
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    INNER JOIN seat_categories sc ON s.category_id = sc.category_id
    $where
    ORDER BY l.name ASC, r.room_name ASC, s.seat_row ASC, CAST(s.seat_number AS UNSIGNED) ASC, s.seat_number ASC
";
$result = $conn->query($sql);
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Danh sách ghế</h2>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/seats/create.php">+ Thêm ghế</a>
    </div>

    <div class="admin-filter-bar">
        <form method="GET">
            <select class="admin-select" name="room_id">
                <option value="">-- Tất cả phòng --</option>
                <?php if ($rooms && $rooms->num_rows > 0): ?>
                    <?php while ($room = $rooms->fetch_assoc()): ?>
                        <option value="<?= (int) $room['room_id'] ?>" <?= $roomFilter === (int) $room['room_id'] ? 'selected' : '' ?>>
                            <?= e($room['location_name'] . ' - ' . $room['room_name']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>

            <button class="admin-btn" type="submit">Lọc</button>
            <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/seats/index.php">Reset</a>
        </form>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Rạp</th>
                    <th>Phòng</th>
                    <th>Hàng</th>
                    <th>Số ghế</th>
                    <th>Loại ghế</th>
                    <th>Lối đi</th>
                    <th>Hỗ trợ</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($seat = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int) $seat['seat_id'] ?></td>
                        <td><?= e($seat['location_name']) ?></td>
                        <td><?= e($seat['room_name']) ?></td>
                        <td><?= e($seat['seat_row']) ?></td>
                        <td><?= e($seat['seat_number']) ?></td>
                        <td><?= e($seat['category_name']) ?></td>
                        <td><?= (int) $seat['is_aisle'] === 1 ? 'Có' : 'Không' ?></td>
                        <td><?= (int) $seat['is_accessible'] === 1 ? 'Có' : 'Không' ?></td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/seats/edit.php?id=<?= (int) $seat['seat_id'] ?>">Sửa</a>
                                <a class="admin-btn-danger" href="<?= BASE_URL ?>admin/seats/delete.php?id=<?= (int) $seat['seat_id'] ?>" onclick="return confirm('Xóa ghế này?')">Xóa</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9"><div class="admin-empty">Chưa có ghế nào.</div></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>