<?php
include('connection.php');

header('Content-Type: application/json');

try {
    
    // Validate student_id
    if (empty($_POST['student_id'])) {
        throw new Exception("Student ID is required");
    }
    
    $studentId = $_POST['student_id'];
    
    $query = "DELETE FROM students WHERE student_id = :student_id";
    $statement = $connection->prepare($query);
    $statement->bindParam(':student_id', $studentId);
    
    if ($statement->execute()) {
        echo json_encode(['res' => 'success']);
    } else {
        throw new Exception("Delete failed");
    }
} catch (\PDOException $e) {
    echo json_encode(['res' => 'error', 'error' => $e->getMessage()]);
} catch (\Exception $e) {
    echo json_encode(['res' => 'error', 'error' => $e->getMessage()]);
}