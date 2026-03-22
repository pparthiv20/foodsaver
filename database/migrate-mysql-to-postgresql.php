<?php
/**
 * Database Migration Script: MySQL to PostgreSQL (Supabase)
 * 
 * This script migrates data from your existing MySQL database to Supabase PostgreSQL.
 * 
 * IMPORTANT: 
 * 1. Ensure Supabase schema is created first (run schema-postgresql.sql)
 * 2. Update .env file with correct DATABASE_URL
 * 3. This script reads from the old database config, writes to Supabase
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Old MySQL Connection (your current database)
$oldDbHost = 'sql103.infinityfree.com';
$oldDbUser = 'ifo_41439659';
$oldDbPass = '2uSkHrVjAMZ8u2';
$oldDbName = 'ifo_41439659_food';

// New PostgreSQL Connection (Supabase) - from .env
$databaseUrl = getenv('DATABASE_URL');
if (!$databaseUrl) {
    die("ERROR: DATABASE_URL not found in .env file\n");
}

// Parse PostgreSQL connection URL
$dbUrl = parse_url($databaseUrl);
$newDbHost = $dbUrl['host'] ?? 'localhost';
$newDbPort = $dbUrl['port'] ?? 5432;
$newDbUser = $dbUrl['user'] ?? '';
$newDbPass = $dbUrl['pass'] ?? '';
$newDbName = ltrim($dbUrl['path'] ?? '/postgres', '/');

echo "=== Food-Saver Database Migration: MySQL to PostgreSQL ===\n\n";

// Connect to old MySQL database
echo "Connecting to old MySQL database...\n";
try {
    $oldDsn = "mysql:host=$oldDbHost;dbname=$oldDbName;charset=utf8mb4";
    $oldDb = new PDO($oldDsn, $oldDbUser, $oldDbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✓ Connected to MySQL\n";
} catch (PDOException $e) {
    die("✗ MySQL connection failed: " . $e->getMessage() . "\n");
}

// Connect to new PostgreSQL database
echo "Connecting to Supabase PostgreSQL database...\n";
try {
    $newDsn = "pgsql:host=$newDbHost;port=$newDbPort;dbname=$newDbName";
    $newDb = new PDO($newDsn, $newDbUser, $newDbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✓ Connected to PostgreSQL\n\n";
} catch (PDOException $e) {
    die("✗ PostgreSQL connection failed: " . $e->getMessage() . "\n");
}

// Tables to migrate (in order of dependencies)
$tables = [
    'admins',
    'restaurants',
    'ngos',
    'users',
    'food_listings',
    'donations',
    'feedback',
    'notifications',
    'activity_logs',
    'contact_messages',
    'password_resets',
    'otp_verifications',
    'site_settings',
];

$totalRecords = 0;

foreach ($tables as $table) {
    echo "Migrating table: $table\n";
    
    try {
        // Get data from MySQL
        $selectStmt = $oldDb->query("SELECT * FROM $table");
        $rows = $selectStmt->fetchAll();
        
        if (empty($rows)) {
            echo "  └─ No records to migrate\n";
            continue;
        }
        
        // Disable triggers temporarily for bulk insert
        if (in_array($table, ['admins', 'restaurants', 'ngos', 'users', 'food_listings', 'site_settings'])) {
            $triggerName = 'update_' . $table . '_updated_at';
            try {
                $newDb->exec("ALTER TABLE $table DISABLE TRIGGER $triggerName");
            } catch (Exception $e) {
                // Trigger might not exist for all tables
            }
        }
        
        // Prepare column names and values
        $columns = array_keys($rows[0]);
        $columnStr = implode(', ', $columns);
        $placeholders = implode(', ', array_map(fn($col) => ':' . $col, $columns));
        
        $insertSql = "INSERT INTO $table ($columnStr) VALUES ($placeholders)";
        $insertStmt = $newDb->prepare($insertSql);
        
        // Insert records
        $count = 0;
        foreach ($rows as $row) {
            try {
                // Handle ENUM value conversions if needed
                $insertStmt->execute($row);
                $count++;
            } catch (PDOException $e) {
                echo "  ✗ Error inserting record: " . $e->getMessage() . "\n";
            }
        }
        
        // Re-enable triggers
        if (in_array($table, ['admins', 'restaurants', 'ngos', 'users', 'food_listings', 'site_settings'])) {
            $triggerName = 'update_' . $table . '_updated_at';
            try {
                $newDb->exec("ALTER TABLE $table ENABLE TRIGGER $triggerName");
            } catch (Exception $e) {
                // Trigger might not exist
            }
        }
        
        echo "  └─ Migrated $count records\n";
        $totalRecords += $count;
        
    } catch (Exception $e) {
        echo "  ✗ Error migrating $table: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Migration Complete ===\n";
echo "Total records migrated: $totalRecords\n\n";

// Verify data integrity
echo "Verifying data integrity...\n";
foreach ($tables as $table) {
    try {
        $mysqlCount = $oldDb->query("SELECT COUNT(*) as cnt FROM $table")->fetch()['cnt'];
        $pgCount = $newDb->query("SELECT COUNT(*) as cnt FROM $table")->fetch()['cnt'];
        
        if ($mysqlCount == $pgCount) {
            echo "✓ $table: $pgCount records\n";
        } else {
            echo "✗ $table: MySQL=$mysqlCount, PostgreSQL=$pgCount (MISMATCH!)\n";
        }
    } catch (Exception $e) {
        echo "? $table: Could not verify\n";
    }
}

echo "\n=== Next Steps ===\n";
echo "1. Update your .env file with the correct DATABASE_URL password\n";
echo "2. Test the connection using test-connection.php\n";
echo "3. Update your application code if needed for PostgreSQL compatibility\n";
echo "4. Monitor your application logs for any database errors\n\n";

$oldDb = null;
$newDb = null;
