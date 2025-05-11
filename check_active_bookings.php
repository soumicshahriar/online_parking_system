<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    echo "0";
    exit();
}

// include the database connection file
require 'database/db.php';

// Get the date from POST request
$date = $_POST['date'] ?? date('Y-m-d');
$user_id = $_SESSION['user_id'];

// Check active bookings for the date
$query = "SELECT COUNT(*) as active_count FROM a_bookings 
          WHERE user_id = ? 
          AND booking_date = ?
          AND NOW() BETWEEN CONCAT(booking_date, ' ', booking_time) 
          AND CONCAT(booking_date, ' ', end_time)";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $user_id, $date);
$stmt->execute();
$result = $stmt->get_result();
$active_bookings = $result->fetch_assoc()['active_count'];

echo $active_bookings;
?> 