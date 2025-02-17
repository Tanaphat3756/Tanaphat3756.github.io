<?php
$servername = "fdb1028.awardspace.net";
$username = "4591737_bomby";
$password = "12345678zA.@"; 
$dbname = "4591737_bomby";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
