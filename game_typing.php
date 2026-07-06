<?php 
session_start(); 
require_once 'database.php';

$db = new Database(); 

// Lấy danh sách chủ đề từ bảng typeword
$topics = $db->select("SELECT id, name FROM typeword");

$level = $_GET['level'] ?? null;
$count = isset($_GET['count']) ? intval($_GET['count']) : 0;
$topic_id = $_GET['topic'] ?? 'all';

$vocabJSON = "[]";

// Nếu đã setup đầy đủ
if ($level && $count > 0) {
    $whereClause = "1=1";
    $params = [];

    // Lọc theo chủ đề nếu không chọn "Tất cả"
    if ($topic_id !== 'all') {
        $whereClause .= " AND typeword_id = ?";
        $params[] = (int)$topic_id;
    }

    // Lọc theo độ khó
    if ($level !== 'mixed') {
        $map = [
            'easy' => ['Dễ', 'easy'],
            'medium' => ['Trung bình', 'medium'],
            'hard' => ['Khó', 'hard']
        ];
        $diffs = $map[$level] ?? ['Trung bình', 'medium'];
        $whereClause .= " AND (difficulty = ? OR difficulty = ?)";
        $params[] = $diffs[0];
        $params[] = $diffs[1];
    }

    // Thêm count vào params cho LIMIT
    $params[] = (int)$count;

    // Truy xuất Database
    $words = $db->select("SELECT word, definition as hint, difficulty FROM vocabulary WHERE $whereClause ORDER BY RAND() LIMIT ?", $params);
    
    if ($words) {
        foreach($words as &$w) {
            $w['word'] = preg_replace('/[^a-zA-Z\s]/', '', strtoupper(trim($w['word'])));
            $d = mb_strtolower($w['difficulty']);
            if (in_array($d, ['dễ', 'easy'])) $w['type'] = 'easy';
            elseif (in_array($d, ['khó', 'hard'])) $w['type'] = 'hard';
            else $w['type'] = 'medium';
        }
        $vocabJSON = json_encode($words);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Word Defender VIP - Trắng Tím</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Nunito:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css"> 
    <style>
        :root {
            --gw-primary: #a855f7; 
            --gw-primary-shadow: #9333ea;
            --gw-secondary: #c084fc; 
            --gw-red: #ef4444; 
            --gw-green: #10b981; 
            --bg-main: #f8fafc; 
            --bg-panel: #ffffff;
            --text-main: #1e293b; 
            --border-color: #e2e8f0; 
            --text-muted: #64748b;
        }

        .game-zone * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
        .app-layout { display: flex; width: 100vw; height: 100vh; overflow: hidden; background: var(--bg-main); }
        .main-content { flex-grow: 1; display: flex; flex-direction: column; position: relative; }
        .game-wrapper { display: flex; flex-direction: column; height: 100%; width: 100%; background: var(--bg-panel); border-radius: 24px 0 0 24px; box-shadow: -5px 0 25px rgba(0,0,0,0.05); overflow: hidden; position: relative;}

        /* NÚT BACK GÓC TRÁI */
        .btn-back-corner { position: absolute; top: 25px; left: 25px; display: flex; align-items: center; gap: 8px; padding: 12px 20px; border-radius: 14px; background: white; color: var(--text-main); font-weight: 800; text-decoration: none; border: 2px solid var(--border-color); box-shadow: 0 4px 0 var(--border-color); transition: 0.15s; z-index: 100;}
        .btn-back-corner:hover { border-color: var(--gw-primary); color: var(--gw-primary); transform: translateY(-2px); box-shadow: 0 6px 0 var(--gw-primary-shadow); }
        .btn-back-corner:active { transform: translateY(4px); box-shadow: none; }
        .btn-back-corner svg { width: 20px; height: 20px; stroke: currentColor; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; fill: none; }

        /* === MÀN HÌNH SETUP TRẮNG TÍM === */
        .setup-screen { display: flex; flex-direction: column; align-items: center; justify-content: flex-start; height: 100%; padding: 40px; padding-top: 80px; background: #faf5ff; overflow-y: auto;}
        .setup-title { font-size: 34px; font-weight: 900; color: var(--gw-primary); margin-bottom: 30px; font-family: 'Nunito', sans-serif;}
        .setup-form { background: var(--bg-panel); padding: 40px; border-radius: 24px; border: 2px solid #e9d5ff; width: 100%; max-width: 750px; box-shadow: 0 20px 40px rgba(168,85,247,0.08); }
        .form-group { margin-bottom: 25px; position: relative; }
        .form-group > label { display: block; font-weight: 800; margin-bottom: 12px; font-size: 17px; color: var(--text-main);}

        /* THANH CHỌN CHỦ ĐỀ CUSTOM PRO VIP (Hỗ trợ 1000 chủ đề + Tìm kiếm) */
        .custom-select-wrapper { position: relative; width: 100%; user-select: none; }
        .custom-select-trigger { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-radius: 16px; border: 2px solid var(--border-color); background: var(--bg-main); font-weight: 800; color: var(--text-main); cursor: pointer; transition: 0.2s; }
        .custom-select-trigger:hover, .custom-select-wrapper.open .custom-select-trigger { border-color: var(--gw-primary); background: #ffffff; box-shadow: 0 4px 12px rgba(168, 85, 247, 0.1); }
        .trigger-left { display: flex; align-items: center; gap: 12px; }
        .t-icon { width: 22px; height: 22px; stroke: var(--gw-primary); stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; fill: none; }
        .arrow-icon { width: 20px; height: 20px; stroke: var(--text-muted); stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; fill: none; transition: transform 0.2s ease; }
        .custom-select-wrapper.open .arrow-icon { transform: rotate(180deg); stroke: var(--gw-primary); }

        .custom-options-dropdown { position: absolute; top: calc(100% + 8px); left: 0; right: 0; background: #ffffff; border: 2px solid var(--gw-primary); border-radius: 16px; box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12); z-index: 120; display: none; flex-direction: column; overflow: hidden; animation: menuFade 0.2s ease; }
        @keyframes menuFade { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .custom-select-wrapper.open .custom-options-dropdown { display: flex; }
        
        .search-box-holder { padding: 12px; border-bottom: 1px solid var(--border-color); background: #faf5ff; }
        .dropdown-search-input { width: 100%; padding: 12px 16px; border-radius: 10px; border: 2px solid var(--border-color); font-size: 15px; font-weight: 700; outline: none; transition: 0.15s; color: var(--text-main); }
        .dropdown-search-input:focus { border-color: var(--gw-primary); background: #ffffff; box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.15); }

        .options-scroller { max-height: 240px; overflow-y: auto; padding: 6px; scrollbar-width: thin; scrollbar-color: #e9d5ff transparent; }
        .options-scroller::-webkit-scrollbar { width: 6px; }
        .options-scroller::-webkit-scrollbar-thumb { background: #e9d5ff; border-radius: 10px; }
        
        .custom-option { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 10px; font-weight: 700; color: var(--text-muted); cursor: pointer; transition: 0.15s; }
        .custom-option:hover { background: rgba(168, 85, 247, 0.06); color: var(--gw-primary); }
        .custom-option.selected { background: var(--gw-primary); color: #ffffff; }
        .custom-option.selected .t-icon { stroke: #ffffff; }
        .no-results-msg { padding: 20px; text-align: center; font-weight: 700; color: var(--text-muted); font-size: 14px; display: none; }

        /* CẤP ĐỘ QUÁI THÚ */
        .difficulty-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; }
        .diff-card { cursor: pointer; position: relative; display: block; }
        .diff-card input { display: none; }
        .card-content { border: 2px solid var(--border-color); border-radius: 18px; padding: 20px 15px; text-align: center; transition: 0.25s ease; background: var(--bg-panel); height: 100%;}
        .card-icon { width: 45px; height: 45px; margin: 0 auto 10px; }
        .card-title { font-size: 18px; font-weight: 900; margin-bottom: 6px; }
        .card-desc { font-size: 12px; color: var(--text-muted); font-weight: 600; line-height: 1.4; }

        .diff-card.easy input:checked + .card-content { border-color: var(--gw-green); background: rgba(16, 185, 129, 0.05); box-shadow: 0 6px 0 var(--gw-green); transform: translateY(-4px); }
        .diff-card.medium input:checked + .card-content { border-color: var(--gw-primary); background: rgba(168, 85, 247, 0.05); box-shadow: 0 6px 0 var(--gw-primary); transform: translateY(-4px); }
        .diff-card.hard input:checked + .card-content { border-color: var(--gw-red); background: rgba(239, 68, 68, 0.05); box-shadow: 0 6px 0 var(--gw-red); transform: translateY(-4px); }
        .diff-card.mixed input:checked + .card-content { border-color: var(--gw-secondary); background: rgba(192, 132, 252, 0.05); box-shadow: 0 6px 0 var(--gw-secondary); transform: translateY(-4px); }

        .setup-form input[type="number"] { width: 100%; padding: 18px; border-radius: 16px; border: 2px solid var(--border-color); font-size: 20px; font-weight: 900; background: var(--bg-main); color: var(--gw-primary); outline: none; transition: 0.2s; text-align: center;}
        .setup-form input[type="number"]:focus { border-color: var(--gw-primary); box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.15); }
        
        .btn-start { width: 100%; padding: 20px; font-size: 20px; font-weight: 900; background: var(--gw-primary); color: white; border: none; border-radius: 16px; cursor: pointer; box-shadow: 0 6px 0 var(--gw-primary-shadow); transition: 0.15s; text-transform: uppercase; margin-top: 15px;}
        .btn-start:active { transform: translateY(6px); box-shadow: none; }
        
        .error-shake { animation: errShake 0.4s ease-in-out; background: var(--gw-red) !important; box-shadow: 0 6px 0 #b91c1c !important;}
        @keyframes errShake { 0%, 100% {transform: translateX(0);} 25% {transform: translateX(-8px);} 75% {transform: translateX(8px);} }

        /* === IN-GAME HUD === */
        .game-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 5%; width: 100%; border-bottom: 2px solid var(--border-color); background: var(--bg-panel); z-index: 100;}
        .hud-group { display: flex; align-items: center; gap: 15px; }
        .hud-item { display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 17px; color: var(--gw-primary); background: #faf5ff; padding: 8px 16px; border-radius: 12px; border: 2px solid #e9d5ff; }
        .hud-hearts { color: var(--gw-red); border-color: #fecaca; background: #fef2f2;} 
        
        #game-area { flex-grow: 1; position: relative; background-color: #faf5ff; overflow: hidden; background-image: radial-gradient(#e9d5ff 2px, transparent 2px); background-size: 40px 40px; }
        
        .data-core { position: absolute; display: flex; flex-direction: column; align-items: center; gap: 5px; z-index: 50; }
        .core-hint { font-size: 13px; color: #6b7280; font-weight: 700; background: rgba(255,255,255,0.9); padding: 4px 12px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 2px 5px rgba(0,0,0,0.05);}
        .core-text { display: flex; align-items: center; gap: 10px; font-size: 22px; font-weight: 900; background: #ffffff; padding: 10px 20px; border-radius: 16px; border: 2px solid; box-shadow: 0 5px 0; color: var(--text-main); font-family: 'Nunito', sans-serif;}
        
        .core-easy .core-text { border-color: var(--gw-green); box-shadow: 0 5px 0 var(--gw-green); }
        .core-medium .core-text { border-color: var(--gw-primary); box-shadow: 0 5px 0 var(--gw-primary); }
        .core-hard .core-text { border-color: var(--gw-red); box-shadow: 0 5px 0 var(--gw-red); }

        .typed { color: #cbd5e1; text-decoration: line-through; }
        .untyped { color: var(--text-main); }
        
        .animal-icon { width: 30px; height: 30px; fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }
        .core-easy .animal-icon { stroke: var(--gw-green); }
        .core-medium .animal-icon { stroke: var(--gw-primary); }
        .core-hard .animal-icon { stroke: var(--gw-red); }

        #defender { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); width: 90px; height: 90px; z-index: 60; transition: transform 0.05s ease; filter: drop-shadow(0 10px 10px rgba(168, 85, 247, 0.2));}
        #laser-canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 40; }
        .laser-beam { stroke: var(--gw-primary); stroke-width: 6; filter: drop-shadow(0 0 12px #c084fc); stroke-linecap: round; opacity: 0.9; stroke-dasharray: 20 15; animation: zap 0.1s linear infinite;}
        @keyframes zap { to { stroke-dashoffset: -35; } }

        .particle { position: absolute; width: 8px; height: 8px; border-radius: 50%; pointer-events: none; z-index: 45; animation: explode 0.5s cubic-bezier(0.1, 0.8, 0.3, 1) forwards; }
        @keyframes explode { 0% { transform: translate(0,0) scale(1); opacity: 1; } 100% { transform: translate(var(--tx), var(--ty)) scale(0); opacity: 0; } }

        /* MODAL KẾT QUẢ */
        .modal-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(30, 41, 59, 0.6); display: flex; justify-content: center; align-items: center; z-index: 200; display: none; backdrop-filter: blur(5px);}
        .modal-box { background: var(--bg-panel); padding: 40px; border-radius: 24px; width: 450px; text-align: center; border: 2px solid var(--border-color); box-shadow: 0 20px 40px rgba(168,85,247,0.15);}
        .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 25px 0; }
        .stat-box { background: #faf5ff; padding: 18px; border-radius: 16px; border: 2px solid #e9d5ff; }
        .stat-num { font-size: 30px; font-weight: 900; color: var(--gw-primary); }
        .stars { font-size: 40px; color: #e2e8f0; margin-bottom: 15px; letter-spacing: 8px;}
        .stars .earned { color: #fbbf24; text-shadow: 0 0 15px rgba(251, 191, 36, 0.6); }
        
        .action-buttons { display: flex; flex-direction: column; gap: 12px; }
        .btn-restart { width: 100%; padding: 18px; font-size: 18px; font-weight: 900; background: var(--gw-primary); color: white; border: none; border-radius: 16px; cursor: pointer; box-shadow: 0 6px 0 var(--gw-primary-shadow); transition: 0.15s; text-transform: uppercase; text-decoration: none; display: block;}
        .btn-restart:active { transform: translateY(6px); box-shadow: none; }
        .btn-home { width: 100%; padding: 18px; font-size: 18px; font-weight: 900; background: #f1f5f9; color: var(--text-main); border: 2px solid #cbd5e1; border-radius: 16px; cursor: pointer; box-shadow: 0 6px 0 #cbd5e1; transition: 0.15s; text-transform: uppercase; text-decoration: none; display: block;}
        .btn-home:active { transform: translateY(6px); box-shadow: none; }

        .icon-svg { width: 26px; height: 26px; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; fill: none; }
    </style>
</head>
<body>
    <div class="app-layout">
        <?php include_once 'sidebar.php'; ?>

        <div class="main-content game-zone">
            <div class="game-wrapper">
                
                <?php if (!$level || $count <= 0): ?>
                
                <a href="minigame.php" class="btn-back-corner">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Về trò chơi
                </a>

                <div class="setup-screen">
                    <h1 class="setup-title">CHIẾN DỊCH PHÒNG THỦ</h1>
                    <form action="" method="GET" class="setup-form" id="setupForm" onsubmit="return validateForm()">
                        
                        <div class="form-group">
                            <label>1. Chọn chủ đề tác chiến:</label>
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
                                        <div class="no-results-msg" id="noResults">❌ Không tìm thấy chủ đề nào!</div>
                                    </div>
                                </div>
                                <input type="hidden" name="topic" id="hiddenTopicInput" value="all">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>2. Chọn cấp độ quái thú xâm lăng:</label>
                            <div class="difficulty-grid">
                                <label class="diff-card easy">
                                    <input type="radio" name="level" value="easy" required>
                                    <div class="card-content">
                                        <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="var(--gw-green)" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                        <div class="card-title" style="color: var(--gw-green)">Dễ</div>
                                        <div class="card-desc">Quái rơi chậm, từ ngắn.</div>
                                    </div>
                                </label>
                                <label class="diff-card medium">
                                    <input type="radio" name="level" value="medium" required checked>
                                    <div class="card-content">
                                        <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="var(--gw-primary)" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                                        <div class="card-title" style="color: var(--gw-primary)">Vừa</div>
                                        <div class="card-desc">Tốc độ tiêu chuẩn.</div>
                                    </div>
                                </label>
                                <label class="diff-card hard">
                                    <input type="radio" name="level" value="hard" required>
                                    <div class="card-content">
                                        <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="var(--gw-red)" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <div class="card-title" style="color: var(--gw-red)">Khó</div>
                                        <div class="card-desc">Rơi chớp nhoáng.</div>
                                    </div>
                                </label>
                                <label class="diff-card mixed">
                                    <input type="radio" name="level" value="mixed" required>
                                    <div class="card-content">
                                        <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="var(--gw-secondary)" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                        <div class="card-title" style="color: var(--gw-secondary)">Hỗn Hợp</div>
                                        <div class="card-desc">Trộn lẫn thử thách.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>3. Tổng số mục tiêu từ vựng:</label>
                            <input type="number" id="c-inp" name="count" min="1" max="1000" value="1" required>
                        </div>

                        <button type="submit" class="btn-start" id="btnStart">🚀 CẤT CÁNH XUẤT KÍCH</button>
                    </form>
                </div>
                
                <script>
                    // === JAVASCRIPT ĐIỀU KHIỂN DROPDOWN TÌM KIẾM CHỦ ĐỀ ===
                    const wrapper = document.getElementById('topicSelectWrapper');
                    const searchInput = document.getElementById('topicSearch');
                    const options = document.querySelectorAll('.custom-option');
                    const noResults = document.getElementById('noResults');

                    function toggleDropdown() {
                        wrapper.classList.toggle('open');
                        if (wrapper.classList.contains('open')) {
                            searchInput.focus();
                        }
                    }

                    function selectTopic(val, text) {
                        document.getElementById('hiddenTopicInput').value = val;
                        document.getElementById('selected-topic-text').innerText = text;
                        
                        options.forEach(opt => opt.classList.remove('selected'));
                        event.currentTarget.classList.add('selected');
                        
                        wrapper.classList.remove('open');
                    }

                    function filterTopics() {
                        const filter = searchInput.value.toLowerCase().trim();
                        let hasVisible = false;

                        options.forEach(opt => {
                            const txt = opt.textContent.toLowerCase();
                            if (txt.includes(filter)) {
                                opt.style.display = 'flex';
                                hasVisible = true;
                            } else {
                                opt.style.display = 'none';
                            }
                        });

                        noResults.style.display = hasVisible ? 'none' : 'block';
                    }

                    // Đóng dropdown khi bấm ra vùng ngoài
                    window.addEventListener('click', function(e) {
                        if (!wrapper.contains(e.target)) {
                            wrapper.classList.remove('open');
                        }
                    });

                    // Validate Form Số lượng học
                    function validateForm() {
                        const countVal = parseInt(document.getElementById('c-inp').value);
                        if(isNaN(countVal) || countVal < 1 || countVal > 1000) {
                            const btn = document.getElementById('btnStart');
                            btn.innerText = "❌ SỐ LƯỢNG TỪ 1 ĐẾN 1000";
                            btn.classList.add('error-shake');
                            setTimeout(() => { 
                                btn.classList.remove('error-shake'); btn.innerText = "🚀 CẤT CÁNH XUẤT KÍCH"; 
                            }, 600);
                            return false;
                        }
                        return true;
                    }
                </script>

                <?php else: ?>

                <header class="game-header">
                    <div class="hud-group">
                        <div class="hud-item">
                            <svg class="icon-svg" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            <span id="words-cleared">0</span> / <?= $count ?> Từ
                        </div>
                    </div>
                    <div class="hud-group">
                        <div class="hud-item hud-hearts">
                            <svg class="icon-svg" style="fill: currentColor; stroke: none;" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            <span id="health-display">5</span>
                        </div>
                    </div>
                </header>

                <div id="game-area">
                    <svg id="laser-canvas"></svg>
                    <div id="defender">
                        <svg viewBox="0 0 100 100" fill="none">
                            <path d="M50 10 L85 80 L70 75 L50 90 L30 75 L15 80 Z" fill="#ffffff" stroke="#a855f7" stroke-width="4" stroke-linejoin="round"/>
                            <path d="M50 25 L65 70 L50 65 L35 70 Z" fill="#f3e8ff" stroke="#c084fc" stroke-width="3"/>
                            <circle cx="50" cy="50" r="8" fill="#a855f7" filter="drop-shadow(0 0 6px #c084fc)"/>
                            <path d="M30 75 L20 90 M70 75 L80 90" stroke="#c084fc" stroke-width="4" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>

                <div class="modal-overlay" id="result-modal">
                    <div class="modal-box">
                        <h1 id="res-title" style="font-size: 32px; font-weight: 900; margin-bottom: 10px; font-family: 'Nunito', sans-serif;">KẾT QUẢ TRẬN ĐẤU</h1>
                        <div class="stars" id="res-stars">★ ★ ★</div>
                        <div class="stat-grid">
                            <div class="stat-box"><div style="color: #64748b; font-weight: 700;">Tốc độ gõ WPM</div><div class="stat-num" id="res-wpm">0</div></div>
                            <div class="stat-box"><div style="color: #64748b; font-weight: 700;">Độ chính xác</div><div class="stat-num" id="res-acc">0%</div></div>
                        </div>
                        <div class="action-buttons">
                            <a href="game_typing.php" class="btn-restart">↺ TÁC CHIẾN LẠI</a>
                            <a href="minigame.php" class="btn-home">⬅ VỀ MÀN HÌNH TRÒ CHƠI</a>
                        </div>
                    </div>
                </div>

                <script>
                    const dbVocab = <?= $vocabJSON ?>; 
                    const targetCount = <?= $count ?>; 
                    
                    if(dbVocab.length === 0) {
                        alert("Lỗi: Không tìm thấy từ vựng nào trong chủ đề này!");
                        window.location.href = 'game_typing.php';
                    }

                    // Xử lý tạo hàng đợi từ vựng (Nhân bản nếu số lượng yêu cầu > số từ trong db)
                    let vocabQueue = [];
                    while(vocabQueue.length < targetCount) {
                        vocabQueue = vocabQueue.concat(dbVocab).sort(() => Math.random() - 0.5);
                    }
                    vocabQueue = vocabQueue.slice(0, targetCount);

                    const icons = {
                        easy: `<svg class="animal-icon" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`,
                        medium: `<svg class="animal-icon" viewBox="0 0 24 24"><polygon points="12 2 2 7 12 12 22 7 12 2"/></svg>`,
                        hard: `<svg class="animal-icon" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87z"/></svg>`
                    };

                    let activeCores = [], currentTarget = null, wordsClearedCount = 0, hearts = 5;
                    let totalKeystrokes = 0, correctKeystrokes = 0, startTime = null, isGameOver = false;
                    
                    const gameArea = document.getElementById('game-area');
                    const defender = document.getElementById('defender');
                    const laserCanvas = document.getElementById('laser-canvas');

                    function drawLaser(targetEl) {
                        const gRect = gameArea.getBoundingClientRect();
                        const tRect = targetEl.getBoundingClientRect();
                        const dRect = defender.getBoundingClientRect();
                        
                        const sX = (dRect.left - gRect.left) + dRect.width / 2;
                        const sY = (dRect.top - gRect.top) + 20; 
                        const eX = (tRect.left - gRect.left) + tRect.width / 2;
                        const eY = (tRect.bottom - gRect.top);
                        
                        laserCanvas.innerHTML = `<line class="laser-beam" x1="${sX}" y1="${sY}" x2="${eX}" y2="${eY}" />`;
                        defender.style.transform = `translateX(-50%) scale(0.9) translateY(8px)`;
                        
                        setTimeout(() => { 
                            laserCanvas.innerHTML = ''; 
                            defender.style.transform = `translateX(-50%) scale(1) translateY(0)`; 
                        }, 100);
                    }

                    function createExplosion(x, y, color) {
                        for(let i = 0; i < 15; i++) {
                            let p = document.createElement('div'); 
                            p.className = 'particle'; 
                            p.style.background = color;
                            p.style.left = x + 'px'; 
                            p.style.top = y + 'px';
                            let a = Math.random() * Math.PI * 2, s = Math.random() * 80 + 40;
                            p.style.setProperty('--tx', Math.cos(a) * s + 'px'); 
                            p.style.setProperty('--ty', Math.sin(a) * s + 'px');
                            gameArea.appendChild(p); 
                            setTimeout(() => p.remove(), 400);
                        }
                    }

                    function spawnWord() {
                        if(isGameOver || vocabQueue.length === 0) return;
                        let item = vocabQueue.shift(); 
                        const el = document.createElement('div');
                        el.className = `data-core core-${item.type}`;
                        
                        let safeX = Math.max(40, Math.random() * (gameArea.clientWidth - 260));
                        el.style.left = safeX + 'px'; 
                        el.style.top = '-80px';
                        
                        el.innerHTML = `<div class="core-hint">${item.hint}</div>
                                        <div class="core-text">
                                            ${icons[item.type]}
                                            <div><span class="typed"></span><span class="untyped">${item.word}</span></div>
                                        </div>`;
                        gameArea.appendChild(el);
                        
                        let speed = (item.type === 'hard' ? 1.2 : (item.type === 'easy' ? 0.6 : 0.9));
                        activeCores.push({ el: el, word: item.word, type: item.type, typedIdx: 0, x: safeX, y: -80, speed: speed });
                    }

                    function gameLoop() {
                        if(!isGameOver) {
                            const bLimit = gameArea.clientHeight - 90;
                            for (let i = 0; i < activeCores.length; i++) {
                                let c = activeCores[i]; 
                                c.y += c.speed; 
                                c.el.style.top = c.y + 'px';
                                
                                if (c.y > bLimit) {
                                    c.el.remove(); 
                                    activeCores.splice(i, 1); 
                                    i--; 
                                    if (c === currentTarget) currentTarget = null;
                                    
                                    hearts--; 
                                    document.getElementById('health-display').innerText = hearts;
                                    if(hearts <= 0) showResult(false);
                                }
                            }
                        }
                        requestAnimationFrame(gameLoop);
                    }

                    window.addEventListener('keydown', (e) => {
                        if(!startTime) startTime = Date.now(); 
                        if(isGameOver) return;
                        
                        const key = e.key.toUpperCase(); 
                        if(!/^[A-Z\s]$/.test(key)) return;
                        totalKeystrokes++;

                        if (!currentTarget && activeCores.length > 0) {
                            currentTarget = activeCores.find(c => c.word[0] === key && c.y > 0);
                        }

                        if (currentTarget) {
                            if (currentTarget.word[currentTarget.typedIdx] === key) {
                                currentTarget.typedIdx++; 
                                correctKeystrokes++;
                                
                                drawLaser(currentTarget.el.querySelector('.core-text'));
                                currentTarget.el.querySelector('.typed').innerText = currentTarget.word.substring(0, currentTarget.typedIdx);
                                currentTarget.el.querySelector('.untyped').innerText = currentTarget.word.substring(currentTarget.typedIdx);

                                if (currentTarget.typedIdx === currentTarget.word.length) {
                                    let col = currentTarget.type === 'hard' ? '#ef4444' : (currentTarget.type === 'easy' ? '#10b981' : '#a855f7');
                                    createExplosion(currentTarget.x + 80, currentTarget.y + 30, col);
                                    
                                    wordsClearedCount++; 
                                    document.getElementById('words-cleared').innerText = wordsClearedCount;
                                    
                                    let toRemove = currentTarget; 
                                    currentTarget = null;
                                    
                                    setTimeout(() => { 
                                        if(toRemove.el) toRemove.el.remove(); 
                                        activeCores = activeCores.filter(c => c !== toRemove); 
                                    }, 40);
                                    
                                    if(wordsClearedCount >= targetCount) showResult(true);
                                }
                            }
                        }
                    });

                    function showResult(win) {
                        isGameOver = true; 
                        document.getElementById('result-modal').style.display = 'flex';
                        
                        let t = document.getElementById('res-title'); 
                        t.innerText = win ? "HOÀN THÀNH NHIỆM VỤ!" : "TÀU BỊ PHÁ HỦY!";
                        t.style.color = win ? "var(--gw-primary)" : "var(--gw-red)";
                        
                        let mins = Math.max(0.1, (Date.now() - startTime) / 60000);
                        let wpm = Math.round(wordsClearedCount / mins);
                        let acc = Math.round((correctKeystrokes / Math.max(1, totalKeystrokes)) * 100);
                        
                        document.getElementById('res-wpm').innerText = wpm; 
                        document.getElementById('res-acc').innerText = acc + "%";
                        document.getElementById('res-acc').style.color = acc >= 80 ? "var(--gw-primary)" : "var(--gw-red)";
                        
                        let starsHTML = `<span class="earned">★</span> <span class="earned">★</span> <span class="earned">★</span>`;
                        if (!win || acc < 50) starsHTML = `<span class="earned">★</span> ★ ★`;
                        else if (acc < 80) starsHTML = `<span class="earned">★</span> <span class="earned">★</span> ★`;
                        
                        document.getElementById('res-stars').innerHTML = starsHTML;
                    }

                    window.onload = () => {
                        setInterval(spawnWord, 1600); 
                        requestAnimationFrame(gameLoop);
                    };
                </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>