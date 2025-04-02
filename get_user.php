<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable direct error display
ob_start(); // Start output buffering
require 'auth_check.php';
include('connection.php');
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized access']));
}
try {
    $query = "SELECT * FROM students";  
    $statement = $connection->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    
 
    
    echo json_encode($result);
} catch (\PDOException $th) {
    error_log("Error fetching students: " . $th->getMessage());
    echo json_encode(['error' => 'Error fetching students: ' . $th->getMessage()]);
}