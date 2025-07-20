<?php
require_once '../config/database.php';
require_once '../app/models/User.php';
require_once '../app/models/PasswordReset.php';

// Redirect if already logged in
if (User::isLoggedIn()) {
    header('Location: ../app/views/user/dashboard.php');
    exit;
}

$token = $_GET['token'] ?? '';
$error_message = '';
$success_message = '';
$valid_token = false;
$user_data = null;

// Verify token
if (empty($token)) {
    $error_message = 'Token reset password tidak ditemukan.';
} else {
    $passwordReset = new PasswordReset();
    $verification = $passwordReset->verifyToken($token);

    if ($verification['success']) {
        $valid_token = true;
        $user_data = $verification['data'];
    } else {
        $error_message = $verification['message'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Token keamanan tidak valid. Silakan coba lagi.';
    } else {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($new_password)) {
            $error_message = 'Password baru harus diisi.';
        } elseif (strlen($new_password) < 6) {
            $error_message = 'Password minimal 6 karakter.';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'Konfirmasi password tidak cocok.';
        } else {
            $result = $passwordReset->resetPassword($token, $new_password);

            if ($result['success']) {
                $success_message = $result['message'];
                $valid_token = false; // Prevent further form submission
            } else {
                $error_message = $result['message'];
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Indonesian PDF Letter Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
                <i class="fas fa-lock text-2xl text-blue-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Reset Password</h1>
            <?php if ($valid_token && $user_data): ?>
                <p class="text-blue-100">Buat password baru untuk akun Anda</p>
            <?php else: ?>
                <p class="text-blue-100">Verifikasi token reset password</p>
            <?php endif; ?>
        </div>

        <!-- Main Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-xl">
            <?php if ($error_message): ?>
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                </div>

                <div class="text-center">
                    <a href="login.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 backdrop-blur-sm border border-white border-opacity-30 inline-block">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Login Sekarang
                    </a>
                </div>
            <?php elseif ($valid_token && $user_data): ?>
                <!-- User Info -->
                <div class="mb-6 p-4 bg-blue-500 bg-opacity-20 backdrop-blur-sm border border-blue-400 border-opacity-50 rounded-lg">
                    <h3 class="font-medium text-blue-100 mb-2">
                        <i class="fas fa-user mr-2"></i>Reset Password untuk:
                    </h3>
                    <p class="text-blue-200 text-sm">
                        <strong><?php echo htmlspecialchars($user_data['full_name']); ?></strong><br>
                        <?php echo htmlspecialchars($user_data['email']); ?>
                    </p>
                </div>

                <form method="POST" class="space-y-6" id="resetForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <!-- New Password -->
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-white mb-2">
                            <i class="fas fa-key mr-2"></i>Password Baru
                        </label>
                        <div class="relative">
                            <input type="password"
                                id="new_password"
                                name="new_password"
                                required
                                minlength="6"
                                class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent pr-12"
                                placeholder="Masukkan password baru">
                            <button type="button" onclick="togglePassword('new_password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-blue-200 hover:text-white">
                                <i class="fas fa-eye" id="new_password_icon"></i>
                            </button>
                        </div>
                        <div class="mt-2">
                            <div class="password-strength bg-gray-300" id="password_strength"></div>
                            <p class="text-xs text-blue-200 mt-1" id="password_feedback">Minimal 6 karakter</p>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-white mb-2">
                            <i class="fas fa-check-double mr-2"></i>Konfirmasi Password
                        </label>
                        <div class="relative">
                            <input type="password"
                                id="confirm_password"
                                name="confirm_password"
                                required
                                class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent pr-12"
                                placeholder="Ulangi password baru">
                            <button type="button" onclick="togglePassword('confirm_password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-blue-200 hover:text-white">
                                <i class="fas fa-eye" id="confirm_password_icon"></i>
                            </button>
                        </div>
                        <p class="text-xs text-blue-200 mt-1" id="confirm_feedback"></p>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        id="submitBtn"
                        class="w-full bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 backdrop-blur-sm border border-white border-opacity-30 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-save mr-2"></i>
                        Reset Password
                    </button>
                </form>
            <?php else: ?>
                <!-- Invalid Token -->
                <div class="text-center">
                    <div class="mb-6">
                        <i class="fas fa-exclamation-triangle text-6xl text-red-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-white mb-2">Token Tidak Valid</h3>
                        <p class="text-blue-200">Token reset password tidak valid atau sudah expired.</p>
                    </div>

                    <a href="forgot_password.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 backdrop-blur-sm border border-white border-opacity-30 inline-block">
                        <i class="fas fa-redo mr-2"></i>
                        Request Reset Lagi
                    </a>
                </div>
            <?php endif; ?>

            <!-- Back to Login -->
            <div class="mt-6 text-center">
                <a href="login.php" class="text-blue-100 hover:text-white transition-colors text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Login
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '_icon');

            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                field.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        // Password strength checker
        document.getElementById('new_password')?.addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('password_strength');
            const feedback = document.getElementById('password_feedback');

            let strength = 0;
            let message = '';

            if (password.length >= 6) strength += 1;
            if (password.length >= 8) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;

            switch (strength) {
                case 0:
                case 1:
                    strengthBar.className = 'password-strength bg-red-400';
                    strengthBar.style.width = '20%';
                    message = 'Password terlalu lemah';
                    break;
                case 2:
                    strengthBar.className = 'password-strength bg-yellow-400';
                    strengthBar.style.width = '40%';
                    message = 'Password lemah';
                    break;
                case 3:
                    strengthBar.className = 'password-strength bg-blue-400';
                    strengthBar.style.width = '60%';
                    message = 'Password sedang';
                    break;
                case 4:
                    strengthBar.className = 'password-strength bg-green-400';
                    strengthBar.style.width = '80%';
                    message = 'Password kuat';
                    break;
                case 5:
                    strengthBar.className = 'password-strength bg-green-500';
                    strengthBar.style.width = '100%';
                    message = 'Password sangat kuat';
                    break;
            }

            feedback.textContent = message;
        });

        // Confirm password checker
        document.getElementById('confirm_password')?.addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirm = this.value;
            const feedback = document.getElementById('confirm_feedback');
            const submitBtn = document.getElementById('submitBtn');

            if (confirm === '') {
                feedback.textContent = '';
                submitBtn.disabled = false;
            } else if (password === confirm) {
                feedback.textContent = '✓ Password cocok';
                feedback.className = 'text-xs text-green-300 mt-1';
                submitBtn.disabled = false;
            } else {
                feedback.textContent = '✗ Password tidak cocok';
                feedback.className = 'text-xs text-red-300 mt-1';
                submitBtn.disabled = true;
            }
        });

        // Form validation
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('new_password').value;
            const confirm = document.getElementById('confirm_password').value;

            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter.');
                return;
            }

            if (password !== confirm) {
                e.preventDefault();
                alert('Konfirmasi password tidak cocok.');
                return;
            }
        });
    </script>
</body>

</html>