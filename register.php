<?php
require_once 'config/database.php';
require_once 'classes/User.php';

// Redirect if already logged in
if (User::isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error_messages = [];
$success_message = '';
$form_data = [];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_messages[] = 'Token keamanan tidak valid. Silakan refresh halaman.';
    } else {
        // Sanitize and collect form data
        $form_data = [
            'username' => sanitizeInput($_POST['username'] ?? ''),
            'email' => sanitizeInput($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'full_name' => sanitizeInput($_POST['full_name'] ?? ''),
            'phone' => sanitizeInput($_POST['phone'] ?? ''),
            'organization' => sanitizeInput($_POST['organization'] ?? ''),
            'position' => sanitizeInput($_POST['position'] ?? ''),
            'address' => sanitizeInput($_POST['address'] ?? ''),
            'city' => sanitizeInput($_POST['city'] ?? ''),
            'province' => sanitizeInput($_POST['province'] ?? '')
        ];

        // Check terms agreement
        if (!isset($_POST['agree_terms'])) {
            $error_messages[] = 'Anda harus menyetujui syarat dan ketentuan';
        }

        if (empty($error_messages)) {
            $user = new User();
            $result = $user->register($form_data);

            if ($result['success']) {
                $success_message = $result['message'];
                $form_data = []; // Clear form data on success
            } else {
                if (isset($result['errors'])) {
                    $error_messages = $result['errors'];
                } else {
                    $error_messages[] = $result['message'];
                }
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
    <title>Daftar - Indonesian PDF Letter Generator</title>
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

<body class="gradient-bg min-h-screen py-8 px-4">
    <div class="max-w-2xl mx-auto">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
                <i class="fas fa-file-pdf text-2xl text-blue-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Letter Generator</h1>
            <p class="text-blue-100">Buat akun baru untuk mulai membuat surat</p>
        </div>

        <!-- Registration Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <?php if (!empty($error_messages)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <strong>Terjadi kesalahan:</strong>
                    </div>
                    <ul class="list-disc list-inside">
                        <?php foreach ($error_messages as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                    <div class="mt-4">
                        <a href="login.php" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login Sekarang
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <!-- Account Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-white border-b border-white border-opacity-30 pb-2">
                        <i class="fas fa-user-circle mr-2"></i>Informasi Akun
                    </h3>

                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-sm font-medium text-white mb-2">
                                <i class="fas fa-user mr-2"></i>Username *
                            </label>
                            <input type="text"
                                id="username"
                                name="username"
                                value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-200"
                                placeholder="Masukkan username"
                                required>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-white mb-2">
                                <i class="fas fa-envelope mr-2"></i>Email *
                            </label>
                            <input type="email"
                                id="email"
                                name="email"
                                value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-200"
                                placeholder="Masukkan email"
                                required>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-white mb-2">
                                <i class="fas fa-lock mr-2"></i>Password *
                            </label>
                            <div class="relative">
                                <input type="password"
                                    id="password"
                                    name="password"
                                    class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-200 pr-12"
                                    placeholder="Minimal 8 karakter"
                                    required>
                                <button type="button"
                                    onclick="togglePassword('password')"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-blue-100 hover:text-white transition-colors">
                                    <i id="password-icon" class="fas fa-eye"></i>
                                </button>
                            </div>
                            <p class="text-xs text-blue-100 mt-1">Minimal 8 karakter dengan huruf besar, kecil, dan angka</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-white mb-2">
                                <i class="fas fa-lock mr-2"></i>Konfirmasi Password *
                            </label>
                            <div class="relative">
                                <input type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-200 pr-12"
                                    placeholder="Ulangi password"
                                    required>
                                <button type="button"
                                    onclick="togglePassword('confirm_password')"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-blue-100 hover:text-white transition-colors">
                                    <i id="confirm-password-icon" class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-white border-b border-white border-opacity-30 pb-2">
                        <i class="fas fa-id-card mr-2"></i>Informasi Pribadi
                    </h3>

                    <!-- Full Name -->
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-white mb-2">
                            <i class="fas fa-user mr-2"></i>Nama Lengkap *
                        </label>
                        <input type="text"
                            id="full_name"
                            name="full_name"
                            value="<?php echo htmlspecialchars($form_data['full_name'] ?? ''); ?>"
                            class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-200"
                            placeholder="Masukkan nama lengkap"
                            required>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-white mb-2">
                                <i class="fas fa-phone mr-2"></i>Nomor Telepon
                            </label>
                            <input type="tel"
                                id="phone"
                                name="phone"
                                value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-200"
                                placeholder="08xxxxxxxxxx">
                        </div>

                        <!-- Organization -->
                        <div>
                            <label for="organization" class="block text-sm font-medium text-white mb-2">
                                <i class="fas fa-building mr-2"></i>Organisasi/Perusahaan
                            </label>
                            <input type="text"
                                id="organization"
                                name="organization"
                                value="<?php echo htmlspecialchars($form_data['organization'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-200"
                                placeholder="PT ABC, Universitas XYZ">
                        </div>
                    </div>

                    <!-- Position -->
                    <div>
                        <label for="position" class="block text-sm font-medium text-white mb-2">
                            <i class="fas fa-briefcase mr-2"></i>Jabatan/Posisi
                        </label>
                        <input type="text"
                            id="position"
                            name="position"
                            value="<?php echo htmlspecialchars($form_data['position'] ?? ''); ?>"
                            class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-200"
                            placeholder="Manager, Mahasiswa, Karyawan">
                    </div>
                </div>

                <!-- Address Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-white border-b border-white border-opacity-30 pb-2">
                        <i class="fas fa-map-marker-alt mr-2"></i>Informasi Alamat (Opsional)
                    </h3>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-white mb-2">
                            <i class="fas fa-home mr-2"></i>Alamat
                        </label>
                        <textarea id="address"
                            name="address"
                            rows="2"
                            class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-200 resize-none"
                            placeholder="Jl. Contoh No. 123, RT/RW"><?php echo htmlspecialchars($form_data['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- City -->
                        <div>
                            <label for="city" class="block text-sm font-medium text-white mb-2">
                                <i class="fas fa-city mr-2"></i>Kota
                            </label>
                            <input type="text"
                                id="city"
                                name="city"
                                value="<?php echo htmlspecialchars($form_data['city'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-200"
                                placeholder="Jakarta, Bandung, Surabaya">
                        </div>

                        <!-- Province -->
                        <div>
                            <label for="province" class="block text-sm font-medium text-white mb-2">
                                <i class="fas fa-map mr-2"></i>Provinsi
                            </label>
                            <input type="text"
                                id="province"
                                name="province"
                                value="<?php echo htmlspecialchars($form_data['province'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent transition-all duration-200"
                                placeholder="DKI Jakarta, Jawa Barat">
                        </div>
                    </div>
                </div>

                <!-- Terms Agreement -->
                <div class="flex items-start space-x-3">
                    <input type="checkbox"
                        id="agree_terms"
                        name="agree_terms"
                        class="mt-1 rounded border-white border-opacity-30 bg-white bg-opacity-20 text-blue-600 focus:ring-white focus:ring-opacity-50"
                        required>
                    <label for="agree_terms" class="text-sm text-white">
                        Saya menyetujui <a href="terms.php" class="text-blue-200 hover:text-white underline" target="_blank">Syarat dan Ketentuan</a>
                        serta <a href="privacy.php" class="text-blue-200 hover:text-white underline" target="_blank">Kebijakan Privasi</a>
                    </label>
                </div>

                <!-- Register Button -->
                <button type="submit"
                    class="w-full bg-white text-blue-600 font-semibold py-3 px-4 rounded-lg hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                </button>
            </form>

            <!-- Divider -->
            <div class="my-6 flex items-center">
                <div class="flex-1 border-t border-white border-opacity-30"></div>
                <span class="px-4 text-sm text-blue-100">atau</span>
                <div class="flex-1 border-t border-white border-opacity-30"></div>
            </div>

            <!-- Login Link -->
            <div class="text-center">
                <p class="text-blue-100 mb-4">Sudah punya akun?</p>
                <a href="login.php"
                    class="inline-flex items-center justify-center w-full bg-transparent border-2 border-white text-white font-semibold py-3 px-4 rounded-lg hover:bg-white hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 transition-all duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk ke Akun
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
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const iconId = fieldId === 'password' ? 'password-icon' : 'confirm-password-icon';
            const passwordIcon = document.getElementById(iconId);

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordField.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthIndicator = document.getElementById('password-strength');

            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            // You can add visual strength indicator here
        });

        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;

            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Password tidak cocok');
            } else {
                this.setCustomValidity('');
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const agreeTerms = document.getElementById('agree_terms').checked;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok');
                return false;
            }

            if (!agreeTerms) {
                e.preventDefault();
                alert('Anda harus menyetujui syarat dan ketentuan');
                return false;
            }

            // Add loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            submitBtn.disabled = true;
        });

        // Auto-hide alerts after 10 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.bg-red-100');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 10000);
    </script>
</body>

</html>