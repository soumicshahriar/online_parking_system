<?php
require_once 'database/db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Get all parking slots with their current booking status
$query = "SELECT 
    p.*,
    CASE
        WHEN EXISTS (
            SELECT 1 FROM a_bookings b 
            WHERE b.slot_id = p.id 
            AND NOW() BETWEEN CONCAT(b.booking_date, ' ', b.booking_time) 
            AND CONCAT(b.booking_date, ' ', b.end_time)
        ) THEN 'Occupied'
        ELSE 'Available'
    END as current_status
FROM parking_slots p 
ORDER BY p.id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Slots Management - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Parking Slots Management</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSlotModal">
                    <i class="fas fa-plus"></i> Add New Slot
                </button>
            </div>

            <!-- Parking Slots Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Slot Number</th>
                                    <th>Location</th>
                                    <th>Vehicle Type</th>
                                    <th>Cost/Hour</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($slot = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $slot['id']; ?></td>
                                    <td><?php echo htmlspecialchars($slot['slot_number']); ?></td>
                                    <td><?php echo htmlspecialchars($slot['location']); ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($slot['vehicle_type'])); ?></td>
                                    <td>৳<?php echo number_format($slot['cost_per_hour'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $slot['current_status'] === 'Available' ? 'success' : 'danger'; ?>">
                                            <?php echo $slot['current_status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-slot" 
                                                data-id="<?php echo $slot['id']; ?>"
                                                data-slot="<?php echo htmlspecialchars($slot['slot_number']); ?>"
                                                data-location="<?php echo htmlspecialchars($slot['location']); ?>"
                                                data-type="<?php echo htmlspecialchars($slot['vehicle_type']); ?>"
                                                data-cost="<?php echo $slot['cost_per_hour']; ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editSlotModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if($slot['current_status'] === 'Available'): ?>
                                        <button class="btn btn-sm btn-danger delete-slot" data-id="<?php echo $slot['id']; ?>">
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

    <!-- Edit Slot Modal -->
    <div class="modal fade" id="editSlotModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Parking Slot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editSlotForm">
                        <input type="hidden" id="edit_slot_id" name="id">
                        <div class="mb-3">
                            <label for="edit_slot_number" class="form-label">Slot Number</label>
                            <input type="text" class="form-control" id="edit_slot_number" name="slot_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="edit_location" name="location" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_vehicle_type" class="form-label">Vehicle Type</label>
                            <select class="form-select" id="edit_vehicle_type" name="vehicle_type" required>
                                <option value="Car">Car</option>
                                <option value="Motorcycle">Motorcycle</option>
                                <option value="Truck">Truck</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_cost_per_hour" class="form-label">Cost per Hour</label>
                            <input type="number" class="form-control" id="edit_cost_per_hour" name="cost_per_hour" step="0.01" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveEditSlot">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Slot Modal -->
    <div class="modal fade" id="addSlotModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Parking Slot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addSlotForm">
                        <div class="mb-3">
                            <label class="form-label">Slot Number</label>
                            <input type="text" class="form-control" name="slot_number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <select class="form-select" name="location" required>
                                <option value="Bashundhara Shopping Mall">Bashundhara Shopping Mall</option>
                                <option value="Jamuna Future Park">Jamuna Future Park</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vehicle Type</label>
                            <select class="form-select" name="vehicle_type" required>
                                <option value="Car">Car</option>
                                <option value="Motorcycle">Motorcycle</option>
                                <option value="Truck">Truck</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cost per Hour</label>
                            <input type="number" class="form-control" name="cost_per_hour" step="0.01" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Slot</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit Slot
        document.querySelectorAll('.edit-slot').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const slot = this.dataset.slot;
                const location = this.dataset.location;
                const type = this.dataset.type;
                const cost = this.dataset.cost;

                document.getElementById('edit_slot_id').value = id;
                document.getElementById('edit_slot_number').value = slot;
                document.getElementById('edit_location').value = location;
                document.getElementById('edit_vehicle_type').value = type;
                document.getElementById('edit_cost_per_hour').value = cost;
            });
        });

        // Save Edit Slot
        document.getElementById('saveEditSlot').addEventListener('click', function() {
            const form = document.getElementById('editSlotForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch('update_slot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

        // Delete Slot
        document.querySelectorAll('.delete-slot').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this parking slot?')) {
                    const id = this.dataset.id;
                    fetch('delete_slot.php', {
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

        // Add Slot Form
        document.getElementById('addSlotForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('add_slot.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });
    </script>
</body>
</html> 