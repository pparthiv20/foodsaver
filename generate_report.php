<?php
/**
 * Food-Saver Report Generator
 * Simple PDF and Excel report generation without external libraries
 * College-level implementation using PHP built-in functions
 */

require_once __DIR__ . '/includes/config.php';

// Require admin authentication
requireAuth(['admin']);

// Create reports directory if it doesn't exist
$reportsDir = __DIR__ . '/reports';
if (!is_dir($reportsDir)) {
    mkdir($reportsDir, 0755, true);
}

$reportType = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'pdf';

$db = getDB();

/**
 * Generate Donation Report
 */
function generateDonationReport($db, $format) {
    $stmt = $db->query("
        SELECT 
            d.id,
            CASE WHEN d.anonymous = 1 THEN 'Anonymous' ELSE COALESCE(u.full_name, 'Guest') END as donor_name,
            n.ngo_name,
            d.amount,
            d.payment_method,
            d.transaction_id,
            d.status,
            d.message,
            DATE_FORMAT(d.created_at, '%Y-%m-%d %H:%i') as donation_date
        FROM donations d
        LEFT JOIN users u ON d.user_id = u.id
        LEFT JOIN ngos n ON d.ngo_id = n.id
        ORDER BY d.created_at DESC
    ");
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalAmount = 0;
    $completedAmount = 0;
    foreach ($donations as $d) {
        $totalAmount += $d['amount'];
        if ($d['status'] == 'completed') {
            $completedAmount += $d['amount'];
        }
    }
    
    $data = [
        'title' => 'Donation Report',
        'generated' => date('Y-m-d H:i:s'),
        'summary' => [
            'Total Donations' => count($donations),
            'Total Amount' => '₹' . number_format($totalAmount, 2),
            'Completed Amount' => '₹' . number_format($completedAmount, 2),
        ],
        'columns' => ['ID', 'Donor', 'NGO', 'Amount', 'Method', 'Status', 'Date'],
        'rows' => array_map(function($d) {
            return [
                $d['id'],
                $d['donor_name'],
                $d['ngo_name'] ?? 'N/A',
                '₹' . number_format($d['amount'], 2),
                ucfirst(str_replace('_', ' ', $d['payment_method'])),
                ucfirst($d['status']),
                $d['donation_date']
            ];
        }, $donations)
    ];
    
    return $data;
}

/**
 * Generate Food Request/Fulfillment Report
 */
function generateRequestReport($db, $format) {
    $stmt = $db->query("
        SELECT 
            f.id,
            f.food_name,
            f.food_type,
            f.quantity,
            f.servings,
            r.restaurant_name,
            COALESCE(n.ngo_name, 'Not Claimed') as requester,
            f.status,
            f.pickup_address,
            DATE_FORMAT(f.created_at, '%Y-%m-%d') as created_date,
            DATE_FORMAT(f.claimed_at, '%Y-%m-%d %H:%i') as claimed_date,
            DATE_FORMAT(f.delivered_at, '%Y-%m-%d %H:%i') as delivered_date,
            f.people_served
        FROM food_listings f
        LEFT JOIN restaurants r ON f.restaurant_id = r.id
        LEFT JOIN ngos n ON f.claimed_by = n.id
        ORDER BY f.created_at DESC
    ");
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $statusCounts = ['available' => 0, 'claimed' => 0, 'picked_up' => 0, 'delivered' => 0, 'expired' => 0];
    $totalServed = 0;
    foreach ($listings as $l) {
        if (isset($statusCounts[$l['status']])) {
            $statusCounts[$l['status']]++;
        }
        $totalServed += $l['people_served'];
    }
    
    $data = [
        'title' => 'Food Request Fulfillment Report',
        'generated' => date('Y-m-d H:i:s'),
        'summary' => [
            'Total Listings' => count($listings),
            'Available' => $statusCounts['available'],
            'Claimed' => $statusCounts['claimed'],
            'Delivered' => $statusCounts['delivered'],
            'People Served' => $totalServed
        ],
        'columns' => ['ID', 'Food', 'Type', 'Restaurant', 'Requester (NGO)', 'Status', 'People Served', 'Date'],
        'rows' => array_map(function($l) {
            return [
                $l['id'],
                $l['food_name'],
                ucfirst($l['food_type']),
                $l['restaurant_name'],
                $l['requester'],
                ucfirst(str_replace('_', ' ', $l['status'])),
                $l['people_served'],
                $l['created_date']
            ];
        }, $listings)
    ];
    
    return $data;
}

/**
 * Generate Transaction Summary Report
 */
function generateTransactionReport($db, $format) {
    $stmt = $db->query("
        SELECT 
            d.id,
            d.transaction_id,
            CASE WHEN d.anonymous = 1 THEN 'Anonymous' ELSE COALESCE(u.full_name, 'Guest') END as payer,
            n.ngo_name as recipient,
            d.amount,
            d.currency,
            d.payment_method,
            d.status,
            DATE_FORMAT(d.created_at, '%Y-%m-%d %H:%i:%s') as timestamp
        FROM donations d
        LEFT JOIN users u ON d.user_id = u.id
        LEFT JOIN ngos n ON d.ngo_id = n.id
        ORDER BY d.created_at DESC
    ");
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $statusTotals = ['pending' => 0, 'completed' => 0, 'failed' => 0, 'refunded' => 0];
    $methodCounts = [];
    foreach ($transactions as $t) {
        if (isset($statusTotals[$t['status']])) {
            $statusTotals[$t['status']] += $t['amount'];
        }
        $method = $t['payment_method'];
        $methodCounts[$method] = ($methodCounts[$method] ?? 0) + 1;
    }
    
    $data = [
        'title' => 'Transaction Summary Report',
        'generated' => date('Y-m-d H:i:s'),
        'summary' => [
            'Total Transactions' => count($transactions),
            'Completed Value' => '₹' . number_format($statusTotals['completed'], 2),
            'Pending Value' => '₹' . number_format($statusTotals['pending'], 2),
            'Failed Value' => '₹' . number_format($statusTotals['failed'], 2),
        ],
        'columns' => ['Trans ID', 'Payer', 'Recipient', 'Amount', 'Method', 'Status', 'Timestamp'],
        'rows' => array_map(function($t) {
            return [
                $t['transaction_id'] ?? ('TXN-' . $t['id']),
                $t['payer'],
                $t['recipient'] ?? 'N/A',
                $t['currency'] . ' ' . number_format($t['amount'], 2),
                ucfirst(str_replace('_', ' ', $t['payment_method'])),
                ucfirst($t['status']),
                $t['timestamp']
            ];
        }, $transactions)
    ];
    
    return $data;
}

/**
 * Generate PDF output (HTML-based for simplicity)
 */
function outputPDF($data, $filename) {
    // For college-level project, we'll create an HTML file that can be printed as PDF
    // Production would use TCPDF or similar library
    
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: inline; filename="' . $filename . '.html"');
    
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($data['title']) . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2ecc71; padding-bottom: 20px; }
        .header h1 { color: #2ecc71; font-size: 28px; margin-bottom: 5px; }
        .header .logo { font-size: 40px; margin-bottom: 10px; }
        .header .subtitle { color: #666; font-size: 14px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px; }
        .meta div { font-size: 12px; color: #666; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .summary-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #2ecc71; }
        .summary-card .value { font-size: 24px; font-weight: bold; color: #2ecc71; }
        .summary-card .label { font-size: 12px; color: #666; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th { background: #2ecc71; color: white; padding: 12px 8px; text-align: left; font-weight: 600; }
        td { padding: 10px 8px; border-bottom: 1px solid #eee; }
        tr:nth-child(even) { background: #f8f9fa; }
        tr:hover { background: #e8f5e9; }
        .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #999; border-top: 1px solid #eee; padding-top: 15px; }
        .print-btn { position: fixed; top: 20px; right: 20px; background: #2ecc71; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .print-btn:hover { background: #27ae60; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
    
    <div class="header">
        <div class="logo">🌱</div>
        <h1>Food-Saver</h1>
        <div class="subtitle">Reduce Food Waste. Feed the Hungry.</div>
    </div>
    
    <h2 style="margin-bottom: 20px; color: #333;">' . htmlspecialchars($data['title']) . '</h2>
    
    <div class="meta">
        <div><strong>Generated:</strong> ' . htmlspecialchars($data['generated']) . '</div>
        <div><strong>Records:</strong> ' . count($data['rows']) . '</div>
    </div>
    
    <div class="summary">';
    
    foreach ($data['summary'] as $label => $value) {
        $html .= '<div class="summary-card">
            <div class="value">' . htmlspecialchars($value) . '</div>
            <div class="label">' . htmlspecialchars($label) . '</div>
        </div>';
    }
    
    $html .= '</div>
    
    <table>
        <thead>
            <tr>';
    
    foreach ($data['columns'] as $col) {
        $html .= '<th>' . htmlspecialchars($col) . '</th>';
    }
    
    $html .= '</tr>
        </thead>
        <tbody>';
    
    foreach ($data['rows'] as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>' . htmlspecialchars($cell) . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</tbody>
    </table>
    
    <div class="footer">
        <p>© ' . date('Y') . ' Food-Saver | This report was auto-generated from the database</p>
        <p>For official use only. Data accurate as of ' . date('Y-m-d H:i:s') . '</p>
    </div>
</body>
</html>';
    
    echo $html;
    exit;
}

/**
 * Generate Excel output (CSV format - universally compatible)
 */
function outputExcel($data, $filename) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Title and metadata
    fputcsv($output, [$data['title']]);
    fputcsv($output, ['Generated: ' . $data['generated']]);
    fputcsv($output, []);
    
    // Summary
    fputcsv($output, ['=== SUMMARY ===']);
    foreach ($data['summary'] as $label => $value) {
        fputcsv($output, [$label, $value]);
    }
    fputcsv($output, []);
    
    // Data table
    fputcsv($output, ['=== DETAILED DATA ===']);
    fputcsv($output, $data['columns']);
    
    foreach ($data['rows'] as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

// Handle report generation
switch ($reportType) {
    case 'donation':
        $data = generateDonationReport($db, $format);
        $filename = 'donation_report_' . date('Y-m-d');
        break;
        
    case 'request':
        $data = generateRequestReport($db, $format);
        $filename = 'request_fulfillment_report_' . date('Y-m-d');
        break;
        
    case 'transaction':
        $data = generateTransactionReport($db, $format);
        $filename = 'transaction_summary_' . date('Y-m-d');
        break;
        
    default:
        header('Location: dashboards/admin.php?page=reports');
        exit;
}

// Output in requested format
if ($format === 'excel' || $format === 'csv') {
    outputExcel($data, $filename);
} else {
    outputPDF($data, $filename);
}
