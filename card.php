<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$user_id = (int)$_SESSION['user_id'];

// Xử lý hành động
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$set_id = (int)($_POST['set_id'] ?? $_GET['set_id'] ?? 0);

// Tạo mới
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    $fronts = $_POST['front'] ?? [];
    $backs = $_POST['back'] ?? [];

    if (empty($title) || empty($fronts) || empty($backs) || count($fronts) === 0) {
        $error = "Vui lòng nhập tiêu đề và ít nhất một thẻ.";
    } else {
        $db->execute(
            "INSERT INTO flashcard_sets (user_id, title, description, is_public) VALUES (?, ?, ?, ?)",
            [$user_id, $title, $description, $is_public]
        );
        $new_set_id = $db->insert_id();
        foreach ($fronts as $i => $front) {
            $front = trim($front);
            $back = trim($backs[$i] ?? '');
            if ($front && $back) {
                $db->execute(
                    "INSERT INTO flashcard_cards (set_id, front, back) VALUES (?, ?, ?)",
                    [$new_set_id, $front, $back]
                );
            }
        }
        header("Location: card.php?success=created");
        exit();
    }
}

// Sửa (update)
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST' && $set_id > 0) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    $fronts = $_POST['front'] ?? [];
    $backs = $_POST['back'] ?? [];

    $check = $db->select("SELECT id FROM flashcard_sets WHERE id = ? AND user_id = ?", [$set_id, $user_id]);
    if (empty($check)) {
        $error = "Bạn không có quyền sửa bộ thẻ này.";
    } elseif (empty($title) || empty($fronts) || count($fronts) === 0) {
        $error = "Vui lòng nhập tiêu đề và ít nhất một thẻ.";
    } else {
        $db->execute(
            "UPDATE flashcard_sets SET title = ?, description = ?, is_public = ? WHERE id = ?",
            [$title, $description, $is_public, $set_id]
        );
        $db->execute("DELETE FROM flashcard_cards WHERE set_id = ?", [$set_id]);
        foreach ($fronts as $i => $front) {
            $front = trim($front);
            $back = trim($backs[$i] ?? '');
            if ($front && $back) {
                $db->execute(
                    "INSERT INTO flashcard_cards (set_id, front, back) VALUES (?, ?, ?)",
                    [$set_id, $front, $back]
                );
            }
        }
        header("Location: card.php?success=updated");
        exit();
    }
}

// Xóa
if ($action === 'delete' && $set_id > 0) {
    $check = $db->select("SELECT id FROM flashcard_sets WHERE id = ? AND user_id = ?", [$set_id, $user_id]);
    if (!empty($check)) {
        $db->execute("DELETE FROM flashcard_sets WHERE id = ?", [$set_id]);
        header("Location: card.php?success=deleted");
        exit();
    } else {
        $error = "Bạn không có quyền xóa bộ thẻ này.";
    }
}

// Lấy danh sách bộ thẻ của user
$sets = $db->select(
    "SELECT s.*, (SELECT COUNT(*) FROM flashcard_cards WHERE set_id = s.id) as card_count
     FROM flashcard_sets s
     WHERE s.user_id = ?
     ORDER BY s.created_at DESC",
    [$user_id]
);

// Chi tiết bộ thẻ (xem)
$detail_set = null;
$cards = [];
if ($set_id > 0 && $action === 'view') {
    $detail_set = $db->select("SELECT * FROM flashcard_sets WHERE id = ? AND user_id = ?", [$set_id, $user_id]);
    $detail_set = $detail_set[0] ?? null;
    if ($detail_set) {
        $cards = $db->select("SELECT * FROM flashcard_cards WHERE set_id = ? ORDER BY id", [$set_id]);
    }
}

