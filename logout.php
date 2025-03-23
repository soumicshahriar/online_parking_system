<?php
session_start();

// Destroy session but keep email & password
session_unset(); // Remove all session variables
session_destroy(); // Destroy session

header("Location: login.php"); // Redirect to login
exit();
?>
