<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "user_database";  

try {
    $connection = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (\PDOException $th) {
    die(json_encode(['error' => 'Database connection failed: ' . $th->getMessage()]));
}
?>