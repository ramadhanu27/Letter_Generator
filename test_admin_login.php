<?php
session_start();
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Admin.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test Admin Login</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-100 p-8'>
    <div class='max-w-2xl mx-auto'>
        <h1 class='text-2xl font-bold mb-6'>Test Admin Login System</h1>";

// Test login with hardcoded admin credentials
if (isset($_GET['test_login'])) {
    echo "<div class='bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6'>
            <h2 class='font-semibold text-blue-800 mb-2'>Testing Admin Login...</h2>";
    
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        // Find first admin user
        $stmt = $conn->query("SELECT * FROM users WHERE role = 'admin' AND is_active = 1 LIMIT 1");
        $admin = $stmt->fetch();
        
        if ($admin) {
            // Simulate login
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['full_name'] = $admin['full_name'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['admin_login_time'] = time();
            $_SESSION['is_admin'] = true;
            
            echo "<p class='text-green-600'>✓ Session set for admin: {$admin['username']}</p>";
            echo "<p class='text-green-600'>✓ User ID: {$admin['id']}</p>";
            echo "<p class='text-green-600'>✓ Role: {$admin['role']}</p>";
        } else {
            echo "<p class='text-red-600'>✗ No admin users found in database</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='text-red-600'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "</div>";
}

// Clear session
if (isset($_GET['clear_session'])) {
    session_destroy();
    session_start();
    echo "<div class='bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6'>
            <p class='text-yellow-800'>Session cleared</p>
          </div>";
}

// Current status
echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
        <h2 class='text-lg font-semibold mb-4'>Current Status</h2>";

$is_logged_in = User::isLoggedIn();
$is_admin = Admin::isAdmin();

echo "<div class='space-y-2'>
        <p><strong>User::isLoggedIn():</strong> <span class='" . ($is_logged_in ? 'text-green-600' : 'text-red-600') . "'>" . ($is_logged_in ? 'true' : 'false') . "</span></p>
        <p><strong>Admin::isAdmin():</strong> <span class='" . ($is_admin ? 'text-green-600' : 'text-red-600') . "'>" . ($is_admin ? 'true' : 'false') . "</span></p>";

if (isset($_SESSION['logged_in'])) {
    echo "<p><strong>Session logged_in:</strong> <span class='text-green-600'>" . ($_SESSION['logged_in'] ? 'true' : 'false') . "</span></p>";
}

if (isset($_SESSION['role'])) {
    echo "<p><strong>Session role:</strong> <span class='text-blue-600'>{$_SESSION['role']}</span></p>";
}

if (isset($_SESSION['user_id'])) {
    echo "<p><strong>Session user_id:</strong> <span class='text-blue-600'>{$_SESSION['user_id']}</span></p>";
}

echo "</div></div>";

// Test buttons
echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
        <h2 class='text-lg font-semibold mb-4'>Test Actions</h2>
        <div class='space-x-4'>
            <a href='?test_login=1' class='bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700'>Test Admin Login</a>
            <a href='?clear_session=1' class='bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700'>Clear Session</a>
            <a href='admin.php' class='bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700'>Go to Admin Dashboard</a>
            <a href='admin_login.php' class='bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700'>Admin Login Page</a>
        </div>
      </div>";

// Test redirect
if ($is_logged_in && $is_admin) {
    echo "<div class='bg-green-50 border border-green-200 rounded-lg p-4 mb-6'>
            <h3 class='font-semibold text-green-800 mb-2'>✓ Admin Access Granted</h3>
            <p class='text-green-700'>You should be able to access admin dashboard now.</p>
            <a href='admin.php' class='inline-block mt-2 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700'>Test Admin Dashboard Access</a>
          </div>";
} else {
    echo "<div class='bg-red-50 border border-red-200 rounded-lg p-4 mb-6'>
            <h3 class='font-semibold text-red-800 mb-2'>✗ Admin Access Denied</h3>
            <p class='text-red-700'>You need to login as admin first.</p>
          </div>";
}

// Database info
echo "<div class='bg-white rounded-lg shadow p-6'>
        <h2 class='text-lg font-semibold mb-4'>Database Admin Users</h2>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $stmt = $conn->query("SELECT id, username, email, role, is_active FROM users WHERE role = 'admin'");
    $admins = $stmt->fetchAll();
    
    if ($admins) {
        echo "<table class='w-full text-sm border'>
                <thead>
                    <tr class='bg-gray-50'>
                        <th class='border p-2 text-left'>ID</th>
                        <th class='border p-2 text-left'>Username</th>
                        <th class='border p-2 text-left'>Email</th>
                        <th class='border p-2 text-left'>Active</th>
                    </tr>
                </thead>
                <tbody>";
        
        foreach ($admins as $admin) {
            $active_class = $admin['is_active'] ? 'text-green-600' : 'text-red-600';
            echo "<tr>
                    <td class='border p-2'>{$admin['id']}</td>
                    <td class='border p-2'>{$admin['username']}</td>
                    <td class='border p-2'>{$admin['email']}</td>
                    <td class='border p-2 $active_class'>" . ($admin['is_active'] ? 'Yes' : 'No') . "</td>
                  </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p class='text-red-600'>No admin users found. <a href='admin_register.php' class='text-blue-600 underline'>Create one here</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p class='text-red-600'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

echo "    </div>
</body>
</html>";
?>
