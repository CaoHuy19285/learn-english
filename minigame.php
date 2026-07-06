<?php 
session_start(); 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WordWise - Trung Tâm Trò Chơi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css"> 

    <style>
        /* === BỐ CỤC CHÍNH === */
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Inter', sans-serif; overflow: hidden; }
        
        .app-layout {
            display: flex;
            width: 100vw;
            height: 100vh;
        }

        .main-content {
            flex-grow: 1;
            padding: 40px 60px;
            overflow-y: auto;
            background: #f8fafc;
        }

        .header-title { margin-bottom: 40px; }
        .header-title h1 {
            font-size: 32px; font-weight: 900; color: #0f172a; margin-bottom: 10px;
            display: flex; align-items: center; gap: 15px;
        }
        
        /* Icon Gamepad trên Header */
        .icon-header {
            width: 35px; height: 35px;
            stroke: #a855f7; fill: rgba(168, 85, 247, 0.1);
        }

        .header-title p { color: #64748b; font-size: 16px; margin: 0; }

        /* === LƯỚI CARD GAME === */
        .game-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        .game-card {
            background: #ffffff; border-radius: 20px; padding: 30px; text-decoration: none;
            display: flex; flex-direction: column; box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border: 2px solid transparent; transition: all 0.3s ease; position: relative; overflow: hidden;
        }

        .game-card:hover {
            transform: translateY(-8px); box-shadow: 0 15px 30px rgba(168, 85, 247, 0.15); border-color: #a855f7;
        }

        /* Phân biệt viền phát sáng đặc biệt cho chế độ VIP (Duo Game) */
        .game-card.vip-mode:hover {
            box-shadow: 0 15px 30px rgba(234, 179, 8, 0.2); border-color: #eab308;
        }

        .game-card::after {
            content: ''; position: absolute; top: -30px; right: -30px; width: 100px; height: 100px;
            border-radius: 50%; background: linear-gradient(135deg, rgba(168, 85, 247, 0.1), rgba(59, 130, 246, 0.1));
            transition: 0.3s;
        }
        .game-card:hover::after { transform: scale(1.5); }

        /* === CHỈNH ICON SVG CODE === */
        .svg-icon-wrap {
            margin-bottom: 20px;
            display: inline-flex;
            transition: 0.3s;
        }
        
        .game-card:hover .svg-icon-wrap {
            transform: scale(1.1); /* Phóng to nhẹ icon khi hover */
        }

        .game-title { font-size: 22px; font-weight: 800; color: #1e293b; margin: 0 0 10px 0; }
        .game-desc { font-size: 14px; color: #64748b; line-height: 1.6; margin: 0 0 25px 0; flex-grow: 1; }

        /* Các loại nhãn trạng thái (Badges) */
        .badge {
            align-self: flex-start; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .badge.vip { background: #fef9c3; color: #ca8a04; } /* Màu vàng Gold cho Duo Game */
        .badge.hot { background: #fee2e2; color: #ef4444; } /* Màu đỏ cho game gõ phím */
        .badge.new { background: #e0e7ff; color: #4f46e5; } 
        .badge.coming { background: #f1f5f9; color: #94a3b8; } 

        .game-card.disabled { filter: grayscale(100%); opacity: 0.7; pointer-events: none; }
    </style>
</head>
<body>

    <div class="app-layout">
        
        <?php include_once 'sidebar.php'; ?>

        <div class="main-content">
            
            <div class="header-title">
                <h1>
                    <svg class="icon-header" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 12h4m-2-2v4m10-4h.01M16 16h.01M21 12a9 9 0 0 1-9 9 9 9 0 0 1-9-9 9 9 0 0 1 18 0Z"/>
                    </svg>
                    Arcade Học Tập
                </h1>
                <p>Khám phá các thử thách từ vựng để nâng cao trình độ tiếng Anh của bạn.</p>
            </div>

            <div class="game-grid">
                
                <a href="game_duo.php" class="game-card vip-mode">
                    <div class="svg-icon-wrap">
                        <svg width="45" height="45" viewBox="0 0 24 24" fill="none" stroke="#eab308" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="filter: drop-shadow(0px 0px 8px rgba(234, 179, 8, 0.6));">
                            <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                            <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                            <path d="M4 22h16"/>
                            <path d="M10 14.66V17c0 .55-.45 1-1 1H4v2h16v-2h-5c-.55 0-1-.45-1-1v-2.34"/>
                            <path d="M12 2a6 6 0 0 1 6 6v5a6 6 0 0 1-6 6 6 6 0 0 1-6-6V8a6 6 0 0 1 6-6z"/>
                        </svg>
                    </div>
                    <h3 class="game-title">WordWise Odyssey</h3>
                    <p class="game-desc">Chế độ cốt lõi! Thử thách toàn diện từ Nghe, Dịch, Điền từ cho đến Phản xạ nói để vượt qua các chặng bài học với 5 mạng tim.</p>
                    <span class="badge vip"> CHẾ ĐỘ VIP</span>
                </a>

                <a href="game_typing.php" class="game-card">
                    <div class="svg-icon-wrap">
                        <svg width="45" height="45" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="filter: drop-shadow(0px 0px 8px rgba(14, 165, 233, 0.5));">
                            <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/>
                            <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/>
                            <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/>
                            <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>
                        </svg>
                    </div>
                    <h3 class="game-title">Cyber Defender</h3>
                    <p class="game-desc">Hoá thân thành vệ binh không gian. Gõ phím thật nhanh và chính xác để tiêu diệt các Lõi Dữ Liệu từ vựng đang rơi xuống.</p>
                    <span class="badge hot">HOT GIẢI TRÍ</span>
                </a>

                <a href="game_memory.php" class="game-card">
                    <div class="svg-icon-wrap">
                        <svg width="45" height="45" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="filter: drop-shadow(0px 0px 8px rgba(168, 85, 247, 0.5));">
                            <rect x="2" y="6" width="14" height="16" rx="2"/>
                            <path d="M22 14V4a2 2 0 0 0-2-2H8"/>
                            <path d="M9 14h0"/>
                        </svg>
                    </div>
                    <h3 class="game-title">Memory Match</h3>
                    <p class="game-desc">Lật thẻ bài và tìm các cặp từ vựng - nghĩa tiếng Việt tương ứng. Thử thách trí nhớ ngắn hạn của bạn.</p>
                    <span class="badge new">VỪA RA MẮT</span>
                </a>

                <a href="#" class="game-card disabled">
                    <div class="svg-icon-wrap">
                        <svg width="45" height="45" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="16 3 21 3 21 8"/>
                            <line x1="4" y1="20" x2="21" y2="3"/>
                            <polyline points="21 16 21 21 16 21"/>
                            <line x1="15" y1="15" x2="21" y2="21"/>
                            <line x1="4" y1="4" x2="9" y2="9"/>
                        </svg>
                    </div>
                    <h3 class="game-title">Word Scramble</h3>
                    <p class="game-desc">Các chữ cái của từ vựng đã bị đảo lộn. Hãy sắp xếp chúng lại thành từ hoàn chỉnh trước khi hết giờ!</p>
                    <span class="badge coming">SẮP RA MẮT</span>
                </a>

            </div>
        </div>
    </div>

</body>
</html>