<?php
require_once 'database.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.";
    } else {
        // Kết nối database
        $db = new Database();
        $sql = "SELECT * FROM users WHERE username = ?";
        $result = $db->select($sql, [$username]);

        if (count($result) > 0) {
            $user = $result[0];
            
            // So sánh mật khẩu (plain text)
            if ($password === $user['password']) {
                // Lưu session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = !empty($user['full_name']) ? $user['full_name'] : 'Người dùng';
                $_SESSION['avatar'] = !empty($user['avatar']) ? $user['avatar'] : 'default_avatar.png';
                $_SESSION['streak'] = isset($user['streak']) ? $user['streak'] : 0;
                $_SESSION['xp'] = isset($user['xp']) ? $user['xp'] : 0;

                // TẤT CẢ ĐỀU CHUYỂN VỀ DASHBOARD
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Mật khẩu không chính xác!";
            }
        } else {
            $error = "Tài khoản không tồn tại!";
        }
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
        * { box-sizing: border-box; margin:0; padding:0; }
        body { font-family: 'Inter', sans-serif; background: #faf5ff; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .auth-container { width: 100%; max-width: 420px; background: white; padding: 40px; border-radius: 24px; box-shadow: 0 20px 40px rgba(168,85,247,0.08); border: 1px solid #e9d5ff; }
        .auth-logo { text-align: center; margin-bottom: 24px; }
        .auth-logo a { text-decoration: none; display: inline-flex; align-items: center; gap: 8px; color: #1e293b; font-size: 24px; font-weight: 800; font-family: 'Nunito', sans-serif; }
        .auth-logo a svg { stroke: #a855f7; }
        .auth-form h2 { font-size: 24px; font-weight: 800; color: #1e293b; text-align: center; margin-bottom: 24px; font-family: 'Nunito', sans-serif; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-weight: 600; color: #1e293b; margin-bottom: 6px; font-size: 14px; }
        .form-group input { width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; font-weight: 500; transition: 0.2s; background: #f8fafc; }
        .form-group input:focus { border-color: #a855f7; outline: none; background: white; box-shadow: 0 0 0 4px rgba(168,85,247,0.1); }
        .auth-btn { width: 100%; padding: 14px; background: #a855f7; color: white; border: none; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer; transition: 0.15s; box-shadow: 0 4px 0 #9333ea; }
        .auth-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 0 #9333ea; }
        .auth-btn:active { transform: translateY(4px); box-shadow: none; }
        .error-msg { background: #fef2f2; color: #991b1b; padding: 10px 14px; border-radius: 10px; font-weight: 600; font-size: 14px; text-align: center; margin-bottom: 16px; border: 1px solid #fecaca; }
        .auth-footer { margin-top: 16px; text-align: center; font-size: 14px; color: #64748b; }
        .auth-footer a { color: #a855f7; font-weight: 600; text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-logo">
            <a href="index.php">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                WordWise
            </a>
        </div>

        <form class="auth-form" method="POST" action="">
            <h2>Đăng nhập</h2>

            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" placeholder="Nhập tài khoản" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>

            <button type="submit" class="auth-btn">Đăng nhập</button>

            <div class="auth-footer">
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </div>
        </form>
    </div>
</body>
</html>