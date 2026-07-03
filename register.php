<?php
require_once 'config.php';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password']; // SỬA TẠI ĐÂY: Lấy trực tiếp, không mã hóa hash nữa

    // Kiểm tra xem user đã tồn tại chưa
    $check = $db->select("SELECT id FROM users WHERE username = ?", [$username]);
    
    if (count($check) > 0) {
        $msg = "<span style='color:red;'>Tài khoản này đã tồn tại!</span>";
    } else {
        // Lưu thẳng chuỗi mật khẩu thô vào Database
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'user')";
        $affected = $db->execute($sql, [$username, $password]);
        
        if ($affected > 0) {
            $msg = "<span style='color:green;'>Đăng ký thành công! <a href='login.php'>Đăng nhập ngay</a></span>";
        } else {
            $msg = "<span style='color:red;'>Có lỗi xảy ra, vui lòng thử lại!</span>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>WordWise - Đăng Ký</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .auth-container { max-width: 400px; margin: 100px auto; background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .auth-btn { width: 100%; padding: 12px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body style="background: #f4f6fc;">
    <div class="auth-container">
        <form method="POST" action="">
            <h2 style="text-align:center; margin-bottom:20px; color:#1e1b4b;">Đăng Ký Tài Khoản</h2>
            <div style="text-align:center; margin-bottom:10px; font-weight:500;"><?= $msg ?></div>
            <div class="form-group">
                <label>Tên tài khoản mới</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="auth-btn">Đăng ký</button>
            <p style="margin-top:15px; text-align:center; font-size:14px;"><a href="login.php">Quay lại Đăng nhập</a></p>
        </form>
    </div>
</body>
</html>