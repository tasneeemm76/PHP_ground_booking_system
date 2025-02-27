<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Check if a booking has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ground_id = isset($_POST['ground_id']) ? $_POST['ground_id'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $contact = isset($_POST['contact']) ? $_POST['contact'] : '';
    $date = $_POST['date'];
    $time_slot = isset($_POST['time_slot']) ? $_POST['time_slot'] : '';
    $user_id = $_SESSION['user_id'];

    // Check if the selected date is in the past
    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        $message = "Error: You cannot book a ground on a past date.";
    } else {
        // Check if the selected time slot is already booked on the chosen date
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE ground_id = ? AND date = ? AND time_slot = ?");
        $check_stmt->bind_param("iss", $ground_id, $date, $time_slot);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            $message = "The selected time slot is already booked. Please choose a different time.";
        } else {
            // Prepare and bind the booking statement
            $stmt = $conn->prepare("INSERT INTO bookings (ground_id, user_name, contact_number, date, time_slot, user_id) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }

            // Bind the parameters
            $stmt->bind_param("issssi", $ground_id, $name, $contact, $date, $time_slot, $user_id);

            // Execute the statement
            if ($stmt->execute()) {
                $message = "Thank you, $name! Your booking for ground ID $ground_id at $time_slot on $date has been confirmed.";
            } else {
                $message = "Error: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        }
    }
} else {
    $ground_id = isset($_GET['ground_id']) ? $_GET['ground_id'] : '';
}

// Set the image path and ground name based on the ground_id parameter
$imagePath = '';
$groundName = '';
switch ($ground_id) {
    case '1': // Cricket Ground
        $imagePath = 'images/cricket.jpg';
        $groundName = 'Cricket Ground';
        break;
    case '2': // Football Turf
        $imagePath = 'images/football_turf.jpg';
        $groundName = 'Football Turf';
        break;
    case '3': // Badminton Ground
        $imagePath = 'images/badminton_ground.jpeg';
        $groundName = 'Badminton Ground';
        break;
    case '4': // Lawn Tennis
        $imagePath = 'images/tennis_court.jpg';
        $groundName = 'Lawn Tennis Court';
        break;
    default:
        echo "Invalid ground selected.";
        exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// User dashboard link
$user_dashboard = "user_dashboard.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Ground</title>
    <link rel="stylesheet" href="css/bookings.css">
    <style>
        /* Base styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

h1 {
    text-align: center;
    margin-top: 20px;
    color: #333;
    font-size: 2em;
}

/* Container styling */
.booking-container {
    max-width: 80%;
    background-color: #ffffff;
    margin: 20px auto;
    display: flex;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    gap: 20px;
    align-items: center;
}

/* Image styling */
.ground-image {
    flex: 1;
    max-width: 50%;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Form styling */
.booking-form {
    flex: 1;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 10px;
}

.booking-form label {
    display: block;
    font-weight: bold;
    margin: 10px 0 5px;
    color: #555;
}

.booking-form input[type="text"],
.booking-form input[type="tel"],
.booking-form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
}

.booking-form button {
    width: 100%;
    padding: 12px;
    background-color: #008080;
    color: #fff;
    font-weight: bold;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
}

.booking-form button:hover {
    background-color: #006666;
}

/* Message styling */
.message {
    background-color: #d4edda;
    color: #155724;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
}

/* Responsive layout */
@media (max-width: 1024px) {
    .booking-container {
        max-width: 90%;
        padding: 15px;
    }
}

@media (max-width: 768px) {
    .booking-container {
        flex-direction: column;
        padding: 15px;
    }

    .ground-image, .booking-form {
        max-width: 100%;
    }
    
    /* Reduced button size for smaller screens */
    .booking-form button {
        font-size: 14px;
        padding: 10px;
    }
}

@media (max-width: 480px) {
    h1 {
        font-size: 1.5em;
    }

    .booking-form button {
        font-size: 12px;
        padding: 8px;
    }

    /* Ensure buttons fit in a single row */
    .button-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
    }

    /* Reduce button size to fit in row */
    .button {
        padding: 8px 12px;
        font-size: 14px;
    }
}

/* Reset some basic styling */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

/* Navbar styling */
.navbar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    background-color: #333;
    padding: 10px 20px;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1000;
}

