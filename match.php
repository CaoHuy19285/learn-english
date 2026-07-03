<?php
session_start();
require_once 'database.php';

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 1;
$db = new Database();

// Lấy tên chủ đề
$cat_data = $db->select("SELECT name FROM typeword WHERE id = ?", [$category_id]);
$cat_name = !empty($cat_data) ? $cat_data[0]['name'] : 'Unknown';

// Lấy TOÀN BỘ danh sách từ của chủ đề này (JS sẽ tự bốc ngẫu nhiên 5 từ mỗi vòng)
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
        @media(max-width: 768px) { .match-grid { grid-template-columns: 1fr; } }
        
        .col-words, .col-defs { display: flex; flex-direction: column; gap: 15px; }
        
        .match-card { background: white; border: 2px solid transparent; border-radius: 12px; padding: 20px; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.03); transition: all 0.2s; user-select: none; display: flex; align-items: center; justify-content: center; text-align: center; min-height: 80px; }
        .match-card:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.06); }
        
        .match-word { font-size: 18px; font-weight: 700; color: #1e1b4b; }
        .match-def { font-size: 16px; font-weight: 600; color: #1e1b4b; line-height: 1.4; }

        .match-card.selected { border-color: #6366f1; background: #f5f7ff; transform: scale(1.02); }
        .match-card.matched { background: #ecfdf5; border-color: #34d399; color: #065f46; opacity: 0.6; cursor: default; transform: none; box-shadow: none; pointer-events: none; }
        .match-card.error { animation: shake 0.4s; border-color: #ef4444; background: #fef2f2; color: #b91c1c; }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Styles cho Modal (Popup) */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); display: none; align-items: center; justify-content: center; z-index: 1000; opacity: 0; transition: opacity 0.3s; }
        .modal-overlay.show { opacity: 1; }
        .modal-box { background: white; width: 90%; max-width: 400px; padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.1); transform: scale(0.9); transition: transform 0.3s; }
        .modal-overlay.show .modal-box { transform: scale(1); }
        
        .modal-box h3 { font-size: 22px; color: #1e1b4b; margin: 0 0 10px; }
        .modal-box p { font-size: 15px; color: #64748b; margin: 0 0 25px; line-height: 1.5; }
        .modal-box .highlight-stat { font-size: 40px; font-weight: 700; color: #10b981; margin: 10px 0; }
        
        .modal-btns { display: flex; gap: 15px; justify-content: center; }
        .btn-modal { flex: 1; padding: 12px; border: none; border-radius: 10px; font-weight: 600; font-size: 15px; cursor: pointer; transition: 0.2s; }
        .btn-primary { background: #4f46e5; color: white; }
        .btn-primary:hover { background: #4338ca; }
        .btn-secondary { background: #f1f5f9; color: #475569; }
        .btn-secondary:hover { background: #e2e8f0; }
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
                
                <div class="subtitle">Click một từ tiếng Anh, sau đó click vào nghĩa tiếng Việt tương ứng.</div>

                <div class="match-grid">
                    <div class="col-words" id="words-col"></div>
                    <div class="col-defs" id="defs-col"></div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="round-modal">
        <div class="modal-box">
            <h3>Tuyệt vời! 🎉</h3>
            <p>Bạn đã ghép đúng toàn bộ 5 từ trong vòng này.</p>
            <div class="modal-btns">
                <button class="btn-modal btn-secondary" onclick="endGame()">Kết thúc</button>
                <button class="btn-modal btn-primary" onclick="startRound()">Học tiếp vòng mới</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="end-modal">
        <div class="modal-box">
            <h3>Tổng kết Học tập 🏆</h3>
            <p>Trong phiên học vừa rồi, bạn đã ghép thành công:</p>
            <div class="highlight-stat" id="total-learned">0</div>
            <p>từ vựng.</p>
            <div class="modal-btns">
                <button class="btn-modal btn-primary" onclick="window.location.href='learn.php'">Về trang học tập</button>
            </div>
        </div>
    </div>

    <script>
        const allVocabData = <?= $json_words ?>;
        let vocabPool = []; // Kho chứa từ vựng chưa bốc
        let totalMatchedSession = 0; // Đếm tổng số từ ghép được trong toàn bộ phiên
        let matchedInRound = 0;
        let totalPairs = 0;
        
        let selectedWordCard = null;
        let selectedDefCard = null;

        // --- HỆ THỐNG ÂM THANH (Sử dụng Web Audio API - Không cần tải file) ---
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        let audioCtx;

        function playTone(frequency, type, duration) {
            if (!audioCtx) audioCtx = new AudioContext();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            
            oscillator.type = type;
            oscillator.frequency.setValueAtTime(frequency, audioCtx.currentTime);
            
            gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime); // Volume
            gainNode.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + duration);
            
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            
            oscillator.start();
            oscillator.stop(audioCtx.currentTime + duration);
        }

        function playCorrectSound() {
            playTone(600, 'sine', 0.1);
            setTimeout(() => playTone(800, 'sine', 0.15), 100);
        }

        function playWrongSound() {
            playTone(200, 'sawtooth', 0.2);
            setTimeout(() => playTone(150, 'sawtooth', 0.2), 150);
        }
        // ----------------------------------------------------------------------

        // Hàm bốc ngẫu nhiên 5 từ
        function getNext5Words() {
            if (allVocabData.length === 0) return [];
            
            let selected = [];
            while(selected.length < 5) {
                // Nếu kho từ rỗng, nạp lại kho từ mảng gốc
                if(vocabPool.length === 0) {
                    vocabPool = [...allVocabData];
                }
                
                // Chọn ngẫu nhiên 1 từ trong kho
                let idx = Math.floor(Math.random() * vocabPool.length);
                let word = vocabPool[idx];
                vocabPool.splice(idx, 1); // Rút từ đó ra khỏi kho
                
                // Tránh trùng lặp trong cùng 1 vòng 5 từ
                if(!selected.find(w => w.id === word.id)) {
                    selected.push(word);
                }
                
                // Nếu DB có ít hơn 5 từ thì cho phép thoát vòng lặp để tránh infinite loop
                if (allVocabData.length < 5 && selected.length === allVocabData.length) {
                    break; 
                }
            }
            return selected;
        }

        // Khởi tạo vòng mới
        function startRound() {
            closeModals();
            let currentWords = getNext5Words();
            totalPairs = currentWords.length;
            matchedInRound = 0;
            
            if(totalPairs === 0) {
                document.querySelector('.match-grid').innerHTML = '<h3 style="text-align:center; grid-column: span 2;">Chưa đủ từ vựng để chơi!</h3>';
                return;
            }
            
            document.getElementById('pairs-counter').innerText = `0 / ${totalPairs} pairs matched`;

            // Trộn mảng cho cột tiếng Anh và cột tiếng Việt
            let wordsArray = [...currentWords].sort(() => 0.5 - Math.random());
            let defsArray = [...currentWords].sort(() => 0.5 - Math.random());

            const wordsCol = document.getElementById('words-col');
            const defsCol = document.getElementById('defs-col');
            wordsCol.innerHTML = '';
            defsCol.innerHTML = '';

            wordsArray.forEach(item => {
                const card = document.createElement('div');
                card.className = 'match-card word-card';
                card.dataset.id = item.id;
                // ĐÃ XÓA IPA ĐỂ CÂN BẰNG GIAO DIỆN
                card.innerHTML = `<div class="match-word">${item.word}</div>`;
                card.onclick = () => handleSelect(card, 'word');
                wordsCol.appendChild(card);
            });

            defsArray.forEach(item => {
                const card = document.createElement('div');
                card.className = 'match-card def-card';
                card.dataset.id = item.id;
                card.innerHTML = `<div class="match-def">${item.definition}</div>`;
                card.onclick = () => handleSelect(card, 'def');
                defsCol.appendChild(card);
            });
        }

        function handleSelect(card, type) {
            if(card.classList.contains('matched')) return;

            // Kích hoạt AudioContext (Do browser yêu cầu user interact trước khi phát âm thanh)
            if (!audioCtx) audioCtx = new AudioContext();

            if(type === 'word') {
                if(selectedWordCard) selectedWordCard.classList.remove('selected');
                selectedWordCard = card;
                selectedWordCard.classList.add('selected');
                
                // Đọc từ vựng
                if ('speechSynthesis' in window) {
                    const text = card.querySelector('.match-word').innerText;
                    window.speechSynthesis.cancel();
                    window.speechSynthesis.speak(new SpeechSynthesisUtterance(text));
                }
            } else {
                if(selectedDefCard) selectedDefCard.classList.remove('selected');
                selectedDefCard = card;
                selectedDefCard.classList.add('selected');
            }

            if(selectedWordCard && selectedDefCard) {
                checkMatch();
            }
        }

        function checkMatch() {
            const id1 = selectedWordCard.dataset.id;
            const id2 = selectedDefCard.dataset.id;
            
            const wCard = selectedWordCard;
            const dCard = selectedDefCard;
            
            selectedWordCard = null;
            selectedDefCard = null;

            if(id1 === id2) {
                // MATCH ĐÚNG
                playCorrectSound(); // Phát âm thanh đúng
                
                wCard.classList.remove('selected');
                dCard.classList.remove('selected');
                wCard.classList.add('matched');
                dCard.classList.add('matched');
                
                wCard.innerHTML = `<span style="color:#10b981; margin-right: 8px;">✓</span> ` + wCard.innerHTML;
                
                matchedInRound++;
                totalMatchedSession++;
                document.getElementById('pairs-counter').innerText = `${matchedInRound} / ${totalPairs} pairs matched`;
                
                if(matchedInRound === totalPairs) {
                    setTimeout(() => {
                        showModal('round-modal');
                    }, 600);
                }
            } else {
                // MATCH SAI
                playWrongSound(); // Phát âm thanh sai
                
                wCard.classList.add('error');
                dCard.classList.add('error');
                
                setTimeout(() => {
                    wCard.classList.remove('selected', 'error');
                    dCard.classList.remove('selected', 'error');
                }, 500);
            }
        }

        // --- Quản lý Modal ---
        function showModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function closeModals() {
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.classList.remove('show');
                setTimeout(() => modal.style.display = 'none', 300);
            });
        }

        function endGame() {
            closeModals();
            document.getElementById('total-learned').innerText = totalMatchedSession;
            setTimeout(() => {
                showModal('end-modal');
            }, 300);
        }

        // Khởi động khi tải trang
        window.onload = function() {
            vocabPool = [...allVocabData]; // Nạp đầy kho từ vựng
            startRound();
        };
    </script>
</body>
</html>