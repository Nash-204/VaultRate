<?php
require_once 'config.php';

class Database {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("SQL prepare error: " . $this->conn->error . " | SQL: " . $sql);
            }
            
            if (!empty($params)) {
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                }
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("SQL execute error: " . $stmt->error . " | SQL: " . $sql);
            }
            
            return $stmt;
        } catch (Exception $e) {
            // Log the error for debugging
            error_log("Database Error: " . $e->getMessage());
            throw $e; // Re-throw the exception for the calling code to handle
        }
    }

    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("FetchAll Error: " . $e->getMessage());
            return []; // Return empty array on error
        }
    }

    public function fetchOne($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            $result = $stmt->get_result();
            return $result->fetch_assoc() ?: null;
        } catch (Exception $e) {
            error_log("FetchOne Error: " . $e->getMessage());
            return null; // Return null on error
        }
    }

    // Add a method to check if a column exists
    public function columnExists($table, $column) {
        $result = $this->conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $result->num_rows > 0;
    }
}

$db = new Database();