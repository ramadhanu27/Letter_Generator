<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Admin.php';

// Require admin access
Admin::requireAdmin();

$current_user = User::getCurrentUser();

// Get filter parameters
$page = (int)($_GET['page'] ?? 1);
$tab = $_GET['tab'] ?? 'admin';
$search = $_GET['search'] ?? '';
$date_filter = $_GET['date'] ?? '';
$action_filter = $_GET['action'] ?? '';

$limit = 50;
$offset = ($page - 1) * $limit;

try {
    $database = new Database();
    $conn = $database->getConnection();

    if ($tab === 'admin') {
        // Get admin logs
        $where_conditions = ["1=1"];
        $params = [];

        if ($search) {
            $where_conditions[] = "(al.action LIKE ? OR al.description LIKE ? OR u.full_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($date_filter) {
            $where_conditions[] = "DATE(al.created_at) = ?";
            $params[] = $date_filter;
        }

        if ($action_filter) {
            $where_conditions[] = "al.action = ?";
            $params[] = $action_filter;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // Get total count
        $count_query = "
            SELECT COUNT(*) as total 
            FROM admin_logs al 
            JOIN users u ON al.admin_id = u.id 
            WHERE $where_clause
        ";
        $stmt = $conn->prepare($count_query);
        $stmt->execute($params);
        $total_records = $stmt->fetch()['total'];
        $total_pages = ceil($total_records / $limit);

        // Get logs
        $query = "
            SELECT al.*, u.full_name as admin_name, u.email as admin_email
            FROM admin_logs al 
            JOIN users u ON al.admin_id = u.id 
            WHERE $where_clause 
            ORDER BY al.created_at DESC 
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        // Get unique actions for filter
        $stmt = $conn->query("SELECT DISTINCT action FROM admin_logs ORDER BY action");
        $actions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } elseif ($tab === 'activity') {
        // Get user activity logs
        $where_conditions = ["1=1"];
        $params = [];

        if ($search) {
            $where_conditions[] = "(al.action LIKE ? OR al.description LIKE ? OR u.full_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($date_filter) {
            $where_conditions[] = "DATE(al.created_at) = ?";
            $params[] = $date_filter;
        }

        if ($action_filter) {
            $where_conditions[] = "al.action = ?";
            $params[] = $action_filter;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // Get total count
        $count_query = "
            SELECT COUNT(*) as total 
            FROM activity_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            WHERE $where_clause
        ";
        $stmt = $conn->prepare($count_query);
        $stmt->execute($params);
        $total_records = $stmt->fetch()['total'];
        $total_pages = ceil($total_records / $limit);

        // Get logs
        $query = "
            SELECT al.*, u.full_name, u.email, u.role
            FROM activity_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            WHERE $where_clause 
            ORDER BY al.created_at DESC 
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        // Get unique actions for filter
        $stmt = $conn->query("SELECT DISTINCT action FROM activity_logs ORDER BY action");
        $actions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        // Get error logs
        $where_conditions = ["1=1"];
        $params = [];

        if ($search) {
            $where_conditions[] = "(el.error_type LIKE ? OR el.error_message LIKE ? OR el.file_path LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($date_filter) {
            $where_conditions[] = "DATE(el.created_at) = ?";
            $params[] = $date_filter;
        }

        if ($action_filter) {
            $where_conditions[] = "el.error_type = ?";
            $params[] = $action_filter;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // Get total count
        $count_query = "
            SELECT COUNT(*) as total 
            FROM error_logs el 
            WHERE $where_clause
        ";
        $stmt = $conn->prepare($count_query);
        $stmt->execute($params);
        $total_records = $stmt->fetch()['total'];
        $total_pages = ceil($total_records / $limit);

        // Get logs
        $query = "
            SELECT el.*, u.full_name, u.email
            FROM error_logs el 
            LEFT JOIN users u ON el.user_id = u.id 
            WHERE $where_clause 
            ORDER BY el.created_at DESC 
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        // Get unique error types for filter
        $stmt = $conn->query("SELECT DISTINCT error_type FROM error_logs ORDER BY error_type");
        $actions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Get log statistics
    $stats_query = "
        SELECT 
            (SELECT COUNT(*) FROM admin_logs WHERE DATE(created_at) = CURDATE()) as admin_logs_today,
            (SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()) as activity_logs_today,
            (SELECT COUNT(*) FROM error_logs WHERE DATE(created_at) = CURDATE()) as error_logs_today,
            (SELECT COUNT(*) FROM admin_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as admin_logs_week,
            (SELECT COUNT(*) FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as activity_logs_week,
            (SELECT COUNT(*) FROM error_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as error_logs_week
    ";
    $stmt = $conn->query($stats_query);
    $log_stats = $stmt->fetch();
} catch (Exception $e) {
    $logs = [];
    $actions = [];
    $total_records = 0;
    $total_pages = 0;
    $log_stats = [
        'admin_logs_today' => 0,
        'activity_logs_today' => 0,
        'error_logs_today' => 0,
        'admin_logs_week' => 0,
        'activity_logs_week' => 0,
        'error_logs_week' => 0
    ];
    error_log("Admin logs error: " . $e->getMessage());
}

// Log admin access
Admin::logActivity('logs_accessed', 'system', null, "Admin accessed $tab logs");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Sistem - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #7c3aed 100%);
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
                        <a href="admin.php" class="text-blue-100 hover:text-white px-1 pt-1 pb-4 text-sm font-medium transition-colors">
                            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                        </a>
                        <a href="admin_users.php" class="text-blue-100 hover:text-white px-1 pt-1 pb-4 text-sm font-medium transition-colors">
                            <i class="fas fa-users mr-1"></i>Pengguna
                        </a>
                        <a href="admin_content.php" class="text-blue-100 hover:text-white px-1 pt-1 pb-4 text-sm font-medium transition-colors">
                            <i class="fas fa-file-alt mr-1"></i>Konten
                        </a>
                        <a href="admin_logs.php" class="text-white border-b-2 border-white px-1 pt-1 pb-4 text-sm font-medium">
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
                <i class="fas fa-list-alt mr-3 text-purple-600"></i>Log Sistem
            </h1>
            <p class="text-gray-600 mt-2">Monitor aktivitas dan error sistem</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-shield-alt text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Log Admin</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo number_format($log_stats['admin_logs_today']); ?></p>
                        <p class="text-sm text-gray-500"><?php echo $log_stats['admin_logs_week']; ?> minggu ini</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-users text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Aktivitas User</h3>
                        <p class="text-3xl font-bold text-green-600"><?php echo number_format($log_stats['activity_logs_today']); ?></p>
                        <p class="text-sm text-gray-500"><?php echo $log_stats['activity_logs_week']; ?> minggu ini</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Error Log</h3>
                        <p class="text-3xl font-bold text-red-600"><?php echo number_format($log_stats['error_logs_today']); ?></p>
                        <p class="text-sm text-gray-500"><?php echo $log_stats['error_logs_week']; ?> minggu ini</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs and Filters -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <a href="?tab=admin" class="<?php echo $tab === 'admin' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?> border-b-2 py-4 px-1 text-sm font-medium">
                        <i class="fas fa-shield-alt mr-2"></i>Log Admin
                    </a>
                    <a href="?tab=activity" class="<?php echo $tab === 'activity' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?> border-b-2 py-4 px-1 text-sm font-medium">
                        <i class="fas fa-users mr-2"></i>Aktivitas User
                    </a>
                    <a href="?tab=errors" class="<?php echo $tab === 'errors' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?> border-b-2 py-4 px-1 text-sm font-medium">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Error Log
                    </a>
                </nav>
            </div>

            <!-- Filters -->
            <div class="p-6 border-b border-gray-200">
                <form method="GET" class="grid md:grid-cols-4 gap-4">
                    <input type="hidden" name="tab" value="<?php echo $tab; ?>">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="Cari dalam log..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo $tab === 'errors' ? 'Tipe Error' : 'Aksi'; ?>
                        </label>
                        <select name="action" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Semua <?php echo $tab === 'errors' ? 'Tipe' : 'Aksi'; ?></option>
                            <?php foreach ($actions as $action): ?>
                                <option value="<?php echo htmlspecialchars($action); ?>" <?php echo $action_filter === $action ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($action); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                        <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <div class="flex items-end space-x-2">
                        <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="?tab=<?php echo $tab; ?>" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                            <i class="fas fa-times mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <?php include 'admin_logs_table.php'; ?>
</body>

</html>