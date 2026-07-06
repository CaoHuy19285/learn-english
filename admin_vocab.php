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

// Xóa từ vựng
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    $affected = $db->execute("DELETE FROM vocabulary WHERE id = ?", [$delete_id]);
    if ($affected > 0) {
        header("Location: admin_vocab.php?msg=" . urlencode("Xóa từ vựng thành công!"));
    } else {
        header("Location: admin_vocab.php?error=" . urlencode("Không tìm thấy từ vựng hoặc xóa thất bại."));
    }
    exit();
}

// ---------------- TÌM KIẾM, SẮP XẾP & PHÂN TRANG ----------------
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;

$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
$sort_order = isset($_GET['sort_order']) && strtolower($_GET['sort_order']) === 'asc' ? 'ASC' : 'DESC';

$allowed_sort_columns = [
    'id' => 'v.id',
    'word' => 'v.word',
    'category' => 't.name'
];
$sql_sort_column = isset($allowed_sort_columns[$sort_by]) ? $allowed_sort_columns[$sort_by] : 'v.id';

function getSortUrl($column, $current_sort_by, $current_sort_order, $search) {
    $new_order = ($current_sort_by === $column && $current_sort_order === 'ASC') ? 'desc' : 'asc';
    return "?search=" . urlencode($search) . "&sort_by=" . $column . "&sort_order=" . $new_order . "&page=1";
}

