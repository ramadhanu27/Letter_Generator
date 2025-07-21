<?php
require_once __DIR__ . '/../../config/database.php';

class User
{
    private $conn;
    private $table_name = "users";
    private $profile_table = "user_profiles";
    private $session_table = "user_sessions";

    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $full_name;
    public $phone;
    public $organization;
    public $position;
    public $created_at;
    public $updated_at;
    public $last_login;
    public $is_active;
    public $email_verified;

    public function __construct($db = null)
    {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    // Register new user
    public function register($data)
    {
        try {
            // Validate input
            $errors = $this->validateRegistrationData($data);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }

            // Check if user already exists
            if ($this->userExists($data['email'], $data['username'])) {
                return ['success' => false, 'message' => 'Email atau username sudah terdaftar'];
            }

            // Hash password
            $password_hash = hashPassword($data['password']);
            $verification_token = generateRandomToken();

            // Insert user
            $query = "INSERT INTO " . $this->table_name . " 
                     (username, email, password_hash, full_name, phone, organization, position, verification_token) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                $data['username'],
                $data['email'],
                $password_hash,
                $data['full_name'],
                $data['phone'] ?? null,
                $data['organization'] ?? null,
                $data['position'] ?? null,
                $verification_token
            ]);

            if ($result) {
                $user_id = $this->conn->lastInsertId();

                // Create user profile
                $this->createUserProfile($user_id, $data);

                // Log activity
                logActivity($user_id, 'user_registered', 'User registered successfully');

                return [
                    'success' => true,
                    'message' => 'Registrasi berhasil! Silakan cek email untuk verifikasi.',
                    'user_id' => $user_id,
                    'verification_token' => $verification_token
                ];
            }

