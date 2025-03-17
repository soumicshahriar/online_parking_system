<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$host = 'localhost';
$db = 'online_parking';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch available parking slots for the selected area, date, time, duration, and vehicle type
$selected_area = isset($_GET['area']) ? $_GET['area'] : 'Bashundhara Shopping Mall';
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$selected_time = isset($_GET['time']) ? $_GET['time'] : date('H:i');
$selected_duration = isset($_GET['duration']) ? $_GET['duration'] : 1;
$selected_vehicle_type = isset($_GET['vehicle_type']) ? $_GET['vehicle_type'] : 'car';

// Calculate end time
$end_time = date('H:i', strtotime("+$selected_duration hours", strtotime($selected_time)));

// Fetch available slots
$slots = [];
$query = "SELECT * FROM parking_slots WHERE location = '$selected_area' AND vehicle_type = '$selected_vehicle_type' AND id NOT IN (
    SELECT slot_id FROM bookings 
    WHERE booking_date = '$selected_date' 
    AND (
        ('$selected_time' < end_time AND '$end_time' > booking_time) or
        ('$selected_time' > end_time AND '$end_time' < booking_time)
    )
)";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error); // Debug query error
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $slots[] = $row;
    }
} else {
    echo "No available slots found in $selected_area for the selected date, time, and vehicle type."; // Debug no slots found
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- daisy UI -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>


</head>

<body class="bg-gray-100 relative">
    <!-- Navbar -->
    <!-- <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="text-2xl font-bold text-blue-600">ParkEase</div>
                <div>
                    <a href="index.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Home</a>
                    <a href="book_slot.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Book Slot</a>
                    <a href="history.php" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700">History</a>
                    <a href="profile.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">Profile</a>
                    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Logout</a>
                </div>
            </div>
        </div>


    </nav> -->


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
                    <li><a href="book_slot.php" class="bg-green-600 text-white px-4 py-2 mr-2 rounded-lg hover:bg-green-700">Book Slot</a></li>
                    <li><a href="history.php" class="bg-yellow-600 text-white px-4 py-2 mr-2 rounded-lg hover:bg-yellow-700">History</a></li>
                    <li><a href="profile.php" class="bg-purple-600 text-white px-4 py-2 mr-2 rounded-lg hover:bg-purple-700">Profile</a></li>
                </ul>
            </div>
            <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Logout</a>
        </div>
    </div>

    <!-- Dashboard -->
    <div class="max-w-6xl mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold mb-6">Available Parking Slots</h2>

        <!-- Area Selection Dropdown -->
        <div class="mb-6">
            <label class="block text-gray-700">Select Area</label>
            <select id="area-select"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="Bashundhara Shopping Mall">Bashundhara Shopping Mall</option>
                <option value="Jamuna Future Park">Jamuna Future Park</option>
            </select>
        </div>

        <!-- Vehicle Type Dropdown -->
        <div class="mb-6">
            <label class="block text-gray-700">Select Vehicle Type</label>
            <select id="vehicle-type-select"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="car">Car</option>
                <option value="bike">Bike</option>
            </select>
        </div>

        <!-- Date and Time Selection -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-gray-700">Booking Date</label>
                <input type="date" id="date-select" value="<?php echo $selected_date; ?>"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700">Booking Time</label>
                <input type="time" id="time-select" value="<?php echo $selected_time; ?>"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700">Duration (hours)</label>
                <input type="number" id="duration-select" value="<?php echo $selected_duration; ?>"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Slot List -->
        <div id="slot-list" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php if (empty($slots)): ?>
                <p class="text-gray-600">No parking slots available at the moment.</p>
            <?php else: ?>
                <?php foreach ($slots as $slot): ?>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-bold mb-2">Slot <?php echo $slot['slot_number']; ?></h3>
                        <p class="text-gray-600 mb-4">Location: <?php echo $slot['location']; ?></p>
                        <p class="text-gray-600 mb-4">Type: <?php echo ucfirst($slot['vehicle_type']); ?></p>
                        <p class="text-gray-600 mb-4">Cost: $<?php echo $slot['cost_per_hour']; ?> per hour</p>
                        <button onclick="bookSlot(<?php echo $slot['id']; ?>, <?php echo $slot['cost_per_hour']; ?>)"
                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                            Book Now
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Booking Modal -->
    <div id="booking-modal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center overflow-y-scroll">
        <div class="bg-white p-10 rounded-lg w-96 ">
            <h2 class="text-2xl font-bold mb-6">Book Parking Slot</h2>
            <form id="booking-form">
                <input type="hidden" id="slot-id" name="slot_id">
                <input type="hidden" id="cost-per-hour" name="cost_per_hour">
                <div class="mb-4">
                    <label class="block text-gray-700">Vehicle Number</label>
                    <input type="text" name="vehicle_number" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Booking Date</label>
                    <input type="date" id="popup-date" name="booking_date" readonly
                        class="w-full px-4 py-2 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Booking Time</label>
                    <input type="time" id="popup-time" name="booking_time" readonly
                        class="w-full px-4 py-2 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Duration (hours)</label>
                    <input type="number" id="popup-duration" name="duration" readonly
                        class="w-full px-4 py-2 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Total Cost</label>
                    <input type="text" id="total-cost" readonly
                        class="w-full px-4 py-2 border rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <!-- bKash Payment Section -->
                <div class="mb-4">
                    <label class="block text-gray-700">bKash Number</label>
                    <input type="text" id="bkash-number" name="bkash_number" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">bKash PIN</label>
                    <input type="password" id="bkash-pin" name="bkash_pin" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                    Confirm Booking
                </button>
            </form>
            <button onclick="closeModal()"
                class="w-full bg-gray-600 text-white py-2 px-4 rounded-lg mt-4 hover:bg-gray-700">
                Cancel
            </button>
        </div>
    </div>

    <script>
        // Open booking modal and populate fields
        function bookSlot(slotId, costPerHour) {
            const selectedDate = document.getElementById('date-select').value;
            const selectedTime = document.getElementById('time-select').value;
            const selectedDuration = document.getElementById('duration-select').value;

            document.getElementById('slot-id').value = slotId;
            document.getElementById('cost-per-hour').value = costPerHour;
            document.getElementById('popup-date').value = selectedDate;
            document.getElementById('popup-time').value = selectedTime;
            document.getElementById('popup-duration').value = selectedDuration;

            // Calculate total cost
            const totalCost = costPerHour * selectedDuration;
            document.getElementById('total-cost').value = `$${totalCost.toFixed(2)}`;

            document.getElementById('booking-modal').classList.remove('hidden');
        }

        // Close booking modal
        function closeModal() {
            document.getElementById('booking-modal').classList.add('hidden');
        }

        // Handle booking form submission
        $('#booking-form').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            // Simulate bKash payment
            const bkashNumber = $('#bkash-number').val();
            const bkashPin = $('#bkash-pin').val();

            if (!bkashNumber || !bkashPin) {
                alert('Please enter your bKash number and PIN.');
                return;
            }

            // Simulate payment success
            alert('Payment successful! Confirming your booking...');

            $.ajax({
                url: 'book_slot.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    alert(response);
                    closeModal();
                    location.reload(); // Refresh the page to update slot availability
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        });

        // Reload slots when area, vehicle type, date, time, or duration is changed
        $('#area-select, #vehicle-type-select, #date-select, #time-select, #duration-select').change(function() {
            const selectedArea = $('#area-select').val();
            const selectedVehicleType = $('#vehicle-type-select').val();
            const selectedDate = $('#date-select').val();
            const selectedTime = $('#time-select').val();
            const selectedDuration = $('#duration-select').val();
            window.location.href = `index.php?area=${selectedArea}&vehicle_type=${selectedVehicleType}&date=${selectedDate}&time=${selectedTime}&duration=${selectedDuration}`;
        });

        // Set the selected area, vehicle type, date, time, and duration in the inputs
        const urlParams = new URLSearchParams(window.location.search);
        const selectedArea = urlParams.get('area') || 'Bashundhara Shopping Mall';
        const selectedVehicleType = urlParams.get('vehicle_type') || 'car';
        const selectedDate = urlParams.get('date') || '<?php echo date('Y-m-d'); ?>';
        const selectedTime = urlParams.get('time') || '<?php echo date('H:i'); ?>';
        const selectedDuration = urlParams.get('duration') || 1;

        $('#area-select').val(selectedArea);
        $('#vehicle-type-select').val(selectedVehicleType);
        $('#date-select').val(selectedDate);
        $('#time-select').val(selectedTime);
        $('#duration-select').val(selectedDuration);
    </script>
</body>

</html>