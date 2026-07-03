<?php
require_once 'config.php';
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Tìm tài khoản dựa trên username
    $sql = "SELECT * FROM users WHERE username = ?";
    $result = $db->select($sql, [$username]);

    if (count($result) > 0) {
        $user = $result[0]; 
        
        // So sánh trực tiếp 2 chuỗi (nếu bạn không dùng mã hóa hash)
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            // Gán dữ liệu mặc định để test nếu thiếu
            $_SESSION['streak'] = $user['streak'] ?? 7; 
            $_SESSION['xp'] = $user['xp'] ?? 400;

            // CHUYỂN HƯỚNG TẠI ĐÂY
            if ($user['role'] === 'admin') {
                header("Location: admin_vocab.php"); // Trang quản lý của admin
            } else {
                header("Location: learn.php"); // Trang học tập của user
            }
            exit();
        } else {
            $error = "Mật khẩu không chính xác!";
        }
    } else {
        $error = "Tài khoản không tồn tại!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>WordWise - Đăng Nhập</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .auth-container { max-width: 400px; margin: 100px auto; background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .auth-form h2 { margin-bottom: 20px; text-align: center; color: #1e1b4b; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .auth-btn { width: 100%; padding: 12px; background: #6366f1; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .auth-btn:hover { background: #4f46e5; }
        .error-msg { color: red; font-size: 14px; margin-bottom: 10px; text-align: center; }
    </style>
</head>
<body style="background: #f4f6fc;">
    <div class="auth-container">
        <form class="auth-form" method="POST" action="">
            <div style="text-align: center; margin-bottom: 20px;">
                <a href="index.php" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; color: #1e1b4b; font-size: 20px; font-weight: 700;">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#6366f1" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    WordWise
                </a>
            </div>
            <h2>Đăng Nhập</h2>
            <?php if($error): ?> <div class="error-msg"><?= $error ?></div> <?php endif; ?>
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" required placeholder="Nhập tài khoản">
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" required placeholder="Nhập mật khẩu">
            </div>
            <button type="submit" class="auth-btn">Đăng nhập</button>
            <p style="margin-top:15px; text-align:center; font-size:14px;">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
        </form>
    </div>
</body>
</html>