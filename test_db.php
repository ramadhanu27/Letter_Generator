<?php
// Test database connection
require_once 'config/database.php';

echo "<h2>Database Connection Test</h2>";

try {
    $database = new Database();
    $result = $database->testConnection();
    
    if ($result['status'] === 'success') {
        echo "<p style='color: green;'>✅ " . $result['message'] . "</p>";
        
        // Test if tables exist
        $conn = $database->getConnection();
        $tables = ['users', 'user_profiles', 'user_sessions', 'activity_logs'];
        
        echo "<h3>Table Check:</h3>";
        foreach ($tables as $table) {
            try {
                $stmt = $conn->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                if ($stmt->fetch()) {
                    echo "<p style='color: green;'>✅ Table '$table' exists</p>";
                } else {
                    echo "<p style='color: red;'>❌ Table '$table' does not exist</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error checking table '$table': " . $e->getMessage() . "</p>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>❌ " . $result['message'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>PHP Configuration:</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>PDO MySQL Available: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "</p>";

echo "<hr>";
echo "<h3>Database Configuration:</h3>";
echo "<p>Host: " . ($_ENV['DB_HOST'] ?? 'localhost') . "</p>";
echo "<p>Database: " . ($_ENV['DB_NAME'] ?? 'letter_generator_db') . "</p>";
echo "<p>Username: " . ($_ENV['DB_USER'] ?? 'root') . "</p>";
?>
