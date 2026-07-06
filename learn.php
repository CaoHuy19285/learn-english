<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bỏ comment dòng dưới nếu đã có hệ thống login
if (!isset($_SESSION['user_id'])) {
    // header("Location: login.php"); 
    // exit();
    // Tạm thời nếu chưa login, gán user_id = 2 (hoặc bạn có thể để 0 và hiển thị 0)
    $user_id = 2; // Ví dụ user demo, bạn có thể thay bằng 0 để hiển thị chưa học
} else {
    $user_id = $_SESSION['user_id'];
}

require_once 'database.php';
$db = new Database();

// Lấy danh sách danh mục (kèm description) và đếm số từ vựng, số từ đã học của user hiện tại
$sql = "SELECT 
            t.*, 
            (SELECT COUNT(*) FROM vocabulary v WHERE v.typeword_id = t.id) as total_words,
            (SELECT COUNT(*) FROM user_progress up 
             JOIN vocabulary v ON up.vocabulary_id = v.id 
             WHERE v.typeword_id = t.id 
               AND up.user_id = ? 
               AND up.status IN ('learned', 'reviewing', 'mastered')) as learned_words
        FROM typeword t";
$params = [$user_id];
$categories = $db->select($sql, $params);

$color_map = [
    'purple' => ['hex' => '#6366f1', 'bg' => '#e0e7ff'],
    'pink'   => ['hex' => '#ec4899', 'bg' => '#fce7f3'],
    'green'  => ['hex' => '#10b981', 'bg' => '#d1fae5'],
    'indigo' => ['hex' => '#4f46e5', 'bg' => '#e0e7ff'],
    'orange' => ['hex' => '#f97316', 'bg' => '#ffedd5']
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Học Tập - WordWise</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .learn-header { margin-bottom: 30px; }
        .learn-header h1 { font-size: 24px; color: #1e1b4b; margin-bottom: 5px; }
        .learn-header p { color: #64748b; font-size: 15px; margin: 0; }
        
        .cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .cat-card { background: white; border: 2px solid transparent; border-radius: 16px; padding: 24px; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
        .cat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.05); }
        .cat-card.active { border-color: #6366f1; background: #fafbff; }
        
        .cat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; color: white; }
        .cat-card h3 { font-size: 18px; margin: 0 0 5px; font-weight: 700; color: #1e1b4b;}
        .cat-stats { font-size: 13px; color: #64748b; margin-bottom: 15px; }
        
        .bar-bg { width: 100%; height: 6px; background: #e2e8f0; border-radius: 3px; margin-bottom: 10px; overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 3px; }
        .stats-row { display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; }
        
        .game-modes-container { display: none; background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); width: 100%; box-sizing: border-box; }
        .game-modes-container h3 { font-size: 16px; margin: 0 0 5px; color: #1e1b4b; }
        .gm-desc-text { font-size: 14px; color: #64748b; margin-bottom: 20px; font-style: italic; }

        .gm-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .gm-card { border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: 0.2s; text-decoration: none; display: block; background: white; }
        .gm-card:hover { border-color: #6366f1; background: #f8faff; }
        .gm-card h4 { margin: 10px 0 5px; font-size: 15px; color: #1e1b4b; font-weight: 600; }
        .gm-card p { margin: 0; font-size: 13px; color: #64748b; }
    </style>
</head>
<body class="app-layout">

    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="learn-header">
            <h1>Chọn một chủ đề</h1>
            <p>Chọn một chủ đề, sau đó chọn chế độ chơi để bắt đầu học.</p>
        </div>

        <div class="cat-grid">
            <?php foreach ($categories as $index => $cat): 
                $theme = isset($color_map[$cat['color_theme'] ?? 'purple']) ? $color_map[$cat['color_theme'] ?? 'purple'] : $color_map['purple'];
                $total = (int)$cat['total_words'];
                $learned = (int)$cat['learned_words'];
                $percent = ($total > 0) ? round(($learned / $total) * 100) : 0;
                
                $desc = isset($cat['description']) ? htmlspecialchars($cat['description'], ENT_QUOTES) : '';
            ?>
                <div class="cat-card" onclick="selectCategory(this, <?=$cat['id']?>, '<?=htmlspecialchars($cat['name'], ENT_QUOTES)?>', '<?=$desc?>')">
                    <div class="cat-icon" style="background: <?=$theme['hex']?>;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    </div>
                    <h3><?=$cat['name']?></h3>
                    <div class="cat-stats"><?=$learned?>/<?=$total?> words learned</div>
                    <div class="bar-bg">
                        <div class="bar-fill" style="width: <?=$percent?>%; background: <?=$theme['hex']?>;"></div>
                    </div>
                    <div class="stats-row" style="color: <?=$theme['hex']?>;">
                        <span><?=$percent?>% mastered</span>
                        <span>100% acc.</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="game-modes-container" id="game-modes">
            <h3>Chọn chế độ học cho chủ đề <span id="gm-title" style="color: #6366f1;"></span>:</h3>
            <p id="gm-desc" class="gm-desc-text"></p>
            
            <div class="gm-grid">
                <a href="#" id="link-flashcard" class="gm-card">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line></svg>
                    <h4>Flashcards</h4>
                    <p>Flip & learn</p>
                </a>
                <a href="#" id="link-quiz" class="gm-card">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
                    <h4>Quiz</h4>
                    <p>4-choice test</p>
                </a>
                <a href="#" id="link-match" class="gm-card">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    <h4>Match</h4>
                    <p>Pair words</p>
                </a>
            </div>
        </div>
    </main>

    <script>
        function selectCategory(element, id, name, desc) {
            document.querySelectorAll('.cat-card').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            
            document.getElementById('gm-title').innerText = name;
            
            if (desc && desc.trim() !== "") {
                document.getElementById('gm-desc').innerText = desc;
                document.getElementById('gm-desc').style.display = 'block';
            } else {
                document.getElementById('gm-desc').style.display = 'none';
            }
            
            document.getElementById('link-flashcard').href = 'flashcard.php?category_id=' + id;
            document.getElementById('link-quiz').href = 'quiz.php?category_id=' + id;
            document.getElementById('link-match').href = 'match.php?category_id=' + id;
            
            document.getElementById('game-modes').style.display = 'block';
            document.getElementById('game-modes').scrollIntoView({ behavior: 'smooth', block: 'end' });
        }
    </script>
</body>
</html>