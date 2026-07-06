<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giới thiệu - WordWise</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
  <style>
    /* Header của trang */
    .page-header { 
        text-align: center; 
        padding: 80px 20px 50px; 
        background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%); 
    }
    .page-header h1 { 
        font-size: 40px; 
        font-weight: 800; 
        color: #1e1b4b; 
        margin-bottom: 18px; 
        letter-spacing: -0.5px;
    }
    .page-header p { 
        color: #475569; 
        max-width: 650px; 
        margin: 0 auto; 
        font-size: 17px; 
        line-height: 1.7; 
        text-align: center; /* Giữ tiêu đề phụ căn giữa cho đẹp */
    }

    /* Container chính */
    .intro-container { 
        max-width: 1000px; 
        margin: 0 auto; 
        padding: 60px 20px; 
    }

   
   /* Phần Câu chuyện / Sứ mệnh */
    .story-section { 
        /* Thử nghiệm với đường dẫn lùi 1 cấp và không dùng phủ gradient trắng để xem ảnh */
        background-image: url('../public/images/Cardmoi_PLT_Trang.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        
        padding: 50px; 
        border-radius: 20px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.03); 
        border: 1px solid #f1f5f9;
        margin-bottom: 60px;
    }
    .story-section h2 { 
        color: #1e1b4b; 
        font-size: 28px; 
        margin-bottom: 25px; 
        font-weight: 800; 
    }
    .story-section p { 
        color: #475569; 
        font-size: 16px; 
        line-height: 1.8; 
        margin-bottom: 16px; 
        text-align: justify; /* Đoạn văn câu chuyện căn lề đều 2 bên */
    }
    .story-section p:last-child {
        margin-bottom: 0;
    }
    .highlight-text {
        color: #4f46e5;
        font-weight: 600;
    }

    /* Phần Giá trị cốt lõi */
    .values-title {
        text-align: center; 
        font-size: 28px; 
        font-weight: 800; 
        color: #1e1b4b; 
        margin-bottom: 40px;
    }
    .values-grid { 
        display: grid; 
        grid-template-columns: repeat(2, 1fr); 
        gap: 25px; 
    }
    .value-card { 
        background: #f8fafc; 
        padding: 40px 35px; 
        border-radius: 16px; 
        border: 1px solid #e2e8f0; 
        transition: all 0.3s ease; 
        position: relative; 
        overflow: hidden; 
    }
    .value-card:hover { 
        transform: translateY(-5px); 
        background: #ffffff;
        box-shadow: 0 10px 25px rgba(0,0,0,0.04);
        border-color: #cbd5e1;
    }
    
    /* Chữ số in chìm thay thế cho Icon */
    .value-number { 
        font-size: 80px; 
        font-weight: 900; 
        color: #e0e7ff; 
        position: absolute; 
        top: -10px; 
        right: 15px; 
        z-index: 0; 
        line-height: 1; 
        opacity: 0.6;
        transition: all 0.3s ease;
    }
    .value-card:hover .value-number {
        color: #c7d2fe;
        transform: scale(1.1);
    }

    .value-card h3 { 
        color: #1e1b4b; 
        font-size: 20px; 
        margin-bottom: 15px; 
        font-weight: 700; 
        position: relative; 
        z-index: 1; 
    }

    /* ĐÃ CẬP NHẬT: Căn lề đều 2 bên cho mô tả trong các ô giá trị (bao gồm đoạn văn xử lý hình ảnh...) */
    .value-card p { 
        color: #475569; 
        font-size: 15px; 
        line-height: 1.7; 
        position: relative; 
        z-index: 1; 
        margin: 0;
        text-align: justify; /* Căn lề đều 2 bên (Justify) */
    }

    /* Responsive */
    @media (max-width: 768px) {
        .values-grid { grid-template-columns: 1fr; }
        .story-section { padding: 30px; }
    }
