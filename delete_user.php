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
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

$id = $data['id'];

// Start transaction
$conn->begin_transaction();

try {
    // Check if user exists and is not the current admin
    if ($id == $_SESSION['user_id']) {
        throw new Exception('Cannot delete your own account');
    }

    // Check if user has any active bookings
    $query = "SELECT id FROM users u 
              WHERE u.id = ? AND 
              EXISTS (
                  SELECT 1 FROM a_bookings b 
                  WHERE b.user_id = u.id 
                  AND NOW() BETWEEN CONCAT(b.booking_date, ' ', b.booking_time) 
                  AND CONCAT(b.booking_date, ' ', b.end_time)
              )";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception('Cannot delete user with active bookings');
    }

    // Delete the user
    $delete_user = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($delete_user);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
