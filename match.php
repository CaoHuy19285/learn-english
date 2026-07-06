<?php
session_start();
require_once 'database.php';

// ===== XỬ LÝ AJAX CẬP NHẬT TIẾN ĐỘ =====
if (isset($_POST['action']) && $_POST['action'] === 'update_progress') {
    header('Content-Type: application/json');
    $user_id = (int)$_SESSION['user_id'] ?? 0;
    if (!$user_id) { echo json_encode(['success'=>false,'error'=>'User not logged in']); exit(); }
    $vocab_id = intval($_POST['vocab_id'] ?? 0);
    $result = $_POST['result'] ?? 'right';
    if (!$vocab_id) { echo json_encode(['success'=>false,'error'=>'Invalid vocab id']); exit(); }

    $db = new Database();
    $check = $db->select("SELECT id, status, correct_count, wrong_count FROM user_progress 
                          WHERE user_id = ? AND vocabulary_id = ?", [$user_id, $vocab_id]);
    if (!empty($check)) {
        $current = $check[0];
        $new_status = $current['status'];
        if ($result === 'right') {
            $new_correct = $current['correct_count'] + 1;
            if ($new_correct >= 3 && $new_status !== 'mastered') {
                $new_status = 'mastered';
            } elseif ($new_status !== 'mastered') {
                $new_status = 'learned';
            }
            $db->execute("UPDATE user_progress SET correct_count = ?, status = ?, last_review = NOW() WHERE id = ?", 
                         [$new_correct, $new_status, $current['id']]);
        } else {
            $new_wrong = $current['wrong_count'] + 1;
            if ($new_wrong >= 2 && $new_status !== 'mastered') {
                $new_status = 'reviewing';
            } else {
                $new_status = $current['status'];
            }
            $db->execute("UPDATE user_progress SET wrong_count = ?, status = ?, last_review = NOW() WHERE id = ?", 
                         [$new_wrong, $new_status, $current['id']]);
        }
    } else {
        $status = ($result === 'right') ? 'learned' : 'reviewing';
        $correct = ($result === 'right') ? 1 : 0;
        $wrong = ($result === 'wrong') ? 1 : 0;
        $db->execute("INSERT INTO user_progress (user_id, vocabulary_id, status, correct_count, wrong_count, last_review, created_at) 
                      VALUES (?, ?, ?, ?, ?, NOW(), NOW())", 
                      [$user_id, $vocab_id, $status, $correct, $wrong]);
    }

    // Ghi vào user_activity
    $db->execute("INSERT INTO user_activity (user_id, activity_type, vocabulary_id, created_at) 
                  VALUES (?, 'match', ?, NOW())", [$user_id, $vocab_id]);

    //  Cập nhật streak
    $db->updateStreak($user_id);

    // Cộng XP nếu đúng
    if ($result === 'right') {
        $db->execute("UPDATE users SET xp = xp + 5 WHERE id = ?", [$user_id]);
    }

    echo json_encode(['success' => true]);
    exit();
}

// ... phần còn lại của match.php hoặc quiz.php

// ===== PHẦN HIỂN THỊ MATCH =====
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 1;
$db = new Database();
$cat_data = $db->select("SELECT name FROM typeword WHERE id = ?", [$category_id]);
$cat_name = !empty($cat_data) ? $cat_data[0]['name'] : 'Unknown';
$words = $db->select("SELECT id, word, definition FROM vocabulary WHERE typeword_id = ?", [$category_id]);
$json_words = json_encode($words, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Word Match - WordWise</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .game-wrapper { display: flex; justify-content: center; align-items: flex-start; min-height: 80vh; padding-top: 20px; }
        .app-container { width: 100%; max-width: 900px; }
        .header { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; }
        .back-btn { width: 40px; height: 40px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; color: #1e1b4b; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .title-area h2 { font-size: 13px; color: #64748b; margin: 0 0 2px; font-weight: 600; }
        .title-area h1 { font-size: 16px; color: #1e1b4b; margin: 0; }
        .subtitle { text-align: center; color: #64748b; font-size: 14px; margin-bottom: 20px; }

        .match-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media(max-width:768px) { .match-grid { grid-template-columns: 1fr; } }
        .col-words, .col-defs { display: flex; flex-direction: column; gap: 15px; }
        .match-card { background: white; border: 2px solid #e9d5ff; border-radius: 12px; padding: 20px; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.02); transition: all 0.2s; user-select: none; display: flex; align-items: center; justify-content: center; text-align: center; min-height: 80px; }
        .match-card:hover:not(.matched) { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(168,85,247,0.08); border-color: #a855f7; }
        .match-word { font-size: 18px; font-weight: 700; color: #1e1b4b; }
        .match-def { font-size: 16px; font-weight: 600; color: #1e1b4b; line-height: 1.4; }
        .match-card.selected { border-color: #a855f7; background: #f3e8ff; transform: scale(1.02); }
        .match-card.matched { background: #dcfce3; border-color: #22c55e; color: #065f46; opacity: 0.6; cursor: default; transform: none; pointer-events: none; }
        .match-card.matched .match-word { color: #065f46; }
        .match-card.matched .match-def { color: #065f46; }
        .match-card.error { animation: shake 0.4s; border-color: #ef4444; background: #fef2f2; color: #b91c1c; }

        @keyframes shake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-5px)} 75%{transform:translateX(5px)} }

        .modal-overlay { position: fixed; top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);display:none;align-items:center;justify-content:center;z-index:1000;opacity:0;transition:opacity 0.3s; }
        .modal-overlay.show { opacity:1; }
        .modal-box { background:white; width:90%; max-width:400px; padding:30px; border-radius:20px; text-align:center; box-shadow:0 10px 25px rgba(0,0,0,0.1); transform:scale(0.9); transition:transform 0.3s; border:1px solid #e9d5ff; }
        .modal-overlay.show .modal-box { transform:scale(1); }
        .modal-box h3 { font-size:22px; color:#1e1b4b; margin:0 0 10px; display:flex; align-items:center; justify-content:center; gap:10px; }
        .modal-box p { font-size:15px; color:#64748b; margin:0 0 25px; line-height:1.5; }
        .highlight-stat { font-size:40px; font-weight:700; color:#a855f7; margin:10px 0; }
        .modal-btns { display:flex; gap:15px; justify-content:center; }
        .btn-modal { flex:1; padding:12px; border:none; border-radius:10px; font-weight:600; font-size:15px; cursor:pointer; transition:0.2s; }
        .btn-primary { background:#a855f7; color:white; }
        .btn-primary:hover { background:#9333ea; }
        .btn-secondary { background:#f1f5f9; color:#475569; }
        .btn-secondary:hover { background:#e2e8f0; }
    </style>
</head>
<body class="app-layout">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="game-wrapper">
            <div class="app-container">
                <div class="header">
                    <a href="learn.php" class="back-btn">&lt;</a>
                    <div class="title-area">
                        <h2>Word Match · <?= htmlspecialchars($cat_name) ?></h2>
                        <h1 id="pairs-counter">0 / 5 pairs matched</h1>
                    </div>
                </div>
                <div class="subtitle">Chọn một từ tiếng Anh, sau đó chọn nghĩa tiếng Việt tương ứng.</div>
                <div class="match-grid">
                    <div class="col-words" id="words-col"></div>
                    <div class="col-defs" id="defs-col"></div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="round-modal">
        <div class="modal-box">
            <h3>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Tuyệt vời!
            </h3>
            <p>Bạn đã ghép đúng toàn bộ 5 từ trong vòng này.</p>
            <div class="modal-btns">
                <button class="btn-modal btn-secondary" onclick="endGame()">Kết thúc</button>
                <button class="btn-modal btn-primary" onclick="startRound()">Học tiếp vòng mới</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="end-modal">
        <div class="modal-box">
            <h3>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                Tổng kết
            </h3>
            <p>Trong phiên học vừa rồi, bạn đã ghép thành công:</p>
            <div class="highlight-stat" id="total-learned">0</div>
            <p>từ vựng.</p>
            <div class="modal-btns">
                <button class="btn-modal btn-primary" onclick="window.location.href='learn.php'">Về chọn chủ đề</button>
            </div>
        </div>
    </div>

    <script>
        const allVocabData = <?= $json_words ?>;
        let vocabPool = [], totalMatchedSession = 0, matchedInRound = 0, totalPairs = 0;
        let selectedWordCard = null, selectedDefCard = null;
        let audioCtx;

        function playTone(freq, type, dur) {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = audioCtx.createOscillator(), gain = audioCtx.createGain();
            osc.type = type; osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + dur);
            osc.connect(gain); gain.connect(audioCtx.destination);
            osc.start(); osc.stop(audioCtx.currentTime + dur);
        }
        function playCorrect() { playTone(600,'sine',0.1); setTimeout(()=>playTone(800,'sine',0.15),100); }
        function playWrong() { playTone(200,'sawtooth',0.2); setTimeout(()=>playTone(150,'sawtooth',0.2),150); }

        function updateProgress(vocabId, result) {
            fetch('match.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=update_progress&vocab_id=' + vocabId + '&result=' + result
            })
            .then(res => res.json())
            .catch(err => console.warn('Cập nhật thất bại:', err));
        }

        function getNext5Words() {
            let selected = [];
            while(selected.length < 5) {
                if(vocabPool.length === 0) vocabPool = [...allVocabData];
                let idx = Math.floor(Math.random() * vocabPool.length);
                let w = vocabPool.splice(idx,1)[0];
                if(!selected.find(s => s.id === w.id)) selected.push(w);
                if(allVocabData.length < 5 && selected.length === allVocabData.length) break;
            }
            return selected;
        }

        function startRound() {
            closeModals();
            let currentWords = getNext5Words();
            totalPairs = currentWords.length;
            matchedInRound = 0;
            if(totalPairs === 0) { document.querySelector('.match-grid').innerHTML = '<h3 style="text-align:center;grid-column:span 2;">Chưa đủ từ vựng!</h3>'; return; }
            document.getElementById('pairs-counter').innerText = `0 / ${totalPairs} pairs matched`;
            let wordsArray = [...currentWords].sort(()=>0.5-Math.random());
            let defsArray = [...currentWords].sort(()=>0.5-Math.random());
            const wc = document.getElementById('words-col'), dc = document.getElementById('defs-col');
            wc.innerHTML = ''; dc.innerHTML = '';
            wordsArray.forEach(item => {
                const card = document.createElement('div');
                card.className = 'match-card word-card';
                card.dataset.id = item.id;
                card.innerHTML = `<div class="match-word">${item.word}</div>`;
                card.onclick = () => handleSelect(card, 'word');
                wc.appendChild(card);
            });
            defsArray.forEach(item => {
                const card = document.createElement('div');
                card.className = 'match-card def-card';
                card.dataset.id = item.id;
                card.innerHTML = `<div class="match-def">${item.definition}</div>`;
                card.onclick = () => handleSelect(card, 'def');
                dc.appendChild(card);
            });
        }

        function handleSelect(card, type) {
            if(card.classList.contains('matched')) return;
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if(type === 'word') {
                if(selectedWordCard) selectedWordCard.classList.remove('selected');
                selectedWordCard = card; selectedWordCard.classList.add('selected');
                if ('speechSynthesis' in window) {
                    const text = card.querySelector('.match-word').innerText;
                    window.speechSynthesis.cancel();
                    window.speechSynthesis.speak(new SpeechSynthesisUtterance(text));
                }
            } else {
                if(selectedDefCard) selectedDefCard.classList.remove('selected');
                selectedDefCard = card; selectedDefCard.classList.add('selected');
            }
            if(selectedWordCard && selectedDefCard) checkMatch();
        }

        function checkMatch() {
            const id1 = selectedWordCard.dataset.id, id2 = selectedDefCard.dataset.id;
            const w = selectedWordCard, d = selectedDefCard;
            const vocabId = parseInt(id1);
            selectedWordCard = null; selectedDefCard = null;
            if(id1 === id2) {
                playCorrect();
                w.classList.remove('selected'); d.classList.remove('selected');
                w.classList.add('matched'); d.classList.add('matched');
                w.innerHTML = `<span style="color:#22c55e;margin-right:8px;">✓</span> ${w.innerHTML}`;
                matchedInRound++; totalMatchedSession++;
                document.getElementById('pairs-counter').innerText = `${matchedInRound} / ${totalPairs} pairs matched`;
                // Cập nhật tiến độ (đúng)
                updateProgress(vocabId, 'right');
                if(matchedInRound === totalPairs) setTimeout(() => showModal('round-modal'), 600);
            } else {
                playWrong();
                w.classList.add('error'); d.classList.add('error');
                // Cập nhật tiến độ (sai)
                updateProgress(vocabId, 'wrong');
                setTimeout(() => { w.classList.remove('selected','error'); d.classList.remove('selected','error'); }, 500);
            }
        }

        function showModal(id) {
            const m = document.getElementById(id);
            m.style.display = 'flex';
            setTimeout(() => m.classList.add('show'), 10);
        }
        function closeModals() {
            document.querySelectorAll('.modal-overlay').forEach(m => { m.classList.remove('show'); setTimeout(()=>m.style.display='none',300); });
        }
        function endGame() {
            closeModals();
            document.getElementById('total-learned').innerText = totalMatchedSession;
            setTimeout(() => showModal('end-modal'), 300);
        }
        window.onload = function() { vocabPool = [...allVocabData]; startRound(); };
    </script>
</body>
</html>