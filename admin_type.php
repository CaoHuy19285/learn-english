<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$db = new Database();
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Xóa chủ đề
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    $check_vocab = $db->select("SELECT COUNT(*) as total FROM vocabulary WHERE typeword_id = ?", [$delete_id]);
    if (!empty($check_vocab) && $check_vocab[0]['total'] > 0) {
        header("Location: admin_type.php?error=" . urlencode("Không thể xóa chủ đề này vì có chứa ". $check_vocab[0]['total'] ." từ vựng!"));
        exit();
    }
    $affected = $db->execute("DELETE FROM typeword WHERE id = ?", [$delete_id]);
    if ($affected > 0) {
        header("Location: admin_type.php?msg=" . urlencode("Xóa chủ đề thành công!"));
    } else {
        header("Location: admin_type.php?error=" . urlencode("Xóa thất bại!"));
    }
    exit();
}

// Tìm kiếm & phân trang
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(*) as total FROM typeword";
$count_params = [];
if ($search !== '') {
    $count_sql .= " WHERE name LIKE ? OR slug LIKE ?";
    $count_params = ["%$search%", "%$search%"];
}
$total_result = $db->select($count_sql, $count_params);
$total = $total_result[0]['total'] ?? 0;
$total_pages = ceil($total / $limit);
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM typeword";
if ($search !== '') {
    $sql .= " WHERE name LIKE ? OR slug LIKE ?";
    $params = ["%$search%", "%$search%"];
} else {
    $params = [];
}
$sql .= " ORDER BY id ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types = $db->select($sql, $params);

// Xây dựng mảng trang hiển thị
$page_range = [];
if ($total_pages > 0) {
    $page_range = [1];
    if ($total_pages >= 8) {
        if ($total_pages >= 2) $page_range[] = 2;
        if ($total_pages >= 3) $page_range[] = 3;
        if ($page > 4) $page_range[] = '...';
        $left = max(4, $page - 2);
        $right = min($total_pages - 3, $page + 2);
        for ($i = $left; $i <= $right; $i++) {
            if (!in_array($i, $page_range) && $i > 3 && $i < $total_pages - 2) {
                $page_range[] = $i;
            }
        }
        if ($page < $total_pages - 3) {
            if (end($page_range) !== '...') $page_range[] = '...';
        }
        if ($total_pages - 2 > 3 && !in_array($total_pages - 2, $page_range)) $page_range[] = $total_pages - 2;
        if ($total_pages - 1 > 3 && !in_array($total_pages - 1, $page_range)) $page_range[] = $total_pages - 1;
        if (!in_array($total_pages, $page_range)) $page_range[] = $total_pages;
    } else {
        for ($i = 2; $i <= $total_pages; $i++) {
            $page_range[] = $i;
        }
    }
    $page_range = array_values(array_unique($page_range, SORT_NUMERIC));
}

