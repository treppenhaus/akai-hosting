<?php
class DatabaseHelper {
    private $host = '10.0.0.41';
    private $dbName = 'akai';
    private $username = 'root'; // adjust as needed
    private $password = '';     // adjust as needed
    public $pdo;

    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die("DB Connection failed: " . $e->getMessage());
        }
    }
}
?>