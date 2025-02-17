<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ลบโพสต์
if (isset($_GET['post_id'])) { // Changed from delete_post to post_id
    $post_id_to_delete = $_GET['post_id'];

    // Check ownership *before* deleting.
    $check_ownership_sql = "SELECT user_id, image FROM posts WHERE post_id = '$post_id_to_delete'";
    $ownership_result = $conn->query($check_ownership_sql);

    if ($ownership_result && $ownership_result->num_rows > 0) {
        $owner = $ownership_result->fetch_assoc();

        if ($owner['user_id'] != $user_id) {
            echo "You are not authorized to delete this post.";
            exit();
        }

        // Delete associated image file
        if ($owner['image']) {
            if (file_exists($owner['image'])) { // Check if file exists
                unlink($owner['image']); // Delete the file
            }
        }

        // Delete likes associated with the post
        $delete_likes_sql = "DELETE FROM likes WHERE post_id = '$post_id_to_delete'";
        $conn->query($delete_likes_sql);

        // Delete comments associated with the post
        $delete_comments_sql = "DELETE FROM comments WHERE post_id = '$post_id_to_delete'";
        $conn->query($delete_comments_sql);

        // Delete the post
        $delete_post_sql = "DELETE FROM posts WHERE post_id = '$post_id_to_delete'";
        if ($conn->query($delete_post_sql) === TRUE) {
            header("Location: index.php");
            exit();
        } else {
            echo "Error deleting post: " . $conn->error; // Handle potential errors
            exit();
        }
    } else {
        echo "Error checking post ownership (delete): " . $conn->error;
        exit();
    }
} else { // Add an else here to handle the case where no post_id is set
  echo "No post ID specified for deletion.";
  exit();
}
?>