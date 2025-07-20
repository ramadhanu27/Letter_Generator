<?php
// Test URL Rewriting
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test URL Rewriting - Indonesian PDF Letter Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">
                <i class="fas fa-link mr-3 text-blue-600"></i>
                Test URL Rewriting
            </h1>
            
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Current Request Info</h2>
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <p><strong>Request URI:</strong> <code class="bg-gray-200 px-2 py-1 rounded"><?php echo $_SERVER['REQUEST_URI'] ?? 'N/A'; ?></code></p>
                    <p><strong>Script Name:</strong> <code class="bg-gray-200 px-2 py-1 rounded"><?php echo $_SERVER['SCRIPT_NAME'] ?? 'N/A'; ?></code></p>
                    <p><strong>Query String:</strong> <code class="bg-gray-200 px-2 py-1 rounded"><?php echo $_SERVER['QUERY_STRING'] ?? 'N/A'; ?></code></p>
                    <p><strong>HTTP Host:</strong> <code class="bg-gray-200 px-2 py-1 rounded"><?php echo $_SERVER['HTTP_HOST'] ?? 'N/A'; ?></code></p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Main Pages -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">
                        <i class="fas fa-home mr-2 text-green-600"></i>
                        Main Pages
                    </h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                            <span class="font-medium">Home</span>
                            <div class="space-x-2">
                                <a href="index" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Clean URL</a>
                                <a href="index.php" class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">With .php</a>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                            <span class="font-medium">Login</span>
                            <div class="space-x-2">
                                <a href="login" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Clean URL</a>
                                <a href="login.php" class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">With .php</a>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                            <span class="font-medium">Register</span>
                            <div class="space-x-2">
                                <a href="register" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Clean URL</a>
                                <a href="register.php" class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">With .php</a>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                            <span class="font-medium">Dashboard</span>
                            <div class="space-x-2">
                                <a href="dashboard" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Clean URL</a>
                                <a href="dashboard.php" class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">With .php</a>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                            <span class="font-medium">Profile</span>
                            <div class="space-x-2">
                                <a href="profile" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Clean URL</a>
                                <a href="profile.php" class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">With .php</a>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                            <span class="font-medium">Templates</span>
                            <div class="space-x-2">
                                <a href="templates" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Clean URL</a>
                                <a href="templates.php" class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">With .php</a>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                            <span class="font-medium">History</span>
                            <div class="space-x-2">
                                <a href="history" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Clean URL</a>
                                <a href="history.php" class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">With .php</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Pages -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">
                        <i class="fas fa-shield-alt mr-2 text-purple-600"></i>
                        Admin Pages
                    </h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                            <span class="font-medium">Admin Dashboard</span>
                            <div class="space-x-2">
                                <a href="admin" class="bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700">Clean URL</a>
                                <a href="admin.php" class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">With .php</a>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                            <span class="font-medium">Admin Login</span>
                            <div class="space-x-2">
                                <a href="admin/login" class="bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700">Clean URL</a>
                                <a href="admin_login.php" class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">With .php</a>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                            <span class="font-medium">Admin Register</span>
                            <div class="space-x-2">
                                <a href="admin/register" class="bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700">Clean URL</a>
                                <a href="admin_register.php" class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">With .php</a>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                            <span class="font-medium">Admin Users</span>
                            <div class="space-x-2">
                                <a href="admin/users" class="bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700">Clean URL</a>
                                <a href="admin_users.php" class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">With .php</a>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                            <span class="font-medium">Admin Logs</span>
                            <div class="space-x-2">
                                <a href="admin/logs" class="bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700">Clean URL</a>
                                <a href="admin_logs.php" class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">With .php</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Results -->
            <div class="mt-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">
                    <i class="fas fa-check-circle mr-2 text-green-600"></i>
                    URL Rewriting Status
                </h2>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-800 mb-2">✓ What Should Work</h3>
                        <ul class="text-sm text-green-700 space-y-1">
                            <li>• <code>/surat/login</code> → <code>login.php</code></li>
                            <li>• <code>/surat/admin</code> → <code>admin.php</code></li>
                            <li>• <code>/surat/admin/login</code> → <code>admin_login.php</code></li>
                            <li>• <code>/surat/dashboard</code> → <code>dashboard.php</code></li>
                            <li>• Auto redirect from .php URLs to clean URLs</li>
                        </ul>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 mb-2">ℹ️ How It Works</h3>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• External redirect: <code>.php</code> → clean URL</li>
                            <li>• Internal rewrite: clean URL → <code>.php</code></li>
                            <li>• Special admin routes handled separately</li>
                            <li>• File existence check prevents conflicts</li>
                            <li>• Preserves query parameters</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="index" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    <a href="login" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                    <a href="admin/login" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-shield-alt mr-2"></i>Admin Login
                    </a>
                    <a href="debug_session" class="bg-orange-600 text-white px-6 py-2 rounded-lg hover:bg-orange-700 transition-colors">
                        <i class="fas fa-bug mr-2"></i>Debug Session
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test URL rewriting with JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers to test links
            document.querySelectorAll('a[href]').forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    console.log('Navigating to:', href);
                    
                    // Show loading indicator for external links
                    if (!href.startsWith('#') && !href.startsWith('javascript:')) {
                        this.style.opacity = '0.7';
                        this.innerHTML += ' <i class="fas fa-spinner fa-spin ml-1"></i>';
                    }
                });
            });
        });
    </script>
</body>
</html>
