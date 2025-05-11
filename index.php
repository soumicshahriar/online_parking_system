<?php
session_start();

// include the database connection file
require 'database/db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user's active bookings count
$user_id = $_SESSION['user_id'];
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$query = "SELECT COUNT(*) as active_count FROM a_bookings 
          WHERE user_id = ? 
          AND booking_date = ?
          AND NOW() BETWEEN CONCAT(booking_date, ' ', booking_time) 
          AND CONCAT(booking_date, ' ', end_time)";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $user_id, $selected_date);
$stmt->execute();
$result = $stmt->get_result();
$active_bookings = $result->fetch_assoc()['active_count'];

// Fetch available parking slots for the selected area, date, time, duration, and vehicle type
$selected_area = isset($_GET['area']) ? $_GET['area'] : 'Bashundhara Shopping Mall';
$selected_time = isset($_GET['time']) ? $_GET['time'] : date('H:i');
$selected_duration = isset($_GET['duration']) ? $_GET['duration'] : 1;
$selected_vehicle_types = isset($_GET['vehicle_types']) ? $_GET['vehicle_types'] : ['car', 'bike'];

// Calculate end time
$end_time = date('H:i', strtotime("+$selected_duration hours", strtotime($selected_time)));

// Fetch available slots
$slots = [];
$query = "SELECT * FROM parking_slots  
          WHERE location = ? 
          AND vehicle_type IN (" . str_repeat('?,', count($selected_vehicle_types) - 1) . "?) 
          AND id NOT IN (
              SELECT slot_id FROM a_bookings 
              WHERE booking_date = ? 
              AND (
                  (? < end_time AND ? > booking_time)
              )
          )";
$stmt = $conn->prepare($query);

