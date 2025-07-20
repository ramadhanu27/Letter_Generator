<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../app/models/User.php';

// Require login
User::requireLogin();

$current_user = User::getCurrentUser();

// Pagination settings
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter settings
$filter_type = $_GET['type'] ?? '';
$filter_date = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';

// Get user's generated letters with filters
try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Build WHERE clause
    $where_conditions = ["user_id = ?"];
    $params = [$current_user['id']];
    
    if ($filter_type) {
        $where_conditions[] = "letter_type = ?";
        $params[] = $filter_type;
    }
    
    if ($filter_date) {
        $where_conditions[] = "DATE(created_at) = ?";
        $params[] = $filter_date;
    }
    
    if ($search) {
        $where_conditions[] = "(letter_title LIKE ? OR letter_data LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    // Get total count for pagination
    $count_query = "SELECT COUNT(*) as total FROM generated_letters WHERE $where_clause";
    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total_records = $stmt->fetch()['total'];
    $total_pages = ceil($total_records / $limit);
    
    // Get letters with pagination
    $query = "
        SELECT * FROM generated_letters 
        WHERE $where_clause 
        ORDER BY created_at DESC 
        LIMIT $limit OFFSET $offset
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $letters = $stmt->fetchAll();
    
    // Get statistics
    $stats_query = "
        SELECT 
            letter_type,
            COUNT(*) as count,
            DATE(created_at) as date
        FROM generated_letters 
        WHERE user_id = ? 
        GROUP BY letter_type, DATE(created_at)
        ORDER BY date DESC
        LIMIT 30
    ";
    $stmt = $conn->prepare($stats_query);
    $stmt->execute([$current_user['id']]);
    $stats = $stmt->fetchAll();
    
} catch (Exception $e) {
    $letters = [];
    $total_records = 0;
    $total_pages = 0;
    $stats = [];
    error_log("History error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Surat - Indonesian PDF Letter Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .letter-card {
            transition: all 0.3s ease;
        }
        .letter-card:hover {
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
                    <a href="templates.php" class="text-white hover:text-blue-200 transition-colors">
                        <i class="fas fa-bookmark mr-1"></i>Template
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
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-history mr-3 text-purple-600"></i>Riwayat Surat
            </h1>
            <p class="text-gray-600 mt-2">Lihat dan kelola surat yang pernah Anda buat</p>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <form method="GET" class="grid md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Surat</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Cari berdasarkan judul atau isi..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Surat</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Semua Jenis</option>
                        <option value="pernyataan" <?php echo $filter_type === 'pernyataan' ? 'selected' : ''; ?>>Surat Pernyataan</option>
                        <option value="izin" <?php echo $filter_type === 'izin' ? 'selected' : ''; ?>>Surat Izin</option>
                        <option value="kuasa" <?php echo $filter_type === 'kuasa' ? 'selected' : ''; ?>>Surat Kuasa</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($filter_date); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <a href="history.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                        <i class="fas fa-times mr-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Statistics Summary -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-file-alt text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Total Surat</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $total_records; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-calendar-day text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Bulan Ini</h3>
                        <p class="text-3xl font-bold text-green-600">
                            <?php 
                                $this_month = 0;
                                foreach ($stats as $stat) {
                                    if (date('Y-m', strtotime($stat['date'])) === date('Y-m')) {
                                        $this_month += $stat['count'];
                                    }
                                }
                                echo $this_month;
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-chart-line text-2xl text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Minggu Ini</h3>
                        <p class="text-3xl font-bold text-purple-600">
                            <?php 
                                $this_week = 0;
                                $week_start = date('Y-m-d', strtotime('monday this week'));
                                foreach ($stats as $stat) {
                                    if ($stat['date'] >= $week_start) {
                                        $this_week += $stat['count'];
                                    }
                                }
                                echo $this_week;
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Letters List -->
        <?php if (!empty($letters)): ?>
            <div class="space-y-6">
                <?php foreach ($letters as $letter): ?>
                    <?php 
                        $letter_data = json_decode($letter['letter_data'], true);
                        $type_names = [
                            'pernyataan' => 'Surat Pernyataan',
                            'izin' => 'Surat Izin',
                            'kuasa' => 'Surat Kuasa'
                        ];
                        $type_colors = [
                            'pernyataan' => 'bg-blue-100 text-blue-800',
                            'izin' => 'bg-green-100 text-green-800',
                            'kuasa' => 'bg-purple-100 text-purple-800'
                        ];
                    ?>
                    <div class="letter-card bg-white rounded-xl shadow-lg p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <span class="px-3 py-1 text-xs font-medium rounded-full <?php echo $type_colors[$letter['letter_type']]; ?>">
                                        <?php echo $type_names[$letter['letter_type']]; ?>
                                    </span>
                                    <span class="ml-3 text-sm text-gray-500">
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?php echo date('d M Y H:i', strtotime($letter['created_at'])); ?>
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    <?php echo htmlspecialchars($letter['letter_title']); ?>
                                </h3>
                                <div class="text-sm text-gray-600">
                                    <?php if (isset($letter_data['nama'])): ?>
                                        <p><strong>Nama:</strong> <?php echo htmlspecialchars($letter_data['nama']); ?></p>
                                    <?php endif; ?>
                                    <?php if (isset($letter_data['tanggal'])): ?>
                                        <p><strong>Tanggal Surat:</strong> <?php echo htmlspecialchars($letter_data['tanggal']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="flex space-x-2 ml-4">
                                <button onclick="viewLetter(<?php echo $letter['id']; ?>)" 
                                        class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                    <i class="fas fa-eye mr-1"></i>Lihat
                                </button>
                                <button onclick="regeneratePDF(<?php echo $letter['id']; ?>)" 
                                        class="bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                                    <i class="fas fa-download mr-1"></i>Unduh
                                </button>
                                <button onclick="deleteLetter(<?php echo $letter['id']; ?>)" 
                                        class="bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm">
                                    <i class="fas fa-trash mr-1"></i>Hapus
                                </button>
                            </div>
                        </div>
                        
                        <?php if ($letter['file_size']): ?>
                            <div class="text-xs text-gray-500">
                                <i class="fas fa-file-pdf mr-1"></i>
                                Ukuran file: <?php echo number_format($letter['file_size'] / 1024, 1); ?> KB
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-8 flex justify-center">
                    <nav class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&type=<?php echo $filter_type; ?>&date=<?php echo $filter_date; ?>&search=<?php echo urlencode($search); ?>" 
                               class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>&type=<?php echo $filter_type; ?>&date=<?php echo $filter_date; ?>&search=<?php echo urlencode($search); ?>" 
                               class="px-3 py-2 border rounded-lg transition-colors <?php echo $i === $page ? 'bg-purple-600 text-white border-purple-600' : 'bg-white border-gray-300 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&type=<?php echo $filter_type; ?>&date=<?php echo $filter_date; ?>&search=<?php echo urlencode($search); ?>" 
                               class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gray-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-history text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Riwayat Surat</h3>
                <p class="text-gray-600 mb-6">Mulai buat surat pertama Anda untuk melihat riwayat di sini</p>
                <a href="app.php" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Buat Surat Baru
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- View Letter Modal -->
    <div id="viewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-96 overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Detail Surat</h3>
                        <button onclick="hideViewModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="letterContent" class="text-sm text-gray-700">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewLetter(letterId) {
            // This would typically fetch letter details via AJAX
            // For now, we'll show a placeholder
            document.getElementById('letterContent').innerHTML = '<p>Loading letter details...</p>';
            document.getElementById('viewModal').classList.remove('hidden');
        }

        function hideViewModal() {
            document.getElementById('viewModal').classList.add('hidden');
        }

        function regeneratePDF(letterId) {
            // This would regenerate and download the PDF
            alert('Fitur regenerate PDF akan segera tersedia');
        }

        function deleteLetter(letterId) {
            if (confirm('Apakah Anda yakin ingin menghapus surat ini dari riwayat?')) {
                // This would delete the letter record
                alert('Fitur hapus surat akan segera tersedia');
            }
        }

        // Close modal when clicking outside
        document.getElementById('viewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideViewModal();
            }
        });
    </script>
</body>
</html>
