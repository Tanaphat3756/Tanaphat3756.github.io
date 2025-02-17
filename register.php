<?php
session_start();
require_once 'db.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์มและตัดช่องว่างออกด้านหน้า-ด้านหลัง
    $username         = trim($_POST['username']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบข้อมูลพื้นฐาน
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif (strlen($username) < 5 || strlen($username) > 20) {  // Added username length check
        $error = "ชื่อผู้ใช้ต้องมีความยาวระหว่าง 5 ถึง 20 ตัวอักษร";
    } elseif ($password !== $confirm_password) {
        $error = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    } elseif (strlen($password) < 6) {  // Add password length check
         $error = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
    }  else {
        // ตรวจสอบว่าชื่อผู้ใช้มีอยู่แล้วหรือไม่  ใช้ Prepared Statements
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        if ($stmt === false) {
            $error = "Database error: " . $conn->error; // More specific error
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();

             if ($stmt->error) { // Added execute error check
                $error = "Database error: " . $stmt->error;
             } else {
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $error = "ชื่อผู้ใช้นี้มีอยู่แล้ว กรุณาเลือกชื่ออื่น";
                } else {
                    $stmt->close(); // Close the statement before reusing $stmt

                    // เข้ารหัสรหัสผ่านด้วย password_hash()
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    // เตรียมคำสั่ง INSERT  ใช้ Prepared Statements
                    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                    if ($stmt === false) {
                         $error = "Database error: " . $conn->error;
                    } else {
                        $stmt->bind_param("ss", $username, $password_hash);

                        if ($stmt->execute()) {
                            // หากสมัครสมาชิกสำเร็จ ให้เปลี่ยนเส้นทางไปยังหน้า login.php
                            header("Location: login.php");
                            exit();
                        } else {
                            $error = "เกิดข้อผิดพลาดในการสมัครสมาชิก: " . $stmt->error; // More specific error
                        }
                    }
                }
             }

             if ($stmt) { // Ensure $stmt exists before closing.
               $stmt->close();
            }
        }
    }
    //$conn->close(); // Don't close the connection here; keep it open in case of redirection.
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>หน้าสมัครสมาชิก</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #6A1B9A, #4A148C); /* Purple gradient */
            color: #fff;
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .register-container {
            background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent white */
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 380px; /* Increased width */
            backdrop-filter: blur(10px); /* Add blur effect */
        }

        .register-container h2 {
            color: #4A148C; /* Dark purple heading */
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
        }

        .register-container label {
            display: block;
            margin-top: 15px;
            color: #333; /* Dark gray for labels */
            font-weight: 500;
        }

        .register-container input[type="text"],
        .register-container input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-top: 5px;
            border: 1px solid #9C27B0; /* Purple border */
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
            font-size: 16px;
        }
          .register-container input[type="text"]:focus,
        .register-container input[type="password"]:focus{
             border-color: #6A1B9A; /* Darker purple on focus */
             outline: none; /* Remove default focus outline */
        }
        .register-container button {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            background-color: #6A1B9A; /* Dark purple button */
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 16px;
            font-weight: 500;
        }

        .register-container button:hover {
            background-color: #4A148C; /* Even darker purple on hover */
        }

        .error-message {
            color: #E53935; /* Red for error messages */
            margin-top: 15px;
            text-align: center;
             font-size: 0.9em; /* Slightly smaller text */
        }

        .login-link {
           text-align: center;
            margin-top: 20px;
            color: #333;
        }
        .login-link a{
           color: #6A1B9A; /* Purple link */
            text-decoration: none;
            font-weight: 500;
        }
         .login-link a:hover{
              text-decoration: underline;
         }

    </style>
</head>
<body>
    <div class="register-container">
        <h2>สมัครสมาชิก</h2>
        <?php
            if (!empty($error)) {
                echo '<p class="error-message">' . $error . '</p>';
            }
        ?>
        <form action="" method="post">
            <label for="username">ชื่อผู้ใช้:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">รหัสผ่าน:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">ยืนยันรหัสผ่าน:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">สมัครสมาชิก</button>
        </form>
        <p class="login-link">
    มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a>
</p>
    </div>
</body>
</html>