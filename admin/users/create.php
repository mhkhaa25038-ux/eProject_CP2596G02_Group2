<?php
require_once '../../config/database.php';
$conn = db_connect();

require_once __DIR__ . '/../../includes/admin_layout_start.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users (name,email,password_hash,role)
            VALUES ('$name','$email','$password','$role')";

    mysqli_query($conn, $sql);

    header("Location: index.php");
}
?>

<div class="content-card">
    <div class="card-header">
        <h2>Thêm người dùng</h2>
    </div>

    <form method="POST" class="admin-form">
        <input type="text" name="name" placeholder="Tên" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>

        <select name="role">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit" class="btn-primary">Lưu</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>