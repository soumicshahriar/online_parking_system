<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please log in to book a slot.";
    exit();
}

// include the database connection file
require 'database/db.php';

// Get form data
$user_id = $_SESSION['user_id'];
$selected_slots = json_decode($_POST['selected_slots'], true);
$vehicle_numbers = $_POST['vehicle_numbers'];
$booking_date = $_POST['booking_date'];
$booking_time = $_POST['booking_time'];
$duration = $_POST['duration'];
$bkash_number = $_POST['bkash_number'];
$bkash_pin = $_POST['bkash_pin'];

// Validate required fields
if (empty($selected_slots) || empty($vehicle_numbers) || empty($booking_date) || empty($booking_time) || empty($duration)) {
    echo "All fields are required.";
    exit();
}

// Validate number of vehicle numbers matches selected slots
if (count($vehicle_numbers) !== count($selected_slots)) {
    echo "Please provide vehicle numbers for all selected slots.";
    exit();
}

// Validate vehicle number format
function validateVehicleNumber($vehicle_number, $vehicle_type) {
    // Remove spaces and convert to uppercase
    $vehicle_number = strtoupper(str_replace(' ', '', $vehicle_number));
    
    if ($vehicle_type === 'car') {
        // Car format: ABC-1234 or ABC-123
        return preg_match('/^[A-Z]{3}-[0-9]{3,4}$/', $vehicle_number);
    } else if ($vehicle_type === 'bike') {
        // Bike format: ABC-12345 or ABC-1234
        return preg_match('/^[A-Z]{3}-[0-9]{4,5}$/', $vehicle_number);
    }
    return false;
}

// Calculate end time
$end_time = date('H:i', strtotime("+$duration hours", strtotime($booking_time)));

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if user has reached booking limit
    $check_query = "SELECT COUNT(*) as active_count FROM a_bookings 
                   WHERE user_id = ? 
                   AND booking_date = ?
                   AND NOW() BETWEEN CONCAT(booking_date, ' ', booking_time) 
                   AND CONCAT(booking_date, ' ', end_time)";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("is", $user_id, $booking_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $active_bookings = $result->fetch_assoc()['active_count'];

    if ($active_bookings + count($selected_slots) > 3) {
        throw new Exception("You can only have 3 active bookings for the same date.");
    }

    // Check if slots are available and validate vehicle numbers
    foreach ($selected_slots as $index => $slot) {
        $check_slot_query = "SELECT * FROM parking_slots WHERE id = ?";
        $stmt = $conn->prepare($check_slot_query);
        $stmt->bind_param("i", $slot['id']);
        $stmt->execute();
        $slot_result = $stmt->get_result();
        $slot_data = $slot_result->fetch_assoc();
        
        if ($slot_result->num_rows === 0) {
            throw new Exception("One or more selected slots are no longer available.");
        }

        // Validate vehicle number format
        if (!validateVehicleNumber($vehicle_numbers[$index], $slot_data['vehicle_type'])) {
            throw new Exception("Invalid vehicle number format for slot " . $slot_data['slot_number'] . 
                              ". Please use format: " . ($slot_data['vehicle_type'] === 'car' ? 'ABC-1234' : 'ABC-12345'));
        }

        // Check if slot is already booked for the selected time
        $check_booking_query = "SELECT * FROM a_bookings 
                              WHERE slot_id = ? 
                              AND booking_date = ? 
                              AND (
                                  (? < end_time AND ? > booking_time)
                              )";
        $stmt = $conn->prepare($check_booking_query);
        $stmt->bind_param("isss", $slot['id'], $booking_date, $booking_time, $end_time);
        $stmt->execute();
        $booking_result = $stmt->get_result();
        
        if ($booking_result->num_rows > 0) {
            throw new Exception("One or more selected slots are already booked for the selected time.");
        }
    }

    // Insert bookings for each selected slot
    $insert_query = "INSERT INTO a_bookings (user_id, slot_id, vehicle_number, booking_date, booking_time, end_time, duration, cost_per_hour, total_cost, bkash_number, bkash_pin) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);

    foreach ($selected_slots as $index => $slot) {
        $total_cost = $slot['cost'] * $duration;
        $vehicle_number = strtoupper(str_replace(' ', '', $vehicle_numbers[$index]));
        
        $stmt->bind_param("iisssssdsss", 
            $user_id, 
            $slot['id'], 
            $vehicle_number, 
            $booking_date, 
            $booking_time, 
            $end_time, 
            $duration,
            $slot['cost'],
            $total_cost, 
            $bkash_number, 
            $bkash_pin
        );
        $stmt->execute();
    }

    // Commit transaction
    $conn->commit();
    echo "Successfully booked " . count($selected_slots) . " slot(s)!";

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo $e->getMessage();
}

$conn->close();
?>