$color_map = [
    'purple' => '#a855f7',
    'pink'   => '#ec4899',
    'green'  => '#22c55e',
    'indigo' => '#6366f1',
    'orange' => '#f97316',
    'red'    => '#ef4444',
    'blue'   => '#3b82f6',
    'yellow' => '#eab308',
    'teal'   => '#14b8a6',
    'cyan'   => '#06b6d4',
    'gray'   => '#6b7280',
    'lime'   => '#84cc16',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Chủ đề - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .search-add-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px; flex-wrap: wrap; }
        .search-form { display: flex; gap: 10px; flex: 1; max-width: 500px; align-items: center; }
        .alert { padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .btn-add { background: #4f46e5; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; white-space: nowrap; }
        .btn-add:hover { background: #4338ca; }
        
        /* Nút Import Excel - viền tím, nền trắng, chữ tím */
        .btn-import-excel {
            background: white;
            color: #4f46e5;
            border: 2px solid #4f46e5;
            padding: 9px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-import-excel:hover {
            background: #e0e7ff;
            border-color: #4338ca;
            color: #4338ca;
        }
        .btn-import-excel svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        
        .btn-edit, .btn-delete { padding: 4px 12px; border-radius: 6px; font-size: 13px; text-decoration: none; font-weight: 500; border: none; cursor: pointer; }
        .btn-edit { background: #e0e7ff; color: #4f46e5; }
        .btn-edit:hover { background: #c7d2fe; }
        .btn-delete { background: #fee2e2; color: #b91c1c; }
        .btn-delete:hover { background: #fecaca; }
        .type-image { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; }
        .theme-badge { padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 13px; background: #f1f5f9; }

        .pagination-container { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; font-size: 14px; color: #64748b; background: white; padding: 15px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); flex-wrap: wrap; gap: 10px; }
        .pagination { display: flex; gap: 5px; flex-wrap: wrap; align-items: center; }
        .page-link { padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; color: #1e1b4b; text-decoration: none; font-weight: 500; transition: 0.2s; background: white; font-size: 13px; }
        .page-link:hover { border-color: #6366f1; color: #6366f1; }
        .page-link.active { background: #6366f1; color: white; border-color: #6366f1; }
        .page-link.disabled { pointer-events: none; opacity: 0.5; background: #f1f5f9; }
        .page-dots { padding: 6px 8px; color: #94a3b8; font-weight: 600; user-select: none; }

        /* Modal Import */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 999;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: white;
            padding: 30px;
            border-radius: 16px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .modal-box h3 { 
            margin-top: 0; 
            color: #1e1b4b; 
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal-box h3 svg {
            width: 24px;
            height: 24px;
            stroke: #4f46e5;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .modal-box .form-group { margin-bottom: 15px; }
        .modal-box .form-group label { display: block; font-weight: 600; margin-bottom: 5px; color: #1e293b; }
        .modal-box .form-group input[type="file"] { width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
        .modal-actions .btn-submit { background: #4f46e5; color: white; padding: 8px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .modal-actions .btn-submit:hover { background: #4338ca; }
        .modal-actions .btn-cancel { background: #f1f5f9; color: #475569; padding: 8px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .modal-actions .btn-cancel:hover { background: #e2e8f0; }
    </style>
</head>
<body class="app-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="welcome-bar">
            <h1>Quản lý Chủ Đề Bài Học</h1>
            <p>Hệ thống phân tách danh mục bài học flashcard trực quan trên nền web.</p>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="search-add-bar">
            <form method="GET" class="search-form">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm tên chủ đề..." class="form-input" style="margin-bottom:0;">
                <button type="submit" class="btn-primary-action" style="width: auto; white-space: nowrap;">Tìm kiếm</button>
                <?php if($search !== ''): ?>
                    <a href="admin_type.php" class="btn-edit" style="display:flex; align-items:center; text-decoration:none; white-space:nowrap;">Xóa bộ lọc</a>
                <?php endif; ?>
            </form>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="admin_type_action.php" class="btn-add">+ Thêm chủ đề mới</a>
                <button class="btn-import-excel" onclick="document.getElementById('importModalType').classList.add('active')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Import Excel
                </button>
            </div>
        </div>

        <!-- Modal Import Excel cho Typeword -->
        <div id="importModalType" class="modal-overlay">
            <div class="modal-box">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Import chủ đề từ Excel
                </h3>
                <form action="import_type.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Chọn file Excel (.xlsx hoặc .xls)</label>
                        <input type="file" name="excel_file" accept=".xlsx,.xls" required>
                    </div>
                    <div style="font-size:13px; color:#64748b; margin-bottom:15px;">
                        <strong>Định dạng cột:</strong> name, description, color_theme, image (có thể bỏ trống)
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="document.getElementById('importModalType').classList.remove('active')">Hủy</button>
                        <button type="submit" class="btn-submit">Import</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="admin-card">
            <h3>Danh sách các danh mục hệ thống (<?= $total ?>)</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên danh mục</th>
                        <th>Mô tả ngắn</th>
                        <th>Màu chủ đề</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($types)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 30px; color: #64748b;">Không tìm thấy chủ đề nào!</td></tr>
                    <?php else: ?>
                        <?php foreach($types as $t): 
                            $hex = $color_map[$t['color_theme']] ?? '#6b7280';
                        ?>
                            <tr>
                                <td><?= $t['id'] ?></td>
                                <td>
                                    <?php if (!empty($t['image'])): ?>
                                        <img src="<?= htmlspecialchars($t['image']) ?>" alt="<?= htmlspecialchars($t['name']) ?>" class="type-image">
                                    <?php else: ?>
                                        <span style="color:#94a3b8;">Không có</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
                                <td><?= htmlspecialchars($t['description'] ?? '') ?></td>
                                <td>
                                    <span class="theme-badge" style="color: <?= $hex ?>; border: 1px solid <?= $hex ?>;">
                                        <?= htmlspecialchars($t['color_theme']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="admin_type_action.php?id=<?= $t['id'] ?>" class="btn-edit">Sửa</a> 
                                    <a href="admin_type.php?action=delete&id=<?= $t['id'] ?>" class="btn-delete" onclick="return confirm('Bạn chắc chắn muốn xóa chủ đề này chứ?')">Xóa</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total > 0): ?>
            <div class="pagination-container">
                <div>Hiển thị từ <strong><?= $offset + 1 ?></strong> đến <strong><?= min($offset + $limit, $total) ?></strong> trong tổng số <strong><?= $total ?></strong> chủ đề.</div>
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?search=<?= urlencode($search) ?>&page=1" class="page-link">&lt;&lt; Đầu</a>
                    <?php else: ?>
                        <span class="page-link disabled">&lt;&lt; Đầu</span>
                    <?php endif; ?>
                    <?php if ($page > 1): ?>
                        <a href="?search=<?= urlencode($search) ?>&page=<?= $page-1 ?>" class="page-link">&lt; Trước</a>
                    <?php else: ?>
                        <span class="page-link disabled">&lt; Trước</span>
                    <?php endif; ?>
                    <?php foreach ($page_range as $item): ?>
                        <?php if ($item === '...'): ?>
                            <span class="page-dots">…</span>
                        <?php else: ?>
                            <a href="?search=<?= urlencode($search) ?>&page=<?= $item ?>" class="page-link <?= $page == $item ? 'active' : '' ?>"><?= $item ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?search=<?= urlencode($search) ?>&page=<?= $page+1 ?>" class="page-link">Sau &gt;</a>
                    <?php else: ?>
                        <span class="page-link disabled">Sau &gt;</span>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?search=<?= urlencode($search) ?>&page=<?= $total_pages ?>" class="page-link">Cuối &gt;&gt;</a>
                    <?php else: ?>
                        <span class="page-link disabled">Cuối &gt;&gt;</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>