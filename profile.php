<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลผู้ใช้
$user = $conn->query("SELECT * FROM users WHERE user_id='$user_id'")->fetch_assoc();

// ตรวจสอบว่า query สำเร็จหรือไม่
if (!$user) {
    echo "เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้: " . $conn->error;  // แสดงข้อผิดพลาดถ้ามี
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>โปรไฟล์ของคุณ</title>
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

        .profile-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 90%; /* Responsive width */
            max-width: 400px; /* Limit maximum width */
             text-align: center; /* Center align content */
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
             text-align: center;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover; /* Ensure the image covers the area */
            margin: 0 auto 20px; /* Center the image and add margin */
            display: block; /*  make it a block element */
            border: 4px solid #ddd;
        }

        .user-info {
            margin-bottom: 20px;
            text-align: center;
        }

        .user-info strong {
            font-weight: bold;
        }

        .links a {
            display: inline-block;
            margin: 5px 10px;
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            text-align: center;

        }

        .links a:hover {
            background-color: #0056b3;
        }
          .links {
            text-align: center; /* Center the links */
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>โปรไฟล์ของคุณ</h2>
        <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-image"><br>
        <div class="user-info">
            <strong>ชื่อผู้ใช้: </strong> <?php echo htmlspecialchars($user['username']); ?><br>
            </div>
        <div class="links">
            <a href="edit_profile.php">แก้ไขโปรไฟล์</a>
            <a href="index.php">กลับหน้าแรก</a>
        </div>
    </div>
</body>
</html>