<?php
session_start();
require_once 'config/database.php';
require_once 'classes/User.php';

// Redirect if already logged in as admin
if (isset($_SESSION['user_id'])) {
    $current_user = User::getCurrentUser();
    if ($current_user && $current_user['role'] === 'admin') {
        header('Location: admin.php');
        exit();
    }
}

$error_message = '';
$success_message = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_identifier = trim($_POST['login_identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    if (empty($login_identifier) || empty($password)) {
        $error_message = 'Username/Email dan password wajib diisi.';
    } else {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            // Check user with admin role only
            $stmt = $conn->prepare("
                SELECT id, username, email, password_hash, full_name, role, is_active, email_verified 
                FROM users 
                WHERE (username = ? OR email = ?) AND role = 'admin'
            ");
            $stmt->execute([$login_identifier, $login_identifier]);
            $user = $stmt->fetch();

            if (!$user) {
                $error_message = 'Admin tidak ditemukan atau Anda bukan admin.';

                // Log failed admin login attempt
                $stmt = $conn->prepare("
                    INSERT INTO admin_logs (admin_id, action, target_type, description, ip_address, created_at) 
                    VALUES (NULL, 'failed_login', 'auth', ?, ?, NOW())
                ");
                $stmt->execute([
                    "Failed admin login attempt for: $login_identifier",
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
            } elseif (!$user['is_active']) {
                $error_message = 'Akun admin Anda telah dinonaktifkan.';
            } elseif (!password_verify($password, $user['password_hash'])) {
                $error_message = 'Password salah.';

                // Log failed password attempt
                $stmt = $conn->prepare("
                    INSERT INTO admin_logs (admin_id, action, target_type, description, ip_address, created_at) 
                    VALUES (?, 'failed_password', 'auth', ?, ?, NOW())
                ");
                $stmt->execute([
                    $user['id'],
                    "Failed password attempt for admin: {$user['username']}",
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
            } else {
                // Successful admin login
                session_regenerate_id(true); // Regenerate session ID for security
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['admin_login_time'] = time();
                $_SESSION['is_admin'] = true;

                // Set remember me cookie for admin
                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('admin_remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);

                    // Store token in database
                    $stmt = $conn->prepare("
                        UPDATE users SET remember_token = ? WHERE id = ?
                    ");
                    $stmt->execute([$token, $user['id']]);
                }

                // Update last login
                $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);

                // Log successful admin login
                try {
                    $stmt = $conn->prepare("
                        INSERT INTO admin_logs (admin_id, action, target_type, description, ip_address, created_at)
                        VALUES (?, 'login', 'auth', ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $user['id'],
                        "Admin login successful: {$user['username']}",
                        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    ]);
                } catch (Exception $log_error) {
                    // Continue even if logging fails
                    error_log("Admin login logging error: " . $log_error->getMessage());
                }

                // Force redirect to admin dashboard
                header('Location: admin.php');
                exit();
            }
        } catch (Exception $e) {
            $error_message = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            error_log("Admin login error: " . $e->getMessage());
        }
    }
}

// Check for remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['admin_remember_token'])) {
    try {
        $database = new Database();
        $conn = $database->getConnection();

        $stmt = $conn->prepare("
            SELECT id, username, role FROM users 
            WHERE remember_token = ? AND role = 'admin' AND is_active = 1
        ");
        $stmt->execute([$_COOKIE['admin_remember_token']]);
        $user = $stmt->fetch();

        if ($user) {
            session_regenerate_id(true);
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['admin_login_time'] = time();
            $_SESSION['is_admin'] = true;

            header('Location: admin.php');
            exit();
        }
    } catch (Exception $e) {
        // Clear invalid cookie
        setcookie('admin_remember_token', '', time() - 3600, '/');
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Indonesian PDF Letter Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-gradient {
            background: linear-gradient(135deg, #0c0a1e 0%, #1a1625 50%, #2d1b69 100%);
        }

        .admin-pattern {
            background-image:
                radial-gradient(circle at 25% 25%, rgba(139, 92, 246, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(79, 70, 229, 0.05) 0%, transparent 50%);
        }

        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .pulse-glow {
            animation: pulse-glow 2s infinite;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
            }

            50% {
                box-shadow: 0 0 35px rgba(139, 92, 246, 0.5);
            }
        }
    </style>
</head>

<body class="admin-gradient admin-pattern min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="floating-animation">
                    <div class="w-24 h-24 bg-violet-900 bg-opacity-60 backdrop-blur-sm rounded-full mx-auto mb-6 flex items-center justify-center pulse-glow">
                        <i class="fas fa-shield-alt text-4xl text-violet-200"></i>
                    </div>
                </div>
                <h2 class="text-4xl font-bold text-white mb-2">Admin Portal</h2>
                <p class="text-violet-200 text-lg">Indonesian PDF Letter Generator</p>
                <div class="mt-4 flex items-center justify-center space-x-2 text-violet-300">
                    <i class="fas fa-lock text-sm"></i>
                    <span class="text-sm">Secure Administrator Access</span>
                </div>
            </div>

            <!-- Login Form -->
            <div class="bg-gray-900 bg-opacity-80 backdrop-blur-md rounded-2xl shadow-2xl p-8 border border-violet-500 border-opacity-30">
                <!-- Messages -->
                <?php if ($error_message): ?>
                    <div class="mb-6 bg-red-900 bg-opacity-40 backdrop-blur-sm border border-red-700 border-opacity-50 text-red-200 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle mr-3"></i>
                            <span><?php echo htmlspecialchars($error_message); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="mb-6 bg-green-900 bg-opacity-40 backdrop-blur-sm border border-green-700 border-opacity-50 text-green-200 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3"></i>
                            <span><?php echo htmlspecialchars($success_message); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="login_identifier" class="block text-sm font-medium text-gray-200 mb-2">
                            <i class="fas fa-user mr-2"></i>Username atau Email Admin
                        </label>
                        <input type="text" name="login_identifier" id="login_identifier" required
                            value="<?php echo htmlspecialchars($_POST['login_identifier'] ?? ''); ?>"
                            class="w-full px-4 py-3 bg-gray-800 bg-opacity-70 backdrop-blur-sm border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-violet-400 focus:border-violet-400 transition-all duration-200"
                            placeholder="Masukkan username atau email admin">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-200 mb-2">
                            <i class="fas fa-lock mr-2"></i>Password Admin
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required
                                class="w-full px-4 py-3 bg-gray-800 bg-opacity-70 backdrop-blur-sm border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-violet-400 focus:border-violet-400 transition-all duration-200"
                                placeholder="Masukkan password admin">
                            <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="checkbox" name="remember_me" id="remember_me"
                                class="h-4 w-4 text-violet-500 focus:ring-violet-400 border-gray-500 rounded bg-gray-700">
                            <label for="remember_me" class="ml-2 block text-sm text-gray-300">
                                Ingat saya (30 hari)
                            </label>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-violet-600 hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 transition-all duration-200 transform hover:scale-105">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-sign-in-alt text-violet-200 group-hover:text-white"></i>
                            </span>
                            Masuk ke Admin Panel
                        </button>
                    </div>
                </form>

                <!-- Additional Links -->
                <div class="mt-8 text-center space-y-3">
                    <div class="border-t border-gray-600 border-opacity-40 pt-6">
                        <p class="text-gray-300 text-sm mb-3">Akses Lainnya:</p>
                        <div class="space-y-2">
                            <a href="login" class="block text-gray-400 hover:text-violet-300 text-sm transition-colors">
                                <i class="fas fa-user mr-2"></i>Login sebagai User Biasa
                            </a>
                            <a href="admin/register" class="block text-gray-400 hover:text-violet-300 text-sm transition-colors">
                                <i class="fas fa-user-plus mr-2"></i>Buat Admin Baru
                            </a>
                            <a href="index" class="block text-gray-400 hover:text-violet-300 text-sm transition-colors">
                                <i class="fas fa-home mr-2"></i>Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="text-center">
                <div class="bg-yellow-900 bg-opacity-40 backdrop-blur-sm border border-yellow-600 border-opacity-50 rounded-lg p-4">
                    <div class="flex items-center justify-center text-yellow-200">
                        <i class="fas fa-shield-alt mr-2"></i>
                        <span class="text-sm">Area Terbatas - Hanya untuk Administrator</span>
                    </div>
                    <p class="text-xs text-yellow-300 mt-2">
                        Semua aktivitas login admin dicatat untuk keamanan sistem
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Auto-focus on first input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('login_identifier').focus();
        });

        // Add enter key support
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });

        // Add some interactive effects
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('transform', 'scale-105');
            });

            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('transform', 'scale-105');
            });
        });
    </script>
</body>

</html>