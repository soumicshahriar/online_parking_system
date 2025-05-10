<?php
require_once 'database/db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Get total users
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'User'";
$result = $conn->query($query);
$total_users = $result->fetch_assoc()['total'];

// Get total parking slots
$query = "SELECT COUNT(*) as total FROM parking_slots";
$result = $conn->query($query);
$total_slots = $result->fetch_assoc()['total'];

// Get active bookings count
$query = "SELECT COUNT(*) as total FROM a_bookings 
          WHERE NOW() BETWEEN CONCAT(booking_date, ' ', booking_time) 
          AND CONCAT(booking_date, ' ', end_time)";
$result = $conn->query($query);
$active_bookings = $result->fetch_assoc()['total'];

// Get available slots
$available_slots = $total_slots - $active_bookings;

// Get recent active bookings
$query = "SELECT b.*, u.username, p.slot_number, p.location 
          FROM a_bookings b 
          JOIN users u ON b.user_id = u.id 
          JOIN parking_slots p ON b.slot_id = p.id 
          WHERE NOW() BETWEEN CONCAT(b.booking_date, ' ', b.booking_time) 
          AND CONCAT(b.booking_date, ' ', b.end_time)
          ORDER BY b.booking_date DESC, b.booking_time DESC 
          LIMIT 5";
$recent_bookings = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Parking Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Dashboard</h2>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <h2 class="card-text"><?php echo $total_users; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Parking Slots</h5>
                            <h2 class="card-text"><?php echo $total_slots; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Active Bookings</h5>
                            <h2 class="card-text"><?php echo $active_bookings; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Available Slots</h5>
                            <h2 class="card-text"><?php echo $available_slots; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <a href="parking_slots.php" class="btn btn-primary me-2">
                                <i class="fas fa-parking"></i> Manage Parking Slots
                            </a>
                            <a href="users.php" class="btn btn-success me-2">
                                <i class="fas fa-users"></i> Manage Users
                            </a>
                            <a href="bookings.php" class="btn btn-info me-2">
                                <i class="fas fa-calendar-check"></i> View All Bookings
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Active Bookings -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Recent Active Bookings</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Slot</th>
                                            <th>Location</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Duration</th>
                                            <th>Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($booking = $recent_bookings->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['slot_number']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['location']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                            <td><?php echo date('h:i A', strtotime($booking['booking_time'])); ?></td>
                                            <td><?php echo $booking['duration']; ?> hours</td>
                                            <td>৳<?php echo number_format($booking['total_cost'], 2); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 