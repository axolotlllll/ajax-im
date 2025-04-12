<?php
include('connection.php');

try {
    $query = "SELECT user_id, first_name, last_name, course, user_address, is_verified FROM users";  
    $statement = $connection->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($result);
} catch (\PDOException $th) {
    error_log("Error fetching users: " . $th->getMessage());
    echo json_encode(['error' => 'Error fetching users: ' . $th->getMessage()]);
}
?>