<?php 
session_start(); 
require_once 'database.php'; // Gọi file kết nối CSDL chuẩn của hệ thống

// === HỆ THỐNG XỬ LÝ TIM & THỜI GIAN ===
$max_hearts = 5;
$regen_time = 3 * 3600; 

if (!isset($_SESSION['hearts'])) {
    $_SESSION['hearts'] = $max_hearts;
    $_SESSION['last_heart_loss'] = time();
} else {
    if ($_SESSION['hearts'] < $max_hearts) {
        $time_passed = time() - $_SESSION['last_heart_loss'];
        $hearts_to_add = floor($time_passed / $regen_time);
        if ($hearts_to_add > 0) {
            $_SESSION['hearts'] = min($max_hearts, $_SESSION['hearts'] + $hearts_to_add);
            if ($_SESSION['hearts'] < $max_hearts) {
                $_SESSION['last_heart_loss'] += $hearts_to_add * $regen_time;
            }
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'lose_heart') {
    if ($_SESSION['hearts'] > 0) {
        if ($_SESSION['hearts'] == $max_hearts) {
            $_SESSION['last_heart_loss'] = time();
        }
        $_SESSION['hearts']--;
    }
    $time_left = ($_SESSION['hearts'] < $max_hearts) ? ($regen_time - (time() - $_SESSION['last_heart_loss'])) : 0;
    header('Content-Type: application/json');
    echo json_encode(['hearts' => $_SESSION['hearts'], 'time_left' => $time_left]);
    exit;
}

$time_left_initial = ($_SESSION['hearts'] < $max_hearts) ? ($regen_time - (time() - $_SESSION['last_heart_loss'])) : 0;

// === KHỞI TẠO CLASS DATABASE ===
$db = new Database();
$level = $_GET['level'] ?? null;
$questionsJSON = "[]";

if ($level) {
    // Sử dụng hàm select() có sẵn trong class Database
    $questions = $db->select("SELECT * FROM questions WHERE difficulty = ? ORDER BY RAND() LIMIT 10", [$level]);
    
    foreach ($questions as &$q) {
        if (!empty($q['options'])) $q['options'] = json_decode($q['options'], true);
        if (!empty($q['correct_answer']) && $q['type_id'] == 7) $q['correct_answer'] = json_decode($q['correct_answer'], true);
    }
    $questionsJSON = json_encode($questions);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WordWise Odyssey VIP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Nunito:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css"> 
    <style>
        :root {
            --gw-primary: #a855f7; --gw-primary-shadow: #9333ea;
            --gw-secondary: #3b82f6; --gw-secondary-shadow: #2563eb;
            --gw-red: #ef4444; --gw-green: #10b981; --gw-yellow: #f59e0b;
            --bg-main: #f8fafc; --bg-panel: #ffffff;
            --text-main: #1e293b; --border-color: #e2e8f0; --text-muted: #94a3b8;
        }

        .game-zone * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
        .app-layout { display: flex; width: 100vw; height: 100vh; overflow: hidden; background: var(--bg-main);}
        .main-content { flex-grow: 1; display: flex; flex-direction: column; overflow: hidden; position: relative; }
        .game-wrapper { display: flex; flex-direction: column; height: 100%; width: 100%; background: var(--bg-panel); border-radius: 24px 0 0 24px; box-shadow: -5px 0 25px rgba(0,0,0,0.05); }

        /* HEADER HUD */
        .game-header { display: flex; align-items: center; justify-content: space-between; padding: 25px 5%; width: 100%; border-bottom: 2px solid var(--border-color); background: var(--bg-panel); }
        .progress-bar { flex-grow: 1; height: 16px; background: var(--border-color); border-radius: 20px; margin-right: 30px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, var(--gw-secondary), var(--gw-primary)); width: 0%; transition: 0.5s; border-radius: 20px; }
        .hud-right-group { display: flex; align-items: center; gap: 20px; }
        .hearts-box { display: flex; flex-direction: column; align-items: center; min-width: 90px; background: var(--bg-main); padding: 6px 12px; border-radius: 12px; border: 2px solid var(--border-color); }
        .hearts { display: flex; align-items: center; gap: 8px; font-weight: 900; font-size: 18px; color: var(--gw-red); }
        .timer-text { font-size: 11px; color: var(--text-muted); font-weight: 700; margin-top: 2px; }
        .btn-close { color: var(--text-muted); cursor: pointer; transition: 0.2s; display: flex; align-items: center; }
        .btn-close:hover { color: var(--gw-red); transform: scale(1.1); }

        /* CẤU TRÚC CARD CHỌN LEVEL VIP */
        .level-screen { display: flex; flex-direction: column; align-items: center; justify-content: center; flex-grow: 1; padding: 40px; background: var(--bg-main); overflow-y: auto;}
        .level-title { font-size: 36px; font-weight: 900; margin-bottom: 10px; background: linear-gradient(90deg, var(--gw-secondary), var(--gw-primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-family: 'Nunito', sans-serif; }
        .level-subtitle { font-size: 16px; color: var(--text-muted); font-weight: 600; margin-bottom: 40px; }
        
        .level-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; width: 100%; max-width: 800px; }
        .lvl-card { background: var(--bg-panel); padding: 30px 20px; border-radius: 24px; text-decoration: none; border: 2px solid var(--border-color); box-shadow: 0 8px 0 var(--border-color); transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; align-items: center; text-align: center; color: var(--text-main); position: relative; }
        .lvl-card:hover { transform: translateY(-4px); }
        .lvl-card:active { transform: translateY(6px); box-shadow: none; }
        
        .lvl-card-icon { width: 60px; height: 60px; margin-bottom: 15px; transition: 0.3s; }
        .lvl-card h2 { font-size: 22px; font-weight: 900; margin: 5px 0 10px 0; font-family: 'Nunito', sans-serif;}
        .lvl-card p { font-size: 13px; color: var(--text-muted); font-weight: 600; line-height: 1.5; margin: 0; }

        /* Hiệu ứng màu phát sáng riêng biệt từng thẻ */
        .lvl-easy:hover { border-color: var(--gw-green); box-shadow: 0 8px 0 var(--gw-green); }
        .lvl-easy:hover h2 { color: var(--gw-green); }
        .lvl-medium:hover { border-color: var(--gw-secondary); box-shadow: 0 8px 0 var(--gw-secondary); }
        .lvl-medium:hover h2 { color: var(--gw-secondary); }
        .lvl-hard:hover { border-color: var(--gw-red); box-shadow: 0 8px 0 var(--gw-red); }
        .lvl-hard:hover h2 { color: var(--gw-red); }

        /* IN-GAME WORKSPACE */
        .game-main { flex-grow: 1; width: 100%; padding: 40px 5%; display: flex; flex-direction: column; overflow-y: auto; justify-content: center; align-items: center;}
        .question-title { font-size: 26px; font-weight: 900; margin-bottom: 30px; text-align: center; color: var(--text-main); font-family: 'Nunito', sans-serif; }
        .question-template { display: none; flex-direction: column; align-items: center; width: 100%; max-width: 600px; }
        .question-template.active { display: flex; animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        .q-text { font-size: 24px; font-weight: 800; margin-bottom: 15px; text-align: center; color: var(--text-main); line-height: 1.4; }
        .q-hint { font-size: 15px; color: var(--gw-secondary); margin-bottom: 30px; text-align: center; font-weight: 700; background: rgba(59, 130, 246, 0.06); padding: 12px 24px; border-radius: 14px; border: 2px dashed var(--gw-secondary);}
        
        .input-text { width: 100%; padding: 18px; font-size: 18px; border: 2px solid var(--border-color); border-radius: 16px; outline: none; transition: 0.3s; font-weight: 800; text-align: center; color: var(--text-main); background: var(--bg-main);}
        .input-text:focus { border-color: var(--gw-primary); box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.15); background: var(--bg-panel); }
        
        .choice-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; width: 100%; }
        .choice-btn { padding: 20px; background: var(--bg-panel); border: 2px solid var(--border-color); border-radius: 18px; font-size: 18px; font-weight: 700; color: var(--text-main); cursor: pointer; box-shadow: 0 5px 0 var(--border-color); transition: 0.15s; text-align: center; }
        .choice-btn.selected { border-color: var(--gw-primary); background: rgba(168, 85, 247, 0.05); color: var(--gw-primary); box-shadow: 0 5px 0 var(--gw-primary); }
        .choice-btn:active { transform: translateY(5px); box-shadow: none; }

        .btn-audio { background: var(--gw-secondary); color: white; border: none; width: 65px; height: 65px; border-radius: 50%; cursor: pointer; box-shadow: 0 5px 0 var(--gw-secondary-shadow); margin-bottom: 25px; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
        .btn-audio:active { transform: translateY(5px); box-shadow: none; }
        
        .btn-mic { background: var(--gw-primary); color: white; border: none; padding: 16px 32px; border-radius: 16px; font-size: 17px; font-weight: 900; cursor: pointer; box-shadow: 0 5px 0 var(--gw-primary-shadow); display: flex; align-items: center; gap: 12px; margin-top: 20px; text-transform: uppercase;}
        .btn-mic.recording { background: var(--gw-red); box-shadow: 0 5px 0 #b91c1c; animation: pulse 1s infinite; }
        @keyframes pulse { 50% { opacity: 0.6; } }

        .btn-skip-speech { background: none; border: none; color: var(--text-muted); font-weight: 700; margin-top: 20px; cursor: pointer; text-decoration: underline; }
        .btn-skip-speech:hover { color: var(--text-main); }

        /* FOOTER FEEDBACK */
        .game-footer { border-top: 2px solid var(--border-color); padding: 25px 5%; display: flex; justify-content: space-between; align-items: center; background: var(--bg-panel); transition: 0.3s; }
        .btn-check { background: var(--border-color); border: none; padding: 16px 45px; border-radius: 16px; font-weight: 900; font-size: 18px; color: #a1a1aa; cursor: not-allowed; text-transform: uppercase; transition: 0.2s;}
        .btn-check.active { background: var(--gw-primary); box-shadow: 0 5px 0 var(--gw-primary-shadow); color: white; cursor: pointer; }
        .btn-check.active:active { transform: translateY(5px); box-shadow: none; }
        
        .game-footer.correct { background: rgba(16, 185, 129, 0.05); border-color: var(--gw-green); }
        .game-footer.correct .btn-check { background: var(--gw-green); box-shadow: 0 5px 0 #047857; color: white; cursor: pointer;}
        .game-footer.wrong { background: rgba(239, 68, 68, 0.05); border-color: var(--gw-red); }
        .game-footer.wrong .btn-check { background: var(--gw-red); box-shadow: 0 5px 0 #b91c1c; color: white; cursor: pointer;}
        
        .feedback-area { display: flex; flex-direction: column; }
        .feedback-msg { display: none; font-size: 24px; font-weight: 900; align-items: center; gap: 12px; font-family: 'Nunito', sans-serif;}
        .game-footer.correct .feedback-msg { display: flex; color: var(--gw-green); }
        .game-footer.wrong .feedback-msg { display: flex; color: var(--gw-red); }
        .hint-text { font-size: 16px; color: var(--text-main); margin-top: 6px; display: none; font-weight: 700; }
        .game-footer.wrong .hint-text { display: block; }
        
        .icon-svg { width: 28px; height: 28px; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; fill: none; }
    </style>
</head>
<body>
    <div class="app-layout">
        <?php include_once 'sidebar.php'; ?>

        <div class="main-content game-zone">
            <div class="game-wrapper">
                
                <header class="game-header">
                    <?php if (!$level): ?>
                        <div style="font-weight: 900; font-size: 24px; background: linear-gradient(90deg, var(--gw-secondary), var(--gw-primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-family: 'Nunito', sans-serif;">WordWise Odyssey</div>
                    <?php else: ?>
                        <div class="progress-bar"><div class="progress-fill" id="progress-bar"></div></div>
                    <?php endif; ?>

                    <div class="hud-right-group">
                        <div class="hearts-box">
                            <div class="hearts">
                                <svg class="icon-svg" style="fill: var(--gw-red); stroke: none;" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                <span id="heart-count"><?= $_SESSION['hearts'] ?></span>
                            </div>
                            <div class="timer-text" id="heart-timer" style="<?= $_SESSION['hearts'] >= 5 ? 'display:none;' : '' ?>">Hồi sau: --:--</div>
                        </div>
                        <a href="minigame.php" class="btn-close" title="Thoát trò chơi" onclick="return confirmExit();">
                            <svg class="icon-svg" stroke="currentColor" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        </a>
                    </div>
                </header>

                <?php if (!$level): ?>
                <div class="level-screen">
                    <h1 class="level-title">CHỌN CẤP ĐỘ THỬ THÁCH</h1>
                    <p class="level-subtitle">Hành trình rèn luyện kỹ năng phản xạ từ vựng đỉnh cao</p>
                    <div class="level-cards">
                        <a href="?level=easy" class="lvl-card lvl-easy">
                            <svg class="lvl-card-icon" viewBox="0 0 24 24" fill="none" stroke="var(--gw-green)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <h2>Cấp Độ Dễ</h2>
                            <p>Phù hợp cho người mới bắt đầu. Từ vựng ngắn, cấu trúc câu căn bản, quen thuộc.</p>
                        </a>
                        <a href="?level=medium" class="lvl-card lvl-medium">
                            <svg class="lvl-card-icon" viewBox="0 0 24 24" fill="none" stroke="var(--gw-secondary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <h2>Trung Bình</h2>
                            <p>Tập trung giao tiếp thực tế đời sống. Cụm từ thông dụng, tốc độ tiêu chuẩn.</p>
                        </a>
                        <a href="?level=hard" class="lvl-card lvl-hard">
                            <svg class="lvl-card-icon" viewBox="0 0 24 24" fill="none" stroke="var(--gw-red)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5 12 2"/></svg>
                            <h2>Cấp Độ Khó</h2>
                            <p>Chinh phục đỉnh cao học thuật. Các câu trúc phức tạp, từ vựng nâng cao chuyên sâu.</p>
                        </a>
                    </div>
                </div>

                <?php else: ?>
                <main class="game-main" id="game-area">
                    <div class="question-template" id="tpl-multiple">
                        <h2 class="question-title" id="mc-title">Chọn đáp án đúng</h2>
                        <button class="btn-audio" id="mc-audio" onclick="playQuestionAudio()" style="display:none;">
                            <svg class="icon-svg" stroke="white" viewBox="0 0 24 24"><path d="M11 5L6 9H2v6h4l5 4V5zM15.54 8.46a5 5 0 0 1 0 7.07M19.07 4.93a10 10 0 0 1 0 14.14"></path></svg>
                        </button>
                        <div class="q-text" id="mc-text"></div>
                        <div class="choice-grid" id="mc-choices"></div>
                    </div>

                    <div class="question-template" id="tpl-input">
                        <h2 class="question-title" id="in-title">Dịch câu sau</h2>
                        <div class="q-text" id="in-text"></div>
                        <div class="q-hint" id="in-hint" style="display:none;"></div>
                        <input type="text" class="input-text" id="in-answer" placeholder="Nhập đáp án chính xác tại đây..." autocomplete="off">
                    </div>

                    <div class="question-template" id="tpl-speak">
                        <h2 class="question-title">Luyện phát âm chuẩn</h2>
                        <button class="btn-audio" onclick="playQuestionAudio()">
                            <svg class="icon-svg" stroke="white" viewBox="0 0 24 24"><path d="M11 5L6 9H2v6h4l5 4V5zM15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
                        </button>
                        <div class="q-text" id="sp-text" style="font-size: 28px; color: var(--gw-primary);"></div>
                        <button class="btn-mic" id="btn-record">
                            <svg class="icon-svg" stroke="white" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path><path d="M19 10v2a7 7 0 0 1-14 0v-2M12 19v4M8 23h8"></path></svg>
                            <span>BẤM ĐỂ NÓI</span>
                        </button>
                        <div id="sp-result" style="margin-top: 15px; font-weight: 700; color: var(--gw-secondary);"></div>
                        <button class="btn-skip-speech" onclick="skipSpeaking()">Tôi không tiện nói lúc này</button>
                    </div>

                    <div class="question-template" id="tpl-select-words">
                        <h2 class="question-title" id="sw-title">Chọn các từ có thật</h2>
                        <div class="q-hint">Hãy quét và chọn TẤT CẢ các từ viết đúng chính tả dưới đây.</div>
                        <div class="choice-grid" id="sw-choices" style="grid-template-columns: repeat(3, 1fr);"></div>
                    </div>
                </main>

                <footer class="game-footer" id="game-footer">
                    <div class="feedback-area">
                        <div class="feedback-msg" id="feedback-msg"></div>
                        <div class="hint-text" id="hint-msg"></div>
                    </div>
                    <button class="btn-check" id="btn-check">KIỂM TRA</button>
                </footer>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function confirmExit() { return confirm("Bạn muốn thoát trận? Tiến trình hiện tại sẽ không được lưu."); }

        let currentHearts = <?= $_SESSION['hearts'] ?? 5 ?>;
        let timeLeft = <?= $time_left_initial ?? 0 ?>;
        const timerEl = document.getElementById('heart-timer');
        const heartCountEl = document.getElementById('heart-count');

        function updateTimerDisplay() {
            if (currentHearts >= 5) { if(timerEl) timerEl.style.display = 'none'; return; }
            if(timerEl) timerEl.style.display = 'block';
            if (timeLeft > 0) {
                let h = Math.floor(timeLeft / 3600), m = Math.floor((timeLeft % 3600) / 60), s = timeLeft % 60;
                if(timerEl) timerEl.innerText = `Hồi sau: ${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
                timeLeft--;
            } else {
                fetch('?action=lose_heart').then(r=>r.json()).then(data => {
                    currentHearts = data.hearts; timeLeft = data.time_left; heartCountEl.innerText = currentHearts;
                });
            }
        }
        setInterval(updateTimerDisplay, 1000);

        <?php if ($level): ?>
        const questions = <?= $questionsJSON ?>;
        if(questions.length === 0) {
            alert("Không tìm thấy dữ liệu câu hỏi!");
            window.location.href = "duo_game.php";
        }

        let currentIndex = 0, isChecked = false, currentAnswer = "", currentTargetText = "", multiSelectAnswers = []; 
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        let recognition = SpeechRecognition ? new SpeechRecognition() : null;
        if (recognition) { recognition.lang = 'en-US'; recognition.interimResults = false; }

        const btnCheck = document.getElementById('btn-check');
        const footer = document.getElementById('game-footer');
        const feedbackMsg = document.getElementById('feedback-msg');
        const hintMsg = document.getElementById('hint-msg');

        function playQuestionAudio() {
            const q = questions[currentIndex];
            if (q.audio_url) { new Audio(q.audio_url).play().catch(() => speakTTS(currentTargetText)); } else { speakTTS(currentTargetText); }
        }
        function speakTTS(text) {
            if(!text) return; window.speechSynthesis.cancel();
            let u = new SpeechSynthesisUtterance(text); u.lang = 'en-US'; window.speechSynthesis.speak(u);
        }
        function skipSpeaking() { currentAnswer = currentTargetText; btnCheck.disabled = false; btnCheck.click(); }

        function loadQuestion(index) {
            if(index >= questions.length) { alert("🎉 Xuất sắc! Bạn đã vượt qua tất cả bài học!"); window.location.href = 'minigame.php'; return; }
            if(currentHearts <= 0) { alert("Hết tim rồi! Quay lại sau nhé."); window.location.href = 'minigame.php'; return; }

            document.querySelectorAll('.question-template').forEach(el => el.classList.remove('active'));
            footer.className = 'game-footer'; btnCheck.className = 'btn-check'; btnCheck.innerText = 'KIỂM TRA';
            btnCheck.disabled = true; feedbackMsg.innerHTML = ''; hintMsg.innerText = '';
            isChecked = false; currentAnswer = ""; multiSelectAnswers = []; currentTargetText = "";

            if(document.getElementById('progress-bar')) document.getElementById('progress-bar').style.width = (index / questions.length * 100) + '%';
            
            const q = questions[index]; const type = parseInt(q.type_id);

            if (type === 1 || type === 5) {
                document.getElementById('tpl-multiple').classList.add('active');
                document.getElementById('mc-title').innerText = q.question_text;
                currentTargetText = q.correct_answer || q.question_text;
                document.getElementById('mc-audio').style.display = (type === 1 || q.audio_url) ? 'flex' : 'none';
                if(type === 1 || q.audio_url) setTimeout(playQuestionAudio, 500);
                
                document.getElementById('mc-text').innerText = (type === 5 && !q.audio_url) ? q.question_text : "";
                const choicesDiv = document.getElementById('mc-choices'); choicesDiv.innerHTML = '';
                (q.options || []).forEach(opt => {
                    let btn = document.createElement('button'); btn.className = 'choice-btn'; btn.innerText = opt;
                    btn.onclick = () => {
                        document.querySelectorAll('.choice-btn').forEach(b => b.classList.remove('selected'));
                        btn.classList.add('selected'); currentAnswer = opt; btnCheck.classList.add('active'); btnCheck.disabled = false;
                    };
                    choicesDiv.appendChild(btn);
                });
            } 
            else if (type === 3 || type === 4 || type === 6) {
                document.getElementById('tpl-input').classList.add('active');
                document.getElementById('in-title').innerText = type === 6 ? "Điền từ thích hợp vào chỗ trống" : "Dịch câu sau sang ngôn ngữ đích";
                document.getElementById('in-text').innerText = q.question_text;
                let hintEl = document.getElementById('in-hint');
                if (q.hint && q.hint.trim() !== "") { hintEl.innerText = q.hint; hintEl.style.display = 'block'; } else { hintEl.style.display = 'none'; }
                
                let inputEl = document.getElementById('in-answer'); inputEl.value = ""; setTimeout(() => inputEl.focus(), 100);
                inputEl.oninput = () => {
                    currentAnswer = inputEl.value.trim(); btnCheck.disabled = currentAnswer.length === 0;
                    if(btnCheck.disabled) btnCheck.classList.remove('active'); else btnCheck.classList.add('active');
                };
                inputEl.onkeypress = (e) => { if(e.key === 'Enter' && !btnCheck.disabled) btnCheck.click(); };
            } 
            else if (type === 2) {
                document.getElementById('tpl-speak').classList.add('active');
                currentTargetText = q.correct_answer || q.question_text.replace(/Hãy đọc to câu sau: |"/g, '').trim();
                document.getElementById('sp-text').innerText = currentTargetText;
                document.getElementById('sp-result').innerText = "";
                let btnMic = document.getElementById('btn-record'), spanMic = btnMic.querySelector('span');
                btnMic.className = 'btn-mic'; spanMic.innerText = 'BẤM ĐỂ NÓI';
                
                btnMic.onclick = () => {
                    if(!recognition) return alert("Trình duyệt không hỗ trợ Web Speech API. Vui lòng dùng Chrome/Edge.");
                    if(btnMic.classList.contains('recording')){ recognition.stop(); return; }
                    btnMic.classList.add('recording'); spanMic.innerText = 'ĐANG NGHE...'; recognition.start();
                };
                recognition.onresult = (event) => {
                    let transcript = event.results[0][0].transcript;
                    document.getElementById('sp-result').innerText = "Kết quả ghi âm: " + transcript;
                    currentAnswer = transcript; btnMic.classList.remove('recording'); spanMic.innerText = 'NGHE LẠI';
                    btnCheck.classList.add('active'); btnCheck.disabled = false;
                };
                recognition.onerror = () => { btnMic.classList.remove('recording'); spanMic.innerText = 'LỖI THU ÂM, THỬ LẠI'; };
            }
            else if (type === 7) {
                document.getElementById('tpl-select-words').classList.add('active');
                document.getElementById('sw-title').innerText = q.question_text;
                const choicesDiv = document.getElementById('sw-choices'); choicesDiv.innerHTML = '';
                (q.options || []).forEach(opt => {
                    let btn = document.createElement('button'); btn.className = 'choice-btn'; btn.innerText = opt;
                    btn.onclick = () => {
                        btn.classList.toggle('selected');
                        if(btn.classList.contains('selected')) multiSelectAnswers.push(opt);
                        else multiSelectAnswers = multiSelectAnswers.filter(a => a !== opt);
                        btnCheck.disabled = multiSelectAnswers.length === 0;
                        if(btnCheck.disabled) btnCheck.classList.remove('active'); else btnCheck.classList.add('active');
                    };
                    choicesDiv.appendChild(btn);
                });
            }
        }

        btnCheck.addEventListener('click', () => {
            if(!isChecked) {
                const q = questions[currentIndex]; let isCorrect = false;
                if(q.type_id == 7) {
                    let correctArr = q.correct_answer || [], sortedUser = multiSelectAnswers.slice().sort(), sortedCorrect = correctArr.slice().sort();
                    isCorrect = JSON.stringify(sortedUser) === JSON.stringify(sortedCorrect);
                } else {
                    let userAns = currentAnswer.toLowerCase().replace(/[.,!?"]/g, "").trim();
                    let correctAns = q.correct_answer ? q.correct_answer.toLowerCase().replace(/[.,!?"]/g, "").trim() : "";
                    if (q.type_id == 2) {
                        let target = currentTargetText.toLowerCase().replace(/[.,!?"]/g, "").trim();
                        if(userAns === target || userAns === correctAns) isCorrect = true;
                    } else { if(userAns === correctAns) isCorrect = true; }
                }
                
                let iconCorrect = `<svg class="icon-svg" viewBox="0 0 24 24" stroke="currentColor"><path d="M20 6L9 17l-5-5"></path></svg>`;
                let iconWrong = `<svg class="icon-svg" viewBox="0 0 24 24" stroke="currentColor"><path d="M18 6L6 18M6 6l12 12"></path></svg>`;

                if(isCorrect) {
                    footer.classList.add('correct'); feedbackMsg.innerHTML = iconCorrect + ' CHÍNH XÁC! TUYỆT VỜI';
                } else {
                    footer.classList.add('wrong'); feedbackMsg.innerHTML = iconWrong + ' CHƯA CHÍNH XÁC';
                    let ansText = (q.type_id == 7) ? (q.correct_answer ? q.correct_answer.join(', ') : "") : (q.correct_answer || currentTargetText);
                    hintMsg.innerText = 'Đáp án đúng là: ' + ansText;
                    
                    fetch('?action=lose_heart').then(r => r.json()).then(data => {
                        currentHearts = data.hearts; timeLeft = data.time_left; heartCountEl.innerText = currentHearts;
                        if(currentHearts <= 0) { alert("Bạn đã cạn mạng tim! Hãy đợi hồi phục nhé."); window.location.href = "minigame.php"; }
                    });
                }
                btnCheck.innerText = 'TIẾP TỤC'; isChecked = true;
            } else { currentIndex++; loadQuestion(currentIndex); }
        });

        window.onload = () => loadQuestion(0);
        <?php endif; ?>
    </script>
</body>
</html>