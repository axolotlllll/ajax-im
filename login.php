<?php

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
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
                
                header("Location: index.html");
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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 400px; margin-top: 100px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Login</h2>
        <?= $message ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
            <div class="text-center mt-3">
                <a href="register.php">Create an account</a>
            </div>
        </form>
    </div>
</body>
</html>