<?php
session_start(); // Start the session to access user data

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "online_parking";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

$email = $data['email'];
$password = $data['password'];

// Validate input
if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit();
}

// Hash the password if it's provided
if (!empty($password)) {
    $password = password_hash($password, PASSWORD_DEFAULT);
}

// Update the user's email and/or password in the database
$sql = "UPDATE users SET email = ?";
if (!empty($password)) {
    $sql .= ", password = ?";
}
$sql .= " WHERE id = ?"; // Use the user ID from the session

$stmt = $conn->prepare($sql);

if (!empty($password)) {
    $stmt->bind_param("ssi", $email, $password, $_SESSION['user_id']);
} else {
    $stmt->bind_param("si", $email, $_SESSION['user_id']);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
}

$stmt->close();
$conn->close();
?>