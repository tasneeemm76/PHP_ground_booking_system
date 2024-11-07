<?php
include 'db_connect.php';

$id = $_GET['id'];
$sql = "DELETE FROM bookings WHERE id = $id";
if ($conn->query($sql) === TRUE) {
    echo "Booking deleted successfully.";
} else {
    echo "Error deleting booking: " . $conn->error;
}

header("Location: admin_dashboard.php");
?>
