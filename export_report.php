<?php
require_once 'database/db.php';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="parking_report_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, ['Booking ID', 'User', 'Slot Number', 'Location', 'Vehicle Number', 'Booking Date', 'Booking Time', 'Duration', 'Total Cost', 'Status']);

// Get all bookings with user and slot information
$query = "SELECT 
    a.id,
    u.username,
    p.slot_number,
    p.location,
    a.vehicle_number,
    a.booking_date,
    a.booking_time,
    a.duration,
    a.total_cost,
    CASE
        WHEN NOW() < CONCAT(a.booking_date, ' ', a.booking_time) THEN 'Pending'
        WHEN NOW() BETWEEN CONCAT(a.booking_date, ' ', a.booking_time) 
            AND CONCAT(a.booking_date, ' ', a.end_time) THEN 'Active'
        ELSE 'Completed'
    END as status
FROM a_bookings a 
JOIN users u ON a.user_id = u.id 
JOIN parking_slots p ON a.slot_id = p.id 
ORDER BY a.booking_date DESC, a.booking_time DESC";

$result = $conn->query($query);

// Write data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['username'],
        $row['slot_number'],
        $row['location'],
        $row['vehicle_number'],
        $row['booking_date'],
        $row['booking_time'],
        $row['duration'],
        $row['total_cost'],
        $row['status']
    ]);
}

// Close the output stream
fclose($output);
$conn->close();
?> 