            return ['success' => false, 'message' => 'Gagal melakukan registrasi'];
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem'];
        }
    }

    // Login user
    public function login($email_or_username, $password, $remember_me = false)
    {
        try {
            // Check login attempts
            if ($this->isAccountLocked($email_or_username)) {
                return ['success' => false, 'message' => 'Akun terkunci karena terlalu banyak percobaan login. Coba lagi nanti.'];
            }

            // Get user
            $user = $this->getUserByEmailOrUsername($email_or_username);

            if (!$user) {
                $this->recordFailedLogin($email_or_username);
                return ['success' => false, 'message' => 'Email/username atau password salah'];
            }

            // Verify password
            if (!verifyPassword($password, $user['password_hash'])) {
                $this->recordFailedLogin($email_or_username);
                return ['success' => false, 'message' => 'Email/username atau password salah'];
            }

            // Check if account is active
            if (!$user['is_active']) {
                return ['success' => false, 'message' => 'Akun tidak aktif. Hubungi administrator.'];
            }

            // Update last login
            $this->updateLastLogin($user['id']);

            // Create session
            $session_token = $this->createSession($user['id'], $remember_me);

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();

            // Log activity
            logActivity($user['id'], 'user_login', 'User logged in successfully');

            return [
                'success' => true,
                'message' => 'Login berhasil',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'organization' => $user['organization'],
                    'position' => $user['position']
                ],
                'session_token' => $session_token
            ];
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem'];
        }
    }

    // Logout user
    public function logout($user_id = null)
    {
        try {
            $user_id = $user_id ?? $_SESSION['user_id'] ?? null;

            if ($user_id) {
                // Remove session from database
                if (isset($_SESSION['session_token'])) {
                    $this->removeSession($_SESSION['session_token']);
                }

                // Log activity
                logActivity($user_id, 'user_logout', 'User logged out');
            }

            // Clear session
            session_unset();
            session_destroy();

            return ['success' => true, 'message' => 'Logout berhasil'];
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat logout'];
        }
    }

    // Get user by ID
    public function getUserById($id)
    {
        $query = "SELECT u.*, p.* FROM " . $this->table_name . " u 
                 LEFT JOIN " . $this->profile_table . " p ON u.id = p.user_id 
                 WHERE u.id = ? AND u.is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    // Get user by email or username
    private function getUserByEmailOrUsername($email_or_username)
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE (email = ? OR username = ?) AND is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email_or_username, $email_or_username]);

        return $stmt->fetch();
    }

    // Check if user exists
    private function userExists($email, $username)
    {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? OR username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email, $username]);

        return $stmt->fetch() !== false;
    }

    // Validate registration data
    private function validateRegistrationData($data)
    {
        $errors = [];

        // Required fields
        $required = ['username', 'email', 'password', 'full_name'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[] = ucfirst($field) . ' wajib diisi';
            }
        }

        // Username validation
        if (!empty($data['username'])) {
            if (strlen($data['username']) < 3 || strlen($data['username']) > 50) {
                $errors[] = 'Username harus 3-50 karakter';
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
                $errors[] = 'Username hanya boleh huruf, angka, dan underscore';
            }
        }

        // Email validation
        if (!empty($data['email']) && !validateEmail($data['email'])) {
            $errors[] = 'Format email tidak valid';
        }

        // Password validation
        if (!empty($data['password'])) {
            if (!validatePassword($data['password'])) {
                $errors[] = 'Password minimal 8 karakter dengan huruf besar, kecil, dan angka';
            }
            if (isset($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
                $errors[] = 'Konfirmasi password tidak cocok';
            }
        }

        // Full name validation
        if (!empty($data['full_name']) && strlen($data['full_name']) < 2) {
            $errors[] = 'Nama lengkap minimal 2 karakter';
        }

        return $errors;
    }

    // Create user profile
    private function createUserProfile($user_id, $data)
    {
        $query = "INSERT INTO " . $this->profile_table . " 
                 (user_id, address, city, province, preferences) 
                 VALUES (?, ?, ?, ?, ?)";

        $preferences = json_encode([
            'theme' => 'light',
            'language' => 'id',
            'notifications' => true
        ]);

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $user_id,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['province'] ?? null,
            $preferences
        ]);
    }

    // Create session
    private function createSession($user_id, $remember_me = false)
    {
        $session_token = generateRandomToken(64);
        $expires_at = date('Y-m-d H:i:s', time() + ($remember_me ? 2592000 : 86400)); // 30 days or 1 day

        $query = "INSERT INTO " . $this->session_table . " 
                 (user_id, session_token, ip_address, user_agent, expires_at) 
                 VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            $user_id,
            $session_token,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $expires_at
        ]);

        $_SESSION['session_token'] = $session_token;
        return $session_token;
    }

    // Remove session
    private function removeSession($session_token)
    {
        $query = "DELETE FROM " . $this->session_table . " WHERE session_token = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$session_token]);
    }

    // Update last login
    private function updateLastLogin($user_id)
    {
        $query = "UPDATE " . $this->table_name . " SET last_login = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id]);
    }

    // Check if account is locked (simplified - you might want to implement proper rate limiting)
    private function isAccountLocked($email_or_username)
    {
        // This is a simplified implementation
        // In production, you'd want to track failed attempts in a separate table
        return false;
    }

    // Record failed login (simplified)
    private function recordFailedLogin($email_or_username)
    {
        // Log failed login attempt
        error_log("Failed login attempt for: " . $email_or_username);
    }

    // Check if user is logged in
    public static function isLoggedIn()
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    // Get current user
    public static function getCurrentUser()
    {
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'full_name' => $_SESSION['full_name'] ?? null
        ];
    }

    // Require login (redirect if not logged in)
    public static function requireLogin($redirect_url = '/surat/login')
    {
        if (!self::isLoggedIn()) {
            header("Location: $redirect_url");
            exit;
        }
    }

    // Update user profile
    public function updateProfile($user_id, $data)
    {
        try {
            $this->conn->beginTransaction();

            // Update users table
            $user_query = "UPDATE " . $this->table_name . "
                          SET full_name = ?, phone = ?, organization = ?, position = ?, updated_at = NOW()
                          WHERE id = ?";
            $user_stmt = $this->conn->prepare($user_query);
            $user_stmt->execute([
                $data['full_name'],
                $data['phone'] ?? null,
                $data['organization'] ?? null,
                $data['position'] ?? null,
                $user_id
            ]);

            // Update user_profiles table
            $profile_query = "UPDATE " . $this->profile_table . "
                             SET address = ?, city = ?, province = ?, postal_code = ?
                             WHERE user_id = ?";
            $profile_stmt = $this->conn->prepare($profile_query);
            $profile_stmt->execute([
                $data['address'] ?? null,
                $data['city'] ?? null,
                $data['province'] ?? null,
                $data['postal_code'] ?? null,
                $user_id
            ]);

            $this->conn->commit();

            // Log activity
            logActivity($user_id, 'profile_updated', 'User profile updated');

            return ['success' => true, 'message' => 'Profil berhasil diperbarui'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Profile update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal memperbarui profil'];
        }
    }

    // Change password
    public function changePassword($user_id, $current_password, $new_password)
    {
        try {
            // Get current user
            $user = $this->getUserById($user_id);
            if (!$user) {
                return ['success' => false, 'message' => 'User tidak ditemukan'];
            }

            // Verify current password
            if (!verifyPassword($current_password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Password lama tidak benar'];
            }

            // Validate new password
            if (!validatePassword($new_password)) {
                return ['success' => false, 'message' => 'Password baru tidak memenuhi kriteria'];
            }

            // Update password
            $new_hash = hashPassword($new_password);
            $query = "UPDATE " . $this->table_name . " SET password_hash = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([$new_hash, $user_id]);

            if ($result) {
                // Log activity
                logActivity($user_id, 'password_changed', 'User changed password');
                return ['success' => true, 'message' => 'Password berhasil diubah'];
            }

            return ['success' => false, 'message' => 'Gagal mengubah password'];
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem'];
        }
    }
}
