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

        if ($action === 'toggle_status') {
            $user_id = (int)($_POST['user_id'] ?? 0);
            $is_active = isset($_POST['is_active']);

            if (Admin::updateUserStatus($user_id, $is_active)) {
                $success_message = 'Status pengguna berhasil diperbarui.';
            } else {
                $error_message = 'Gagal memperbarui status pengguna.';
            }
        } elseif ($action === 'delete_user') {
            $user_id = (int)($_POST['user_id'] ?? 0);

            // Prevent admin from deleting themselves
            if ($user_id === $current_user['id']) {
                $error_message = 'Anda tidak dapat menghapus akun sendiri.';
            } else {
                if (Admin::deleteUser($user_id)) {
                    $success_message = 'Pengguna berhasil dihapus.';
                } else {
                    $error_message = 'Gagal menghapus pengguna.';
                }
            }
        } elseif ($action === 'reset_password') {
            $user_id = (int)($_POST['user_id'] ?? 0);
            $new_password = $_POST['new_password'] ?? '';

            if (strlen($new_password) < 8) {
                $error_message = 'Password minimal 8 karakter.';
            } else {
                if (Admin::resetUserPassword($user_id, $new_password)) {
                    $success_message = 'Password pengguna berhasil direset.';
                } else {
                    $error_message = 'Gagal mereset password pengguna.';
                }
            }
        }
    }
}

// Get filter parameters
$page = (int)($_GET['page'] ?? 1);
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Get users data
$users_data = Admin::getUsers($page, 20, $search, $role_filter, $status_filter);

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - Admin Panel</title>
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
                        <a href="users.php" class="text-white border-b-2 border-white px-1 pt-1 pb-4 text-sm font-medium">
                            <i class="fas fa-users mr-1"></i>Pengguna
                        </a>
                        <a href="admin_content.php" class="text-blue-100 hover:text-white px-1 pt-1 pb-4 text-sm font-medium transition-colors">
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
                <i class="fas fa-users mr-3 text-blue-600"></i>Manajemen Pengguna
            </h1>
            <p class="text-gray-600 mt-2">Kelola semua pengguna sistem</p>
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

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <form method="GET" class="grid md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Pengguna</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Nama, email, atau username..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Role</option>
                        <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <a href="users.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                        <i class="fas fa-times mr-2"></i>Reset
                    </a>
                    <button type="button" onclick="exportUsers()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Daftar Pengguna (<?php echo number_format($users_data['total']); ?> total)
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktivitas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bergabung</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users_data['users'] as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-blue-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($user['full_name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($user['email']); ?>
                                            </div>
                                            <?php if ($user['city']): ?>
                                                <div class="text-xs text-gray-400">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                                    <?php echo htmlspecialchars($user['city']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $user['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div><?php echo $user['letter_count']; ?> surat</div>
                                    <div><?php echo $user['template_count']; ?> template</div>
                                    <?php if ($user['last_login']): ?>
                                        <div class="text-xs">Login: <?php echo date('d M Y', strtotime($user['last_login'])); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="viewUser(<?php echo $user['id']; ?>)"
                                            class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <?php if ($user['id'] !== $current_user['id']): ?>
                                            <button onclick="toggleUserStatus(<?php echo $user['id']; ?>, <?php echo $user['is_active'] ? 'false' : 'true'; ?>)"
                                                class="text-yellow-600 hover:text-yellow-900">
                                                <i class="fas <?php echo $user['is_active'] ? 'fa-ban' : 'fa-check'; ?>"></i>
                                            </button>

                                            <button onclick="resetPassword(<?php echo $user['id']; ?>)"
                                                class="text-orange-600 hover:text-orange-900">
                                                <i class="fas fa-key"></i>
                                            </button>

                                            <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')"
                                                class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">(Anda)</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($users_data['pages'] > 1): ?>
                <div class="px-6 py-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Menampilkan <?php echo (($page - 1) * 20) + 1; ?> - <?php echo min($page * 20, $users_data['total']); ?>
                            dari <?php echo $users_data['total']; ?> pengguna
                        </div>
                        <nav class="flex space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role_filter; ?>&status=<?php echo $status_filter; ?>"
                                    class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($users_data['pages'], $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role_filter; ?>&status=<?php echo $status_filter; ?>"
                                    class="px-3 py-2 border rounded-lg transition-colors <?php echo $i === $page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white border-gray-300 hover:bg-gray-50'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $users_data['pages']): ?>
                                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role_filter; ?>&status=<?php echo $status_filter; ?>"
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

    <!-- Hidden Forms -->
    <form id="statusForm" method="POST" class="hidden">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="action" value="toggle_status">
        <input type="hidden" name="user_id" id="statusUserId">
        <input type="hidden" name="is_active" id="statusIsActive">
    </form>

    <form id="deleteForm" method="POST" class="hidden">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="action" value="delete_user">
        <input type="hidden" name="user_id" id="deleteUserId">
    </form>

    <form id="resetForm" method="POST" class="hidden">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="action" value="reset_password">
        <input type="hidden" name="user_id" id="resetUserId">
        <input type="hidden" name="new_password" id="resetPassword">
    </form>

    <script>
        function viewUser(userId) {
            // Implement user detail view
            alert('Fitur detail pengguna akan segera tersedia');
        }

        function toggleUserStatus(userId, isActive) {
            const action = isActive === 'true' ? 'mengaktifkan' : 'menonaktifkan';
            if (confirm(`Apakah Anda yakin ingin ${action} pengguna ini?`)) {
                document.getElementById('statusUserId').value = userId;
                document.getElementById('statusIsActive').value = isActive === 'true' ? '1' : '';
                document.getElementById('statusForm').submit();
            }
        }

        function deleteUser(userId, userName) {
            if (confirm(`Apakah Anda yakin ingin menghapus pengguna "${userName}"?\n\nSemua data terkait (surat, template, dll) akan ikut terhapus dan tidak dapat dikembalikan.`)) {
                document.getElementById('deleteUserId').value = userId;
                document.getElementById('deleteForm').submit();
            }
        }

        function resetPassword(userId) {
            const newPassword = prompt('Masukkan password baru (minimal 8 karakter):');
            if (newPassword && newPassword.length >= 8) {
                if (confirm('Apakah Anda yakin ingin mereset password pengguna ini?')) {
                    document.getElementById('resetUserId').value = userId;
                    document.getElementById('resetPassword').value = newPassword;
                    document.getElementById('resetForm').submit();
                }
            } else if (newPassword !== null) {
                alert('Password minimal 8 karakter!');
            }
        }

        function exportUsers() {
            // Implement CSV export
            alert('Fitur export akan segera tersedia');
        }
    </script>
</body>

</html>