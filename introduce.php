<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giới thiệu - WordWise</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .grid-2-col { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; max-width: 1000px; margin: 0 auto; padding: 0 20px; }
        .feature-box { background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; transition: transform 0.3s ease; }
        .feature-box:hover { transform: translateY(-5px); }
        .feature-box h3 { margin: 15px 0 10px; color: #1e1b4b; font-size: 18px; }
        .feature-box p { color: #64748b; font-size: 14px; line-height: 1.6; }
        .page-header { text-align: center; padding: 80px 20px 40px; background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%); }
        .page-header h1 { font-size: 36px; font-weight: 800; color: #1e1b4b; margin-bottom: 15px; }
        .page-header p { color: #64748b; max-width: 600px; margin: 0 auto; font-size: 16px; line-height: 1.6; }
    </style>
</head>
<body class="landing-body">
      <?php include_once "header.php" ?>


    <div class="page-header">
        <h1>Về WordWise</h1>
        <p>Sứ mệnh của chúng tôi là giúp việc học ngôn ngữ trở nên dễ dàng, thú vị và mang lại hiệu quả lâu dài thông qua sức mạnh của công nghệ.</p>
    </div>

    <section style="padding: 60px 0; background: #f8fafc;">
        <h2 style="text-align: center; font-size: 28px; font-weight: 800; color: #1e1b4b; margin-bottom: 40px;">Lợi Ích Khi Đồng Hành Cùng WordWise</h2>
        <div class="grid-2-col">
            <div class="feature-box">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                <h3>Tiết kiệm thời gian, tối ưu kết quả</h3>
                <p>Không cần học dồn, thuật toán AI sẽ tự động phân phối các bài tập ngắn gọn chỉ 15 phút mỗi ngày.</p>
            </div>
            <div class="feature-box">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <h3>Quản lý tiến độ dễ dàng</h3>
                <p>Theo dõi xem bạn đã ghi nhớ từ vựng đến mức độ nào thông qua thanh quá trình trực quan.</p>
            </div>
            <div class="feature-box">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <h3>Chủ động chọn chủ đề</h3>
                <p>Từ IT & Tech, Business đến Travel. Hãy tự do lựa chọn nhóm từ vựng phù hợp với mục tiêu của bạn.</p>
            </div>
            <div class="feature-box">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M23 6l-9.5 9.5-5-5L1 18"></path><polyline points="16 6 23 6 23 13"></polyline></svg>
                <h3>Gia tăng động lực liên tục</h3>
                <p>Thu thập XP, nâng cấp Level và duy trì ngọn lửa Streak giúp biến việc học thành một trò chơi thú vị.</p>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>