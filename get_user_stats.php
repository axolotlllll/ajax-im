<?php
require 'connection.php';

try {
    // Get total users
    $totalQuery = $connection->prepare("SELECT COUNT(*) as total FROM users");
    $totalQuery->execute();
    $total = $totalQuery->fetch(PDO::FETCH_ASSOC)['total'];

    // Get verified users
    $verifiedQuery = $connection->prepare("SELECT COUNT(*) as verified FROM users WHERE is_verified = 1");
    $verifiedQuery->execute();
    $verified = $verifiedQuery->fetch(PDO::FETCH_ASSOC)['verified'];

    echo json_encode([
        'total_users' => $total,
        'verified_users' => $verified
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>