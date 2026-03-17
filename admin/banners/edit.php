<?php
require_once '../../config/database.php';
require_once '../../includes/admin_layout_start.php';

$conn = db_connect();
$id = $_GET['id'];
$sql = "SELECT * FROM banners WHERE banner_id=$id";
$result = mysqli_query($conn, $sql);
$banner = mysqli_fetch_assoc($result);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $image = $_FILES['image']['name'];
    if ($image != "") {
        $tmp = $_FILES['image']['tmp_name'];
        move_uploaded_file($tmp, "../../public/uploads/banners/" . $image);
        $sql = "UPDATE banners 
SET title='$title', image_url='$image' 
WHERE banner_id=$id";
    } else {
        $sql = "UPDATE banners 
SET title='$title' 
WHERE banner_id=$id";
    }
    mysqli_query($conn, $sql);
    header("Location:index.php");
}
?>
<div class="content-card">

    <div class="card-header">
        <h2>Chỉnh sửa banner</h2>
    </div>

    <form method="POST" enctype="multipart/form-data" class="admin-form">

        <div class="form-group">
            <label>Tiêu đề</label>
            <input type="text" name="title" value="<?= $banner['title'] ?>">
        </div>

        <div class="form-group">
            <label>Ảnh hiện tại</label><br>
            <img src="../../public/uploads/banners/<?= $banner['image_url'] ?>" class="banner-preview">
        </div>

        <div class="form-group">
            <label>Đổi ảnh</label>
            <input type="file" name="image">
        </div>

        <div class="form-group">
            <label>Link</label>
            <input type="text" name="link_url" value="<?= $banner['link_url'] ?>">
        </div>

        <div class="form-group">
            <label>Trạng thái</label>
            <select name="status">
                <option value="active" <?= $banner['status'] == "active" ? "selected" : "" ?>>Active</option>
                <option value="inactive" <?= $banner['status'] == "inactive" ? "selected" : "" ?>>Inactive</option>
            </select>
        </div>

        <button type="submit" class="btn-primary">Cập nhật</button>

    </form>

</div>
<?php require_once '../../includes/admin_layout_end.php'; ?>