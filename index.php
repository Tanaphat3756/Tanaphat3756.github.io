<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// เพิ่มโพสต์ใหม่
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_content'])) {
    $content = $_POST['post_content'];
    $image = "";

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $image_name = uniqid() . "." . $imageFileType;
        $target_file = $target_dir . $image_name;

        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            echo "File is not an image.";
            exit();
        }
        if ($_FILES["image"]["size"] > 5000000) {
            echo "Sorry, your file is too large.";
            exit();
        }
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            exit();
        }

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file;
        } else {
            echo "Sorry, there was an error uploading your file.";
            exit();
        }
    }

    $content = mysqli_real_escape_string($conn, $content);
    $image = mysqli_real_escape_string($conn, $image);

    $sql = "INSERT INTO posts (user_id, content, image) VALUES ('$user_id', '$content', '$image')";
    if (!$conn->query($sql)) {
        echo "Error: " . $sql . "<br>" . $conn->error;
        exit();
    }
    header("Location: index.php");
    exit();
}

// --- Edit Post Logic ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_post_id'])) {
    $edit_post_id = $_POST['edit_post_id'];
    $new_content = $_POST['edit_content'];
    $new_content = mysqli_real_escape_string($conn, $new_content);

    // Check ownership
    $check_ownership_sql = "SELECT user_id FROM posts WHERE post_id = '$edit_post_id'";
    $ownership_result = $conn->query($check_ownership_sql);
    if ($ownership_result && $ownership_result->num_rows > 0) {
        $owner = $ownership_result->fetch_assoc();
        if ($owner['user_id'] != $user_id) {
            echo "You are not authorized to edit this post.";
            exit();
        }
    } else {
        echo "Error checking post ownership: " . $conn->error;
        exit();
    }

    $update_sql = "UPDATE posts SET content = '$new_content' WHERE post_id = '$edit_post_id'";
    if ($conn->query($update_sql) === TRUE) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error updating post: " . $conn->error;
        exit();
    }
}
// --- End Edit Post Logic ---

// --- Delete Post Logic ---
if (isset($_GET['delete_post'])) {
    $post_id_to_delete = $_GET['delete_post'];
    $check_ownership_sql = "SELECT user_id, image FROM posts WHERE post_id = '$post_id_to_delete'";
    $ownership_result = $conn->query($check_ownership_sql);
    if ($ownership_result && $ownership_result->num_rows > 0) {
        $owner = $ownership_result->fetch_assoc();
        if ($owner['user_id'] != $user_id) {
            echo "You are not authorized to delete this post.";
            exit();
        }
        if ($owner['image']) {
            if (file_exists($owner['image'])) {
                unlink($owner['image']);
            }
        }
        $delete_likes_sql = "DELETE FROM likes WHERE post_id = '$post_id_to_delete'";
        $conn->query($delete_likes_sql);
        $delete_comments_sql = "DELETE FROM comments WHERE post_id = '$post_id_to_delete'";
        $conn->query($delete_comments_sql);
        $delete_post_sql = "DELETE FROM posts WHERE post_id = '$post_id_to_delete'";
          if ($conn->query($delete_post_sql) === false) {
             echo "Error deleting post: " . $conn->error;
           exit();
         }
          header("Location: index.php");
          exit();

    } else {
        echo "Error checking post ownership (delete): " . $conn->error;
        exit();
    }
}
// --- End Delete Post Logic ---

// --- Delete comment ---
if (isset($_GET['delete_comment'])) {
    $comment_id_to_delete = $_GET['delete_comment'];

     // Check comment ownership before deleting
    $check_comment_ownership = "SELECT user_id FROM comments WHERE comment_id = '$comment_id_to_delete'";
    $ownership_result = $conn->query($check_comment_ownership);

    if($ownership_result && $ownership_result->num_rows > 0){
        $owner = $ownership_result->fetch_assoc();

        if($owner['user_id'] != $user_id){
            echo "You are not authorised to delete this comment";
            exit();
        }

        // Delete the comment
        $delete_comment_sql = "DELETE FROM comments WHERE comment_id = '$comment_id_to_delete'";
         if (!$conn->query($delete_comment_sql)) {
              echo "Error deleting comment: " . $conn->error;
              exit();
         }
             header("Location: index.php");
             exit();

    } else {
         echo "Error checking comment ownership: " . $conn->error;
        exit();
    }

}
// --- Fetch User's Profile Image ---
$profile_image_query = "SELECT profile_image FROM users WHERE user_id = '$user_id'"; // Corrected query
$profile_image_result = $conn->query($profile_image_query);

