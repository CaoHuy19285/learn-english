<?php
session_start();
// Chặn Admin: Nếu là admin, đẩy sang trang Quản lý từ vựng
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_vocab.php");
    exit();
}
// Chặn khách vãng lai
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; $_SESSION['role'] = 'user'; $_SESSION['streak'] = 7; $_SESSION['xp'] = 400; // Dữ liệu test
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bảng điều khiển - WordWise</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="app-layout">

    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="welcome-bar">
            <h1>Chào buổi tối! 👋</h1>
            <p>Hãy giữ vững phong độ — bạn có bài học cần hoàn thành hôm nay.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="st-icon icon-purple">
                    <?php $bookColor = "#6366f1"; ?>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="<?php echo $bookColor; ?>" stroke-width="2">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                </div>
                <div class="st-info"><span class="st-label">Từ đã học</span><span class="st-val">23</span></div>
            </div>

            <div class="stat-card">
                <div class="st-icon icon-orange">
                    <?php $fireColor = "#f59e0b"; ?>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C12 2 7 5.5 7 10.5C7 13.5 9.24 16 12 16C14.76 16 17 13.5 17 10.5C17 5.5 12 2 12 2Z" fill="<?php echo $fireColor; ?>" />
                        <path d="M12 18C10.34 18 9 19.34 9 21C9 22.66 10.34 24 12 24C13.66 24 15 22.66 15 21C15 19.34 13.66 18 12 18Z" fill="<?php echo $fireColor; ?>" opacity="0.6" />
                    </svg>
                </div>
                <div class="st-info"><span class="st-label">Chuỗi ngày</span><span class="st-val"><?= $_SESSION['streak'] ?></span></div>
            </div>

            <div class="stat-card">
                <div class="st-icon icon-green">
                    <?php $targetColor = "#10b981"; ?>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="<?php echo $targetColor; ?>" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="6"></circle>
                        <circle cx="12" cy="12" r="2"></circle>
                    </svg>
                </div>
                <div class="st-info"><span class="st-label">Độ chính xác</span><span class="st-val">77%</span></div>
            </div>

            <div class="stat-card">
                <div class="st-icon icon-gold">
                    <?php $starColor = "#ea580c"; ?>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="<?php echo $starColor; ?>" />
                    </svg>
                </div>
                <div class="st-info"><span class="st-label">Cấp độ</span><span class="st-val">Lv. 3</span></div>
            </div>
        </div>

        <div class="progress-banner">
            <div class="banner-header">
                <span class="banner-title">Tiến trình đạt mức Scholar</span>
                <span class="banner-value">400 / 500 XP</span>
            </div>
            <div class="banner-subtitle">Học viên · Cấp 3</div>
            <div class="bar-bg"><div class="bar-fill" style="width: 80%;"></div></div>
        </div>

        <div class="word-of-the-day">
            <div class="wotd-badge">✨ TỪ VỰNG HÔM NAY</div>
            <div class="wotd-main">
                <div>
                    <h2>API</h2>
                    <div class="wotd-ipa">/ˌeɪ.piˈaɪ/</div>
                </div>
                <button class="btn-audio">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
                </button>
            </div>
            <p class="wotd-def">Giao diện lập trình ứng dụng, tập hợp các quy tắc kết nối phần mềm.</p>
            <p class="wotd-example">"The weather app fetches live data through a public API."</p>
        </div>

        <?php include 'footer.php'; ?>
    </main>
</body>
</html>