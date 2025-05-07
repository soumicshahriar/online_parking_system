<?php
include 'database/db.php';



if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?page=a_user.php" );
        exit;
    } else {
        echo "Error deleting user.";
    }
} else {
    echo "No user ID provided.";
}
