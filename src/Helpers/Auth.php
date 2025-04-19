<?php
class Auth {
    private static $instance = null;
    private $user = null;
    
    private function __construct() {
        session_start();
        $this->checkAuth();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function checkAuth() {
        if (isset($_SESSION['user_id'])) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $this->user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    public function login($email, $password) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $this->user = $user;
            return true;
        }
        return false;
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        $this->user = null;
    }
    
    public function user() {
        return $this->user;
    }
    
    public function isAuthenticated() {
        return $this->user !== null;
    }
    
    public function hasRole($role) {
        return $this->isAuthenticated() && $this->user['role'] === $role;
    }
    
    public function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}