</style>
</head>
<body class="landing-body">
    
    <?php include_once "header.php" ?>

    <div class="page-header">
        <h1>Giới thiệu về <span class="text-gradient">WordWise</span></h1>
        <p>Hành trình thay đổi cách chúng ta tiếp cận, ghi nhớ và làm chủ ngôn ngữ mới thông qua sự kết hợp giữa khoa học và công nghệ.</p>
    </div>

    <main class="intro-container">
        
        <section class="story-section">
            <h2>Câu chuyện của chúng tôi</h2>
            <p>Học ngoại ngữ từ lâu đã gắn liền với những cuốn vở chép tay dày cộp và những buổi nhồi nhét từ vựng khô khan. Hầu hết người học đều gặp phải một vấn đề chung: <span class="highlight-text">Học rất nhanh nhưng quên cũng rất mau</span>. Nhận thấy sự lãng phí thời gian và công sức đó, WordWise đã ra đời.</p>
            <p>Chúng tôi không xây dựng một cuốn từ điển điện tử thông thường. WordWise là một hệ sinh thái học tập được thiết kế dựa trên các nghiên cứu về tâm lý học hành vi và khả năng ghi nhớ của não bộ. Bằng cách số hóa phương pháp học Flashcard (thẻ ghi nhớ) và kết hợp với dữ liệu trực quan sinh động, chúng tôi biến những từ vựng trừu tượng thành những hình ảnh và định nghĩa có tính liên kết cao.</p>
            <p>Sứ mệnh của chúng tôi là tạo ra một môi trường học tập nơi người dùng không còn cảm thấy áp lực. Việc mở WordWise mỗi ngày sẽ giống như một thói quen giải trí nhẹ nhàng, nhưng lại mang đến hiệu quả ghi nhớ ngôn ngữ sâu sắc và bền vững.</p>
        </section>

        <h2 class="values-title">Giá trị cốt lõi của nền tảng</h2>
        <div class="values-grid">
            
            <div class="value-card">
                <div class="value-number">01</div>
                <h3>Phương pháp học thuận tự nhiên</h3>
                <p>Não bộ con người xử lý hình ảnh nhanh hơn 60.000 lần so với văn bản. WordWise loại bỏ việc học vẹt bằng cách đính kèm hình ảnh minh họa, phiên âm IPA chuẩn và ví dụ thực tế cho từng từ vựng, giúp bạn ghi nhớ theo ngữ cảnh một cách tự nhiên nhất.</p>
            </div>

            <div class="value-card">
                <div class="value-number">02</div>
                <h3>Sự cá nhân hóa tối đa</h3>
                <p>Mỗi người có một mục tiêu học tập khác nhau. Hệ thống cho phép bạn tự do lựa chọn các chủ đề (Topic) từ cơ bản đến chuyên ngành (IT, Kinh doanh, Nghệ thuật...). Bạn học những gì bạn thực sự cần, không học lan man.</p>
            </div>

            <div class="value-card">
                <div class="value-number">03</div>
                <h3>Thiết kế tối giản & Tập trung</h3>
                <p>Chúng tôi tin rằng sự phức tạp là kẻ thù của việc học. Giao diện của WordWise được lược bỏ mọi chi tiết thừa, mang ngôn ngữ thiết kế phẳng (Flat Design) kết hợp với không gian trắng (Whitespace) giúp đôi mắt của bạn luôn cảm thấy dễ chịu dù học trong thời gian dài.</p>
            </div>

            <div class="value-card">
                <div class="value-number">04</div>
                <h3>Game hóa (Gamification) quá trình học</h3>
                <p>Cảm giác nhàm chán sẽ bị đánh bay bởi hệ thống điểm thưởng XP và chuỗi ngày học liên tục (Streak). Biến quá trình trau dồi từ vựng thành những cột mốc thú vị, kích thích não bộ tiết ra Dopamine để bạn luôn khao khát quay lại học vào ngày hôm sau.</p>
            </div>

        </div>
    </main>

    <?php include 'footer.php'; ?>
    
</body>
</html>