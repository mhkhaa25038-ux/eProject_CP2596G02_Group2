<?php
require_once '../../config/database.php';
require_once '../../includes/admin_layout_start.php';
$conn = db_connect();
$sql = "SELECT * FROM banners ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$banners = [];
while ($row = mysqli_fetch_assoc($result)) {
    $banners[] = $row;
}
?>

<div class="section-inner">
    <h1>Manage Banners</h1>
    <a class="hero-btn-primary" href="create.php">Add Banner</a>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Image</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($banners as $banner): ?>
                <tr>
                    <td><?= $banner['banner_id'] ?></td>
                    <td><?= $banner['title'] ?></td>
                    <td>
                        <img class="banner-img" src="<?= BASE_URL ?>public/uploads/banners/<?= $banner['image_url'] ?>">
                    </td>
                    <td>
                        <span
                            class="status-badge <?= $banner['status'] == 'active' ? 'status-active' : 'status-inactive' ?>">
                            <?= $banner['status'] ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-group">
                            <a class="btn btn-edit" href="edit.php?id=<?= $banner['banner_id'] ?>">Sửa</a>
                            <a class="btn btn-delete" onclick="return confirm('Xóa banner?')"
                                href="delete.php?id=<?= $banner['banner_id'] ?>">Xóa</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once '../../includes/admin_layout_end.php'; ?>