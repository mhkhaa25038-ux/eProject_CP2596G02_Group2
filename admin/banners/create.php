<?php
require_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST['title'];

    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];

    move_uploaded_file($tmp, "../../public/uploads/" . $image);

    $sql = "INSERT INTO banners (title,image)
VALUES ('$title','$image')";

    mysqli_query($conn, $sql);

    header("Location: index.php");

}
?>

<h2>Add Banner</h2>

<form method="POST" enctype="multipart/form-data">

    Title<br>
    <input type="text" name="title"><br><br>

    Image<br>
    <input type="file" name="image"><br><br>

    <button type="submit">Save</button>

</form>