<?php session_start(); 
// Thêm vào dòng 2 của admin_vocab.php và admin_type.php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
} ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Loại từ - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="welcome-bar">
            <h1>Quản lý Chủ Đề / Loại Từ</h1>
            <p>Hệ thống cấu hình danh mục bài học trực quan.</p>
        </div>

        <div class="admin-card">
            <h3>Thêm loại từ mới</h3>
            <form method="POST" class="inline-form">
                <input type="text" name="name" placeholder="Tên loại từ (Ví dụ: IT & Tech)" required class="form-input">
                <select name="color_theme" class="form-input">
                    <option value="purple">Màu Tím (IT & Tech)</option>
                    <option value="pink">Màu Hồng (Arts & Culture)</option>
                    <option value="green">Màu Xanh lá (Business)</option>
                </select>
                <button type="submit" class="btn-primary-action">Thêm mới</button>
            </form>
        </div>

        <div class="admin-card" style="margin-top: 24px;">
            <h3>Danh sách các danh mục</h3>
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Tên danh mục</th><th>Slug URL</th><th>Theme Màu</th><th>Hành động</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td><td><strong>IT & Tech</strong></td><td>it-tech</td>
                        <td><span class="badge purple">purple</span></td>
                        <td><a href="#" class="btn-edit">Sửa</a> <a href="#" class="btn-delete">Xóa</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php include 'footer.php'; ?>
    </main>
</body>
</html>