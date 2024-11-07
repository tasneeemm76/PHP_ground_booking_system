<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ground_id = $_POST['ground_id'];
    $user_name = $_POST['user_name']; // This will be "admin"
    $user_id = $_POST['user_id']; // This will be "0"
    $date = $_POST['date'];
    $time_slot = $_POST['time_slot'];

    // Insert into bookings table
    $stmt = $conn->prepare("INSERT INTO bookings (ground_id, user_name, user_id, date, time_slot) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $ground_id, $user_name, $user_id, $date, $time_slot);

    if ($stmt->execute()) {
        // Redirect to the admin dashboard after successful insertion
        header("Location: admin_dashboard.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
