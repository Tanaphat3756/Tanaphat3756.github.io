<?php
$servername = "sql309.infinityfree.com";
$username = "if0_38297625";
$password = "Tvz1JUYtCr"; 
$dbname = "if0_38297625_root";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