if ($profile_image_result && $profile_image_result->num_rows > 0) {
    $profile_image_data = $profile_image_result->fetch_assoc();
    $profile_image = $profile_image_data['profile_image'];  // Corrected variable name

    $_SESSION['profile_image'] = $profile_image;
} else {
      $_SESSION['profile_image'] = 'default_profile.jpg'; // Use a default image path
}
// --- End Fetch User's Profile Image ---

?>

<!DOCTYPE html>
<html>
<head>
    <title>หน้าแรก</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h2, h3 {
            color: #1c1e21;
        }
        textarea {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #dddfe2;
            border-radius: 4px;
            resize: vertical;
            box-sizing: border-box;
        }
        input[type="file"] {
            margin-bottom: 10px;
        }
        button[type="submit"] {
            background-color: #1877f2;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button[type="submit"]:hover {
            background-color: #166fe5;
        }
        .post, .comment {
            border: 1px solid #dddfe2;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #fff;
            border-radius: 8px;
        }
        .post-header, .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
         .post-header img, .comment-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .post p {
            margin: 5px 0 10px 0;
            color: #1c1e21;
            line-height: 1.4;
        }
        .actions a {
            margin-right: 10px;
            text-decoration: none;
            color: #1877f2;
            font-size: 0.9em;
        }
        .actions a:hover {
            text-decoration: underline;
        }
        .comment {
            margin-left: 20px;
            background-color: #f0f2f5;
        }
        .comment p {
            color: #333;
        }
        hr {
            border: 0;
            height: 1px;
            background-color: #dddfe2;
            margin: 20px 0;
        }

        .post-stats {
          font-size: 0.9em;
          color: #65676b;
          margin-bottom: 5px;
        }

        .comment-form {
          display: flex;
          margin-top: 5px;
          align-items: center;
        }

        .comment-form input[type="text"] {
          flex-grow: 1;
          padding: 8px;
          border: 1px solid #dddfe2;
          border-radius: 4px;
          margin-right: 5px;
        }
      .logout-profile-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .edit-form {
        margin-top: 10px;
        border: 1px solid #ddd;
        padding: 10px;
        background-color: #f9f9f9;
    }
    </style>
</head>
<body>
    <div class="container">
        <h2>ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>

        <div class="logout-profile-container">
        <a href="logout.php" style="text-decoration: none;">
    <img src="12.png" alt="Logout" style="width: 32px; height: 32px; vertical-align: middle;">
    <span style="vertical-align: middle;"></span>
