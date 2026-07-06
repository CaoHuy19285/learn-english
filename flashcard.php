<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$user_id = (int)$_SESSION['user_id'];

// ===== XỬ LÝ CẬP NHẬT TIẾN ĐỘ QUA AJAX =====
if (isset($_POST['action']) && $_POST['action'] === 'update_progress') {
    $vocab_id = intval($_POST['vocab_id']);
    $result = $_POST['result'] ?? 'right'; // 'right' hoặc 'wrong'

    if ($vocab_id > 0) {
        // Lấy trạng thái hiện tại của từ này cho user
        $check = $db->select("SELECT status, correct_count, wrong_count FROM user_progress 
                              WHERE user_id = ? AND vocabulary_id = ?", [$user_id, $vocab_id]);

        if (!empty($check)) {
            $current = $check[0];
            $new_status = $current['status'];

            if ($new_status === 'mastered') {
                if ($result === 'wrong') {
                    $db->execute("UPDATE user_progress 
                                  SET wrong_count = wrong_count + 1, last_review = NOW() 
                                  WHERE id = ?", [$current['id']]);
                } else {
                    $db->execute("UPDATE user_progress 
                                  SET correct_count = correct_count + 1, last_review = NOW() 
                                  WHERE id = ?", [$current['id']]);
                }
                echo json_encode(['success' => true, 'status' => $new_status]);
                exit();
            }

            if ($result === 'right') {
                $new_correct = $current['correct_count'] + 1;
                if ($new_correct >= 3) {
                    $new_status = 'mastered';
                } else {
                    $new_status = 'learned';
                }
                $db->execute("UPDATE user_progress 
                              SET correct_count = ?, status = ?, last_review = NOW() 
                              WHERE id = ?", [$new_correct, $new_status, $current['id']]);
            } else {
                $new_wrong = $current['wrong_count'] + 1;
                if ($new_wrong >= 2 && $current['status'] !== 'mastered') {
                    $new_status = 'reviewing';
                } else {
                    $new_status = $current['status'];
                }
                $db->execute("UPDATE user_progress 
                              SET wrong_count = ?, status = ?, last_review = NOW() 
                              WHERE id = ?", [$new_wrong, $new_status, $current['id']]);
            }
        } else {
            $status = ($result === 'right') ? 'learned' : 'reviewing';
            $correct = ($result === 'right') ? 1 : 0;
            $wrong = ($result === 'wrong') ? 1 : 0;
            $db->execute("INSERT INTO user_progress (user_id, vocabulary_id, status, correct_count, wrong_count, last_review, created_at) 
                          VALUES (?, ?, ?, ?, ?, NOW(), NOW())", 
                          [$user_id, $vocab_id, $status, $correct, $wrong]);
        }

        // Ghi vào bảng user_activity
        $db->execute("INSERT INTO user_activity (user_id, activity_type, vocabulary_id, created_at) 
                      VALUES (?, 'flashcard', ?, NOW())", [$user_id, $vocab_id]);

        //  Cập nhật streak
        $db->updateStreak($user_id);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid vocabulary id']);
    }
    exit();
}

// ... phần còn lại của flashcard.php (lấy dữ liệu, HTML, JS)
// ===== LẤY DỮ LIỆU FLASHCARD =====
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$set_id = isset($_GET['set_id']) ? intval($_GET['set_id']) : 0;

$words = [];
$set_title = '';

if ($category_id > 0) {
    $cat = $db->select("SELECT name FROM typeword WHERE id = ?", [$category_id]);
    $set_title = !empty($cat) ? $cat[0]['name'] : 'Chủ đề';
    $vocab = $db->select("SELECT * FROM vocabulary WHERE typeword_id = ?", [$category_id]);
    foreach ($vocab as &$v) {
        $v['difficulty'] = $v['difficulty'] ?? 'medium';
        $v['example'] = $v['example'] ?? '';
        $v['image'] = $v['image'] ?? null;
    }
    $words = $vocab;
} elseif ($set_id > 0) {
    $set_info = $db->select("SELECT * FROM flashcard_sets WHERE id = ? AND user_id = ?", [$set_id, $user_id]);
    $set_info = $set_info[0] ?? null;
    if ($set_info) {
        $set_title = $set_info['title'];
        $cards = $db->select("SELECT front as word, back as definition, NULL as ipa, NULL as example, NULL as image FROM flashcard_cards WHERE set_id = ?", [$set_id]);
        foreach ($cards as &$c) {
            $c['difficulty'] = 'medium';
            $c['ipa'] = '';
            $c['example'] = '';
            $c['image'] = null;
        }
        $words = $cards;
    }
} else {
    $firstSet = $db->select("SELECT id FROM flashcard_sets WHERE user_id = ? ORDER BY id LIMIT 1", [$user_id]);
    if (!empty($firstSet)) {
        header("Location: flashcard.php?set_id=" . $firstSet[0]['id']);
        exit();
    }
    $set_title = 'Bộ thẻ của tôi';
}

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
        .back-btn svg { stroke: currentColor; }
        .title-area h2 { font-size: 14px; color: #64748b; margin: 0 0 2px; font-weight: 500; }
        .title-area h1 { font-size: 16px; color: #1e1b4b; margin: 0; }
        .progress-dots { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; max-width: 200px; }
        .dot { width: 6px; height: 6px; border-radius: 50%; background: #e2e8f0; margin-bottom: 4px; }
        .dot.active { background: #a855f7; }

        .flashcard-wrapper { perspective: 1000px; height: 420px; margin-bottom: 30px; cursor: pointer; }
        .flashcard { width: 100%; height: 100%; position: relative; transform-style: preserve-3d; transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
        .flashcard.flipped { transform: rotateY(180deg); }
        .card-face { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; border-radius: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px; box-sizing: border-box; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .card-front { background: white; border: 1px solid #e9d5ff; }
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; text-transform: lowercase; margin-bottom: 15px; }
        .badge-easy { background: #d1fae5; color: #065f46; }
        .badge-medium { background: #fef3c7; color: #92400e; }
        .badge-hard { background: #fee2e2; color: #991b1b; }

        .word { font-size: 40px; font-weight: 700; color: #1e1b4b; margin: 0 0 5px; text-align: center; }
        .ipa { font-size: 16px; color: #64748b; font-family: monospace; margin-bottom: 15px; }
        .card-image-box { width: 100%; height: 140px; margin-bottom: 20px; display: flex; justify-content: center; align-items: center; border-radius: 12px; overflow: hidden; }
        .card-image-box img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; }
        .listen-btn { background: #f3e8ff; color: #7c3aed; border: none; padding: 8px 20px; border-radius: 25px; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: 0.2s; margin-bottom: 15px; }
        .listen-btn:hover { background: #e9d5ff; transform: scale(1.05); }
        .hint { color: #94a3b8; font-size: 13px; }

        .card-back { background: #a855f7; transform: rotateY(180deg); color: white; text-align: center; }
        .def-label { font-size: 14px; font-weight: 600; opacity: 0.8; letter-spacing: 1px; margin-bottom: 15px; text-transform: uppercase; }
        .definition { font-size: 22px; font-weight: 600; margin-bottom: 25px; line-height: 1.4; }
        .example { font-size: 16px; opacity: 0.9; font-style: italic; line-height: 1.5; }

        .action-btns { display: flex; gap: 20px; justify-content: center; }
        .btn { flex: 1; padding: 15px; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; background: white; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-wrong { border: 2px solid #fee2e2; color: #e11d48; }
        .btn-wrong:hover { background: #fee2e2; }
        .btn-right { border: 2px solid #dcfce3; color: #16a34a; }
        .btn-right:hover { background: #dcfce3; }

        .end-screen { display: none; background: white; border-radius: 20px; padding: 40px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 30px; border: 1px solid #e9d5ff; }
        .end-screen h2 { color: #1e1b4b; font-size: 28px; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; gap: 12px; }
        .end-screen p { color: #64748b; font-size: 16px; margin-bottom: 25px; }
        .stats-box { display: flex; justify-content: center; gap: 30px; margin-bottom: 30px; flex-wrap: wrap; }
        .stat-item { background: #f8fafc; padding: 15px 25px; border-radius: 12px; border: 1px solid #e9d5ff; }
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
                        <a href="<?= $category_id > 0 ? 'learn.php' : 'card.php' ?>" class="back-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        </a>
                        <div class="title-area">
                            <h2>Flashcards · <?= htmlspecialchars($set_title) ?></h2>
                            <h1 id="card-counter">Card 1 of X</h1>
                        </div>
                    </div>
                    <div class="progress-dots" id="progress-dots"></div>
                </div>

                <div id="game-section">
                    <div class="flashcard-wrapper" onclick="flipCard()">
                        <div class="flashcard" id="main-card">
                            <div class="card-face card-front">
                                <div class="card-image-box" id="f-img-box" style="display: none;"><img id="f-img" src="" alt="Minh họa"></div>
                                <span class="badge" id="f-diff">medium</span>
                                <h1 class="word" id="f-word">Word</h1>
                                <div class="ipa" id="f-ipa">/ipa/</div>
                                <button class="listen-btn" onclick="playSound(event)">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                                    Listen
                                </button>
                                <div class="hint">Chạm vào thẻ để xem nghĩa</div>
                            </div>
                            <div class="card-face card-back">
                                <div class="def-label">Ý NGHĨA & VÍ DỤ</div>
                                <div class="definition" id="b-def">Definition here</div>
                                <div class="example" id="b-ex"></div>
                            </div>
                        </div>
                    </div>

                    <div class="action-btns">
                        <button class="btn btn-wrong" onclick="markWrong()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Still Learning
                        </button>
                        <button class="btn btn-right" onclick="markRight()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            Got It! +5 XP
                        </button>
                    </div>
                </div>

                <div class="end-screen" id="end-screen">
                    <h2>
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Tuyệt vời! Bạn đã xem hết thẻ!
                    </h2>
                    <p>Dưới đây là kết quả học tập của bạn trong phiên này:</p>
                    <div class="stats-box">
                        <div class="stat-item"><h3 class="text-orange" id="stat-xp">0</h3><span>XP Nhận được</span></div>
                        <div class="stat-item"><h3 class="text-green" id="stat-right">0</h3><span>Từ đã thuộc</span></div>
                        <div class="stat-item"><h3 class="text-red" id="stat-wrong">0</h3><span>Từ cần học lại</span></div>
                    </div>
                    <div class="action-btns">
                        <button id="btn-review" class="btn btn-wrong" onclick="restartReview()" style="display: none;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                            Học lại từ chưa thuộc
                        </button>
                        <a href="<?= $category_id > 0 ? 'learn.php' : 'card.php' ?>" class="btn btn-right" style="text-decoration: none;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                            Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const originalVocabData = <?= $json_words ?>;
        let currentDeck = [], reviewDeck = [], currentIndex = 0, sessionXP = 0, wordsMastered = 0;

        function initCard() {
            if(originalVocabData.length === 0) {
                document.getElementById('game-section').innerHTML = '<h3 style="text-align:center">Chưa có từ vựng!</h3>';
                return;
            }
            currentDeck = [...originalVocabData];
            currentIndex = 0; reviewDeck = [];
            renderCard(); renderDots();
        }

        function renderCard() {
            const item = currentDeck[currentIndex];
            document.getElementById('main-card').classList.remove('flipped');

            const diff = item.difficulty || 'medium';
            const diffMap = {
                'Dễ': 'easy', 'easy': 'easy',
                'Trung bình': 'medium', 'medium': 'medium',
                'Khó': 'hard', 'hard': 'hard'
            };
            const diffClass = diffMap[diff] || 'medium';
            const badge = document.getElementById('f-diff');
            badge.className = `badge badge-${diffClass}`;
            badge.innerText = diff;

            document.getElementById('f-word').innerText = item.word;
            document.getElementById('f-ipa').innerText = item.ipa || '';
            const imgBox = document.getElementById('f-img-box'), imgEl = document.getElementById('f-img');
            if (item.image && item.image.trim() !== '') {
                imgEl.src = item.image; imgBox.style.display = 'flex';
            } else { imgBox.style.display = 'none'; }
            document.getElementById('b-def').innerText = item.definition;
            document.getElementById('b-ex').innerText = item.example ? `"${item.example}"` : '';
            document.getElementById('card-counter').innerText = `Card ${currentIndex + 1} of ${currentDeck.length}`;
            updateDots();
        }

        function renderDots() {
            const c = document.getElementById('progress-dots'); c.innerHTML = '';
            for(let i=0; i<currentDeck.length; i++) {
                const d = document.createElement('div'); d.className = 'dot';
                if(i===0) d.classList.add('active');
                c.appendChild(d);
            }
        }

        function updateDots() {
            document.querySelectorAll('.dot').forEach((d,i) => { d.classList.toggle('active', i===currentIndex); });
        }

        function flipCard() { document.getElementById('main-card').classList.toggle('flipped'); }

        // --- HÀM GỌI API CẬP NHẬT TIẾN ĐỘ ---
        function updateProgress(vocabId, result) {
            fetch('flashcard.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=update_progress&vocab_id=' + vocabId + '&result=' + result
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // console.log('Cập nhật thành công');
                } else {
                    console.warn('Cập nhật thất bại:', data.error);
                }
            })
            .catch(err => console.error('Lỗi kết nối:', err));
        }

        function markWrong() {
            const word = currentDeck[currentIndex];
            updateProgress(word.id, 'wrong');
            reviewDeck.push(word);
            nextCard();
        }

        function markRight() {
            const word = currentDeck[currentIndex];
            updateProgress(word.id, 'right');
            sessionXP += 5;
            wordsMastered++;
            nextCard();
        }

        function nextCard() {
            if (currentIndex < currentDeck.length - 1) {
                currentIndex++;
                renderCard();
            } else {
                showEndScreen();
            }
        }

        function showEndScreen() {
            document.getElementById('game-section').style.display = 'none';
            document.getElementById('end-screen').style.display = 'block';
            document.getElementById('stat-xp').innerText = '+' + sessionXP;
            document.getElementById('stat-right').innerText = wordsMastered;
            document.getElementById('stat-wrong').innerText = reviewDeck.length;
            const btn = document.getElementById('btn-review');
            if (reviewDeck.length > 0) {
                btn.style.display = 'flex';
                btn.innerText = `Học lại ${reviewDeck.length} từ chưa thuộc`;
            } else {
                btn.style.display = 'none';
                document.querySelector('.end-screen h2').innerHTML = `
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    Tuyệt đỉnh! Bạn đã thuộc 100% từ vựng!
                `;
            }
        }

        function restartReview() {
            currentDeck = [...reviewDeck]; reviewDeck = []; currentIndex = 0;
            document.getElementById('end-screen').style.display = 'none';
            document.getElementById('game-section').style.display = 'block';
            renderCard(); renderDots();
        }

        function playSound(e) {
            e.stopPropagation();
            const w = currentDeck[currentIndex].word;
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const u = new SpeechSynthesisUtterance(w);
                u.lang = 'en-US';
                window.speechSynthesis.speak(u);
            }
        }
        window.onload = initCard;
    </script>
</body>
</html>