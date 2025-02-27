<!DOCTYPE html>
<html lang="en">
<head>
<?php
session_start();
?>
    <meta charset="UTF-8">
    <title>Ground Booking System</title>
    <style>
        /* Body and general styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

 

       /* Adjust container to prevent wrapping */
.container {
    width: 80%;
    margin: 20px auto;
    display: flex;
    flex-direction: column; /* Stack items vertically */
    gap: 20px; /* Space between rows */
}
/* Each ground box styling */
.ground-box {
    width: 100%; /* Full width for each box */
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    display: flex;
    flex-direction: row; /* Keep image and content side by side */
    align-items: center; /* Center the content vertically */
}

/* Image styling */
.ground-box img {
    width: 40%; /* Fixed width for the image */
    height: 350px; /* Fixed height for uniformity */
    object-fit: cover; /* Ensure images cover the area without distortion */
}

/* Content styling for the right side */
.ground-box .content {
    padding: 10px; /* Add padding around the content */
    width: 60%; /* Remaining space for the text */
}


        /* Content styling */
        .content {
            padding: 15px;
            width: 50%;
        }

        /* Book Now button styling */
        .book-now {
            display: inline-block;
            width: 30%;
            text-align: center;
            padding: 10px;
            background-color: #008080;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            margin-top: 10px;
            transition: background 0.3s;
        }

        .book-now:hover {
            background-color: #006666;
        }

        /* Footer styling */
        footer {
            background-color: #333;
            color: #fff;
            padding: 20px 10px;
            width: 100%;
            position: relative;
            bottom: 0;
        }

        /* Responsive media query */
        @media (max-width: 768px) {
            .ground-box {
                width: 100%; /* Full width on small screens */
            }
        }
        /* Reset styling for nav */
nav {
    width: 100%;
    background-color:rgb(255, 255, 255);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    box-sizing: border-box;
}

/* Style for the welcome message */
nav div:first-child p {
    margin: 0;
    color: black;
    font-size: 18px;
    font-weight: bold;
}

/* Styling for nav links */
nav div:last-child a {
    color: black;
    text-decoration: none;
    margin: 0 10px;
    font-size: 16px;
}

/* Centered and responsive heading */
h1 {
    text-align: center;
    font-family: Arial, sans-serif;
    margin: 20px 0;
    font-size: 2em;
}

/* Responsive styling */
@media (max-width: 768px) {
    /* Stack nav items vertically */
    nav {
        flex-direction: column;
        align-items: flex-start;
    }
    
    /* Adjust nav links spacing and alignment */
    nav div:last-child a {
        display: block;
        margin: 5px 0;
    }
    
    /* Reduce font size for smaller screens */
    h1 {
        font-size: 1.5em;
    }
}

.banner {
            position: relative;
            width: 100%;
            height: 300px; /* Adjust height as needed */
            background: url('images/turf_banner.jpg') no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .overlay-text {
            color: white;
            font-size: 50px;
            font-weight: bold;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8);
            text-align: center;
        }       
    </style>
</head>
<body>
    <nav>
        <div>
            <?php if (isset($_SESSION['username'])): ?>
                <p>Welcome, <?php echo $_SESSION['username']; ?></p>
            <?php endif; ?>
        </div>
        <div>
            <a href="user_dashboard.php" >My bookings</a>
            <a href="logout.php" >Logout</a>
        </div>
    </nav>

    <div class="banner">
        <div class="overlay-text">Sports Ground</div>
    </div>
        <div class="container">
        <!-- Cricket Ground -->
        <div class="ground-box">
            <img src="images/cricket.jpg" alt="Cricket Ground">
            <div class="content">
                <h2>Cricket Ground</h2>
                <p>Enjoy a fantastic game of cricket at our well-maintained ground.<br> Ideal for matches and practice.</p>
                Timings: <br>
Tuesday to Friday<br>
04:00 to 06:00 pm<br>
                <a href="booking.php?ground_id=1" class="book-now">Book Now</a>
            </div>
        </div>

        <!-- Football Turf -->
        <div class="ground-box">
            <img src="images/football_turf.jpg" alt="Football Turf">
            <div class="content">
                <h2>Football Turf</h2>
                <p>Play football on our state-of-the-art turf, perfect for all skill levels.<br>
                Timing<br>
                7AM - 11PM
                </p>
                <a href="booking.php?ground_id=2" class="book-now">Book Now</a>
            </div>
        </div>

        <!-- Badminton Ground -->
        <div class="ground-box">
            <img src="images/badminton_ground.jpeg" alt="Badminton Ground">
            <div class="content">
                <h2>Badminton Ground</h2>
                <p>Join us for an exciting game of badminton on our premium courts.<br>
                About Venue<br>
* Non marking shoes are compulsory.<br>
* 4 Synthetic Badminton Courts<br>
                </p>
                <a href="booking.php?ground_id=3" class="book-now">Book Now</a>
            </div>
        </div>

        <!-- Lawn Tennis -->
        <div class="ground-box">
            <img src="images/tennis_court.jpg" alt="Lawn Tennis">
            <div class="content">
                <h2>Lawn Tennis</h2>
                <p>Experience the thrill of tennis on our well-maintained courts.</p>
                <a href="booking.php?ground_id=4" class="book-now">Book Now</a>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <footer>
        <div style="max-width: 80%; margin: auto; text-align: center;">
            <p style="margin: 10px 0; font-size: 18px; font-weight: bold;">Ground Booking System</p>
            <p style="margin: 5px 0;">Location: 1234 Sports Complex, Main Street, City, ZIP 12345</p>
            <p style="margin: 5px 0;">Phone: +1 (555) 123-4567</p>
            <p style="margin: 5px 0;">Email: support@groundbooking.com</p>
            <p style="margin: 15px 0; font-size: 14px;">&copy; <?php echo date("Y"); ?> Ground Booking System. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>
