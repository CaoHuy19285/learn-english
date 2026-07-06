<?php

class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "wordwise_db";
    public $conn;

  public function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
            $this->conn->set_charset("utf8mb4");
        } catch (mysqli_sql_exception $e) {
            // Hiển thị câu thông báo thân thiện hoặc ghi log thay vì sập web
            die("Lỗi kết nối cơ sở dữ liệu: Vui lòng thử lại sau."); 
        }
    }

    // Hàm bổ trợ để tự động xác định chuỗi types (s, i, d)
    private function getTypes($params)
    {
        $types = "";
        foreach ($params as $p) {
            if (is_int($p)) $types .= 'i';
            else if (is_float($p)) $types .= 'd';
            else $types .= 's';
        }
        return $types;
    }

    public function select($sql, $params = [])
    {
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            // Tự động nhận diện types nếu người dùng chỉ truyền mảng params
            $types = $this->getTypes($params);
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $results = $stmt->get_result();
        $data = $results ? $results->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $data;
    }

    /**
     * Sửa lỗi Fatal Error: Chấp nhận cả (sql, params) hoặc (sql, types, params)
     */
    public function execute($sql, $arg1 = [], $arg2 = [])
    {
        $stmt = $this->conn->prepare($sql);
        
        $types = "";
        $params = [];

        if (is_string($arg1)) {
            // Trường hợp truyền 3 tham số: execute($sql, "ssi", [...])
            $types = $arg1;
            $params = $arg2;
        } else {
            // Trường hợp truyền 2 tham số: execute($sql, [...]) -> Tự nhận diện types
            $params = $arg1;
            $types = $this->getTypes($params);
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    public function insert_id()
    {
        return $this->conn->insert_id;
    }

    public function close()
    {
        // Sửa lỗi lặp vô hạn: phải gọi hàm close của mysqli, không phải gọi chính nó
        return $this->conn->close();
    }

    public function getConn()
    {
        return $this->conn;
    }
    /**
 * Cập nhật hoặc chèn tiến độ học từ vựng của user
 */
public function updateProgress($user_id, $vocabulary_id, $status = 'learned') {
    // Kiểm tra xem đã có bản ghi chưa
    $check = $this->select("SELECT id, correct_count, wrong_count FROM user_progress 
                            WHERE user_id = ? AND vocabulary_id = ?", [$user_id, $vocabulary_id]);
    if (!empty($check)) {
        // Nếu đã có, cập nhật status và tăng correct_count nếu chưa mastered
        $new_status = $status;
        $current = $check[0];
        // Nếu đã mastered thì giữ nguyên, không downgrade
        if ($current['status'] === 'mastered') {
            $new_status = 'mastered';
        }
        $this->execute("UPDATE user_progress 
                        SET status = ?, correct_count = correct_count + 1, last_review = NOW() 
                        WHERE id = ?", [$new_status, $current['id']]);
    } else {
        // Chưa có, chèn mới
        $this->execute("INSERT INTO user_progress (user_id, vocabulary_id, status, correct_count, last_review, created_at) 
                        VALUES (?, ?, ?, 1, NOW(), NOW())", [$user_id, $vocabulary_id, $status]);
    }
}
/**
 * Cập nhật streak cho user khi có hoạt động học

 */
public function updateStreak($user_id) {
    $user = $this->select("SELECT last_study_date, streak FROM users WHERE id = ?", [$user_id]);
    if (empty($user)) return 0;
    $user = $user[0];
    $today = date('Y-m-d');
    $last_date = $user['last_study_date'] ? date('Y-m-d', strtotime($user['last_study_date'])) : null;
    $streak = (int)$user['streak'];
    
    if ($last_date === $today) {
        // Hôm nay đã học → không tăng streak (chỉ cập nhật lại last_study_date nếu cần)
        return $streak;
    } elseif ($last_date === date('Y-m-d', strtotime('-1 day'))) {
        // Học liên tục → tăng streak
        $streak++;
    } else {
        // Bỏ lỡ ngày → reset về 1
        $streak = 1;
    }
    // Cập nhật vào DB
    $this->execute("UPDATE users SET streak = ?, last_study_date = NOW() WHERE id = ?", [$streak, $user_id]);
    return $streak;
}
}
?>