.navbar div p {
    margin: 0;
    color: white;
    font-size: 18px;
    font-weight: bold;
}

/* Header styling */
header {
    text-align: center;
    margin-top: 80px; /* Offset for fixed navbar */
}

/* Button styling */
.button {
    display: inline-block;
    margin: 0 10px;
    padding: 10px 20px;
    text-decoration: none;
    color: #fff;
    background-color: #008080;
    border-radius: 5px;
    font-weight: bold;
    transition: background-color 0.3s;
}

.button:hover {
    background-color: #0056b3;
}

/* Styling for the page content */
.content {
    padding: 20px;
    max-width: 800px;
    margin: 0 auto;
}

    </style>
</head>
<body>
<div>
            <?php if (isset($_SESSION['username'])): ?>
                <p>Welcome, <?php echo $_SESSION['username']; ?></p>
            <?php endif; ?>
        </div>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <a href="<?php echo $user_dashboard; ?>" class="button">Dashboard</a>
        <a href="index.php" class="button">Home</a>
        <a href="logout.php" class="button">Logout</a>
    </nav>

    <!-- Header -->
    <header>
        <h1>Book Your <?php echo htmlspecialchars($groundName); ?> </h1>
        <!-- Display the ground name -->
    </header>

    <div class="booking-container">
        <!-- Display the ground image -->
        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Ground" class="ground-image">
        
        <!-- Booking form -->
        <div class="booking-form">
            <form action="booking.php?ground_id=<?php echo urlencode($ground_id); ?>" method="POST">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>

                <label for="contact">Contact Number:</label>
                <input type="tel" id="contact" name="contact" required>

                <label for="date">Booking Date:</label>
                <input type="date" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>">

                <label for="time_slot">Select Time Slot:</label>
<select id="time_slot" name="time_slot" required>
    <option value="">Select a time slot</option>
    <?php
    // Generate time slots from 7AM to 7PM
    for ($hour = 7; $hour <= 19; $hour++) {
        $time_12hr = date("gA", strtotime("$hour:00"));
        $time_24hr = date("H:i:s", strtotime("$hour:00"));

        // Check if this time slot is already booked on the selected date
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE ground_id = ? AND date = ? AND time_slot = ?");
        $check_stmt->bind_param("iss", $ground_id, $date, $time_24hr);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        // Display "Booked" if the slot is unavailable, else show as available
        if ($count > 0) {
            echo "<option value=\"$time_24hr\" disabled>$time_12hr - Booked</option>";
        } else {
            echo "<option value=\"$time_24hr\">$time_12hr</option>";
        }
    }
    ?>
</select>

<script>
document.getElementById('date').addEventListener('change', function() {
    const date = this.value;
    const groundId = "<?php echo htmlspecialchars($ground_id); ?>";
    const timeSlotSelect = document.getElementById('time_slot');

    // Clear the current options
    timeSlotSelect.innerHTML = '<option value="">Select a time slot</option>';

    // Fetch booking data for the selected date via AJAX
    fetch(`fetch_time_slots.php?ground_id=${groundId}&date=${date}`)
        .then(response => response.json())
        .then(data => {
            data.time_slots.forEach(slot => {
                const option = document.createElement('option');
                option.value = slot.value;
                option.disabled = slot.booked;
                option.textContent = slot.text;
                timeSlotSelect.appendChild(option);
            });
        });
});
</script>


                <input type="hidden" name="ground_id" value="<?php echo htmlspecialchars($ground_id); ?>">

                <button type="submit">Book Now</button>
            </form>

            <!-- Display confirmation message if set -->
            <?php if (isset($message)) : ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
