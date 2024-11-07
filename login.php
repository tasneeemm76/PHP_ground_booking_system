<?php
session_start();
include 'db_connect.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the submitted username/email and password
    $usernameOrEmail = $_POST['username_or_email'];
    $password = $_POST['password'];

    // Admin credentials (for security, these should ideally be stored in a database and hashed)
    $adminUsername = 'admin';
    $adminPassword = 'admin';

    // Check if the input is an admin login
    if ($usernameOrEmail === $adminUsername && $password === $adminPassword) {
        // Set session variable to mark the user as an admin
        $_SESSION['is_admin'] = true;
        header("Location: admin_dashboard.php"); // Redirect to the admin dashboard
        exit();
    } else {
        // Otherwise, check for user login
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php"); // Redirect to user index
            exit();
        } else {
            $error = "Invalid login credentials. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        /* CSS for the login form */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            margin: 0;
        }

        .login-container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-container h2 {
            margin: 0;
            color: #333;
            text-align: center;
        }

        .login-container form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .login-container label {
            font-weight: bold;
            color: #555;
        }

        .login-container input[type="text"],
        .login-container input[type="email"],
        .login-container input[type="password"] {
            padding: 0.75rem;
            border-radius: 4px;
            border: 1px solid #ddd;
            outline: none;
            font-size: 1rem;
            width: 100%;
        }

        .login-container input[type="submit"] {
            background-color: #0072ff;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            padding: 0.75rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .login-container input[type="submit"]:hover {
            background-color: #005bb5;
        }

        .create-account {
            text-align: center;
            margin-top: 1rem;
        }

        .create-account a {
            color: #0072ff;
            text-decoration: none;
        }

        .create-account a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Login</h2>
    <form method="post" autocomplete="off">
        <label for="username_or_email">Username/Email:</label>
        <input type="text" id="username_or_email" name="username_or_email" required autocomplete="off">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required autocomplete="off">

        <input type="submit" value="Login">
    </form>

    <div class="create-account">
        <p>Don't have an account? <a href="signup.php">Create an Account</a></p>
    </div>

    <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
</div>
</body>
</html>
