<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$db = new Database();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$is_edit = ($id > 0);

$error_msg = "";
$type_data = ['name' => '', 'slug' => '', 'description' => '', 'color_theme' => 'purple', 'image' => ''];

if ($is_edit) {
    $res = $db->select("SELECT * FROM typeword WHERE id = ?", [$id]);
    if (!empty($res)) {
        $type_data = $res[0];
    } else {
        header("Location: admin_type.php?error=" . urlencode("Chủ đề không tồn tại!"));
        exit();
    }
}

// Danh sách màu mở rộng với mã hex
$color_list = [
    'purple' => ['label' => 'Tím', 'hex' => '#a855f7'],
    'pink'   => ['label' => 'Hồng', 'hex' => '#ec4899'],
    'green'  => ['label' => 'Xanh lá', 'hex' => '#22c55e'],
    'indigo' => ['label' => 'Chàm', 'hex' => '#6366f1'],
    'orange' => ['label' => 'Cam', 'hex' => '#f97316'],
    'red'    => ['label' => 'Đỏ', 'hex' => '#ef4444'],
    'blue'   => ['label' => 'Xanh dương', 'hex' => '#3b82f6'],
    'yellow' => ['label' => 'Vàng', 'hex' => '#eab308'],
    'teal'   => ['label' => 'Xanh ngọc', 'hex' => '#14b8a6'],
    'cyan'   => ['label' => 'Xanh lơ', 'hex' => '#06b6d4'],
    'gray'   => ['label' => 'Xám', 'hex' => '#6b7280'],
    'lime'   => ['label' => 'Xanh chanh', 'hex' => '#84cc16'],
];

