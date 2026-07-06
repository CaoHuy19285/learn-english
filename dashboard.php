<?php
session_start();
require_once 'database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$user_id = (int)$_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';

// Lấy thông tin user
$userData = $db->select("SELECT * FROM users WHERE id = ?", [$user_id]);
if (empty($userData)) {
    session_destroy();
    header('Location: login.php');
    exit();
}
$user = $userData[0];

// ===== NẾU LÀ USER – LẤY THỐNG KÊ CÁ NHÂN =====
if ($role === 'user') {
    // 1. Số từ đã học (có tiến độ)
    $learnedResult = $db->select(
        "SELECT COUNT(DISTINCT vocabulary_id) as total 
         FROM user_progress 
         WHERE user_id = ?",
        [$user_id]
    );
    $learnedWords = (int)($learnedResult[0]['total'] ?? 0);

    // 2. Số từ đang ôn (status = 'reviewing')
    $reviewingResult = $db->select(
        "SELECT COUNT(DISTINCT vocabulary_id) as total 
         FROM user_progress 
         WHERE user_id = ? AND status = 'reviewing'",
        [$user_id]
    );
    $reviewingWords = (int)($reviewingResult[0]['total'] ?? 0);

    // 3. Số từ thành thạo (status = 'mastered')
    $masteredResult = $db->select(
        "SELECT COUNT(DISTINCT vocabulary_id) as total 
         FROM user_progress 
         WHERE user_id = ? AND status = 'mastered'",
        [$user_id]
    );
    $masteredWords = (int)($masteredResult[0]['total'] ?? 0);

    // 4. Độ chính xác
    $accuracyResult = $db->select(
        "SELECT SUM(correct_count) as correct, SUM(wrong_count) as wrong 
         FROM user_progress 
         WHERE user_id = ?",
        [$user_id]
    );
    $correct = (int)($accuracyResult[0]['correct'] ?? 0);
    $wrong   = (int)($accuracyResult[0]['wrong'] ?? 0);
    $totalAttempts = $correct + $wrong;
    $accuracy = $totalAttempts > 0 ? round(($correct / $totalAttempts) * 100) : 0;

    // 5. Streak, XP, Level
    $streak = (int)$user['streak'];
    $xp = (int)$user['xp'];
    $level = floor($xp / 100) + 1;
    $xpForNextLevel = 500;
    $progressPercent = min(100, round(($xp / $xpForNextLevel) * 100));

    // 6. Hoạt động trong tuần (từ bảng user_activity)
    $days = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
    $dailyActivity = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $count = $db->select(
            "SELECT COUNT(*) as total 
             FROM user_activity 
             WHERE user_id = ? AND DATE(created_at) = ?",
            [$user_id, $date]
        );
        $dailyActivity[] = (int)($count[0]['total'] ?? 0);
    }

    // 7. Từ vựng theo chủ đề (biểu đồ tròn)
    $topicStats = $db->select(
        "SELECT t.name, COUNT(up.vocabulary_id) as count
         FROM user_progress up
         JOIN vocabulary v ON up.vocabulary_id = v.id
         JOIN typeword t ON v.typeword_id = t.id
         WHERE up.user_id = ?
         GROUP BY t.id
         ORDER BY count DESC",
        [$user_id]
    );
    $topicLabels = array_column($topicStats, 'name');
    $topicData = array_column($topicStats, 'count');

    // 8. Từ vựng ngẫu nhiên (Word of the day)
    $wordOfDay = $db->select("SELECT * FROM vocabulary ORDER BY RAND() LIMIT 1");
    $wordOfDay = $wordOfDay[0] ?? null;

    // 9. Số flashcard đã tạo
    $flashcardCount = $db->select(
        "SELECT COUNT(*) as total FROM flashcard_sets WHERE user_id = ?",
        [$user_id]
    );
    $flashcardSets = (int)($flashcardCount[0]['total'] ?? 0);

    // 10. Số bài quiz đã làm (từ game_sessions)
    $quizCount = $db->select(
        "SELECT COUNT(*) as total FROM game_sessions WHERE user_id = ? AND status = 'completed'",
        [$user_id]
    );
    $quizzesDone = (int)($quizCount[0]['total'] ?? 0);
}

