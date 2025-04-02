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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 600px; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <div id="message"></div>
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
            <a href="login.php" class="btn btn-link">Already have an account? Login</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                type: 'POST',
                url: 'register.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#message').html('<div class="alert alert-success">' + response.message + '</div>');
                        $('#registerForm')[0].reset();
                    } else {
                        $('#message').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#message').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                }
            });
        });
    });
    </script>
</body>
</html>