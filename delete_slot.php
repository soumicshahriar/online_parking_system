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
    echo json_encode(['success' => false, 'message' => 'Slot ID is required']);
    exit();
}

$id = $data['id'];

// Start transaction
$conn->begin_transaction();

try {
    // Check if slot exists and has no active bookings
    $query = "SELECT id FROM parking_slots WHERE id = ? AND 
              NOT EXISTS (
                  SELECT 1 FROM a_bookings 
                  WHERE slot_id = parking_slots.id 
                  AND NOW() BETWEEN CONCAT(booking_date, ' ', booking_time) 
                  AND CONCAT(booking_date, ' ', end_time)
              )";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Slot not found or has active bookings');
    }

    // Delete the slot
    $delete_slot = "DELETE FROM parking_slots WHERE id = ?";
    $stmt = $conn->prepare($delete_slot);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Parking slot deleted successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?> 