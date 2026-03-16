<?php
require_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users (name,email,password,role)
VALUES ('$name','$email','$password','$role')";

    mysqli_query($conn, $sql);

    header("Location: index.php");
}
?>

<h2>Add User</h2>

<form method="POST">

    Name<br>
    <input type="text" name="name"><br><br>

    Email<br>
    <input type="email" name="email"><br><br>

    Password<br>
    <input type="password" name="password"><br><br>

    Role<br>
    <select name="role">
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select><br><br>

    <button type="submit">Save</button>

</form>