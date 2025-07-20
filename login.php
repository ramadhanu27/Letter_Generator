<?php
require_once 'config/database.php';
require_once 'classes/User.php';

// Redirect if already logged in
if (User::isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';
$success_message = '';

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
            $user = new User();
            $result = $user->login($email_or_username, $password, $remember_me);

            if ($result['success']) {
                $redirect_url = $_GET['redirect'] ?? 'dashboard.php';
                header("Location: $redirect_url");
                exit;
            } else {
                $error_message = $result['message'];
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
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
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
    </script>
</body>

</html>