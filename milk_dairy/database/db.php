<?php

$host = "localhost";
$user = "root";
$password = "DairyRoot@1234";
$database = "milk_dairy";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>