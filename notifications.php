<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'database.php';
$db = new Database();
$user_id = $_SESSION['user_id'];

// Xử lý Xóa thông báo lẻ hoặc tất cả
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'delete' && isset($_GET['id'])) {
        $db->execute("DELETE FROM notifications WHERE id = ? AND user_id = ?", [$_GET['id'], $user_id]);
    } elseif ($_GET['action'] == 'clear_all') {
        $db->execute("DELETE FROM notifications WHERE user_id = ?", [$user_id]);
    }
    header("Location: notifications.php");
    exit;
}

// Khi xem trang này thì tự động chuyển trạng thái thành "đã đọc"
$db->execute("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0", [$user_id]);

// Đọc danh sách thông báo
$notifications = $db->select("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC", [$user_id]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo - WordWise</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        body { background: #f8fafc; font-family: 'Inter', sans-serif; overflow: hidden; margin: 0; }
        .app-layout { display: flex; width: 100vw; height: 100vh; }
        .main-content { flex-grow: 1; padding: 40px 60px; overflow-y: auto; }
        
        .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title { font-size: 32px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 12px; }
        .page-title svg { color: #a855f7; }
        
        .btn-clear { background: #fee2e2; color: #ef4444; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 6px; transition: 0.2s; font-size: 14px;}
        .btn-clear:hover { background: #ef4444; color: white; }

        .notif-list { display: flex; flex-direction: column; gap: 15px; max-width: 800px; }
        
        .notif-card { background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; display: flex; gap: 15px; align-items: flex-start; transition: 0.3s; position: relative;}
        .notif-card:hover { box-shadow: 0 10px 25px rgba(0,0,0,0.03); border-color: #cbd5e1; }
        .notif-card.unread { border-left: 4px solid #a855f7; background: #faf5ff; }
        
        .notif-icon-wrap { background: rgba(168, 85, 247, 0.08); color: #a855f7; width: 46px; height: 46px; display: flex; align-items: center; justify-content: center; border-radius: 12px; flex-shrink: 0;}
        
        .notif-body { flex-grow: 1; }
        .notif-title { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0; }
        .notif-msg { font-size: 14px; color: #475569; margin: 0 0 10px 0; line-height: 1.5; }
        .notif-time { font-size: 12px; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 5px;}
        
        .btn-delete { background: none; border: none; color: #94a3b8; cursor: pointer; padding: 6px; transition: 0.2s; border-radius: 8px; display: flex; align-items: center; justify-content: center;}
        .btn-delete:hover { background: #fee2e2; color: #ef4444; }

        .empty-state { text-align: center; padding: 80px 20px; color: #64748b; }
        .empty-state svg { width: 64px; height: 64px; color: #cbd5e1; margin-bottom: 16px; }
        .empty-state h3 { font-size: 18px; color: #1e293b; margin-bottom: 8px;}
    </style>
</head>
<body>
    <div class="app-layout">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header-action">
                <h1 class="page-title">
                    <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    Thông báo
                </h1>
                
                <?php if (!empty($notifications)): ?>
                <a href="notifications.php?action=clear_all" class="btn-clear" onclick="return confirm('Bạn có chắc chắn muốn xoá toàn bộ thông báo?')">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    Xóa tất cả
                </a>
                <?php endif; ?>
            </div>

            <div class="notif-list">
                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <h3>Không có thông báo nào</h3>
                        <p>Hệ thống sẽ gửi lời nhắc học tập hoặc sự kiện tại đây khi có.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $n): 
                        // Chuyển đổi định dạng ngày tháng năm sang: Giờ:Phút - Ngày/Tháng/Năm
                        $date = new DateTime($n['created_at']);
                        $formattedDate = $date->format('H:i - d/m/Y');
                    ?>
                    <div class="notif-card <?= $n['is_read'] == 0 ? 'unread' : '' ?>">
                        <div class="notif-icon-wrap">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        </div>
                        <div class="notif-body">
                            <h4 class="notif-title"><?= htmlspecialchars($n['title']) ?></h4>
                            <p class="notif-msg"><?= htmlspecialchars($n['message']) ?></p>
                            <span class="notif-time">
                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                <?= $formattedDate ?>
                            </span>
                        </div>
                        <a href="notifications.php?action=delete&id=<?= $n['id'] ?>" class="btn-delete" title="Xóa thông báo này">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>