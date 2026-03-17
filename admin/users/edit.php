<?php
require_once '../../config/database.php';
require_once __DIR__ . '/../../includes/admin_layout_start.php';
$conn = db_connect();
$id = $_GET['id'];

$sql = "SELECT * FROM users WHERE user_id=$id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $sql = "UPDATE users SET
name='$name',
email='$email',
role='$role'
WHERE user_id=$id";

    mysqli_query($conn, $sql);

    header("Location: index.php");
}
?>

<div class="content-card">

    <div class="card-header">
        <h2>Chỉnh sửa người dùng</h2>
    </div>

    <form method="POST" class="admin-form">

        <div class="form-group">
            <label>Tên</label>
            <input type="text" name="name" value="<?= $user['name'] ?>">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= $user['email'] ?>">
        </div>

        <div class="form-group">
            <label>Vai trò</label>
            <select name="role">
                <option value="user" <?= $user['role'] == "user" ? "selected" : "" ?>>User</option>
                <option value="admin" <?= $user['role'] == "admin" ? "selected" : "" ?>>Admin</option>
            </select>
        </div>

        <button type="submit" class="btn-primary">Cập nhật</button>

    </form>

</div>
<?php require_once __DIR__ . '/../../includes/admin_layout_start.php'; ?>