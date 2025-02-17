<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

// ตรวจสอบว่ากดถูกใจแล้วหรือยัง
$checkLike = $conn->query("SELECT * FROM likes WHERE post_id='$post_id' AND user_id='$user_id'");
if ($checkLike->num_rows > 0) {
    // ลบถูกใจ
    $conn->query("DELETE FROM likes WHERE post_id='$post_id' AND user_id='$user_id'");
} else {
    // เพิ่มถูกใจ
    $conn->query("INSERT INTO likes (post_id, user_id) VALUES ('$post_id', '$user_id')");
}

header("Location: index.php");
exit();
?>
