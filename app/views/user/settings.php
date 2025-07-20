<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../app/models/User.php';

// Require login
User::requireLogin();

$current_user = User::getCurrentUser();
$user = new User();
$user_data = $user->getUserById($current_user['id']);

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Token keamanan tidak valid. Silakan refresh halaman.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $data = [
                'full_name' => sanitizeInput($_POST['full_name'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'organization' => sanitizeInput($_POST['organization'] ?? ''),
                'position' => sanitizeInput($_POST['position'] ?? ''),
                'address' => sanitizeInput($_POST['address'] ?? ''),
                'city' => sanitizeInput($_POST['city'] ?? ''),
                'province' => sanitizeInput($_POST['province'] ?? ''),
                'postal_code' => sanitizeInput($_POST['postal_code'] ?? '')
            ];
            
            $result = $user->updateProfile($current_user['id'], $data);
            if ($result['success']) {
                $success_message = $result['message'];
                // Update session data
                $_SESSION['full_name'] = $data['full_name'];
                $user_data = $user->getUserById($current_user['id']); // Refresh data
            } else {
                $error_message = $result['message'];
            }
        } elseif ($action === 'change_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if ($new_password !== $confirm_password) {
                $error_message = 'Konfirmasi password baru tidak cocok.';
            } else {
                $result = $user->changePassword($current_user['id'], $current_password, $new_password);
                if ($result['success']) {
                    $success_message = $result['message'];
                } else {
                    $error_message = $result['message'];
                }
            }
        } elseif ($action === 'update_preferences') {
            $preferences = [
                'theme' => $_POST['theme'] ?? 'light',
                'language' => $_POST['language'] ?? 'id',
                'notifications' => isset($_POST['notifications']),
                'auto_save' => isset($_POST['auto_save']),
                'default_letter_type' => $_POST['default_letter_type'] ?? 'pernyataan'
            ];
            
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                $stmt = $conn->prepare("UPDATE user_profiles SET preferences = ? WHERE user_id = ?");
                $result = $stmt->execute([json_encode($preferences), $current_user['id']]);
                
                if ($result) {
                    $success_message = 'Preferensi berhasil diperbarui.';
                    $user_data = $user->getUserById($current_user['id']); // Refresh data
                } else {
                    $error_message = 'Gagal memperbarui preferensi.';
                }
            } catch (Exception $e) {
                $error_message = 'Terjadi kesalahan sistem.';
            }
        }
    }
}

$csrf_token = generateCSRFToken();
$preferences = json_decode($user_data['preferences'] ?? '{}', true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Indonesian PDF Letter Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="flex-shrink-0 flex items-center">
                        <i class="fas fa-file-pdf text-2xl text-white mr-3"></i>
                        <span class="text-white text-xl font-bold">Letter Generator</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-white hover:text-blue-200 transition-colors">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                    <a href="app.php" class="text-white hover:text-blue-200 transition-colors">
                        <i class="fas fa-edit mr-1"></i>Buat Surat
                    </a>
                    <div class="text-white">
                        <span class="text-sm">Halo, </span>
                        <span class="font-semibold"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                    </div>
                    <a href="dashboard.php?action=logout" class="text-white hover:text-red-200 transition-colors">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-cog mr-3 text-blue-600"></i>Pengaturan
            </h1>
            <p class="text-gray-600 mt-2">Kelola profil dan preferensi akun Anda</p>
        </div>

        <!-- Messages -->
        <?php if ($success_message): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Settings Tabs -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button onclick="showTab('profile')" id="tab-profile" class="tab-button border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600">
                        <i class="fas fa-user mr-2"></i>Profil
                    </button>
                    <button onclick="showTab('password')" id="tab-password" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700">
                        <i class="fas fa-lock mr-2"></i>Kata Sandi
                    </button>
                    <button onclick="showTab('preferences')" id="tab-preferences" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700">
                        <i class="fas fa-sliders-h mr-2"></i>Preferensi
                    </button>
                </nav>
            </div>

            <!-- Profile Tab -->
            <div id="content-profile" class="tab-content p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Informasi Profil</h2>
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100" readonly>
                            <p class="text-xs text-gray-500 mt-1">Email tidak dapat diubah</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Organisasi/Perusahaan</label>
                            <input type="text" name="organization" value="<?php echo htmlspecialchars($user_data['organization'] ?? ''); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jabatan</label>
                            <input type="text" name="position" value="<?php echo htmlspecialchars($user_data['position'] ?? ''); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kota</label>
                            <input type="text" name="city" value="<?php echo htmlspecialchars($user_data['city'] ?? ''); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                        <textarea name="address" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Provinsi</label>
                            <input type="text" name="province" value="<?php echo htmlspecialchars($user_data['province'] ?? ''); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kode Pos</label>
                            <input type="text" name="postal_code" value="<?php echo htmlspecialchars($user_data['postal_code'] ?? ''); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Tab -->
            <div id="content-password" class="tab-content p-6 hidden">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Ubah Kata Sandi</h2>
                <form method="POST" class="space-y-6 max-w-md">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kata Sandi Baru</label>
                        <input type="password" name="new_password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter dengan huruf besar, kecil, dan angka</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="confirm_password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-key mr-2"></i>Ubah Kata Sandi
                        </button>
                    </div>
                </form>
            </div>

            <!-- Preferences Tab -->
            <div id="content-preferences" class="tab-content p-6 hidden">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Preferensi Aplikasi</h2>
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="update_preferences">
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tema</label>
                            <select name="theme" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="light" <?php echo ($preferences['theme'] ?? 'light') === 'light' ? 'selected' : ''; ?>>Terang</option>
                                <option value="dark" <?php echo ($preferences['theme'] ?? 'light') === 'dark' ? 'selected' : ''; ?>>Gelap</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bahasa</label>
                            <select name="language" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="id" <?php echo ($preferences['language'] ?? 'id') === 'id' ? 'selected' : ''; ?>>Bahasa Indonesia</option>
                                <option value="en" <?php echo ($preferences['language'] ?? 'id') === 'en' ? 'selected' : ''; ?>>English</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Surat Default</label>
                            <select name="default_letter_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="pernyataan" <?php echo ($preferences['default_letter_type'] ?? 'pernyataan') === 'pernyataan' ? 'selected' : ''; ?>>Surat Pernyataan</option>
                                <option value="izin" <?php echo ($preferences['default_letter_type'] ?? 'pernyataan') === 'izin' ? 'selected' : ''; ?>>Surat Izin</option>
                                <option value="kuasa" <?php echo ($preferences['default_letter_type'] ?? 'pernyataan') === 'kuasa' ? 'selected' : ''; ?>>Surat Kuasa</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="notifications" id="notifications" 
                                   <?php echo ($preferences['notifications'] ?? true) ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="notifications" class="ml-2 block text-sm text-gray-900">
                                Aktifkan notifikasi
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="auto_save" id="auto_save" 
                                   <?php echo ($preferences['auto_save'] ?? false) ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="auto_save" class="ml-2 block text-sm text-gray-900">
                                Simpan otomatis draft surat
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Simpan Preferensi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active state from all tabs
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected tab content
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            // Add active state to selected tab
            const activeTab = document.getElementById('tab-' + tabName);
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-blue-500', 'text-blue-600');
        }
    </script>
</body>
</html>
