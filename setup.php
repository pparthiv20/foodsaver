<?php
/**
 * Food-Saver Database Setup Script
 * Run this file once to set up the database on localhost (XAMPP)
 * 
 * URL: http://localhost/food-saver-php/setup.php
 */

// Prevent running in production
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') === false) {
    die('This script can only be run on localhost for security reasons.');
}

$status = [];
$errors = [];

// Database connection settings for XAMPP
$host = 'localhost';
$username = 'root';
$password = '';
$dbName = 'food_saver';

echo '<!DOCTYPE html>
<html>
<head>
    <title>FoodSaver - Database Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Segoe UI", Arial, sans-serif; background: #f5f5f5; padding: 40px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #2ecc71, #27ae60); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 5px; }
        .header p { opacity: 0.9; }
        .content { padding: 30px; }
        .step { margin-bottom: 20px; padding: 15px; border-radius: 8px; border-left: 4px solid #ddd; background: #fafafa; }
        .step.success { border-left-color: #2ecc71; background: #e8f5e9; }
        .step.error { border-left-color: #e74c3c; background: #ffebee; }
        .step.info { border-left-color: #3498db; background: #e3f2fd; }
        .step h3 { margin-bottom: 5px; font-size: 16px; }
        .step p { color: #666; font-size: 14px; }
        .credentials { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-top: 20px; }
        .credentials h3 { margin-bottom: 15px; color: #2ecc71; }
        .cred-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .cred-item:last-child { border-bottom: none; }
        .cred-label { color: #666; }
        .cred-value { font-family: monospace; background: #e9ecef; padding: 2px 8px; border-radius: 4px; }
        .btn { display: inline-block; padding: 12px 24px; background: #2ecc71; color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; }
        .btn:hover { background: #27ae60; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 15px; margin-top: 20px; }
        .warning strong { color: #856404; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🌱 FoodSaver Setup</h1>
        <p>Database Installation & Configuration</p>
    </div>
    <div class="content">';

// Step 1: Connect to MySQL
try {
    $pdo = new PDO("mysql:host=$host", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $status[] = ['success', 'MySQL Connection', 'Successfully connected to MySQL server'];
} catch (PDOException $e) {
    $errors[] = "Could not connect to MySQL: " . $e->getMessage();
    echo '<div class="step error"><h3>❌ MySQL Connection Failed</h3><p>' . htmlspecialchars($e->getMessage()) . '</p></div>';
    echo '<div class="warning"><strong>⚠️ Make sure:</strong><br>1. XAMPP MySQL service is running<br>2. Default credentials (root with no password) are configured</div>';
    echo '</div></div></body></html>';
    exit;
}

// Step 2: Create database
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $status[] = ['success', 'Database Created', "Database '$dbName' is ready"];
} catch (PDOException $e) {
    $errors[] = "Could not create database: " . $e->getMessage();
}

// Step 3: Use the database
try {
    $pdo->exec("USE `$dbName`");
    $status[] = ['success', 'Database Selected', "Using database '$dbName'"];
} catch (PDOException $e) {
    $errors[] = "Could not select database: " . $e->getMessage();
}

// Step 4: Run schema.sql
$schemaFile = __DIR__ . '/database/schema.sql';
if (file_exists($schemaFile)) {
    try {
        $schema = file_get_contents($schemaFile);
        // Split by semicolons but handle multi-statement
        $pdo->exec($schema);
        $status[] = ['success', 'Schema Created', 'All tables created successfully'];
    } catch (PDOException $e) {
        // Tables might already exist
        if (strpos($e->getMessage(), 'already exists') !== false) {
            $status[] = ['info', 'Schema Check', 'Tables already exist (skipped recreation)'];
        } else {
            $errors[] = "Schema error: " . $e->getMessage();
        }
    }
} else {
    $errors[] = "Schema file not found at: $schemaFile";
}

// Step 5: Run dummy_data.sql
$dummyFile = __DIR__ . '/database/dummy_data.sql';
if (file_exists($dummyFile)) {
    try {
        $dummyData = file_get_contents($dummyFile);
        $pdo->exec($dummyData);
        $status[] = ['success', 'Dummy Data Loaded', 'Test data inserted successfully'];
    } catch (PDOException $e) {
        // Duplicate key errors are expected if data exists
        if (strpos($e->getMessage(), 'Duplicate') !== false) {
            $status[] = ['info', 'Dummy Data Check', 'Test data already exists (skipped)'];
        } else {
            $errors[] = "Data error: " . $e->getMessage();
        }
    }
} else {
    $status[] = ['info', 'Dummy Data', 'No dummy data file found (optional)'];
}

// Step 6: Verify installation
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $tableCount = count($tables);
    $status[] = ['success', 'Verification', "$tableCount tables found in database"];
} catch (PDOException $e) {
    $errors[] = "Verification failed: " . $e->getMessage();
}

// Display results
foreach ($status as $item) {
    $class = $item[0];
    $title = $item[1];
    $message = $item[2];
    $icon = $class === 'success' ? '✅' : ($class === 'error' ? '❌' : 'ℹ️');
    echo "<div class=\"step $class\"><h3>$icon $title</h3><p>$message</p></div>";
}

foreach ($errors as $error) {
    echo "<div class=\"step error\"><h3>❌ Error</h3><p>" . htmlspecialchars($error) . "</p></div>";
}

if (empty($errors)) {
    echo '
    <div class="credentials">
        <h3>🔐 Demo Login Credentials</h3>
        <div class="cred-item">
            <span class="cred-label">Admin Login</span>
            <span class="cred-value">admin / admin123</span>
        </div>
        <div class="cred-item">
            <span class="cred-label">Restaurant Login</span>
            <span class="cred-value">restaurant1 / test123</span>
        </div>
        <div class="cred-item">
            <span class="cred-label">NGO Login</span>
            <span class="cred-value">ngo1 / test123</span>
        </div>
        <div class="cred-item">
            <span class="cred-label">User Login</span>
            <span class="cred-value">user1 / test123</span>
        </div>
    </div>
    
    <a href="index.php" class="btn">🚀 Go to FoodSaver</a>
    
    <div class="warning">
        <strong>⚠️ Security Note:</strong> Delete this setup.php file after installation for security.
    </div>';
}

echo '</div></div></body></html>';
?>
