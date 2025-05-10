<?php
require_once 'database/db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit();
}

$id = $data['id'];

// Start transaction
$conn->begin_transaction();

try {
    // Check if booking exists and is completed
    $query = "SELECT slot_id FROM a_bookings WHERE id = ? AND 
              NOW() > CONCAT(booking_date, ' ', end_time)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Booking not found or cannot be deleted');
    }

    $booking = $result->fetch_assoc();
    $slot_id = $booking['slot_id'];

    // Delete the booking
    $delete_booking = "DELETE FROM a_bookings WHERE id = ?";
    $stmt = $conn->prepare($delete_booking);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Update slot availability if needed
    $update_slot = "UPDATE parking_slots SET is_available = 1 WHERE id = ?";
    $stmt = $conn->prepare($update_slot);
    $stmt->bind_param("i", $slot_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Booking deleted successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?> 