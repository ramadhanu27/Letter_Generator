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
    <title>Debug Session - Admin System</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-100 p-8'>
    <div class='max-w-4xl mx-auto'>
        <h1 class='text-2xl font-bold mb-6'>Debug Session & Admin System</h1>";

echo "<div class='grid md:grid-cols-2 gap-6'>";

// Session Information
echo "<div class='bg-white rounded-lg shadow p-6'>
        <h2 class='text-lg font-semibold mb-4 text-blue-600'>Session Information</h2>";

if (empty($_SESSION)) {
    echo "<p class='text-red-600'>No active session</p>";
} else {
    echo "<table class='w-full text-sm'>
            <thead>
                <tr class='border-b'>
                    <th class='text-left p-2'>Key</th>
                    <th class='text-left p-2'>Value</th>
                </tr>
            </thead>
            <tbody>";
    
    foreach ($_SESSION as $key => $value) {
        $display_value = is_array($value) ? json_encode($value) : htmlspecialchars($value);
        echo "<tr class='border-b'>
                <td class='p-2 font-medium'>$key</td>
                <td class='p-2'>$display_value</td>
              </tr>";
    }
    echo "</tbody></table>";
}

echo "</div>";

// User & Admin Status
echo "<div class='bg-white rounded-lg shadow p-6'>
        <h2 class='text-lg font-semibold mb-4 text-green-600'>User & Admin Status</h2>";

$is_logged_in = User::isLoggedIn();
$is_admin = Admin::isAdmin();
$current_user = User::getCurrentUser();

echo "<div class='space-y-2'>
        <p><strong>Is Logged In:</strong> <span class='" . ($is_logged_in ? 'text-green-600' : 'text-red-600') . "'>" . ($is_logged_in ? 'Yes' : 'No') . "</span></p>
        <p><strong>Is Admin:</strong> <span class='" . ($is_admin ? 'text-green-600' : 'text-red-600') . "'>" . ($is_admin ? 'Yes' : 'No') . "</span></p>";

if ($current_user) {
    echo "<p><strong>Current User:</strong></p>
          <ul class='ml-4 space-y-1'>
            <li>ID: {$current_user['id']}</li>
            <li>Username: {$current_user['username']}</li>
            <li>Email: {$current_user['email']}</li>
            <li>Role: {$current_user['role']}</li>
            <li>Active: " . ($current_user['is_active'] ? 'Yes' : 'No') . "</li>
          </ul>";
} else {
    echo "<p><strong>Current User:</strong> <span class='text-red-600'>None</span></p>";
}

echo "</div></div>";

echo "</div>";

// Database Check
echo "<div class='bg-white rounded-lg shadow p-6 mt-6'>
        <h2 class='text-lg font-semibold mb-4 text-purple-600'>Database Check</h2>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Check admin users
    $stmt = $conn->query("SELECT id, username, email, full_name, role, is_active FROM users WHERE role = 'admin'");
    $admins = $stmt->fetchAll();
    
    echo "<h3 class='font-medium mb-2'>Admin Users in Database:</h3>";
    if ($admins) {
        echo "<table class='w-full text-sm border'>
                <thead>
                    <tr class='bg-gray-50'>
                        <th class='border p-2 text-left'>ID</th>
                        <th class='border p-2 text-left'>Username</th>
                        <th class='border p-2 text-left'>Email</th>
                        <th class='border p-2 text-left'>Name</th>
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
                    <td class='border p-2'>{$admin['full_name']}</td>
                    <td class='border p-2 $active_class'>" . ($admin['is_active'] ? 'Yes' : 'No') . "</td>
                  </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p class='text-red-600'>No admin users found in database</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='text-red-600'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Actions
echo "<div class='bg-white rounded-lg shadow p-6 mt-6'>
        <h2 class='text-lg font-semibold mb-4 text-orange-600'>Actions</h2>
        <div class='space-x-4'>
            <a href='admin_login.php' class='bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700'>Admin Login</a>
            <a href='admin.php' class='bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700'>Admin Dashboard</a>
            <a href='login.php' class='bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700'>User Login</a>";

if ($is_logged_in) {
    echo "<a href='dashboard.php?action=logout' class='bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700'>Logout</a>";
}

echo "        </div>
      </div>";

// PHP Info
echo "<div class='bg-white rounded-lg shadow p-6 mt-6'>
        <h2 class='text-lg font-semibold mb-4 text-gray-600'>PHP Environment</h2>
        <div class='grid md:grid-cols-2 gap-4 text-sm'>
            <div>
                <p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>
                <p><strong>Session ID:</strong> " . session_id() . "</p>
                <p><strong>Session Name:</strong> " . session_name() . "</p>
            </div>
            <div>
                <p><strong>Server:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>
                <p><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</p>
                <p><strong>Script Name:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "</p>
            </div>
        </div>
      </div>";

echo "    </div>
</body>
</html>";
?>
