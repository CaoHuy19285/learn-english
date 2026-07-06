<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Bao gồm thư viện SimpleXLSX
require_once 'libs/SimpleXLSX.php';

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (!in_array($ext, ['xlsx', 'xls'])) {
        header("Location: admin_vocab.php?error=" . urlencode("Vui lòng upload file Excel (.xlsx hoặc .xls)"));
        exit();
    }

    try {
        if ($xlsx = SimpleXLSX::parse($file['tmp_name'])) {
            $rows = $xlsx->rows();
            // Bỏ header (dòng đầu)
            array_shift($rows);

            $count = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) continue;

                $word = trim($row[0] ?? '');
                $ipa = trim($row[1] ?? '');
                $definition = trim($row[2] ?? '');
                $example = trim($row[3] ?? '');
                $difficulty = trim($row[4] ?? 'Trung bình');
                $typeword_name = trim($row[5] ?? '');
                $image = trim($row[6] ?? '');

                if (empty($word) || empty($definition)) {
                    $errors[] = "Dòng " . ($index + 2) . ": Thiếu từ hoặc định nghĩa, bỏ qua.";
                    continue;
                }

                $typeword_id = null;
                if (!empty($typeword_name)) {
                    $res = $db->select("SELECT id FROM typeword WHERE name = ?", [$typeword_name]);
                    if (!empty($res)) {
                        $typeword_id = $res[0]['id'];
                    } else {
                        $errors[] = "Dòng " . ($index + 2) . ": Chủ đề '$typeword_name' không tồn tại, bỏ qua.";
                        continue;
                    }
                }

                $sql = "INSERT INTO vocabulary (typeword_id, word, ipa, definition, example, difficulty, image) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $params = [$typeword_id, $word, $ipa, $definition, $example, $difficulty, $image];
                $db->execute($sql, $params);
                $count++;
            }

            $msg = "Import thành công $count từ vựng!";
            if (!empty($errors)) {
                $msg .= " Lỗi: " . implode('; ', $errors);
            }
            header("Location: admin_vocab.php?msg=" . urlencode($msg));
        } else {
            header("Location: admin_vocab.php?error=" . urlencode(SimpleXLSX::parseError()));
        }
    } catch (Exception $e) {
        header("Location: admin_vocab.php?error=" . urlencode("Lỗi xử lý file: " . $e->getMessage()));
    }
} else {
    header("Location: admin_vocab.php");
}
exit();