// Lấy dữ liệu để sửa (edit)
$edit_set = null;
$edit_cards = [];
if ($set_id > 0 && $action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $edit_set = $db->select("SELECT * FROM flashcard_sets WHERE id = ? AND user_id = ?", [$set_id, $user_id]);
    $edit_set = $edit_set[0] ?? null;
    if ($edit_set) {
        $edit_cards = $db->select("SELECT * FROM flashcard_cards WHERE set_id = ? ORDER BY id", [$set_id]);
    }
}

$pageTitle = "Thẻ ghi nhớ - WordWise";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Nunito:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        * { box-sizing: border-box; margin:0; padding:0; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; overflow: hidden; }
        .app-layout { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 30px 40px 20px; background: #faf5ff; }

        .page-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; }
        .page-header h1 { font-size: 28px; font-weight: 900; color: #1e293b; font-family: 'Nunito', sans-serif; display: flex; align-items: center; gap: 12px; }
        .btn-primary { background: #a855f7; color: white; border: none; padding: 12px 24px; border-radius: 14px; font-weight: 700; font-size: 15px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 0 #9333ea; transition: 0.15s; text-decoration: none; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 0 #9333ea; }
        .btn-primary:active { transform: translateY(4px); box-shadow: none; }
        .btn-primary-outline { background: #f1f5f9; color: #1e293b; border: 2px solid #e2e8f0; box-shadow: 0 4px 0 #cbd5e1; }
        .btn-primary-outline:hover { background: #e2e8f0; }

        .set-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; margin-top: 10px; }
        .set-card { background: white; border-radius: 20px; padding: 20px; border: 1px solid #e9d5ff; box-shadow: 0 4px 12px rgba(168,85,247,0.04); transition: 0.2s; display: flex; flex-direction: column; }
        .set-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(168,85,247,0.08); }
        .set-card h3 { font-size: 18px; font-weight: 800; color: #1e293b; margin-bottom: 4px; }
        .set-card .desc { color: #64748b; font-size: 14px; flex: 1; margin-bottom: 12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .set-card .meta { display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 12px; }
        .set-card .actions { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
        .set-card .actions a, .set-card .actions button { font-size: 13px; font-weight: 600; color: #a855f7; text-decoration: none; padding: 6px 14px; border-radius: 8px; background: #f3e8ff; border: none; cursor: pointer; transition: 0.15s; display: inline-flex; align-items: center; gap: 6px; }
        .set-card .actions a:hover, .set-card .actions button:hover { background: #ede9fe; }
        .set-card .actions .delete { color: #ef4444; background: #fef2f2; }
        .set-card .actions .delete:hover { background: #fecaca; }

        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty-state svg { width: 80px; height: 80px; stroke: #cbd5e1; margin-bottom: 16px; }

        /* Modal */
        .modal-overlay { position: fixed; top:0; left:0; right:0; bottom:0; background: rgba(30,41,59,0.5); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; z-index:1000; padding:20px; }
        .modal-overlay.active { display: flex; }
        .modal-box { background: white; border-radius: 24px; padding: 32px; max-width: 700px; width:100%; max-height:90vh; overflow-y:auto; border:2px solid #e9d5ff; box-shadow: 0 20px 40px rgba(168,85,247,0.12); }
        .modal-box h2 { font-size: 24px; font-weight: 900; margin-bottom:20px; font-family:'Nunito',sans-serif; display:flex; align-items:center; gap:12px; }
        .form-group { margin-bottom:18px; }
        .form-group label { display:block; font-weight:700; font-size:14px; color:#1e293b; margin-bottom:6px; }
        .form-group input, .form-group textarea { width:100%; padding:12px 16px; border:2px solid #e2e8f0; border-radius:12px; font-size:15px; font-weight:500; transition:0.2s; background:#f8fafc; }
        .form-group input:focus, .form-group textarea:focus { border-color:#a855f7; outline:none; background:white; box-shadow:0 0 0 4px rgba(168,85,247,0.1); }
        .checkbox-group { display:flex; align-items:center; gap:10px; }
        .checkbox-group input[type="checkbox"] { width:20px; height:20px; accent-color:#a855f7; }
        .card-entries { display:flex; flex-direction:column; gap:12px; margin:12px 0; }
        .card-entry { display:flex; gap:12px; align-items:center; }
        .card-entry input { flex:1; padding:10px 14px; border:2px solid #e2e8f0; border-radius:10px; font-size:14px; background:#f8fafc; transition:0.2s; }
        .card-entry input:focus { border-color:#a855f7; outline:none; background:white; }
        .btn-remove-entry { background:none; border:none; color:#ef4444; cursor:pointer; padding:6px; border-radius:8px; transition:0.15s; display:inline-flex; align-items:center; }
        .btn-remove-entry:hover { background:#fef2f2; }
        .btn-add-entry { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:#ede9fe; color:#7c3aed; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; transition:0.15s; }
        .btn-add-entry:hover { background:#ddd6fe; }
        .modal-actions { display:flex; gap:12px; margin-top:24px; justify-content:flex-end; }
        .modal-actions .btn-secondary { background:#f1f5f9; color:#1e293b; border:2px solid #e2e8f0; padding:12px 24px; border-radius:14px; font-weight:700; cursor:pointer; transition:0.15s; }
        .modal-actions .btn-secondary:hover { background:#e2e8f0; }

        /* Detail view - hai cột */
        .detail-view { background:white; border-radius:20px; padding:24px; border:1px solid #e9d5ff; margin-bottom:20px; }
        .detail-view .back-link { display:inline-flex; align-items:center; gap:8px; color:#a855f7; text-decoration:none; font-weight:700; margin-bottom:16px; }
        .detail-view .back-link:hover { text-decoration:underline; }
        .detail-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:20px; }
        .detail-header .info { display:flex; gap:20px; font-size:14px; color:#94a3b8; }
        .detail-header .info span { display:flex; align-items:center; gap:6px; }
        .card-list { display:flex; flex-direction:column; gap:12px; }
        .card-item { display:grid; grid-template-columns:1fr 1fr; gap:16px; padding:14px 18px; background:#f8fafc; border-radius:12px; border:1px solid #e9d5ff; align-items:center; }
        .card-item .front { font-weight:700; color:#1e293b; }
        .card-item .back { color:#475569; }

        .toast { margin-bottom:16px; padding:12px 20px; border-radius:12px; font-weight:600; display:flex; align-items:center; gap:10px; }
        .toast-success { background:#d1fae5; color:#065f46; }
        .toast-error { background:#fef2f2; color:#991b1b; }

        /* Edit form rộng hơn */
        .edit-form-wrapper { max-width:900px; margin:0 auto; width:100%; }

        @media (max-width:640px) { .main-content { padding:16px; } .set-grid { grid-template-columns:1fr; } .modal-box { padding:20px; } .card-item { grid-template-columns:1fr; gap:4px; } }
    </style>
</head>
<body class="app-layout">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <?php if (isset($_GET['success'])): ?>
            <div class="toast toast-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <?php if ($_GET['success'] === 'created'): ?>
                    Bộ thẻ đã được tạo thành công!
                <?php elseif ($_GET['success'] === 'updated'): ?>
                    Bộ thẻ đã được cập nhật!
                <?php elseif ($_GET['success'] === 'deleted'): ?>
                    Bộ thẻ đã được xóa.
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="toast toast-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($detail_set): ?>
            <!-- Chi tiết bộ thẻ -->
            <div class="page-header">
                <h1>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <line x1="3" y1="9" x2="21" y2="9"/>
                    </svg>
                    <?= htmlspecialchars($detail_set['title']) ?>
                </h1>
                <div style="display:flex; gap:12px;">
                    <a href="flashcard.php?set_id=<?= $set_id ?>" class="btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                        Học từ vựng
                    </a>
                    <a href="card.php" class="btn-primary btn-primary-outline">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        Quay lại
                    </a>
                </div>
            </div>
            <div class="detail-view">
                <div class="detail-header">
                    <p style="color:#64748b; margin:0;"><?= nl2br(htmlspecialchars($detail_set['description'] ?? '')) ?></p>
                    <div class="info">
                        <span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/></svg>
                            <?= count($cards) ?> thẻ
                        </span>
                        <span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                            <?= $detail_set['is_public'] ? 'Công khai' : 'Riêng tư' ?>
                        </span>
                    </div>
                </div>
                <div class="card-list">
                    <?php foreach ($cards as $index => $card): ?>
                        <div class="card-item">
                            <div class="front"><?= htmlspecialchars($card['front']) ?></div>
                            <div class="back"><?= htmlspecialchars($card['back']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php elseif ($edit_set): ?>
            <!-- Form sửa bộ thẻ – rộng hơn, đẹp hơn -->
            <div class="page-header">
                <h1>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2">
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                    Sửa bộ thẻ
                </h1>
                <a href="card.php" class="btn-primary btn-primary-outline">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                    Hủy
                </a>
            </div>
            <div class="edit-form-wrapper">
                <form method="POST" action="card.php?action=edit&set_id=<?= $set_id ?>" style="background:white; border-radius:20px; padding:30px; border:1px solid #e9d5ff;">
                    <div class="form-group">
                        <label for="title">Tiêu đề *</label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($edit_set['title']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Mô tả</label>
                        <textarea id="description" name="description" rows="3"><?= htmlspecialchars($edit_set['description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="is_public" name="is_public" value="1" <?= $edit_set['is_public'] ? 'checked' : '' ?>>
                        <label for="is_public" style="margin:0;">Công khai (mọi người có thể xem)</label>
                    </div>

                    <div class="form-group">
                        <label>Thẻ (mặt trước - mặt sau)</label>
                        <div class="card-entries" id="editCardEntries">
                            <?php foreach ($edit_cards as $card): ?>
                                <div class="card-entry">
                                    <input type="text" name="front[]" value="<?= htmlspecialchars($card['front']) ?>" required>
                                    <input type="text" name="back[]" value="<?= htmlspecialchars($card['back']) ?>" required>
                                    <button type="button" class="btn-remove-entry" onclick="removeEntry(this)">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add-entry" onclick="addEditEntry()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Thêm thẻ
                        </button>
                    </div>

                    <div class="modal-actions">
                        <a href="card.php" class="btn-secondary" style="text-decoration:none;">Hủy</a>
                        <button type="submit" class="btn-primary">Cập nhật</button>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <!-- Danh sách bộ thẻ -->
            <div class="page-header">
                <h1>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <line x1="3" y1="9" x2="21" y2="9"/>
                    </svg>
                    Thẻ ghi nhớ của tôi
                </h1>
                <button class="btn-primary" onclick="openCreateModal()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tạo học phần mới
                </button>
            </div>

            <?php if (empty($sets)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="2" y="3" width="20" height="18" rx="2"/>
                        <line x1="8" y1="7" x2="16" y2="7"/>
                        <line x1="8" y1="11" x2="16" y2="11"/>
                        <line x1="8" y1="15" x2="12" y2="15"/>
                    </svg>
                    <p style="font-size:18px; font-weight:600; color:#64748b;">Bạn chưa có bộ thẻ nào</p>
                    <p style="color:#94a3b8; margin-top:8px;">Nhấn "Tạo học phần mới" để bắt đầu.</p>
                </div>
            <?php else: ?>
                <div class="set-grid">
                    <?php foreach ($sets as $set): ?>
                        <div class="set-card">
                            <h3><?= htmlspecialchars($set['title']) ?></h3>
                            <div class="desc"><?= nl2br(htmlspecialchars($set['description'] ?? '')) ?></div>
                            <div class="meta">
                                <span style="display:flex; align-items:center; gap:4px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/></svg>
                                    <?= $set['card_count'] ?> thẻ
                                </span>
                                <span style="display:flex; align-items:center; gap:4px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                                    <?= $set['is_public'] ? 'Công khai' : 'Riêng tư' ?>
                                </span>
                            </div>
                            <div class="actions">
                                <a href="card.php?action=view&set_id=<?= $set['id'] ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Xem
                                </a>
                                <a href="card.php?action=edit&set_id=<?= $set['id'] ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    Sửa
                                </a>
                                <a href="card.php?action=delete&set_id=<?= $set['id'] ?>" class="delete" onclick="return confirm('Bạn chắc chắn muốn xóa bộ thẻ này?')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    Xóa
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Modal tạo mới -->
        <div class="modal-overlay" id="createModal">
            <div class="modal-box">
                <h2>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tạo học phần mới
                </h2>
                <form method="POST" action="card.php?action=create" id="createForm">
                    <div class="form-group">
                        <label for="title">Tiêu đề *</label>
                        <input type="text" id="title" name="title" placeholder="Ví dụ: 50 từ vựng TOEIC" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Mô tả</label>
                        <textarea id="description" name="description" rows="2" placeholder="Thêm mô tả..."></textarea>
                    </div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="is_public" name="is_public" value="1">
                        <label for="is_public" style="margin:0;">Công khai (mọi người có thể xem)</label>
                    </div>

                    <div class="form-group">
                        <label>Thẻ (mặt trước - mặt sau)</label>
                        <div class="card-entries" id="cardEntries">
                            <div class="card-entry">
                                <input type="text" name="front[]" placeholder="Từ tiếng Anh" required>
                                <input type="text" name="back[]" placeholder="Nghĩa tiếng Việt" required>
                                <button type="button" class="btn-remove-entry" onclick="removeEntry(this)">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn-add-entry" onclick="addEntry()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Thêm thẻ
                        </button>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-secondary" onclick="closeCreateModal()">Hủy</button>
                        <button type="submit" class="btn-primary">Tạo</button>
                    </div>
                </form>
            </div>
        </div>

    </main>

    <script>
        // Modal tạo mới
        const modal = document.getElementById('createModal');

        function openCreateModal() {
            modal.classList.add('active');
            document.getElementById('createForm').reset();
            const container = document.getElementById('cardEntries');
            container.innerHTML = `
                <div class="card-entry">
                    <input type="text" name="front[]" placeholder="Từ tiếng Anh" required>
                    <input type="text" name="back[]" placeholder="Nghĩa tiếng Việt" required>
                    <button type="button" class="btn-remove-entry" onclick="removeEntry(this)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            `;
        }

        function closeCreateModal() {
            modal.classList.remove('active');
        }

        function addEntry() {
            const container = document.getElementById('cardEntries');
            const entry = document.createElement('div');
            entry.className = 'card-entry';
            entry.innerHTML = `
                <input type="text" name="front[]" placeholder="Từ tiếng Anh" required>
                <input type="text" name="back[]" placeholder="Nghĩa tiếng Việt" required>
                <button type="button" class="btn-remove-entry" onclick="removeEntry(this)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            `;
            container.appendChild(entry);
        }

        function removeEntry(btn) {
            const container = btn.closest('.card-entries');
            if (container.children.length <= 1) {
                alert('Phải có ít nhất một thẻ.');
                return;
            }
            btn.closest('.card-entry').remove();
        }

        function addEditEntry() {
            const container = document.getElementById('editCardEntries');
            if (!container) return;
            const entry = document.createElement('div');
            entry.className = 'card-entry';
            entry.innerHTML = `
                <input type="text" name="front[]" placeholder="Từ tiếng Anh" required>
                <input type="text" name="back[]" placeholder="Nghĩa tiếng Việt" required>
                <button type="button" class="btn-remove-entry" onclick="removeEntry(this)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            `;
            container.appendChild(entry);
        }

        modal.addEventListener('click', function(e) {
            if (e.target === this) closeCreateModal();
        });
    </script>
</body>
</html>