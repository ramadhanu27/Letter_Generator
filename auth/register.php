<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/User.php';

// Redirect if already logged in
if (User::isLoggedIn()) {
    header('Location: /surat/dashboard');
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
        /* Indonesian Professional Color Scheme - Darker Palette */
        :root {
            --primary-blue: #1E40AF;
            --primary-navy: #1E293B;
            --dark-navy: #0F172A;
            --indonesian-red: #B91C1C;
            --golden-yellow: #D97706;
            --emerald-green: #047857;
            --slate-gray: #334155;
            --cool-gray: #475569;
            --light-gray: #F1F5F9;
            --warm-orange: #C2410C;
            --success-green: #15803D;
            --warning-amber: #B45309;
            --info-blue: #0369A1;
            --white: #FFFFFF;
            --black: #0F172A;
            --text-primary: #F8FAFC;
            --text-secondary: #E2E8F0;
        }

        .gradient-bg {
            /* Darker gradient for better contrast */
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 30%, #334155 70%, #475569 100%);
            position: relative;
            min-height: 100vh;
        }

        .gradient-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(30, 41, 59, 0.6) 50%, rgba(51, 65, 85, 0.4) 100%);
            pointer-events: none;
        }

        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='3'/%3E%3Ccircle cx='10' cy='10' r='2'/%3E%3Ccircle cx='50' cy='50' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .glass-effect {
            backdrop-filter: blur(16px);
            background: rgba(248, 250, 252, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        }

        .register-container {
            position: relative;
            z-index: 10;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1E40AF, #1E293B);
            color: var(--white);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1D4ED8, #0F172A);
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(30, 64, 175, 0.4);
            border-color: #3B82F6;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #F59E0B, #D97706);
            color: var(--white);
            font-weight: 700;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #EAB308, #CA8A04);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(245, 158, 11, 0.4);
            border-color: #FCD34D;
        }

        .input-field {
            background: rgba(248, 250, 252, 0.9);
            border: 2px solid rgba(51, 65, 85, 0.2);
            color: var(--slate-gray);
            transition: all 0.3s ease;
        }

        .input-field:focus {
            background: rgba(255, 255, 255, 0.95);
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
            outline: none;
        }

        .text-shadow {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body class="gradient-bg hero-pattern min-h-screen py-8 px-4">
    <div class="max-w-2xl mx-auto register-container">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full shadow-2xl mb-6" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                <i class="fas fa-file-pdf text-3xl text-white"></i>
            </div>
            <h1 class="text-4xl font-bold mb-3 text-shadow" style="color: var(--text-primary);">Letter Generator</h1>
            <p class="text-xl" style="color: var(--text-secondary);">Buat akun baru untuk mulai membuat surat</p>
        </div>

        <!-- Registration Form -->
        <div class="glass-effect rounded-3xl p-10 shadow-2xl border-2 border-white border-opacity-20">
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
                        <a href="/surat/login" class="inline-flex items-center px-6 py-3 rounded-xl font-bold transition-all duration-200 transform hover:scale-105" style="background: linear-gradient(135deg, #10B981, #059669); color: white; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);">
                            <i class="fas fa-sign-in-alt mr-3"></i>Login Sekarang
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <!-- Account Information -->
                <div class="space-y-6">
                    <h3 class="text-xl font-bold border-b-2 border-gray-300 pb-3" style="color: var(--slate-gray);">
                        <i class="fas fa-user-circle mr-3" style="color: var(--primary-blue);"></i>Informasi Akun
                    </h3>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-sm font-semibold mb-3" style="color: var(--slate-gray);">
                                <i class="fas fa-user mr-2" style="color: var(--primary-blue);"></i>Username *
                            </label>
                            <input type="text"
                                id="username"
                                name="username"
                                value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>"
                                class="input-field w-full px-5 py-4 rounded-xl text-lg font-medium placeholder-gray-400"
                                placeholder="Masukkan username"
                                required>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-semibold mb-3" style="color: var(--slate-gray);">
                                <i class="fas fa-envelope mr-2" style="color: var(--primary-blue);"></i>Email *
                            </label>
                            <input type="email"
                                id="email"
                                name="email"
                                value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                                class="input-field w-full px-5 py-4 rounded-xl text-lg font-medium placeholder-gray-400"
                                placeholder="Masukkan email"
                                required>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-semibold mb-3" style="color: var(--slate-gray);">
                                <i class="fas fa-lock mr-2" style="color: var(--primary-blue);"></i>Password *
                            </label>
                            <div class="relative">
                                <input type="password"
                                    id="password"
                                    name="password"
                                    class="input-field w-full px-5 py-4 rounded-xl text-lg font-medium placeholder-gray-400 pr-14"
                                    placeholder="Minimal 8 karakter"
                                    required>
                                <button type="button"
                                    onclick="togglePassword('password')"
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 transition-colors"
                                    style="color: var(--slate-gray);">
                                    <i id="password-icon" class="fas fa-eye text-xl"></i>
                                </button>
                            </div>
                            <p class="text-xs mt-2" style="color: var(--slate-gray);">Minimal 8 karakter dengan huruf besar, kecil, dan angka</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="confirm_password" class="block text-sm font-semibold mb-3" style="color: var(--slate-gray);">
                                <i class="fas fa-lock mr-2" style="color: var(--primary-blue);"></i>Konfirmasi Password *
                            </label>
                            <div class="relative">
                                <input type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    class="input-field w-full px-5 py-4 rounded-xl text-lg font-medium placeholder-gray-400 pr-14"
                                    placeholder="Ulangi password"
                                    required>
                                <button type="button"
                                    onclick="togglePassword('confirm_password')"
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 transition-colors"
                                    style="color: var(--slate-gray);">
                                    <i id="confirm-password-icon" class="fas fa-eye text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="space-y-6">
                    <h3 class="text-xl font-bold border-b-2 border-gray-300 pb-3" style="color: var(--slate-gray);">
                        <i class="fas fa-id-card mr-3" style="color: var(--primary-blue);"></i>Informasi Pribadi
                    </h3>

                    <!-- Full Name -->
                    <div>
                        <label for="full_name" class="block text-sm font-semibold mb-3" style="color: var(--slate-gray);">
                            <i class="fas fa-user mr-2" style="color: var(--primary-blue);"></i>Nama Lengkap *
                        </label>
                        <input type="text"
                            id="full_name"
                            name="full_name"
                            value="<?php echo htmlspecialchars($form_data['full_name'] ?? ''); ?>"
                            class="input-field w-full px-5 py-4 rounded-xl text-lg font-medium placeholder-gray-400"
                            placeholder="Masukkan nama lengkap"
                            required>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-semibold mb-3" style="color: var(--slate-gray);">
                                <i class="fas fa-phone mr-2" style="color: var(--primary-blue);"></i>Nomor Telepon
                            </label>
                            <input type="tel"
                                id="phone"
                                name="phone"
                                value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                                class="input-field w-full px-5 py-4 rounded-xl text-lg font-medium placeholder-gray-400"
                                placeholder="08xxxxxxxxxx">
                        </div>

                        <!-- Organization -->
                        <div>
                            <label for="organization" class="block text-sm font-semibold mb-3" style="color: var(--slate-gray);">
                                <i class="fas fa-building mr-2" style="color: var(--primary-blue);"></i>Organisasi/Perusahaan
                            </label>
                            <input type="text"
                                id="organization"
                                name="organization"
                                value="<?php echo htmlspecialchars($form_data['organization'] ?? ''); ?>"
                                class="input-field w-full px-5 py-4 rounded-xl text-lg font-medium placeholder-gray-400"
                                placeholder="PT ABC, Universitas XYZ">
                        </div>
                    </div>

                    <!-- Position -->
                    <div>
                        <label for="position" class="block text-sm font-semibold mb-3" style="color: var(--slate-gray);">
                            <i class="fas fa-briefcase mr-2" style="color: var(--primary-blue);"></i>Jabatan/Posisi
                        </label>
                        <input type="text"
                            id="position"
                            name="position"
                            value="<?php echo htmlspecialchars($form_data['position'] ?? ''); ?>"
                            class="input-field w-full px-5 py-4 rounded-xl text-lg font-medium placeholder-gray-400"
                            placeholder="Manager, Mahasiswa, Karyawan">
                    </div>
                </div>

                <!-- Address Information -->
                <div class="space-y-6">
                    <h3 class="text-xl font-bold border-b-2 border-gray-300 pb-3" style="color: var(--slate-gray);">
                        <i class="fas fa-map-marker-alt mr-3" style="color: var(--primary-blue);"></i>Informasi Alamat (Opsional)
                    </h3>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-semibold mb-3" style="color: var(--slate-gray);">
                            <i class="fas fa-home mr-2" style="color: var(--primary-blue);"></i>Alamat
                        </label>
                        <textarea id="address"
                            name="address"
                            rows="3"
                            class="input-field w-full px-5 py-4 rounded-xl text-lg font-medium placeholder-gray-400 resize-none"
                            placeholder="Jl. Contoh No. 123, RT/RW"><?php echo htmlspecialchars($form_data['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- City -->
                        <div>
                            <label for="city" class="block text-sm font-semibold mb-3" style="color: var(--slate-gray);">
                                <i class="fas fa-city mr-2" style="color: var(--primary-blue);"></i>Kota
                            </label>
                            <input type="text"
                                id="city"
                                name="city"
                                value="<?php echo htmlspecialchars($form_data['city'] ?? ''); ?>"
                                class="input-field w-full px-5 py-4 rounded-xl text-lg font-medium placeholder-gray-400"
                                placeholder="Jakarta, Bandung, Surabaya">
                        </div>

                        <!-- Province -->
                        <div>
                            <label for="province" class="block text-sm font-semibold mb-3" style="color: var(--slate-gray);">
                                <i class="fas fa-map mr-2" style="color: var(--primary-blue);"></i>Provinsi
                            </label>
                            <input type="text"
                                id="province"
                                name="province"
                                value="<?php echo htmlspecialchars($form_data['province'] ?? ''); ?>"
                                class="input-field w-full px-5 py-4 rounded-xl text-lg font-medium placeholder-gray-400"
                                placeholder="DKI Jakarta, Jawa Barat">
                        </div>
                    </div>
                </div>

                <!-- Terms Agreement -->
                <div class="flex items-start space-x-4 p-4 rounded-xl" style="background: rgba(248, 250, 252, 0.1); border: 1px solid rgba(51, 65, 85, 0.2);">
                    <input type="checkbox"
                        id="agree_terms"
                        name="agree_terms"
                        class="mt-1 w-5 h-5 rounded border-2 focus:ring-2 focus:ring-blue-500"
                        style="border-color: var(--primary-blue); accent-color: var(--primary-blue);"
                        required>
                    <label for="agree_terms" class="text-sm font-medium leading-relaxed" style="color: var(--slate-gray);">
                        Saya menyetujui <a href="/surat/terms" class="font-semibold underline transition-colors" style="color: var(--primary-blue);" target="_blank">Syarat dan Ketentuan</a>
                        serta <a href="/surat/privacy" class="font-semibold underline transition-colors" style="color: var(--primary-blue);" target="_blank">Kebijakan Privasi</a>
                    </label>
                </div>

                <!-- Register Button -->
                <button type="submit"
                    class="btn-primary w-full py-4 px-6 rounded-xl text-lg font-bold flex items-center justify-center">
                    <i class="fas fa-user-plus mr-3"></i>Daftar Sekarang
                </button>
            </form>

            <!-- Divider -->
            <div class="my-8 flex items-center">
                <div class="flex-1 border-t-2 border-gray-300"></div>
                <span class="px-6 text-sm font-medium" style="color: var(--slate-gray);">atau</span>
                <div class="flex-1 border-t-2 border-gray-300"></div>
            </div>

            <!-- Login Link -->
            <div class="text-center">
                <p class="mb-4 text-lg" style="color: var(--slate-gray);">Sudah punya akun?</p>
                <a href="/surat/login"
                    class="btn-secondary w-full py-4 px-6 rounded-xl text-lg font-bold flex items-center justify-center">
                    <i class="fas fa-sign-in-alt mr-3"></i>Masuk ke Akun
                </a>
            </div>

            <!-- Back to Home -->
            <div class="text-center mt-8">
                <a href="/surat/home" class="text-sm font-medium transition-colors inline-flex items-center" style="color: var(--slate-gray);" onmouseover="this.style.color='var(--primary-blue)'" onmouseout="this.style.color='var(--slate-gray)'">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-12 text-sm font-medium" style="color: var(--text-secondary);">
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