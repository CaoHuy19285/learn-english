<?php session_start(); 
// Thêm vào dòng 2 của admin_vocab.php và admin_type.php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Từ vựng - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-layout">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="welcome-bar">
            <h1>Kho Quản Lý Từ Vựng</h1>
            <p>Thêm mới từ, chỉnh sửa phiên âm và đính kèm liên kết hình ảnh trực quan.</p>
        </div>

        <div class="admin-card">
            <h3>Thêm từ vựng mới hệ thống</h3>
            <form method="POST" class="grid-form">
                <div class="form-group">
                    <label>Từ vựng (English)</label>
                    <input type="text" name="word" placeholder="Ví dụ: Algorithm" required class="form-input">
                </div>
                <div class="form-group">
                    <label>Phiên âm IPA</label>
                    <input type="text" name="ipa" placeholder="Ví dụ: /ˈæl.ɡə.rɪ.ðəm/" required class="form-input">
                </div>
                <div class="form-group">
                    <label>Chủ đề danh mục</label>
                    <select name="typeword_id" class="form-input">
                        <option value="1">IT & Tech</option>
                        <option value="2">Arts & Culture</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Độ khó</label>
                    <select name="difficulty" class="form-input">
                        <option value="easy">Dễ</option>
                        <option value="medium" selected>Trung bình</option>
                        <option value="hard">Khó</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label>Nghĩa tiếng Việt</label>
                    <input type="text" name="definition" placeholder="Ví dụ: Thuật toán máy tính" required class="form-input">
                </div>
                <div class="form-group full-width">
                    <label>Đường dẫn ảnh minh họa (URL)</label>
                    <input type="url" name="image" placeholder="Nhập liên kết ảnh ví dụ: https://link-anh.png" class="form-input">
                </div>
                <div class="form-group full-width">
                    <label>Câu ví dụ (English)</label>
                    <textarea name="example" class="form-textarea" placeholder="The app uses a complex algorithm."></textarea>
                </div>
                <div class="form-actions full-width">
                    <button type="submit" class="btn-primary-action">Lưu từ vựng</button>
                </div>
            </form>
        </div>

        <div class="admin-card" style="margin-top: 24px;">
            <h3>Dữ liệu kho từ hiện tại</h3>
            <table class="data-table">
                <thead>
                    <tr><th>Hình ảnh</th><th>Từ vựng & IPA</th><th>Chủ đề</th><th>Định nghĩa / Ví dụ</th><th>Thao tác</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="table-img-container">
                                <img src="https://cdn-icons-png.flaticon.com/512/1792/1792183.png" alt="Algorithm">
                            </div>
                        </td>
                        <td>
                            <div class="word-main-title">Algorithm</div>
                            <div class="word-ipa-sub">/ˈæl.ɡə.rɪ.ðəm/</div>
                        </td>
                        <td><span class="badge purple">IT & Tech</span></td>
                        <td>
                            <div class="def-val">Thuật toán</div>
                            <div class="ex-val"><em>The app uses a complex algorithm.</em></div>
                        </td>
                        <td><a href="#" class="btn-edit">Sửa</a> <a href="#" class="btn-delete">Xóa</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php include 'footer.php'; ?>
    </main>
</body>
</html>