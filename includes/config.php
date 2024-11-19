<?php
$host = 'localhost';
$db_name = 'reservation_renuion';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
