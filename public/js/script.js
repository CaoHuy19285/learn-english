let currentCardIndex = 0;
let isFlipped = false;

// Hàm chuyển đổi giữa tab Dashboard và Flashcard
function switchTab(tabId) {
    const sections = document.querySelectorAll('.view-section');
    sections.forEach(section => section.style.display = 'none');

    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => item.classList.remove('active'));

    if (tabId === 'dashboard') {
        document.getElementById('view-dashboard').style.display = 'block';
        navItems[0].classList.add('active');
    } else if (tabId === 'flashcards') {
        document.getElementById('view-flashcards').style.display = 'block';
        navItems[1].classList.add('active');
        loadCard(); // Tải từ vựng đầu tiên từ DB khi mở tab
    }
}

// Đổ dữ liệu từ mảng SQL vào cấu trúc thẻ HTML
function loadCard() {
    if (!wordsData || wordsData.length === 0) {
        document.getElementById('studyCard').innerHTML = "<h3>Hiện tại chưa có từ vựng nào trong CSDL! Hãy đăng nhập quyền Admin để thêm từ.</h3>";
        return;
    }

    isFlipped = false;
    const currentWord = wordsData[currentCardIndex];
    
    // Cập nhật thanh meta thông tin số lượng
    document.getElementById('card-category').innerText = `Thẻ ghi nhớ • ${currentWord.category}`;
    document.getElementById('card-index').innerText = `Thẻ ${currentCardIndex + 1} trên ${wordsData.length}`;

    // Vẽ giao diện mặt trước của thẻ (Tiếng Anh)
    renderFrontCard(currentWord);
}

function renderFrontCard(wordObj) {
    const card = document.getElementById('studyCard');
    card.style.background = 'var(--white)';
    card.innerHTML = `
        <div class="difficulty-badge">${wordObj.difficulty}</div>
        <h2 class="word">${wordObj.word}</h2>
        <p class="pronunciation">${wordObj.ipa}</p>
        <button class="listen-btn" onclick="playAudio('${wordObj.word}')">🔊 Nghe phát âm</button>
        <p class="instruction">Chạm vào vùng trống của thẻ để lật xem nghĩa tiếng Việt</p>
    `;
}

// Lắng nghe sự kiện click lật mặt thẻ
const studyCard = document.getElementById('studyCard');
if (studyCard) {
    studyCard.addEventListener('click', function(e) {
        if (e.target.tagName.toLowerCase() === 'button' || wordsData.length === 0) return;

        const currentWord = wordsData[currentCardIndex];
        if (!isFlipped) {
            // Lật ra mặt sau (Nghĩa Tiếng Việt & Ví dụ ngữ cảnh)
            this.style.background = '#eef2ff'; 
            this.innerHTML = `
                <div class="difficulty-badge">${currentWord.difficulty}</div>
                <h2 class="word" style="color:#4f46e5;">${currentWord.definition}</h2>
                <p class="pronunciation" style="margin-bottom: 15px; font-weight:500; color:#374151;">Ví dụ áp dụng:</p>
                <p style="font-size:14px; padding:0 30px; font-style:italic; text-align:center; color:#6b7280;">"${currentWord.example}"</p>
                <p class="instruction" style="margin-top:30px;">Chạm lại để quay về từ tiếng Anh</p>
            `;
            isFlipped = true;
        } else {
            renderFrontCard(currentWord);
            isFlipped = false;
        }
    });
}

// Chuyển sang từ tiếp theo vòng lặp tròn
function nextCard() {
    if (wordsData.length === 0) return;
    currentCardIndex = (currentCardIndex + 1) % wordsData.length;
    loadCard();
}

// Đọc giọng máy phát âm chuẩn Anh - Mỹ
function playAudio(word) {
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel(); // Tắt âm đang đọc dở trước đó tránh đè tiếng
        const speech = new SpeechSynthesisUtterance(word);
        speech.lang = 'en-US';
        speech.rate = 0.85;
        window.speechSynthesis.speak(speech);
    }
}