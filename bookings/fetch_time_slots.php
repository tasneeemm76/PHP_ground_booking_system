<?php
session_start();
include 'db_connect.php';

// Check if ground_id and date are set in the GET request
if (!isset($_GET['ground_id']) || !isset($_GET['date'])) {
    exit(json_encode(['error' => 'Invalid request: Missing ground_id or date']));
}

// Get ground ID and date from the request
$ground_id = $_GET['ground_id'];
$date = $_GET['date'];

// Initialize an array to store time slots
$time_slots = [];
for ($hour = 7; $hour <= 19; $hour++) {
    $time_24hr = date("H:i:s", strtotime("$hour:00"));

    // Check if this time slot is already booked on the selected date
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE ground_id = ? AND date = ? AND time_slot = ?");
    $check_stmt->bind_param("iss", $ground_id, $date, $time_24hr);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    // Prepare the time slot data
    $time_slots[] = [
        'value' => $time_24hr,
        'booked' => $count > 0,
        'text' => $count > 0 ? date("gA", strtotime("$hour:00")) . " - Booked" : date("gA", strtotime("$hour:00"))
    ];
}

// Return the time slots as a JSON response
echo json_encode(['time_slots' => $time_slots]);
?>
