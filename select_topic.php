<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$type = $_GET['type'] ?? 'flashcard';
$user_id = (int)$_SESSION['user_id'];

// Màu sắc theo loại
$typeColors = [
    'flashcard' => ['primary' => '#a855f7', 'light' => '#f3e8ff', 'border' => '#e9d5ff', 'bg' => '#faf5ff'],
    'quiz'      => ['primary' => '#3b82f6', 'light' => '#eff6ff', 'border' => '#bfdbfe', 'bg' => '#f5f9ff'],
    'match'     => ['primary' => '#f97316', 'light' => '#fff7ed', 'border' => '#fed7aa', 'bg' => '#fffaf5'],
];
$color = $typeColors[$type] ?? $typeColors['flashcard'];

$topics = $db->select("SELECT id, name, image, color_theme FROM typeword ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['topic_id'])) {
    $topic_id = (int)$_POST['topic_id'];
    switch ($type) {
        case 'flashcard': header("Location: flashcard.php?category_id=$topic_id"); break;
        case 'quiz':      header("Location: quiz.php?category_id=$topic_id"); break;
        case 'match':     header("Location: match.php?category_id=$topic_id"); break;
        default:          header("Location: dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn chủ đề - WordWise</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Nunito:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        * { box-sizing: border-box; margin:0; padding:0; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; overflow: hidden; }
        .app-layout { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 30px 40px 20px; background: <?= $color['bg'] ?>; }

        .page-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; }
        .page-header h1 { font-size: 28px; font-weight: 900; color: #1e293b; font-family: 'Nunito', sans-serif; display: flex; align-items: center; gap: 12px; }
        .page-header h1 svg { stroke: <?= $color['primary'] ?>; }
        .page-header p { color: #64748b; font-weight: 500; }

        .topic-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; margin-top: 10px; }
        .topic-card { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px 16px; background: white; border-radius: 20px; border: 2px solid <?= $color['border'] ?>; box-shadow: 0 4px 12px rgba(0,0,0,0.02); transition: 0.25s; cursor: pointer; text-align: center; background: <?= $color['light'] ?>; width: 100%; font-family: inherit; font-size: inherit; }
        .topic-card:hover { border-color: <?= $color['primary'] ?>; transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.06); background: white; }
        .topic-card .card-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; background: white; }
        .topic-card .card-icon svg { stroke: <?= $color['primary'] ?>; }
        .topic-card .topic-image { width: 56px; height: 56px; border-radius: 12px; object-fit: cover; margin-bottom: 12px; }
        .topic-card .name { font-weight: 800; font-size: 16px; color: #1e293b; }
        .topic-card .count { font-size: 13px; color: #94a3b8; margin-top: 4px; }

        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .back-link { display: inline-flex; align-items: center; gap: 6px; color: <?= $color['primary'] ?>; text-decoration: none; font-weight: 600; margin-bottom: 8px; }
        .back-link:hover { text-decoration: underline; }

        @media (max-width: 640px) { .main-content { padding: 16px; } .topic-grid { grid-template-columns: 1fr 1fr; } }
    </style>
</head>
<body class="app-layout">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <a href="dashboard.php" class="back-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Về trang chủ
        </a>

        <div class="page-header">
            <div>
                <h1>
                    <?php if ($type === 'flashcard'): ?>
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/></svg>
                        Chọn chủ đề Flashcard
                    <?php elseif ($type === 'quiz'): ?>
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                        Chọn chủ đề Quiz
                    <?php else: ?>
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        Chọn chủ đề Match
                    <?php endif; ?>
                </h1>
                <p>Chọn một chủ đề để bắt đầu học.</p>
            </div>
        </div>

        <?php if (empty($topics)): ?>
            <div class="empty-state">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                <p style="font-size:18px; font-weight:600; color:#64748b;">Chưa có chủ đề nào</p>
            </div>
        <?php else: ?>
            <form method="POST" id="topicForm">
                <div class="topic-grid">
                    <?php foreach ($topics as $topic): 
                        $count = $db->select("SELECT COUNT(*) as total FROM vocabulary WHERE typeword_id = ?", [$topic['id']]);
                        $wordCount = $count[0]['total'] ?? 0;
                    ?>
                        <button type="submit" name="topic_id" value="<?= $topic['id'] ?>" class="topic-card" onclick="return confirm('Bạn có chắc muốn chọn chủ đề \'<?= htmlspecialchars($topic['name']) ?>\'?')">
                            <?php if (!empty($topic['image'])): ?>
                                <img src="<?= htmlspecialchars($topic['image']) ?>" alt="<?= htmlspecialchars($topic['name']) ?>" class="topic-image">
                            <?php else: ?>
                                <div class="card-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <?php if ($type === 'flashcard'): ?>
                                            <rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/>
                                        <?php elseif ($type === 'quiz'): ?>
                                            <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>
                                        <?php else: ?>
                                            <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                        <?php endif; ?>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <div class="name"><?= htmlspecialchars($topic['name']) ?></div>
                            <div class="count"><?= $wordCount ?> từ vựng</div>
                        </button>
                    <?php endforeach; ?>
                </div>
            </form>
        <?php endif; ?>

    </main>
</body>
</html>