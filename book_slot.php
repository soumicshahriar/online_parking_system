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

// Calculate end time
$end_time = date('H:i', strtotime("+$duration hours", strtotime($booking_time)));

// Check for overlapping bookings
$query = "SELECT id FROM bookings WHERE slot_id = $slot_id AND booking_date = '$booking_date' AND (
    (booking_time <= '$booking_time' AND end_time > '$booking_time') OR
    (booking_time < '$end_time' AND end_time >= '$end_time') OR
    (booking_time >= '$booking_time' AND end_time <= '$end_time')
)";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "This slot is already booked for the selected date and time.";
} else {
    // Insert booking into database
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, slot_id, vehicle_number, booking_date, booking_time, duration, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssis", $user_id, $slot_id, $vehicle_number, $booking_date, $booking_time, $duration, $end_time);

    if ($stmt->execute()) {
        echo "Booking successful!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>