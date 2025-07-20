<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Admin.php';

// Require admin access
Admin::requireAdmin();

$current_user = User::getCurrentUser();
$stats = Admin::getDashboardStats();

// Get recent activities
try {
    $database = new Database();
    $conn = $database->getConnection();

    // Recent user registrations
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recent_users = $stmt->fetchAll();

    // Recent letters
    $stmt = $conn->prepare("
        SELECT gl.*, u.full_name, u.email 
        FROM generated_letters gl 
        JOIN users u ON gl.user_id = u.id 
        ORDER BY gl.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recent_letters = $stmt->fetchAll();

    // Recent admin activities
    $stmt = $conn->prepare("
        SELECT al.*, u.full_name as admin_name 
        FROM admin_logs al 
        JOIN users u ON al.admin_id = u.id 
        ORDER BY al.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recent_admin_activities = $stmt->fetchAll();

    // Monthly statistics for chart
    $stmt = $conn->prepare("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count,
            'users' as type
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        
        UNION ALL
        
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count,
            'letters' as type
        FROM generated_letters 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        
        ORDER BY month DESC
    ");
    $stmt->execute();
    $monthly_stats = $stmt->fetchAll();
} catch (Exception $e) {
    $recent_users = [];
    $recent_letters = [];
    $recent_admin_activities = [];
    $monthly_stats = [];
    error_log("Admin dashboard error: " . $e->getMessage());
}

// Log admin dashboard access
Admin::logActivity('dashboard_accessed', 'system', null, 'Admin accessed dashboard');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Indonesian PDF Letter Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #7c3aed 100%);
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .admin-sidebar {
            background: linear-gradient(180deg, #1e40af 0%, #7c3aed 100%);
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Admin Navigation -->
    <nav class="admin-gradient shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-shield-alt text-2xl text-white mr-3"></i>
                        <span class="text-white text-xl font-bold">Admin Panel</span>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="admin.php" class="text-white border-b-2 border-white px-1 pt-1 pb-4 text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                        </a>
                        <a href="admin_users.php" class="text-blue-100 hover:text-white px-1 pt-1 pb-4 text-sm font-medium transition-colors">
                            <i class="fas fa-users mr-1"></i>Pengguna
                        </a>
                        <a href="admin_content.php" class="text-blue-100 hover:text-white px-1 pt-1 pb-4 text-sm font-medium transition-colors">
                            <i class="fas fa-file-alt mr-1"></i>Konten
                        </a>
                        <a href="admin_logs.php" class="text-blue-100 hover:text-white px-1 pt-1 pb-4 text-sm font-medium transition-colors">
                            <i class="fas fa-list-alt mr-1"></i>Log Sistem
                        </a>
                        <a href="admin_settings.php" class="text-blue-100 hover:text-white px-1 pt-1 pb-4 text-sm font-medium transition-colors">
                            <i class="fas fa-cog mr-1"></i>Pengaturan
                        </a>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-blue-100 hover:text-white transition-colors text-sm">
                        <i class="fas fa-user mr-1"></i>Mode User
                    </a>
                    <div class="text-white">
                        <span class="text-sm">Admin: </span>
                        <span class="font-semibold"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                    </div>
                    <a href="dashboard.php?action=logout" class="text-blue-100 hover:text-red-200 transition-colors">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-tachometer-alt mr-3 text-blue-600"></i>Dashboard Admin
            </h1>
            <p class="text-gray-600 mt-2">Selamat datang di panel administrasi Indonesian PDF Letter Generator</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-users text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Total Pengguna</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo number_format($stats['total_users']); ?></p>
                        <p class="text-sm text-gray-500">+<?php echo $stats['new_users_today']; ?> hari ini</p>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-file-pdf text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Total Surat</h3>
                        <p class="text-3xl font-bold text-green-600"><?php echo number_format($stats['total_letters']); ?></p>
                        <p class="text-sm text-gray-500">+<?php echo $stats['letters_today']; ?> hari ini</p>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-bookmark text-2xl text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Template</h3>
                        <p class="text-3xl font-bold text-purple-600"><?php echo number_format($stats['total_global_templates']); ?></p>
                        <p class="text-sm text-gray-500">Template global</p>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-lg">
                        <i class="fas fa-chart-line text-2xl text-orange-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Aktivitas</h3>
                        <p class="text-3xl font-bold text-orange-600"><?php echo number_format($stats['activities_today']); ?></p>
                        <p class="text-sm text-gray-500">Aktivitas hari ini</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Recent Activity -->
        <div class="grid lg:grid-cols-2 gap-8 mb-8">
            <!-- Monthly Statistics Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-chart-bar mr-2 text-blue-600"></i>Statistik 6 Bulan Terakhir
                </h3>
                <canvas id="monthlyChart" width="400" height="200"></canvas>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-bolt mr-2 text-yellow-600"></i>Aksi Cepat
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <a href="admin_users.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                            <i class="fas fa-user-plus text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Kelola User</p>
                            <p class="text-sm text-gray-500">Manajemen pengguna</p>
                        </div>
                    </a>

                    <a href="admin_content.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="p-2 bg-green-100 rounded-lg mr-3">
                            <i class="fas fa-file-alt text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Kelola Konten</p>
                            <p class="text-sm text-gray-500">Surat & template</p>
                        </div>
                    </a>

                    <a href="admin_logs.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="p-2 bg-purple-100 rounded-lg mr-3">
                            <i class="fas fa-list-alt text-purple-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Log Sistem</p>
                            <p class="text-sm text-gray-500">Monitor aktivitas</p>
                        </div>
                    </a>

                    <a href="admin_settings.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="p-2 bg-orange-100 rounded-lg mr-3">
                            <i class="fas fa-cog text-orange-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Pengaturan</p>
                            <p class="text-sm text-gray-500">Konfigurasi sistem</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Recent Users -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-user-plus mr-2 text-green-600"></i>Pengguna Terbaru
                </h3>
                <div class="space-y-3">
                    <?php foreach ($recent_users as $user): ?>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-blue-600 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    <?php echo htmlspecialchars($user['full_name']); ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4">
                    <a href="admin_users.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        Lihat semua pengguna <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>

            <!-- Recent Letters -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-file-pdf mr-2 text-red-600"></i>Surat Terbaru
                </h3>
                <div class="space-y-3">
                    <?php foreach ($recent_letters as $letter): ?>
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-file-pdf text-red-600 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    <?php echo htmlspecialchars($letter['letter_title']); ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    oleh <?php echo htmlspecialchars($letter['full_name']); ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo date('d M Y H:i', strtotime($letter['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4">
                    <a href="admin_content.php" class="text-red-600 hover:text-red-700 text-sm font-medium">
                        Lihat semua surat <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>

            <!-- Recent Admin Activities -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-shield-alt mr-2 text-purple-600"></i>Aktivitas Admin
                </h3>
                <div class="space-y-3">
                    <?php foreach (array_slice($recent_admin_activities, 0, 5) as $activity): ?>
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-cog text-purple-600 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">
                                    <?php
                                    $action_names = [
                                        'dashboard_accessed' => 'Mengakses dashboard',
                                        'user_activated' => 'Mengaktifkan user',
                                        'user_deactivated' => 'Menonaktifkan user',
                                        'user_deleted' => 'Menghapus user',
                                        'setting_updated' => 'Memperbarui pengaturan'
                                    ];
                                    echo $action_names[$activity['action']] ?? $activity['action'];
                                    ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo htmlspecialchars($activity['admin_name']); ?> -
                                    <?php echo date('d M H:i', strtotime($activity['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4">
                    <a href="admin_logs.php" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                        Lihat semua log <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Monthly Statistics Chart
        const ctx = document.getElementById('monthlyChart').getContext('2d');

        // Process PHP data for Chart.js
        const monthlyData = <?php echo json_encode($monthly_stats); ?>;
        const months = [...new Set(monthlyData.map(item => item.month))].sort().reverse().slice(0, 6);

        const usersData = months.map(month => {
            const item = monthlyData.find(d => d.month === month && d.type === 'users');
            return item ? parseInt(item.count) : 0;
        });

        const lettersData = months.map(month => {
            const item = monthlyData.find(d => d.month === month && d.type === 'letters');
            return item ? parseInt(item.count) : 0;
        });

        const monthLabels = months.map(month => {
            const [year, monthNum] = month.split('-');
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return monthNames[parseInt(monthNum) - 1] + ' ' + year;
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Pengguna Baru',
                    data: usersData,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Surat Dibuat',
                    data: lettersData,
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>