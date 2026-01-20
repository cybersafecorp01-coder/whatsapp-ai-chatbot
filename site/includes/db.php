<?php
// Carregar variáveis de ambiente
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
        putenv(trim($key) . '=' . trim($value));
    }
}

loadEnv(__DIR__ . '/../.env');

class Database {
    private $pdo;
    
    public function __construct() {
        try {
            $host = getenv('DB_HOST') ?: 'localhost';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') ?: '';
            $name = getenv('DB_NAME') ?: 'mona_reservas';
            
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$name;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function insert($table, $data) {
        $keys = array_keys($data);
        $placeholders = array_fill(0, count($keys), '?');
        $sql = "INSERT INTO $table (" . implode(',', $keys) . ") VALUES (" . implode(',', $placeholders) . ")";
        return $this->query($sql, array_values($data));
    }
    
    public function update($table, $data, $where) {
        $set = [];
        foreach ($data as $key => $val) {
            $set[] = "$key = ?";
        }
        $whereKeys = array_keys($where);
        $whereClause = [];
        foreach ($whereKeys as $key) {
            $whereClause[] = "$key = ?";
        }
        $sql = "UPDATE $table SET " . implode(',', $set) . " WHERE " . implode(' AND ', $whereClause);
        $params = array_merge(array_values($data), array_values($where));
        return $this->query($sql, $params);
    }
    
    public function delete($table, $where) {
        $whereKeys = array_keys($where);
        $whereClause = [];
        foreach ($whereKeys as $key) {
            $whereClause[] = "$key = ?";
        }
        $sql = "DELETE FROM $table WHERE " . implode(' AND ', $whereClause);
        return $this->query($sql, array_values($where));
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollback() {
        return $this->pdo->rollBack();
    }
}

$db = new Database();
