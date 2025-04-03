<?php
session_start();
header('Content-Type: application/json');

$loggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

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
