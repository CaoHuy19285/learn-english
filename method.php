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
    /* Hiệu ứng chữ Gradient */
    .text-gradient {
        background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 800;
    }

    /* Header của trang */
    .page-header { 
        text-align: center; 
        padding: 80px 20px 50px; 
        background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%); 
    }
    .page-header h1 { 
        font-size: 38px; 
        font-weight: 800; 
        color: #1e1b4b; 
        margin-bottom: 18px; 
    }
    .page-header p { 
        color: #475569; 
        max-width: 650px; 
        margin: 0 auto; 
        font-size: 16px; 
        line-height: 1.7; 
        text-align: center; /* Tiêu đề phụ trên header giữ căn giữa cho đẹp */
    }

    /* Vùng nội dung các phương pháp học dạng so le */
    .method-container {
        max-width: 1050px;
        margin: 0 auto;
        padding: 80px 20px;
    }

    .method-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 60px;
        margin-bottom: 100px;
    }

    /* Đảo ngược vị trí đối với dòng chẵn để tạo hiệu ứng so le */
    .method-row:nth-child(even) {
        flex-direction: row-reverse;
    }

    /* Khối chữ giới thiệu phương pháp */
    .method-content {
        flex: 1;
    }
    .method-tag {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #4f46e5;
        letter-spacing: 1px;
        margin-bottom: 10px;
        display: inline-block;
    }
    .method-content h2 {
        font-size: 26px;
        font-weight: 800;
        color: #1e1b4b;
        margin-bottom: 18px;
        line-height: 1.3;
    }
    
    /* ĐÃ CẬP NHẬT: Căn lề đều 2 bên cho tất cả các đoạn văn mô tả */
    .method-content p {
        color: #475569;
        font-size: 14.5px;
        line-height: 1.7;
        margin: 0;
        text-align: justify; /* Căn lề đều 2 bên (Justify) */
    }

    /* Khối hiển thị giao diện minh họa (Thay thế hoàn toàn cho Icon) */
    .method-visual {
        flex: 1;
        background: #ffffff;
        border-radius: 20px;
        padding: 35px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 240px;
    }

    /* Thiết kế các component mô phỏng bên trong phần Visual */
    /* 1. Thẻ Flashcard học từ */
    .simulated-card {
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        background: #f8fafc;
    }
    .simulated-card .word { font-size: 24px; font-weight: 800; color: #4f46e5; }
    .simulated-card .ipa { font-size: 14px; color: #64748b; margin: 4px 0 12px 0; }
    .simulated-card .desc { font-size: 14px; color: #334155; font-style: italic; }

    /* 2. Giao diện nối từ */
    .simulated-match {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .match-item {
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        text-align: center;
        font-weight: 500;
        background: #fff;
    }
    .match-item.active-left { border-color: #06b6d4; background: #ecfeff; color: #0891b2; }
    .match-item.active-right { border-color: #06b6d4; background: #ecfeff; color: #0891b2; }

    /* 3. Giao diện trò chơi trắc nghiệm */
    .simulated-quiz {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .quiz-question { font-size: 14px; font-weight: 700; color: #1e1b4b; margin-bottom: 6px; }
    .quiz-option { padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; color: #475569; background: #fff;}
    .quiz-option.correct { border-color: #10b981; background: #ecfdf5; color: #059669; font-weight: 600; }

    /* 4. Giao diện bảng thông số AI */
    .simulated-ai {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
        border-radius: 12px;
        padding: 20px;
        color: #ffffff;
    }
    .ai-status { font-size: 12px; color: #a5b4fc; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; }
    .ai-title { font-size: 16px; font-weight: 700; margin: 6px 0 12px 0; color: #fff; }
    .ai-progress-bar { height: 6px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; }
    .ai-progress-fill { height: 100%; width: 78%; background: #06b6d4; border-radius: 10px; }
    .ai-footer-text { font-size: 11px; color: #cbd5e1; margin-top: 10px; text-align: right; }

    /* Responsive cho thiết bị di động */
    @media (max-width: 768px) {
        .method-row, .method-row:nth-child(even) {
            flex-direction: column;
            gap: 30px;
            margin-bottom: 60px;
        }
        .page-header h1 { font-size: 30px; }
    }
</style>
</head>
<body class="landing-body">
    
    <?php include_once "header.php" ?>

    <div class="page-header">
        <h1>Các Phương Pháp Học Tập</h1>
        <p>Đến với <span class="text-gradient">WordWise</span>, bạn sẽ được trải nghiệm hệ thống phương pháp học tập toàn diện được tối ưu hóa theo chu trình nạp từ, phản xạ trò chơi và ghi nhớ bền vững bằng công nghệ.</p>
    </div>

    <main class="method-container">
        
        <div class="method-row">
            <div class="method-content">
                <span class="method-tag">Phương pháp 1: Tiếp thu</span>
                <h2>Học từ vựng trực quan đa chiều</h2>
                <p>Loại bỏ cách học chép phạt truyền thống. Tại đây, bạn sẽ được tiếp cận từ vựng thông qua hệ thống thẻ thông minh tích hợp đầy đủ âm thanh chuẩn quốc tế, ký tự phiên âm IPA chuẩn xác, giải nghĩa tinh gọn và các ví dụ thực tế giúp hiểu sâu bản chất ngữ cảnh của từ.</p>
            </div>
            <div class="method-visual">
                <div class="simulated-card">
                    <div class="word">Perspicacious</div>
                    <div class="ipa">/ˌpɜː.spɪˈkeɪ.ʃəs/</div>
                    <div class="desc">"Sáng suốt, minh mẫn, có óc thấu suốt."</div>
                </div>
            </div>
        </div>

        <div class="method-row">
            <div class="method-content">
                <span class="method-tag"> Phương pháp 2: Kết nối</span>
                <h2>Thử thách đấu trí nối từ nhanh</h2>
                <p>Kích hoạt vùng liên kết dữ liệu trong não bộ bằng cơ chế nối từ (Match). Bạn sẽ phải quan sát nhanh hai cột chứa từ vựng và ngữ nghĩa đảo lộn, tìm cách ghép cặp chính xác chúng lại với nhau dưới áp lực thời gian để tăng tốc độ hiểu nghĩa từ.</p>
            </div>
            <div class="method-visual">
                <div class="simulated-match">
                    <div class="match-item active-left">Abandon</div>
                    <div class="match-item">Thành tựu</div>
                    <div class="match-item">Accomplish</div>
                    <div class="match-item active-right">Từ bỏ, ruồng rẫy</div>
                </div>
            </div>
        </div>

        <div class="method-row">
            <div class="method-content">
                <span class="method-tag"> Phương pháp 3: Phản xạ</span>
                <h2>Chơi trò chơi trắc nghiệm vui nhộn</h2>
                <p>Biến việc ôn bài thành những ván game thú vị để tích lũy điểm kinh nghiệm (XP) và duy trì chuỗi học tập (Streak). Các bộ câu hỏi trắc nghiệm (Quiz) thông minh được thiết kế sinh động giúp bạn kiểm tra tức thì khả năng phản xạ mà không hề gây áp lực.</p>
            </div>
            <div class="method-visual">
                <div class="simulated-quiz">
                    <div class="quiz-question">Đâu là nghĩa chính xác của từ "Benevolent"?</div>
                    <div class="quiz-option">Ích kỷ, nhỏ nhen</div>
                    <div class="quiz-option correct">Nhân từ, rộng lượng ✓</div>
                    <div class="quiz-option">Cẩn trọng, dè dặt</div>
                </div>
            </div>
        </div>

        <div class="method-row">
            <div class="method-content">
                <span class="method-tag">Phương pháp 4: Ghi nhớ lâu dài</span>
                <h2>Trí tuệ nhân tạo & Lặp lại ngắt quãng</h2>
                <p>Hệ thống AI cốt lõi chạy ngầm sẽ đo lường độ quên của bạn dựa trên thuật toán Spaced Repetition danh tiếng. Thay vì ôn tập tràn lan, AI sẽ tự động phân tích và chỉ đẩy những từ vựng bạn chuẩn bị quên lên ôn tập lại vào đúng "thời điểm vàng", đảm bảo ghi nhớ trọn đời.</p>
            </div>
            <div class="method-visual">
                <div class="simulated-ai">
                    <div class="ai-status">WordWise AI Engine</div>
                    <div class="ai-title">Thuật toán Spaced Repetition đang chạy...</div>
                    <div class="ai-progress-bar">
                        <div class="ai-progress-fill"></div>
                    </div>
                    <div class="ai-footer-text">Đã phân tích 78% tần suất ghi nhớ từ của bạn</div>
                </div>
            </div>
        </div>

    </main>

    <?php include 'footer.php'; ?>
    
</body>
</html>