// Xử lý lưu form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $color_theme = trim($_POST['color_theme'] ?? 'purple');

    // Xử lý upload ảnh (giống admin_vocab_action)
    $image_path = $type_data['image'] ?? '';
    $remove_image = isset($_POST['remove_image']) ? intval($_POST['remove_image']) : 0;
    if ($remove_image === 1) {
        $image_path = '';
    }

    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'public/images/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $file_tmp = $_FILES['image_file']['tmp_name'];
        $file_name = basename($_FILES['image_file']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (!in_array($file_ext, $allowed)) {
            $error_msg = "Chỉ hỗ trợ các định dạng: " . implode(', ', $allowed);
        } else {
            $new_name = uniqid() . '.' . $file_ext;
            $destination = $upload_dir . $new_name;
            if (move_uploaded_file($file_tmp, $destination)) {
                if (!empty($type_data['image']) && file_exists($type_data['image']) && $remove_image !== 1) {
                    unlink($type_data['image']);
                }
                $image_path = $destination;
            } else {
                $error_msg = "Không thể upload ảnh.";
            }
        }
    }

    // Tự động sinh slug từ name
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    if (empty($name) || empty($slug)) {
        $error_msg = "Vui lòng nhập tên danh mục.";
    } else {
        if (empty($error_msg)) {
            if ($is_edit) {
                $sql = "UPDATE typeword SET name = ?, slug = ?, description = ?, color_theme = ?, image = ? WHERE id = ?";
                $db->execute($sql, [$name, $slug, $description, $color_theme, $image_path, $id]);
                header("Location: admin_type.php?msg=" . urlencode("Cập nhật danh mục chủ đề thành công!"));
                exit();
            } else {
                $check = $db->select("SELECT id FROM typeword WHERE slug = ?", [$slug]);
                if (!empty($check)) {
                    $error_msg = "Tên chủ đề đã tồn tại. Vui lòng nhập tên khác.";
                } else {
                    $sql = "INSERT INTO typeword (name, slug, description, color_theme, image) VALUES (?, ?, ?, ?, ?)";
                    $db->execute($sql, [$name, $slug, $description, $color_theme, $image_path]);
                    header("Location: admin_type.php?msg=" . urlencode("Thêm mới danh mục chủ đề thành công!"));
                    exit();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $is_edit ? 'Chỉnh sửa' : 'Thêm' ?> Chủ đề - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        * { box-sizing: border-box; margin:0; padding:0; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; overflow: hidden; }
        .app-layout { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 30px 40px 20px; background: #faf5ff; }
        .required-star { color: #ef4444; margin-left: 4px; font-weight: bold; }
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .back-link { display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: #4f46e5; font-weight: 600; margin-bottom: 15px; }
        .back-link:hover { text-decoration: underline; }

        .admin-card { background: white; border-radius: 20px; padding: 30px; border: 1px solid #e9d5ff; box-shadow: 0 4px 12px rgba(168,85,247,0.04); }
        .admin-card h3 { font-size: 18px; font-weight: 800; color: #1e293b; margin-bottom: 20px; }

        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-form .full-width { grid-column: 1 / -1; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; font-size: 14px; color: #1e293b; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
        .form-group label svg { width: 18px; height: 18px; stroke: #a855f7; stroke-width: 2; fill: none; }
        .form-input, .form-textarea { padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; font-weight: 500; transition: 0.15s; background: #f8fafc; width: 100%; }
        .form-input:focus, .form-textarea:focus { border-color: #a855f7; outline: none; background: white; box-shadow: 0 0 0 4px rgba(168,85,247,0.1); }
        .form-textarea { resize: vertical; min-height: 70px; }

        .form-actions { grid-column: 1 / -1; display: flex; gap: 12px; margin-top: 10px; flex-wrap: wrap; }
        .btn-primary-action { background: #a855f7; color: white; border: none; padding: 12px 28px; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer; box-shadow: 0 4px 0 #9333ea; transition: 0.15s; }
        .btn-primary-action:hover { transform: translateY(-2px); box-shadow: 0 6px 0 #9333ea; }
        .btn-primary-action:active { transform: translateY(4px); box-shadow: none; }
        .btn-secondary-action { background: #f1f5f9; color: #1e293b; border: 2px solid #e2e8f0; padding: 12px 28px; border-radius: 12px; font-weight: 700; font-size: 16px; text-decoration: none; text-align: center; transition: 0.15s; }
        .btn-secondary-action:hover { background: #e2e8f0; }

        /* Color preview */
        .color-picker-wrapper { display: flex; align-items: center; gap: 12px; }
        .color-preview { width: 36px; height: 36px; border-radius: 8px; border: 2px solid #e2e8f0; flex-shrink: 0; transition: 0.2s; }

        /* Upload ảnh (giống admin_vocab_action) */
        .upload-area { border: 2px dashed #e2e8f0; border-radius: 16px; padding: 20px; background: #faf5ff; transition: 0.2s; }
        .upload-area:hover { border-color: #a855f7; background: #f3e8ff; }
        .current-image-wrapper { display: flex; align-items: center; gap: 16px; padding: 12px; background: white; border-radius: 12px; border: 1px solid #e9d5ff; margin-bottom: 16px; }
        .current-image-wrapper .image-preview { width: 64px; height: 64px; border-radius: 8px; overflow: hidden; flex-shrink: 0; border: 1px solid #e2e8f0; }
        .current-image-wrapper .image-preview img { width: 100%; height: 100%; object-fit: cover; }
        .current-image-wrapper .image-info { flex: 1; display: flex; align-items: center; justify-content: space-between; }
        .current-image-wrapper .file-name { font-weight: 600; color: #1e293b; }
        .btn-remove-image { background: #fef2f2; border: none; width: 32px; height: 32px; border-radius: 8px; color: #ef4444; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.15s; }
        .btn-remove-image:hover { background: #fee2e2; }

        .file-upload-box { position: relative; border-radius: 12px; overflow: hidden; }
        .file-upload-box input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }
        .upload-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px 20px; border: 2px dashed #e9d5ff; border-radius: 12px; background: white; transition: 0.2s; }
        .file-upload-box:hover .upload-placeholder { border-color: #a855f7; background: #faf5ff; }
        .upload-placeholder .upload-text { font-weight: 700; color: #1e293b; margin-top: 8px; }
        .upload-placeholder .upload-hint { font-size: 13px; color: #94a3b8; margin-top: 4px; }
        .file-selected { display: none; align-items: center; gap: 16px; padding: 12px; background: white; border-radius: 12px; border: 1px solid #a855f7; box-shadow: 0 0 0 2px rgba(168,85,247,0.1); }
        .file-selected .file-preview { width: 48px; height: 48px; border-radius: 8px; overflow: hidden; flex-shrink: 0; border: 1px solid #e2e8f0; }
        .file-selected .file-preview img { width: 100%; height: 100%; object-fit: cover; }
        .file-selected .file-info { flex: 1; display: flex; align-items: center; justify-content: space-between; }
        .file-selected .file-name { font-weight: 600; color: #1e293b; }
        .btn-remove-file { background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 8px; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.15s; }
        .btn-remove-file:hover { background: #e2e8f0; color: #ef4444; }
        .upload-note { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #94a3b8; margin-top: 8px; }

        .welcome-bar { margin-bottom: 20px; }
        .welcome-bar h1 { font-size: 26px; font-weight: 900; color: #1e293b; display: flex; align-items: center; gap: 12px; }
        .welcome-bar p { color: #64748b; font-weight: 500; }

        @media (max-width: 768px) { .grid-form { grid-template-columns: 1fr; } .grid-form .full-width { grid-column: 1; } .main-content { padding: 16px; } }
    </style>
</head>
<body class="app-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="welcome-bar">
            <a href="admin_type.php" class="back-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                Quay lại danh sách
            </a>
            <h1><?= $is_edit ? 'Chỉnh sửa' : 'Thêm mới' ?> Chủ Đề</h1>
            <!-- Đã xóa dòng chữ "Các trường có dấu * không được để trống." -->
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <div class="admin-card">
            <h3>Cấu hình thông tin danh mục</h3>
            <form method="POST" class="grid-form" enctype="multipart/form-data">
                <!-- Tên danh mục + Màu sắc ngang hàng -->
                <div class="form-group">
                    <label>
                        <svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        Tên danh mục chủ đề<span class="required-star">*</span>
                    </label>
                    <input type="text" name="name" value="<?= htmlspecialchars($type_data['name']) ?>" placeholder="Ví dụ: IT & Tech" class="form-input" required>
                </div>

                <div class="form-group">
                    <label>
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 2v20M2 12h20"/></svg>
                        Màu sắc hiển thị<span class="required-star">*</span>
                    </label>
                    <div class="color-picker-wrapper">
                        <select name="color_theme" id="colorThemeSelect" class="form-input" style="flex:1;" onchange="updateColorPreview(this.value)">
                            <?php foreach($color_list as $key => $c): ?>
                                <option value="<?= $key ?>" <?= $type_data['color_theme'] == $key ? 'selected' : '' ?>>
                                    <?= $c['label'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="color-preview" id="colorPreview" style="background: <?= $color_list[$type_data['color_theme']]['hex'] ?? '#a855f7' ?>;"></div>
                    </div>
                </div>

                <!-- Mô tả (full-width) -->
                <div class="form-group full-width">
                    <label>
                        <svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        Mô tả ngắn
                    </label>
                    <input type="text" name="description" value="<?= htmlspecialchars($type_data['description'] ?? '') ?>" placeholder="Nhập mô tả ngắn về chủ đề..." class="form-input">
                </div>

                <!-- Hình ảnh (full-width, giống admin_vocab_action) -->
                <div class="form-group full-width">
                    <label>
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5-5 5-5-5-3 3"/></svg>
                        Hình ảnh đại diện
                    </label>
                    <div class="upload-area" id="uploadArea">
                        <?php if (!empty($type_data['image'])): ?>
                            <div class="current-image-wrapper" id="currentImageWrapper">
                                <div class="image-preview">
                                    <img src="<?= htmlspecialchars($type_data['image']) ?>" alt="Ảnh hiện tại">
                                </div>
                                <div class="image-info">
                                    <span class="file-name">Ảnh hiện tại</span>
                                    <button type="button" class="btn-remove-image" id="removeCurrentImage" title="Xóa ảnh này">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="18" y1="6" x2="6" y2="18"/>
                                            <line x1="6" y1="6" x2="18" y2="18"/>
                                        </svg>
                                    </button>
                                </div>
                                <input type="hidden" name="remove_image" id="removeImageInput" value="0">
                            </div>
                        <?php endif; ?>

                        <div class="file-upload-box" id="fileUploadBox">
                            <input type="file" name="image_file" accept="image/*" id="imageFileInput">
                            <div class="upload-placeholder" id="uploadPlaceholder">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="1.5">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/>
                                    <line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                                <span class="upload-text">Kéo thả hoặc nhấn để chọn ảnh</span>
                                <span class="upload-hint">JPG, PNG, GIF, WEBP, SVG</span>
                            </div>
                            <div class="file-selected" id="fileSelected">
                                <div class="file-preview" id="filePreview"></div>
                                <div class="file-info">
                                    <span class="file-name" id="fileName">Chưa có file</span>
                                    <button type="button" class="btn-remove-file" id="removeFileBtn" title="Bỏ chọn file">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="18" y1="6" x2="6" y2="18"/>
                                            <line x1="6" y1="6" x2="18" y2="18"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="upload-note">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="12" x2="12" y2="16"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        Để trống nếu không muốn thay đổi ảnh.
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary-action"><?= $is_edit ? 'Cập nhật' : 'Tạo mới' ?></button>
                    <a href="admin_type.php" class="btn-secondary-action">Hủy</a>
                </div>
            </form>
        </div>
        <?php include 'footer.php'; ?>
    </main>

    <script>
        // ===== PREVIEW MÀU =====
        const colorSelect = document.getElementById('colorThemeSelect');
        const colorPreview = document.getElementById('colorPreview');
        const colorMap = <?= json_encode($color_list) ?>;

        function updateColorPreview(value) {
            if (colorMap[value]) {
                colorPreview.style.background = colorMap[value].hex;
            }
        }

        // ===== UPLOAD ẢNH =====
        const fileInput = document.getElementById('imageFileInput');
        const fileSelected = document.getElementById('fileSelected');
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const removeFileBtn = document.getElementById('removeFileBtn');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        const currentImageWrapper = document.getElementById('currentImageWrapper');
        const removeCurrentBtn = document.getElementById('removeCurrentImage');
        const removeImageInput = document.getElementById('removeImageInput');

        if (removeCurrentBtn) {
            removeCurrentBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Bạn có chắc muốn xóa ảnh này?')) {
                    removeImageInput.value = '1';
                    currentImageWrapper.style.display = 'none';
                }
            });
        }

        fileInput.addEventListener('change', function(e) {
            const file = this.files[0];
            if (!file) {
                fileSelected.style.display = 'none';
                uploadPlaceholder.style.display = 'flex';
                return;
            }
            fileName.textContent = file.name;
            const reader = new FileReader();
            reader.onload = function(e) {
                filePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            };
            reader.readAsDataURL(file);
            uploadPlaceholder.style.display = 'none';
            fileSelected.style.display = 'flex';
        });

        removeFileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fileInput.value = '';
            fileSelected.style.display = 'none';
            uploadPlaceholder.style.display = 'flex';
        });
    </script>
</body>
</html>