// ===== NẾU LÀ ADMIN – LẤY THỐNG KÊ HỆ THỐNG =====
if ($role === 'admin') {
    $adminStats = [];
    $totalUsers = $db->select("SELECT COUNT(*) as total FROM users");
    $adminStats['totalUsers'] = (int)($totalUsers[0]['total'] ?? 0);
    $totalWords = $db->select("SELECT COUNT(*) as total FROM vocabulary");
    $adminStats['totalWords'] = (int)($totalWords[0]['total'] ?? 0);
    $totalTopics = $db->select("SELECT COUNT(*) as total FROM typeword");
    $adminStats['totalTopics'] = (int)($totalTopics[0]['total'] ?? 0);
    $totalLearned = $db->select("SELECT COUNT(DISTINCT user_id, vocabulary_id) as total FROM user_progress");
    $adminStats['totalLearned'] = (int)($totalLearned[0]['total'] ?? 0);
    $totalFlashcards = $db->select("SELECT COUNT(*) as total FROM flashcard_sets");
    $adminStats['totalFlashcards'] = (int)($totalFlashcards[0]['total'] ?? 0);
    $totalQuizzes = $db->select("SELECT COUNT(*) as total FROM game_sessions WHERE status = 'completed'");
    $adminStats['totalQuizzes'] = (int)($totalQuizzes[0]['total'] ?? 0);

    // Người dùng mới trong 7 ngày
    $days = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
    $newUsers = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $count = $db->select(
            "SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = ?",
            [$date]
        );
        $newUsers[] = (int)($count[0]['total'] ?? 0);
    }

    // Từ vựng theo chủ đề (toàn hệ thống)
    $topicAll = $db->select(
        "SELECT t.name, COUNT(v.id) as count
         FROM typeword t
         LEFT JOIN vocabulary v ON t.id = v.typeword_id
         GROUP BY t.id
         ORDER BY count DESC"
    );
    $adminTopicLabels = array_column($topicAll, 'name');
    $adminTopicData = array_column($topicAll, 'count');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WordWise</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Nunito:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root { --gw-primary: #a855f7; --gw-secondary: #c084fc; --gw-red: #ef4444; --gw-green: #10b981; --gw-blue: #3b82f6; --gw-orange: #f97316; }
        * { box-sizing: border-box; margin:0; padding:0; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; overflow: hidden; }
        .app-layout { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 30px 40px 20px; background: #faf5ff; }

        .welcome-bar h1 { font-size: 28px; font-weight: 900; color: #1e293b; margin-bottom: 4px; display: flex; align-items: center; gap: 12px; font-family: 'Nunito', sans-serif; }
        .welcome-bar p { color: #64748b; font-weight: 500; font-size: 15px; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin: 24px 0 28px; }
        .stat-card { background: white; border-radius: 16px; padding: 16px 18px; border: 1px solid #e9d5ff; box-shadow: 0 2px 8px rgba(168,85,247,0.04); transition: 0.2s; display: flex; align-items: center; gap: 14px; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(168,85,247,0.08); }
        .st-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .st-info { display: flex; flex-direction: column; }
        .st-label { font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.3px; }
        .st-val { font-size: 22px; font-weight: 900; color: #1e293b; line-height: 1.2; }
        .st-val small { font-size: 13px; font-weight: 600; color: #94a3b8; margin-left: 4px; }

        .chart-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .chart-box { background: white; border-radius: 20px; padding: 20px; border: 1px solid #e9d5ff; box-shadow: 0 2px 8px rgba(168,85,247,0.04); }
        .chart-box h3 { font-size: 16px; font-weight: 800; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .chart-box h3 svg { stroke: var(--gw-primary); }
        .chart-container { position: relative; height: 200px; }

        .progress-banner { background: white; border-radius: 20px; padding: 20px 24px; border: 1px solid #e9d5ff; margin-bottom: 28px; }
        .banner-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; }
        .banner-title { font-weight: 800; font-size: 16px; color: #1e293b; }
        .banner-value { font-weight: 700; color: #a855f7; background: #f3e8ff; padding: 2px 14px; border-radius: 30px; font-size: 13px; }
        .banner-subtitle { color: #94a3b8; font-weight: 500; font-size: 13px; margin: 2px 0 12px; }
        .bar-bg { width: 100%; height: 6px; background: #e9d5ff; border-radius: 20px; overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 20px; background: linear-gradient(90deg, #a855f7, #d8b4fe); transition: width 0.6s ease; }

        .admin-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 14px; margin-bottom: 28px; }
        .admin-stat-item { background: white; padding: 16px; border-radius: 16px; border: 1px solid #e9d5ff; text-align: center; }
        .admin-stat-item .label { font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; }
        .admin-stat-item .value { font-size: 24px; font-weight: 900; color: #a855f7; margin-top: 2px; }

        .word-of-the-day { background: white; border-radius: 20px; padding: 20px 24px; border: 1px solid #e9d5ff; margin-top: 0; }
        .wotd-badge { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; background: #ede9fe; color: #7c3aed; padding: 2px 14px; border-radius: 30px; display: inline-block; margin-bottom: 10px; }
        .wotd-main { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .wotd-main h2 { font-size: 26px; font-weight: 900; color: #1e293b; font-family: 'Nunito', sans-serif; }
        .wotd-ipa { font-size: 15px; font-weight: 500; color: #94a3b8; }
        .btn-audio { background: none; border: none; cursor: pointer; color: #a855f7; padding: 6px; border-radius: 50%; transition: 0.2s; display: flex; align-items: center; justify-content: center; }
        .btn-audio:hover { background: #ede9fe; }
        .wotd-def { font-size: 15px; color: #475569; margin: 6px 0 2px; font-weight: 500; }
        .wotd-example { font-style: italic; color: #64748b; font-size: 14px; background: #f8fafc; padding: 10px 16px; border-radius: 12px; border-left: 3px solid #a855f7; margin-top: 10px; }

        @media (max-width: 768px) {
            .main-content { padding: 16px; }
            .chart-row { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .admin-stats { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .admin-stats { grid-template-columns: 1fr; }
            .wotd-main { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body class="app-layout">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <!-- ===== WELCOME ===== -->
        <div class="welcome-bar">
            <h1>
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M12 8v4l2 2"/>
                </svg>
                <?php if ($role === 'admin'): ?>
                    Chào Admin, <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>!
                <?php else: ?>
                    Chào <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>!
                <?php endif; ?>
            </h1>
            <p>
                <?php if ($role === 'admin'): ?>
                    Đây là tổng quan hệ thống. Bạn có thể quản lý từ vựng, chủ đề và người dùng.
                <?php else: ?>
                    Hãy giữ vững phong độ — bạn có bài học cần hoàn thành hôm nay.
                <?php endif; ?>
            </p>
        </div>

        <!-- ===== USER DASHBOARD ===== -->
        <?php if ($role === 'user'): ?>
            <!-- Stats cá nhân -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="st-icon" style="background:#ede9fe;"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
                    <div class="st-info"><span class="st-label">Đã học</span><span class="st-val"><?= $learnedWords ?></span></div>
                </div>
                <div class="stat-card">
                    <div class="st-icon" style="background:#fef3c7;"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l2 2"/></svg></div>
                    <div class="st-info"><span class="st-label">Đang ôn</span><span class="st-val"><?= $reviewingWords ?></span></div>
                </div>
                <div class="stat-card">
                    <div class="st-icon" style="background:#d1fae5;"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div class="st-info"><span class="st-label">Thành thạo</span><span class="st-val"><?= $masteredWords ?></span></div>
                </div>
                <div class="stat-card">
                    <div class="st-icon" style="background:#fde68a;"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                    <div class="st-info"><span class="st-label">Cấp độ</span><span class="st-val">Lv. <?= $level ?></span></div>
                </div>
                <div class="stat-card">
                    <div class="st-icon" style="background:#e0f2fe;"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v4l2 2"/></svg></div>
                    <div class="st-info"><span class="st-label">Chính xác</span><span class="st-val"><?= $totalAttempts > 0 ? $accuracy.'%' : '—' ?></span></div>
                </div>
                <div class="stat-card">
                    <div class="st-icon" style="background:#fce7f3;"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div>
                    <div class="st-info"><span class="st-label">Chuỗi ngày</span><span class="st-val"><?= $streak ?></span></div>
                </div>
            </div>

            <!-- Progress banner -->
            <div class="progress-banner">
                <div class="banner-header">
                    <span class="banner-title">Tiến trình Scholar</span>
                    <span class="banner-value"><?= $xp ?> / <?= $xpForNextLevel ?> XP</span>
                </div>
                <div class="banner-subtitle">
                    <?php if ($xp < $xpForNextLevel): ?>
                        Cần <?= $xpForNextLevel - $xp ?> XP nữa để lên cấp tiếp
                    <?php else: ?>
                        Chúc mừng! Bạn đã đạt mức Scholar!
                    <?php endif; ?>
                </div>
                <div class="bar-bg"><div class="bar-fill" style="width: <?= $progressPercent ?>%;"></div></div>
            </div>

            <!-- Charts -->
            <div class="chart-row">
                <div class="chart-box">
                    <h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2v20M2 12h20"/></svg> Từ vựng theo chủ đề</h3>
                    <div class="chart-container"><canvas id="topicChart"></canvas></div>
                </div>
                <div class="chart-box">
                    <h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg> Hoạt động trong tuần</h3>
                    <div class="chart-container"><canvas id="weeklyChart"></canvas></div>
                </div>
            </div>

            <!-- Word of the day -->
            <?php if ($wordOfDay): ?>
            <div class="word-of-the-day">
                <div class="wotd-badge"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> TỪ VỰNG HÔM NAY</div>
                <div class="wotd-main">
                    <div>
                        <h2><?= htmlspecialchars($wordOfDay['word']) ?></h2>
                        <div class="wotd-ipa"><?= htmlspecialchars($wordOfDay['ipa']) ?></div>
                    </div>
                    <button class="btn-audio" onclick="speak('<?= addslashes($wordOfDay['word']) ?>')">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                    </button>
                </div>
                <p class="wotd-def"><?= htmlspecialchars($wordOfDay['definition']) ?></p>
                <?php if ($wordOfDay['example']): ?>
                    <p class="wotd-example">"<?= htmlspecialchars($wordOfDay['example']) ?>"</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- ===== ADMIN DASHBOARD ===== -->
        <?php if ($role === 'admin'): ?>
            <div class="admin-stats">
                <div class="admin-stat-item"><div class="label">Người dùng</div><div class="value"><?= $adminStats['totalUsers'] ?></div></div>
                <div class="admin-stat-item"><div class="label">Từ vựng</div><div class="value"><?= $adminStats['totalWords'] ?></div></div>
                <div class="admin-stat-item"><div class="label">Chủ đề</div><div class="value"><?= $adminStats['totalTopics'] ?></div></div>
                <div class="admin-stat-item"><div class="label">Lượt học</div><div class="value"><?= $adminStats['totalLearned'] ?></div></div>
                <div class="admin-stat-item"><div class="label">Flashcard</div><div class="value"><?= $adminStats['totalFlashcards'] ?></div></div>
                <div class="admin-stat-item"><div class="label">Bài quiz</div><div class="value"><?= $adminStats['totalQuizzes'] ?></div></div>
            </div>

            <div class="chart-row">
                <div class="chart-box">
                    <h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Người dùng mới (7 ngày)</h3>
                    <div class="chart-container"><canvas id="newUsersChart"></canvas></div>
                </div>
                <div class="chart-box">
                    <h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg> Từ vựng theo chủ đề</h3>
                    <div class="chart-container"><canvas id="adminTopicChart"></canvas></div>
                </div>
            </div>
        <?php endif; ?>

        <?php include 'footer.php'; ?>
    </main>

    <script>
        // ===== USER CHARTS =====
        <?php if ($role === 'user'): ?>
        const topicLabels = <?= json_encode($topicLabels) ?>;
        const topicData = <?= json_encode($topicData) ?>;
        const dailyActivity = <?= json_encode($dailyActivity) ?>;
        const days = <?= json_encode($days) ?>;

        if (document.getElementById('topicChart')) {
            new Chart(document.getElementById('topicChart'), {
                type: 'doughnut',
                data: {
                    labels: topicLabels.length ? topicLabels : ['Chưa có dữ liệu'],
                    datasets: [{
                        data: topicLabels.length ? topicData : [1],
                        backgroundColor: ['#a855f7','#ec4899','#22c55e','#f97316','#3b82f6','#eab308','#14b8a6','#8b5cf6','#f59e0b','#ef4444'],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } } }, cutout: '60%' }
            });
        }

        if (document.getElementById('weeklyChart')) {
            new Chart(document.getElementById('weeklyChart'), {
                type: 'bar',
                data: {
                    labels: days,
                    datasets: [{ label: 'Lượt học', data: dailyActivity, backgroundColor: '#a855f7', borderRadius: 6, barPercentage: 0.6 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } } }
            });
        }
        <?php endif; ?>

        // ===== ADMIN CHARTS =====
        <?php if ($role === 'admin'): ?>
        const newUsersData = <?= json_encode($newUsers) ?>;
        const adminDays = <?= json_encode($days) ?>;
        const adminTopicLabels = <?= json_encode($adminTopicLabels) ?>;
        const adminTopicData = <?= json_encode($adminTopicData) ?>;

        if (document.getElementById('newUsersChart')) {
            new Chart(document.getElementById('newUsersChart'), {
                type: 'line',
                data: {
                    labels: adminDays,
                    datasets: [{ label: 'Người dùng mới', data: newUsersData, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.3, pointBackgroundColor: '#3b82f6' }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } } }
            });
        }

        if (document.getElementById('adminTopicChart')) {
            new Chart(document.getElementById('adminTopicChart'), {
                type: 'doughnut',
                data: {
                    labels: adminTopicLabels.length ? adminTopicLabels : ['Chưa có dữ liệu'],
                    datasets: [{
                        data: adminTopicLabels.length ? adminTopicData : [1],
                        backgroundColor: ['#a855f7','#ec4899','#22c55e','#f97316','#3b82f6','#eab308','#14b8a6','#8b5cf6','#f59e0b','#ef4444'],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } } }, cutout: '60%' }
            });
        }
        <?php endif; ?>

        function speak(text) {
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'en-US';
                utterance.rate = 0.9;
                window.speechSynthesis.speak(utterance);
            }
        }
    </script>
</body>
</html>