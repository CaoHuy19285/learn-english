<?php
require_once 'config.php';

// Kiểm tra quyền truy cập: Chỉ admin mới được vào
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Truy cập bị từ chối! Bạn không phải là quản trị viên.");
}

// Xử lý Thêm Từ Mới (Dùng Prepared Statements để chống lỗi ký tự đặc biệt như dấu nháy đơn)
if (isset($_POST['add_vocab'])) {
    $word = trim($_POST['word']);
    $ipa = trim($_POST['ipa']);
    $definition = trim($_POST['definition']);
    $example = trim($_POST['example']);
    $category = trim($_POST['category']);
    $difficulty = trim($_POST['difficulty']);

    $sql = "INSERT INTO vocabulary (word, ipa, definition, example, category, difficulty) VALUES (?, ?, ?, ?, ?, ?)";
    $db->execute($sql, [$word, $ipa, $definition, $example, $category, $difficulty]);
    
    // Refresh trang để tránh gửi lại form khi F5
    header("Location: admin.php");
    exit();
}

// Xử lý Xóa Từ
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->execute("DELETE FROM vocabulary WHERE id = ?", [$id]);
    header("Location: admin.php");
    exit();
}

// Lấy danh sách từ vựng hiện tại
$vocab_list = $db->select("SELECT * FROM vocabulary ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>WordWise - Quản Trị Từ Vựng</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6fc; margin:0; padding: 20px; }
        .admin-box { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow:0 4px 6px rgba(0,0,0,0.05); }
        .header-panel { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px;}
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        input, textarea, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-submit { padding: 12px 20px; background: #4f46e5; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        .btn-delete { color: red; text-decoration: none; font-weight: bold; padding: 5px 10px; border-radius: 4px; background: #fee2e2; }
    </style>
</head>
<body>
    <div class="admin-box">
        <div class="header-panel">
            <h2>Quản Trị Hệ Thống (ADMIN)</h2>
            <div>
                <a href="index.php" style="margin-right: 15px; text-decoration:none; color:#4f46e5; font-weight:bold;">Xem Bảng Điều Khiển</a>
                <a href="logout.php" style="color:red; text-decoration:none;">Đăng xuất</a>
            </div>
        </div>

        <form method="POST" action="">
            <h3>Thêm từ vựng mới</h3>
            <div class="form-grid">
                <div>
                    <label>Từ tiếng Anh</label>
                    <input type="text" name="word" required placeholder="Ví dụ: Repository">
                </div>
                <div>
                    <label>Phiên âm (IPA)</label>
                    <input type="text" name="ipa" required placeholder="Ví dụ: /rɪˈpɒz.ɪ.tər.i/">
                </div>
                <div style="grid-column: span 2;">
                    <label>Ý nghĩa tiếng Việt</label>
                    <textarea name="definition" rows="2" required placeholder="Ví dụ: Kho lưu trữ mã nguồn..."></textarea>
                </div>
                <div style="grid-column: span 2;">
                    <label>Câu ví dụ áp dụng</label>
                    <input type="text" name="example" placeholder="Ví dụ: Push your code changes to the repository.">
                </div>
                <div>
                    <label>Chuyên ngành / Chủ đề</label>
                    <select name="category">
                        <option value="IT & Tech">Công Nghệ Thông Tin (IT)</option>
                        <option value="Arts & Culture">Nghệ Thuật & Văn Hóa</option>
                        <option value="Business">Kinh Tế Thương Mại</option>
                    </select>
                </div>
                <div>
                    <label>Độ khó</label>
                    <select name="difficulty">
                        <option value="Dễ">Dễ</option>
                        <option value="Trung bình" selected>Trung bình</option>
                        <option value="Khó">Khó</option>
                    </select>
                </div>
            </div>
            <button type="submit" name="add_vocab" class="btn-submit">+ Thêm Từ Vựng</button>
        </form>

        <h3 style="margin-top:40px;">Kho từ vựng hiện tại (<?= count($vocab_list) ?> từ)</h3>
        <table>
            <thead>
                <tr>
                    <th>Từ vựng</th>
                    <th>Phiên âm</th>
                    <th>Ý nghĩa</th>
                    <th>Chủ đề</th>
                    <th>Độ khó</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vocab_list as $row): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['word']) ?></strong></td>
                    <td><span style="color:#6b7280;"><?= htmlspecialchars($row['ipa']) ?></span></td>
                    <td><?= htmlspecialchars($row['definition']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['difficulty']) ?></td>
                    <td>
                        <a href="admin.php?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa từ này?')">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>