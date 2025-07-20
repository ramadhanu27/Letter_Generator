<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Admin.php';

// Require admin access
Admin::requireAdmin();

$current_user = User::getCurrentUser();
$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Token keamanan tidak valid. Silakan refresh halaman.';
    } else {
        $action = $_POST['action'] ?? '';

        try {
            $database = new Database();
            $conn = $database->getConnection();

            if ($action === 'delete_letter') {
                $letter_id = (int)($_POST['letter_id'] ?? 0);

                $stmt = $conn->prepare("DELETE FROM generated_letters WHERE id = ?");
                $result = $stmt->execute([$letter_id]);

                if ($result) {
                    $success_message = 'Surat berhasil dihapus.';
                    Admin::logActivity('letter_deleted', 'letter', $letter_id, 'Admin deleted letter');
                } else {
                    $error_message = 'Gagal menghapus surat.';
                }
            } elseif ($action === 'create_global_template') {
                $template_type = sanitizeInput($_POST['template_type'] ?? '');
                $template_name = sanitizeInput($_POST['template_name'] ?? '');
                $description = sanitizeInput($_POST['description'] ?? '');
                $template_data = $_POST['template_data'] ?? [];

                if (empty($template_name) || empty($template_type)) {
                    $error_message = 'Nama template dan jenis surat wajib diisi.';
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO global_templates (template_type, template_name, template_data, description, created_by) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $result = $stmt->execute([
                        $template_type,
                        $template_name,
                        json_encode($template_data),
                        $description,
                        $current_user['id']
                    ]);

                    if ($result) {
                        $success_message = 'Template global berhasil dibuat.';
                        Admin::logActivity('global_template_created', 'template', $conn->lastInsertId(), "Created global template: $template_name");
                    } else {
                        $error_message = 'Gagal membuat template global.';
                    }
                }
            } elseif ($action === 'toggle_template') {
                $template_id = (int)($_POST['template_id'] ?? 0);
                $is_active = isset($_POST['is_active']);

                $stmt = $conn->prepare("UPDATE global_templates SET is_active = ? WHERE id = ?");
                $result = $stmt->execute([$is_active ? 1 : 0, $template_id]);

                if ($result) {
                    $success_message = 'Status template berhasil diperbarui.';
                    $action_name = $is_active ? 'global_template_activated' : 'global_template_deactivated';
                    Admin::logActivity($action_name, 'template', $template_id, 'Admin toggled template status');
                } else {
                    $error_message = 'Gagal memperbarui status template.';
                }
            } elseif ($action === 'delete_global_template') {
                $template_id = (int)($_POST['template_id'] ?? 0);

                $stmt = $conn->prepare("DELETE FROM global_templates WHERE id = ?");
                $result = $stmt->execute([$template_id]);

                if ($result) {
                    $success_message = 'Template global berhasil dihapus.';
                    Admin::logActivity('global_template_deleted', 'template', $template_id, 'Admin deleted global template');
                } else {
                    $error_message = 'Gagal menghapus template global.';
                }
            }
        } catch (Exception $e) {
            $error_message = 'Terjadi kesalahan sistem.';
            error_log("Admin content error: " . $e->getMessage());
        }
    }
}

// Get filter parameters
$page = (int)($_GET['page'] ?? 1);
$tab = $_GET['tab'] ?? 'letters';
$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';
$user_filter = $_GET['user'] ?? '';

$limit = 20;
$offset = ($page - 1) * $limit;

