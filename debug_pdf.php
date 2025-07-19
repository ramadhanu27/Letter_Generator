<?php
require_once 'config/database.php';
require_once 'classes/User.php';

// Check if user is logged in
$is_logged_in = User::isLoggedIn();
$current_user = $is_logged_in ? User::getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug PDF Generation - Indonesian PDF Letter Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">
                <i class="fas fa-bug mr-2 text-red-600"></i>
                PDF Generation Debug Panel
            </h1>
            
            <!-- Authentication Status -->
            <div class="mb-8 p-4 rounded-lg <?php echo $is_logged_in ? 'bg-green-100 border border-green-300' : 'bg-red-100 border border-red-300'; ?>">
                <h2 class="text-lg font-semibold mb-2">
                    <i class="fas fa-user mr-2"></i>Authentication Status
                </h2>
                <?php if ($is_logged_in): ?>
                    <p class="text-green-800">✅ User is logged in</p>
                    <p class="text-sm text-gray-600">User: <?php echo htmlspecialchars($current_user['full_name']); ?> (<?php echo htmlspecialchars($current_user['email']); ?>)</p>
                    <a href="app.php" class="inline-block mt-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Go to Application
                    </a>
                <?php else: ?>
                    <p class="text-red-800">❌ User is not logged in</p>
                    <a href="login.php" class="inline-block mt-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Login
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Library Check -->
            <div class="mb-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h2 class="text-lg font-semibold mb-4">
                    <i class="fas fa-code mr-2"></i>Library Status
                </h2>
                <div id="library-status" class="space-y-2">
                    <p>Checking libraries...</p>
                </div>
            </div>
            
            <!-- Quick Test -->
            <div class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h2 class="text-lg font-semibold mb-4">
                    <i class="fas fa-flask mr-2"></i>Quick PDF Test
                </h2>
                <button onclick="quickTest()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-play mr-2"></i>Run Quick Test
                </button>
                <div id="test-results" class="mt-4"></div>
            </div>
            
            <!-- Form Test -->
            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Mini Form -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">
                        <i class="fas fa-edit mr-2"></i>Test Form
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Surat</label>
                            <select id="debug-jenis-surat" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="pernyataan">Surat Pernyataan</option>
                                <option value="izin">Surat Izin</option>
                                <option value="kuasa">Surat Kuasa</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                            <input type="text" id="debug-nama" value="Ahmad Wijaya" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                            <input type="date" id="debug-tanggal" value="2025-07-19" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        
                        <button onclick="testFormPDF()" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-file-pdf mr-2"></i>Generate Test PDF
                        </button>
                    </div>
                </div>
                
                <!-- Console Output -->
                <div class="bg-gray-900 text-green-400 rounded-lg p-6 font-mono text-sm">
                    <h3 class="text-white text-lg font-semibold mb-4">
                        <i class="fas fa-terminal mr-2"></i>Console Output
                    </h3>
                    <div id="console-output" class="h-64 overflow-y-auto">
                        <p>Ready for testing...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let consoleOutput = document.getElementById('console-output');
        
        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const colors = {
                info: 'text-green-400',
                error: 'text-red-400',
                success: 'text-blue-400',
                warning: 'text-yellow-400'
            };
            
            consoleOutput.innerHTML += `<p class="${colors[type]}">[${timestamp}] ${message}</p>`;
            consoleOutput.scrollTop = consoleOutput.scrollHeight;
        }
        
        function checkLibraries() {
            const status = document.getElementById('library-status');
            let html = '';
            
            // Check jsPDF
            if (window.jspdf) {
                html += '<p class="text-green-600">✅ jsPDF library loaded</p>';
                log('jsPDF library loaded successfully', 'success');
            } else {
                html += '<p class="text-red-600">❌ jsPDF library not found</p>';
                log('jsPDF library not found', 'error');
            }
            
            // Check Tailwind CSS
            if (document.querySelector('script[src*="tailwindcss"]')) {
                html += '<p class="text-green-600">✅ Tailwind CSS loaded</p>';
                log('Tailwind CSS loaded', 'success');
            } else {
                html += '<p class="text-yellow-600">⚠️ Tailwind CSS not detected</p>';
                log('Tailwind CSS not detected', 'warning');
            }
            
            // Check Font Awesome
            if (document.querySelector('link[href*="font-awesome"]')) {
                html += '<p class="text-green-600">✅ Font Awesome loaded</p>';
                log('Font Awesome loaded', 'success');
            } else {
                html += '<p class="text-yellow-600">⚠️ Font Awesome not detected</p>';
                log('Font Awesome not detected', 'warning');
            }
            
            status.innerHTML = html;
        }
        
        function quickTest() {
            log('Starting quick PDF test...', 'info');
            
            try {
                if (!window.jspdf) {
                    throw new Error('jsPDF library not available');
                }
                
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                
                log('jsPDF instance created', 'success');
                
                doc.setFont("helvetica", "normal");
                doc.setFontSize(12);
                doc.text("Test PDF Generation", 20, 20);
                doc.text("Indonesian PDF Letter Generator", 20, 30);
                doc.text("Generated at: " + new Date().toLocaleString(), 20, 40);
                
                log('Content added to PDF', 'success');
                
                doc.save('quick-test.pdf');
                
                log('PDF saved successfully!', 'success');
                
                document.getElementById('test-results').innerHTML = `
                    <div class="p-4 bg-green-100 text-green-800 rounded-lg">
                        <i class="fas fa-check-circle mr-2"></i>
                        Quick test passed! PDF generated successfully.
                    </div>
                `;
                
            } catch (error) {
                log('Error in quick test: ' + error.message, 'error');
                
                document.getElementById('test-results').innerHTML = `
                    <div class="p-4 bg-red-100 text-red-800 rounded-lg">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Quick test failed: ${error.message}
                    </div>
                `;
            }
        }
        
        function testFormPDF() {
            log('Starting form PDF test...', 'info');
            
            try {
                const jenisSurat = document.getElementById('debug-jenis-surat').value;
                const nama = document.getElementById('debug-nama').value;
                const tanggal = document.getElementById('debug-tanggal').value;
                
                log(`Form data - Type: ${jenisSurat}, Name: ${nama}, Date: ${tanggal}`, 'info');
                
                if (!window.jspdf) {
                    throw new Error('jsPDF library not available');
                }
                
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                
                doc.setFont("helvetica", "bold");
                doc.setFontSize(16);
                doc.text(`SURAT ${jenisSurat.toUpperCase()}`, 105, 30, { align: 'center' });
                
                doc.setFont("helvetica", "normal");
                doc.setFontSize(12);
                doc.text(`Nama: ${nama}`, 25, 60);
                doc.text(`Tanggal: ${tanggal}`, 25, 75);
                doc.text("Generated by Debug Panel", 25, 90);
                
                const filename = `debug-${jenisSurat}-${Date.now()}.pdf`;
                doc.save(filename);
                
                log(`Form PDF generated: ${filename}`, 'success');
                
            } catch (error) {
                log('Error in form PDF test: ' + error.message, 'error');
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            log('Debug panel initialized', 'info');
            checkLibraries();
        });
    </script>
</body>
</html>
