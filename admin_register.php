<?php
session_start();
require_once 'config/database.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $admin_key = $_POST['admin_key'] ?? '';

    // Admin key validation (simple security measure)
    $valid_admin_key = 'ADMIN2024KEY'; // Change this to a secure key

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($admin_key)) {
        $error_message = 'Semua field wajib diisi.';
    } elseif ($admin_key !== $valid_admin_key) {
        $error_message = 'Kunci admin tidak valid. Hubungi administrator sistem.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 8) {
        $error_message = 'Password minimal 8 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Format email tidak valid.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_message = 'Username hanya boleh mengandung huruf, angka, dan underscore.';
    } else {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            // Check if username or email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->fetch()) {
                $error_message = 'Username atau email sudah digunakan.';
            } else {
                $conn->beginTransaction();

                // Hash password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert admin user
                $stmt = $conn->prepare("
                    INSERT INTO users (username, email, password_hash, full_name, role, is_active, email_verified, created_at) 
                    VALUES (?, ?, ?, ?, 'admin', 1, 1, NOW())
                ");
                $stmt->execute([$username, $email, $password_hash, $full_name]);
                $admin_id = $conn->lastInsertId();

                // Try to insert admin profile if table exists
                try {
                    $check_stmt = $conn->query("SHOW TABLES LIKE 'user_profiles'");
                    if ($check_stmt->rowCount() > 0) {
                        $stmt = $conn->prepare("
                            INSERT INTO user_profiles (user_id, organization, position, created_at) 
                            VALUES (?, 'Indonesian PDF Letter Generator', 'Administrator', NOW())
                        ");
                        $stmt->execute([$admin_id]);
                    }
                } catch (Exception $profile_error) {
                    // Continue without profile if table doesn't exist
                }

                // Log admin creation
                try {
                    $stmt = $conn->prepare("
                        INSERT INTO admin_logs (admin_id, action, target_type, target_id, description, ip_address, created_at) 
                        VALUES (?, 'admin_created', 'user', ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $admin_id,
                        $admin_id,
                        "New admin account created: $username",
                        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    ]);
                } catch (Exception $log_error) {
                    // Continue without logging if table doesn't exist
                }

                $conn->commit();

                $success_message = "Admin berhasil dibuat!<br><br>
                    <strong>Username:</strong> $username<br>
                    <strong>Email:</strong> $email<br>
                    <strong>Nama:</strong> $full_name<br><br>
                    <a href='admin_login.php' class='inline-block bg-violet-600 text-white px-4 py-2 rounded-lg hover:bg-violet-700 transition-colors'>
                        <i class='fas fa-sign-in-alt mr-2'></i>Login Sekarang
                    </a>";

                // Clear form
                $username = $email = $full_name = $password = $confirm_password = $admin_key = '';
            }
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            $error_message = 'Terjadi kesalahan sistem: ' . $e->getMessage();
            error_log("Admin registration error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Admin Baru - Indonesian PDF Letter Generator</title>
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

        .strength-weak {
            color: #ef4444;
        }

        .strength-medium {
            color: #f59e0b;
        }

        .strength-strong {
            color: #10b981;
        }
    </style>
</head>

<body class="admin-gradient admin-pattern min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-lg w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="floating-animation">
                    <div class="w-24 h-24 bg-violet-900 bg-opacity-60 backdrop-blur-sm rounded-full mx-auto mb-6 flex items-center justify-center pulse-glow">
                        <i class="fas fa-user-plus text-4xl text-violet-200"></i>
                    </div>
                </div>
                <h2 class="text-4xl font-bold text-white mb-2">Buat Admin Baru</h2>
                <p class="text-violet-200 text-lg">Indonesian PDF Letter Generator</p>
                <div class="mt-4 flex items-center justify-center space-x-2 text-violet-300">
                    <i class="fas fa-shield-alt text-sm"></i>
                    <span class="text-sm">Administrator Registration</span>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="bg-gray-900 bg-opacity-80 backdrop-blur-md rounded-2xl shadow-2xl p-8 border border-violet-500 border-opacity-30">
                <!-- Messages -->
                <?php if ($error_message): ?>
                    <div class="mb-6 bg-red-900 bg-opacity-40 backdrop-blur-sm border border-red-700 border-opacity-50 text-red-200 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle mr-3"></i>
                            <span><?php echo $error_message; ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="mb-6 bg-green-900 bg-opacity-40 backdrop-blur-sm border border-green-700 border-opacity-50 text-green-200 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3"></i>
                            <div><?php echo $success_message; ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-200 mb-2">
                                <i class="fas fa-user mr-2"></i>Nama Lengkap
                            </label>
                            <input type="text" name="full_name" id="full_name" required
                                value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                                class="w-full px-4 py-3 bg-gray-800 bg-opacity-70 backdrop-blur-sm border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-violet-400 focus:border-violet-400 transition-all duration-200"
                                placeholder="Nama lengkap admin">
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-200 mb-2">
                                <i class="fas fa-at mr-2"></i>Username
                            </label>
                            <input type="text" name="username" id="username" required
                                value="<?php echo htmlspecialchars($username ?? ''); ?>"
                                class="w-full px-4 py-3 bg-gray-800 bg-opacity-70 backdrop-blur-sm border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-violet-400 focus:border-violet-400 transition-all duration-200"
                                placeholder="Username admin">
                            <p class="text-xs text-gray-400 mt-1">Hanya huruf, angka, dan underscore</p>
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-200 mb-2">
                            <i class="fas fa-envelope mr-2"></i>Email
                        </label>
                        <input type="email" name="email" id="email" required
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                            class="w-full px-4 py-3 bg-gray-800 bg-opacity-70 backdrop-blur-sm border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-violet-400 focus:border-violet-400 transition-all duration-200"
                            placeholder="admin@example.com">
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-200 mb-2">
                                <i class="fas fa-lock mr-2"></i>Password
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="password" required
                                    class="w-full px-4 py-3 bg-gray-800 bg-opacity-70 backdrop-blur-sm border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-violet-400 focus:border-violet-400 transition-all duration-200"
                                    placeholder="Minimal 8 karakter">
                                <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                                    <i class="fas fa-eye" id="passwordToggle"></i>
                                </button>
                            </div>
                            <div id="passwordStrength" class="text-xs mt-1"></div>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-200 mb-2">
                                <i class="fas fa-lock mr-2"></i>Konfirmasi Password
                            </label>
                            <div class="relative">
                                <input type="password" name="confirm_password" id="confirm_password" required
                                    class="w-full px-4 py-3 bg-gray-800 bg-opacity-70 backdrop-blur-sm border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-violet-400 focus:border-violet-400 transition-all duration-200"
                                    placeholder="Ulangi password">
                                <button type="button" onclick="togglePassword('confirm_password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                                    <i class="fas fa-eye" id="confirmPasswordToggle"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="text-xs mt-1"></div>
                        </div>
                    </div>

                    <div>
                        <label for="admin_key" class="block text-sm font-medium text-gray-200 mb-2">
                            <i class="fas fa-key mr-2"></i>Kunci Admin
                        </label>
                        <input type="password" name="admin_key" id="admin_key" required
                            class="w-full px-4 py-3 bg-gray-800 bg-opacity-70 backdrop-blur-sm border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-violet-400 focus:border-violet-400 transition-all duration-200"
                            placeholder="Masukkan kunci admin">
                        <p class="text-xs text-gray-400 mt-1">Hubungi administrator sistem untuk mendapatkan kunci ini</p>
                    </div>

                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-violet-600 hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 transition-all duration-200 transform hover:scale-105">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="fas fa-user-plus text-violet-200 group-hover:text-white"></i>
                            </span>
                            Buat Admin Baru
                        </button>
                    </div>
                </form>

                <!-- Additional Links -->
                <div class="mt-8 text-center space-y-3">
                    <div class="border-t border-gray-600 border-opacity-40 pt-6">
                        <p class="text-gray-300 text-sm mb-3">Sudah punya akun admin?</p>
                        <div class="space-y-2">
                            <a href="admin_login.php" class="block text-gray-400 hover:text-violet-300 text-sm transition-colors">
                                <i class="fas fa-sign-in-alt mr-2"></i>Login Admin
                            </a>
                            <a href="login.php" class="block text-gray-400 hover:text-violet-300 text-sm transition-colors">
                                <i class="fas fa-user mr-2"></i>Login sebagai User Biasa
                            </a>
                            <a href="index.php" class="block text-gray-400 hover:text-violet-300 text-sm transition-colors">
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
                        <span class="text-sm">Registrasi Admin Memerlukan Kunci Khusus</span>
                    </div>
                    <p class="text-xs text-yellow-300 mt-2">
                        Hanya administrator sistem yang memiliki kunci dapat membuat akun admin baru
                    </p>
                </div>
            </div>

            <!-- Current Admins Info -->
            <div class="bg-gray-900 bg-opacity-60 backdrop-blur-md rounded-xl border border-gray-600 border-opacity-30 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">
                    <i class="fas fa-users-cog mr-2 text-violet-400"></i>Admin yang Terdaftar
                </h3>
                <?php
                try {
                    $database = new Database();
                    $conn = $database->getConnection();
                    $stmt = $conn->query("SELECT username, email, full_name, created_at FROM users WHERE role = 'admin' ORDER BY created_at DESC LIMIT 5");
                    $admins = $stmt->fetchAll();

                    if ($admins) {
                        echo '<div class="space-y-3">';
                        foreach ($admins as $admin) {
                            echo '<div class="flex items-center justify-between bg-gray-800 bg-opacity-50 p-3 rounded-lg border border-gray-700">';
                            echo '<div>';
                            echo '<div class="text-white font-medium">' . htmlspecialchars($admin['full_name']) . '</div>';
                            echo '<div class="text-gray-400 text-sm">' . htmlspecialchars($admin['username']) . ' • ' . htmlspecialchars($admin['email']) . '</div>';
                            echo '</div>';
                            echo '<div class="text-gray-500 text-xs">' . date('d M Y', strtotime($admin['created_at'])) . '</div>';
                            echo '</div>';
                        }
                        echo '</div>';

                        $stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
                        $total = $stmt->fetch()['total'];
                        if ($total > 5) {
                            echo '<p class="text-gray-400 text-sm mt-3 text-center">Dan ' . ($total - 5) . ' admin lainnya...</p>';
                        }
                    } else {
                        echo '<div class="text-center py-4">';
                        echo '<i class="fas fa-user-slash text-4xl text-gray-600 mb-2"></i>';
                        echo '<p class="text-gray-400">Belum ada admin yang terdaftar</p>';
                        echo '</div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="text-center py-4">';
                    echo '<i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-2"></i>';
                    echo '<p class="text-red-400 text-sm">Error loading admin list</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggle = document.getElementById(fieldId + 'Toggle');

            if (field.type === 'password') {
                field.type = 'text';
                toggle.classList.remove('fa-eye');
                toggle.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                toggle.classList.remove('fa-eye-slash');
                toggle.classList.add('fa-eye');
            }
        }

        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            const strength = getPasswordStrength(password);

            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }

            let strengthText = '';
            let strengthClass = '';

            if (strength < 3) {
                strengthText = 'Lemah';
                strengthClass = 'strength-weak';
            } else if (strength < 5) {
                strengthText = 'Sedang';
                strengthClass = 'strength-medium';
            } else {
                strengthText = 'Kuat';
                strengthClass = 'strength-strong';
            }

            strengthDiv.innerHTML = `<span class="${strengthClass}">Kekuatan password: ${strengthText}</span>`;
        });

        // Password match checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('passwordMatch');

            if (confirmPassword.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }

            if (password === confirmPassword) {
                matchDiv.innerHTML = '<span class="strength-strong">Password cocok</span>';
            } else {
                matchDiv.innerHTML = '<span class="strength-weak">Password tidak cocok</span>';
            }
        });

        function getPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            return strength;
        }

        // Auto-generate username from full name
        document.getElementById('full_name').addEventListener('input', function() {
            const fullName = this.value.toLowerCase();
            const username = fullName.replace(/\s+/g, '').replace(/[^a-z0-9]/g, '');
            const usernameField = document.getElementById('username');

            if (username && !usernameField.value) {
                usernameField.value = username;
            }
        });

        // Auto-focus on first input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('full_name').focus();
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return false;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Password minimal 8 karakter!');
                return false;
            }
        });

        // Add interactive effects
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