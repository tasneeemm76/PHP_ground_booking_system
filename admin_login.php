<?php
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Hardcoded admin credentials (for security, these should ideally be stored in a database and hashed)
    $adminUsername = 'a';
    $adminPassword = 'p';

    // Get the submitted username and password
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verify credentials
    if ($username === $adminUsername && $password === $adminPassword) {
        // Set session variable to mark the user as logged in
        $_SESSION['is_admin'] = true;
        header("Location: admin_dashboard.php"); // Redirect to the admin dashboard
        exit();
    } else {
        // Display an error message if login fails
        $error = "Invalid username or password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
</head>
<body>
    <h2>Admin Login</h2>
    <!-- Display error message if login fails -->
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" action="admin_login.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
