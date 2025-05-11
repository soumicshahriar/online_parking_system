<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// include the database connection file
require 'database/db.php';

// Get user's active bookings count
$user_id = $_SESSION['user_id'];

// Get user's booking statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN NOW() < CONCAT(booking_date, ' ', booking_time) THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN NOW() BETWEEN CONCAT(booking_date, ' ', booking_time) AND CONCAT(booking_date, ' ', end_time) THEN 1 ELSE 0 END) as active_count,
        SUM(CASE WHEN NOW() > CONCAT(booking_date, ' ', end_time) THEN 1 ELSE 0 END) as completed_count,
        SUM(total_cost) as total_spent
    FROM a_bookings 
    WHERE user_id = ?";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password
        $verify_query = "SELECT password FROM users WHERE id = ?";
        $verify_stmt = $conn->prepare($verify_query);
        $verify_stmt->bind_param("i", $user_id);
        $verify_stmt->execute();
        $result = $verify_stmt->get_result();
        $user = $result->fetch_assoc();
        $verify_stmt->close();

        if (password_verify($current_password, $user['password'])) {
            // Update profile
            if (!empty($new_password)) {
                if ($new_password === $confirm_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("sssi", $username, $email, $hashed_password, $user_id);
                } else {
                    $error = "New passwords do not match.";
                }
            } else {
                $update_query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ssi", $username, $email, $user_id);
            }

            if (isset($update_stmt) && $update_stmt->execute()) {
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $success = "Profile updated successfully.";
            } else {
                $error = "Error updating profile.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

// Get user details
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkEase - Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-card {
            transition: all 0.3s ease;
        }
        .profile-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php include 'navigation.php'; ?>

    <!-- Loading Spinner -->
    <div id="loading-spinner" class="loading-spinner">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
                    <p class="text-sm text-gray-500 mt-1">Manage your account information</p>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Profile Information -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <?php if (isset($error)): ?>
                        <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($success)): ?>
                        <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Current Password</label>
                            <input type="password" name="current_password" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">New Password (leave blank to keep current)</label>
                            <input type="password" name="new_password"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                            <input type="password" name="confirm_password"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="update_profile"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-2"></i>
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column - Statistics -->
            <div class="space-y-6">
                <!-- User Stats -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Account Statistics</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Total Bookings</span>
                            <span class="text-sm font-medium text-gray-900"><?php echo $stats['total_bookings']; ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Pending Bookings</span>
                            <span class="text-sm font-medium text-yellow-600"><?php echo $stats['pending_count']; ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Active Bookings</span>
                            <span class="text-sm font-medium text-green-600"><?php echo $stats['active_count']; ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Completed Bookings</span>
                            <span class="text-sm font-medium text-gray-600"><?php echo $stats['completed_count']; ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Total Spent</span>
                            <span class="text-sm font-medium text-blue-600">৳<?php echo number_format($stats['total_spent'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="index.php" class="flex items-center text-sm text-gray-600 hover:text-blue-600">
                            <i class="fas fa-car-side w-5"></i>
                            <span>Book a Parking Slot</span>
                        </a>
                        <a href="booking_slot.php" class="flex items-center text-sm text-gray-600 hover:text-blue-600">
                            <i class="fas fa-history w-5"></i>
                            <span>View Booking History</span>
                        </a>
                        <a href="logout.php" class="flex items-center text-sm text-red-600 hover:text-red-700">
                            <i class="fas fa-sign-out-alt w-5"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Show loading spinner
        function showLoading() {
            document.getElementById('loading-spinner').style.display = 'block';
        }

        // Hide loading spinner
        function hideLoading() {
            document.getElementById('loading-spinner').style.display = 'none';
        }

        // Handle form submission
        $('form').on('submit', function() {
            showLoading();
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>