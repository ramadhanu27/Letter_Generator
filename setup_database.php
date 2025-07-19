<?php
// Database setup script
echo "<h2>Database Setup for Indonesian PDF Letter Generator</h2>";

try {
    // First, connect to MySQL without specifying a database
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASS'] ?? '';
    $db_name = $_ENV['DB_NAME'] ?? 'letter_generator_db';
    
    echo "<p>Connecting to MySQL server...</p>";
    
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<p style='color: green;'>✅ Connected to MySQL server</p>";
    
    // Create database if it doesn't exist
    echo "<p>Creating database '$db_name' if it doesn't exist...</p>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✅ Database '$db_name' created/verified</p>";
    
    // Switch to the database
    $pdo->exec("USE `$db_name`");
    
    // Read and execute the schema file
    echo "<p>Reading schema file...</p>";
    $schema = file_get_contents('database/schema.sql');
    
    if ($schema === false) {
        throw new Exception("Could not read schema file");
    }
    
    // Remove the CREATE DATABASE and USE statements since we already handled them
    $schema = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/', '', $schema);
    $schema = preg_replace('/USE.*?;/', '', $schema);
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    echo "<p>Executing schema statements...</p>";
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^\s*--/', $statement)) {
            try {
                $pdo->exec($statement);
                echo "<p style='color: green; font-size: 12px;'>✅ Executed: " . substr($statement, 0, 50) . "...</p>";
            } catch (PDOException $e) {
                // Ignore "table already exists" errors
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "<p style='color: orange; font-size: 12px;'>⚠️ Warning: " . $e->getMessage() . "</p>";
                }
            }
        }
    }
    
    echo "<h3 style='color: green;'>✅ Database setup completed successfully!</h3>";
    
    // Test the connection with our Database class
    echo "<p>Testing connection with Database class...</p>";
    require_once 'config/database.php';
    
    $database = new Database();
    $result = $database->testConnection();
    
    if ($result['status'] === 'success') {
        echo "<p style='color: green;'>✅ Database class connection test passed</p>";
    } else {
        echo "<p style='color: red;'>❌ Database class connection test failed: " . $result['message'] . "</p>";
    }
    
    echo "<hr>";
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li>✅ Database is ready</li>";
    echo "<li>✅ You can now access the application</li>";
    echo "<li>📝 Default login credentials:</li>";
    echo "<ul>";
    echo "<li>Username: <strong>admin</strong> | Password: <strong>password</strong></li>";
    echo "<li>Username: <strong>demo_user</strong> | Password: <strong>password</strong></li>";
    echo "</ul>";
    echo "<li>🔗 <a href='login.php'>Go to Login Page</a></li>";
    echo "<li>🔗 <a href='app.php'>Go to Application</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your MySQL server is running and the credentials are correct.</p>";
}
?>
