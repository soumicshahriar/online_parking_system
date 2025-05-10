<?php
require_once 'database/db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Get all bookings with user and slot information
$query = "SELECT 
    a.*,
    u.username,
    p.slot_number,
    p.location,
    CASE
        WHEN NOW() < CONCAT(a.booking_date, ' ', a.booking_time) THEN 'Pending'
        WHEN NOW() BETWEEN CONCAT(a.booking_date, ' ', a.booking_time) 
            AND CONCAT(a.booking_date, ' ', a.end_time) THEN 'Active'
        ELSE 'Completed'
    END as current_status
FROM a_bookings a 
JOIN users u ON a.user_id = u.id 
JOIN parking_slots p ON a.slot_id = p.id 
ORDER BY a.booking_date DESC, a.booking_time DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Management - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Bookings Management</h2>
                <div>
                    <a href="export_report.php" class="btn btn-success me-2">
                        <i class="fas fa-download"></i> Export Report
                    </a>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Slot</th>
                                    <th>Location</th>
                                    <th>Vehicle</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Duration</th>
                                    <th>Cost</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($booking = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $booking['id']; ?></td>
                                    <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['slot_number']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['location']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['vehicle_number']); ?></td>
                                    <td><?php echo $booking['booking_date']; ?></td>
                                    <td><?php echo $booking['booking_time']; ?></td>
                                    <td><?php echo $booking['duration']; ?> hours</td>
                                    <td>৳<?php echo number_format($booking['total_cost'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $booking['current_status'] === 'Pending' ? 'warning' : 
                                                ($booking['current_status'] === 'Active' ? 'success' : 'secondary'); 
                                        ?>">
                                            <?php echo $booking['current_status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-booking" 
                                                data-id="<?php echo $booking['id']; ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewBookingModal">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if($booking['current_status'] === 'Pending'): ?>
                                        <button class="btn btn-sm btn-danger cancel-booking" data-id="<?php echo $booking['id']; ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if($booking['current_status'] === 'Completed'): ?>
                                        <button class="btn btn-sm btn-danger delete-booking" data-id="<?php echo $booking['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Booking Modal -->
    <div class="modal fade" id="viewBookingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Booking ID:</strong>
                            <span id="view_booking_id"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>User:</strong>
                            <span id="view_username"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Slot Number:</strong>
                            <span id="view_slot"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Location:</strong>
                            <span id="view_location"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Vehicle Number:</strong>
                            <span id="view_vehicle"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <span id="view_status"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date:</strong>
                            <span id="view_date"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Time:</strong>
                            <span id="view_time"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Duration:</strong>
                            <span id="view_duration"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Total Cost:</strong>
                            <span id="view_cost"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // View Booking
        document.querySelectorAll('.view-booking').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                fetch('get_booking.php?id=' + id)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const booking = data.booking;
                            document.getElementById('view_booking_id').textContent = booking.id;
                            document.getElementById('view_username').textContent = booking.username;
                            document.getElementById('view_slot').textContent = booking.slot_number;
                            document.getElementById('view_location').textContent = booking.location;
                            document.getElementById('view_vehicle').textContent = booking.vehicle_number;
                            document.getElementById('view_status').textContent = booking.current_status;
                            document.getElementById('view_date').textContent = booking.booking_date;
                            document.getElementById('view_time').textContent = booking.booking_time;
                            document.getElementById('view_duration').textContent = booking.duration + ' hours';
                            document.getElementById('view_cost').textContent = '৳' + booking.total_cost;
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            });
        });

        // Cancel Booking
        document.querySelectorAll('.cancel-booking').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to cancel this booking?')) {
                    const id = this.dataset.id;
                    fetch('cancel_booking.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
                }
            });
        });

        // Delete Booking
        document.querySelectorAll('.delete-booking').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this completed booking?')) {
                    const id = this.dataset.id;
                    fetch('delete_booking.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
                }
            });
        });
    </script>
</body>
</html> 