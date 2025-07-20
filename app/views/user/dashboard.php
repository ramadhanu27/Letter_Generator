<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../app/models/User.php';

// Require login
User::requireLogin();

$current_user = User::getCurrentUser();
$user = new User();
$user_data = $user->getUserById($current_user['id']);

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $user->logout();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Indonesian PDF Letter Generator</title>
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
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-file-pdf text-2xl text-white mr-3"></i>
                        <span class="text-white text-xl font-bold">Letter Generator</span>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="text-white">
                        <span class="text-sm">Selamat datang, </span>
                        <span class="font-semibold"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                    </div>
                    
                    <div class="relative">
                        <button onclick="toggleDropdown()" class="flex items-center text-white hover:text-blue-200 transition-colors">
                            <i class="fas fa-user-circle text-2xl"></i>
                            <i class="fas fa-chevron-down ml-1 text-sm"></i>
                        </button>
                        
                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                            <a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-blue-50 transition-colors">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>
                            <a href="settings.php" class="block px-4 py-2 text-gray-800 hover:bg-blue-50 transition-colors">
                                <i class="fas fa-cog mr-2"></i>Pengaturan
                            </a>
                            <hr class="my-2">
                            <a href="?action=logout" class="block px-4 py-2 text-red-600 hover:bg-red-50 transition-colors">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard</h1>
                    <p class="text-gray-600">Kelola surat dan template Anda dengan mudah</p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-blue-100 p-4 rounded-lg">
                        <i class="fas fa-file-pdf text-4xl text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <!-- Create New Letter -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer" onclick="window.location.href='app.php'">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-plus text-2xl text-blue-600"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Buat Surat Baru</h3>
                <p class="text-gray-600">Mulai membuat surat pernyataan, izin, atau kuasa</p>
            </div>

            <!-- My Templates -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer" onclick="window.location.href='templates.php'">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-bookmark text-2xl text-green-600"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Template Saya</h3>
                <p class="text-gray-600">Kelola template yang sering digunakan</p>
            </div>

            <!-- History -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer" onclick="window.location.href='history.php'">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-history text-2xl text-purple-600"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Riwayat Surat</h3>
                <p class="text-gray-600">Lihat surat yang pernah dibuat</p>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-file-alt text-xl text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Surat</p>
                        <p class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-bookmark text-xl text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Template</p>
                        <p class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-yellow-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-calendar text-xl text-yellow-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Bulan Ini</p>
                        <p class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-purple-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-download text-xl text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Download</p>
                        <p class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">
                <i class="fas fa-clock mr-2 text-gray-600"></i>Aktivitas Terbaru
            </h3>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-2 rounded-lg mr-4">
                            <i class="fas fa-user-plus text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Akun berhasil dibuat</p>
                            <p class="text-sm text-gray-600">Selamat datang di Letter Generator!</p>
                        </div>
                    </div>
                    <span class="text-sm text-gray-500">Baru saja</span>
                </div>
                
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-4"></i>
                    <p>Belum ada aktivitas lainnya</p>
                    <p class="text-sm">Mulai buat surat pertama Anda!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <p class="text-gray-600">&copy; 2025 Indonesian PDF Letter Generator. All rights reserved.</p>
                <div class="flex space-x-4">
                    <a href="help.php" class="text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-question-circle mr-1"></i>Bantuan
                    </a>
                    <a href="contact.php" class="text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-envelope mr-1"></i>Kontak
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const button = event.target.closest('button');
            
            if (!button || !button.onclick) {
                dropdown.classList.add('hidden');
            }
        });

        // Add smooth transitions
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.hover\\:shadow-xl');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });

        // Welcome animation
        setTimeout(() => {
            const welcomeSection = document.querySelector('.bg-white.rounded-xl.shadow-lg.p-6.mb-8');
            welcomeSection.style.animation = 'fadeIn 0.6s ease-out';
        }, 100);
    </script>
</body>
</html>
