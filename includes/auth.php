<?php
require_once 'db.php';
require_once 'functions.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function register($username, $email, $password) {

        $check = $this->db->fetchOne("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
        
        if ($check) {
            if ($check['username'] === $username) {
                return 'username'; // Be specific about what exists
            } else {
                return 'email';
            }
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $this->db->query(
                "INSERT INTO users (username, email, password) VALUES (?, ?, ?)",
                [$username, $email, $hashedPassword]
            );
            
            if ($stmt->affected_rows === 1) {
                return true; // Return true on success
            }
            return false;
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    public function login($username, $password) {
        $user = $this->db->fetchOne("SELECT * FROM users WHERE username = ?", [$username]);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];
            return true;
        }
        
        return false;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function getUser($id) {
        return $this->db->fetchOne("SELECT id, username, email, created_at FROM users WHERE id = ?", [$id]);
    }
}

$auth = new Auth();