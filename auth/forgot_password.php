<?php
require_once '../config/database.php';
require_once '../app/models/User.php';
require_once '../app/models/PasswordReset.php';

// Redirect if already logged in
if (User::isLoggedIn()) {
    header('Location: ../app/views/user/dashboard.php');
    exit;
}

$message = '';
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Token keamanan tidak valid. Silakan coba lagi.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');

        if (empty($email)) {
            $error_message = 'Email harus diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Format email tidak valid.';
        } else {
            $passwordReset = new PasswordReset();
            $result = $passwordReset->sendResetToken($email);

            if ($result['success']) {
                $success_message = $result['message'];
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
    <title>Lupa Password - Indonesian PDF Letter Generator</title>
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
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
                <i class="fas fa-key text-2xl text-blue-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Lupa Password</h1>
            <p class="text-blue-100">Masukkan email untuk reset password</p>
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
            <?php endif; ?>

            <?php if (!$success_message): ?>
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-white mb-2">
                            <i class="fas fa-envelope mr-2"></i>Email Address
                        </label>
                        <input type="email"
                            id="email"
                            name="email"
                            required
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent"
                            placeholder="Masukkan email Anda">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 backdrop-blur-sm border border-white border-opacity-30">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Kirim Link Reset Password
                    </button>
                </form>
            <?php endif; ?>

            <!-- Info Box -->
            <div class="mt-6 p-4 bg-blue-500 bg-opacity-20 backdrop-blur-sm border border-blue-400 border-opacity-50 rounded-lg">
                <h3 class="font-medium text-blue-100 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>Informasi
                </h3>
                <ul class="text-sm text-blue-200 space-y-1">
                    <li>• Link reset akan dikirim ke email Anda</li>
                    <li>• Link berlaku selama 1 jam</li>
                    <li>• Periksa folder spam jika tidak ada di inbox</li>
                    <li>• Hanya bisa request reset setiap 5 menit</li>
                </ul>
            </div>

            <!-- Back to Login -->
            <div class="mt-6 text-center">
                <a href="login.php" class="text-blue-100 hover:text-white transition-colors text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Login
                </a>
            </div>
        </div>

        <!-- Additional Links -->
        <div class="mt-8 text-center space-y-2">
            <p class="text-blue-200 text-sm">Belum punya akun?</p>
            <a href="register.php" class="text-white hover:text-blue-200 transition-colors font-medium">
                <i class="fas fa-user-plus mr-2"></i>
                Daftar Sekarang
            </a>
        </div>
    </div>

    <script>
        // Auto-focus on email input
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            if (emailInput && !emailInput.value) {
                emailInput.focus();
            }
        });

        // Form validation
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();

            if (!email) {
                e.preventDefault();
                alert('Email harus diisi.');
                return;
            }

            if (!email.includes('@') || !email.includes('.')) {
                e.preventDefault();
                alert('Format email tidak valid.');
                return;
            }
        });
    </script>
</body>

</html>