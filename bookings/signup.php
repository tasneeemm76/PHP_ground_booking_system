<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<p class='error'>Email already registered. Please use another email.</p>";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        if ($stmt->execute()) {
            header("Location: login.php");
            exit;
        } else {
            echo "<p class='error'>Signup failed. Please try again.</p>";
        }
        
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <style>
        /* CSS for the signup form */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            margin: 0;
        }

        .signup-container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .signup-container h2 {
            margin: 0;
            color: #333;
            text-align: center;
        }

        .signup-container form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .signup-container label {
            font-weight: bold;
            color: #555;
        }

        .signup-container input[type="text"],
        .signup-container input[type="email"],
        .signup-container input[type="password"] {
            padding: 0.75rem;
            border-radius: 4px;
            border: 1px solid #ddd;
            outline: none;
            font-size: 1rem;
            width: 100%;
        }

        .signup-container input[type="submit"] {
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

        .signup-container input[type="submit"]:hover {
            background-color: #005bb5;
        }

        .login-option {
            text-align: center;
            margin-top: 1rem;
        }

        .login-option a {
            color: #0072ff;
            text-decoration: none;
        }

        .login-option a:hover {
            text-decoration: underline;
        }

        .error, .success {
            text-align: center;
            color: red;
            margin-top: 1rem;
        }

        .success {
            color: green;
        }
    </style>
</head>
<body>
<div class="signup-container">
    <h2>Sign Up</h2>
    <form method="post" autocomplete="off">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required autocomplete="off">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required autocomplete="off">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required autocomplete="off">

        <input type="submit" value="Sign Up">
    </form>

    <div class="login-option">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>
</body>
</html>
