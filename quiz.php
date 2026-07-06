<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = (int)$_SESSION['user_id'];
$db = new Database();

// ===== XỬ LÝ CẬP NHẬT TIẾN ĐỘ QUA AJAX =====
if (isset($_POST['action']) && $_POST['action'] === 'update_progress') {
    $vocab_id = intval($_POST['vocab_id']);
    $result = $_POST['result'] ?? 'right';

    if ($vocab_id > 0) {
        // Cập nhật progress
        $check = $db->select("SELECT status, correct_count, wrong_count FROM user_progress 
                              WHERE user_id = ? AND vocabulary_id = ?", [$user_id, $vocab_id]);
        if (!empty($check)) {
            $current = $check[0];
            $new_status = $current['status'];
            if ($result === 'right') {
                $new_correct = $current['correct_count'] + 1;
                if ($new_correct >= 3) $new_status = 'mastered';
                else $new_status = 'learned';
                $db->execute("UPDATE user_progress 
                              SET correct_count = ?, status = ?, last_review = NOW() 
                              WHERE id = ?", [$new_correct, $new_status, $current['id']]);
            } else {
                $new_wrong = $current['wrong_count'] + 1;
                if ($new_wrong >= 2 && $current['status'] !== 'mastered') $new_status = 'reviewing';
                else $new_status = $current['status'];
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

        // Cập nhật streak mỗi khi có hoạt động
        $db->updateStreak($user_id);

        // Ghi activity
        $db->execute("INSERT INTO user_activity (user_id, activity_type, vocabulary_id, created_at) 
                      VALUES (?, 'quiz', ?, NOW())", [$user_id, $vocab_id]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// ===== LẤY DỮ LIỆU =====
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 1;
$cat_data = $db->select("SELECT name FROM typeword WHERE id = ?", [$category_id]);
$cat_name = !empty($cat_data) ? $cat_data[0]['name'] : 'Unknown';
$words = $db->select("SELECT * FROM vocabulary WHERE typeword_id = ?", [$category_id]);
$all_defs_raw = $db->select("SELECT definition FROM vocabulary");
$all_definitions = array_column($all_defs_raw, 'definition');
$json_words = json_encode($words, JSON_UNESCAPED_UNICODE);
$json_all_defs = json_encode($all_definitions, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quiz - WordWise</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        /* ... CSS giữ nguyên như cũ ... */
        .game-wrapper { display: flex; justify-content: center; align-items: flex-start; min-height: 80vh; padding-top: 20px; }
        .app-container { width: 100%; max-width: 700px; }
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .back-btn { width: 40px; height: 40px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; color: #1e1b4b; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .title-area h2 { font-size: 13px; color: #64748b; margin: 0 0 2px; font-weight: 600; }
        .title-area h1 { font-size: 16px; color: #1e1b4b; margin: 0; }
        .score-display { font-size: 15px; font-weight: 600; color: #ea580c; display: flex; align-items: center; gap: 5px; }
        .question-card { background: white; border-radius: 20px; padding: 40px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 20px; position: relative; border: 1px solid #e9d5ff; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; position: absolute; top: 20px; left: 20px; }
        .badge-easy { background: #d1fae5; color: #065f46; }
        .badge-medium { background: #fef3c7; color: #92400e; }
        .badge-hard { background: #fee2e2; color: #991b1b; }
        .audio-btn { background: #f3e8ff; color: #7c3aed; border: none; width: 36px; height: 36px; border-radius: 50%; position: absolute; top: 20px; right: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .audio-btn:hover { background: #a855f7; color: white; }
        .word { font-size: 36px; font-weight: 700; color: #1e1b4b; margin: 20px 0 10px; }
        .ipa { font-size: 16px; color: #64748b; font-family: monospace; margin-bottom: 20px; }
        .instruction { font-size: 14px; color: #64748b; }
        .options-grid { display: flex; flex-direction: column; gap: 12px; }
        .option-btn { background: white; border: 2px solid #e2e8f0; border-radius: 16px; padding: 20px; text-align: left; font-size: 15px; font-weight: 500; color: #1e1b4b; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; }
        .option-btn:hover:not(:disabled) { border-color: #a855f7; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(168,85,247,0.08); }
        .option-label { font-weight: 700; color: #64748b; margin-right: 15px; font-size: 16px; }
        .option-btn.correct { background: #dcfce3; border-color: #22c55e; color: #166534; }
        .option-btn.wrong { background: #fee2e2; border-color: #ef4444; color: #991b1b; }
        .option-btn:disabled { cursor: not-allowed; opacity: 0.8; }
        .next-btn-container { text-align: center; margin-top: 30px; display: none; }
        .next-btn { background: #a855f7; color: white; border: none; padding: 14px 30px; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .next-btn:hover { background: #9333ea; }
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
                            <h2>Quiz · <?= htmlspecialchars($cat_name) ?></h2>
                            <h1 id="question-counter">Question 1 / X</h1>
                        </div>
                    </div>
                    <div class="score-display">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        <span id="score-text">0 pts</span>
                    </div>
                </div>

                <div class="question-card">
                    <span class="badge" id="q-diff">medium</span>
                    <button class="audio-btn" onclick="playSound()" title="Nghe phát âm">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/></svg>
                    </button>
                    <h1 class="word" id="q-word">Word</h1>
                    <div class="ipa" id="q-ipa">/ipa/</div>
                    <div class="instruction">Chọn định nghĩa đúng:</div>
                </div>

                <div class="options-grid" id="options-container"></div>
                <div class="next-btn-container" id="next-container">
                    <button class="next-btn" onclick="nextQuestion()">
                        Tiếp tục
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
        const vocabData = <?= $json_words ?>;
        const allDefs = <?= $json_all_defs ?>;
        let currentIndex = 0, score = 0;
        const labels = ['A', 'B', 'C', 'D'];
        const optionsContainer = document.getElementById('options-container');
        const nextContainer = document.getElementById('next-container');

        // Hàm gọi AJAX cập nhật tiến độ
        function updateProgress(vocabId, result) {
            fetch('quiz.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=update_progress&vocab_id=' + vocabId + '&result=' + result
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) console.warn('Cập nhật thất bại');
            })
            .catch(err => console.error('Lỗi kết nối:', err));
        }

        function initQuiz() {
            if(vocabData.length === 0) return;
            renderQuestion();
        }

        function renderQuestion() {
            nextContainer.style.display = 'none';
            const item = vocabData[currentIndex];
            document.getElementById('question-counter').innerText = `Question ${currentIndex+1} / ${vocabData.length}`;

            const diff = item.difficulty || 'medium';
            const diffMap = {
                'Dễ': 'easy', 'easy': 'easy',
                'Trung bình': 'medium', 'medium': 'medium',
                'Khó': 'hard', 'hard': 'hard'
            };
            const diffClass = diffMap[diff] || 'medium';
            const badge = document.getElementById('q-diff');
            badge.className = `badge badge-${diffClass}`;
            badge.innerText = diff;

            document.getElementById('q-word').innerText = item.word;
            document.getElementById('q-ipa').innerText = item.ipa || '';
            playSound();

            let opts = [item.definition];
            let avail = allDefs.filter(d => d !== item.definition);
            avail.sort(() => 0.5 - Math.random());
            opts.push(...avail.slice(0, 3));
            while(opts.length < 4) opts.push('Định nghĩa bổ sung');
            opts.sort(() => 0.5 - Math.random());

            optionsContainer.innerHTML = '';
            opts.forEach((opt, idx) => {
                const btn = document.createElement('button');
                btn.className = 'option-btn';
                btn.innerHTML = `<span class="option-label">${labels[idx]}.</span> <span>${opt}</span>`;
                btn.onclick = () => checkAnswer(btn, opt === item.definition, item.id);
                optionsContainer.appendChild(btn);
            });
        }

        function checkAnswer(btn, correct, vocabId) {
            const all = optionsContainer.querySelectorAll('.option-btn');
            all.forEach(b => b.disabled = true);
            if(correct) {
                btn.classList.add('correct');
                score += 10;
                document.getElementById('score-text').innerText = `${score} pts`;
                updateProgress(vocabId, 'right');
            } else {
                btn.classList.add('wrong');
                updateProgress(vocabId, 'wrong');
                all.forEach(b => {
                    if(b.innerText.includes(vocabData[currentIndex].definition)) b.classList.add('correct');
                });
            }
            nextContainer.style.display = 'block';
        }

        function nextQuestion() {
            if(currentIndex < vocabData.length - 1) { currentIndex++; renderQuestion(); }
            else {
                alert(`Hoàn thành! Điểm của bạn: ${score} pts`);
                window.location.href = 'learn.php';
            }
        }

        function playSound() {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const u = new SpeechSynthesisUtterance(vocabData[currentIndex].word);
                u.lang = 'en-US';
                window.speechSynthesis.speak(u);
            }
        }
        window.onload = initQuiz;
    </script>
</body>
</html>