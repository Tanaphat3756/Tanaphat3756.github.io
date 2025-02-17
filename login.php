<?php
session_start();
require_once 'db.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

$error = ''; // ตัวแปรเก็บข้อความ error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์ม
    $username = $_POST['username'];
    $password = $_POST['password'];

    // เตรียมคำสั่ง SQL เพื่อป้องกัน SQL Injection
    $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE username = ?");

    // Check if prepare was successful
    if ($stmt === false) {
        $error = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $conn->error;
    } else {
        $stmt->bind_param("s", $username);
        $stmt->execute();

        // Check for execution errors
        if ($stmt->error) {
            $error = "เกิดข้อผิดพลาดในการ execute คำสั่ง SQL: " . $stmt->error;
        } else {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $db_username, $db_password_hash);
                $stmt->fetch();

                // ตรวจสอบรหัสผ่าน (ใช้ password_verify หากเก็บแบบ hashed)
                if (password_verify($password, $db_password_hash)) {
                    // เก็บข้อมูลผู้ใช้ใน session
                    $_SESSION['user_id']   = $id;
                    $_SESSION['username']  = $db_username;
                    // เปลี่ยนเส้นทางไปหน้า index เมื่อเข้าสู่ระบบสำเร็จ
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
                }
            } else {
                $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>หน้าเข้าสู่ระบบ</title>
    <style>
        /* ตัวอย่าง CSS เบื้องต้น */
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
            margin: 0; /* Reset default margin */
        }
        .login-container {
            background-color: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px; /* Added width */
        }
        .login-container h2 {
            margin-bottom: 15px;
            text-align: center; /* Center the heading */
        }
        .login-container label {
            display: block;
            margin-top: 10px;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Include padding in width */
        }
        .login-container button {
            margin-top: 15px;
            width: 100%;
            padding: 10px;
            background-color: #4285F4;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .login-container button:hover {
            background-color: #357ae8;
        }
        .error-message {
            color: red;
            margin-top: 10px;
            text-align: center; /* Center error messages */
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>เข้าสู่ระบบ</h2>
        <?php
            if (!empty($error)) {
                echo '<p class="error-message">'.$error.'</p>';
            }
        ?>
        <form action="" method="post">
            <label for="username">ชื่อผู้ใช้:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">รหัสผ่าน:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">เข้าสู่ระบบ</button>
        </form>

        <p class="register-link">
            ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a>
        </p>
    </div>
</body>
</html>