<?php
require_once '../../../config/database.php';
require_once '../../../app/models/User.php';

// Require login
User::requireLogin();

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

            if ($action === 'save_template') {
                $template_name = sanitizeInput($_POST['template_name'] ?? '');
                $template_type = sanitizeInput($_POST['template_type'] ?? '');
                $template_data = $_POST['template_data'] ?? [];
                $is_default = isset($_POST['is_default']);

                if (empty($template_name) || empty($template_type)) {
                    $error_message = 'Nama template dan jenis surat wajib diisi.';
                } else {
                    // If setting as default, remove default from other templates of same type
                    if ($is_default) {
                        $stmt = $conn->prepare("UPDATE saved_templates SET is_default = 0 WHERE user_id = ? AND template_type = ?");
                        $stmt->execute([$current_user['id'], $template_type]);
                    }

                    $stmt = $conn->prepare("
                        INSERT INTO saved_templates (user_id, template_type, template_name, template_data, is_default) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $result = $stmt->execute([
                        $current_user['id'],
                        $template_type,
                        $template_name,
                        json_encode($template_data),
                        $is_default ? 1 : 0
                    ]);

                    if ($result) {
                        $success_message = 'Template berhasil disimpan.';
                        logActivity($current_user['id'], 'template_saved', "Template '$template_name' disimpan");
                    } else {
                        $error_message = 'Gagal menyimpan template.';
                    }
                }
            } elseif ($action === 'delete_template') {
                $template_id = (int)($_POST['template_id'] ?? 0);

                $stmt = $conn->prepare("DELETE FROM saved_templates WHERE id = ? AND user_id = ?");
                $result = $stmt->execute([$template_id, $current_user['id']]);

                if ($result) {
                    $success_message = 'Template berhasil dihapus.';
                    logActivity($current_user['id'], 'template_deleted', "Template ID $template_id dihapus");
                } else {
                    $error_message = 'Gagal menghapus template.';
                }
            } elseif ($action === 'set_default') {
                $template_id = (int)($_POST['template_id'] ?? 0);

                // Get template info
                $stmt = $conn->prepare("SELECT template_type FROM saved_templates WHERE id = ? AND user_id = ?");
                $stmt->execute([$template_id, $current_user['id']]);
                $template = $stmt->fetch();

                if ($template) {
                    // Remove default from other templates of same type
                    $stmt = $conn->prepare("UPDATE saved_templates SET is_default = 0 WHERE user_id = ? AND template_type = ?");
                    $stmt->execute([$current_user['id'], $template['template_type']]);

                    // Set this template as default
                    $stmt = $conn->prepare("UPDATE saved_templates SET is_default = 1 WHERE id = ? AND user_id = ?");
                    $result = $stmt->execute([$template_id, $current_user['id']]);

                    if ($result) {
                        $success_message = 'Template default berhasil diatur.';
                    } else {
                        $error_message = 'Gagal mengatur template default.';
                    }
                } else {
                    $error_message = 'Template tidak ditemukan.';
                }
            }
        } catch (Exception $e) {
            $error_message = 'Terjadi kesalahan sistem.';
            error_log("Template error: " . $e->getMessage());
        }
    }
}

// Get user templates
try {
    $database = new Database();
    $conn = $database->getConnection();

    $stmt = $conn->prepare("
        SELECT * FROM saved_templates 
        WHERE user_id = ? 
        ORDER BY is_default DESC, template_type, template_name
    ");
    $stmt->execute([$current_user['id']]);
    $templates = $stmt->fetchAll();

    // Group templates by type
    $grouped_templates = [];
    foreach ($templates as $template) {
        $grouped_templates[$template['template_type']][] = $template;
    }
} catch (Exception $e) {
    $templates = [];
    $grouped_templates = [];
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template Surat - Indonesian PDF Letter Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .template-card {
            transition: all 0.3s ease;
        }

        .template-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
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
                    <a href="profile.php" class="text-white hover:text-blue-200 transition-colors">
                        <i class="fas fa-user mr-1"></i>Profil
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
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-bookmark mr-3 text-green-600"></i>Template Surat
                </h1>
                <p class="text-gray-600 mt-2">Kelola template surat untuk mempercepat pembuatan dokumen</p>
            </div>
            <button onclick="showCreateModal()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Buat Template Baru
            </button>
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

        <!-- Templates by Type -->
        <?php if (!empty($grouped_templates)): ?>
            <?php foreach ($grouped_templates as $type => $type_templates): ?>
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-folder mr-2 text-blue-600"></i>
                        <?php
                        echo $type === 'pernyataan' ? 'Surat Pernyataan' : ($type === 'izin' ? 'Surat Izin' : 'Surat Kuasa');
                        ?>
                    </h2>

                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($type_templates as $template): ?>
                            <div class="template-card bg-white rounded-xl shadow-lg p-6 relative">
                                <?php if ($template['is_default']): ?>
                                    <div class="absolute top-4 right-4">
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">
                                            <i class="fas fa-star mr-1"></i>Default
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        <?php echo htmlspecialchars($template['template_name']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Dibuat: <?php echo date('d M Y', strtotime($template['created_at'])); ?>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        Diperbarui: <?php echo date('d M Y', strtotime($template['updated_at'])); ?>
                                    </p>
                                </div>

                                <div class="flex space-x-2">
                                    <button onclick="useTemplate(<?php echo $template['id']; ?>)"
                                        class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                        <i class="fas fa-play mr-1"></i>Gunakan
                                    </button>

                                    <?php if (!$template['is_default']): ?>
                                        <button onclick="setDefault(<?php echo $template['id']; ?>)"
                                            class="bg-yellow-600 text-white py-2 px-3 rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    <?php endif; ?>

                                    <button onclick="deleteTemplate(<?php echo $template['id']; ?>)"
                                        class="bg-red-600 text-white py-2 px-3 rounded-lg hover:bg-red-700 transition-colors text-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gray-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-bookmark text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Template</h3>
                <p class="text-gray-600 mb-6">Buat template pertama Anda untuk mempercepat pembuatan surat</p>
                <button onclick="showCreateModal()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Buat Template Baru
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Create Template Modal -->
    <div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Buat Template Baru</h3>
                        <button onclick="hideCreateModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="save_template">

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

                        <div class="flex items-center">
                            <input type="checkbox" name="is_default" id="is_default"
                                class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <label for="is_default" class="ml-2 block text-sm text-gray-900">
                                Jadikan template default
                            </label>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="hideCreateModal()"
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                Simpan Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Forms for Actions -->
    <form id="deleteForm" method="POST" class="hidden">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="action" value="delete_template">
        <input type="hidden" name="template_id" id="deleteTemplateId">
    </form>

    <form id="defaultForm" method="POST" class="hidden">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="action" value="set_default">
        <input type="hidden" name="template_id" id="defaultTemplateId">
    </form>

    <script>
        function showCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function hideCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        function useTemplate(templateId) {
            // Redirect to app.php with template parameter
            window.location.href = 'app.php?template=' + templateId;
        }

        function deleteTemplate(templateId) {
            if (confirm('Apakah Anda yakin ingin menghapus template ini?')) {
                document.getElementById('deleteTemplateId').value = templateId;
                document.getElementById('deleteForm').submit();
            }
        }

        function setDefault(templateId) {
            if (confirm('Jadikan template ini sebagai default?')) {
                document.getElementById('defaultTemplateId').value = templateId;
                document.getElementById('defaultForm').submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideCreateModal();
            }
        });
    </script>
</body>

</html>