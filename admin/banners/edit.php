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
<div class="admin-card">
    <div class="admin-card-head">
        <h2>Edit Banner</h2>
    </div>
    <form method="POST" enctype="multipart/form-data">
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-label">Title</label>
                <input type="text" name="title" class="admin-input" value="<?= $banner['title'] ?>">
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Current Image</label><br>
                <?php if (!empty($banner['image_url'])) { ?>
                    <img src="../../public/uploads/banners/<?= $banner['image_url'] ?>" width="200">
                <?php } else { ?>
                    <p>No image</p>
                <?php } ?>
                <br><br>
                <label class="admin-label">Change Image</label>
                <input type="file" name="image" class="admin-input">
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Link URL</label>
                <input type="text" name="link_url" class="admin-input" value="<?= $banner['link_url'] ?>">
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Status</label>
                <select name="status" class="admin-select">
                    <option value="active" <?= $banner['status'] == "active" ? "selected" : "" ?>>
                        Active
                    </option>
                    <option value="inactive" <?= $banner['status'] == "inactive" ? "selected" : "" ?>>
                        Inactive
                    </option>
                </select>
            </div>
        </div>
        <button type="submit" class="admin-btn">Update Banner</button>
    </form>
</div>
<?php require_once '../../includes/admin_layout_end.php'; ?>