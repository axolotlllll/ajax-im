<?php
session_start();

// Check if user is authenticated
$authenticated = isset($_SESSION['user_id']) || isset($_SESSION['student_id']);

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'authenticated' => $authenticated,
    'session_details' => [
        'user_id_set' => isset($_SESSION['user_id']),
        'student_id_set' => isset($_SESSION['student_id'])
    ]
]);
?>