<?php
// include the database connection file
require 'database/db.php';

// Get current date and time
$current_time = date('H:i');
$current_date = date('Y-m-d');

// Update status of bookings
$query = "UPDATE bookings 
          SET status = CASE 
              WHEN booking_date = '$current_date' AND '$current_time' >= booking_time AND '$current_time' <= end_time THEN 'active'
              WHEN booking_date = '$current_date' AND '$current_time' < booking_time THEN 'pending'
              WHEN booking_date < '$current_date' OR (booking_date = '$current_date' AND '$current_time' > end_time) THEN 'done'
              ELSE status
          END";

if ($conn->query($query)) {
    echo "Booking statuses updated successfully!";
} else {
    echo "Error updating booking statuses: " . $conn->error;
}

$conn->close();
?>