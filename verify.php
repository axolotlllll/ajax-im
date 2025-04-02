<?php
require 'connection.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    try {
        $connection->beginTransaction();
        
        // First check if the code exists and is valid
        $checkStmt = $connection->prepare("
            SELECT user_id, student_id 
            FROM users 
            WHERE verification_code = :code 
            AND is_verified = 0
        ");
        $checkStmt->bindParam(':code', $code);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            // Verify the user
            $stmt = $connection->prepare("
                UPDATE users 
                SET verification_code = NULL, 
                    is_verified = 1 
                WHERE verification_code = :code
            ");
            $stmt->bindParam(':code', $code);
            $stmt->execute();
            
            $connection->commit();
            header("Location: login.php?verified=1");
        } else {
            header("Location: login.php?error=invalid_code");
        }
    } catch (PDOException $e) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        error_log("Verification error: " . $e->getMessage());
        header("Location: login.php?error=database_error");
    }
    exit();
} else {
    header("Location: login.php?error=no_code");
    exit();
}