// Create parameter array for bind_param
$params = array_merge([$selected_area], $selected_vehicle_types, [$selected_date, $selected_time, $end_time]);
$types = str_repeat('s', count($params));
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $slots[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkEase - Smart Parking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .slot-card {
            transition: all 0.3s ease;
        }
        .slot-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }
        .vehicle-type-checkbox {
            display: none;
        }
        .vehicle-type-label {
            display: inline-block;
            padding: 0.5rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .vehicle-type-checkbox:checked + .vehicle-type-label {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Loading Spinner -->
    <div id="loading-spinner" class="loading-spinner">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <?php include 'navigation.php'; ?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Available Parking Slots</h1>
                    <p class="text-sm text-red-600 mt-1">You can book up to 3 slots at a time</p>
                </div>
                <div class="bg-blue-50 text-blue-700 px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-car"></i>
                    <span>Selected Slots: <span id="selected-count" class="font-bold">0</span>/3</span>
                </div>
            </div>
        </div>

        <?php if($active_bookings >= 3): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        You have reached the maximum limit of 3 active bookings. Please wait until one of your current bookings is completed.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filters Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Area Selection -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Area</label>
                <select id="area-select" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="Bashundhara Shopping Mall">Bashundhara Shopping Mall</option>
                    <option value="Jamuna Future Park">Jamuna Future Park</option>
                </select>
            </div>

            <!-- Vehicle Type -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Vehicle Types</label>
                <div class="flex gap-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="vehicle_types[]" value="car" class="vehicle-type-checkbox"
                            <?php echo in_array('car', $selected_vehicle_types) ? 'checked' : ''; ?>>
                        <span class="vehicle-type-label">
                            <i class="fas fa-car mr-2"></i>Car
                        </span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="vehicle_types[]" value="bike" class="vehicle-type-checkbox"
                            <?php echo in_array('bike', $selected_vehicle_types) ? 'checked' : ''; ?>>
                        <span class="vehicle-type-label">
                            <i class="fas fa-motorcycle mr-2"></i>Bike
                        </span>
                    </label>
                </div>
            </div>

            <!-- Date Selection -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Booking Date</label>
                <input type="date" id="date-select" value="<?php echo $selected_date; ?>" min="<?php echo date('Y-m-d'); ?>"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Time Selection -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Booking Time</label>
                <input type="time" id="time-select" value="<?php echo $selected_time; ?>"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>

        <!-- Duration Selection -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Duration (hours)</label>
            <input type="number" id="duration-select" value="<?php echo $selected_duration; ?>" min="1" max="24"
                class="w-full md:w-1/3 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Parking Slots Grid -->
        <div id="slot-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($slots)): ?>
                <div class="col-span-full">
                    <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                        <i class="fas fa-parking text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600">No parking slots available for the selected criteria.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($slots as $slot): ?>
                    <div class="slot-card bg-white rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Slot <?php echo $slot['slot_number']; ?></h3>
                                <p class="text-sm text-gray-500"><?php echo $slot['location']; ?></p>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" class="slot-checkbox w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                       data-slot-id="<?php echo $slot['id']; ?>"
                                       data-cost="<?php echo $slot['cost_per_hour']; ?>"
                                       <?php echo $active_bookings >= 3 ? 'disabled' : ''; ?>>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-car-side w-5"></i>
                                <span><?php echo ucfirst($slot['vehicle_type']); ?></span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-tag w-5"></i>
                                <span>৳<?php echo number_format($slot['cost_per_hour'], 2); ?> per hour</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Book Button -->
        <div class="mt-8 text-center">
            <button id="book-selected-slots" 
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-gray-400 disabled:cursor-not-allowed"
                    disabled>
                <i class="fas fa-check-circle mr-2"></i>
                Book Selected Slots
            </button>
        </div>
    </main>

    <!-- Booking Modal -->
    <div id="booking-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center overflow-y-auto">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Confirm Booking</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="booking-form" class="space-y-4">
                <input type="hidden" id="selected-slots" name="selected_slots">
                
                <div id="vehicle-numbers-container" class="space-y-4">
                    <!-- Vehicle number inputs will be added here dynamically -->
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Booking Date</label>
                        <input type="date" id="popup-date" name="booking_date" readonly 
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Booking Time</label>
                        <input type="time" id="popup-time" name="booking_time" readonly 
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Duration (hours)</label>
                        <input type="number" id="popup-duration" name="duration" min="1" max="24" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               onchange="updateTotalCost()">
                        <p class="mt-1 text-sm text-red-500 duration-error"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Cost</label>
                        <input type="text" id="total-cost" readonly 
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">bKash Number</label>
                    <input type="text" id="bkash-number" name="bkash_number" placeholder="Enter bKash number" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="validateBkashNumber(this)">
                    <p class="mt-1 text-sm text-red-500 bkash-number-error"></p>
                </div>

                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700">bKash PIN</label>
                    <input type="password" id="bkash-pin" name="bkash_pin" placeholder="Enter bKash PIN" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="validateBkashPin(this)">
                    <button type="button" onclick="togglePassword('bkash-pin')" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500">
                        <i class="fas fa-eye"></i>
                    </button>
                    <p class="mt-1 text-sm text-red-500 bkash-pin-error"></p>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Confirm Booking
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Track selected slots
        let selectedSlots = new Set();
        const maxSlots = 3;

        // Show loading spinner
        function showLoading() {
            document.getElementById('loading-spinner').style.display = 'block';
        }

        // Hide loading spinner
        function hideLoading() {
            document.getElementById('loading-spinner').style.display = 'none';
        }

        // Handle slot selection
        $('.slot-checkbox').change(function() {
            const checkbox = $(this);
            const slotId = checkbox.data('slot-id');
            const cost = parseFloat(checkbox.data('cost'));
            const vehicleType = checkbox.closest('.slot-card').find('.text-gray-600').first().text().trim().toLowerCase();
            const slotData = {
                id: slotId,
                cost: cost,
                vehicle_type: vehicleType
            };

            if (checkbox.prop('checked')) {
                // Check active bookings for the selected date
                const selectedDate = $('#date-select').val();
                $.ajax({
                    url: 'check_active_bookings.php',
                    type: 'POST',
                    data: { date: selectedDate },
                    success: function(response) {
                        const activeBookings = parseInt(response);
                        if (activeBookings + selectedSlots.size >= maxSlots) {
                            if (!confirm(`You already have ${activeBookings} active booking(s) for this date. You can only book up to 3 slots per day. Do you want to continue?`)) {
                                checkbox.prop('checked', false);
                                return;
                            }
                        }
                        selectedSlots.add(slotData);
                        updateSelectedCount();
                        updateBookButton();
                    },
                    error: function() {
                        alert('Error checking active bookings. Please try again.');
                        checkbox.prop('checked', false);
                    }
                });
            } else {
                // Remove the slot from selectedSlots
                for (let slot of selectedSlots) {
                    if (slot.id === slotId) {
                        selectedSlots.delete(slot);
                        break;
                    }
                }
                updateSelectedCount();
                updateBookButton();
            }
        });

        // Update selected slots count
        function updateSelectedCount() {
            $('#selected-count').text(selectedSlots.size);
        }

        // Update book button state
        function updateBookButton() {
            const bookButton = $('#book-selected-slots');
            bookButton.prop('disabled', selectedSlots.size === 0);
        }

        // Book selected slots
        $('#book-selected-slots').click(function() {
            if (selectedSlots.size === 0) return;

            const selectedDate = $('#date-select').val();
            const selectedTime = $('#time-select').val();
            const selectedDuration = parseInt($('#duration-select').val());

            // Calculate total cost
            let totalCost = 0;
            selectedSlots.forEach(slot => {
                totalCost += slot.cost * selectedDuration;
            });

            // Update modal
            $('#selected-slots').val(JSON.stringify(Array.from(selectedSlots)));
            $('#popup-date').val(selectedDate);
            $('#popup-time').val(selectedTime);
            $('#popup-duration').val(selectedDuration);
            $('#total-cost').val(`৳${totalCost.toFixed(2)}`);

            // Generate vehicle number inputs
            const container = $('#vehicle-numbers-container');
            container.empty();
            
            selectedSlots.forEach((slot, index) => {
                const vehicleType = slot.vehicle_type;
                const format = vehicleType === 'car' ? 'ABC-1234' : 'ABC-12345';
                const costPerHour = slot.cost;
                
                container.append(`
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-sm font-medium text-gray-900">Vehicle ${index + 1}</h3>
                            <span class="text-sm text-gray-500">${vehicleType.charAt(0).toUpperCase() + vehicleType.slice(1)}</span>
                        </div>
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-gray-700">Vehicle Number</label>
                            <input type="text" 
                                   name="vehicle_numbers[]" 
                                   placeholder="Enter vehicle number (${format})" 
                                   required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   data-vehicle-type="${vehicleType}">
                            <p class="mt-1 text-sm text-gray-500">Format: ${format}</p>
                            <p class="mt-1 text-sm text-red-500 vehicle-error"></p>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Cost per hour:</span>
                            <span class="font-medium text-blue-600">৳${costPerHour.toFixed(2)}</span>
                        </div>
                    </div>
                `);
            });

            // Show modal
            $('#booking-modal').removeClass('hidden').addClass('flex');
        });

        // Validate vehicle numbers on input
        $(document).on('input', 'input[name="vehicle_numbers[]"]', function() {
            const vehicleNumber = $(this).val().trim();
            const vehicleType = $(this).data('vehicle-type');
            const errorDiv = $(this).siblings('.vehicle-error');
            
            // Remove spaces and convert to uppercase
            const formattedNumber = vehicleNumber.toUpperCase().replace(/\s/g, '');
            
            // Validate format
            let isValid = false;
            if (vehicleType === 'car') {
                isValid = /^[A-Z]{3}-[0-9]{3,4}$/.test(formattedNumber);
            } else {
                isValid = /^[A-Z]{3}-[0-9]{4,5}$/.test(formattedNumber);
            }
            
            if (!isValid) {
                errorDiv.text(`Invalid ${vehicleType} number format`);
                $(this).addClass('border-red-500');
            } else {
                errorDiv.text('');
                $(this).removeClass('border-red-500');
            }
        });

        // Function to update total cost when duration changes
        function updateTotalCost() {
            const duration = parseInt(document.getElementById('popup-duration').value);
            const durationError = document.querySelector('.duration-error');
            
            if (duration < 1 || duration > 24) {
                durationError.textContent = 'Duration must be between 1 and 24 hours';
                return;
            }
            
            durationError.textContent = '';
            let totalCost = 0;
            selectedSlots.forEach(slot => {
                totalCost += slot.cost * duration;
            });
            document.getElementById('total-cost').value = `৳${totalCost.toFixed(2)}`;
        }

        // Function to validate bKash number
        function validateBkashNumber(input) {
            const bkashNumber = input.value.trim();
            const errorDiv = input.nextElementSibling;
            
            // bKash number format: 01XXXXXXXXX (11 digits starting with 01)
            const isValid = /^01[0-9]{9}$/.test(bkashNumber);
            
            if (!isValid) {
                errorDiv.textContent = 'Please enter a valid bKash number (e.g., 017XXXXXXXX)';
                input.classList.add('border-red-500');
            } else {
                errorDiv.textContent = '';
                input.classList.remove('border-red-500');
            }
        }

        // Function to validate bKash PIN
        function validateBkashPin(input) {
            const bkashPin = input.value.trim();
            const errorDiv = input.nextElementSibling.nextElementSibling;
            
            // bKash PIN format: 4-6 digits
            const isValid = /^[0-9]{4,6}$/.test(bkashPin);
            
            if (!isValid) {
                errorDiv.textContent = 'PIN must be 4-6 digits';
                input.classList.add('border-red-500');
            } else {
                errorDiv.textContent = '';
                input.classList.remove('border-red-500');
            }
        }

        // Update the booking form submission handler
        $('#booking-form').submit(function(e) {
            e.preventDefault();

            // Validate duration
            const duration = parseInt(document.getElementById('popup-duration').value);
            if (duration < 1 || duration > 24) {
                document.querySelector('.duration-error').textContent = 'Duration must be between 1 and 24 hours';
                return;
            }

            // Validate bKash number
            const bkashNumber = $('#bkash-number').val().trim();
            if (!/^01[0-9]{9}$/.test(bkashNumber)) {
                $('.bkash-number-error').text('Please enter a valid bKash number (e.g., 017XXXXXXXX)');
                return;
            }

            // Validate bKash PIN
            const bkashPin = $('#bkash-pin').val().trim();
            if (!/^[0-9]{4,6}$/.test(bkashPin)) {
                $('.bkash-pin-error').text('PIN must be 4-6 digits');
                return;
            }

            // Validate all vehicle numbers
            let isValid = true;
            $('input[name="vehicle_numbers[]"]').each(function() {
                const vehicleNumber = $(this).val().trim();
                const vehicleType = $(this).data('vehicle-type');
                const formattedNumber = vehicleNumber.toUpperCase().replace(/\s/g, '');
                
                let numberValid = false;
                if (vehicleType === 'car') {
                    numberValid = /^[A-Z]{3}-[0-9]{3,4}$/.test(formattedNumber);
                } else {
                    numberValid = /^[A-Z]{3}-[0-9]{4,5}$/.test(formattedNumber);
                }
                
                if (!numberValid) {
                    isValid = false;
                    $(this).addClass('border-red-500');
                    $(this).siblings('.vehicle-error').text(`Invalid ${vehicleType} number format`);
                }
            });

            if (!isValid) {
                return;
            }

            showLoading();

            // Simulate bKash payment
            alert('Payment successful! Confirming your bookings...');

            const formData = $(this).serialize();

            $.ajax({
                url: 'book_slot.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    hideLoading();
                    alert(response);
                    closeModal();
                    location.reload();
                },
                error: function() {
                    hideLoading();
                    alert('An error occurred. Please try again.');
                }
            });
        });

        // Close booking modal
        function closeModal() {
            $('#booking-modal').removeClass('flex').addClass('hidden');
            // Reset form
            $('#booking-form')[0].reset();
            // Clear selected slots
            selectedSlots.clear();
            $('.slot-checkbox').prop('checked', false);
            updateSelectedCount();
            updateBookButton();
        }

        // Toggle password visibility
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const eyeIcon = passwordField.nextElementSibling.querySelector('i');

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }

        // Reload slots when filters change
        $('#area-select, #date-select, #time-select, #duration-select').change(function() {
            updateSlots();
        });

        // Handle vehicle type changes
        $('input[name="vehicle_types[]"]').change(function() {
            updateSlots();
        });

        // Function to update slots
        function updateSlots() {
            showLoading();
            const selectedArea = $('#area-select').val();
            const selectedDate = $('#date-select').val();
            const selectedTime = $('#time-select').val();
            const selectedDuration = $('#duration-select').val();
            const selectedVehicleTypes = [];
            
            $('input[name="vehicle_types[]"]:checked').each(function() {
                selectedVehicleTypes.push($(this).val());
            });

            // If no vehicle types are selected, select both
            if (selectedVehicleTypes.length === 0) {
                selectedVehicleTypes.push('car', 'bike');
                $('input[name="vehicle_types[]"]').prop('checked', true);
            }

            const queryParams = new URLSearchParams({
                area: selectedArea,
                date: selectedDate,
                time: selectedTime,
                duration: selectedDuration
            });

            // Add each vehicle type as a separate parameter
            selectedVehicleTypes.forEach(type => {
                queryParams.append('vehicle_types[]', type);
            });

            window.location.href = `index.php?${queryParams.toString()}`;
        }

        // Set initial values from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const selectedArea = urlParams.get('area') || 'Bashundhara Shopping Mall';
        const selectedDate = urlParams.get('date') || '<?php echo date('Y-m-d'); ?>';
        const selectedTime = urlParams.get('time') || '<?php echo date('H:i'); ?>';
        const selectedDuration = urlParams.get('duration') || 1;
        const selectedVehicleTypes = urlParams.getAll('vehicle_types[]');

        $('#area-select').val(selectedArea);
        $('#date-select').val(selectedDate);
        $('#time-select').val(selectedTime);
        $('#duration-select').val(selectedDuration);

        // Set vehicle type checkboxes
        if (selectedVehicleTypes.length > 0) {
            $('input[name="vehicle_types[]"]').prop('checked', false);
            selectedVehicleTypes.forEach(type => {
                $(`input[name="vehicle_types[]"][value="${type}"]`).prop('checked', true);
            });
        }

        // Mobile menu toggle with animation
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            this.setAttribute('aria-expanded', !isExpanded);
            mobileMenu.classList.toggle('hidden');
            
            // Toggle icon with animation
            const icon = this.querySelector('i');
            if (isExpanded) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
                mobileMenu.style.transform = 'translateY(-10px)';
                mobileMenu.style.opacity = '0';
            } else {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
                mobileMenu.style.transform = 'translateY(0)';
                mobileMenu.style.opacity = '1';
            }
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            
            if (!mobileMenu.contains(event.target) && !mobileMenuButton.contains(event.target)) {
                mobileMenu.classList.add('hidden');
                mobileMenuButton.setAttribute('aria-expanded', 'false');
                const icon = mobileMenuButton.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        // Add active state to current page link
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('nav a');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath.split('/').pop()) {
                    link.classList.add('border-blue-500', 'text-gray-900');
                    link.classList.remove('border-transparent', 'text-gray-500');
                }
            });
        });
    </script>
</body>

</html>