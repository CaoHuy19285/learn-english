<?php
require_once 'config.php'; // Đảm bảo cấu hình DB
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// ==========================================
// 1. XỬ LÝ LƯU DỮ LIỆU ĐẶT TRƯỚC TIÊN
// Để SESSION cập nhật kịp thời chuyển sang Header
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update_queries = [];
    $params = [];

    // Xử lý Ảnh đại diện (File tải lên)
    if (isset($_FILES['custom_avatar']) && $_FILES['custom_avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['custom_avatar']['tmp_name'];
        $fileName = $_FILES['custom_avatar']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = 'public/images/avatars/';
        
        if (!is_dir($uploadFileDir)) { mkdir($uploadFileDir, 0755, true); }
        
        if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
            $update_queries[] = "avatar = ?";
            $params[] = $newFileName;
            $_SESSION['avatar'] = $newFileName; // Cập nhật Session
        }
    } elseif (!empty($_POST['avatar_url'])) {
        // Xử lý Ảnh con vật có sẵn
        $update_queries[] = "avatar = ?";
        $params[] = trim($_POST['avatar_url']);
        $_SESSION['avatar'] = trim($_POST['avatar_url']); // Cập nhật Session
    }

    // Xử lý Tên
    if (isset($_POST['full_name'])) {
        $update_queries[] = "full_name = ?";
        $params[] = trim($_POST['full_name']);
        $_SESSION['full_name'] = trim($_POST['full_name']); // Cập nhật Session
    }
    
    // Xử lý Tài khoản
    if (isset($_POST['username'])) {
        $update_queries[] = "username = ?";
        $params[] = trim($_POST['username']);
        $_SESSION['username'] = trim($_POST['username']); 
    }
    
    if (isset($_POST['birth_year'])) {
        $update_queries[] = "birth_year = ?";
        $params[] = intval($_POST['birth_year']);
    }
    
    if (isset($_POST['gender'])) {
        $update_queries[] = "gender = ?";
        $params[] = trim($_POST['gender']);
    }

    // Chạy câu lệnh UPDATE gom chung
    if (!empty($update_queries)) {
        $params[] = $user_id;
        $sql = "UPDATE users SET " . implode(", ", $update_queries) . " WHERE id = ?";
        $db->execute($sql, $params);
        $msg = "Cài đặt tài khoản thành công!";
    }
}

// ==========================================
// 2. LẤY THÔNG TIN ĐÃ CẬP NHẬT TỪ DATABASE
// ==========================================
$userResult = $db->select("SELECT * FROM users WHERE id = ?", [$user_id]);
$user = (count($userResult) > 0) ? $userResult[0] : die("Lỗi dữ liệu.");

// Danh sách 18 hình đại diện
$animal_avatars = [
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Rabbit&backgroundColor=c0aede",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Cat&backgroundColor=ffdfbf",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Dog&backgroundColor=b6e3f4",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Bird&backgroundColor=f1f4dc",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Panda&backgroundColor=ffd5dc",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Bear&backgroundColor=d1d4f9",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Lion&backgroundColor=ffdfbf",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Tiger&backgroundColor=c0aede",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Fox&backgroundColor=b6e3f4",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Wolf&backgroundColor=f1f4dc",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Monkey&backgroundColor=ffd5dc",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Koala&backgroundColor=d1d4f9",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Frog&backgroundColor=c0aede",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Turtle&backgroundColor=ffdfbf",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Penguin&backgroundColor=b6e3f4",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Owl&backgroundColor=f1f4dc",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Mouse&backgroundColor=ffd5dc",
    "https://api.dicebear.com/7.x/fun-emoji/svg?seed=Hamster&backgroundColor=d1d4f9"
];

