<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Setup Admin System - Indonesian PDF Letter Generator</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-100 min-h-screen py-8'>
    <div class='max-w-4xl mx-auto'>
        <div class='bg-white rounded-xl shadow-lg p-8'>
            <h1 class='text-2xl font-bold text-gray-800 mb-6'>
                <i class='fas fa-shield-alt mr-2 text-blue-600'></i>
                Setup Admin System
            </h1>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<div class='space-y-4'>";
    
    // Read and execute schema update
    $schema_file = 'database/admin_schema_update.sql';
    if (!file_exists($schema_file)) {
        throw new Exception("Schema file not found: $schema_file");
    }
    
    $schema = file_get_contents($schema_file);
    if ($schema === false) {
        throw new Exception("Could not read schema file");
    }
    
    echo "<div class='bg-blue-50 border border-blue-200 rounded-lg p-4'>
            <h3 class='font-semibold text-blue-800 mb-2'>Executing Database Updates...</h3>
          </div>";
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', preg_split('/;(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $schema)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^\s*--/', $statement) && !preg_match('/^\s*\/\*/', $statement)) {
            try {
                $conn->exec($statement);
                $success_count++;
                
                // Show what was executed (first 100 chars)
                $preview = substr(trim($statement), 0, 100);
                if (strlen($statement) > 100) $preview .= '...';
                
                echo "<div class='bg-green-50 border border-green-200 rounded-lg p-3 mb-2'>
                        <div class='flex items-center'>
                            <i class='fas fa-check-circle text-green-600 mr-2'></i>
                            <span class='text-green-800 text-sm'>$preview</span>
                        </div>
                      </div>";
                
            } catch (PDOException $e) {
                $error_count++;
                $error_msg = $e->getMessage();
                
                // Ignore certain expected errors
                if (strpos($error_msg, 'already exists') !== false || 
                    strpos($error_msg, 'Duplicate column') !== false ||
                    strpos($error_msg, 'Duplicate key') !== false) {
                    echo "<div class='bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-2'>
                            <div class='flex items-center'>
                                <i class='fas fa-exclamation-triangle text-yellow-600 mr-2'></i>
                                <span class='text-yellow-800 text-sm'>Skipped (already exists): " . substr(trim($statement), 0, 50) . "...</span>
                            </div>
                          </div>";
                } else {
                    echo "<div class='bg-red-50 border border-red-200 rounded-lg p-3 mb-2'>
                            <div class='flex items-center'>
                                <i class='fas fa-times-circle text-red-600 mr-2'></i>
                                <span class='text-red-800 text-sm'>Error: $error_msg</span>
                            </div>
                          </div>";
                }
            }
        }
    }
    
    echo "<div class='bg-green-100 border border-green-400 rounded-lg p-4 mt-6'>
            <h3 class='font-semibold text-green-800 mb-2'>
                <i class='fas fa-check-circle mr-2'></i>Database Update Completed
            </h3>
            <p class='text-green-700'>Successfully executed $success_count statements.</p>
          </div>";
    
    // Check if admin user exists and create if needed
    echo "<div class='bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6'>
            <h3 class='font-semibold text-blue-800 mb-2'>
                <i class='fas fa-user-shield mr-2'></i>Admin User Setup
            </h3>";
    
    // Check for existing admin
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p class='text-blue-700'>Admin user already exists:</p>
              <ul class='list-disc list-inside text-blue-700 mt-2'>
                <li>Username: " . htmlspecialchars($admin['username']) . "</li>
                <li>Email: " . htmlspecialchars($admin['email']) . "</li>
                <li>Role: " . htmlspecialchars($admin['role']) . "</li>
              </ul>";
    } else {
        // Create default admin user
        $admin_username = 'admin';
        $admin_email = 'admin@lettergen.com';
        $admin_password = 'admin123';
        $admin_name = 'Administrator';
        
        // Hash password
        $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
        
        try {
            $conn->beginTransaction();
            
            // Insert admin user
            $stmt = $conn->prepare("
                INSERT INTO users (username, email, password_hash, full_name, role, is_active, email_verified) 
                VALUES (?, ?, ?, ?, 'admin', 1, 1)
            ");
            $stmt->execute([$admin_username, $admin_email, $password_hash, $admin_name]);
            $admin_id = $conn->lastInsertId();
            
            // Insert admin profile
            $stmt = $conn->prepare("
                INSERT INTO user_profiles (user_id, phone, organization, position, address, city, province, postal_code) 
                VALUES (?, '', 'Indonesian PDF Letter Generator', 'System Administrator', '', '', '', '')
            ");
            $stmt->execute([$admin_id]);
            
            $conn->commit();
            
            echo "<div class='bg-green-50 border border-green-200 rounded-lg p-3 mt-3'>
                    <p class='text-green-700 font-semibold'>Default admin user created successfully!</p>
                    <ul class='list-disc list-inside text-green-700 mt-2'>
                        <li>Username: <strong>$admin_username</strong></li>
                        <li>Email: <strong>$admin_email</strong></li>
                        <li>Password: <strong>$admin_password</strong></li>
                    </ul>
                    <p class='text-green-600 text-sm mt-2'>
                        <i class='fas fa-exclamation-triangle mr-1'></i>
                        Please change the default password after first login!
                    </p>
                  </div>";
                  
        } catch (Exception $e) {
            $conn->rollBack();
            echo "<div class='bg-red-50 border border-red-200 rounded-lg p-3 mt-3'>
                    <p class='text-red-700'>Failed to create admin user: " . $e->getMessage() . "</p>
                  </div>";
        }
    }
    
    echo "</div>";
    
    // Test admin class
    echo "<div class='bg-purple-50 border border-purple-200 rounded-lg p-4 mt-6'>
            <h3 class='font-semibold text-purple-800 mb-2'>
                <i class='fas fa-cogs mr-2'></i>System Test
            </h3>";
    
    try {
        require_once 'classes/Admin.php';
        
        // Test dashboard stats
        $stats = Admin::getDashboardStats();
        echo "<p class='text-purple-700'>✅ Admin class loaded successfully</p>";
        echo "<p class='text-purple-700'>✅ Dashboard stats: " . $stats['total_users'] . " users, " . $stats['total_letters'] . " letters</p>";
        
        // Test system settings
        $settings = Admin::getSystemSettings();
        echo "<p class='text-purple-700'>✅ System settings loaded: " . count($settings) . " settings</p>";
        
    } catch (Exception $e) {
        echo "<p class='text-red-700'>❌ Admin class test failed: " . $e->getMessage() . "</p>";
    }
    
    echo "</div>";
    
    echo "</div>";
    
    // Next steps
    echo "<div class='bg-gray-50 border border-gray-200 rounded-lg p-6 mt-8'>
            <h3 class='font-semibold text-gray-800 mb-4'>
                <i class='fas fa-list-check mr-2'></i>Next Steps
            </h3>
            <ol class='list-decimal list-inside space-y-2 text-gray-700'>
                <li>Login as admin using the credentials above</li>
                <li>Access admin panel at: <a href='admin.php' class='text-blue-600 hover:underline'>admin.php</a></li>
                <li>Change default admin password in admin settings</li>
                <li>Configure system settings as needed</li>
                <li>Create additional admin users if required</li>
                <li>Delete this setup file for security</li>
            </ol>
            
            <div class='mt-6 flex space-x-4'>
                <a href='admin.php' class='bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors'>
                    <i class='fas fa-shield-alt mr-2'></i>Go to Admin Panel
                </a>
                <a href='login.php' class='bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors'>
                    <i class='fas fa-sign-in-alt mr-2'></i>Login as Admin
                </a>
                <a href='dashboard.php' class='bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors'>
                    <i class='fas fa-user mr-2'></i>User Dashboard
                </a>
            </div>
          </div>";
    
} catch (Exception $e) {
    echo "<div class='bg-red-100 border border-red-400 rounded-lg p-4'>
            <h3 class='font-semibold text-red-800 mb-2'>
                <i class='fas fa-exclamation-triangle mr-2'></i>Setup Failed
            </h3>
            <p class='text-red-700'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
            <p class='text-red-600 text-sm mt-2'>Please check your database connection and try again.</p>
          </div>";
}

echo "        </div>
    </div>
</body>
</html>";
?>
