<?php
require 'vendor/autoload.php';
require 'connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');

    // Extract and sanitize input
    $firstName = htmlspecialchars($_POST['first_name']);
    $lastName = htmlspecialchars($_POST['last_name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $gender = htmlspecialchars($_POST['gender']);
    $course = htmlspecialchars($_POST['course']);
    $address = isset($_POST['address']) ? htmlspecialchars($_POST['address']) : null;
    $birthdate = $_POST['birthdate'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $verification_code = bin2hex(random_bytes(16));

    try {
        $connection->beginTransaction();

        // Check if email already exists in users table
        $checkStmt = $connection->prepare("SELECT email FROM users WHERE email = :email");
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
            exit;
        }

        // Insert user directly into users table (without linking to students table)
        $userStmt = $connection->prepare("
            INSERT INTO users 
            (first_name, last_name, email, password, gender, course, user_address, birthdate, verification_code) 
            VALUES 
            (:first_name, :last_name, :email, :password, :gender, :course, :address, :birthdate, :verification_code)
        ");

        $userStmt->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':password' => $password,
            ':gender' => $gender,
            ':course' => $course,
            ':address' => $address,
            ':birthdate' => $birthdate,
            ':verification_code' => $verification_code
        ]);

        // Send verification email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'fattynigel@gmail.com';
            $mail->Password = 'ckhe oxpc xsuc ipox';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('macmacdawaton@gmail.com', 'Student System');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            $mail->Body = "Please click the link below to verify your email address:<br><br>
                         <a href='http://localhost/ajax-im-main/verify.php?code=$verification_code'>Verify Email Address</a>";

            $mail->send();
            $connection->commit();
            echo json_encode(['status' => 'success', 'message' => 'Registration successful! Please check your email to verify your account.']);
        } catch (Exception $e) {
            $connection->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Mail error: ' . $mail->ErrorInfo]);
        }
    } catch (PDOException $e) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
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

        .register-form {
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

        .register-form h2 {
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

        .login-link {
            margin-top: 20px;
            text-align: center;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
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
        <div class="register-form">
            <div id="message"></div>
            <h2>Student Management System</h2>
            <form id="registerForm">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" class="form-control" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                        <option value="Prefer not to say">Prefer not to say</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Course</label>
                    <input type="text" name="course" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control">
                </div>
                <div class="form-group">
                    <label>Birthdate</label>
                    <input type="date" name="birthdate" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
                <div class="login-link">
                    <a href="login.php" class="btn btn-link">Already have an account? Login</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="main.js"></script>
</body>
</html>