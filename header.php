<?php
$currentPage = basename($_SERVER['PHP_SELF']);

// Lấy thông tin cá nhân từ session (nếu có, không thì dùng mặc định)
$user_name = isset($_SESSION['full_name']) && !empty($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Người dùng';
$user_avatar = isset($_SESSION['avatar']) && !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'default_avatar.png';

// Đường dẫn thư mục chứa ảnh đại diện 
$avatar_path = "public/images/avatars/" . $user_avatar; 
if (!file_exists($avatar_path) || empty($user_avatar)) {
    // Nếu là link web (như dicebear) thì dùng thẳng
    if (strpos($user_avatar, 'http') !== false) {
        $avatar_path = $user_avatar;
    } else {
        $avatar_path = "https://cdn-icons-png.flaticon.com/512/3177/3177440.png";
    }
}
?>

<style>
    /* Giao diện Header nền trắng sáng */
    .landing-header {
        background-color: #ffffff; /* Trả lại nền trắng */
        padding: 10px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    /* Style Logo */
    .landing-logo {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        transition: transform 0.3s ease;
    }
    .landing-logo:hover {
        transform: translateY(-2px);
    }
    .landing-logo img {
        height: 55px;
        width: auto;
        object-fit: contain;
    }
    .landing-logo span {
        font-family: 'Inter', sans-serif;
        font-size: 24px;
        font-weight: 800;
        color: #1e1b4b; /* Đổi chữ WordWise thành màu xanh đen để nổi trên nền trắng */
    }

    /* Menu Điều hướng */
    .landing-nav a {
        color: #475569; /* Màu xám đậm */
        text-decoration: none;
        margin: 0 15px;
        font-weight: 500;
        transition: color 0.2s;
    }
    .landing-nav a:hover, .landing-nav a.active {
        color: #4f46e5; /* Đổi màu xanh tím khi hover */
    }

    /* Khung Auth điều hướng bên phải */
    .landing-auth {
        display: flex;
        align-items: center;
        gap: 15px;
        position: relative;
    }

    .user-profile-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .avatar-toggle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid #4f46e5;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .avatar-toggle:hover {
        transform: scale(1.05);
        border-color: #db2777;
    }

    /* Menu thả xuống */
    .user-dropdown-menu {
        position: absolute;
        top: 55px;
        right: 0;
        width: 250px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        display: none; 
        flex-direction: column;
        z-index: 1000;
        overflow: hidden;
        animation: fadeInDropdown 0.2s ease-out;
    }
    
    .user-dropdown-menu.show {
        display: flex; 
    }

    @keyframes fadeInDropdown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Khu vực hiển thị thông tin User sau hình ảnh */
    .dropdown-user-info {
        padding: 20px 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .dropdown-user-info img {
        width: 65px;
        height: 65px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 10px;
        border: 2px solid #e2e8f0;
    }
    .dropdown-user-info .user-greeting {
        font-size: 15px;
        color: #475569;
        margin-bottom: 3px;
    }
    .dropdown-user-info .user-name {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
    }

    /* Các liên kết hành động trong Menu Dropdown */
    .dropdown-links {
        display: flex;
        flex-direction: column;
        padding: 6px 0;
    }
    .dropdown-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        color: #334155;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: background 0.2s, color 0.2s;
    }
    .dropdown-item:hover {
        background: #f1f5f9;
        color: #4f46e5;
    }

    /* Style cấu trúc khối icon SVG text */
    .dropdown-item-text {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .dropdown-item svg {
        color: #64748b;
        transition: color 0.2s;
    }
    .dropdown-item:hover svg {
        color: #4f46e5;
    }

    /* Nút Đăng xuất */
    .dropdown-item.logout-btn {
        color: #dc2626;
        border-top: 1px solid #f1f5f9;
        margin-top: 4px;
    }
    .dropdown-item.logout-btn:hover {
        background: #fef2f2;
        color: #b91c1c;
    }
    .dropdown-item.logout-btn:hover svg {
        color: #dc2626;
    }
</style>

<header class="landing-header">
    <a href="index.php" class="landing-logo">
        <img src="public/images/CARD MOI.png" alt="WordWise Logo">
        <span>WordWise</span>
    </a>

    <nav class="landing-nav">
        <a href="index.php" class="<?= ($currentPage == 'index.php') ? 'active' : '' ?>">Trang chủ</a>
        <a href="introduce.php" class="<?= ($currentPage == 'introduce.php') ? 'active' : '' ?>">Giới thiệu</a>
        <a href="method.php" class="<?= ($currentPage == 'method.php') ? 'active' : '' ?>">Phương pháp học</a>
    </nav>

    <div class="landing-auth">
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="<?= ($_SESSION['role'] == 'admin') ? 'admin_vocab.php' : 'learn.php' ?>" class="btn-primary" style="background:#4f46e5; color:white; padding: 8px 16px; border-radius: 8px; text-decoration:none; font-weight:600;">Vào hệ thống</a>
            
            <div class="user-profile-wrapper">
                <img src="<?= $avatar_path ?>" alt="Avatar" class="avatar-toggle" id="avatarToggleBtn">
                
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <div class="dropdown-user-info">
                        <img src="<?= $avatar_path ?>" alt="User Big Avatar">
                        <div class="user-greeting">Chào,</div>
                        <div class="user-name"><?= htmlspecialchars($user_name) ?></div>
                    </div>
                    
                    <div class="dropdown-links">
                        <a href="profile.php" class="dropdown-item">
                            <span class="dropdown-item-text">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                Cài đặt tài khoản
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                        
                        <a href="logout.php" class="dropdown-item logout-btn" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất tài khoản hay không?')">
                            <span class="dropdown-item-text">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                Đăng xuất tài khoản
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php" class="btn-secondary">Đăng nhập</a>
            <a href="register.php" class="btn-primary">Đăng ký ngay</a>
        <?php endif; ?>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const avatarToggle = document.getElementById('avatarToggleBtn');
    const dropdownMenu = document.getElementById('userDropdownMenu');

    if (avatarToggle && dropdownMenu) {
        avatarToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            if (!dropdownMenu.contains(e.target) && e.target !== avatarToggle) {
                dropdownMenu.classList.remove('show');
            }
        });
    }
});
</script>