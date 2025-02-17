<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลผู้ใช้ (with error handling)
$user_result = $conn->query("SELECT * FROM users WHERE user_id='$user_id'");

if (!$user_result) {
    die("Error fetching user data: " . $conn->error);
}

$user = $user_result->fetch_assoc();

if (!$user) {
    die("User not found."); // Handle case where user ID doesn't exist
}

// แก้ไขโปรไฟล์
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];  // Get the username

    // Sanitize the username (important for security)
    $username = $conn->real_escape_string(trim($username));

    $profile_image = $user['profile_image']; // Default to existing image

    // Image Upload Handling
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = $_FILES['profile_image']['name'];
        $fileSize = $_FILES['profile_image']['size'];
        $fileType = $_FILES['profile_image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($fileExtension, $allowedfileExtensions)) {
             // directory in which the uploaded file will be moved
            $uploadFileDir = './uploads/';
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path))
            {
              $profile_image = $dest_path;
            }
            else
            {
              $message = 'There was some error moving the file to upload directory.  Please make sure the upload directory is writable by web server.';
              echo "<script>alert('$message');</script>";
            }
        } else {
            $message = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
            echo "<script>alert('$message');</script>";
            // Don't update $profile_image; keep the old one
        }
    } elseif ($_FILES['profile_image']['error'] != UPLOAD_ERR_NO_FILE) {
        // Handle other upload errors (e.g., file too large)
        $message = "File upload error: " . $_FILES['profile_image']['error'];
         echo "<script>alert('$message');</script>";
        // Don't update $profile_image
    }


    // Update database (only if username is not empty)
    if (!empty($username)) {
        $sql = "UPDATE users SET username=?, profile_image=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ssi", $username, $profile_image, $user_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $_SESSION['username'] = $username; // Update session username
                header("Location: profile.php"); // Redirect to profile page
                exit();
            } else {
                echo "Error updating profile: " . $stmt->error;  // Error handling
            }
            $stmt->close();
        } else {
             echo "Error preparing statement: " . $conn->error;
        }
    }
        else{
             echo "<script>alert('Username cannot be empty');</script>";
        }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>แก้ไขโปรไฟล์</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 500px;
             text-align: center; /* Center all content within the form */
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
              text-align: center;
        }

        label {
            display: block; /* Make labels block-level */
            margin-bottom: 5px;
            text-align: left; /* Align labels to the left */
        }

        input[type="text"],
        input[type="file"] {
            width: 100%; /* Take up full container width */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box; /* Include padding and border in element's total width/height */

        }

        .current-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 10px auto; /* Center the image */
            display: block;  /* Make the image a block-level element for centering */
            border: 2px solid #ddd;
        }


        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
             width: 100%;
            margin-bottom: 10px;
        }
        button[type="submit"]:hover {
              background-color: #45a049;
        }

        .cancel-link {
            display: block; /* Ensure the cancel link takes up the full width */
            text-align: center;  /* Center the text inside */
            color: #007bff;
            text-decoration: none;
            padding: 10px;
            border-radius: 4px;
        }
         .cancel-link:hover{
            background-color:#f0f0f0; /* Light grey background on hover */
         }
         .form-group {
            text-align: left;  /* Left-align the content within each form group */
            margin-bottom: 15px;
        }

    </style>
</head>
<body>
    <div class="form-container">
        <h2>แก้ไขโปรไฟล์</h2>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
            <label for="username">ชื่อผู้ใช้:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
            <label>รูปโปรไฟล์:</label><br>
            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Current Profile Image" class="current-image">
            <input type="file" name="profile_image" id="profile_image">
           </div>
            <button type="submit">บันทึก</button>
        </form>
         <a href="profile.php" class="cancel-link">ยกเลิก</a>
    </div>
</body>
</html>