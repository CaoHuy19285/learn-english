<?php
session_start();
require_once 'database.php';

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 1;
$db = new Database();

// Lấy tên chủ đề
$cat_data = $db->select("SELECT name FROM typeword WHERE id = ?", [$category_id]);
$cat_name = !empty($cat_data) ? $cat_data[0]['name'] : 'Unknown';

// Lấy danh sách từ vựng theo ID
$words = $db->select("SELECT * FROM vocabulary WHERE typeword_id = ?", [$category_id]);

// Lấy tất cả định nghĩa để làm phương án sai
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
        .game-wrapper { display: flex; justify-content: center; align-items: flex-start; min-height: 80vh; padding-top: 20px; }
        .app-container { width: 100%; max-width: 700px; }
        
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .back-btn { width: 40px; height: 40px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; color: #1e1b4b; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .title-area h2 { font-size: 13px; color: #64748b; margin: 0 0 2px; font-weight: 600; }
        .title-area h1 { font-size: 16px; color: #1e1b4b; margin: 0; }
        .score-display { font-size: 15px; font-weight: 600; color: #ea580c; display: flex; align-items: center; gap: 5px; }

        .question-card { background: white; border-radius: 20px; padding: 40px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 20px; position: relative; }
        .badge { background: #fef3c7; color: #b45309; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; position: absolute; top: 20px; left: 20px; }
        .audio-btn { background: #e0e7ff; color: #4f46e5; border: none; width: 36px; height: 36px; border-radius: 50%; position: absolute; top: 20px; right: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .audio-btn:hover { background: #4f46e5; color: white; }
        
        .word { font-size: 36px; font-weight: 700; color: #1e1b4b; margin: 20px 0 10px; }
        .ipa { font-size: 16px; color: #64748b; font-family: monospace; margin-bottom: 20px; }
        .instruction { font-size: 14px; color: #64748b; }

        .options-grid { display: flex; flex-direction: column; gap: 12px; }
        .option-btn { background: white; border: 2px solid transparent; border-radius: 16px; padding: 20px; text-align: left; font-size: 15px; font-weight: 500; color: #1e1b4b; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .option-btn:hover:not(:disabled) { border-color: #6366f1; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1); }
        .option-label { font-weight: 700; color: #64748b; margin-right: 15px; font-size: 16px; }
        
        .option-btn.correct { background: #dcfce3; border-color: #22c55e; color: #166534; }
        .option-btn.wrong { background: #fee2e2; border-color: #ef4444; color: #991b1b; }
        .option-btn:disabled { cursor: not-allowed; opacity: 0.8; }
        
        .next-btn-container { text-align: center; margin-top: 30px; display: none; }
        .next-btn { background: #4f46e5; color: white; border: none; padding: 14px 30px; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .next-btn:hover { background: #4338ca; }
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
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        <span id="score-text">0 pts</span>
                    </div>
                </div>

                <div class="question-card">
                    <span class="badge" id="q-diff">medium</span>
                    <button class="audio-btn" onclick="playSound()" title="Nghe phát âm">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon></svg>
                    </button>
                    <h1 class="word" id="q-word">Word</h1>
                    <div class="ipa" id="q-ipa">/ipa/</div>
                    <div class="instruction">Select the correct definition:</div>
                </div>

                <div class="options-grid" id="options-container"></div>

                <div class="next-btn-container" id="next-container">
                    <button class="next-btn" onclick="nextQuestion()">Tiếp tục ➔</button>
                </div>
            </div>
        </div>
    </main>

    <script>
        const vocabData = <?= $json_words ?>;
        const allDefs = <?= $json_all_defs ?>;
        let currentIndex = 0;
        let score = 0;
        
        const optionsContainer = document.getElementById('options-container');
        const nextContainer = document.getElementById('next-container');
        const labels = ['A', 'B', 'C', 'D'];

        function initQuiz() {
            if(vocabData.length === 0) return;
            renderQuestion();
        }

        function renderQuestion() {
            nextContainer.style.display = 'none';
            const currentItem = vocabData[currentIndex];
            
            document.getElementById('question-counter').innerText = `Question ${currentIndex + 1} / ${vocabData.length}`;
            document.getElementById('q-diff').innerText = currentItem.difficulty;
            document.getElementById('q-word').innerText = currentItem.word;
            document.getElementById('q-ipa').innerText = currentItem.ipa;
            
            playSound();

            let options = [currentItem.definition];
            let availableDefs = allDefs.filter(d => d !== currentItem.definition);
            
            availableDefs.sort(() => 0.5 - Math.random());
            options.push(...availableDefs.slice(0, 3));
            
            while(options.length < 4) { options.push("Định nghĩa bổ sung " + Math.random()); }
            options.sort(() => 0.5 - Math.random());

            optionsContainer.innerHTML = '';
            options.forEach((opt, index) => {
                const btn = document.createElement('button');
                btn.className = 'option-btn';
                btn.innerHTML = `<span class="option-label">${labels[index]}.</span> <span>${opt}</span>`;
                btn.onclick = () => checkAnswer(btn, opt === currentItem.definition);
                optionsContainer.appendChild(btn);
            });
        }

        function checkAnswer(selectedBtn, isCorrect) {
            const allBtns = optionsContainer.querySelectorAll('.option-btn');
            allBtns.forEach(btn => btn.disabled = true);

            if(isCorrect) {
                selectedBtn.classList.add('correct');
                score += 10;
                document.getElementById('score-text').innerText = `${score} pts`;
            } else {
                selectedBtn.classList.add('wrong');
                allBtns.forEach(btn => {
                    if(btn.innerText.includes(vocabData[currentIndex].definition)) {
                        btn.classList.add('correct');
                        btn.style.border = "2px solid #22c55e";
                    }
                });
            }
            nextContainer.style.display = 'block';
        }

        function nextQuestion() {
            if (currentIndex < vocabData.length - 1) {
                currentIndex++;
                renderQuestion();
            } else {
                alert(`Hoàn thành! Điểm của bạn: ${score} pts`);
                window.location.href = 'learn.php';
            }
        }

        function playSound() {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(vocabData[currentIndex].word);
                utterance.lang = 'en-US';
                window.speechSynthesis.speak(utterance);
            }
        }

        window.onload = initQuiz;
    </script>
</body>
</html>