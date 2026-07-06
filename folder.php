<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$user_id = (int)$_SESSION['user_id'];

// ============================================================
// KIỂM TRA & TẠO BẢNG NẾU CHƯA CÓ (đảm bảo chạy ổn định)
// ============================================================
$tables = $db->select("SHOW TABLES LIKE 'folders'");
if (empty($tables)) {
    // Tạo bảng folders
    $db->execute("
        CREATE TABLE IF NOT EXISTS `folders` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `name` varchar(255) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    // Tạo bảng folder_items
    $db->execute("
        CREATE TABLE IF NOT EXISTS `folder_items` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `folder_id` int(11) NOT NULL,
          `item_type` enum('flashcard','vocabulary','quiz') DEFAULT 'flashcard',
          `item_id` int(11) NOT NULL,
          `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `folder_id` (`folder_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    // Thêm khóa ngoại (nếu chưa có)
    try {
        $db->execute("ALTER TABLE `folders` ADD CONSTRAINT `folders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE");
    } catch (Exception $e) {}
    try {
        $db->execute("ALTER TABLE `folder_items` ADD CONSTRAINT `folder_items_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE");
    } catch (Exception $e) {}
}

// ============================================================
// XỬ LÝ CÁC ACTION POST
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Tạo thư mục
    if ($action === 'create_folder') {
        $name = trim($_POST['name'] ?? '');
        if (!empty($name)) {
            $db->execute("INSERT INTO folders (user_id, name) VALUES (?, ?)", [$user_id, $name]);
            header('Location: folder.php?msg=' . urlencode('Đã tạo thư mục thành công!'));
        } else {
            header('Location: folder.php?error=' . urlencode('Tên thư mục không được để trống'));
        }
        exit();
    }
    
    // Xóa thư mục
    if ($action === 'delete_folder') {
        $folder_id = (int)$_POST['folder_id'];
        $check = $db->select("SELECT id FROM folders WHERE id = ? AND user_id = ?", [$folder_id, $user_id]);
        if (!empty($check)) {
            $db->execute("DELETE FROM folders WHERE id = ?", [$folder_id]);
            header('Location: folder.php?msg=' . urlencode('Đã xóa thư mục thành công!'));
        } else {
            header('Location: folder.php?error=' . urlencode('Không có quyền xóa'));
        }
        exit();
    }
    
    // Thêm flashcard set vào thư mục
    if ($action === 'add_item') {
        $folder_id = (int)$_POST['folder_id'];
        $item_type = $_POST['item_type'] ?? 'flashcard';
        $item_id = (int)$_POST['item_id'];
        
        $check = $db->select("SELECT id FROM folders WHERE id = ? AND user_id = ?", [$folder_id, $user_id]);
        if (!empty($check)) {
            // Kiểm tra đã tồn tại chưa
            $exists = $db->select(
                "SELECT id FROM folder_items WHERE folder_id = ? AND item_type = ? AND item_id = ?",
                [$folder_id, $item_type, $item_id]
            );
            if (empty($exists)) {
                $db->execute(
                    "INSERT INTO folder_items (folder_id, item_type, item_id) VALUES (?, ?, ?)",
                    [$folder_id, $item_type, $item_id]
                );
                header('Location: folder.php?id=' . $folder_id . '&msg=' . urlencode('Đã thêm tài liệu vào thư mục!'));
            } else {
                header('Location: folder.php?id=' . $folder_id . '&error=' . urlencode('Tài liệu đã có trong thư mục'));
            }
        } else {
            header('Location: folder.php?error=' . urlencode('Không có quyền'));
        }
        exit();
    }
    
    // Xóa flashcard set khỏi thư mục
    if ($action === 'remove_item') {
        $folder_id = (int)$_POST['folder_id'];
        $item_id = (int)$_POST['item_id'];
        $item_type = $_POST['item_type'] ?? 'flashcard';
        
        $check = $db->select("SELECT id FROM folders WHERE id = ? AND user_id = ?", [$folder_id, $user_id]);
        if (!empty($check)) {
            $db->execute(
                "DELETE FROM folder_items WHERE folder_id = ? AND item_type = ? AND item_id = ?",
                [$folder_id, $item_type, $item_id]
            );
            header('Location: folder.php?id=' . $folder_id . '&msg=' . urlencode('Đã xóa tài liệu khỏi thư mục'));
        } else {
            header('Location: folder.php?error=' . urlencode('Không có quyền'));
        }
        exit();
    }
}

// ============================================================
// LẤY DỮ LIỆU HIỂN THỊ
// ============================================================
// Danh sách thư mục
$folders = $db->select(
    "SELECT f.*, (SELECT COUNT(*) FROM folder_items WHERE folder_id = f.id) as item_count
     FROM folders f
     WHERE f.user_id = ?
     ORDER BY f.created_at DESC",
    [$user_id]
);

// Danh sách flashcard sets của user (để thêm vào thư mục)
$user_sets = $db->select(
    "SELECT id, title, description, is_public,
            (SELECT COUNT(*) FROM flashcard_cards WHERE set_id = fs.id) as card_count
     FROM flashcard_sets fs
     WHERE user_id = ?
     ORDER BY created_at DESC",
    [$user_id]
);

// Nếu có folder_id -> hiển thị chi tiết
$current_folder = null;
$folder_items = [];
$folder_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($folder_id > 0) {
    $current_folder = $db->select("SELECT * FROM folders WHERE id = ? AND user_id = ?", [$folder_id, $user_id]);
    $current_folder = $current_folder[0] ?? null;
    if ($current_folder) {
        $items = $db->select(
            "SELECT fi.*, fs.title, fs.description, fs.is_public,
                    (SELECT COUNT(*) FROM flashcard_cards WHERE set_id = fs.id) as card_count
             FROM folder_items fi
             JOIN flashcard_sets fs ON fi.item_id = fs.id
             WHERE fi.folder_id = ? AND fi.item_type = 'flashcard'
             ORDER BY fi.added_at DESC",
            [$folder_id]
        );
        $folder_items = $items;
    }
}

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư mục - WordWise</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Nunito:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        * { box-sizing: border-box; margin:0; padding:0; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; overflow: hidden; }
        .app-layout { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 30px 40px 20px; background: #faf5ff; }

        .page-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; }
        .page-header h1 { font-size: 28px; font-weight: 900; color: #1e293b; font-family: 'Nunito', sans-serif; display: flex; align-items: center; gap: 12px; }
        .page-header h1 svg { stroke: #a855f7; }

        .btn-primary { background: #a855f7; color: white; border: none; padding: 12px 24px; border-radius: 14px; font-weight: 700; font-size: 15px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 0 #9333ea; transition: 0.15s; text-decoration: none; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 0 #9333ea; }
        .btn-primary:active { transform: translateY(4px); box-shadow: none; }
        .btn-secondary { background: #f1f5f9; color: #1e293b; border: 2px solid #e2e8f0; padding: 10px 20px; border-radius: 12px; font-weight: 700; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: 0.15s; }
        .btn-secondary:hover { background: #e2e8f0; }

        .folder-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-top: 10px; }
        .folder-card { background: white; border-radius: 20px; padding: 20px; border: 1px solid #e9d5ff; box-shadow: 0 4px 12px rgba(168,85,247,0.04); transition: 0.2s; cursor: pointer; text-decoration: none; color: #1e293b; display: flex; flex-direction: column; }
        .folder-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(168,85,247,0.08); border-color: #a855f7; }
        .folder-card .folder-icon { width: 48px; height: 48px; background: #f3e8ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
        .folder-card .folder-icon svg { stroke: #a855f7; }
        .folder-card h3 { font-size: 18px; font-weight: 800; margin-bottom: 4px; }
        .folder-card .meta { font-size: 13px; color: #94a3b8; display: flex; align-items: center; gap: 6px; }
        .folder-card .actions { margin-top: 12px; display: flex; gap: 8px; }
        .folder-card .actions button { background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px 8px; border-radius: 6px; font-size: 13px; transition: 0.15s; display: flex; align-items: center; gap: 4px; }
        .folder-card .actions button:hover { background: #f1f5f9; color: #ef4444; }

        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty-state svg { width: 80px; height: 80px; stroke: #cbd5e1; margin-bottom: 16px; }

        .detail-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; }
        .detail-header .back-link { display: inline-flex; align-items: center; gap: 6px; color: #a855f7; text-decoration: none; font-weight: 600; }
        .detail-header .back-link:hover { text-decoration: underline; }
        .item-list { display: flex; flex-direction: column; gap: 12px; margin-top: 16px; }
        .item-card { background: white; border-radius: 16px; padding: 16px 20px; border: 1px solid #e9d5ff; display: flex; justify-content: space-between; align-items: center; transition: 0.2s; }
        .item-card:hover { border-color: #a855f7; }
        .item-card .info h4 { font-weight: 700; color: #1e293b; }
        .item-card .info p { font-size: 13px; color: #64748b; margin-top: 2px; }
        .item-card .actions { display: flex; gap: 8px; }
        .item-card .actions button { background: none; border: none; color: #94a3b8; cursor: pointer; padding: 6px; border-radius: 6px; transition: 0.15s; }
        .item-card .actions button:hover { background: #fef2f2; color: #ef4444; }

        .modal-overlay { position: fixed; top:0; left:0; right:0; bottom:0; background: rgba(30,41,59,0.5); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; z-index:1000; padding:20px; }
        .modal-overlay.active { display: flex; }
        .modal-box { background: white; border-radius: 24px; padding: 32px; max-width: 500px; width:100%; border:2px solid #e9d5ff; box-shadow: 0 20px 40px rgba(168,85,247,0.12); }
        .modal-box h2 { font-size: 22px; font-weight: 900; margin-bottom:20px; font-family:'Nunito',sans-serif; display:flex; align-items:center; gap:12px; }
        .modal-box .form-group { margin-bottom:16px; }
        .modal-box .form-group label { display:block; font-weight:700; font-size:14px; color:#1e293b; margin-bottom:6px; }
        .modal-box .form-group input { width:100%; padding:12px 16px; border:2px solid #e2e8f0; border-radius:12px; font-size:15px; font-weight:500; transition:0.2s; background:#f8fafc; }
        .modal-box .form-group input:focus { border-color:#a855f7; outline:none; background:white; box-shadow:0 0 0 4px rgba(168,85,247,0.1); }
        .modal-actions { display:flex; gap:12px; margin-top:20px; justify-content:flex-end; }

        .add-item-popup { position: fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; border-radius:24px; padding:32px; max-width:600px; width:95%; max-height:80vh; overflow-y:auto; border:2px solid #e9d5ff; box-shadow:0 20px 40px rgba(0,0,0,0.15); z-index:1001; display:none; }
        .add-item-popup.active { display:block; }
        .add-item-popup .close-btn { float:right; background:none; border:none; cursor:pointer; color:#94a3b8; padding:4px; }
        .add-item-popup h3 { font-size:20px; font-weight:800; margin-bottom:16px; display:flex; align-items:center; gap:10px; }
        .add-item-popup .search-box { width:100%; padding:10px 14px; border:2px solid #e2e8f0; border-radius:12px; margin-bottom:16px; font-size:14px; }
        .add-item-popup .item-list { display:flex; flex-direction:column; gap:10px; max-height:300px; overflow-y:auto; }
        .add-item-popup .item-row { display:flex; justify-content:space-between; align-items:center; padding:12px 16px; background:#f8fafc; border-radius:12px; border:1px solid #e9d5ff; }
        .add-item-popup .item-row .info { flex:1; }
        .add-item-popup .item-row .info .title { font-weight:700; color:#1e293b; }
        .add-item-popup .item-row .info .desc { font-size:13px; color:#64748b; }
        .add-item-popup .item-row .btn-add { background:#a855f7; color:white; border:none; padding:6px 14px; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer; transition:0.15s; }
        .add-item-popup .item-row .btn-add:hover { background:#9333ea; }
        .add-item-popup .item-row .btn-added { background:#dcfce3; color:#065f46; border:none; padding:6px 14px; border-radius:8px; font-weight:600; font-size:13px; cursor:default; }

        .overlay-bg { position:fixed; top:0;left:0;right:0;bottom:0; background:rgba(30,41,59,0.5); backdrop-filter:blur(4px); z-index:1000; display:none; }
        .overlay-bg.active { display:block; }

        .toast { margin-bottom:16px; padding:12px 20px; border-radius:12px; font-weight:600; display:flex; align-items:center; gap:10px; }
        .toast-success { background:#d1fae5; color:#065f46; }
        .toast-error { background:#fef2f2; color:#991b1b; }

        @media (max-width:640px) { .main-content { padding:16px; } .folder-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body class="app-layout">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <?php if ($msg): ?>
            <div class="toast toast-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="toast toast-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($current_folder): ?>
            <!-- Chi tiết thư mục -->
            <div class="detail-header">
                <div>
                    <a href="folder.php" class="back-link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        Danh sách thư mục
                    </a>
                    <h1 style="font-size:28px; font-weight:900; color:#1e293b; font-family:'Nunito',sans-serif; margin-top:6px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        <?= htmlspecialchars($current_folder['name']) ?>
                    </h1>
                    <p style="color:#64748b; margin-top:4px;"><?= count($folder_items) ?> tài liệu trong thư mục</p>
                </div>
                <div style="display:flex; gap:12px;">
                    <button class="btn-primary" onclick="openAddPopup(<?= $current_folder['id'] ?>)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Thêm tài liệu học
                    </button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa thư mục này?')">
                        <input type="hidden" name="action" value="delete_folder">
                        <input type="hidden" name="folder_id" value="<?= $current_folder['id'] ?>">
                        <button type="submit" class="btn-secondary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                            Xóa thư mục
                        </button>
                    </form>
                </div>
            </div>

            <?php if (empty($folder_items)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    <p style="font-size:18px; font-weight:600; color:#64748b;">Chưa có tài liệu nào</p>
                    <p style="color:#94a3b8; margin-top:8px;">Nhấn "Thêm tài liệu học" để bắt đầu.</p>
                </div>
            <?php else: ?>
                <div class="item-list">
                    <?php foreach ($folder_items as $item): ?>
                        <div class="item-card">
                            <div class="info">
                                <h4><?= htmlspecialchars($item['title']) ?></h4>
                                <p><?= htmlspecialchars($item['description'] ?? '') ?> · <?= $item['card_count'] ?> thẻ · <?= $item['is_public'] ? 'Công khai' : 'Riêng tư' ?></p>
                            </div>
                            <div class="actions">
                                <form method="POST">
                                    <input type="hidden" name="action" value="remove_item">
                                    <input type="hidden" name="folder_id" value="<?= $current_folder['id'] ?>">
                                    <input type="hidden" name="item_type" value="flashcard">
                                    <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                    <button type="submit" title="Xóa khỏi thư mục" onclick="return confirm('Xóa tài liệu này khỏi thư mục?')">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Danh sách thư mục -->
            <div class="page-header">
                <h1>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    Thư mục của tôi
                </h1>
                <button class="btn-primary" onclick="openCreateModal()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Tạo thư mục mới
                </button>
            </div>

            <?php if (empty($folders)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    <p style="font-size:18px; font-weight:600; color:#64748b;">Bạn chưa có thư mục nào</p>
                    <p style="color:#94a3b8; margin-top:8px;">Nhấn "Tạo thư mục mới" để bắt đầu sắp xếp tài liệu học.</p>
                </div>
            <?php else: ?>
                <div class="folder-grid">
                    <?php foreach ($folders as $folder): ?>
                        <a href="folder.php?id=<?= $folder['id'] ?>" class="folder-card">
                            <div class="folder-icon">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                            </div>
                            <h3><?= htmlspecialchars($folder['name']) ?></h3>
                            <div class="meta">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/></svg>
                                <?= $folder['item_count'] ?> tài liệu
                            </div>
                            <div class="actions" onclick="event.stopPropagation();">
                                <form method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa thư mục này?')">
                                    <input type="hidden" name="action" value="delete_folder">
                                    <input type="hidden" name="folder_id" value="<?= $folder['id'] ?>">
                                    <button type="submit">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                        Xóa
                                    </button>
                                </form>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Modal tạo thư mục -->
        <div class="modal-overlay" id="createModal">
            <div class="modal-box">
                <h2>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Đặt tên cho thư mục của bạn
                </h2>
                <form method="POST">
                    <input type="hidden" name="action" value="create_folder">
                    <div class="form-group">
                        <label for="folderName">Tên thư mục</label>
                        <input type="text" id="folderName" name="name" placeholder="Ví dụ: Từ vựng TOEIC" required />
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-secondary" onclick="closeCreateModal()">Hủy</button>
                        <button type="submit" class="btn-primary">Tạo</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Popup thêm tài liệu học -->
        <div class="overlay-bg" id="overlayBg" onclick="closeAddPopup()"></div>
        <div class="add-item-popup" id="addPopup">
            <button class="close-btn" onclick="closeAddPopup()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <h3>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Thêm tài liệu học
            </h3>
            <input type="text" class="search-box" id="searchItems" placeholder="Tìm kiếm học phần..." oninput="filterItems()">
            <div class="item-list" id="itemList">
                <?php foreach ($user_sets as $set): ?>
                    <div class="item-row" data-id="<?= $set['id'] ?>">
                        <div class="info">
                            <div class="title"><?= htmlspecialchars($set['title']) ?></div>
                            <div class="desc"><?= $set['card_count'] ?? 0 ?> thẻ · <?= $set['is_public'] ? 'Công khai' : 'Riêng tư' ?></div>
                        </div>
                        <?php
                        // Kiểm tra đã có trong thư mục chưa
                        $exists = $db->select(
                            "SELECT id FROM folder_items WHERE folder_id = ? AND item_type = 'flashcard' AND item_id = ?",
                            [$folder_id, $set['id']]
                        );
                        ?>
                        <?php if (!empty($exists)): ?>
                            <button class="btn-added" disabled>✓ Đã thêm</button>
                        <?php else: ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="add_item">
                                <input type="hidden" name="folder_id" value="<?= $folder_id ?>">
                                <input type="hidden" name="item_type" value="flashcard">
                                <input type="hidden" name="item_id" value="<?= $set['id'] ?>">
                                <button type="submit" class="btn-add">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Thêm
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($user_sets)): ?>
                    <p style="text-align:center; color:#94a3b8; padding:20px;">Bạn chưa có học phần nào. Tạo flashcard trước!</p>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.add('active');
            document.getElementById('folderName').focus();
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.remove('active');
        }

        function openAddPopup(folderId) {
            document.getElementById('overlayBg').classList.add('active');
            document.getElementById('addPopup').classList.add('active');
            // Cập nhật folder_id cho các form thêm
            document.querySelectorAll('#addPopup form').forEach(form => {
                const fInput = form.querySelector('input[name="folder_id"]');
                if (fInput) fInput.value = folderId;
            });
        }

        function closeAddPopup() {
            document.getElementById('overlayBg').classList.remove('active');
            document.getElementById('addPopup').classList.remove('active');
        }

        function filterItems() {
            const keyword = document.getElementById('searchItems').value.toLowerCase();
            const rows = document.querySelectorAll('.item-row');
            rows.forEach(row => {
                const title = row.querySelector('.title')?.textContent.toLowerCase() || '';
                row.style.display = title.includes(keyword) ? '' : 'none';
            });
        }

        // Đóng modal khi click bên ngoài
        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) closeCreateModal();
        });
    </script>
</body>
</html>