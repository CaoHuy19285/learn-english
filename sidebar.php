<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'user';
?>

<aside class="sidebar-left">

    <!-- Logo -->
    <a href="index.php" class="logo" style="text-decoration:none;color:inherit;">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#818cf8" stroke-width="2.5">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
        </svg>
        <span>WordWise</span>
    </a>

    <nav class="nav-menu">

        <!-- Trang chủ -->
        <a href="index.php" class="nav-item <?= ($currentPage == 'index.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            </svg>
            Trang chủ
        </a>

        <?php if ($role == 'user'): ?>

            <!-- Học tập -->
            <a href="learn.php" class="nav-item <?= ($currentPage == 'learn.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
                Học tập
            </a>

            <!-- Thư viện -->
            <a href="library.php" class="nav-item <?= ($currentPage == 'library.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18"></path>
                    <path d="M3 12h18"></path>
                    <path d="M3 18h18"></path>
                </svg>
                Thư viện của bạn
            </a>

            <!-- Nhóm học -->
            <a href="group.php" class="nav-item <?= ($currentPage == 'group.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Nhóm học
            </a>

            <!-- Thông báo -->
            <a href="notification.php" class="nav-item <?= ($currentPage == 'notification.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                Thông báo
            </a>

            <div class="menu-divider">Thư mục của bạn</div>

            <!-- Flashcard -->
            <a href="flashcard.php" class="nav-item <?= ($currentPage == 'flashcard.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                </svg>
                Flashcard
            </a>

            <a href="quiz.php" class="nav-item <?= ($currentPage == 'quiz.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <circle cx="12" cy="12" r="6"></circle>
                    <circle cx="12" cy="12" r="2"></circle>
                </svg>
                Quiz
            </a>

            <a href="match.php" class="nav-item <?= ($currentPage == 'match.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                Match
            </a>
            <!-- Thư mục -->
            <a href="folder.php" class="nav-item <?= ($currentPage == 'folder.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Thư mục mới
            </a>

            <div class="menu-divider">Bắt đầu tại đây</div>

            <a href="card.php" class="nav-item <?= ($currentPage == 'card.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                </svg>
                Thẻ ghi nhớ
            </a>

            <a href="solution.php" class="nav-item <?= ($currentPage == 'solution.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                Lời giải chuyên sâu
            </a>

        <?php endif; ?>

        <?php if ($role == 'admin'): ?>

            <div class="menu-divider">Quản trị viên</div>

            <!-- Dashboard -->
            <a href="dashboard.php" class="nav-item <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <line x1="9" y1="21" x2="9" y2="9"></line>
                </svg>
                Dashboard
            </a>

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

    </nav>

    <div class="sidebar-bottom">

        <?php if ($role == 'user'): ?>

            <div class="level-card">

                <div class="level-info">
                    <span>Đã học</span>
                    <span><?= $learnedWords ?? 0 ?> từ</span>
                </div>

                <div class="progress-bar-small">
                    <div class="progress-fill-small" style="width:<?= $percent ?? 0 ?>%;"></div>
                </div>

                <div class="streak-info">
                    🔥 <?= $streak ?? 0 ?> ngày liên tiếp
                </div>
            </div>

        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="logout.php" class="btn-logout">
                Đăng xuất
            </a>

        <?php else: ?>

            <a href="login.php" class="btn-logout" style="background:#6366f1;color:white;">
                Đăng nhập
            </a>

        <?php endif; ?>

    </div>

</aside>