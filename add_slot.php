<?php
require_once 'database/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slot_number = $_POST['slot_number'];
    $location = $_POST['location'];
    $vehicle_type = $_POST['vehicle_type'];
    $cost_per_hour = $_POST['cost_per_hour'];

    // Validate input
    if (empty($slot_number) || empty($location) || empty($vehicle_type) || empty($cost_per_hour)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    // Check if slot number already exists
    $check_query = "SELECT id FROM parking_slots WHERE slot_number = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $slot_number);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Slot number already exists']);
        exit;
    }

    // Insert new slot
    $insert_query = "INSERT INTO parking_slots (slot_number, location, vehicle_type, cost_per_hour, is_available) 
                    VALUES (?, ?, ?, ?, 1)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("sssd", $slot_number, $location, $vehicle_type, $cost_per_hour);

    if ($insert_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Slot added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding slot: ' . $conn->error]);
    }

    $insert_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?> 