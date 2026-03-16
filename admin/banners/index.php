<?php
require_once '../../config/database.php';

$sql = "SELECT * FROM banners ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<h2>Manage Banners</h2>

<a href="create.php">Add Banner</a>

<table border="1" cellpadding="10">

    <tr>
        <th>ID</th>
        <th>Image</th>
        <th>Title</th>
        <th>Action</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>

        <tr>

            <td><?php echo $row['id']; ?></td>

            <td>
                <img src="../../public/uploads/<?php echo $row['image']; ?>" width="150">
            </td>

            <td><?php echo $row['title']; ?></td>

            <td>

                <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a>

                <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Delete banner?')">Delete</a>

            </td>

        </tr>

    <?php } ?>

</table>