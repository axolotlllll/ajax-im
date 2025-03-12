<?php
include('connection.php');

header('Content-Type: application/json');

try {
    $requiredFields = ['student_id', 'first_name', 'last_name', 'email', 'gender', 'course', 'birthdate'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }
    
    // Extract data from POST
    $studentId = $_POST['student_id'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $course = $_POST['course'];
    $userAddress = $_POST['address'] ?? null;
    $birthdate = $_POST['birthdate'];
    
    $checkQuery = "SELECT COUNT(*) FROM students WHERE email = :email AND student_id != :student_id";
    $checkStmt = $connection->prepare($checkQuery);
    $checkStmt->bindParam(':email', $email);
    $checkStmt->bindParam(':student_id', $studentId);
    $checkStmt->execute();
    
    if ($checkStmt->fetchColumn() > 0) {
        throw new Exception("Email already exists for another student");
    }
    
    $query = "UPDATE students 
              SET first_name = :first_name, 
                  last_name = :last_name, 
                  email = :email, 
                  gender = :gender, 
                  course = :course, 
                  user_address = :user_address, 
                  birthdate = :birthdate 
              WHERE student_id = :student_id";
              
    $statement = $connection->prepare($query);
    $statement->bindParam(':student_id', $studentId);
    $statement->bindParam(':first_name', $firstName);
    $statement->bindParam(':last_name', $lastName);
    $statement->bindParam(':email', $email);
    $statement->bindParam(':gender', $gender);
    $statement->bindParam(':course', $course);
    $statement->bindParam(':user_address', $userAddress);
    $statement->bindParam(':birthdate', $birthdate);
    
    if ($statement->execute()) {
        echo json_encode(['res' => 'success']);
    } else {
        throw new Exception("Update failed");
    }
} catch (\PDOException $e) {
    echo json_encode(['res' => 'error', 'error' => $e->getMessage()]);
} catch (\Exception $e) {
    echo json_encode(['res' => 'error', 'error' => $e->getMessage()]);
}