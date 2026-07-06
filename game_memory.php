<?php 
session_start(); 
require_once 'database.php';

$db = new Database(); 

// Lấy danh sách chủ đề cho màn hình Setup
$topics = $db->select("SELECT id, name FROM typeword");

// Nhận tham số cấu hình game
$topic_id = $_GET['topic'] ?? 'all';
$difficulty = $_GET['difficulty'] ?? null;

$cardsJSON = "[]";
$timeLimit = 0;
$gridClass = "";

if ($difficulty) {
    // Thiết lập số lượng cặp từ và thời gian dựa trên độ khó
    $pairs = 8; // Mặc định 4x4
    if ($difficulty === 'easy') { $pairs = 8; $timeLimit = 60; $gridClass = "grid-4x4"; } // 16 thẻ
    if ($difficulty === 'medium') { $pairs = 12; $timeLimit = 90; $gridClass = "grid-4x6"; } // 24 thẻ
    if ($difficulty === 'hard') { $pairs = 18; $timeLimit = 120; $gridClass = "grid-6x6"; } // 36 thẻ

    $whereClause = "1=1";
    $params = [];

    // Lọc theo chủ đề
    if ($topic_id !== 'all') {
        $whereClause .= " AND typeword_id = ?";
        $params[] = (int)$topic_id;
    }
    $params[] = (int)$pairs;

    // Lấy từ vựng VÀ typeword_id để làm màu nền
    $words = $db->select("SELECT id, word, definition, typeword_id FROM vocabulary WHERE $whereClause ORDER BY RAND() LIMIT ?", $params);
    
    // Nếu không đủ từ, lấy thêm ngẫu nhiên cho đủ pairs (fallback)
    if(count($words) > 0 && count($words) < $pairs) {
        $needed = $pairs - count($words);
        $extra = $db->select("SELECT id, word, definition, typeword_id FROM vocabulary ORDER BY RAND() LIMIT ?", [$needed]);
        $words = array_merge($words, $extra);
    }

    $cardsJSON = json_encode($words);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Match VIP - Nhóm Màu Theo Chủ Đề</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Nunito:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        :root {
            --gw-primary: #a855f7; 
            --gw-primary-shadow: #9333ea;
            --gw-secondary: #c084fc; 
            --gw-light: #faf5ff;
            --gw-border: #e9d5ff;
            --gw-red: #ef4444; 
            --gw-green: #10b981; 
            --bg-main: #f8fafc; 
            --bg-panel: #ffffff;
            --text-main: #1e293b; 
            --text-muted: #64748b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-main); height: 100vh; overflow: hidden; display: flex; }
        
        .app-layout { display: flex; width: 100%; height: 100%; }
        .main-content { flex-grow: 1; display: flex; flex-direction: column; position: relative; background: var(--bg-panel); border-radius: 24px 0 0 24px; box-shadow: -5px 0 25px rgba(0,0,0,0.05); overflow: hidden; }

        /* Nút quay lại */
        .btn-back-corner { position: absolute; top: 25px; left: 25px; display: flex; align-items: center; gap: 8px; padding: 12px 20px; border-radius: 14px; background: white; color: var(--text-main); font-weight: 800; text-decoration: none; border: 2px solid #e2e8f0; box-shadow: 0 4px 0 #e2e8f0; transition: 0.15s; z-index: 100;}
        .btn-back-corner:hover { border-color: var(--gw-primary); color: var(--gw-primary); transform: translateY(-2px); box-shadow: 0 6px 0 var(--gw-primary-shadow); }
        .btn-back-corner:active { transform: translateY(4px); box-shadow: none; }
        .btn-back-corner svg { width: 20px; height: 20px; stroke: currentColor; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; fill: none; }

        /* === SETUP SCREEN === */
        .setup-screen { display: flex; flex-direction: column; align-items: center; justify-content: flex-start; height: 100%; padding: 40px; padding-top: 80px; background: var(--gw-light); overflow-y: auto;}
        .setup-header { text-align: center; margin-bottom: 40px; }
        .game-icon-svg { width: 80px; height: 80px; stroke: var(--gw-primary); stroke-width: 2; fill: none; margin-bottom: 15px; }
        .setup-title { font-size: 36px; font-weight: 900; color: var(--gw-primary); font-family: 'Nunito', sans-serif;}
        .setup-desc { color: var(--text-muted); font-weight: 600; font-size: 16px; margin-top: 8px; }

        .setup-form { background: var(--bg-panel); padding: 40px; border-radius: 24px; border: 2px solid var(--gw-border); width: 100%; max-width: 700px; box-shadow: 0 20px 40px rgba(168,85,247,0.08); }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; font-weight: 800; margin-bottom: 12px; font-size: 17px; color: var(--text-main);}
        
        .difficulty-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .diff-card { cursor: pointer; position: relative; display: block; }
        .diff-card input { display: none; }
        .card-content { border: 2px solid #e2e8f0; border-radius: 18px; padding: 20px 15px; text-align: center; transition: 0.25s ease; background: var(--bg-panel); height: 100%;}
        .card-title { font-size: 20px; font-weight: 900; margin-bottom: 6px; }
        .card-desc { font-size: 13px; color: var(--text-muted); font-weight: 600; line-height: 1.4; }
        
        .diff-card.easy input:checked + .card-content { border-color: var(--gw-green); background: rgba(16, 185, 129, 0.05); box-shadow: 0 6px 0 var(--gw-green); transform: translateY(-4px); }
        .diff-card.easy .card-title { color: var(--gw-green); }
        
        .diff-card.medium input:checked + .card-content { border-color: var(--gw-primary); background: rgba(168, 85, 247, 0.05); box-shadow: 0 6px 0 var(--gw-primary); transform: translateY(-4px); }
        .diff-card.medium .card-title { color: var(--gw-primary); }
        
        .diff-card.hard input:checked + .card-content { border-color: var(--gw-red); background: rgba(239, 68, 68, 0.05); box-shadow: 0 6px 0 var(--gw-red); transform: translateY(-4px); }
        .diff-card.hard .card-title { color: var(--gw-red); }

        .btn-start { width: 100%; padding: 20px; font-size: 20px; font-weight: 900; background: var(--gw-primary); color: white; border: none; border-radius: 16px; cursor: pointer; box-shadow: 0 6px 0 var(--gw-primary-shadow); transition: 0.15s; text-transform: uppercase; margin-top: 15px;}
        .btn-start:active { transform: translateY(6px); box-shadow: none; }

        /* === CUSTOM DROPDOWN === */
        .custom-select-wrapper { position: relative; width: 100%; user-select: none; }
        .custom-select-trigger { display: flex; justify-content: space-between; align-items: center; background: var(--bg-main); border: 2px solid #e2e8f0; padding: 14px 18px; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; }
        .custom-select-trigger:hover, .custom-select-wrapper.active .custom-select-trigger { border-color: var(--gw-primary); background: rgba(168, 85, 247, 0.05); box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.15); }
        .trigger-left { display: flex; align-items: center; gap: 10px; font-weight: 600; color: var(--text-main); }
        .trigger-left .t-icon { width: 20px; height: 20px; stroke: var(--gw-primary); fill: none; stroke-width: 2; }
        .arrow-icon { width: 20px; height: 20px; stroke: #94a3b8; fill: none; stroke-width: 2; transition: transform 0.3s; }
        .custom-select-wrapper.active .arrow-icon { transform: rotate(180deg); stroke: var(--gw-primary); }
        
        .custom-options-dropdown { position: absolute; top: calc(100% + 8px); left: 0; width: 100%; background: var(--bg-panel); border: 2px solid var(--gw-border); border-radius: 12px; box-shadow: 0 10px 25px rgba(168,85,247,0.1); opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); z-index: 100; overflow: hidden; }
        .custom-select-wrapper.active .custom-options-dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
        .search-box-holder { padding: 12px; border-bottom: 2px solid var(--gw-border); background: var(--gw-light); }
        .dropdown-search-input { width: 100%; padding: 10px 14px; background: var(--bg-panel); border: 2px solid #e2e8f0; border-radius: 8px; color: var(--text-main); outline: none; font-family: 'Inter', sans-serif; transition: 0.2s; font-weight: 600; }
        .dropdown-search-input:focus { border-color: var(--gw-primary); box-shadow: 0 0 0 3px rgba(168,85,247,0.15); }
        .options-scroller { max-height: 250px; overflow-y: auto; padding: 8px; }
        .options-scroller::-webkit-scrollbar { width: 6px; }
        .options-scroller::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-option { display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; cursor: pointer; transition: background 0.2s; color: var(--text-main); font-weight: 600; }
        .custom-option .t-icon { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2; }
        .custom-option:hover { background: var(--gw-light); color: var(--gw-primary); }
        .custom-option.selected { background: rgba(168, 85, 247, 0.1); color: var(--gw-primary); font-weight: 700; border-left: 3px solid var(--gw-primary); }

        /* === GAME BOARD === */
        .game-header { display: flex; align-items: center; justify-content: flex-end; padding: 20px 5%; width: 100%; border-bottom: 2px solid var(--gw-border); background: var(--bg-panel); z-index: 10;}
        .game-stats { display: flex; gap: 15px; }
        .hud-item { display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 18px; color: var(--gw-primary); background: var(--gw-light); padding: 10px 20px; border-radius: 14px; border: 2px solid var(--gw-border); }
        .hud-timer { color: var(--gw-red); border-color: #fecaca; background: #fef2f2; }
        .icon-sm { width: 24px; height: 24px; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; fill: none; }

        .game-area { flex-grow: 1; background: var(--gw-light); padding: 40px 30px; display: flex; justify-content: center; align-items: flex-start; overflow-y: auto; }
        
        .memory-grid { display: grid; gap: 15px; max-width: 900px; width: 100%; margin: auto; perspective: 1000px; padding-bottom: 40px; }
        .grid-4x4 { grid-template-columns: repeat(4, 1fr); }
        .grid-4x6 { grid-template-columns: repeat(6, 1fr); }
        .grid-6x6 { grid-template-columns: repeat(6, 1fr); }

        @media (max-width: 768px) {
            .grid-4x4 { grid-template-columns: repeat(4, 1fr); gap: 8px; }
            .grid-4x6 { grid-template-columns: repeat(4, 1fr); gap: 8px; } 
            .grid-6x6 { grid-template-columns: repeat(4, 1fr); gap: 6px; }
        }

        /* 3D Card Flip */
        .card { position: relative; width: 100%; aspect-ratio: 1; cursor: pointer; transform-style: preserve-3d; transition: transform 0.6s cubic-bezier(0.4, 0.2, 0.2, 1); border-radius: 16px; }
        .card.flipped, .card.matched { transform: rotateY(180deg); }
        
        .card-face { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; border-radius: 16px; display: flex; justify-content: center; align-items: center; padding: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; }
        
        /* Mặt lưng (Sẽ được gắn màu ngẫu nhiên qua Javascript theo Topic) */
        .card-front { border: 2px solid rgba(0,0,0,0.1); }
        .card-front svg { width: 40px; height: 40px; stroke-width: 2; fill: none; stroke: rgba(255,255,255,0.95); }

        /* Mặt nội dung (Luôn Nền Trắng để dễ đọc) */
        .card-back { transform: rotateY(180deg); background-color: #ffffff; border: 3px solid var(--gw-border); word-break: break-word; transition: background 0.3s, color 0.3s, border-color 0.3s; }
        
        /* Chữ cho thẻ Từ Vựng */
        .card-back.type-word { color: var(--gw-primary); font-weight: 900; font-size: 18px; }

        /* Chữ cho thẻ Định Nghĩa */
        .card-back.type-meaning { color: var(--text-main); font-weight: 700; font-size: 15px; }

        /* Khi Match đúng */
        .card.matched .card-back { border-color: var(--gw-green) !important; background-color: #ecfdf5 !important; color: #047857 !important; box-shadow: 0 0 15px rgba(16, 185, 129, 0.3); }
        .card.matched { pointer-events: none; }

        /* Animation khi sai */
        @keyframes shake { 0%, 100% {transform: translateX(0) rotateY(180deg);} 20% {transform: translateX(-5px) rotateY(180deg);} 40% {transform: translateX(5px) rotateY(180deg);} 60% {transform: translateX(-5px) rotateY(180deg);} 80% {transform: translateX(5px) rotateY(180deg);} }
        .shake .card-back { animation: shake 0.5s ease-in-out; border-color: var(--gw-red) !important; color: var(--gw-red) !important; }

        /* Màn hình kết quả */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(30, 41, 59, 0.7); display: none; justify-content: center; align-items: center; z-index: 200; backdrop-filter: blur(5px);}
        .modal-box { background: var(--bg-panel); padding: 40px; border-radius: 24px; width: 450px; text-align: center; border: 2px solid var(--gw-border); box-shadow: 0 20px 40px rgba(168,85,247,0.2);}
        .modal-title { font-size: 32px; font-weight: 900; margin-bottom: 20px; font-family: 'Nunito', sans-serif;}
        .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
        .stat-box { background: var(--gw-light); padding: 15px; border-radius: 16px; border: 2px solid var(--gw-border); }
        .stat-num { font-size: 28px; font-weight: 900; color: var(--gw-primary); }
        
        #confetti-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 150; }
    </style>
</head>
<body>
    <div class="app-layout">
        
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <?php if (!$difficulty): ?>
            
            <a href="minigame.php" class="btn-back-corner">
                <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
                Về kho Game
            </a>

            <div class="setup-screen">
                <div class="setup-header">
                    <svg class="game-icon-svg" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="12" height="12" rx="2"></rect>
                        <rect x="9" y="9" width="12" height="12" rx="2"></rect>
                        <path d="M12 15h3 M15 12v3"></path>
                    </svg>
                    <h1 class="setup-title">MEMORY MATCH</h1>
                    <p class="setup-desc">Thử thách trí nhớ - Ghép thẻ từ vựng & nghĩa</p>
                </div>

                <form action="" method="GET" class="setup-form">
                    <div class="form-group">
                        <label>1. Chọn bộ từ vựng:</label>
                        <div class="custom-select-wrapper" id="topicSelectWrapper">
                            <div class="custom-select-trigger" onclick="toggleDropdown()">
                                <div class="trigger-left">
                                    <svg class="t-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>
                                    <span id="selected-topic-text">Tất cả chủ đề</span>
                                </div>
                                <svg class="arrow-icon" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </div>
                            
                            <div class="custom-options-dropdown">
                                <div class="search-box-holder">
                                    <input type="text" class="dropdown-search-input" id="topicSearch" placeholder="Tìm tên chủ đề nhanh..." oninput="filterTopics()">
                                </div>
                                <div class="options-scroller" id="optionsScroller">
                                    <div class="custom-option selected" data-value="all" onclick="selectTopic('all', 'Tất cả chủ đề')">
                                        <svg class="t-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>
                                        <span>Tất cả chủ đề</span>
                                    </div>
                                    <?php if($topics): foreach($topics as $t): ?>
                                    <div class="custom-option" data-value="<?= $t['id'] ?>" onclick="selectTopic('<?= $t['id'] ?>', '<?= htmlspecialchars($t['name']) ?>')">
                                        <svg class="t-icon" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                                        <span class="topic-name-span"><?= htmlspecialchars($t['name']) ?></span>
                                    </div>
                                    <?php endforeach; endif; ?>
                                    <div class="no-results-msg" id="noResults" style="display:none; padding:10px; text-align:center; color:#ef4444; font-weight: bold;">❌ Không tìm thấy chủ đề nào!</div>
                                </div>
                            </div>
                            <input type="hidden" name="topic" id="hiddenTopicInput" value="all">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>2. Độ khó (Kích thước & Thời gian):</label>
                        <div class="difficulty-grid">
                            <label class="diff-card easy">
                                <input type="radio" name="difficulty" value="easy" required checked>
                                <div class="card-content">
                                    <div class="card-title">Dễ</div>
                                    <div class="card-desc">Lưới 4x4<br>60 Giây</div>
                                </div>
                            </label>
                            <label class="diff-card medium">
                                <input type="radio" name="difficulty" value="medium" required>
                                <div class="card-content">
                                    <div class="card-title">Vừa</div>
                                    <div class="card-desc">Lưới 4x6<br>90 Giây</div>
                                </div>
                            </label>
                            <label class="diff-card hard">
                                <input type="radio" name="difficulty" value="hard" required>
                                <div class="card-content">
                                    <div class="card-title">Khó</div>
                                    <div class="card-desc">Lưới 6x6<br>120 Giây</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn-start">🚀 BẮT ĐẦU TRÒ CHƠI</button>
                </form>
                
                <script>
                    function toggleDropdown() {
                        document.getElementById('topicSelectWrapper').classList.toggle('active');
                        if (document.getElementById('topicSelectWrapper').classList.contains('active')) {
                            document.getElementById('topicSearch').focus();
                        }
                    }
                    function selectTopic(val, text) {
                        document.getElementById('selected-topic-text').innerText = text;
                        document.getElementById('hiddenTopicInput').value = val;
                        document.querySelectorAll('.custom-option').forEach(opt => {
                            opt.classList.remove('selected');
                            if (opt.dataset.value === val) opt.classList.add('selected');
                        });
                        document.getElementById('topicSelectWrapper').classList.remove('active');
                    }
                    function filterTopics() {
                        let filter = document.getElementById('topicSearch').value.toLowerCase();
                        let options = document.querySelectorAll('.custom-option');
                        let hasResult = false;
                        options.forEach(opt => {
                            let text = opt.innerText.toLowerCase();
                            if (text.includes(filter)) {
                                opt.style.display = "flex";
                                hasResult = true;
                            } else {
                                opt.style.display = "none";
                            }
                        });
                        document.getElementById('noResults').style.display = hasResult ? "none" : "block";
                    }
                    document.addEventListener('click', (e) => {
                        if (!e.target.closest('.custom-select-wrapper')) {
                            document.getElementById('topicSelectWrapper').classList.remove('active');
                        }
                    });
                </script>
            </div>

            <?php else: ?>
            
            <a href="game_memory.php" class="btn-back-corner" style="z-index: 1000">
                <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
                Thoát
            </a>

            <header class="game-header">
                <div class="game-stats">
                    <div class="hud-item">
                        <svg class="icon-sm" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Điểm: <span id="score-display">0</span>
                    </div>
                    <div class="hud-item hud-timer">
                        <svg class="icon-sm" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path></svg>
                        <span id="time-display"><?= $timeLimit ?>s</span>
                    </div>
                </div>
            </header>

            <div class="game-area">
                <div class="memory-grid <?= $gridClass ?>" id="board">
                </div>
            </div>

            <canvas id="confetti-canvas"></canvas>
            <div class="modal-overlay" id="result-modal">
                <div class="modal-box">
                    <h1 class="modal-title" id="res-title">XUẤT SẮC!</h1>
                    <div class="stat-grid">
                        <div class="stat-box"><div style="color: var(--text-muted); font-weight: 700; font-size:14px;">Thời gian</div><div class="stat-num" id="res-time">0s</div></div>
                        <div class="stat-box"><div style="color: var(--text-muted); font-weight: 700; font-size:14px;">Số lượt lật</div><div class="stat-num" id="res-flips">0</div></div>
                    </div>
                    <a href="game_memory.php" class="btn-start" style="text-decoration: none; display: block; margin-bottom:10px;">↺ CHƠI LẠI</a>
                    <a href="minigame.php" class="btn-back-corner" style="position: static; width:100%; justify-content:center; padding:18px;">⬅ VỀ KHO GAME</a>
                </div>
            </div>

            <script>
                const wordsData = <?= $cardsJSON ?>;
                if(wordsData.length === 0) {
                    alert("Không tìm thấy từ vựng nào!");
                    window.location.href = 'game_memory.php';
                }

                const board = document.getElementById('board');
                const timeDisplay = document.getElementById('time-display');
                const scoreDisplay = document.getElementById('score-display');
                
                let timeLimit = <?= $timeLimit ?>;
                let cards = [];
                let flippedCards = [];
                let matchedPairs = 0;
                let totalFlips = 0;
                let score = 0;
                let gameInterval;
                let lockBoard = false;

                // Chuẩn bị dữ liệu thẻ - Nhớ kèm theo Topic ID
                wordsData.forEach((item) => {
                    cards.push({ id: item.id, type: 'word', text: item.word, topic: item.typeword_id });
                    cards.push({ id: item.id, type: 'meaning', text: item.definition, topic: item.typeword_id });
                });

                // Bảng màu rực rỡ để gán cho các Topic khác nhau
                const topicColors = [
                    'linear-gradient(135deg, #a855f7 0%, #c084fc 100%)', // 0: Tím WordWise
                    'linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%)', // 1: Xanh Dương
                    'linear-gradient(135deg, #10b981 0%, #34d399 100%)', // 2: Xanh Lá
                    'linear-gradient(135deg, #f97316 0%, #fb923c 100%)', // 3: Cam
                    'linear-gradient(135deg, #ec4899 0%, #f472b6 100%)', // 4: Hồng
                    'linear-gradient(135deg, #14b8a6 0%, #2dd4bf 100%)'  // 5: Xanh Ngọc
                ];

                // Xáo trộn Fisher-Yates
                cards.sort(() => Math.random() - 0.5);

                // Render HTML Thẻ
                cards.forEach((card, index) => {
                    const cardEl = document.createElement('div');
                    cardEl.classList.add('card');
                    cardEl.dataset.id = card.id;
                    cardEl.dataset.type = card.type;
                    cardEl.dataset.text = card.text;

                    // Lấy màu dựa trên Topic ID (Nếu người chơi chọn 1 topic cụ thể, tất cả thẻ sẽ chung 1 màu)
                    let tId = card.topic ? parseInt(card.topic) : 0;
                    let cardBg = topicColors[tId % topicColors.length];

                    cardEl.innerHTML = `
                        <div class="card-face card-front" style="background: ${cardBg};">
                            <svg viewBox="0 0 24 24"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path></svg>
                        </div>
                        <div class="card-face card-back type-${card.type}">
                            ${card.text}
                        </div>
                    `;
                    
                    cardEl.addEventListener('click', flipCard);
                    board.appendChild(cardEl);
                });

                function speakWord(text) {
                    if ('speechSynthesis' in window) {
                        const utterance = new SpeechSynthesisUtterance(text);
                        utterance.lang = 'en-US';
                        utterance.rate = 1.0;
                        window.speechSynthesis.speak(utterance);
                    }
                }

                function flipCard() {
                    if (lockBoard) return;
                    if (this === flippedCards[0]) return;

                    this.classList.add('flipped');
                    
                    if(this.dataset.type === 'word') {
                        speakWord(this.dataset.text);
                    }

                    flippedCards.push(this);

                    if (flippedCards.length === 2) {
                        totalFlips++;
                        checkForMatch();
                    }
                }

                function checkForMatch() {
                    let isMatch = flippedCards[0].dataset.id === flippedCards[1].dataset.id;

                    if (isMatch) {
                        disableCards();
                        score += 10;
                        scoreDisplay.innerText = score;
                        matchedPairs++;
                        if (matchedPairs === wordsData.length) {
                            endGame(true);
                        }
                    } else {
                        unflipCards();
                    }
                }

                function disableCards() {
                    flippedCards[0].removeEventListener('click', flipCard);
                    flippedCards[1].removeEventListener('click', flipCard);
                    
                    flippedCards[0].classList.add('matched');
                    flippedCards[1].classList.add('matched');
                    
                    resetBoard();
                }

                function unflipCards() {
                    lockBoard = true;
                    flippedCards[0].children[1].classList.add('shake');
                    flippedCards[1].children[1].classList.add('shake');

                    setTimeout(() => {
                        flippedCards[0].classList.remove('flipped');
                        flippedCards[1].classList.remove('flipped');
                        
                        flippedCards[0].children[1].classList.remove('shake');
                        flippedCards[1].children[1].classList.remove('shake');
                        
                        resetBoard();
                    }, 800); 
                }

                function resetBoard() {
                    [flippedCards, lockBoard] = [[], false];
                }

                gameInterval = setInterval(() => {
                    timeLimit--;
                    timeDisplay.innerText = timeLimit + 's';
                    if (timeLimit <= 0) {
                        endGame(false);
                    }
                }, 1000);

                function endGame(isWin) {
                    clearInterval(gameInterval);
                    document.getElementById('result-modal').style.display = 'flex';
                    
                    const title = document.getElementById('res-title');
                    title.innerText = isWin ? "XUẤT SẮC!" : "HẾT GIỜ!";
                    title.style.color = isWin ? "var(--gw-primary)" : "var(--gw-red)";
                    
                    document.getElementById('res-time').innerText = (<?= $timeLimit ?> - timeLimit) + 's';
                    document.getElementById('res-flips').innerText = totalFlips;

                    if(isWin) fireConfetti();
                }

                function fireConfetti() {
                    const canvas = document.getElementById('confetti-canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = window.innerWidth;
                    canvas.height = window.innerHeight;
                    
                    let particles = [];
                    const colors = ['#a855f7', '#c084fc', '#10b981', '#fbbf24', '#ef4444'];
                    
                    for(let i = 0; i < 100; i++) {
                        particles.push({
                            x: canvas.width / 2, y: canvas.height / 2 + 100,
                            r: Math.random() * 6 + 4,
                            dx: Math.random() * 10 - 5, dy: Math.random() * -10 - 5,
                            color: colors[Math.floor(Math.random() * colors.length)],
                            tilt: Math.random() * 10 - 10, tiltAngleInc: (Math.random() * 0.07) + 0.05, tiltAngle: 0
                        });
                    }
                    
                    function animate() {
                        requestAnimationFrame(animate);
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        particles.forEach(p => {
                            p.tiltAngle += p.tiltAngleInc;
                            p.y += (Math.cos(p.tiltAngle) + 1 + p.r / 2) / 2;
                            p.x += Math.sin(p.tiltAngle) * 2;
                            p.dy += 0.05;
                            p.x += p.dx; p.y += p.dy;
                            
                            ctx.beginPath();
                            ctx.lineWidth = p.r;
                            ctx.strokeStyle = p.color;
                            ctx.moveTo(p.x + p.tilt + p.r, p.y);
                            ctx.lineTo(p.x + p.tilt, p.y + p.tilt + p.r);
                            ctx.stroke();
                        });
                    }
                    animate();
                }
            </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>