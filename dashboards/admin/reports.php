<?php
/**
 * Food-Saver - Admin Reports Page
 * Display and download various system reports in PDF format
 */

// Get database statistics for reports
$reportStats = [
    'total_restaurants' => $db->query("SELECT COUNT(*) FROM restaurants")->fetchColumn(),
    'approved_restaurants' => $db->query("SELECT COUNT(*) FROM restaurants WHERE status = 'approved'")->fetchColumn(),
    'total_ngos' => $db->query("SELECT COUNT(*) FROM ngos")->fetchColumn(),
    'approved_ngos' => $db->query("SELECT COUNT(*) FROM ngos WHERE status = 'approved'")->fetchColumn(),
    'total_food_listings' => $db->query("SELECT COUNT(*) FROM food_listings")->fetchColumn(),
    'available_food' => $db->query("SELECT COUNT(*) FROM food_listings WHERE status = 'available'")->fetchColumn(),
    'food_delivered' => $db->query("SELECT COUNT(*) FROM food_listings WHERE status = 'delivered'")->fetchColumn(),
    'total_donations' => $db->query("SELECT COUNT(*) FROM donations WHERE status = 'completed'")->fetchColumn(),
    'total_donation_amount' => $db->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'completed'")->fetchColumn(),
    'total_users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
];

// Get monthly data for charts
$monthlyDonations = $db->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total
    FROM donations
    WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
")->fetchAll(PDO::FETCH_ASSOC);

$monthlyFood = $db->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total
    FROM food_listings
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Handle PDF download requests
if (isset($_GET['download_pdf'])) {
    $reportType = $_GET['download_pdf'];
    
    // You can generate PDFs here using a library like TCPDF or mPDF
    // For now, we'll create a simple text-based download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $reportType . '_' . date('Y-m-d') . '.pdf"');
    
    // This is a placeholder - implement actual PDF generation
    echo "PDF generation for: $reportType\n";
    echo "Generated on: " . date('Y-m-d H:i:s') . "\n";
    exit;
}
?>

<!-- Reports Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-chart-bar"></i> Reports & Analytics
    </h1>
    <p class="page-subtitle">Download and view system reports in PDF format</p>
</div>

<!-- Summary Cards -->
<div class="dashboard-stats mb-4">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-file-pdf"></i>
        </div>
        <div class="stat-value"><?php echo count($monthlyDonations); ?></div>
        <div class="stat-label">Available Reports</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon secondary">
            <i class="fas fa-utensils"></i>
        </div>
        <div class="stat-value"><?php echo number_format($reportStats['total_restaurants']); ?></div>
        <div class="stat-label">Active Restaurants</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-apple-alt"></i>
        </div>
        <div class="stat-value"><?php echo number_format($reportStats['total_food_listings']); ?></div>
        <div class="stat-label">Food Listings</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-donate"></i>
        </div>
        <div class="stat-value">₹<?php echo number_format($reportStats['total_donation_amount']); ?></div>
        <div class="stat-label">Total Donations</div>
    </div>
</div>

