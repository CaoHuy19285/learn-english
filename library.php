<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$user_id = (int)$_SESSION['user_id'];

// Lấy thống kê tổng quan
// 1. Tổng số flashcard sets
$totalSets = $db->select("SELECT COUNT(*) as total FROM flashcard_sets WHERE user_id = ?", [$user_id]);
$totalSets = (int)($totalSets[0]['total'] ?? 0);

// 2. Tổng số thẻ trong tất cả flashcard sets
$totalCards = $db->select(
    "SELECT COUNT(*) as total FROM flashcard_cards WHERE set_id IN (SELECT id FROM flashcard_sets WHERE user_id = ?)",
    [$user_id]
);
$totalCards = (int)($totalCards[0]['total'] ?? 0);

// 3. Tổng số từ vựng đã học
$learnedWords = $db->select(
    "SELECT COUNT(DISTINCT vocabulary_id) as total FROM user_progress WHERE user_id = ?",
    [$user_id]
);
$learnedWords = (int)($learnedWords[0]['total'] ?? 0);

// 4. Tổng số thư mục
$totalFolders = $db->select("SELECT COUNT(*) as total FROM folders WHERE user_id = ?", [$user_id]);
$totalFolders = (int)($totalFolders[0]['total'] ?? 0);

// 5. Tổng số bài quiz đã hoàn thành
$totalQuizzes = $db->select(
    "SELECT COUNT(*) as total FROM game_sessions WHERE user_id = ? AND status = 'completed'",
    [$user_id]
);
$totalQuizzes = (int)($totalQuizzes[0]['total'] ?? 0);

// Lấy danh sách flashcard sets gần đây nhất (10 sets)
$recentSets = $db->select(
    "SELECT s.*, (SELECT COUNT(*) FROM flashcard_cards WHERE set_id = s.id) as card_count
     FROM flashcard_sets s
     WHERE s.user_id = ?
     ORDER BY s.updated_at DESC, s.created_at DESC
     LIMIT 10",
    [$user_id]
);

// Lấy danh sách thư mục gần đây (5 thư mục)
$recentFolders = $db->select(
    "SELECT f.*, (SELECT COUNT(*) FROM folder_items WHERE folder_id = f.id) as item_count
     FROM folders f
     WHERE f.user_id = ?
     ORDER BY f.updated_at DESC, f.created_at DESC
     LIMIT 5",
    [$user_id]
);

// Lấy danh sách từ vựng đã học gần đây (dựa trên user_progress)
$recentWords = $db->select(
    "SELECT v.word, v.definition, v.ipa, up.created_at
     FROM user_progress up
     JOIN vocabulary v ON up.vocabulary_id = v.id
     WHERE up.user_id = ?
     ORDER BY up.created_at DESC
     LIMIT 10",
    [$user_id]
);

