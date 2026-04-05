<?php
/**
 * Food-Saver - Public Report Generator
 * Simple PDF downloads for visitors/donors
 */

require_once __DIR__ . '/includes/config.php';

$db = getDB();
$reportType = $_GET['type'] ?? '';

// Get data based on report type
switch ($reportType) {
    case 'monthly':
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        $data = generateMonthlyReport($db, $month, $year);
        $filename = "FoodSaver_Monthly_Report_{$year}_{$month}";
        break;
        
    case 'yearly':
        $year = $_GET['year'] ?? date('Y');
        $data = generateYearlyReport($db, $year);
        $filename = "FoodSaver_Yearly_Report_{$year}";
        break;
        
    case 'restaurants':
        $data = generateRestaurantDirectory($db);
        $filename = "FoodSaver_Restaurant_Directory";
        break;
        
    case 'ngos':
        $data = generateNGODirectory($db);
        $filename = "FoodSaver_NGO_Directory";
        break;
        
    default:
        header('Location: pages/reports.php');
        exit;
}

// Output as HTML (can be printed/saved as PDF)
outputReport($data, $filename);

/**
 * Monthly Impact Report - Table format showing ALL records
 */
function generateMonthlyReport($db, $month, $year) {
    $monthName = date('F Y', strtotime("$year-$month-01"));
    
    // Get ALL food listings (not filtered by month)
    $stmt = $db->query("
        SELECT 
            fl.id,
            fl.food_name,
            fl.quantity,
            fl.status,
            r.restaurant_name,
            DATE_FORMAT(fl.created_at, '%d %b %Y') as posted_date,
            COALESCE(fl.people_served, 0) as people_served
        FROM food_listings fl
        LEFT JOIN restaurants r ON fl.restaurant_id = r.id
        ORDER BY fl.created_at DESC
    ");
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Summary stats
    $totalListings = count($listings);
    $delivered = array_filter($listings, fn($l) => $l['status'] === 'delivered');
    $peopleServed = array_sum(array_column($listings, 'people_served'));
    
    return [
        'title' => "Monthly Report - $monthName",
        'subtitle' => 'Detailed food distribution and donation statistics',
        'generated' => date('d M Y, h:i A'),
        'summary' => [
            'Total Listings' => $totalListings,
            'Delivered' => count($delivered),
            'People Served' => number_format($peopleServed)
        ],
        'columns' => ['#', 'Food Item', 'Quantity', 'Restaurant', 'Posted Date', 'Status', 'People Served'],
        'rows' => array_map(function($l, $i) {
            return [
                $i + 1,
                $l['food_name'],
                $l['quantity'],
                $l['restaurant_name'] ?? 'N/A',
                $l['posted_date'],
                ucfirst($l['status']),
                $l['people_served'] ?: '-'
            ];
        }, $listings, array_keys($listings)),
        'type' => 'table',
        'count' => $totalListings
    ];
}

/**
 * Yearly Impact Report - Table format showing ALL records
 */
function generateYearlyReport($db, $year) {
    // Get ALL food listings (not filtered by year)
    $stmt = $db->query("
        SELECT 
            fl.id,
            fl.food_name,
            fl.quantity,
            fl.status,
            r.restaurant_name,
            DATE_FORMAT(fl.created_at, '%d %b %Y') as posted_date,
            COALESCE(fl.people_served, 0) as people_served
        FROM food_listings fl
        LEFT JOIN restaurants r ON fl.restaurant_id = r.id
        ORDER BY fl.created_at DESC
    ");
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Summary stats
    $totalListings = count($listings);
    $delivered = array_filter($listings, fn($l) => $l['status'] === 'delivered');
    $peopleServed = array_sum(array_column($listings, 'people_served'));
    
    // Get total donations
    $donationTotal = $db->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'completed'")->fetchColumn();
    
    return [
        'title' => "Annual Report $year",
        'subtitle' => 'Comprehensive yearly overview and achievements',
        'generated' => date('d M Y, h:i A'),
        'summary' => [
            'Total Listings' => $totalListings,
            'Delivered' => count($delivered),
            'People Served' => number_format($peopleServed),
            'Total Donations' => '₹' . number_format($donationTotal, 2)
        ],
        'columns' => ['#', 'Food Item', 'Quantity', 'Restaurant', 'Posted Date', 'Status', 'People Served'],
        'rows' => array_map(function($l, $i) {
            return [
                $i + 1,
                $l['food_name'],
                $l['quantity'],
                $l['restaurant_name'] ?? 'N/A',
                $l['posted_date'],
                ucfirst($l['status']),
                $l['people_served'] ?: '-'
            ];
        }, $listings, array_keys($listings)),
        'type' => 'table',
        'count' => $totalListings
    ];
}

/**
 * Restaurant Directory
 */
function generateRestaurantDirectory($db) {
    $stmt = $db->query("
        SELECT restaurant_name, owner_name, address, city, state, pincode, phone, cuisine_type
        FROM restaurants 
        WHERE status = 'approved'
        ORDER BY restaurant_name
    ");
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'title' => 'Restaurant Partners Directory',
        'subtitle' => 'Our Food Donation Partners',
        'generated' => date('d M Y, h:i A'),
        'columns' => ['Restaurant Name', 'Owner', 'Address', 'City', 'Phone', 'Cuisine'],
        'rows' => array_map(function($r) {
            return [
                $r['restaurant_name'],
                $r['owner_name'],
                $r['address'],
                $r['city'] . ', ' . $r['state'],
                $r['phone'],
                $r['cuisine_type'] ?? '-'
            ];
        }, $restaurants),
        'type' => 'table',
        'count' => count($restaurants)
    ];
}

/**
 * NGO Directory
 */
function generateNGODirectory($db) {
    $stmt = $db->query("
        SELECT ngo_name, contact_person, address, city, state, pincode, phone, service_areas
        FROM ngos 
        WHERE status = 'approved'
        ORDER BY ngo_name
    ");
    $ngos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'title' => 'NGO Partners Directory',
        'subtitle' => 'Organizations Serving Communities',
        'generated' => date('d M Y, h:i A'),
        'columns' => ['NGO Name', 'Contact Person', 'Address', 'City', 'Phone', 'Service Areas'],
        'rows' => array_map(function($n) {
            return [
                $n['ngo_name'],
                $n['contact_person'],
                $n['address'],
                $n['city'] . ', ' . $n['state'],
                $n['phone'],
                $n['service_areas'] ?? '-'
            ];
        }, $ngos),
        'type' => 'table',
        'count' => count($ngos)
    ];
}

/**
 * Output HTML Report (Print as PDF)
 */
function outputReport($data, $filename) {
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($data['title']); ?> - FoodSaver</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; color: #333; background: #fff; }
        
        .print-btn { 
            position: fixed; top: 20px; right: 20px; 
            background: #2ecc71; color: white; border: none; 
            padding: 12px 24px; border-radius: 8px; cursor: pointer; 
            font-size: 16px; font-weight: 600;
            box-shadow: 0 4px 15px rgba(46,204,113,0.3);
        }
        .print-btn:hover { background: #27ae60; }
        
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #2ecc71; }
        .logo { font-size: 50px; margin-bottom: 10px; }
        .brand { font-size: 28px; font-weight: 700; color: #2ecc71; margin-bottom: 5px; }
        .title { font-size: 22px; color: #333; margin-bottom: 5px; }
        .subtitle { font-size: 14px; color: #666; }
        .meta { font-size: 12px; color: #999; margin-top: 10px; }
        
        .summary-bar {
            display: flex; flex-wrap: wrap; gap: 15px;
            background: #f8f9fa; padding: 15px 20px;
            border-radius: 8px; margin-bottom: 25px;
            border-left: 4px solid #2ecc71;
        }
        .summary-item { 
            padding-right: 20px; 
            border-right: 1px solid #ddd; 
        }
        .summary-item:last-child { border-right: none; }
        .summary-label { font-size: 11px; color: #888; text-transform: uppercase; }
        .summary-value { font-size: 18px; font-weight: 700; color: #2ecc71; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th { background: #2ecc71; color: white; padding: 12px 10px; text-align: left; font-weight: 600; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        tr:nth-child(even) { background: #f8f9fa; }
        tr:hover { background: #e8f5e9; }
        
        .status-delivered { color: #27ae60; font-weight: 600; }
        .status-claimed { color: #f39c12; font-weight: 600; }
        .status-available { color: #3498db; font-weight: 600; }
        .status-expired { color: #e74c3c; font-weight: 600; }
        
        .count-badge { 
            display: inline-block; 
            background: #2ecc71; color: white; 
            padding: 5px 15px; border-radius: 20px; 
            font-size: 14px; margin-bottom: 15px;
        }
        
        .footer { 
            margin-top: 40px; padding-top: 20px; 
            border-top: 1px solid #eee; 
            text-align: center; font-size: 12px; color: #999; 
        }
        
        @media print { 
            .print-btn { display: none; }
            body { padding: 15px; }
            table { font-size: 11px; }
            th, td { padding: 8px 6px; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">📄 Print / Save as PDF</button>
    
    <div class="header">
        <div class="logo">🌱</div>
        <div class="brand">Food-Saver</div>
        <h1 class="title"><?php echo htmlspecialchars($data['title']); ?></h1>
        <p class="subtitle"><?php echo htmlspecialchars($data['subtitle']); ?></p>
        <p class="meta">Generated: <?php echo htmlspecialchars($data['generated']); ?></p>
    </div>
    
    <?php if (!empty($data['summary'])): ?>
        <div class="summary-bar">
            <?php foreach ($data['summary'] as $label => $value): ?>
                <div class="summary-item">
                    <div class="summary-label"><?php echo htmlspecialchars($label); ?></div>
                    <div class="summary-value"><?php echo htmlspecialchars($value); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($data['type'] === 'table'): ?>
        <div class="count-badge">Total: <?php echo $data['count']; ?> Records</div>
        <table>
            <thead>
                <tr>
                    <?php foreach ($data['columns'] as $col): ?>
                        <th><?php echo htmlspecialchars($col); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['rows'])): ?>
                    <tr><td colspan="<?php echo count($data['columns']); ?>" style="text-align:center; padding: 30px; color: #999;">No data available for this period</td></tr>
                <?php else: ?>
                    <?php foreach ($data['rows'] as $row): ?>
                        <tr>
                            <?php foreach ($row as $cell): ?>
                                <?php 
                                $statusClass = '';
                                $cellLower = strtolower($cell);
                                if ($cellLower === 'delivered') $statusClass = 'status-delivered';
                                elseif ($cellLower === 'claimed') $statusClass = 'status-claimed';
                                elseif ($cellLower === 'available') $statusClass = 'status-available';
                                elseif ($cellLower === 'expired') $statusClass = 'status-expired';
                                ?>
                                <td class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($cell); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <div class="footer">
        <p>© <?php echo date('Y'); ?> Food-Saver | Reduce Food Waste. Feed the Hungry.</p>
        <p>This report is auto-generated. For official inquiries, contact support@foodsaver.org</p>
    </div>
</body>
</html>
    <?php
    exit;
}
