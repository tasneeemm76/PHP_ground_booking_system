<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Get user ID from the session
$user_id = $_SESSION['user_id'];

// Prepare a query to fetch all bookings for the logged-in user
$sql = "SELECT b.id AS booking_id, g.name AS ground_name, b.date, b.time_slot
        FROM bookings AS b
        JOIN grounds AS g ON b.ground_id = g.id
        WHERE b.user_id = ?
        ORDER BY b.date DESC, b.time_slot DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

// Bind the user ID to the query
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Get the result set
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking History</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .delete-button {
            color: red;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h1>Your Booking History</h1>

<?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Ground Name</th>
                <th>Date</th>
                <th>Time Slot</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['ground_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><?php echo htmlspecialchars($row['time_slot']); ?></td>
                    <td>
                        <form action="delete_booking.php" method="POST" style="display:inline;">
                            <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($row['booking_id']); ?>">
                            <button type="submit" class="delete-button">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No bookings found.</p>
<?php endif; ?>

<?php
// Close the statement
$stmt->close();
?>

</body>
</html>
