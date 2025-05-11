<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please log in to view booking details.";
    exit();
}
if (isset($_POST["username"])) {
    $_SESSION['username'] = $_POST['username'];
}
if (isset($_POST["email"])) {
    $_SESSION['email'] = $_POST['email'];
}

// include the database connection file
require 'database/db.php';

// Get user's active bookings count
$user_id = $_SESSION['user_id'];

// Get counts for all booking statuses
$status_counts_query = "
    SELECT 
        SUM(CASE WHEN NOW() < CONCAT(booking_date, ' ', booking_time) THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN NOW() BETWEEN CONCAT(booking_date, ' ', booking_time) AND CONCAT(booking_date, ' ', end_time) THEN 1 ELSE 0 END) as active_count,
        SUM(CASE WHEN NOW() > CONCAT(booking_date, ' ', end_time) THEN 1 ELSE 0 END) as completed_count
    FROM a_bookings 
    WHERE user_id = ?";
$status_stmt = $conn->prepare($status_counts_query);
$status_stmt->bind_param("i", $user_id);
$status_stmt->execute();
$status_counts = $status_stmt->get_result()->fetch_assoc();
$status_stmt->close();

$active_bookings = $status_counts['active_count'];
$pending_bookings = $status_counts['pending_count'];
$completed_bookings = $status_counts['completed_count'];

// Handle bulk delete operation
if (isset($_POST['delete_selected'])) {
    if (!empty($_POST['selected_bookings'])) {
        $selected_bookings = $_POST['selected_bookings'];
        $placeholders = implode(',', array_fill(0, count($selected_bookings), '?')); // Create placeholders for prepared statement
        $delete_query = "DELETE FROM a_bookings WHERE id IN ($placeholders) AND user_id = ?";
        $stmt = $conn->prepare($delete_query);

        // Bind parameters dynamically
        $types = str_repeat('i', count($selected_bookings)) . 'i'; // e.g., "iii" for 3 selected items
        $params = array_merge($selected_bookings, [$_SESSION['user_id']]); // Combine selected IDs and user ID
        $stmt->bind_param($types, ...$params); // Bind parameters

        if ($stmt->execute()) {
            echo "<script>alert('Selected bookings deleted successfully.');</script>";
        } else {
            echo "<script>alert('Error deleting selected bookings.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('No bookings selected.');</script>";
    }
}


// Handle delete operation
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM a_bookings WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $delete_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        echo "<script>alert('Booking deleted successfully.');</script>";
    } else {
        echo "<script>alert('Error deleting booking.');</script>";
    }
    $stmt->close();
}