$current_avatar = $user['avatar'] ?? '';
if (!empty($current_avatar)) {
    $avatar_display = (strpos($current_avatar, 'http') === false) ? "public/images/avatars/" . $current_avatar : $current_avatar;
} else {
    $avatar_display = $animal_avatars[0];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cài đặt tài khoản - WordWise</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css"> <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; margin: 0; padding: 0; color: #1e293b; }
        
        /* LAYOUT CHÍNH */
        .profile-layout { display: flex; justify-content: center; padding: 50px 20px; }
        .profile-container { width: 100%; max-width: 700px; }
        .page-header-title { font-size: 15px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 20px; }
        
        .alert-toast { background: #dcfce7; color: #15803d; padding: 14px; border-radius: 12px; font-weight: 600; font-size: 14px; text-align: center; margin-bottom: 25px; border: 1px solid #bbf7d0; }
        
        .profile-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 35px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        
        /* KHU VỰC AVATAR */
        .avatar-box-section { border-bottom: 1px solid #f1f5f9; padding-bottom: 30px; margin-bottom: 10px; }
        .avatar-title-label { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 15px; }
        .avatar-flex-container { display: flex; gap: 30px; align-items: center; }
        .main-avatar-view { width: 110px; height: 110px; border-radius: 50%; overflow: hidden; border: 3px solid #e2e8f0; flex-shrink: 0; }
        .main-avatar-view img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-selection-grid { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .avatar-thumb { width: 44px; height: 44px; border-radius: 50%; cursor: pointer; transition: all 0.2s; border: 2px solid transparent; }
        .avatar-thumb:hover { transform: scale(1.1); }
        .avatar-thumb.active { border-color: #4f46e5; transform: scale(1.1); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15); }
        .upload-trigger-btn { width: 44px; height: 44px; border-radius: 50%; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #64748b; cursor: pointer; background: #f8fafc; }
        .upload-trigger-btn:hover { border-color: #4f46e5; color: #4f46e5; }

        /* KHU VỰC FORM HÀNG NGANG */
        .info-data-row { display: flex; justify-content: space-between; align-items: center; padding: 24px 0; border-bottom: 1px solid #f1f5f9; min-height: 45px; }
        .info-left-block { display: flex; flex-direction: column; gap: 6px; flex: 1; }
        .info-field-label { font-size: 15px; font-weight: 700; color: #0f172a; }
        .info-field-value { font-size: 14px; color: #64748b; }
        
        .edit-input-wrapper { display: none; align-items: center; width: 100%; justify-content: space-between; }
        .edit-input-wrapper.active { display: flex; }
        .view-mode-wrapper { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .view-mode-wrapper.hidden { display: none; }

        .custom-input-field { width: 100%; max-width: 350px; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; }
        .custom-input-field:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        
        /* GENDER RADIO */
        .gender-radio-group { display: flex; gap: 20px; align-items: center; margin-top: 5px; }
        .radio-item-label { display: flex; align-items: center; gap: 6px; font-size: 14px; color: #334155; cursor: pointer; }
        .radio-item-label input[type="radio"] { width: 18px; height: 18px; accent-color: #4f46e5; cursor: pointer; }

        /* NÚT BẤM */
        .action-text-btn { font-size: 14px; font-weight: 600; color: #4f46e5; background: transparent; border: none; cursor: pointer; padding: 6px 12px; border-radius: 6px; }
        .action-text-btn:hover { background: #eef2ff; }
        .btn-cancel { color: #64748b; }
        .btn-cancel:hover { background: #f1f5f9; }
        
        /* FOOTER LƯU & LÀM LẠI */
        .form-footer-actions { display: flex; justify-content: flex-end; gap: 15px; margin-top: 35px; padding-top: 25px; border-top: 1px solid #f1f5f9; }
        .btn-reset { padding: 12px 24px; font-size: 15px; font-weight: 600; color: #475569; background: #f1f5f9; border: none; border-radius: 10px; cursor: pointer; transition: 0.2s; }
        .btn-reset:hover { background: #e2e8f0; }
        .btn-submit { padding: 12px 30px; font-size: 15px; font-weight: 600; color: white; background: #4f46e5; border: none; border-radius: 10px; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2); }
        .btn-submit:hover { background: #4338ca; transform: translateY(-1px); }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="profile-layout">
        <div class="profile-container">
            <div class="page-header-title">Cài đặt tài khoản</div>
            
            <?php if(!empty($msg)): ?>
                <div class="alert-toast"><?= $msg ?></div>
            <?php endif; ?>

            <form action="profile.php" method="POST" enctype="multipart/form-data" class="profile-card">
                
                <div class="avatar-box-section">
                    <div class="avatar-title-label">Ảnh hồ sơ</div>
                    <div class="avatar-flex-container">
                        <div class="main-avatar-view">
                            <img src="<?= $avatar_display ?>" id="current-avatar-preview">
                        </div>
                        
                        <div class="avatar-selection-grid">
                            <?php foreach($animal_avatars as $animal): ?>
                                <img src="<?= $animal ?>" class="avatar-thumb <?= ($animal === $current_avatar) ? 'active' : '' ?>" onclick="selectAnimalAvatar(this, '<?= $animal ?>')">
                            <?php endforeach; ?>
                            
                            <input type="hidden" name="avatar_url" id="hiddenAvatarUrl" value="<?= htmlspecialchars($current_avatar) ?>">
                            
                            <div class="upload-trigger-btn" onclick="document.getElementById('fileUploadInput').click()">+</div>
                            <input type="file" name="custom_avatar" id="fileUploadInput" accept="image/*" style="display: none;" onchange="previewUpload(event)">
                        </div>
                    </div>
                </div>

                <div class="info-data-row">
                    <div class="view-mode-wrapper" id="view-fullname">
                        <div class="info-left-block">
                            <div class="info-field-label">Tên người dùng</div>
                            <div class="info-field-value"><?= htmlspecialchars($user['full_name'] ?? 'Chưa cập nhật') ?></div>
                        </div>
                        <button type="button" class="action-text-btn" onclick="toggleEdit('fullname', true)">Sửa</button>
                    </div>
                    <div class="edit-input-wrapper" id="edit-fullname">
                        <div class="info-left-block">
                            <div class="info-field-label">Tên người dùng</div>
                            <input type="text" name="full_name" class="custom-input-field" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                        </div>
                        <button type="button" class="action-text-btn btn-cancel" onclick="toggleEdit('fullname', false)">Hủy</button>
                    </div>
                </div>

                <div class="info-data-row">
                    <div class="view-mode-wrapper" id="view-username">
                        <div class="info-left-block">
                            <div class="info-field-label">Email / Tên đăng nhập</div>
                            <div class="info-field-value"><?= htmlspecialchars($user['username']) ?></div>
                        </div>
                        <button type="button" class="action-text-btn" onclick="toggleEdit('username', true)">Sửa</button>
                    </div>
                    <div class="edit-input-wrapper" id="edit-username">
                        <div class="info-left-block">
                            <div class="info-field-label">Email / Tên đăng nhập</div>
                            <input type="text" name="username" class="custom-input-field" value="<?= htmlspecialchars($user['username']) ?>">
                        </div>
                        <button type="button" class="action-text-btn btn-cancel" onclick="toggleEdit('username', false)">Hủy</button>
                    </div>
                </div>

                <div class="info-data-row">
                    <div class="view-mode-wrapper" id="view-birthyear">
                        <div class="info-left-block">
                            <div class="info-field-label">Năm sinh</div>
                            <div class="info-field-value"><?= !empty($user['birth_year']) ? $user['birth_year'] : 'Chưa cập nhật' ?></div>
                        </div>
                        <button type="button" class="action-text-btn" onclick="toggleEdit('birthyear', true)">Sửa</button>
                    </div>
                    <div class="edit-input-wrapper" id="edit-birthyear">
                        <div class="info-left-block">
                            <div class="info-field-label">Năm sinh</div>
                            <input type="number" name="birth_year" class="custom-input-field" value="<?= htmlspecialchars($user['birth_year'] ?? '') ?>">
                        </div>
                        <button type="button" class="action-text-btn btn-cancel" onclick="toggleEdit('birthyear', false)">Hủy</button>
                    </div>
                </div>

                <div class="info-data-row" style="border-bottom: none;">
                    <div class="view-mode-wrapper" id="view-gender">
                        <div class="info-left-block">
                            <div class="info-field-label">Giới tính</div>
                            <div class="info-field-value"><?= !empty($user['gender']) ? $user['gender'] : 'Chưa cập nhật' ?></div>
                        </div>
                        <button type="button" class="action-text-btn" onclick="toggleEdit('gender', true)">Sửa</button>
                    </div>
                    <div class="edit-input-wrapper" id="edit-gender">
                        <div class="info-left-block">
                            <div class="info-field-label">Giới tính</div>
                            <div class="gender-radio-group">
                                <label class="radio-item-label">
                                    <input type="radio" name="gender" value="Nam" <?= (($user['gender'] ?? '') === 'Nam') ? 'checked' : '' ?>> Nam
                                </label>
                                <label class="radio-item-label">
                                    <input type="radio" name="gender" value="Nữ" <?= (($user['gender'] ?? '') === 'Nữ') ? 'checked' : '' ?>> Nữ
                                </label>
                                <label class="radio-item-label">
                                    <input type="radio" name="gender" value="Khác" <?= (($user['gender'] ?? '') === 'Khác') ? 'checked' : '' ?>> Khác
                                </label>
                            </div>
                        </div>
                        <button type="button" class="action-text-btn btn-cancel" onclick="toggleEdit('gender', false)">Hủy</button>
                    </div>
                </div>

                <div class="form-footer-actions">
                    <button type="button" class="btn-reset" onclick="window.location.reload()">Làm lại</button>
                    <button type="submit" class="btn-submit">Lưu tài khoản</button>
                </div>

            </form>
        </div>
    </div>

    <script>
        function toggleEdit(fieldId, showEdit) {
            const viewDiv = document.getElementById('view-' + fieldId);
            const editDiv = document.getElementById('edit-' + fieldId);
            
            if (showEdit) {
                viewDiv.classList.add('hidden');
                editDiv.classList.add('active');
            } else {
                viewDiv.classList.remove('hidden');
                editDiv.classList.remove('active');
            }
        }

        function selectAnimalAvatar(imgElement, url) {
            document.querySelectorAll('.avatar-thumb').forEach(el => el.classList.remove('active'));
            imgElement.classList.add('active');
            document.getElementById('hiddenAvatarUrl').value = url;
            document.getElementById('current-avatar-preview').src = url;
            document.getElementById('fileUploadInput').value = "";
        }

        function previewUpload(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('current-avatar-preview').src = e.target.result;
                    document.querySelectorAll('.avatar-thumb').forEach(el => el.classList.remove('active'));
                    document.getElementById('hiddenAvatarUrl').value = "";
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>