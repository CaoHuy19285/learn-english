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
$vocab_data = [
    'word' => '', 'ipa' => '', 'typeword_id' => '', 'difficulty' => 'Trung bình', 'definition' => '', 'image' => '', 'example' => ''
];

if ($is_edit) {
    $res = $db->select("SELECT * FROM vocabulary WHERE id = ?", [$id]);
    if (!empty($res)) {
        $vocab_data = $res[0];
    } else {
        header("Location: admin_vocab.php?error=" . urlencode("Từ vựng không tồn tại!"));
        exit();
    }
}

$categories = $db->select("SELECT id, name FROM typeword ORDER BY id ASC");

// Tạo thư mục uploads nếu chưa có
$upload_dir = 'public/images/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $word = trim($_POST['word'] ?? '');
    $ipa = trim($_POST['ipa'] ?? '');
    $typeword_id = intval($_POST['typeword_id'] ?? 0);
    $difficulty = trim($_POST['difficulty'] ?? 'Trung bình');
    $definition = trim($_POST['definition'] ?? '');
    $example = trim($_POST['example'] ?? '');
    
    $image_path = $vocab_data['image'] ?? '';

    // Xóa ảnh cũ nếu có yêu cầu
    $remove_image = isset($_POST['remove_image']) ? intval($_POST['remove_image']) : 0;
    if ($remove_image === 1) {
        $image_path = '';
        // Xóa file vật lý cũ
        if (!empty($vocab_data['image']) && file_exists($vocab_data['image'])) {
            unlink($vocab_data['image']);
        }
    }

    // Upload ảnh mới
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image_file']['tmp_name'];
        $file_name = basename($_FILES['image_file']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($file_ext, $allowed_ext)) {
            $error_msg = "Chỉ hỗ trợ các định dạng ảnh: " . implode(', ', $allowed_ext);
        } else {
            // Kiểm tra dung lượng (giới hạn 5MB)
            if ($_FILES['image_file']['size'] > 5 * 1024 * 1024) {
                $error_msg = "Dung lượng ảnh vượt quá 5MB. Vui lòng chọn ảnh nhỏ hơn.";
            } else {
                $new_file_name = uniqid() . '.' . $file_ext;
                $destination = $upload_dir . $new_file_name;
                if (move_uploaded_file($file_tmp, $destination)) {
                    // Nếu có ảnh cũ và không bị xóa thì xóa file cũ
                    if (!empty($vocab_data['image']) && file_exists($vocab_data['image']) && $remove_image !== 1) {
                        unlink($vocab_data['image']);
                    }
                    $image_path = $destination;
                } else {
                    $error_msg = "Không thể upload ảnh. Vui lòng kiểm tra quyền ghi thư mục.";
                }
            }
        }
    }

    // Đồng bộ dữ liệu để hiển thị lại form nếu lỗi
    $vocab_data = compact('word', 'ipa', 'typeword_id', 'difficulty', 'definition', 'example');
    $vocab_data['image'] = $image_path;

    if (empty($error_msg)) {
        if (empty($word) || empty($ipa) || empty($definition) || $typeword_id <= 0) {
            $error_msg = "Vui lòng nhập đầy đủ các trường thông tin bắt buộc (*)";
        } else {
            if ($is_edit) {
                $sql = "UPDATE vocabulary SET word=?, ipa=?, typeword_id=?, difficulty=?, definition=?, image=?, example=? WHERE id=?";
                $db->execute($sql, [$word, $ipa, $typeword_id, $difficulty, $definition, $image_path, $example, $id]);
                header("Location: admin_vocab.php?msg=" . urlencode("Cập nhật từ vựng thành công!"));
                exit();
            } else {
                $sql = "INSERT INTO vocabulary (word, ipa, typeword_id, difficulty, definition, image, example) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $db->execute($sql, [$word, $ipa, $typeword_id, $difficulty, $definition, $image_path, $example]);
                header("Location: admin_vocab.php?msg=" . urlencode("Thêm từ vựng mới thành công!"));
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $is_edit ? 'Chỉnh sửa' : 'Thêm' ?> Từ vựng - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        :root { --gw-primary: #a855f7; --gw-primary-shadow: #9333ea; --gw-red: #ef4444; --bg-main: #f8fafc; --text-main: #1e293b; --border-color: #e2e8f0; --text-muted: #64748b; }
        * { box-sizing: border-box; margin:0; padding:0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-main); overflow: hidden; }
        .app-layout { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 30px 40px 20px; background: #faf5ff; }
        .required-star { color: var(--gw-red); margin-left: 4px; font-weight: bold; }
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .back-link { display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: #4f46e5; font-weight: 600; margin-bottom: 15px; }
        .back-link:hover { text-decoration: underline; }

        .admin-card { background: white; border-radius: 20px; padding: 30px; border: 1px solid #e9d5ff; box-shadow: 0 4px 12px rgba(168,85,247,0.04); }
        .admin-card h3 { font-size: 18px; font-weight: 800; color: var(--text-main); margin-bottom: 20px; }
        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-form .full-width { grid-column: 1 / -1; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; font-size: 14px; color: var(--text-main); margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
        .form-group label svg { width: 18px; height: 18px; stroke: var(--gw-primary); stroke-width: 2; fill: none; }
        .form-input, .form-textarea { padding: 10px 14px; border: 2px solid var(--border-color); border-radius: 12px; font-size: 15px; font-weight: 500; transition: 0.15s; background: #f8fafc; width: 100%; }
        .form-input:focus, .form-textarea:focus { border-color: var(--gw-primary); outline: none; background: white; box-shadow: 0 0 0 4px rgba(168,85,247,0.1); }
        .form-textarea { resize: vertical; min-height: 70px; }
        .form-actions { grid-column: 1 / -1; display: flex; gap: 12px; margin-top: 10px; flex-wrap: wrap; }
        .btn-primary-action { background: var(--gw-primary); color: white; border: none; padding: 12px 28px; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer; box-shadow: 0 4px 0 var(--gw-primary-shadow); transition: 0.15s; }
        .btn-primary-action:hover { transform: translateY(-2px); box-shadow: 0 6px 0 var(--gw-primary-shadow); }
        .btn-primary-action:active { transform: translateY(4px); box-shadow: none; }
        .btn-secondary-action { background: #f1f5f9; color: var(--text-main); border: 2px solid var(--border-color); padding: 12px 28px; border-radius: 12px; font-weight: 700; font-size: 16px; text-decoration: none; text-align: center; transition: 0.15s; }
        .btn-secondary-action:hover { background: #e2e8f0; }

        /* Upload ảnh */
        .upload-area { border: 2px dashed var(--border-color); border-radius: 16px; padding: 20px; background: #faf5ff; transition: 0.2s; }
        .upload-area:hover { border-color: var(--gw-primary); background: #f3e8ff; }
        .current-image-wrapper { display: flex; align-items: center; gap: 16px; padding: 12px; background: white; border-radius: 12px; border: 1px solid #e9d5ff; margin-bottom: 16px; }
        .current-image-wrapper .image-preview { width: 64px; height: 64px; border-radius: 8px; overflow: hidden; flex-shrink: 0; border: 1px solid var(--border-color); }
        .current-image-wrapper .image-preview img { width: 100%; height: 100%; object-fit: cover; }
        .current-image-wrapper .image-info { flex: 1; display: flex; align-items: center; justify-content: space-between; }
        .current-image-wrapper .file-name { font-weight: 600; color: var(--text-main); }
        .btn-remove-image { background: #fef2f2; border: none; width: 32px; height: 32px; border-radius: 8px; color: var(--gw-red); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.15s; }
        .btn-remove-image:hover { background: #fee2e2; }
        .file-upload-box { position: relative; border-radius: 12px; overflow: hidden; }
        .file-upload-box input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }
        .upload-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px 20px; border: 2px dashed #e9d5ff; border-radius: 12px; background: white; transition: 0.2s; }
        .file-upload-box:hover .upload-placeholder { border-color: var(--gw-primary); background: #faf5ff; }
        .upload-placeholder .upload-text { font-weight: 700; color: var(--text-main); margin-top: 8px; }
        .upload-placeholder .upload-hint { font-size: 13px; color: #94a3b8; margin-top: 4px; }
        .file-selected { display: none; align-items: center; gap: 16px; padding: 12px; background: white; border-radius: 12px; border: 1px solid var(--gw-primary); box-shadow: 0 0 0 2px rgba(168,85,247,0.1); }
        .file-selected .file-preview { width: 48px; height: 48px; border-radius: 8px; overflow: hidden; flex-shrink: 0; border: 1px solid var(--border-color); }
        .file-selected .file-preview img { width: 100%; height: 100%; object-fit: cover; }
        .file-selected .file-info { flex: 1; display: flex; align-items: center; justify-content: space-between; }
        .file-selected .file-name { font-weight: 600; color: var(--text-main); }
        .btn-remove-file { background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 8px; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.15s; }
        .btn-remove-file:hover { background: #e2e8f0; color: var(--gw-red); }
        .upload-note { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #94a3b8; margin-top: 8px; }

        .welcome-bar { margin-bottom: 20px; }
        .welcome-bar h1 { font-size: 26px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 12px; }
        .welcome-bar p { color: #64748b; font-weight: 500; }

        .ipa-auto { font-size: 12px; color: #94a3b8; margin-top: 4px; }
        .ipa-auto .status { color: #10b981; font-weight: 600; }
        .ipa-auto .error { color: var(--gw-red); font-weight: 600; }
        .word-input-wrapper { position: relative; }
        .word-input-wrapper .loading-spinner { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); display: none; }
        .spinner { width: 20px; height: 20px; border: 3px solid #e9d5ff; border-top-color: var(--gw-primary); border-radius: 50%; animation: spin 0.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 768px) { .grid-form { grid-template-columns: 1fr; } .grid-form .full-width { grid-column: 1; } .main-content { padding: 16px; } }
    </style>
</head>
<body class="app-layout">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="welcome-bar">
            <a href="admin_vocab.php" class="back-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                Quay lại danh sách
            </a>
            <h1><?= $is_edit ? 'Chỉnh sửa' : 'Thêm mới' ?> Từ vựng</h1>
            <p>Vui lòng điền thông tin chính xác. Các trường có dấu <span class="required-star">*</span> không được để trống.</p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <div class="admin-card">
            <h3>Thông tin chi tiết từ vựng</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="grid-form">
                    <!-- Từ vựng với tự động lấy IPA -->
                    <div class="form-group">
                        <label>
                            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="21" x2="9" y2="3"/><line x1="15" y1="21" x2="15" y2="3"/></svg>
                            Từ vựng (English)<span class="required-star">*</span>
                        </label>
                        <div class="word-input-wrapper">
                            <input type="text" name="word" id="word-input" value="<?= htmlspecialchars($vocab_data['word']) ?>" placeholder="Ví dụ: Algorithm" class="form-input" oninput="fetchIPA(this.value)" required>
                            <div class="loading-spinner" id="spinner"><div class="spinner"></div></div>
                        </div>
                        <div class="ipa-auto" id="ipa-status">💡 Nhập từ tiếng Anh → hệ thống sẽ tự động lấy phiên âm IPA.</div>
                    </div>

                    <!-- Phiên âm IPA -->
                    <div class="form-group">
                        <label>
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v4l2 2"/></svg>
                            Phiên âm IPA<span class="required-star">*</span>
                        </label>
                        <input type="text" name="ipa" id="ipa-input" value="<?= htmlspecialchars($vocab_data['ipa']) ?>" placeholder="Tự động điền khi nhập từ" class="form-input" required>
                    </div>

                    <!-- Chủ đề -->
                    <div class="form-group">
                        <label>
                            <svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                            Chủ đề danh mục<span class="required-star">*</span>
                        </label>
                        <select name="typeword_id" class="form-input" required>
                            <option value="">-- Chọn chủ đề --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $vocab_data['typeword_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Độ khó -->
                    <div class="form-group">
                        <label>
                            <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            Độ khó<span class="required-star">*</span>
                        </label>
                        <select name="difficulty" class="form-input">
                            <option value="Dễ" <?= $vocab_data['difficulty'] == 'Dễ' ? 'selected' : '' ?>>Dễ</option>
                            <option value="Trung bình" <?= $vocab_data['difficulty'] == 'Trung bình' ? 'selected' : '' ?>>Trung bình</option>
                            <option value="Khó" <?= $vocab_data['difficulty'] == 'Khó' ? 'selected' : '' ?>>Khó</option>
                        </select>
                    </div>

                    <!-- Nghĩa tiếng Việt -->
                    <div class="form-group full-width">
                        <label>
                            <svg viewBox="0 0 24 24"><path d="M3 3h18v18H3zM3 9h18"/><path d="M3 15h18"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
                            Nghĩa tiếng Việt<span class="required-star">*</span>
                        </label>
                        <input type="text" name="definition" value="<?= htmlspecialchars($vocab_data['definition']) ?>" placeholder="Ví dụ: Thuật toán máy tính" class="form-input" required>
                    </div>

                    <!-- Upload ảnh -->
                    <div class="form-group full-width">
                        <label>
                            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5-5 5-5-5-3 3"/></svg>
                            Hình ảnh minh họa
                        </label>
                        <div class="upload-area" id="uploadArea">
                            <?php if (!empty($vocab_data['image'])): ?>
                                <div class="current-image-wrapper" id="currentImageWrapper">
                                    <div class="image-preview">
                                        <img src="<?= htmlspecialchars($vocab_data['image']) ?>" alt="Ảnh hiện tại">
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
                                    <span class="upload-hint">JPG, PNG, GIF, WEBP (tối đa 5MB)</span>
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

                    <!-- Câu ví dụ -->
                    <div class="form-group full-width">
                        <label>
                            <svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                            Câu ví dụ (English)
                        </label>
                        <textarea name="example" class="form-textarea" placeholder="The app uses a complex algorithm."><?= htmlspecialchars($vocab_data['example']) ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary-action"><?= $is_edit ? 'Cập nhật' : 'Thêm mới' ?></button>
                        <a href="admin_vocab.php" class="btn-secondary-action">Hủy</a>
                    </div>
                </div>
            </form>
        </div>
        <?php include 'footer.php'; ?>
    </main>

    <script>
        // === TỰ ĐỘNG LẤY IPA ===
        let timeoutId = null;
        const ipaInput = document.getElementById('ipa-input');
        const statusDiv = document.getElementById('ipa-status');
        const spinner = document.getElementById('spinner');

        async function fetchIPA(word) {
            if (!word || word.trim() === '') {
                ipaInput.value = '';
                statusDiv.innerHTML = '💡 Nhập từ tiếng Anh → hệ thống sẽ tự động lấy phiên âm IPA.';
                spinner.style.display = 'none';
                return;
            }

            spinner.style.display = 'block';
            statusDiv.innerHTML = '⏳ Đang tra cứu phiên âm...';

            try {
                const response = await fetch(`https://api.dictionaryapi.dev/api/v2/entries/en/${encodeURIComponent(word.trim())}`);
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Không tìm thấy từ');
                }

                let ipa = '';
                if (data && data.length > 0) {
                    const entry = data[0];
                    if (entry.phonetics && entry.phonetics.length > 0) {
                        for (let p of entry.phonetics) {
                            if (p.text) {
                                ipa = p.text;
                                break;
                            }
                        }
                    }
                }

                if (ipa) {
                    ipaInput.value = ipa;
                    statusDiv.innerHTML = ' Đã tìm thấy phiên âm: <span class="status">' + ipa + '</span>';
                } else {
                    ipaInput.value = '';
                    statusDiv.innerHTML = ' Không tìm thấy phiên âm cho từ này. Vui lòng nhập thủ công.';
                }
            } catch (error) {
                console.error('Lỗi lấy IPA:', error);
                ipaInput.value = '';
                statusDiv.innerHTML = ' Lỗi kết nối API. Vui lòng nhập IPA thủ công.';
            } finally {
                spinner.style.display = 'none';
            }
        }

        // Debounce
        const originalFetchIPA = fetchIPA;
        fetchIPA = function(word) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                originalFetchIPA(word);
            }, 400);
        };

        // Nếu đang sửa và có từ, tự động lấy IPA khi load
        document.addEventListener('DOMContentLoaded', function() {
            const wordInput = document.getElementById('word-input');
            if (wordInput.value.trim() !== '') {
                fetchIPA(wordInput.value);
            }
        });

        // === UPLOAD ẢNH ===
        document.addEventListener('DOMContentLoaded', function() {
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

            const uploadBox = document.getElementById('fileUploadBox');
            uploadBox.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('drag-over');
            });
            uploadBox.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');
            });
            uploadBox.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });
        });
    </script>
</body>
</html>