<?php
$pageTitle = 'Quản lý phòng';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$sql = "
    SELECT r.room_id, r.room_name, r.capacity, r.status, l.name AS location_name
    FROM rooms r
    INNER JOIN locations l ON r.location_id = l.location_id
    ORDER BY l.name ASC, r.room_name ASC
";
$result = $conn->query($sql);
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Danh sách phòng</h2>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/rooms/create.php">+ Thêm phòng</a>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên phòng</th>
                    <th>Rạp</th>
                    <th>Sức chứa</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($room = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int) $room['room_id'] ?></td>
                        <td><?= e($room['room_name']) ?></td>
                        <td><?= e($room['location_name']) ?></td>
                        <td><?= (int) $room['capacity'] ?></td>
                        <td><span class="admin-badge active"><?= e($room['status']) ?></span></td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/rooms/edit.php?id=<?= (int) $room['room_id'] ?>">Sửa</a>
                                <a class="admin-btn-danger" href="<?= BASE_URL ?>admin/rooms/delete.php?id=<?= (int) $room['room_id'] ?>" onclick="return confirm('Xóa phòng này?')">Xóa</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6"><div class="admin-empty">Chưa có phòng nào.</div></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>