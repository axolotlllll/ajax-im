<?php
// Ensure no warnings/notices interfere with JSON output
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
header('Content-Type: application/json');
ob_clean(); // Clear any output before sending JSON

require 'connection.php';
require 'auth_check.php';

$response = ['res' => 'error', 'message' => 'Unknown error occurred.'];

try {
    // Validate required fields
    $requiredFields = ['student_id', 'first_name', 'last_name', 'email', 'gender', 'course', 'birthdate'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Extract data from POST
    $studentId  = $_POST['student_id'];
    $firstName  = $_POST['first_name'];
    $lastName   = $_POST['last_name'];
    $email      = $_POST['email'];
    $gender     = $_POST['gender'];
    $course     = $_POST['course'];
    $birthdate  = $_POST['birthdate'];

    // ✅ Fix: Check for empty input and preserve existing values if missing
    $userAddress = isset($_POST['user_address']) && trim($_POST['user_address']) !== "" ? trim($_POST['user_address']) : null;

    // Debugging: Log received user_address
    error_log("Received Address: " . ($userAddress ?? "NULL") . "\n", 3, "debug_log.txt");

    // Check if email already exists for another student
    $checkQuery = "SELECT COUNT(*) FROM students WHERE email = :email AND student_id != :student_id LIMIT 1";
    $checkStmt = $connection->prepare($checkQuery);
    $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);
    $checkStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->fetchColumn() > 0) {
        throw new Exception("Email already exists for another student");
    }

    // ✅ Fix: Preserve current address if new one is empty
    if (is_null($userAddress)) {
        $fetchAddressQuery = "SELECT user_address FROM students WHERE student_id = :student_id";
        $fetchAddressStmt = $connection->prepare($fetchAddressQuery);
        $fetchAddressStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $fetchAddressStmt->execute();
        $userAddress = $fetchAddressStmt->fetchColumn(); // Preserve existing address if null
    }

    // Debugging: Log final user_address before update
    error_log("Final Address Before Update: " . ($userAddress ?? "NULL") . "\n", 3, "debug_log.txt");

    // Create the update query
    $query = "UPDATE students 
    SET first_name = :first_name, 
        last_name = :last_name, 
        email = :email, 
        gender = :gender, 
        course = :course, 
        user_address = IF(:user_address IS NOT NULL, :user_address, user_address), 
        birthdate = :birthdate
    WHERE student_id = :student_id";


    $stmt = $connection->prepare($query);

    $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
    $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
    $stmt->bindParam(':course', $course, PDO::PARAM_STR);
    $stmt->bindParam(':user_address', $userAddress, PDO::PARAM_STR);
    $stmt->bindParam(':birthdate', $birthdate, PDO::PARAM_STR);
    $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        error_log("SQL Executed Successfully\n", 3, "debug_log.txt");

        // Fetch updated data to verify
        $fetchUpdatedQuery = "SELECT user_address FROM students WHERE student_id = :student_id";
        $fetchUpdatedStmt = $connection->prepare($fetchUpdatedQuery);
        $fetchUpdatedStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $fetchUpdatedStmt->execute();
        $updatedAddress = $fetchUpdatedStmt->fetchColumn();

        // Debugging: Log the updated address
        error_log("Updated Address in DB: " . ($updatedAddress ?? "NULL") . "\n", 3, "debug_log.txt");

        $response = [
            'res' => 'success',
            'message' => 'Student information updated successfully.',
            'updated_address' => $updatedAddress
        ];
    } else {
        throw new Exception("Update failed.");
    }
} catch (\PDOException $e) {
    error_log(date("[Y-m-d H:i:s]") . " Database Error: " . $e->getMessage() . "\n", 3, "error_log.txt");
    $response = ['res' => 'error', 'message' => 'Database error occurred.'];
} catch (\Exception $e) {
    error_log(date("[Y-m-d H:i:s]") . " General Error: " . $e->getMessage() . "\n", 3, "debug_log.txt");
    $response = ['res' => 'error', 'message' => $e->getMessage()];
}

echo json_encode($response);
exit;
?>
