<?php
require_once '../../config/database.php';

$id = $_GET['id'];

$sql = "SELECT * FROM banners WHERE id=$id";
$result = mysqli_query($conn, $sql);
$banner = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST['title'];

    $image = $_FILES['image']['name'];

    if ($image != "") {

        $tmp = $_FILES['image']['tmp_name'];

        move_uploaded_file($tmp, "../../public/uploads/" . $image);

        $sql = "UPDATE banners SET title='$title', image='$image' WHERE id=$id";

    } else {

        $sql = "UPDATE banners SET title='$title' WHERE id=$id";

    }

    mysqli_query($conn, $sql);

    header("Location: index.php");

}
?>

<h2>Edit Banner</h2>

<form method="POST" enctype="multipart/form-data">

    Title<br>
    <input type="text" name="title" value="<?php echo $banner['title']; ?>"><br><br>

    Current Image<br>
    <img src="../../public/uploads/<?php echo $banner['image']; ?>" width="200"><br><br>

    Change Image<br>
    <input type="file" name="image"><br><br>

    <button type="submit">Update</button>

</form>