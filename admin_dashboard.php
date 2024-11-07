<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch grounds for checkboxes and selection in add booking form
$ground_result = $conn->query("SELECT id, name FROM grounds");

// Function to load bookings based on selected filters
function loadBookings($ground_filters = [], $date_filter = '', $booking_period = '') {
    global $conn;
    $booking_query = "SELECT b.*, g.name AS ground_name FROM bookings b JOIN grounds g ON b.ground_id = g.id WHERE 1=1";
    $params = [];
    $param_types = '';

    // Add conditions based on selected filters
    if (!empty($ground_filters)) {
        $ground_placeholders = implode(',', array_fill(0, count($ground_filters), '?'));
        $booking_query .= " AND b.ground_id IN ($ground_placeholders)";
        $params = array_merge($params, array_map('intval', $ground_filters));
        $param_types .= str_repeat('i', count($ground_filters));
    }

    if ($date_filter) {
        $booking_query .= " AND b.date = ?";
        $params[] = $date_filter;
        $param_types .= 's';
    }

    if ($booking_period) {
        $today = date('Y-m-d');
        switch ($booking_period) {
            case 'past_10_days':
                $booking_query .= " AND b.date >= DATE_SUB(?, INTERVAL 10 DAY)";
                $params[] = $today;
                $param_types .= 's';
                break;
            case 'past_month':
                $booking_query .= " AND b.date >= DATE_SUB(?, INTERVAL 1 MONTH)";
                $params[] = $today;
                $param_types .= 's';
                break;
            case 'all':
                // No additional condition for all bookings
                break;
        }
    }

    $stmt = $conn->prepare($booking_query);
    if ($param_types) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Handle AJAX requests for filtering and adding bookings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_request'])) {
    $ground_filters = isset($_POST['ground_filters']) ? $_POST['ground_filters'] : [];
    $date_filter = $_POST['date_filter'] ?? '';
    $booking_period = $_POST['booking_period'] ?? '';
    $bookings = loadBookings($ground_filters, $date_filter, $booking_period);

    // Generate the updated table rows
    foreach ($bookings as $booking) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($booking['id']) . "</td>";
        echo "<td>" . htmlspecialchars($booking['ground_name']) . "</td>";
        echo "<td>" . htmlspecialchars($booking['user_name']) . "</td>";
        echo "<td>" . htmlspecialchars($booking['date']) . "</td>";
        echo "<td>" . htmlspecialchars($booking['time_slot']) . "</td>";
        echo "<td><button onclick=\"deleteBooking(" . htmlspecialchars($booking['id']) . ")\">Delete</button></td>";
        echo "</tr>";
    }
    exit;
}

// Handle delete booking action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking'])) {
    $booking_id = $_POST['delete_booking'];
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    exit;
}

// Handle adding a new booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_booking'])) {
    $ground_id = $_POST['ground_id'];
    $user_name = $_POST['user_name'];
    $date = $_POST['date'];
    $time_slot = $_POST['time_slot'];

    $stmt = $conn->prepare("INSERT INTO bookings (ground_id, user_name, date, time_slot) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $ground_id, $user_name, $date, $time_slot);
    $stmt->execute();
    exit;
}

// Initialize error and success messages -admin ground booking
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if form data is set
    if (isset($_POST['ground_id'], $_POST['user_name'], $_POST['user_id'], $_POST['date'], $_POST['time_slot'])) {
        $ground_id = $_POST['ground_id'];
        $user_name = $_POST['user_name']; // This will be "admin"
        $user_id = $_POST['user_id']; // This will be "0"
        $date = $_POST['date'];
        $time_slot = $_POST['time_slot'];

        // Insert into bookings table
        $stmt = $conn->prepare("INSERT INTO bookings (ground_id, user_name, user_id, date, time_slot) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $ground_id, $user_name, $user_id, $date, $time_slot);

        if ($stmt->execute()) {
            $message = "Booking successful!";
            $messageType = "success";
            // Optionally reset the form after booking
            // header("Location: admin_dashboard.php"); // Uncomment to redirect
        } else {
            $message = "Error: " . $stmt->error;
            $messageType = "error";
        }

        $stmt->close();
    } else {
        $message = "Please fill all fields.";
        $messageType = "error";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">

    <script>
        function fetchTimeSlots() {
            const groundId = document.getElementById('ground-select').value;
            const date = document.getElementById('date-input').value;

            if (groundId && date) {
                fetch(`fetch_time_slots.php?ground_id=${groundId}&date=${date}`)
                    .then(response => response.json())
                    .then(data => {
                        const timeSlotSelect = document.getElementById('time-slot-select');
                        timeSlotSelect.innerHTML = ''; // Clear previous options

                        data.time_slots.forEach(slot => {
                            const option = document.createElement('option');
                            option.value = slot.value;
                            option.textContent = slot.text;
                            timeSlotSelect.appendChild(option);
                        });
                    });
            }
        }
        
        function toggleDateTimeSelection() {
            const groundId = document.getElementById('ground-select').value;
            document.getElementById('date-time-selection').style.display = groundId ? 'block' : 'none';
        }
   
        function fetchBookings() {
            const formData = new FormData(document.getElementById('filter-form'));
            formData.append('ajax_request', 'true');

            fetch('admin_dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('booking-table-body').innerHTML = html;
            });
        }

        function deleteBooking(bookingId) {
            if (confirm("Are you sure you want to delete this booking?")) {
                const formData = new FormData();
                formData.append('delete_booking', bookingId);

                fetch('admin_dashboard.php', {
                    method: 'POST',
                    body: formData
                })
                .then(() => fetchBookings());
            }
        }

        function addBooking() {
            const formData = new FormData(document.getElementById('add-booking-form'));
            formData.append('add_booking', 'true');

            fetch('admin_dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(() => {
                fetchBookings();
                document.getElementById('add-booking-form').reset();
            });
        }
    </script>