<!-- Reports Grid -->
<div class="grid grid-2">
    <!-- System Reports Section -->
    <div class="card">
        <div class="card-header">
            <h3>System Reports</h3>
            <p class="card-subtitle">General system statistics and information</p>
        </div>
        <div class="card-body">
            <div class="reports-list">
                <!-- Executive Summary Report -->
                <div class="report-item">
                    <div class="report-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="report-info">
                        <h4 class="report-title">Executive Summary</h4>
                        <p class="report-description">Overall system performance and key metrics</p>
                        <div class="report-meta">
                            <span class="report-date">Generated: <?php echo date('M d, Y'); ?></span>
                            <span class="report-size">~2.5 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn btn-sm btn-primary" onclick="downloadReport('executive-summary')">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                </div>

                <!-- Quarterly Report -->
                <div class="report-item">
                    <div class="report-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="report-info">
                        <h4 class="report-title">Quarterly Report Q<?php echo ceil(date('n')/3); ?> <?php echo date('Y'); ?></h4>
                        <p class="report-description">Detailed quarterly performance analysis and trends</p>
                        <div class="report-meta">
                            <span class="report-date">Generated: <?php echo date('M d, Y'); ?></span>
                            <span class="report-size">~3.8 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn btn-sm btn-primary" onclick="downloadReport('quarterly-report')">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                </div>

                <!-- Annual Report -->
                <div class="report-item">
                    <div class="report-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="report-info">
                        <h4 class="report-title">Annual Report <?php echo date('Y'); ?></h4>
                        <p class="report-description">Comprehensive yearly overview and achievements</p>
                        <div class="report-meta">
                            <span class="report-date">Generated: <?php echo date('M d, Y'); ?></span>
                            <span class="report-size">~5.2 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn btn-sm btn-primary" onclick="downloadReport('annual-report')">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Operations Reports Section -->
    <div class="card">
        <div class="card-header">
            <h3>Operations Reports</h3>
            <p class="card-subtitle">Restaurants, NGOs, and food logistics</p>
        </div>
        <div class="card-body">
            <div class="reports-list">
                <!-- Restaurants Report -->
                <div class="report-item">
                    <div class="report-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="report-info">
                        <h4 class="report-title">Restaurants Directory</h4>
                        <p class="report-description"><?php echo $reportStats['total_restaurants']; ?> registered restaurants with details</p>
                        <div class="report-meta">
                            <span class="report-date">Updated: <?php echo date('M d, Y'); ?></span>
                            <span class="report-size">~1.5 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn btn-sm btn-primary" onclick="downloadReport('restaurants-report')">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                </div>

                <!-- NGOs Report -->
                <div class="report-item">
                    <div class="report-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <div class="report-info">
                        <h4 class="report-title">NGOs Directory</h4>
                        <p class="report-description"><?php echo $reportStats['total_ngos']; ?> registered NGOs with contact information</p>
                        <div class="report-meta">
                            <span class="report-date">Updated: <?php echo date('M d, Y'); ?></span>
                            <span class="report-size">~1.2 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn btn-sm btn-primary" onclick="downloadReport('ngos-report')">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                </div>

                <!-- Food Distribution Report -->
                <div class="report-item">
                    <div class="report-icon">
                        <i class="fas fa-apple-alt"></i>
                    </div>
                    <div class="report-info">
                        <h4 class="report-title">Food Distribution Analytics</h4>
                        <p class="report-description">Food listings and distribution statistics</p>
                        <div class="report-meta">
                            <span class="report-date">Generated: <?php echo date('M d, Y'); ?></span>
                            <span class="report-size">~2.1 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn btn-sm btn-primary" onclick="downloadReport('food-distribution')">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Reports Section -->
    <div class="card">
        <div class="card-header">
            <h3>Financial Reports</h3>
            <p class="card-subtitle">Donation tracking and financial data</p>
        </div>
        <div class="card-body">
            <div class="reports-list">
                <!-- Donations Report -->
                <div class="report-item">
                    <div class="report-icon">
                        <i class="fas fa-donate"></i>
                    </div>
                    <div class="report-info">
                        <h4 class="report-title">Donations Report</h4>
                        <p class="report-description">Complete donation records and transaction details</p>
                        <div class="report-meta">
                            <span class="report-date">Updated: <?php echo date('M d, Y'); ?></span>
                            <span class="report-size">~1.8 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn btn-sm btn-primary" onclick="downloadReport('donations-report')">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                </div>

                <!-- Monthly Financial Report -->
                <div class="report-item">
                    <div class="report-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="report-info">
                        <h4 class="report-title">Monthly Financial Summary</h4>
                        <p class="report-description">Month-by-month financial overview and trends</p>
                        <div class="report-meta">
                            <span class="report-date">Generated: <?php echo date('M d, Y'); ?></span>
                            <span class="report-size">~2.3 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn btn-sm btn-primary" onclick="downloadReport('monthly-financial')">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                </div>

                <!-- Audit Report -->
                <div class="report-item">
                    <div class="report-icon">
                        <i class="fas fa-audit"></i>
                    </div>
                    <div class="report-info">
                        <h4 class="report-title">Audit & Compliance Report</h4>
                        <p class="report-description">System audit logs and compliance verification</p>
                        <div class="report-meta">
                            <span class="report-date">Generated: <?php echo date('M d, Y'); ?></span>
                            <span class="report-size">~3.5 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn btn-sm btn-primary" onclick="downloadReport('audit-report')">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User & Activity Reports Section -->
    <div class="card">
        <div class="card-header">
            <h3>User & Activity Reports</h3>
            <p class="card-subtitle">User management and system activity logs</p>
        </div>
        <div class="card-body">
            <div class="reports-list">
                <!-- User Activity Report -->
                <div class="report-item">
                    <div class="report-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="report-info">
                        <h4 class="report-title">User Activity Report</h4>
                        <p class="report-description"><?php echo $reportStats['total_users']; ?> users and their activities</p>
                        <div class="report-meta">
                            <span class="report-date">Generated: <?php echo date('M d, Y'); ?></span>
                            <span class="report-size">~2.7 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn btn-sm btn-primary" onclick="downloadReport('user-activity')">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                </div>

                <!-- System Logs Report -->
                <div class="report-item">
                    <div class="report-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="report-info">
                        <h4 class="report-title">System Logs</h4>
                        <p class="report-description">Detailed system activity and event logs</p>
                        <div class="report-meta">
                            <span class="report-date">Generated: <?php echo date('M d, Y'); ?></span>
                            <span class="report-size">~4.2 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn btn-sm btn-primary" onclick="downloadReport('system-logs')">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                </div>

                <!-- Performance Report -->
                <div class="report-item">
                    <div class="report-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <div class="report-info">
                        <h4 class="report-title">System Performance Report</h4>
                        <p class="report-description">Server performance and system health metrics</p>
                        <div class="report-meta">
                            <span class="report-date">Generated: <?php echo date('M d, Y'); ?></span>
                            <span class="report-size">~1.9 MB</span>
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="btn btn-sm btn-primary" onclick="downloadReport('performance-report')">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Download Stats -->
<div class="card mt-4">
    <div class="card-header">
        <h3>Report Statistics</h3>
    </div>
    <div class="card-body">
        <div class="grid grid-3">
            <div class="stat-box">
                <div class="stat-number"><?php echo count($monthlyDonations) * 3; ?></div>
                <div class="stat-text">Total Reports Available</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">24 hrs</div>
                <div class="stat-text">Update Frequency</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">100%</div>
                <div class="stat-text">Data Accuracy</div>
            </div>
        </div>
    </div>
</div>



<script>
// Download report function
function downloadReport(reportType) {
    // Show loading state
    showNotification('Preparing your report...', 'info');
    
    // Simulate download
    setTimeout(() => {
        // In a real scenario, this would redirect to a PDF generation endpoint
        window.location.href = `?page=reports&download_pdf=${reportType}`;
        showNotification('Report downloaded successfully!', 'success');
    }, 500);
}
</script>
