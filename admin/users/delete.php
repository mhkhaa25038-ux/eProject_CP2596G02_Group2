<?php
require_once '../../config/database.php';

$id = $_GET['id'];

$sql = "DELETE FROM users WHERE id=$id";

mysqli_query($conn, $sql);

header("Location: index.php");
?>