</head>
<body>

    
<div class="container">
   
<div class="left-column">


<!-- Display Success/Error Messages -->
<?php if ($message): ?>
    <div class="<?= $messageType === 'success' ? 'success-message' : 'error-message' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Booking Form -->
<form id="booking-form" method="POST" action="admin_dashboard.php">
    <label for="ground-select">Select Ground:</label>
    <select name="ground_id" id="ground-select" onchange="toggleDateTimeSelection(); fetchTimeSlots();" required>
        <option value="">Select Ground</option>
        <?php
        // Fetch available grounds from the database to populate this select
        $ground_result = $conn->query("SELECT id, name FROM grounds");
        while ($ground = $ground_result->fetch_assoc()) {
            echo "<option value=\"{$ground['id']}\">{$ground['name']}</option>";
        }
        ?>
    </select>

    <div id="date-time-selection" style="display: none;">
        <label for="date-input">Select Date:</label>
        <input type="date" name="date" id="date-input" onchange="fetchTimeSlots();" required>

        <label for="time-slot-select">Select Time Slot:</label>
        <select name="time_slot" id="time-slot-select" required>
            <option value="">Select Time Slot</option>
            <!-- Time slots will be populated based on the ground and date selection -->
        </select>
    </div>

    <input type="hidden" name="user_name" value="admin">
    <input type="hidden" name="user_id" value="0">
    <button type="submit">Book Now</button>
</form>

<!-- Advanced Filter Bookings -->
<h2>Filter Bookings</h2>
<form id="filter-form" method="POST" onsubmit="event.preventDefault(); fetchBookings();">
    <h3>Select Grounds:</h3>
    <?php
    $ground_result->data_seek(0); // Reset result pointer to reuse it again
    while ($ground = $ground_result->fetch_assoc()) {
        echo "<label><input type=\"checkbox\" name=\"ground_filters[]\" value=\"{$ground['id']}\" onclick=\"fetchBookings()\"> {$ground['name']}</label><br>";
    }
    ?>
    
    <label for="date-filter">Select Date:</label>
    <input type="date" name="date_filter" id="date-filter" onchange="fetchBookings()">

    <label for="booking-period">Booking Period:</label>
    <select name="booking_period" id="booking-period" onchange="fetchBookings()">
        <option value="">Select Period</option>
        <option value="past_10_days">Past 10 Days</option>
        <option value="past_month">Past Month</option>
        <option value="all">All Bookings</option>
    </select>
</form>
</div>
<!-- Bookings Table -->
<div class="right-column">
<h2>Bookings</h2>
<table >
    <thead>
        <tr>
            <th>ID</th>
            <th>Ground Name</th>
            <th>User Name</th>
            <th>Date</th>
            <th>Time Slot</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="booking-table-body">
        <?php
        $initialBookings = loadBookings();
        foreach ($initialBookings as $booking) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($booking['id']) . "</td>";
            echo "<td>" . htmlspecialchars($booking['ground_name']) . "</td>";
            echo "<td>" . htmlspecialchars($booking['user_name']) . "</td>";
            echo "<td>" . htmlspecialchars($booking['date']) . "</td>";
            echo "<td>" . htmlspecialchars($booking['time_slot']) . "</td>";
            echo "<td><button onclick=\"deleteBooking(" . htmlspecialchars($booking['id']) . ")\">Delete</button></td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
</div>

</body>
</html>
