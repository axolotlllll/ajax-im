<?php
header('Content-Type: application/json');
require 'connection.php';
$response = ['res' => 'error', 'message' => 'Unknown error occurred.'];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST["first_name"]);
    $lastName = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $gender = trim($_POST["gender"]);
    $course = trim($_POST["course"]);
    $uaddress = trim($_POST["address"]);
    $birthdate = trim($_POST["birthdate"]);
    $profileImagePath = null;


    // Image upload handling
    if (!empty($_FILES["profileImage"]["name"])) {
        $uploadDir = "profiles/"; 
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true); 
        }
        $imageName = time() . "_" . basename($_FILES["profileImage"]["name"]);
        $uploadFile = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $uploadFile)) {
            $profileImagePath = $uploadFile;
        } else {
            $response = ["res" => "error", "message" => "Image upload failed"];
            echo json_encode($response);
            exit;
        }
    }

    try {

        $checkEmail = "SELECT email FROM students WHERE email = :email";
        $checkStmt = $connection->prepare($checkEmail);
        $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            $response = ["res" => "error", "message" => "Email already exists"];
            echo json_encode($response);
            exit;
        }

        $query = "INSERT INTO students (first_name, last_name, email, gender, course, user_address, birthdate, profile_image)
                  VALUES (:first_name, :last_name, :email, :gender, :course, :uaddress, :birthdate, :profile_image)";
        $stmt = $connection->prepare($query);

        $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':uaddress', $uaddress, PDO::PARAM_STR);
        $stmt->bindParam(':birthdate', $birthdate, PDO::PARAM_STR);
        $stmt->bindParam(':profile_image', $profileImagePath, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $response = ["res" => "success", "message" => "User added successfully!", "image" => $profileImagePath];
        } else {
            $response = ["res" => "error", "message" => "Failed to add user"];
        }
    } catch (PDOException $e) {
        $response = ["res" => "error", "message" => "Database error: " . $e->getMessage()];
    }

    echo json_encode($response);
    exit;
}

exit;
