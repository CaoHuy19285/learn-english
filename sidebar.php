<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'user';

// Kết nối database để lấy danh sách thư mục và thông tin user
require_once 'database.php';
$db = new Database();

// === Lấy thông tin user (streak, số từ đã học) cho level-card ===
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];

    // Lấy streak từ bảng users
    $userData = $db->select("SELECT streak, last_study_date FROM users WHERE id = ?", [$user_id]);
    $streak = $userData ? (int)$userData[0]['streak'] : 0;

    // Đếm số từ đã học (có trong user_progress, loại trừ trạng thái 'new' nếu có)
    $learnedCount = $db->select(
        "SELECT COUNT(DISTINCT vocabulary_id) as count 
         FROM user_progress 
         WHERE user_id = ? AND status IN ('learning', 'learned', 'mastered')",
        [$user_id]
    );
    $learnedWords = $learnedCount ? (int)$learnedCount[0]['count'] : 0;

    // Mục tiêu học (ví dụ 100 từ), tính phần trăm
    $target = 100;
    $percent = min(100, round(($learnedWords / $target) * 100));
} else {
    $streak = 0;
    $learnedWords = 0;
    $percent = 0;
}
?>

<aside class="sidebar-left">

    <style>
        /* CSS cho Logo */
        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        .brand-logo:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }
        .brand-logo img {
            height: 55px;
            width: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        .brand-logo:hover img {
            transform: scale(1.05);
        }
        .brand-text {
            font-family: 'Inter', sans-serif;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
    </style>

    <a href="index.php" class="brand-logo">
        <img src="public/images/CARD MOI.png" alt="WordWise Logo">
        <span class="brand-text">WordWise</span>
    </a>

    <!-- ===== PHẦN ADMIN (chỉ hiển thị khi role = admin) ===== -->
    <?php if ($role == 'admin'): ?>
        <div class="menu-divider">QUẢN TRỊ VIÊN</div>

        <a href="admin_type.php" class="nav-item <?= ($currentPage == 'admin_type.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 20h9"></path>
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
            </svg>
            Quản lý Loại Từ
        </a>

        <a href="admin_vocab.php" class="nav-item <?= ($currentPage == 'admin_vocab.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
            </svg>
            Kho Từ Vựng
        </a>
    <?php endif; ?>

    <nav class="nav-menu">
        <!-- ===== DANH MỤC CHÍNH (hiển thị cho cả admin và user) ===== -->
        <div class="menu-divider">DANH MỤC CHÍNH</div>

        <a href="dashboard.php" class="nav-item <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
            Dashboard
        </a>

        <!-- ===== PHẦN CHUNG (Admin + User) ===== -->
        <?php if ($role == 'admin' || $role == 'user'): ?>

            <a href="learn.php" class="nav-item <?= ($currentPage == 'learn.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
                Học tập
            </a>

            <a href="minigame.php" class="nav-item <?= ($currentPage == 'minigame.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="8" width="20" height="12" rx="2" ry="2"/>
                    <circle cx="8" cy="14" r="2"/>
                    <circle cx="16" cy="14" r="2"/>
                    <line x1="12" y1="12" x2="12" y2="12"/>
                    <path d="M6 8V6a2 2 0 0 1 4 0v2"/>
                    <path d="M18 8V6a2 2 0 0 0-4 0v2"/>
                </svg>
                Trò chơi
            </a>

            <a href="notifications.php" class="nav-item <?= ($currentPage == 'notifications.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                Thông báo
            </a>

            <div class="menu-divider">PHƯƠNG PHÁP HỌC TẬP</div>

            <a href="select_topic.php?type=flashcard" class="nav-item <?= ($currentPage == 'flashcard.php' || $currentPage == 'select_topic.php' && $_GET['type'] == 'flashcard') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                </svg>
                Flashcard
            </a>

            <a href="select_topic.php?type=quiz" class="nav-item <?= ($currentPage == 'quiz.php' || $currentPage == 'select_topic.php' && $_GET['type'] == 'quiz') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <circle cx="12" cy="12" r="6"></circle>
                    <circle cx="12" cy="12" r="2"></circle>
                </svg>
                Quiz
            </a>

            <a href="select_topic.php?type=match" class="nav-item <?= ($currentPage == 'match.php' || $currentPage == 'select_topic.php' && $_GET['type'] == 'match') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                Match
            </a>

        <?php endif; ?>


        <!-- ===== CHỈ DÀNH CHO USER (không hiển thị cho admin) ===== -->
        <?php if ($role == 'user'): ?>

            <!-- ===== THƯ MỤC CỦA USER ===== -->
            <div class="menu-divider">THƯ MỤC CỦA BẠN</div>

            <?php
            // Lấy danh sách thư mục của user (nếu đã đăng nhập)
            if (isset($_SESSION['user_id'])) {
                $user_id = (int)$_SESSION['user_id'];
                $folders_sidebar = $db->select(
                    "SELECT id, name FROM folders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
                    [$user_id]
                );
                foreach ($folders_sidebar as $folder): ?>
                    <a href="folder.php?id=<?= $folder['id'] ?>" class="nav-item <?= ($currentPage == 'folder.php' && isset($_GET['id']) && $_GET['id'] == $folder['id']) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                        </svg>
                        <?= htmlspecialchars($folder['name']) ?>
                    </a>
                <?php endforeach;
            }
            ?>

            <!-- Thư mục mới -->
            <a href="folder.php" class="nav-item <?= ($currentPage == 'folder.php' && !isset($_GET['id'])) ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Thư mục mới
            </a>

            <!-- ===== THƯ VIỆN & THẺ GHI NHỚ ===== -->
            <a href="library.php" class="nav-item <?= ($currentPage == 'library.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="18" rx="2"/>
                    <line x1="8" y1="7" x2="16" y2="7"/>
                    <line x1="8" y1="11" x2="16" y2="11"/>
                    <line x1="8" y1="15" x2="12" y2="15"/>
                </svg>
                Thư viện của bạn
            </a>

            <div class="menu-divider">BẮT ĐẦU TẠI ĐÂY</div>

            <a href="card.php" class="nav-item <?= ($currentPage == 'card.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                </svg>
                Thẻ ghi nhớ
            </a>

        <?php endif; ?>

    </nav>

    <div class="sidebar-bottom">

        <!-- Level card (chỉ hiển thị cho user, admin không cần) -->
        <?php if ($role == 'user'): ?>
            <div class="level-card">
                <div class="level-info">
                    <span>Đã học</span>
                    <span><?= $learnedWords ?> từ</span>
                </div>
                <div class="progress-bar-small">
                    <div class="progress-fill-small" style="width:<?= $percent ?>%;"></div>
                </div>
                <div class="streak-info">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    <span class="streak-number"><?= $streak ?></span>
                    <span class="streak-label">ngày liên tiếp</span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php" class="btn-logout">Đăng xuất</a>
        <?php else: ?>
            <a href="login.php" class="btn-logout" style="background:#6366f1;color:white;">Đăng nhập</a>
        <?php endif; ?>

    </div>

</aside>

<?php if (file_exists('notify.php')) include_once 'notify.php'; ?>