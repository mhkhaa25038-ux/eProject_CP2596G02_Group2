<?php
require_once '../../includes/helpers.php';
require_once '../../includes/admin_layout_start.php';
$conn = db_connect();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $link = $_POST['link'];
    $status = $_POST['status'];
    $imageName = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    $uploadPath = "../../public/uploads/banners/" . $imageName;
    move_uploaded_file($tmp, $uploadPath);
    $sql = "INSERT INTO banners (title,image_url,link_url,status,created_at)
VALUES ('$title','$imageName','$link','$status',NOW())";
    mysqli_query($conn, $sql);
    header("Location: index.php");
    exit;
}
?>
<div class="admin-card">
    <div class="admin-card-head">
        <h2>Create Banner</h2>
    </div>
    <form method="POST" enctype="multipart/form-data">
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-label">Title</label>
                <input type="text" name="title" class="admin-input" required>
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Banner Image</label>
                <input type="file" name="image" class="admin-input" required>
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Link URL</label>
                <input type="text" name="link" class="admin-input">
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Status</label>
                <select name="status" class="admin-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <button type="submit" class="admin-btn">Create Banner</button>
    </form>
</div>
<script>
    function previewBanner(event) {
        const preview = document.getElementById("preview");
        preview.src = URL.createObjectURL(event.target.files[0]);

    }
</script>
<?php require_once '../../includes/admin_layout_end.php'; ?>