// Handle CSV download
if (isset($_GET['download_csv'])) {
    $status_filter = $_GET['status_filter'] ?? '';
    $query = "
        SELECT 
            id AS booking_id,
            slot_id,
            vehicle_number,
            booking_date,
            booking_time,
            duration,
            end_time,
            cost_per_hour,
            total_cost,
            bkash_number,
            bkash_pin,
            CASE
                WHEN NOW() < CONCAT(booking_date, ' ', booking_time) THEN 'Pending'
                WHEN NOW() BETWEEN CONCAT(booking_date, ' ', booking_time) AND CONCAT(booking_date, ' ', end_time) THEN 'Active'
                ELSE 'Completed'
            END AS status
        FROM 
            a_bookings
        WHERE 
            user_id = ?
    ";
    if (!empty($status_filter)) {
        $query .= " HAVING status = ?";
    }
    $stmt = $conn->prepare($query);
    if (!empty($status_filter)) {
        $stmt->bind_param("is", $_SESSION['user_id'], $status_filter);
    } else {
        $stmt->bind_param("i", $_SESSION['user_id']);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Output CSV headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="bookings.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('Booking ID', 'Slot ID', 'Vehicle Number', 'Booking Date', 'Booking Time', 'Duration', 'End Time', 'Cost Per Hour', 'Total Cost', 'Bkash Number', 'Bkash PIN', 'Status'));

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Fetch booking details for the logged-in user with dynamic status calculation
$status_filter = $_GET['status_filter'] ?? '';
$query = "
    SELECT 
        id AS booking_id,
        slot_id,
        vehicle_number,
        booking_date,
        booking_time,
        duration,
        end_time,
        cost_per_hour,
        total_cost,
        bkash_number,
        bkash_pin,
        CASE
            WHEN NOW() < CONCAT(booking_date, ' ', booking_time) THEN 'Pending'
            WHEN NOW() BETWEEN CONCAT(booking_date, ' ', booking_time) AND CONCAT(booking_date, ' ', end_time) THEN 'Active'
            ELSE 'Completed'
        END AS status
    FROM 
        a_bookings
    WHERE 
        user_id = ?
";
if (!empty($status_filter)) {
    $query .= " HAVING status = ?";
}
$stmt = $conn->prepare($query);
if (!empty($status_filter)) {
    $stmt->bind_param("is", $_SESSION['user_id'], $status_filter);
} else {
    $stmt->bind_param("i", $_SESSION['user_id']);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkEase - Booking History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .booking-card {
            transition: all 0.3s ease;
        }
        .booking-card:hover {
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
                    <h1 class="text-2xl font-bold text-gray-900">Booking History</h1>
                    <p class="text-sm text-gray-500 mt-1">View and manage your parking bookings</p>
                </div>
                <div class="flex items-center gap-4">
                    <form method="GET" action="" class="flex items-center gap-2">
                        <select name="status_filter" id="status_filter"
                            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Bookings</option>
                            <option value="Pending" <?php echo ($status_filter == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Active" <?php echo ($status_filter == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Completed" <?php echo ($status_filter == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-filter mr-2"></i>
                            Filter
                        </button>
                    </form>
                    <form method="GET" action="">
                        <input type="hidden" name="status_filter" value="<?php echo $status_filter; ?>">
                        <button type="submit" name="download_csv"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <i class="fas fa-download mr-2"></i>
                            Download CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- User Info Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900"><?php echo $_SESSION['username']; ?></h2>
                    <p class="text-sm text-gray-500"><?php echo $_SESSION['email']; ?></p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        <i class="fas fa-clock mr-1"></i>
                        Pending: <?php echo $pending_bookings; ?>
                    </span>
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>
                        Active: <?php echo $active_bookings; ?>
                    </span>
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        <i class="fas fa-check-double mr-1"></i>
                        Completed: <?php echo $completed_bookings; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Booking Status Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Pending Bookings -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Pending Bookings</h3>
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        <?php echo $pending_bookings; ?>
                    </span>
                </div>
                <p class="text-sm text-gray-500">Bookings that are scheduled for future dates</p>
            </div>

            <!-- Active Bookings -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Active Bookings</h3>
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <?php echo $active_bookings; ?>
                    </span>
                </div>
                <p class="text-sm text-gray-500">Currently ongoing parking sessions</p>
            </div>

            <!-- Completed Bookings -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Completed Bookings</h3>
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        <?php echo $completed_bookings; ?>
                    </span>
                </div>
                <p class="text-sm text-gray-500">Past parking sessions</p>
            </div>
        </div>

        <!-- Bulk Delete Form -->
        <form method="POST" action="" class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-4">
                    <button type="submit" name="delete_selected"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Delete Selected
                    </button>
                    <span class="text-sm text-gray-500">Select bookings to delete</span>
                </div>
            </div>

            <!-- Bookings Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): 
                        $status_class = '';
                        $status_icon = '';
                        $status_bg = '';
                        switch ($row['status']) {
                            case 'Pending':
                                $status_class = 'bg-yellow-100 text-yellow-800';
                                $status_icon = 'fa-clock';
                                $status_bg = 'bg-yellow-50';
                                break;
                            case 'Active':
                                $status_class = 'bg-green-100 text-green-800';
                                $status_icon = 'fa-check-circle';
                                $status_bg = 'bg-green-50';
                                break;
                            case 'Completed':
                                $status_class = 'bg-gray-100 text-gray-800';
                                $status_icon = 'fa-check-double';
                                $status_bg = 'bg-gray-50';
                                break;
                        }
                    ?>
                        <div class="booking-card bg-white rounded-lg shadow-sm p-6 <?php echo $status_bg; ?>">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Booking #<?php echo $row['booking_id']; ?></h3>
                                    <p class="text-sm text-gray-500">Slot #<?php echo $row['slot_id']; ?></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $status_class; ?>">
                                        <i class="fas <?php echo $status_icon; ?> mr-1"></i>
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                    <input type="checkbox" name="selected_bookings[]" value="<?php echo $row['booking_id']; ?>"
                                        class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-car-side w-5"></i>
                                    <span><?php echo htmlspecialchars($row['vehicle_number']); ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-calendar w-5"></i>
                                    <span><?php echo htmlspecialchars($row['booking_date']); ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-clock w-5"></i>
                                    <span><?php echo htmlspecialchars($row['booking_time']); ?> - <?php echo htmlspecialchars($row['end_time']); ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-hourglass-half w-5"></i>
                                    <span><?php echo htmlspecialchars($row['duration']); ?> hours</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-tag w-5"></i>
                                    <span>৳<?php echo number_format($row['cost_per_hour'], 2); ?> per hour</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-money-bill-wave w-5"></i>
                                    <span>Total: ৳<?php echo number_format($row['total_cost'], 2); ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-mobile-alt w-5"></i>
                                    <span><?php echo htmlspecialchars($row['bkash_number']); ?></span>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end">
                                <a href="?delete_id=<?php echo $row['booking_id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this booking?')"
                                   class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full">
                        <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                            <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-600">No bookings found.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </form>
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

        // Handle form submissions
        $('form').on('submit', function() {
            showLoading();
        });

        // Handle delete confirmation
        $('a[href^="?delete_id="]').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this booking?')) {
                e.preventDefault();
            } else {
                showLoading();
            }
        });

        // Handle bulk delete confirmation
        $('form').on('submit', function(e) {
            if ($(this).find('input[name="selected_bookings[]"]:checked').length > 0) {
                if (!confirm('Are you sure you want to delete the selected bookings?')) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>