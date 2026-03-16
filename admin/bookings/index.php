<?php
require_once '../../config/database.php';

$sql = "SELECT 
bookings.booking_id,
bookings.booking_code,
users.name AS user_name,
movies.title AS movie_title,
shows.show_time,
bookings.total,
bookings.status,
bookings.created_at
FROM bookings
JOIN users ON bookings.user_id = users.id
JOIN shows ON bookings.show_id = shows.id
JOIN movies ON shows.movie_id = movies.id
ORDER BY bookings.booking_id DESC";

$result = mysqli_query($conn, $sql);
?>

<h2>Manage Bookings</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Code</th>
        <th>User</th>
        <th>Movie</th>
        <th>Total</th>
        <th>Status</th>
        <th>Created</th>
        <th>Action</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>

        <tr>
            <td><?php echo $row['booking_id']; ?></td>
            <td><?php echo $row['booking_code']; ?></td>
            <td><?php echo $row['user_name']; ?></td>
            <td><?php echo $row['movie_title']; ?></td>
            <td><?php echo $row['total']; ?></td>
            <td><?php echo $row['status']; ?></td>
            <td><?php echo $row['created_at']; ?></td>

            <td>
                <a href="detail.php?id=<?php echo $row['booking_id']; ?>">View</a>
            </td>

        </tr>

    <?php } ?>

</table>