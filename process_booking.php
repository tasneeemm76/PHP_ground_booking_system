<?php
include 'db_connect.php';

// Fetch form data
$ground_id = $_POST['ground_id'];
$user_name = $_POST['user_name'];
$contact_number = $_POST['contact_number'];
$date = $_POST['date'];
$time_slot = $_POST['time_slot'];

// Check if the slot is already booked
$sql = "SELECT * FROM bookings WHERE ground_id = '$ground_id' AND date = '$date' AND time_slot = '$time_slot'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "This time slot is already booked. Please choose a different slot.";
} else {
    // Insert the booking into the database
    $sql = "INSERT INTO bookings (ground_id, user_name, contact_number, date, time_slot)
            VALUES ('$ground_id', '$user_name', '$contact_number', '$date', '$time_slot')";
    if ($conn->query($sql) === TRUE) {
        echo "Booking successful!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
