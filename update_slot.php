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

if (!isset($data['id']) || !isset($data['slot_number']) || !isset($data['location']) || 
    !isset($data['vehicle_type']) || !isset($data['cost_per_hour'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

$id = $data['id'];
$slot_number = $data['slot_number'];
$location = $data['location'];
$vehicle_type = $data['vehicle_type'];
$cost_per_hour = $data['cost_per_hour'];

// Start transaction
$conn->begin_transaction();

try {
    // Check if slot exists
    $check_query = "SELECT id FROM parking_slots WHERE id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Parking slot not found');
    }

    // Check if slot number already exists (excluding current slot)
    $check_duplicate = "SELECT id FROM parking_slots WHERE slot_number = ? AND id != ?";
    $stmt = $conn->prepare($check_duplicate);
    $stmt->bind_param("si", $slot_number, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception('Slot number already exists');
    }

    // Update the slot
    $update_query = "UPDATE parking_slots SET 
                    slot_number = ?, 
                    location = ?, 
                    vehicle_type = ?, 
                    cost_per_hour = ? 
                    WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssdi", $slot_number, $location, $vehicle_type, $cost_per_hour, $id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Parking slot updated successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?> 