$params = [];
$where_sql = "";
if ($search !== '') {
    $where_sql = "WHERE v.word LIKE ? OR v.definition LIKE ? OR t.name LIKE ? OR v.difficulty LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$count_sql = "SELECT COUNT(*) as total FROM vocabulary v LEFT JOIN typeword t ON v.typeword_id = t.id $where_sql";
$total_res = $db->select($count_sql, $params);
$total_records = !empty($total_res) ? $total_res[0]['total'] : 0;

$total_pages = ceil($total_records / $limit);
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
$offset = ($page - 1) * $limit;

$fetch_params = $params;
$sql = "SELECT v.*, t.name AS category_name, t.color_theme 
        FROM vocabulary v 
        LEFT JOIN typeword t ON v.typeword_id = t.id 
        $where_sql 
        ORDER BY $sql_sort_column $sort_order 
        LIMIT ? OFFSET ?";
$fetch_params[] = intval($limit);
$fetch_params[] = intval($offset);
$words = $db->select($sql, $fetch_params);

// Xây dựng mảng trang hiển thị (1,2,3 … 8,9,10)
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Từ vựng - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .search-add-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px; flex-wrap: wrap;}
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
        
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; white-space: nowrap; }
        .bg-green { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
        .bg-yellow { background: #fef08a; color: #ca8a04; border: 1px solid #fde047; }
        .bg-red { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .bg-gray { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .cat-purple { background: #e0e7ff; color: #4f46e5; border: 1px solid #c7d2fe; }
        .cat-pink { background: #fce7f3; color: #db2777; border: 1px solid #fbcfe8; }
        .cat-green { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .cat-indigo { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
        .cat-orange { background: #ffedd5; color: #ea580c; border: 1px solid #fed7aa; }

        /* Phân trang */
        .pagination-container { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; font-size: 14px; color: #64748b; background: white; padding: 15px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); flex-wrap: wrap; gap: 10px; }
        .pagination { display: flex; gap: 5px; flex-wrap: wrap; align-items: center; }
        .page-link { padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; color: #1e1b4b; text-decoration: none; font-weight: 500; transition: 0.2s; background: white; font-size: 13px; }
        .page-link:hover { border-color: #6366f1; color: #6366f1; }
        .page-link.active { background: #6366f1; color: white; border-color: #6366f1; }
        .page-link.disabled { pointer-events: none; opacity: 0.5; background: #f1f5f9; }
        .page-dots { padding: 6px 8px; color: #94a3b8; font-weight: 600; user-select: none; }
        
        .sort-header { color: #1e1b4b; text-decoration: none; display: flex; align-items: center; gap: 5px; transition: 0.2s; }
        .sort-header:hover { color: #4f46e5; }
        .sort-icon { font-size: 12px; color: #94a3b8; }
        .sort-icon.active { color: #4f46e5; font-weight: bold; }

        .table-img-container img { width: 40px; height: 40px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; }
        .word-main-title { font-weight: 600; color: #0f172a; }
        .word-ipa-sub { font-size: 12px; color: #64748b; }
        .def-val { font-size: 14px; color: #1e293b; }
        .ex-val { font-size: 13px; color: #64748b; font-style: italic; }
        .btn-edit, .btn-delete { padding: 4px 12px; border-radius: 6px; font-size: 13px; text-decoration: none; font-weight: 500; border: none; cursor: pointer; }
        .btn-edit { background: #e0e7ff; color: #4f46e5; }
        .btn-edit:hover { background: #c7d2fe; }
        .btn-delete { background: #fee2e2; color: #b91c1c; }
        .btn-delete:hover { background: #fecaca; }

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
            <h1>Kho Quản Lý Từ Vựng</h1>
            <p>Xem danh sách, sắp xếp A-Z, tìm kiếm đa năng và quản lý thông tin các từ vựng.</p>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="search-add-bar">
            <form method="GET" class="search-form">
                <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sort_by) ?>">
                <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sort_order) ?>">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm từ, định nghĩa, chủ đề, độ khó..." class="form-input" style="margin-bottom:0;">
                <button type="submit" class="btn-primary-action" style="width: auto; white-space: nowrap;">Tìm kiếm</button>
                <?php if($search !== ''): ?>
                    <a href="admin_vocab.php" class="btn-edit" style="display:flex; align-items:center; text-decoration:none; white-space:nowrap;">Xóa tìm kiếm</a>
                <?php endif; ?>
            </form>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="admin_vocab_action.php" class="btn-add">+ Thêm từ mới</a>
                <button class="btn-import-excel" onclick="document.getElementById('importModalVocab').classList.add('active')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Import Excel
                </button>
            </div>
        </div>

        <!-- Modal Import Excel cho Vocabulary -->
        <div id="importModalVocab" class="modal-overlay">
            <div class="modal-box">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Import từ vựng từ Excel
                </h3>
                <form action="import_vocab.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Chọn file Excel (.xlsx hoặc .xls)</label>
                        <input type="file" name="excel_file" accept=".xlsx,.xls" required>
                    </div>
                    <div style="font-size:13px; color:#64748b; margin-bottom:15px;">
                        <strong>Định dạng cột:</strong> word, ipa, definition, example, difficulty, typeword_name, image (có thể bỏ trống)
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="document.getElementById('importModalVocab').classList.remove('active')">Hủy</button>
                        <button type="submit" class="btn-submit">Import</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="admin-card">
            <h3>Dữ liệu kho từ hiện tại (Tìm thấy: <?= $total_records ?> từ)</h3>
            <table class="data-table" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">STT</th>
                        <th>Hình ảnh</th>
                        <th>
                            <a href="<?= getSortUrl('word', $sort_by, $sort_order, $search) ?>" class="sort-header">
                                Từ vựng & IPA
                                <span class="sort-icon <?= $sort_by === 'word' ? 'active' : '' ?>">
                                    <?= ($sort_by === 'word' && $sort_order === 'ASC') ? '▲' : '▼' ?>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="<?= getSortUrl('category', $sort_by, $sort_order, $search) ?>" class="sort-header">
                                Chủ đề
                                <span class="sort-icon <?= $sort_by === 'category' ? 'active' : '' ?>">
                                    <?= ($sort_by === 'category' && $sort_order === 'ASC') ? '▲' : '▼' ?>
                                </span>
                            </a>
                        </th>
                        <th>Độ khó</th>
                        <th>Định nghĩa / Ví dụ</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($words)): ?>
                        <tr><td colspan="7" style="text-align: center; padding: 30px; color: #64748b;">Không tìm thấy kết quả từ vựng phù hợp!</td></tr>
                    <?php else: ?>
                        <?php 
                        $stt = $offset + 1; 
                        foreach ($words as $w): 
                            $diff = $w['difficulty'];
                            $diff_class = 'bg-gray'; 
                            if ($diff === 'Dễ') $diff_class = 'bg-green';
                            elseif ($diff === 'Trung bình') $diff_class = 'bg-yellow';
                            elseif ($diff === 'Khó') $diff_class = 'bg-red';
                            $cat_theme = !empty($w['color_theme']) ? 'cat-' . htmlspecialchars($w['color_theme']) : 'cat-purple';
                        ?>
                            <tr>
                                <td style="text-align: center; font-weight: 600; color: #64748b;"><?= $stt++ ?></td>
                                <td>
                                    <div class="table-img-container">
                                        <img src="<?= !empty($w['image']) ? htmlspecialchars($w['image']) : 'https://cdn-icons-png.flaticon.com/512/1792/1792183.png' ?>" alt="Vocab Image" onerror="this.src='https://cdn-icons-png.flaticon.com/512/1792/1792183.png'">
                                    </div>
                                </td>
                                <td>
                                    <div class="word-main-title"><?= htmlspecialchars($w['word']) ?></div>
                                    <div class="word-ipa-sub"><?= htmlspecialchars($w['ipa']) ?></div>
                                </td>
                                <td><span class="badge <?= $cat_theme ?>"><?= htmlspecialchars($w['category_name'] ?? 'Chưa phân loại') ?></span></td>
                                <td><span class="badge <?= $diff_class ?>"><?= htmlspecialchars($w['difficulty']) ?></span></td>
                                <td>
                                    <div class="def-val"><?= htmlspecialchars($w['definition']) ?></div>
                                    <div class="ex-val" style="margin-top: 4px; color: #64748b;"><em><?= htmlspecialchars($w['example']) ?></em></div>
                                </td>
                                <td style="white-space: nowrap;">
                                    <a href="admin_vocab_action.php?id=<?= $w['id'] ?>" class="btn-edit">Sửa</a> 
                                    <a href="admin_vocab.php?action=delete&id=<?= $w['id'] ?>" class="btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa từ này?')">Xóa</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_records > 0): ?>
            <div class="pagination-container">
                <div>Hiển thị từ <strong><?= $offset + 1 ?></strong> đến <strong><?= min($offset + $limit, $total_records) ?></strong> trong tổng số <strong><?= $total_records ?></strong> từ vựng.</div>
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?search=<?= urlencode($search) ?>&sort_by=<?= urlencode($sort_by) ?>&sort_order=<?= urlencode($sort_order) ?>&page=1" class="page-link">&lt;&lt; Đầu</a>
                    <?php else: ?>
                        <span class="page-link disabled">&lt;&lt; Đầu</span>
                    <?php endif; ?>
                    <?php if ($page > 1): ?>
                        <a href="?search=<?= urlencode($search) ?>&sort_by=<?= urlencode($sort_by) ?>&sort_order=<?= urlencode($sort_order) ?>&page=<?= $page-1 ?>" class="page-link">&lt; Trước</a>
                    <?php else: ?>
                        <span class="page-link disabled">&lt; Trước</span>
                    <?php endif; ?>
                    <?php foreach ($page_range as $item): ?>
                        <?php if ($item === '...'): ?>
                            <span class="page-dots">…</span>
                        <?php else: ?>
                            <a href="?search=<?= urlencode($search) ?>&sort_by=<?= urlencode($sort_by) ?>&sort_order=<?= urlencode($sort_order) ?>&page=<?= $item ?>" class="page-link <?= $page == $item ? 'active' : '' ?>"><?= $item ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?search=<?= urlencode($search) ?>&sort_by=<?= urlencode($sort_by) ?>&sort_order=<?= urlencode($sort_order) ?>&page=<?= $page+1 ?>" class="page-link">Sau &gt;</a>
                    <?php else: ?>
                        <span class="page-link disabled">Sau &gt;</span>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?search=<?= urlencode($search) ?>&sort_by=<?= urlencode($sort_by) ?>&sort_order=<?= urlencode($sort_order) ?>&page=<?= $total_pages ?>" class="page-link">Cuối &gt;&gt;</a>
                    <?php else: ?>
                        <span class="page-link disabled">Cuối &gt;&gt;</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php include 'footer.php'; ?>
    </main>
</body>
</html>