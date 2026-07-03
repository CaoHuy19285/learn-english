<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<header class="landing-header">
        <a href="index.php" class="landing-logo" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
    <img src="public/images/CARD MOI.png" alt="WordWise Logo" width="150" height="70" style="object-fit: contain;">
    <span>WordWise</span>
</a>
        <nav class="landing-nav">
    <a href="index.php" class="<?= ($currentPage == 'index.php') ? 'active' : '' ?>">
        Trang chủ
    </a>

    <a href="introduce.php" class="<?= ($currentPage == 'introduce.php') ? 'active' : '' ?>">
        Giới thiệu
    </a>

    <a href="method.php" class="<?= ($currentPage == 'method.php') ? 'active' : '' ?>">
        Phương pháp học
    </a>
</nav>
        <div class="landing-auth">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="<?= ($_SESSION['role'] == 'admin') ? 'admin_vocab.php' : 'learn.php' ?>" class="btn-primary">Vào hệ thống</a>
            <?php else: ?>
                <a href="login.php" class="btn-secondary">Đăng nhập</a>
                <a href="register.php" class="btn-primary">Đăng ký ngay</a>
            <?php endif; ?>
        </div>
    </header>