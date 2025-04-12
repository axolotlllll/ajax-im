<?php
session_start(); 

require 'connection.php';

$message = '';
if (isset($_GET['verified'])) {
    $message = '<div class="alert alert-success">Your email has been verified! Please login.</div>';
}
if (isset($_GET['error'])) {
    $errors = [
        'invalid_code' => 'Invalid or expired verification code.',
        'database_error' => 'Database error occurred.',
        'no_code' => 'No verification code provided.'
    ];
    $message = '<div class="alert alert-danger">' . ($errors[$_GET['error']] ?? 'An error occurred.') . '</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        // Check users table for credentials
        $stmt = $connection->prepare("
            SELECT u.*, s.first_name, s.last_name 
            FROM users u
            LEFT JOIN students s ON u.student_id = s.student_id
            WHERE u.email = :email
        ");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_verified']) {
                // Destroy any existing session first
                session_regenerate_id(true);
                
                // Clear any existing session data
                $_SESSION = [];

                // Set new session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                
                // Debugging: output session variables
                error_log("Login Session Variables: " . print_r($_SESSION, true));

                header("Location: dashboard.html");
                exit();
            } else {
                $message = '<div class="alert alert-warning">Please verify your email before logging in. Check your inbox for the verification link.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Invalid email or password.</div>';
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $message = '<div class="alert alert-danger">An error occurred during login. Please try again.</div>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff0055;
            --secondary-color: #00ff00;
            --accent-color: #00ffff;
            --background-dark: #1a1a1a;
            --background-light: #242424;
            --glow-color: rgba(255, 0, 85, 0.2);
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            background: url('uploads/1332485.jpeg') center/cover;
        }

        .container {
            max-width: 550px;
            margin-top: 50px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
            z-index: 2;
        }

        .login-form {
            padding: 30px;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 0, 85, 0.1);
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            animation: glow 2s infinite alternate;
            position: relative;
            z-index: 2;
        }

        .login-form h2 {
            margin-top: 0;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 10px var(--glow-color);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
            width: 80%;
            margin-left: 0;
            margin-right: auto;
        }

        .form-control {
            width: 100%;
            padding: 12px 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            font-size: 16px;
            transition: all 0.3s ease;
            max-width: 400px;
        }

        select.form-control {
            background: rgba(0, 0, 0, 0.2);
            color: var(--primary-color);
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='rgba(255,0,85,0.5)'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1rem;
            padding-right: 2.5rem;
        }

        select.form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 10px rgba(255, 0, 85, 0.3);
        }

        select.form-control option {
            background: var(--background-dark);
            color: var(--primary-color);
            padding: 10px;
        }

        select.form-control option:hover {
            background: rgba(255, 0, 85, 0.1);
        }

        .btn-primary {
            width: 80%;
            padding: 12px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 8px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-shadow: 0 0 5px var(--glow-color);
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
            display: block;
        }

        .register-link {
            margin-top: 20px;
            text-align: center;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: var(--secondary-color);
            text-shadow: 0 0 5px var(--glow-color);
        }

        @keyframes glow {
            from {
                box-shadow: 0 0 10px rgba(255, 0, 85, 0.1);
            }
            to {
                box-shadow: 0 0 20px rgba(255, 0, 85, 0.2);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-form">
            <div id="message"><?php echo $message; ?></div>
            <h2>Student Management System</h2>
            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <div class="register-link">
                <a href="register.php">Register New Account</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>