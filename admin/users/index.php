<?php
require_once '../../config/database.php';
$conn = db_connect();
require_once __DIR__ . '/../../includes/admin_layout_start.php';
$sql = "SELECT * FROM users ORDER BY user_id DESC";
$result = mysqli_query($conn, $sql);
?>
<div class="content-card">
    <div class="card-header">
        <h2>Danh sách người dùng</h2>
        <a href="create.php" class="btn-primary">+ Thêm user</a>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['user_id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td>
                            <span class="badge <?= $row['role'] ?>">
                                <?= $row['role'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit.php?id=<?= $row['user_id'] ?>" class="action edit">Sửa</a>
                            <a href="delete.php?id=<?= $row['user_id'] ?>" class="action delete">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">
                        <div class="empty">Chưa có user nào.</div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>