$pageTitle = "Thư viện - WordWise";
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
        .page-header h1 svg { stroke: #a855f7; }

        /* Stats grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 16px; margin-bottom: 30px; }
        .stat-card { background: white; border-radius: 16px; padding: 18px; border: 1px solid #e9d5ff; text-align: center; box-shadow: 0 2px 8px rgba(168,85,247,0.04); }
        .stat-card .number { font-size: 28px; font-weight: 900; color: #a855f7; }
        .stat-card .label { font-size: 13px; color: #94a3b8; font-weight: 600; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.3px; }

        /* Section */
        .section { margin-bottom: 32px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .section-header h2 { font-size: 18px; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 8px; }
        .section-header h2 svg { stroke: #a855f7; }
        .section-header .view-all { color: #a855f7; text-decoration: none; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 4px; }
        .section-header .view-all:hover { text-decoration: underline; }

        .set-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
        .set-card { background: white; border-radius: 16px; padding: 18px; border: 1px solid #e9d5ff; transition: 0.2s; text-decoration: none; color: #1e293b; display: flex; flex-direction: column; }
        .set-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(168,85,247,0.06); border-color: #a855f7; }
        .set-card .title { font-weight: 700; font-size: 16px; margin-bottom: 4px; }
        .set-card .meta { font-size: 13px; color: #94a3b8; display: flex; align-items: center; gap: 6px; }
        .set-card .author { font-size: 12px; color: #a855f7; font-weight: 600; margin-top: 8px; display: flex; align-items: center; gap: 4px; }

        .word-list { display: flex; flex-direction: column; gap: 10px; }
        .word-item { background: white; border-radius: 12px; padding: 12px 16px; border: 1px solid #e9d5ff; display: flex; justify-content: space-between; align-items: center; }
        .word-item .word { font-weight: 700; color: #1e293b; }
        .word-item .definition { color: #64748b; font-size: 14px; }
        .word-item .ipa { color: #94a3b8; font-size: 13px; font-family: monospace; }

        .empty-state { text-align: center; padding: 40px 20px; color: #94a3b8; }
        .empty-state svg { width: 60px; height: 60px; stroke: #cbd5e1; margin-bottom: 12px; }

        .folder-tag { display: inline-flex; align-items: center; gap: 4px; background: #f3e8ff; color: #7c3aed; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; margin-right: 8px; }
        .folder-tag svg { stroke: #7c3aed; }

        @media (max-width: 640px) { .main-content { padding: 16px; } .stats-grid { grid-template-columns: 1fr 1fr; } .set-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="app-layout">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2">
                    <path d="M3 6h18"></path>
                    <path d="M3 12h18"></path>
                    <path d="M3 18h18"></path>
                </svg>
                Thư viện của bạn
            </h1>
        </div>

        <!-- Thống kê -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?= $totalSets ?></div>
                <div class="label">Học phần</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $totalCards ?></div>
                <div class="label">Thẻ ghi nhớ</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $learnedWords ?></div>
                <div class="label">Từ đã học</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $totalFolders ?></div>
                <div class="label">Thư mục</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $totalQuizzes ?></div>
                <div class="label">Bài quiz</div>
            </div>
        </div>

        <!-- Gần đây - Flashcard Sets -->
        <div class="section">
            <div class="section-header">
                <h2>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v4l2 2"/></svg>
                    Gần đây
                </h2>
                <a href="card.php" class="view-all">
                    Xem tất cả
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>

            <?php if (empty($recentSets)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/></svg>
                    <p>Bạn chưa có học phần nào. Hãy tạo flashcard đầu tiên!</p>
                </div>
            <?php else: ?>
                <div class="set-grid">
                    <?php foreach ($recentSets as $set): ?>
                        <a href="flashcard.php?set_id=<?= $set['id'] ?>" class="set-card">
                            <div class="title"><?= htmlspecialchars($set['title']) ?></div>
                            <div class="meta">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/></svg>
                                <?= $set['card_count'] ?> thẻ
                            </div>
                            <div class="author">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Bạn
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Thư mục -->
        <div class="section">
            <div class="section-header">
                <h2>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    Thư mục
                </h2>
                <a href="folder.php" class="view-all">
                    Xem tất cả
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>

            <?php if (empty($recentFolders)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    <p>Chưa có thư mục nào. Tạo thư mục để sắp xếp tài liệu!</p>
                </div>
            <?php else: ?>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr)); gap:12px;">
                    <?php foreach ($recentFolders as $folder): ?>
                        <a href="folder.php?id=<?= $folder['id'] ?>" style="background:white; border-radius:12px; padding:16px; border:1px solid #e9d5ff; text-decoration:none; color:#1e293b; transition:0.2s; display:flex; align-items:center; gap:12px;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                            <div>
                                <div style="font-weight:700;"><?= htmlspecialchars($folder['name']) ?></div>
                                <div style="font-size:12px; color:#94a3b8;"><?= $folder['item_count'] ?> tài liệu</div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Từ vựng đã học gần đây -->
        <div class="section">
            <div class="section-header">
                <h2>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    Từ vựng đã học
                </h2>
            </div>

            <?php if (empty($recentWords)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    <p>Bạn chưa học từ nào. Bắt đầu học ngay!</p>
                </div>
            <?php else: ?>
                <div class="word-list">
                    <?php foreach ($recentWords as $w): ?>
                        <div class="word-item">
                            <div>
                                <span class="word"><?= htmlspecialchars($w['word']) ?></span>
                                <span class="ipa"><?= htmlspecialchars($w['ipa']) ?></span>
                            </div>
                            <div class="definition"><?= htmlspecialchars($w['definition']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php include 'footer.php'; ?>
    </main>
</body>
</html>