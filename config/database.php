<?php
/**
 * Kết nối CSDL MySQL sử dụng PDO chuẩn mực (Theo yêu cầu Bài 5)
 * Cấu hình chế độ báo lỗi Exception và fetch mảng kết hợp (FETCH_ASSOC)
 */

function getPDO(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $host = '127.0.0.1';
        $port = '3306';
        $dbname = 'mini_store_db';
        $username = 'root';
        $password = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            die("<div style='color:red;padding:20px;font-family:sans-serif;'>
                <h3>Lỗi kết nối CSDL qua PDO:</h3>
                <p>" . htmlspecialchars($e->getMessage()) . "</p>
                <p>Vui lòng đảm bảo XAMPP MySQL đang chạy và CSDL <code>mini_store_db</code> đã được import.</p>
            </div>");
        }
    }
    return $pdo;
}
