<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// If not logged in, redirect to login page
if (!$loggedIn) {
    header('Location: login.php');
    exit;
}

// Return session status
echo json_encode([
    'loggedIn' => $loggedIn,
    'user_id' => $_SESSION['user_id']
]);
?>
