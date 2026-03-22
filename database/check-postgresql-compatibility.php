<?php
/**
 * PostgreSQL Compatibility Checker
 * 
 * Scans PHP files for MySQL-specific syntax that may not work with PostgreSQL
 */

echo "=== Food-Saver PostgreSQL Compatibility Check ===\n\n";

// Patterns that may cause issues in PostgreSQL
$patterns = [
    'mysql_' => [
        'pattern' => '/mysql_[a-z_]+\s*\(/i',
        'severity' => 'CRITICAL',
        'message' => 'Deprecated MySQL function (use PDO instead)',
        'action' => 'Replace with PDO statements'
    ],
    'LIMIT with comma' => [
        'pattern' => '/LIMIT\s+\d+\s*,\s*\d+/i',
        'severity' => 'WARNING',
        'message' => 'MySQL-style LIMIT offset (use LIMIT x OFFSET y for PostgreSQL)',
        'action' => 'Replace LIMIT 10,5 with LIMIT 5 OFFSET 10'
    ],
    'AUTO_INCREMENT' => [
        'pattern' => '/AUTO_INCREMENT/i',
        'severity' => 'WARNING',
        'message' => 'MySQL AUTO_INCREMENT keyword (schema already converted)',
        'action' => 'Only in migrations - production schema already uses SERIAL'
    ],
    'IFNULL' => [
        'pattern' => '/IFNULL\s*\(/i',
        'severity' => 'WARNING',
        'message' => 'MySQL IFNULL() - use COALESCE() in PostgreSQL',
        'action' => 'Replace IFNULL(a, b) with COALESCE(a, b)'
    ],
    'CONCAT' => [
        'pattern' => '/CONCAT\s*\(/i',
        'severity' => 'INFO',
        'message' => 'CONCAT function - PostgreSQL uses || or CONCAT()',
        'action' => 'Use || operator for string concatenation or CONCAT()'
    ],
    'STRAIGHT_JOIN' => [
        'pattern' => '/STRAIGHT_JOIN/i',
        'severity' => 'WARNING',
        'message' => 'MySQL STRAIGHT_JOIN hints not supported',
        'action' => 'Remove STRAIGHT_JOIN - let optimizer handle joins'
    ],
    'BACKQUOTES' => [
        'pattern' => '/`[a-z_]+`/i',
        'severity' => 'WARNING',
        'message' => 'MySQL backticks for identifiers - PostgreSQL uses double quotes',
        'action' => 'Replace backticks with double quotes'
    ],
    '@@variables' => [
        'pattern' => '/@@[a-z_]+/i',
        'severity' => 'WARNING',
        'message' => 'MySQL system variables - PostgreSQL uses different approach',
        'action' => 'Check if you can replace with PostgreSQL settings()'
    ],
];

// Files to check
$filesToCheck = [];
$dir = new RecursiveDirectoryIterator(__DIR__ . '/../');
$iter = new RecursiveIteratorIterator($dir);
$regex = new RegexIterator($iter, '/\.php$/');

// Build file list
foreach ($regex as $file) {
    $filePath = $file->getPathname();
    
    // Skip certain directories
    if (strpos($filePath, 'vendor') !== false || 
        strpos($filePath, 'PHPMailer') !== false ||
        strpos($filePath, 'docs') !== false) {
        continue;
    }
    
    $filesToCheck[] = $filePath;
}

echo "Scanning " . count($filesToCheck) . " PHP files...\n\n";

$issues = [];
$criticalCount = 0;
$warningCount = 0;
$infoCount = 0;

foreach ($filesToCheck as $file) {
    $content = file_get_contents($file);
    $lines = file($file);
    
    foreach ($patterns as $name => $check) {
        if (preg_match_all($check['pattern'], $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                // Find line number
                $lineNum = substr_count($content, "\n", 0, $match[1]) + 1;
                $lineContent = trim($lines[$lineNum - 1] ?? '');
                
                $issue = [
                    'file' => str_replace(__DIR__ . '/../', '', $file),
                    'line' => $lineNum,
                    'pattern' => $name,
                    'severity' => $check['severity'],
                    'message' => $check['message'],
                    'action' => $check['action'],
                    'found' => substr($lineContent, 0, 80),
                ];
                
                $issues[] = $issue;
                
                if ($check['severity'] === 'CRITICAL') $criticalCount++;
                elseif ($check['severity'] === 'WARNING') $warningCount++;
                else $infoCount++;
            }
        }
    }
}

// Display results
if (empty($issues)) {
    echo "✓ No PostgreSQL compatibility issues found!\n\n";
} else {
    echo "Found " . count($issues) . " potential issues:\n";
    echo "  • Critical: $criticalCount\n";
    echo "  • Warnings: $warningCount\n";
    echo "  • Info: $infoCount\n\n";
    
    // Group by severity
    $grouped = array_reduce($issues, function($carry, $item) {
        $carry[$item['severity']][] = $item;
        return $carry;
    }, []);
    
    foreach (['CRITICAL', 'WARNING', 'INFO'] as $severity) {
        if (isset($grouped[$severity])) {
            echo "\n=== $severity ISSUES ===\n";
            foreach ($grouped[$severity] as $issue) {
                echo "\n" . str_repeat("─", 70) . "\n";
                echo "File: " . $issue['file'] . ":" . $issue['line'] . "\n";
                echo "Pattern: " . $issue['pattern'] . "\n";
                echo "Issue: " . $issue['message'] . "\n";
                echo "Code: " . $issue['found'] . "\n";
                echo "Fix: " . $issue['action'] . "\n";
            }
        }
    }
}

echo "\n" . str_repeat("═", 70) . "\n";
echo "PostgreSQL Compatibility Check Complete\n";
echo str_repeat("═", 70) . "\n\n";

if ($criticalCount > 0) {
    echo "⚠ CRITICAL ISSUES FOUND - Please fix before migration\n";
    exit(1);
} elseif ($warningCount > 0) {
    echo "⚠ Some warnings found - Review and fix if needed\n";
    exit(0);
} else {
    echo "✓ Code is ready for PostgreSQL!\n";
    exit(0);
}
