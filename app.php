<?php
require_once 'config/database.php';
require_once 'classes/User.php';

// Require login
User::requireLogin();

$current_user = User::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator Surat PDF - Indonesian PDF Letter Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Form Section -->
            <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                <div class="flex items-center mb-6">
                    <div class="bg-blue-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-edit text-2xl text-blue-600"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800">Form Input</h2>
                </div>

                <form id="form-surat" class="space-y-6">
                    <div class="space-y-3">
                        <label class="flex items-center text-sm font-medium text-gray-700">
                            <i class="fas fa-list-ul mr-2 text-blue-500"></i>
                            Jenis Surat
                        </label>
                        <select id="jenis-surat"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white shadow-sm hover:shadow-md">
                            <option value="pernyataan">📄 Surat Pernyataan</option>
                            <option value="izin">📝 Surat Izin (Universal)</option>
                            <option value="kuasa">🤝 Surat Kuasa</option>
                        </select>
                        
                        <!-- Tombol Contoh untuk Surat Izin -->
                        <div id="contoh-buttons" class="mt-4 hidden">
                            <p class="text-sm text-gray-600 mb-2">💡 Isi dengan contoh data:</p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" id="contoh-karyawan" 
                                    class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition-colors">
                                    👔 Karyawan
                                </button>
                                <button type="button" id="contoh-mahasiswa" 
                                    class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded-full hover:bg-green-200 transition-colors">
                                    🎓 Mahasiswa
                                </button>
                                <button type="button" id="contoh-siswa" 
                                    class="px-3 py-1 text-xs bg-purple-100 text-purple-700 rounded-full hover:bg-purple-200 transition-colors">
                                    📚 Siswa
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="input-fields" class="space-y-4"></div>

                    <div class="space-y-3">
                        <label class="flex items-center text-sm font-medium text-gray-700">
                            <i class="fas fa-paperclip mr-2 text-blue-500"></i>
                            Lampiran (Opsional)
                        </label>
                        <input type="file" id="lampiran" accept="image/*"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white shadow-sm hover:shadow-md">
                        <p class="text-xs text-gray-500">Format: JPG, PNG, GIF. Maksimal 5MB</p>
                    </div>

                    <button type="button" id="generate-btn"
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold py-3 px-6 rounded-lg hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 shadow-lg">
                        <span id="btn-text">
                            <i class="fas fa-file-pdf mr-2"></i>Generate PDF
                        </span>
                        <span id="btn-loading" class="hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Generating...
                        </span>
                    </button>
                </form>
            </div>

            <!-- Preview Section -->
            <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                <div class="flex items-center mb-6">
                    <div class="bg-green-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-eye text-2xl text-green-600"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800">Preview Surat</h2>
                </div>

                <div id="preview-container" class="bg-gray-50 rounded-lg p-6 min-h-96 border-2 border-dashed border-gray-300">
                    <div class="text-center text-gray-500">
                        <i class="fas fa-file-alt text-4xl mb-4"></i>
                        <p class="text-lg font-medium">Preview akan muncul di sini</p>
                        <p class="text-sm">Isi form untuk melihat preview surat</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Info Section -->
        <div class="mt-8 bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-user mr-2 text-blue-600"></i>Informasi Pengguna
            </h3>
            <div class="grid md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Nama:</span>
                    <span class="font-medium ml-2"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                </div>
                <div>
                    <span class="text-gray-600">Email:</span>
                    <span class="font-medium ml-2"><?php echo htmlspecialchars($current_user['email']); ?></span>
                </div>
            </div>
            <div class="mt-4 text-xs text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Informasi ini akan digunakan sebagai data default dalam surat Anda.
                <a href="profile.php" class="text-blue-600 hover:underline ml-1">Edit profil</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="text-center text-gray-600">
                <p>&copy; 2025 Indonesian PDF Letter Generator. All rights reserved.</p>
                <p class="text-sm mt-2">Dibuat dengan ❤️ untuk komunitas Indonesia</p>
            </div>
        </div>
    </footer>

    <!-- Include the original script.js with modifications -->
    <script src="script.js"></script>
    
    <script>
        // Override some functions to work with authenticated user
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-fill user data if available
            const userData = {
                nama: '<?php echo addslashes($current_user['full_name']); ?>',
                email: '<?php echo addslashes($current_user['email']); ?>'
            };

            // Function to auto-fill user data in forms
            function autoFillUserData() {
                const namaField = document.getElementById('nama');
                if (namaField && !namaField.value) {
                    namaField.value = userData.nama;
                }
                
                const emailField = document.getElementById('email');
                if (emailField && !emailField.value) {
                    emailField.value = userData.email;
                }
                
                updatePreview();
            }

            // Override the renderInputs function to auto-fill data
            const originalRenderInputs = window.renderInputs;
            window.renderInputs = function(type) {
                originalRenderInputs(type);
                setTimeout(autoFillUserData, 100);
            };

            // Save generated letter to database (you can implement this)
            const originalGenerateBtn = document.getElementById('generate-btn');
            if (originalGenerateBtn) {
                originalGenerateBtn.addEventListener('click', function() {
                    // You can add code here to save the letter data to database
                    console.log('Letter generated by user:', userData);
                });
            }
        });

        // Add notification system
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                    ${message}
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Show welcome notification
        setTimeout(() => {
            showNotification('Selamat datang! Mulai buat surat Anda.', 'success');
        }, 1000);
    </script>
</body>
</html>
