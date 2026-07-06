<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

require_once 'libs/SimpleXLSX.php';

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (!in_array($ext, ['xlsx', 'xls'])) {
        header("Location: admin_type.php?error=" . urlencode("Vui lòng upload file Excel (.xlsx hoặc .xls)"));
        exit();
    }

    try {
        if ($xlsx = SimpleXLSX::parse($file['tmp_name'])) {
            $rows = $xlsx->rows();
            array_shift($rows);

            $count = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) continue;

                $name = trim($row[0] ?? '');
                $description = trim($row[1] ?? '');
                $color_theme = trim($row[2] ?? 'purple');
                $image = trim($row[3] ?? '');

                if (empty($name)) {
                    $errors[] = "Dòng " . ($index + 2) . ": Thiếu tên chủ đề, bỏ qua.";
                    continue;
                }

                $check = $db->select("SELECT id FROM typeword WHERE name = ?", [$name]);
                if (!empty($check)) {
                    $errors[] = "Dòng " . ($index + 2) . ": Chủ đề '$name' đã tồn tại, bỏ qua.";
                    continue;
                }

                $sql = "INSERT INTO typeword (name, description, color_theme, image) VALUES (?, ?, ?, ?)";
                $db->execute($sql, [$name, $description, $color_theme, $image]);
                $count++;
            }

            $msg = "Import thành công $count chủ đề!";
            if (!empty($errors)) {
                $msg .= " Lỗi: " . implode('; ', $errors);
            }
            header("Location: admin_type.php?msg=" . urlencode($msg));
        } else {
            header("Location: admin_type.php?error=" . urlencode(SimpleXLSX::parseError()));
        }
    } catch (Exception $e) {
        header("Location: admin_type.php?error=" . urlencode("Lỗi xử lý file: " . $e->getMessage()));
    }
} else {
    header("Location: admin_type.php");
}
exit();