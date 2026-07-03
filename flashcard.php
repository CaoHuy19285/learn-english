<?php
session_start();
require_once 'database.php';

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 1;
$db = new Database();

// Lấy tên chủ đề
$cat_data = $db->select("SELECT name FROM typeword WHERE id = ?", [$category_id]);
$cat_name = !empty($cat_data) ? $cat_data[0]['name'] : 'Unknown';

// Lấy danh sách từ vựng (Bao gồm cả cột image)
$words = $db->select("SELECT * FROM vocabulary WHERE typeword_id = ?", [$category_id]);
$json_words = json_encode($words, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Flashcards - WordWise</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .game-wrapper { display: flex; justify-content: center; align-items: flex-start; min-height: 80vh; padding-top: 20px; }
        .app-container { width: 100%; max-width: 700px; }
        
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .back-btn { width: 40px; height: 40px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; color: #1e1b4b; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .title-area h2 { font-size: 14px; color: #64748b; margin: 0 0 2px; font-weight: 500; }
        .title-area h1 { font-size: 16px; color: #1e1b4b; margin: 0; }
        
        .progress-dots { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; max-width: 200px; }
        .dot { width: 6px; height: 6px; border-radius: 50%; background: #e2e8f0; margin-bottom: 4px; }
        .dot.active { background: #4f86f7; }

        /* Flashcard Styles */
        .flashcard-wrapper { perspective: 1000px; height: 420px; margin-bottom: 30px; cursor: pointer; }
        .flashcard { width: 100%; height: 100%; position: relative; transform-style: preserve-3d; transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
        .flashcard.flipped { transform: rotateY(180deg); }
        .card-face { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; border-radius: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px; box-sizing: border-box; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        
        .card-front { background: white; }
        .badge { background: #fef3c7; color: #b45309; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; text-transform: lowercase; margin-bottom: 15px; }
        .word { font-size: 40px; font-weight: 700; color: #1e1b4b; margin: 0 0 5px; text-align: center; }
        .ipa { font-size: 16px; color: #64748b; font-family: monospace; margin-bottom: 15px; }
        
        /* Khu vực hiển thị ảnh trên thẻ */
        .card-image-box { width: 100%; height: 140px; margin-bottom: 20px; display: flex; justify-content: center; align-items: center; border-radius: 12px; overflow: hidden; }
        .card-image-box img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; }

        .listen-btn { background: #eef2ff; color: #4f46e5; border: none; padding: 8px 20px; border-radius: 25px; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: 0.2s; margin-bottom: 15px; }
        .listen-btn:hover { background: #e0e7ff; transform: scale(1.05); }
        .hint { color: #94a3b8; font-size: 13px; }

        .card-back { background: #4a8df8; transform: rotateY(180deg); color: white; text-align: center; }
        .def-label { font-size: 14px; font-weight: 600; opacity: 0.8; letter-spacing: 1px; margin-bottom: 15px; text-transform: uppercase; }
        .definition { font-size: 22px; font-weight: 600; margin-bottom: 25px; line-height: 1.4; }
        .example { font-size: 16px; opacity: 0.9; font-style: italic; line-height: 1.5; }

        /* Action Buttons */
        .action-btns { display: flex; gap: 20px; justify-content: center; }
        .btn { flex: 1; padding: 15px; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; background: white; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-wrong { border: 2px solid #fee2e2; color: #e11d48; }
        .btn-wrong:hover { background: #fee2e2; }
        .btn-right { border: 2px solid #dcfce3; color: #16a34a; }
        .btn-right:hover { background: #dcfce3; }

        /* End Screen Style */
        .end-screen { display: none; background: white; border-radius: 20px; padding: 40px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .end-screen h2 { color: #1e1b4b; font-size: 28px; margin-bottom: 10px; }
        .end-screen p { color: #64748b; font-size: 16px; margin-bottom: 25px; }
        .stats-box { display: flex; justify-content: center; gap: 30px; margin-bottom: 30px; }
        .stat-item { background: #f8fafc; padding: 15px 25px; border-radius: 12px; }
        .stat-item h3 { margin: 0 0 5px; font-size: 24px; color: #1e1b4b; }
        .stat-item span { font-size: 13px; color: #64748b; text-transform: uppercase; font-weight: 600; }
        .text-green { color: #10b981 !important; }
        .text-red { color: #ef4444 !important; }
        .text-orange { color: #f97316 !important; }
    </style>
</head>
<body class="app-layout">

    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="game-wrapper">
            <div class="app-container">
                
                <div class="header">
                    <div class="header-left">
                        <a href="learn.php" class="back-btn">&lt;</a>
                        <div class="title-area">
                            <h2>Flashcards · <?= htmlspecialchars($cat_name) ?></h2>
                            <h1 id="card-counter">Card 1 of X</h1>
                        </div>
                    </div>
                    <div class="progress-dots" id="progress-dots"></div>
                </div>

                <div id="game-section">
                    <div class="flashcard-wrapper" onclick="flipCard()">
                        <div class="flashcard" id="main-card">
                            <div class="card-face card-front">
                                <div class="card-image-box" id="f-img-box" style="display: none;">
                                    <img id="f-img" src="" alt="Minh họa">
                                </div>
                                <span class="badge" id="f-diff">medium</span>
                                <h1 class="word" id="f-word">Word</h1>
                                <div class="ipa" id="f-ipa">/ipa/</div>
                                
                                

                                <button class="listen-btn" onclick="playSound(event)">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
                                    Listen
                                </button>
                                <div class="hint">Chạm vào thẻ để xem nghĩa</div>
                            </div>
                            <div class="card-face card-back">
                                <div class="def-label">Ý NGHĨA & VÍ DỤ</div>
                                <div class="definition" id="b-def">Definition here</div>
                                <div class="example" id="b-ex">"Example sentence here"</div>
                            </div>
                        </div>
                    </div>

                    <div class="action-btns">
                        <button class="btn btn-wrong" onclick="markWrong()">✕ Still Learning</button>
                        <button class="btn btn-right" onclick="markRight()">✓ Got It! +5 XP</button>
                    </div>
                </div>

                <div class="end-screen" id="end-screen">
                    <h2>Tuyệt vời! Bạn đã xem hết thẻ! 🎉</h2>
                    <p>Dưới đây là kết quả học tập của bạn trong phiên này:</p>
                    
                    <div class="stats-box">
                        <div class="stat-item">
                            <h3 class="text-orange" id="stat-xp">0</h3>
                            <span>XP Nhận được</span>
                        </div>
                        <div class="stat-item">
                            <h3 class="text-green" id="stat-right">0</h3>
                            <span>Từ đã thuộc</span>
                        </div>
                        <div class="stat-item">
                            <h3 class="text-red" id="stat-wrong">0</h3>
                            <span>Từ cần học lại</span>
                        </div>
                    </div>

                    <div class="action-btns">
                        <button id="btn-review" class="btn btn-wrong" onclick="restartReview()" style="display: none;">Học lại từ chưa thuộc ↺</button>
                        <a href="learn.php" class="btn btn-right" style="text-decoration: none;">Về Màn Hình Chính ➔</a>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        const originalVocabData = <?= $json_words ?>;
        
        let currentDeck = [];
        let reviewDeck = []; // Chứa những từ bấm "Still Learning"
        let currentIndex = 0;
        
        // Stats
        let sessionXP = 0;
        let wordsMastered = 0;

        function initCard() {
            if(originalVocabData.length === 0) {
                document.getElementById('game-section').innerHTML = '<h3 style="text-align:center">Chủ đề này chưa có từ vựng!</h3>';
                return;
            }
            // Khởi tạo deck bằng toàn bộ từ vựng lúc ban đầu
            currentDeck = [...originalVocabData];
            currentIndex = 0;
            reviewDeck = [];
            
            renderCard();
            renderDots();
        }

        function renderCard() {
            const item = currentDeck[currentIndex];
            document.getElementById('main-card').classList.remove('flipped');
            
            document.getElementById('f-diff').innerText = item.difficulty;
            document.getElementById('f-word').innerText = item.word;
            document.getElementById('f-ipa').innerText = item.ipa;
            
            // Xử lý hình ảnh (Nếu có thì hiện, không thì ẩn khung ảnh)
            const imgBox = document.getElementById('f-img-box');
            const imgEl = document.getElementById('f-img');
            if (item.image && item.image.trim() !== '') {
                imgEl.src = item.image;
                imgBox.style.display = 'flex';
            } else {
                imgBox.style.display = 'none';
            }

            document.getElementById('b-def').innerText = item.definition;
            document.getElementById('b-ex').innerText = `"${item.example}"`;
            document.getElementById('card-counter').innerText = `Card ${currentIndex + 1} of ${currentDeck.length}`;
            
            updateDots();
        }

        function renderDots() {
            const dotsContainer = document.getElementById('progress-dots');
            dotsContainer.innerHTML = '';
            for(let i = 0; i < currentDeck.length; i++) {
                const dot = document.createElement('div');
                dot.className = 'dot';
                if(i === 0) dot.classList.add('active');
                dotsContainer.appendChild(dot);
            }
        }

        function updateDots() {
            const dots = document.querySelectorAll('.dot');
            dots.forEach((dot, index) => {
                if(index === currentIndex) dot.classList.add('active');
                else dot.classList.remove('active');
            });
        }

        function flipCard() {
            document.getElementById('main-card').classList.toggle('flipped');
        }

        // Xử lý khi bấm nút "✕ Still Learning"
        function markWrong() {
            reviewDeck.push(currentDeck[currentIndex]); // Lưu lại vào danh sách cần học lại
            nextCard();
        }

        // Xử lý khi bấm nút "✓ Got It!"
        function markRight() {
            sessionXP += 5; 
            wordsMastered++;
            // Note: Tại đây bạn có thể gọi AJAX để lưu XP vào DB nếu cần
            nextCard();
        }

        function nextCard() {
            if (currentIndex < currentDeck.length - 1) {
                currentIndex++;
                renderCard();
            } else {
                // Đã hết thẻ trong lượt học hiện tại -> Hiện thông báo tổng kết
                showEndScreen();
            }
        }

        function showEndScreen() {
            // Ẩn khu vực chơi, hiện khu vực kết quả
            document.getElementById('game-section').style.display = 'none';
            document.getElementById('end-screen').style.display = 'block';
            
            // Cập nhật số liệu
            document.getElementById('stat-xp').innerText = '+' + sessionXP;
            document.getElementById('stat-right').innerText = wordsMastered;
            document.getElementById('stat-wrong').innerText = reviewDeck.length;
            
            // Hiện nút Học lại nếu có từ chưa thuộc
            const btnReview = document.getElementById('btn-review');
            if (reviewDeck.length > 0) {
                btnReview.style.display = 'flex';
                btnReview.innerText = `Học lại ${reviewDeck.length} từ chưa thuộc ↺`;
            } else {
                btnReview.style.display = 'none';
                document.querySelector('.end-screen h2').innerText = "Tuyệt đỉnh! Bạn đã thuộc 100% từ vựng! 🏆";
            }
        }

        // Hàm Reset lại Game nhưng chỉ với những từ "Still Learning"
        function restartReview() {
            currentDeck = [...reviewDeck]; // Lấy danh sách chưa thuộc làm danh sách học chính
            reviewDeck = []; // Xóa rỗng danh sách chưa thuộc để làm vòng mới
            currentIndex = 0;
            
            // Chuyển giao diện
            document.getElementById('end-screen').style.display = 'none';
            document.getElementById('game-section').style.display = 'block';
            
            renderCard();
            renderDots();
        }

        function playSound(event) {
            event.stopPropagation();
            const wordToSpeak = currentDeck[currentIndex].word;
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(wordToSpeak);
                utterance.lang = 'en-US';
                window.speechSynthesis.speak(utterance);
            }
        }

        window.onload = initCard;
    </script>
</body>
</html>