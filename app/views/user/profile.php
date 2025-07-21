<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/surat/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/surat/app/models/User.php';

// Require login
User::requireLogin();

$current_user = User::getCurrentUser();
$user = new User();
$user_data = $user->getUserById($current_user['id']);

// Get user statistics
try {
    $database = new Database();
    $conn = $database->getConnection();

    // Count generated letters
    $stmt = $conn->prepare("SELECT COUNT(*) as total_letters FROM generated_letters WHERE user_id = ?");
    $stmt->execute([$current_user['id']]);
    $letter_stats = $stmt->fetch();

    // Count saved templates
    $stmt = $conn->prepare("SELECT COUNT(*) as total_templates FROM saved_templates WHERE user_id = ?");
    $stmt->execute([$current_user['id']]);
    $template_stats = $stmt->fetch();

    // Get recent activity
    $stmt = $conn->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$current_user['id']]);
    $recent_activities = $stmt->fetchAll();

    // Get letter type statistics
    $stmt = $conn->prepare("
        SELECT letter_type, COUNT(*) as count 
        FROM generated_letters 
        WHERE user_id = ? 
        GROUP BY letter_type
    ");
    $stmt->execute([$current_user['id']]);
    $letter_type_stats = $stmt->fetchAll();
} catch (Exception $e) {
    $letter_stats = ['total_letters' => 0];
    $template_stats = ['total_templates' => 0];
    $recent_activities = [];
    $letter_type_stats = [];
}

$preferences = json_decode($user_data['preferences'] ?? '{}', true);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Indonesian PDF Letter Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card {
            transition: transform 0.2s ease-in-out;
        }

        .stat-card:hover {
            transform: translateY(-2px);
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
                    <a href="settings.php" class="text-white hover:text-blue-200 transition-colors">
                        <i class="fas fa-cog mr-1"></i>Pengaturan
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
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-user-circle mr-3 text-blue-600"></i>Profil Pengguna
            </h1>
            <p class="text-gray-600 mt-2">Informasi akun dan statistik penggunaan</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Profile Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="text-center">
                        <div class="w-24 h-24 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <i class="fas fa-user text-3xl text-white"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($user_data['full_name']); ?></h2>
                        <p class="text-gray-600"><?php echo htmlspecialchars($user_data['email']); ?></p>
                        <?php if ($user_data['position']): ?>
                            <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($user_data['position']); ?></p>
                        <?php endif; ?>
                        <?php if ($user_data['organization']): ?>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($user_data['organization']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="space-y-3">
                            <?php if ($user_data['phone']): ?>
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-phone text-gray-400 w-5"></i>
                                    <span class="ml-2"><?php echo htmlspecialchars($user_data['phone']); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($user_data['city']): ?>
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-map-marker-alt text-gray-400 w-5"></i>
                                    <span class="ml-2"><?php echo htmlspecialchars($user_data['city']); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="flex items-center text-sm">
                                <i class="fas fa-calendar text-gray-400 w-5"></i>
                                <span class="ml-2">Bergabung <?php echo date('d M Y', strtotime($user_data['created_at'])); ?></span>
                            </div>

                            <?php if ($user_data['last_login']): ?>
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-clock text-gray-400 w-5"></i>
                                    <span class="ml-2">Login terakhir <?php echo date('d M Y H:i', strtotime($user_data['last_login'])); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="settings.php" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors text-center block">
                            <i class="fas fa-edit mr-2"></i>Edit Profil
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics and Activity -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Statistics Cards -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="stat-card bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <i class="fas fa-file-pdf text-2xl text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">Total Surat</h3>
                                <p class="text-3xl font-bold text-blue-600"><?php echo $letter_stats['total_letters']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <i class="fas fa-bookmark text-2xl text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">Template Tersimpan</h3>
                                <p class="text-3xl font-bold text-green-600"><?php echo $template_stats['total_templates']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Letter Type Statistics -->
                <?php if (!empty($letter_type_stats)): ?>
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-chart-pie mr-2 text-purple-600"></i>Statistik Jenis Surat
                        </h3>
                        <div class="space-y-3">
                            <?php foreach ($letter_type_stats as $stat): ?>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full mr-3 
                                            <?php
                                            echo $stat['letter_type'] === 'pernyataan' ? 'bg-blue-500' : ($stat['letter_type'] === 'izin' ? 'bg-green-500' : 'bg-purple-500');
                                            ?>">
                                        </div>
                                        <span class="text-sm text-gray-700">
                                            <?php
                                            echo $stat['letter_type'] === 'pernyataan' ? 'Surat Pernyataan' : ($stat['letter_type'] === 'izin' ? 'Surat Izin' : 'Surat Kuasa');
                                            ?>
                                        </span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900"><?php echo $stat['count']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Recent Activity -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-history mr-2 text-orange-600"></i>Aktivitas Terbaru
                    </h3>

                    <?php if (!empty($recent_activities)): ?>
                        <div class="space-y-4">
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-circle text-xs text-gray-400"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-900">
                                            <?php
                                            $action_text = [
                                                'user_login' => 'Login ke sistem',
                                                'user_logout' => 'Logout dari sistem',
                                                'user_registered' => 'Mendaftar akun baru',
                                                'profile_updated' => 'Memperbarui profil',
                                                'password_changed' => 'Mengubah kata sandi',
                                                'letter_generated' => 'Membuat surat baru'
                                            ];
                                            echo $action_text[$activity['action']] ?? $activity['action'];
                                            ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo date('d M Y H:i', strtotime($activity['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">Belum ada aktivitas</p>
                    <?php endif; ?>

                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="history.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            Lihat semua aktivitas <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-bolt mr-2 text-yellow-600"></i>Aksi Cepat
                    </h3>

                    <div class="grid md:grid-cols-2 gap-4">
                        <a href="app.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="p-2 bg-blue-100 rounded-lg mr-3">
                                <i class="fas fa-plus text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Buat Surat Baru</p>
                                <p class="text-sm text-gray-500">Mulai membuat surat</p>
                            </div>
                        </a>

                        <a href="templates.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="p-2 bg-green-100 rounded-lg mr-3">
                                <i class="fas fa-bookmark text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Template Saya</p>
                                <p class="text-sm text-gray-500">Kelola template</p>
                            </div>
                        </a>

                        <a href="history.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="p-2 bg-purple-100 rounded-lg mr-3">
                                <i class="fas fa-history text-purple-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Riwayat Surat</p>
                                <p class="text-sm text-gray-500">Lihat surat yang dibuat</p>
                            </div>
                        </a>

                        <a href="settings.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="p-2 bg-orange-100 rounded-lg mr-3">
                                <i class="fas fa-cog text-orange-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Pengaturan</p>
                                <p class="text-sm text-gray-500">Kelola akun</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>