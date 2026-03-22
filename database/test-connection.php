<?php
/**
 * Supabase Connection Test Script
 * 
 * Tests the connection to Supabase PostgreSQL database
 * and verifies that tables are properly created
 */

// Load config which includes .env loading
require_once __DIR__ . '/../includes/config.php';

echo "=== Food-Saver Supabase Connection Test ===\n\n";

// Check if DATABASE_URL is set
$databaseUrl = getenv('DATABASE_URL');
if (!$databaseUrl) {
    echo "✗ ERROR: DATABASE_URL not found in .env file\n";
    echo "  Please update your .env file with the correct Supabase connection string\n";
    exit(1);
}

echo "Database URL found: " . (strpos($databaseUrl, '[YOUR-PASSWORD]') !== false ? 
    "⚠ WARNING: Password placeholder still in use" : "✓ Connection string configured") . "\n";

// Parse connection details
$dbUrl = parse_url($databaseUrl);
$dbHost = $dbUrl['host'] ?? 'localhost';
$dbPort = $dbUrl['port'] ?? 5432;
$dbUser = $dbUrl['user'] ?? '';
$dbName = ltrim($dbUrl['path'] ?? '/postgres', '/');

echo "\nConnection Details:\n";
echo "  Host: $dbHost\n";
echo "  Port: $dbPort\n";
echo "  Database: $dbName\n";
echo "  User: $dbUser\n";

// Check if password is still a placeholder
if (strpos($databaseUrl, '[YOUR-PASSWORD]') !== false) {
    echo "\n✗ ERROR: Password placeholder detected!\n";
    echo "  Please update the DATABASE_URL in .env with your actual Supabase password\n\n";
    exit(1);
}

// Try to connect
echo "\nAttempting connection...\n";
try {
    $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName";
    $dbPass = $dbUrl['pass'] ?? '';
    
    $connection = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10,
    ]);
    
    echo "✓ Successfully connected to Supabase PostgreSQL!\n";
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test query execution
echo "\nTesting database functionality:\n";
try {
    $result = $connection->query("SELECT version()");
    $version = $result->fetch()['version'];
    echo "✓ PostgreSQL version: " . substr($version, 0, 50) . "...\n";
} catch (Exception $e) {
    echo "✗ Failed to query database: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if tables exist
echo "\nChecking tables:\n";
$tableCheckQuery = "
    SELECT table_name 
    FROM information_schema.tables 
    WHERE table_schema = 'public' 
    ORDER BY table_name
";

try {
    $result = $connection->query($tableCheckQuery);
    $tables = $result->fetchAll();
    
    if (empty($tables)) {
        echo "✗ No tables found! You need to run the schema-postgresql.sql first\n";
        echo "\nTo create tables:\n";
        echo "  1. Go to Supabase Dashboard → SQL Editor\n";
        echo "  2. Click 'New Query'\n";
        echo "  3. Copy the contents of database/schema-postgresql.sql\n";
        echo "  4. Paste and run in Supabase SQL Editor\n";
        exit(1);
    }
    
    echo "✓ Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "  • " . $table['table_name'] . "\n";
    }
} catch (Exception $e) {
    echo "✗ Failed to check tables: " . $e->getMessage() . "\n";
    exit(1);
}

// Test data insertion (on admins table)
echo "\nTesting data operations:\n";
try {
    $connection->beginTransaction();
    
    // Check if test record exists
    $stmt = $connection->prepare("SELECT COUNT(*) as cnt FROM admins WHERE username = 'test_connection'");
    $stmt->execute();
    $exists = $stmt->fetch()['cnt'] > 0;
    
    if (!$exists) {
        // Insert test record
        $stmt = $connection->prepare("
            INSERT INTO admins (username, email, password, full_name, status) 
            VALUES ('test_connection', 'test@localhost', 'test123', 'Test User', 'active')
        ");
        $stmt->execute();
        echo "✓ Successfully inserted test record\n";
    } else {
        echo "✓ Test record already exists\n";
    }
    
    // Read test record
    $stmt = $connection->prepare("SELECT * FROM admins WHERE username = 'test_connection'");
    $stmt->execute();
    $record = $stmt->fetch();
    if ($record) {
        echo "✓ Successfully read test record (ID: " . $record['id'] . ")\n";
    }
    
    // Clean up
    $stmt = $connection->prepare("DELETE FROM admins WHERE username = 'test_connection'");
    $stmt->execute();
    echo "✓ Cleaned up test data\n";
    
    $connection->commit();
} catch (Exception $e) {
    $connection->rollBack();
    echo "✗ Data operation failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test application config class
echo "\nTesting application config:\n";
try {
    require_once __DIR__ . '/../includes/config.php';
    $db = getDB();
    $result = $db->query("SELECT COUNT(*) as cnt FROM site_settings");
    $count = $result->fetch()['cnt'];
    echo "✓ Application config class works correctly\n";
    echo "✓ Found $count site settings in database\n";
} catch (Exception $e) {
    echo "⚠ Warning: Could not test config class: " . $e->getMessage() . "\n";
}

echo "\n=== Connection Test Complete ===\n";
echo "✓ All tests passed! Your Supabase connection is working correctly.\n\n";

echo "Next Steps:\n";
echo "1. If you haven't migrated data yet, run: php database/migrate-mysql-to-postgresql.php\n";
echo "2. Update your application to ensure it's using the new PostgreSQL connection\n";
echo "3. Monitor your application logs in the Supabase dashboard\n";
echo "4. Configure backups in the Supabase dashboard\n";

$connection = null;
