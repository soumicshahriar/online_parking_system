<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please log in to book a slot.";
    exit();
}

// Database connection
$host = 'localhost';
$db   = 'online_parking';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$slot_id = $_POST['slot_id'];
$user_id = $_SESSION['user_id'];
$vehicle_number = $_POST['vehicle_number'];
$booking_date = $_POST['booking_date'];
$booking_time = $_POST['booking_time'];
$duration = $_POST['duration'];
$cost_per_hour = $_POST['cost_per_hour'];

// Calculate end time and total cost
$end_time = date('H:i', strtotime("+$duration hours", strtotime($booking_time)));
$total_cost = $cost_per_hour * $duration;

// Insert booking into database
$stmt = $conn->prepare("INSERT INTO bookings (user_id, slot_id, vehicle_number, booking_date, booking_time, duration, end_time, cost_per_hour, total_cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisssisdd", $user_id, $slot_id, $vehicle_number, $booking_date, $booking_time, $duration, $end_time, $cost_per_hour, $total_cost);

if ($stmt->execute()) {
    echo "Booking successful! Total cost: $" . number_format($total_cost, 2);
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>