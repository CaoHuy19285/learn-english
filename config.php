<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database.php';
$db = new Database();

// XỬ LÝ ĐỒNG BỘ STREAK & XP QUA AJAX (KHI NGƯỜI DÙNG BẤM HỌC THUỘC TỪ)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'sync_progress') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit();
    }

    $userId = $_SESSION['user_id'];
    $xp_to_add = isset($_POST['xp_added']) ? (int)$_POST['xp_added'] : 0;

    // 1. Lấy dữ liệu streak hiện tại của người dùng
    $user_data = $db->select("SELECT xp, streak, last_study_date FROM nguoidung WHERE id = ?", [$userId]);
    if (count($user_data) > 0) {
        $current_xp = $user_data[0]['xp'] + $xp_to_add;
        $current_streak = $user_data[0]['streak'];
        $last_study = $user_data[0]['last_study_date'];

        $now = new DateTime();
        if ($last_study === null) {
            // Lần đầu tiên học bài
            $current_streak = 1;
        } else {
            $last_date = new DateTime($last_study);
            $interval = $now->diff($last_date);
            $hours_diff = ($interval->days * 24) + $interval->h;

            if ($hours_diff >= 24 && $hours_diff <= 48) {
                // Thỏa mãn quy tắc học xuyên suốt 2 ngày (khoảng cách từ 24h - 48h): Tăng lửa
                $current_streak += 1;
            } else if ($hours_diff > 48) {
                // Quá 48 tiếng không học bài: Tắt lửa hoàn toàn
                $current_streak = 1; 
            }
            // Nếu học tiếp trong vòng chưa tới 24h thì giữ nguyên chuỗi ngày (chờ ngày hôm sau)
        }

        // Cập nhật lại vào cơ sở dữ liệu
        $db->execute(
            "UPDATE nguoidung SET xp = ?, streak = ?, last_study_date = NOW() WHERE id = ?",
            [$current_xp, $current_streak, $userId]
        );

        $_SESSION['streak'] = $current_streak;
        $_SESSION['xp'] = $current_xp;

        echo json_encode([
            'status' => 'success',
            'xp' => $current_xp,
            'streak' => $current_streak
        ]);
    }
    exit();
}
?>