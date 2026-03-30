<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "shopy"; // You must replace this with your actual database name

// The variable name here ($conn) must exactly match the one used in index.php
$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>