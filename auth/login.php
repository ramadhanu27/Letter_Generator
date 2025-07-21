<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/User.php';

// Prevent redirect loops
if (!isset($_SESSION['login_redirect_count'])) {
    $_SESSION['login_redirect_count'] = 0;
}

// Redirect if already logged in (but prevent loops)
if (User::isLoggedIn() && !isset($_GET['force'])) {
    $_SESSION['login_redirect_count']++;

    if ($_SESSION['login_redirect_count'] < 3) {
        header('Location: ../dashboard');
        exit;
    } else {
        // Reset counter and show error
        unset($_SESSION['login_redirect_count']);
        $error_message = 'Redirect loop detected. Please clear your browser cache and cookies.';
    }
} else {
    // Reset counter when showing login form
    unset($_SESSION['login_redirect_count']);
}

$error_message = '';
$success_message = '';

// Handle 2FA expired error
if (isset($_GET['error']) && $_GET['error'] === '2fa_expired') {
    $error_message = 'Sesi verifikasi 2FA telah berakhir. Silakan login kembali.';
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Token keamanan tidak valid. Silakan refresh halaman.';
    } else {
        $email_or_username = sanitizeInput($_POST['email_or_username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember_me = isset($_POST['remember_me']);

        if (empty($email_or_username) || empty($password)) {
            $error_message = 'Email/username dan password wajib diisi';
        } else {
            try {
                $database = new Database();
                $conn = $database->getConnection();

                // Check user credentials first
                $stmt = $conn->prepare("
                    SELECT id, username, email, password_hash, full_name, role, is_active, two_factor_enabled
                    FROM users
                    WHERE (username = ? OR email = ?) AND is_active = 1
                ");
                $stmt->execute([$email_or_username, $email_or_username]);
                $user_data = $stmt->fetch();

                if (!$user_data) {
                    $error_message = 'Email/username atau password tidak valid.';
                } elseif (!password_verify($password, $user_data['password_hash'])) {
                    $error_message = 'Email/username atau password tidak valid.';
                } else {
                    // Successful login
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user_data['id'];
                    $_SESSION['username'] = $user_data['username'];
                    $_SESSION['email'] = $user_data['email'];
                    $_SESSION['role'] = $user_data['role'];
                    $_SESSION['full_name'] = $user_data['full_name'];

                    // Update last login
                    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user_data['id']]);

                    // Handle remember me
                    if ($remember_me) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);

                        $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                        $stmt->execute([$token, $user_data['id']]);
                    }

                    // Redirect based on role
                    if ($user_data['role'] === 'admin') {
                        header('Location: ../admin');
                    } else {
                        $redirect_url = $_GET['redirect'] ?? '../dashboard';
                        header("Location: $redirect_url");
                    }
                    exit;
                }
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $error_message = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Indonesian PDF Letter Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../public/assets/css/security-protection.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* ========== SECURITY PROTECTION CSS ========== */

        /* Disable text selection and other interactions */
        * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
            -webkit-tap-highlight-color: transparent;
        }

        /* Allow selection only for input fields */
        input,
        textarea,
        button {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }

        /* Disable image dragging */
        img {
            -webkit-user-drag: none;
            -khtml-user-drag: none;
            -moz-user-drag: none;
            -o-user-drag: none;
            user-drag: none;
            pointer-events: none;
        }

        /* Disable highlighting */
        ::selection {
            background: transparent;
        }

        ::-moz-selection {
            background: transparent;
        }

        /* Anti-screenshot protection */
        body {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* Disable print styles */
        @media print {
            * {
                display: none !important;
            }

            body::before {
                content: "Printing is not allowed for security reasons.";
                display: block !important;
                font-size: 24px;
                text-align: center;
                margin-top: 50px;
            }
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
                <i class="fas fa-file-pdf text-2xl text-blue-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Letter Generator</h1>
            <p class="text-blue-100">Masuk ke akun Anda</p>
        </div>

        <!-- Login Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <!-- Email/Username Field -->
                <div>
                    <label for="email_or_username" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-user mr-2"></i>Email atau Username
                    </label>
                    <input type="text"
                        id="email_or_username"
                        name="email_or_username"
                        value="<?php echo htmlspecialchars($_POST['email_or_username'] ?? ''); ?>"
                        class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-200"
                        placeholder="Masukkan email atau username"
                        required>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <div class="relative">
                        <input type="password"
                            id="password"
                            name="password"
                            class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-200 pr-12"
                            placeholder="Masukkan password"
                            required>
                        <button type="button"
                            onclick="togglePassword()"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-blue-100 hover:text-white transition-colors">
                            <i id="password-icon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center text-sm text-white">
                        <input type="checkbox"
                            name="remember_me"
                            class="mr-2 rounded border-white border-opacity-30 bg-white bg-opacity-20 text-blue-600 focus:ring-white focus:ring-opacity-50">
                        Ingat saya
                    </label>
                    <a href="forgot-password" class="text-sm text-blue-100 hover:text-white transition-colors">
                        Lupa password?
                    </a>
                </div>

                <!-- Login Button -->
                <button type="submit"
                    class="w-full bg-white text-blue-600 font-semibold py-3 px-4 rounded-lg hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                </button>
            </form>

            <!-- Divider -->
            <div class="my-6 flex items-center">
                <div class="flex-1 border-t border-white border-opacity-30"></div>
                <span class="px-4 text-sm text-blue-100">atau</span>
                <div class="flex-1 border-t border-white border-opacity-30"></div>
            </div>

            <!-- Register Link -->
            <div class="text-center">
                <p class="text-blue-100 mb-4">Belum punya akun?</p>
                <a href="register"
                    class="inline-flex items-center justify-center w-full bg-transparent border-2 border-white text-white font-semibold py-3 px-4 rounded-lg hover:bg-white hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 transition-all duration-200">
                    <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                </a>
            </div>

            <!-- Back to Home -->
            <div class="text-center mt-6">
                <a href="index.html" class="text-sm text-blue-100 hover:text-white transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali ke Beranda
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-blue-100 text-sm">
            <p>&copy; 2025 Indonesian PDF Letter Generator. All rights reserved.</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordField.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.bg-red-100, .bg-green-100');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const emailOrUsername = document.getElementById('email_or_username').value.trim();
            const password = document.getElementById('password').value;

            if (!emailOrUsername || !password) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang diperlukan');
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter');
                return false;
            }
        });

        // Add loading state to submit button
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            submitBtn.disabled = true;
        });

        // ========== SECURITY PROTECTION ==========

        // Disable right click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });

        // Disable keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // F12 (Developer Tools)
            if (e.keyCode === 123) {
                e.preventDefault();
                return false;
            }
            // Ctrl+Shift+I (Developer Tools)
            if (e.ctrlKey && e.shiftKey && e.keyCode === 73) {
                e.preventDefault();
                return false;
            }
            // Ctrl+U (View Source)
            if (e.ctrlKey && e.keyCode === 85) {
                e.preventDefault();
                return false;
            }
            // Ctrl+S (Save Page)
            if (e.ctrlKey && e.keyCode === 83) {
                e.preventDefault();
                return false;
            }
            // Ctrl+Shift+C (Inspect Element)
            if (e.ctrlKey && e.shiftKey && e.keyCode === 67) {
                e.preventDefault();
                return false;
            }
            // Ctrl+Shift+J (Console)
            if (e.ctrlKey && e.shiftKey && e.keyCode === 74) {
                e.preventDefault();
                return false;
            }
            // Ctrl+A (Select All)
            if (e.ctrlKey && e.keyCode === 65) {
                e.preventDefault();
                return false;
            }
            // Ctrl+P (Print)
            if (e.ctrlKey && e.keyCode === 80) {
                e.preventDefault();
                return false;
            }
        });

        // Disable text selection
        document.addEventListener('selectstart', function(e) {
            e.preventDefault();
            return false;
        });

        // Disable drag and drop
        document.addEventListener('dragstart', function(e) {
            e.preventDefault();
            return false;
        });

        // Clear console periodically
        setInterval(function() {
            console.clear();
        }, 1000);

        // Detect developer tools
        let devtools = {
            open: false,
            orientation: null
        };

        const threshold = 160;

        setInterval(function() {
            if (window.outerHeight - window.innerHeight > threshold ||
                window.outerWidth - window.innerWidth > threshold) {
                if (!devtools.open) {
                    devtools.open = true;
                    // Show warning when dev tools detected
                    document.body.innerHTML = '<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:#000;color:#fff;display:flex;align-items:center;justify-content:center;font-size:24px;z-index:9999;font-family:Arial,sans-serif;"><div style="text-align:center;"><div style="font-size:48px;margin-bottom:20px;color:#ff4444;">🛡️</div>Access Denied<br><small style="font-size:16px;opacity:0.8;">Developer Tools Detected</small></div></div>';
                }
            } else {
                devtools.open = false;
            }
        }, 500);

        // Console warning
        console.log('%cSTOP!', 'color: red; font-size: 50px; font-weight: bold;');
        console.log('%cThis is a browser feature intended for developers. If someone told you to copy-paste something here, it is a scam and will give them access to your account.', 'color: red; font-size: 16px;');
        console.log('%cSee https://en.wikipedia.org/wiki/Self-XSS for more information.', 'color: red; font-size: 16px;');
    </script>

    <!-- Security Protection Script -->
    <script src="../public/assets/js/security-protection.js"></script>
</body>

</html>