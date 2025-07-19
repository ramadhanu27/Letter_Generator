<?php
/**
 * Database Configuration
 * Indonesian PDF Letter Generator
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    public $conn;

    public function __construct() {
        // Load configuration from environment or use defaults
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? 'letter_generator_db';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
        $this->charset = 'utf8mb4';
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            throw new Exception("Database connection failed");
        }

        return $this->conn;
    }

    public function closeConnection() {
        $this->conn = null;
    }

    // Test database connection
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            if ($conn) {
                return ['status' => 'success', 'message' => 'Database connection successful'];
            }
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}

// Database configuration constants
define('DB_CONFIG', [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'dbname' => $_ENV['DB_NAME'] ?? 'letter_generator_db',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]
]);

// Session configuration
define('SESSION_CONFIG', [
    'name' => 'LETTER_GEN_SESSION',
    'lifetime' => 86400, // 24 hours
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Security configuration
define('SECURITY_CONFIG', [
    'password_min_length' => 8,
    'password_require_uppercase' => true,
    'password_require_lowercase' => true,
    'password_require_numbers' => true,
    'password_require_symbols' => false,
    'max_login_attempts' => 5,
    'lockout_duration' => 900, // 15 minutes
    'session_regenerate_interval' => 1800, // 30 minutes
    'csrf_token_lifetime' => 3600 // 1 hour
]);

// Application configuration
define('APP_CONFIG', [
    'name' => 'Indonesian PDF Letter Generator',
    'version' => '1.0.0',
    'timezone' => 'Asia/Jakarta',
    'locale' => 'id_ID',
    'debug' => $_ENV['APP_DEBUG'] ?? false,
    'url' => $_ENV['APP_URL'] ?? 'http://localhost:3000',
    'upload_max_size' => 5242880, // 5MB
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif'],
    'default_avatar' => '/assets/images/default-avatar.png'
]);

// Email configuration (for verification and password reset)
define('EMAIL_CONFIG', [
    'smtp_host' => $_ENV['SMTP_HOST'] ?? 'localhost',
    'smtp_port' => $_ENV['SMTP_PORT'] ?? 587,
    'smtp_username' => $_ENV['SMTP_USER'] ?? '',
    'smtp_password' => $_ENV['SMTP_PASS'] ?? '',
    'smtp_encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'tls',
    'from_email' => $_ENV['FROM_EMAIL'] ?? 'noreply@lettergen.com',
    'from_name' => $_ENV['FROM_NAME'] ?? 'Letter Generator'
]);

// Set timezone
date_default_timezone_set(APP_CONFIG['timezone']);

// Start session with secure configuration
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(
        SESSION_CONFIG['lifetime'],
        SESSION_CONFIG['path'],
        SESSION_CONFIG['domain'],
        SESSION_CONFIG['secure'],
        SESSION_CONFIG['httponly']
    );
    session_name(SESSION_CONFIG['name']);
    session_start();
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || 
        !isset($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > SECURITY_CONFIG['csrf_token_lifetime']) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token) &&
           isset($_SESSION['csrf_token_time']) &&
           (time() - $_SESSION['csrf_token_time']) <= SECURITY_CONFIG['csrf_token_lifetime'];
}

// Utility functions
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword($password) {
    $config = SECURITY_CONFIG;
    
    if (strlen($password) < $config['password_min_length']) {
        return false;
    }
    
    if ($config['password_require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    if ($config['password_require_lowercase'] && !preg_match('/[a-z]/', $password)) {
        return false;
    }
    
    if ($config['password_require_numbers'] && !preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    if ($config['password_require_symbols'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
        return false;
    }
    
    return true;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateRandomToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function logActivity($user_id, $action, $description = '', $ip = null, $user_agent = null) {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $user_agent ?? $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt = $conn->prepare("
            INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$user_id, $action, $description, $ip, $user_agent]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

// Error handling
function handleError($message, $code = 500) {
    http_response_code($code);
    if (APP_CONFIG['debug']) {
        echo json_encode(['error' => $message, 'code' => $code]);
    } else {
        echo json_encode(['error' => 'Internal server error', 'code' => $code]);
    }
    exit;
}

// Response helpers
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function successResponse($message, $data = null) {
    $response = ['success' => true, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    jsonResponse($response);
}

function errorResponse($message, $code = 400) {
    jsonResponse(['success' => false, 'message' => $message], $code);
}
?>