</a>
            <a href="profile.php">
                <img src="<?php echo htmlspecialchars($profile_image); ?>" style="width: 40px; height: 40px; border-radius: 50%;" alt="Profile Picture">
            </a>
        </div>

        <form method="post" enctype="multipart/form-data">
            <textarea name="post_content" placeholder="คุณคิดอะไรอยู่?" required></textarea><br>
            <input type="file" name="image"><br>
            <button type="submit">โพสต์</button>
        </form>
        <hr>

        <h3>โพสต์ล่าสุด</h3>
        <?php
        // Corrected query to use profile_image
        $posts = $conn->query("SELECT posts.*, users.username, users.profile_image, posts.user_id AS post_user_id
                                FROM posts
                                JOIN users ON posts.user_id = users.user_id
                                ORDER BY posts.created_at DESC");

        if (!$posts) {
            die("Error fetching posts: " . $conn->error);
        }

        while ($post = $posts->fetch_assoc()) {
            echo "<div class='post'>";
                // header ของโพสต์ (รูปและชื่อผู้ใช้)
                echo "<div class='post-header'>";
                // Corrected: use profile_image here
                echo "<img src='" . htmlspecialchars($post['profile_image']) . "' alt='Profile'>";
                echo "<strong>" . htmlspecialchars($post['username']) . "</strong>";
                echo "</div>";

                // เนื้อหาโพสต์
                echo "<p>" . htmlspecialchars($post['content']) . "</p>";
                if ($post['image']) {
                    echo "<img src='" . htmlspecialchars($post['image']) . "' style='max-width: 100%; height: auto;'><br>";
                }

                // Edit and Delete Links
                if ($post['post_user_id'] == $user_id) {
                    echo "<div class='actions'>";
                        echo "<a href='?edit_post=" . $post['post_id'] . "'>แก้ไข</a> | ";
                        echo "<a href='?delete_post=" . $post['post_id'] . "' onclick='return confirm(\"คุณต้องการลบโพสต์นี้หรือไม่?\")'>ลบ</a>";
                    echo "</div>";
                }
                // Edit form
                  if (isset($_GET['edit_post']) && $_GET['edit_post'] == $post['post_id']) {
                    echo "<div class='edit-form'>";
                    echo "<form method='post'>";
                    echo "<input type='hidden' name='edit_post_id' value='" . $post['post_id'] . "'>";
                    echo "<textarea name='edit_content' required>" . htmlspecialchars($post['content']) . "</textarea><br>";
                    echo "<button type='submit'>บันทึก</button>";
                    echo "</form>";
                    echo "</div>";
                }

              // Like Count and Form
              $post_id = $post['post_id'];
              $like_count = $conn->query("SELECT COUNT(*) AS count FROM likes WHERE post_id='$post_id'")->fetch_assoc()['count'];
              $comment_count = $conn->query("SELECT COUNT(*) AS count FROM comments WHERE post_id='$post_id'")->fetch_assoc()['count'];
                echo "<p class='post-stats'>$like_count ถูกใจ | $comment_count ความคิดเห็น</p>";

                echo "<form method='post' action='like.php' style='display:inline;'>";
                echo "<input type='hidden' name='post_id' value='" . $post['post_id'] . "'>";
                echo "<button type='submit'>ถูกใจ</button>";
                echo "</form>";

                // Comment Form
              echo "<form class='comment-form' method='post' action='comment.php'>";
              echo "<input type='hidden' name='post_id' value='" . $post['post_id'] . "'>";
              echo "<input type='text' name='comment' placeholder='แสดงความคิดเห็น...' required>";
              echo "<button type='submit'>ส่ง</button>";
              echo "</form>";

                // Display Comments
                // Corrected: use profile_image here too
                $comments = $conn->query("SELECT comments.*, users.username, users.profile_image
                                        FROM comments
                                        JOIN users ON comments.user_id = users.user_id
                                        WHERE post_id='$post_id'
                                        ORDER BY comments.created_at ASC");

                if ($comments && $comments->num_rows > 0) {
                  while ($comment = $comments->fetch_assoc()) {
                      echo "<div class='comment'>";
                          echo "<div class='comment-header'>";
                          // Corrected: use profile_image here
                              echo "<img src='" . htmlspecialchars($comment['profile_image']) . "' alt='Profile'>";
                              echo "<strong>" . htmlspecialchars($comment['username']) . "</strong>";
                          echo "</div>";
                          echo "<p>" . htmlspecialchars($comment['comment']) . "</p>";

                           if ($comment['user_id'] == $user_id) {
                              echo "<div class='actions'>";
                                  echo "<a href='?edit_comment=" . $comment['comment_id'] . "'>แก้ไข</a>";
                                  echo "<a href='?delete_comment=" . $comment['comment_id'] . "' onclick='return confirm(\"คุณต้องการลบคอมเมนต์นี้หรือไม่?\")'>ลบ</a>";
                              echo "</div>";
                          }
                      echo "</div>";
                  }
                }

            echo "</div>"; // Close post div
        }
        ?>
    </div>