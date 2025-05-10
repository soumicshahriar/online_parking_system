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

if (!isset($data['id']) || !isset($data['username']) || !isset($data['email']) || !isset($data['role'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

$id = $data['id'];
$username = $data['username'];
$email = $data['email'];
$role = $data['role'];
$password = isset($data['password']) ? $data['password'] : null;

// Start transaction
$conn->begin_transaction();

try {
    // Check if user exists
    $check_query = "SELECT id FROM users WHERE id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('User not found');
    }

    // Check if email already exists (excluding current user)
    $check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception('Email already exists');
    }

    // Update the user
    if ($password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET 
                        username = ?, 
                        email = ?, 
                        role = ?,
                        password = ?
                        WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssi", $username, $email, $role, $hashed_password, $id);
    } else {
        $update_query = "UPDATE users SET 
                        username = ?, 
                        email = ?, 
                        role = ?
                        WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssi", $username, $email, $role, $id);
    }
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?> 