<?php
require_once '../../config/database.php';

$id = $_GET['id'];

$sql = "SELECT 
bookings.booking_code,
users.name,
movies.title,
shows.show_time,
bookings.subtotal,
bookings.discount_total,
bookings.total,
bookings.status,
bookings.created_at
FROM bookings
JOIN users ON bookings.user_id = users.id
JOIN shows ON bookings.show_id = shows.id
JOIN movies ON shows.movie_id = movies.id
WHERE bookings.booking_id = $id";

$result = mysqli_query($conn, $sql);
$booking = mysqli_fetch_assoc($result);
?>

<h2>Booking Detail</h2>

<p><strong>Booking Code:</strong> <?php echo $booking['booking_code']; ?></p>
<p><strong>User:</strong> <?php echo $booking['name']; ?></p>
<p><strong>Movie:</strong> <?php echo $booking['title']; ?></p>
<p><strong>Showtime:</strong> <?php echo $booking['show_time']; ?></p>

<p><strong>Subtotal:</strong> <?php echo $booking['subtotal']; ?></p>
<p><strong>Discount:</strong> <?php echo $booking['discount_total']; ?></p>
<p><strong>Total:</strong> <?php echo $booking['total']; ?></p>

<p><strong>Status:</strong> <?php echo $booking['status']; ?></p>
<p><strong>Created:</strong> <?php echo $booking['created_at']; ?></p>

<a href="index.php">Back</a>