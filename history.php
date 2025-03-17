<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (isset($_POST["username"])) {
    $_SESSION['username'] = $_POST['username'];
}
if (isset($_POST["email"])) {
    $_SESSION['email'] = $_POST['email'];
}

// Database connection
$host = 'localhost';
$db   = 'online_parking';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch booking history for the logged-in user
$user_id = $_SESSION['user_id'];
$query = "SELECT bookings.*, parking_slots.slot_number, parking_slots.location, users.username, users.email 
          FROM bookings 
          JOIN parking_slots ON bookings.slot_id = parking_slots.id 
          JOIN users ON bookings.user_id = users.id 
          WHERE bookings.user_id = $user_id 
          ORDER BY bookings.booking_date DESC, bookings.booking_time DESC";
$result = $conn->query($query);

$bookings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History - ParkEase</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- daisy UI -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <div class="navbar bg-base-100 shadow-sm sticky top-0">
        <div class="navbar-start">
            <div class="dropdown">
                <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h8m-8 6h16" />
                    </svg>
                </div>
                <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
                    <li><a href="index.php" class="bg-blue-600 text-white px-4 py-2 mb-2 rounded-lg hover:bg-blue-700">Home</a></li>
                    <li><a href="book_slot.php" class="bg-green-600 text-white px-4 py-2 mb-2 rounded-lg hover:bg-green-700">Book Slot</a></li>
                    <li><a href="history.php" class="bg-yellow-600 text-white px-4 py-2 mb-2 rounded-lg hover:bg-yellow-700">History</a></li>
                    <li><a href="profile.php" class="bg-purple-600 text-white px-4 py-2 mb-2 rounded-lg hover:bg-purple-700">Profile</a></li>
                </ul>
            </div>
            <a class="btn btn-ghost text-xl">ParkEase</a>
        </div>

        <div class="navbar-end">
            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal">
                    <li><a href="index.php" class="bg-blue-600 text-white px-4 py-2 mr-2 rounded-lg hover:bg-blue-700">Home</a></li>
                    <li><a href="#slot-list" class="bg-green-600 text-white px-4 py-2 mr-2 rounded-lg hover:bg-green-700">Book Slot</a></li>
                    <li><a href="history.php" class="bg-yellow-600 text-white px-4 py-2 mr-2 rounded-lg hover:bg-yellow-700">History</a></li>
                    <li><a href="profile.php" class="bg-purple-600 text-white px-4 py-2 mr-2 rounded-lg hover:bg-purple-700">Profile</a></li>
                </ul>
            </div>
            <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Logout</a>
        </div>
    </div>

    <!-- Booking History Section -->
    <div class="max-w-6xl mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold mb-6">Booking History</h2>
        

        <?php if (empty($bookings)): ?>
            <p class="text-gray-600">No booking history found.</p>
        <?php else: ?>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <table class="w-full">
                <!-- <h1><?php echo $_SESSION['user_email']; ?></h1> -->
               <div class="w-3/5 mx-auto bg-gray-100 p-2 rounded-lg mb-4">
                    <h1 class="text-center">User_Name: <?php echo $_SESSION['username']; ?></h1>
                    <h1 class="text-center">Email: <?php echo $_SESSION['email']; ?></h1>
               </div>
                
               

                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Slot Number</th>
                            <th class="text-left py-2">Location</th>
                            <th class="text-left py-2">Booking Date</th>
                            <th class="text-left py-2">Booking Time</th>
                            <th class="text-left py-2">Duration (hours)</th>
                            <th class="text-left py-2">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr class="border-b">
                                <td class="py-2"><?php echo $booking['slot_number']; ?></td>
                                <td class="py-2"><?php echo $booking['location']; ?></td>
                                <td class="py-2"><?php echo $booking['booking_date']; ?></td>
                                <td class="py-2"><?php echo $booking['booking_time']; ?></td>
                                <td class="py-2"><?php echo $booking['duration']; ?></td>
                                <td class="py-2">$<?php echo number_format($booking['total_cost'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>