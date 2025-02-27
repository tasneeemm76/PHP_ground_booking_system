<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id']; // Retrieve user ID from session
$username = $_SESSION['username']; // Retrieve username from session
$bookings = []; // Initialize as an empty array

// Fetch previous bookings for the logged-in user
$query = "SELECT b.id, b.ground_id, b.user_name, b.contact_number, b.date, b.time_slot, g.name AS ground_name
          FROM bookings b
          JOIN grounds g ON b.ground_id = g.id
          WHERE b.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $bookings = $result->fetch_all(MYSQLI_ASSOC); // Fetch all rows as an associative array
}
$stmt->close();

// Handle delete booking request
if (isset($_GET['delete'])) {
    $booking_id = $_GET['delete'];

    // Delete booking from the database
    $delete_stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $booking_id, $user_id);
    
    if ($delete_stmt->execute()) {
        $message = "Booking deleted successfully.";
    } else {
        $message = "Error deleting booking: " . $delete_stmt->error;
    }

    $delete_stmt->close();
    // Refresh the bookings list after deletion
    header("Location: user_dashboard.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <style>
      /* Navbar styling */
nav {
    display: flex;
    justify-content: flex-end;
    background-color: #333;
    padding: 10px;
    flex-wrap: wrap; /* Allows items to wrap on smaller screens */
}

nav a {
    color: #fff;
    padding: 10px 20px;
    text-decoration: none;
    font-weight: bold;
    margin-left: 10px;
    font-size: 1em;
}

nav a:hover {
    background-color: #555;
}

/* Header styling */
header {
    padding: 20px;
    text-align: center;
    background-color: #f4f4f4;
    font-size: 24px;
    font-weight: bold;
}

/* Table styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 1em;
}

th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
    font-size: 0.9em;
}

th {
    background-color: #f2f2f2;
}

/* Message styling */
.message {
    color: green;
    font-weight: bold;
    margin-top: 10px;
}

.error {
    color: red;
    font-weight: bold;
    margin-top: 10px;
}

/* Responsive layout for smaller screens */
@media (max-width: 768px) {
    nav {
        justify-content: center; /* Center align links on smaller screens */
    }
    
    nav a {
        padding: 8px 12px;
        font-size: 0.9em;
    }

    header {
        font-size: 20px;
        padding: 15px;
    }

    table {
        display: block;
        font-size: 1em;
    }

    /* Make each table row a block */
    tr {
        display: block;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        background-color: #f9f9f9;
    }

    /* Hide table headers */
    thead {
        display: none;
    }

    /* Style each table cell as a separate row with a label */
    td {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

    td:last-child {
        border-bottom: none;
    }

    /* Label for each cell on mobile */
    td:before {
        content: attr(data-label);
        font-weight: bold;
        color: #555;
        margin-right: 10px;
    }
}

@media (max-width: 480px) {
    header {
        font-size: 18px;
    }

    nav a {
        padding: 6px 10px;
        font-size: 0.85em;
    }

    table, th, td {
        font-size: 0.85em;
    }
}

       </style>
</head>
<body>
    <nav>
        <a href="index.php">Back to Home</a>
        <a href="logout.php">Logout</a>
    </nav>

    <header>
        <?php echo htmlspecialchars($username); ?>'s Bookings
    </header>

    <!-- Display confirmation message if set -->
    <?php if (isset($message)) : ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <table>
        <tr>
            <th>Ground Name</th>
            <th>User Name</th>
            <th>Date</th>
            <th>Time Slot</th>
            <th>Action</th>
        </tr>
        <tbody id="booking-table-body">
        <?php if (count($bookings) > 0): ?>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td data-label="Ground Name"><?php echo htmlspecialchars($booking['ground_name']); ?></td>
                    <td data-label="User Name"><?php echo htmlspecialchars($booking['user_name']); ?></td>
                    <td data-label="Date"><?php echo htmlspecialchars($booking['date']); ?></td>
                    <td data-label="Time Slot"><?php echo htmlspecialchars($booking['time_slot']); ?></td>
                    <td data-label="Actions">
                        <a href="user_dashboard.php?delete=<?php echo $booking['id']; ?>" onclick="return confirm('Are you sure you want to delete this booking?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        <?php else: ?>
            <tr>
                <td colspan="6">No bookings found.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>
