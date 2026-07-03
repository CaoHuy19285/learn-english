<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phương pháp học - WordWise</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .grid-3-col { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; max-width: 1100px; margin: 0 auto; padding: 0 20px; }
        .feature-box { background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; transition: transform 0.3s ease; border: 1px solid #e2e8f0; }
        .feature-box:hover { transform: translateY(-5px); border-color: #6366f1; }
        .feature-box h3 { margin: 15px 0 10px; color: #1e1b4b; font-size: 18px; }
        .feature-box p { color: #64748b; font-size: 14px; line-height: 1.6; }
        .page-header { text-align: center; padding: 80px 20px 40px; background: linear-gradient(135deg, #f8fafc 0%, #d1fae5 100%); }
        .page-header h1 { font-size: 36px; font-weight: 800; color: #1e1b4b; margin-bottom: 15px; }
        .page-header p { color: #64748b; max-width: 600px; margin: 0 auto; font-size: 16px; line-height: 1.6; }
    </style>
</head>
<body class="landing-body">
      <?php include_once "header.php" ?>

    <div class="page-header">
        <h1>Các Phương Pháp Học Tập</h1>
        <p>Được thiết kế dựa trên khoa học thần kinh về trí nhớ, WordWise mang đến cho bạn các chế độ học tập đa dạng để khắc sâu từ vựng.</p>
    </div>

    <section style="padding: 60px 0;">
        <div class="grid-3-col">
            <div class="feature-box">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line></svg>
                <h3>Flashcards (Thẻ lật)</h3>
                <p>Cách kinh điển và hiệu quả nhất. Lật thẻ để xem mặt chữ, IPA, hình ảnh minh họa và ý nghĩa. Phương pháp kích thích trực giác đỉnh cao.</p>
            </div>
            <div class="feature-box">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
                <h3>Quiz (Trắc nghiệm)</h3>
                <p>Kiểm tra khả năng phản xạ với các dạng câu hỏi chọn 4 đáp án đúng, hoặc điền từ còn thiếu vào câu ví dụ cụ thể.</p>
            </div>
            <div class="feature-box">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                <h3>Match (Nối từ)</h3>
                <p>Thử thách tốc độ tay và mắt bằng cách ghép nối định nghĩa với từ vựng chuẩn xác trong thời gian ngắn nhất.</p>
            </div>
            <div class="feature-box">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                <h3>Spaced Repetition</h3>
                <p>Lặp lại ngắt quãng. Thuật toán tự động tính toán thời điểm bạn sắp quên từ để nhắc lại vào "thời điểm vàng".</p>
            </div>
            <div class="feature-box">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
                <h3>Luyện phát âm</h3>
                <p>Tích hợp AI đọc từ vựng chuẩn giọng bản xứ, giúp bạn không chỉ nhớ mặt chữ mà còn nghe - nói chuẩn xác.</p>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>