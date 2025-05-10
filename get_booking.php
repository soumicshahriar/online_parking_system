<?php
require_once 'database/db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit();
}

$id = $_GET['id'];

// Get booking details with user and slot information
$query = "SELECT 
    a.*,
    u.username,
    p.slot_number,
    p.location,
    CASE
        WHEN NOW() < CONCAT(a.booking_date, ' ', a.booking_time) THEN 'Pending'
        WHEN NOW() BETWEEN CONCAT(a.booking_date, ' ', a.booking_time) 
            AND CONCAT(a.booking_date, ' ', a.end_time) THEN 'Active'
        ELSE 'Completed'
    END as current_status
FROM a_bookings a 
JOIN users u ON a.user_id = u.id 
JOIN parking_slots p ON a.slot_id = p.id 
WHERE a.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $booking = $result->fetch_assoc();
    echo json_encode(['success' => true, 'booking' => $booking]);
} else {
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
}

$stmt->close();
$conn->close();
?> 