try {
    $database = new Database();
    $conn = $database->getConnection();

    if ($tab === 'letters') {
        // Get letters with filters
        $where_conditions = ["1=1"];
        $params = [];

        if ($search) {
            $where_conditions[] = "(gl.letter_title LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($type_filter) {
            $where_conditions[] = "gl.letter_type = ?";
            $params[] = $type_filter;
        }

        if ($user_filter) {
            $where_conditions[] = "gl.user_id = ?";
            $params[] = $user_filter;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // Get total count
        $count_query = "
            SELECT COUNT(*) as total 
            FROM generated_letters gl 
            JOIN users u ON gl.user_id = u.id 
            WHERE $where_clause
        ";
        $stmt = $conn->prepare($count_query);
        $stmt->execute($params);
        $total_records = $stmt->fetch()['total'];
        $total_pages = ceil($total_records / $limit);

        // Get letters
        $query = "
            SELECT gl.*, u.full_name, u.email, u.role
            FROM generated_letters gl 
            JOIN users u ON gl.user_id = u.id 
            WHERE $where_clause 
            ORDER BY gl.created_at DESC 
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $letters = $stmt->fetchAll();
    } else {
        // Get global templates
        $where_conditions = ["1=1"];
        $params = [];

        if ($search) {
            $where_conditions[] = "(gt.template_name LIKE ? OR gt.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($type_filter) {
            $where_conditions[] = "gt.template_type = ?";
            $params[] = $type_filter;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // Get total count
        $count_query = "SELECT COUNT(*) as total FROM global_templates gt WHERE $where_clause";
        $stmt = $conn->prepare($count_query);
        $stmt->execute($params);
        $total_records = $stmt->fetch()['total'];
        $total_pages = ceil($total_records / $limit);

        // Get templates
        $query = "
            SELECT gt.*, u.full_name as creator_name
            FROM global_templates gt 
            JOIN users u ON gt.created_by = u.id 
            WHERE $where_clause 
            ORDER BY gt.created_at DESC 
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $templates = $stmt->fetchAll();
    }

    // Get users for filter dropdown
    $stmt = $conn->query("SELECT id, full_name, email FROM users WHERE role = 'user' ORDER BY full_name");
    $users_list = $stmt->fetchAll();
} catch (Exception $e) {
    $letters = [];
    $templates = [];
    $users_list = [];
    $total_records = 0;
    $total_pages = 0;
    error_log("Admin content data error: " . $e->getMessage());
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Konten - Admin Panel</title>
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
                        <a href="index.php" class="text-blue-100 hover:text-white px-1 pt-1 pb-4 text-sm font-medium transition-colors">
                            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                        </a>
                        <a href="users.php" class="text-blue-100 hover:text-white px-1 pt-1 pb-4 text-sm font-medium transition-colors">
                            <i class="fas fa-users mr-1"></i>Pengguna
                        </a>
                        <a href="admin_content.php" class="text-white border-b-2 border-white px-1 pt-1 pb-4 text-sm font-medium">
                            <i class="fas fa-file-alt mr-1"></i>Konten
                        </a>
                        <a href="logs.php" class="text-blue-100 hover:text-white px-1 pt-1 pb-4 text-sm font-medium transition-colors">
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
                <i class="fas fa-file-alt mr-3 text-green-600"></i>Manajemen Konten
            </h1>
            <p class="text-gray-600 mt-2">Kelola surat dan template sistem</p>
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

        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <a href="?tab=letters" class="<?php echo $tab === 'letters' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?> border-b-2 py-4 px-1 text-sm font-medium">
                        <i class="fas fa-file-pdf mr-2"></i>Surat Pengguna
                    </a>
                    <a href="?tab=templates" class="<?php echo $tab === 'templates' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?> border-b-2 py-4 px-1 text-sm font-medium">
                        <i class="fas fa-bookmark mr-2"></i>Template Global
                    </a>
                </nav>
            </div>

            <!-- Filters -->
            <div class="p-6 border-b border-gray-200">
                <form method="GET" class="grid md:grid-cols-5 gap-4">
                    <input type="hidden" name="tab" value="<?php echo $tab; ?>">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="<?php echo $tab === 'letters' ? 'Judul surat atau nama user...' : 'Nama template...'; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis</label>
                        <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Semua Jenis</option>
                            <option value="pernyataan" <?php echo $type_filter === 'pernyataan' ? 'selected' : ''; ?>>Surat Pernyataan</option>
                            <option value="izin" <?php echo $type_filter === 'izin' ? 'selected' : ''; ?>>Surat Izin</option>
                            <option value="kuasa" <?php echo $type_filter === 'kuasa' ? 'selected' : ''; ?>>Surat Kuasa</option>
                        </select>
                    </div>

                    <?php if ($tab === 'letters'): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pengguna</label>
                            <select name="user" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Semua Pengguna</option>
                                <?php foreach ($users_list as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo $user_filter == $user['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>

                    <div class="flex items-end space-x-2">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="?tab=<?php echo $tab; ?>" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                            <i class="fas fa-times mr-2"></i>Reset
                        </a>
                    </div>

                    <?php if ($tab === 'templates'): ?>
                        <div class="flex items-end">
                            <button type="button" onclick="showCreateTemplateModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>Buat Template
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Content Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <?php if ($tab === 'letters'): ?>
                        Daftar Surat (<?php echo number_format($total_records); ?> total)
                    <?php else: ?>
                        Template Global (<?php echo number_format($total_records); ?> total)
                    <?php endif; ?>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <?php if ($tab === 'letters'): ?>
                    <!-- Letters Table -->
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Surat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ukuran</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($letters as $letter): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-file-pdf text-red-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($letter['letter_title']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    ID: <?php echo $letter['id']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($letter['full_name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($letter['email']); ?></div>
                                        <?php if ($letter['role'] === 'admin'): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                Admin
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            <?php
                                            echo $letter['letter_type'] === 'pernyataan' ? 'bg-blue-100 text-blue-800' : ($letter['letter_type'] === 'izin' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800');
                                            ?>">
                                            <?php
                                            echo $letter['letter_type'] === 'pernyataan' ? 'Pernyataan' : ($letter['letter_type'] === 'izin' ? 'Izin' : 'Kuasa');
                                            ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php if ($letter['file_size']): ?>
                                            <?php echo number_format($letter['file_size'] / 1024, 1); ?> KB
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo date('d M Y H:i', strtotime($letter['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="viewLetter(<?php echo $letter['id']; ?>)"
                                                class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="downloadLetter(<?php echo $letter['id']; ?>)"
                                                class="text-green-600 hover:text-green-900">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button onclick="deleteLetter(<?php echo $letter['id']; ?>, '<?php echo htmlspecialchars($letter['letter_title']); ?>')"
                                                class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <!-- Templates Table -->
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembuat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($templates as $template): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-bookmark text-blue-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($template['template_name']); ?>
                                                </div>
                                                <?php if ($template['description']): ?>
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($template['description']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            <?php
                                            echo $template['template_type'] === 'pernyataan' ? 'bg-blue-100 text-blue-800' : ($template['template_type'] === 'izin' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800');
                                            ?>">
                                            <?php
                                            echo $template['template_type'] === 'pernyataan' ? 'Pernyataan' : ($template['template_type'] === 'izin' ? 'Izin' : 'Kuasa');
                                            ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $template['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $template['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo htmlspecialchars($template['creator_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo date('d M Y', strtotime($template['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="viewTemplate(<?php echo $template['id']; ?>)"
                                                class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="toggleTemplate(<?php echo $template['id']; ?>, <?php echo $template['is_active'] ? 'false' : 'true'; ?>)"
                                                class="text-yellow-600 hover:text-yellow-900">
                                                <i class="fas <?php echo $template['is_active'] ? 'fa-ban' : 'fa-check'; ?>"></i>
                                            </button>
                                            <button onclick="deleteTemplate(<?php echo $template['id']; ?>, '<?php echo htmlspecialchars($template['template_name']); ?>')"
                                                class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="px-6 py-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Menampilkan <?php echo (($page - 1) * $limit) + 1; ?> - <?php echo min($page * $limit, $total_records); ?>
                            dari <?php echo $total_records; ?> item
                        </div>
                        <nav class="flex space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?tab=<?php echo $tab; ?>&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo $type_filter; ?>&user=<?php echo $user_filter; ?>"
                                    class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?tab=<?php echo $tab; ?>&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo $type_filter; ?>&user=<?php echo $user_filter; ?>"
                                    class="px-3 py-2 border rounded-lg transition-colors <?php echo $i === $page ? 'bg-green-600 text-white border-green-600' : 'bg-white border-gray-300 hover:bg-gray-50'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?tab=<?php echo $tab; ?>&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo $type_filter; ?>&user=<?php echo $user_filter; ?>"
                                    class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Create Template Modal -->
    <div id="createTemplateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Buat Template Global</h3>
                        <button onclick="hideCreateTemplateModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="create_global_template">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Template</label>
                            <input type="text" name="template_name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Surat</label>
                            <select name="template_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Pilih Jenis Surat</option>
                                <option value="pernyataan">Surat Pernyataan</option>
                                <option value="izin">Surat Izin</option>
                                <option value="kuasa">Surat Kuasa</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <textarea name="description" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="hideCreateTemplateModal()"
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                Buat Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Forms -->
    <form id="deleteLetterForm" method="POST" class="hidden">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="action" value="delete_letter">
        <input type="hidden" name="letter_id" id="deleteLetterIdInput">
    </form>

    <form id="toggleTemplateForm" method="POST" class="hidden">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="action" value="toggle_template">
        <input type="hidden" name="template_id" id="toggleTemplateIdInput">
        <input type="hidden" name="is_active" id="toggleTemplateStatusInput">
    </form>

    <form id="deleteTemplateForm" method="POST" class="hidden">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="action" value="delete_global_template">
        <input type="hidden" name="template_id" id="deleteTemplateIdInput">
    </form>

    <script>
        function showCreateTemplateModal() {
            document.getElementById('createTemplateModal').classList.remove('hidden');
        }

        function hideCreateTemplateModal() {
            document.getElementById('createTemplateModal').classList.add('hidden');
        }

        function viewLetter(letterId) {
            // Implement letter detail view
            alert('Fitur detail surat akan segera tersedia');
        }

        function downloadLetter(letterId) {
            // Implement letter download
            alert('Fitur download surat akan segera tersedia');
        }

        function deleteLetter(letterId, letterTitle) {
            if (confirm(`Apakah Anda yakin ingin menghapus surat "${letterTitle}"?\n\nData ini tidak dapat dikembalikan.`)) {
                document.getElementById('deleteLetterIdInput').value = letterId;
                document.getElementById('deleteLetterForm').submit();
            }
        }

        function viewTemplate(templateId) {
            // Implement template detail view
            alert('Fitur detail template akan segera tersedia');
        }

        function toggleTemplate(templateId, isActive) {
            const action = isActive === 'true' ? 'mengaktifkan' : 'menonaktifkan';
            if (confirm(`Apakah Anda yakin ingin ${action} template ini?`)) {
                document.getElementById('toggleTemplateIdInput').value = templateId;
                document.getElementById('toggleTemplateStatusInput').value = isActive === 'true' ? '1' : '';
                document.getElementById('toggleTemplateForm').submit();
            }
        }

        function deleteTemplate(templateId, templateName) {
            if (confirm(`Apakah Anda yakin ingin menghapus template "${templateName}"?\n\nTemplate ini tidak dapat dikembalikan.`)) {
                document.getElementById('deleteTemplateIdInput').value = templateId;
                document.getElementById('deleteTemplateForm').submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('createTemplateModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideCreateTemplateModal();
            }
        });
    </script>
</body>

</html>