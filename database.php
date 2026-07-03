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
}
?>