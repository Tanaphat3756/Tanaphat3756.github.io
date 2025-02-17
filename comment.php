<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];
$comment = $_POST['comment'];

$conn->query("INSERT INTO comments (post_id, user_id, comment) VALUES ('$post_id', '$user_id', '$comment')");
header("Location: index.php");
exit();
?>
