<?php
/**
 * Food-Saver - Public Reports Download
 * Allows users and visitors to download yearly and monthly reports
 */

require_once '../includes/config.php';

// Check if PDF download is requested
if (isset($_GET['download_pdf'])) {
    $reportType = $_GET['download_pdf'];
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    
    // Validate inputs
    if (!preg_match('/^[0-9]{4}$/', $year) || !preg_match('/^[0-9]{2}$/', $month)) {
        die('Invalid parameters');
    }
    
    // Generate PDF filename
    $filename = $reportType . '_' . $year . '-' . $month . '.pdf';
    
    // For now, create a simple text-based download (replace with actual PDF generation library)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: 0');
    
    // This is a placeholder - in production, use TCPDF or mPDF library
    echo "PDF Report: $reportType\nYear: $year\nMonth: $month\n";
    exit;
}

// Get database statistics
$db = getDB();
$stats = [
    'total_food_posted' => $db->query("SELECT COUNT(*) FROM food_listings")->fetchColumn(),
    'total_food_delivered' => $db->query("SELECT COUNT(*) FROM food_listings WHERE status = 'delivered'")->fetchColumn(),
    'total_donations' => $db->query("SELECT COUNT(*) FROM donations WHERE status = 'completed'")->fetchColumn(),
    'total_donation_amount' => $db->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'completed'")->fetchColumn(),
    'total_restaurants' => $db->query("SELECT COUNT(*) FROM restaurants WHERE status = 'approved'")->fetchColumn(),
    'total_ngos' => $db->query("SELECT COUNT(*) FROM ngos WHERE status = 'approved'")->fetchColumn(),
];

// Get monthly data
$monthlyReports = $db->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total_food
    FROM food_listings
    WHERE status = 'delivered'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Reports - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/mobile-responsive.css">
    <link rel="stylesheet" href="../assets/css/mobile-advanced.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌱</text></svg>">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-inner">
                <a href="../index.php" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <span>Food-Saver</span>
                </a>
                
                <ul class="nav-links">
                    <li><a href="../index.php#home">Home</a></li>
                    <li><a href="../index.php#how-it-works">How It Works</a></li>
                    <li><a href="../index.php#about-us">About Us</a></li>
                    <li><a href="../index.php#reports" class="active">Reports</a></li>
                    <li><a href="../index.php#contact">Contact</a></li>
                </ul>
                
                <div class="nav-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="../dashboards/<?php echo $_SESSION['user_type']; ?>.php" class="btn btn-primary btn-sm">
                            Dashboard
                        </a>
                        <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline btn-sm">Login</a>
                        <a href="register.php" class="btn btn-primary btn-sm">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container py-5">
        <!-- Header Section -->
        <div class="page-header text-center mb-5">
            <h1 class="page-title">System Reports</h1>
            <p class="page-subtitle">Download detailed reports about food donations, distributions, and platform statistics</p>
        </div>

        <!-- Summary Stats -->
        <div class="dashboard-stats mb-5">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-apple-alt"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_food_posted']); ?></div>
                <div class="stat-label">Total Food Posted</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon secondary">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_food_delivered']); ?></div>
                <div class="stat-label">Food Delivered</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-donate"></i>
                </div>
                <div class="stat-value">₹<?php echo number_format($stats['total_donation_amount']); ?></div>
                <div class="stat-label">Total Donations</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_restaurants']); ?></div>
                <div class="stat-label">Active Restaurants</div>
            </div>
        </div>

        <!-- Reports Section -->
        <div class="grid grid-2">
            <!-- Yearly Reports -->
            <div class="card">
                <div class="card-header">
                    <h3>Yearly Reports</h3>
                    <p class="card-subtitle">Download comprehensive annual reports</p>
                </div>
                <div class="card-body">
                    <div class="reports-list">
                        <?php 
                        $currentYear = date('Y');
                        for ($i = 0; $i < 3; $i++):
                            $year = $currentYear - $i;
                        ?>
                        <div class="report-item">
                            <div class="report-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="report-info">
                                <h4 class="report-title">Annual Report <?php echo $year; ?></h4>
                                <p class="report-description">Comprehensive yearly overview and achievements</p>
                                <div class="report-meta">
                                    <span class="report-date">
                                        <i class="fas fa-calendar"></i> Year <?php echo $year; ?>
                                    </span>
                                    <span class="report-size">~3.5 MB</span>
                                </div>
                            </div>
                            <div class="report-actions">
                                <button class="btn btn-sm btn-primary" onclick="downloadReport('annual', '<?php echo $year; ?>', '')">
                                    <i class="fas fa-download"></i> Download
                                </button>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Monthly Reports -->
            <div class="card">
                <div class="card-header">
                    <h3>Monthly Reports</h3>
                    <p class="card-subtitle">Download month-by-month detailed analysis</p>
                </div>
                <div class="card-body">
                    <div class="reports-list">
                        <?php
                        $months = [];
                        for ($i = 0; $i < 12; $i++):
                            $date = new DateTime();
                            $date->modify("-$i months");
                            $monthKey = $date->format('m');
                            $yearKey = $date->format('Y');
                            $monthName = $date->format('F Y');
                        ?>
                        <div class="report-item">
                            <div class="report-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="report-info">
                                <h4 class="report-title">Monthly Report - <?php echo $monthName; ?></h4>
                                <p class="report-description">Detailed food distribution and donation statistics</p>
                                <div class="report-meta">
                                    <span class="report-date">
                                        <i class="fas fa-calendar"></i> <?php echo $monthName; ?>
                                    </span>
                                    <span class="report-size">~1.8 MB</span>
                                </div>
                            </div>
                            <div class="report-actions">
                                <button class="btn btn-sm btn-primary" onclick="downloadReport('monthly', '<?php echo $yearKey; ?>', '<?php echo $monthKey; ?>')">
                                    <i class="fas fa-download"></i> Download
                                </button>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Reports -->
        <div class="grid grid-2 mt-4">
            <!-- Statistics Report -->
            <div class="card">
                <div class="card-header">
                    <h3>Platform Statistics</h3>
                    <p class="card-subtitle">Key metrics and performance indicators</p>
                </div>
                <div class="card-body">
                    <div class="stats-table">
                        <div class="stat-row">
                            <span class="stat-label">Total Restaurants</span>
                            <span class="stat-value"><?php echo number_format($stats['total_restaurants']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Total NGOs</span>
                            <span class="stat-value"><?php echo number_format($stats['total_ngos']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Food Listings Posted</span>
                            <span class="stat-value"><?php echo number_format($stats['total_food_posted']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Food Successfully Delivered</span>
                            <span class="stat-value"><?php echo number_format($stats['total_food_delivered']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Total Donations Received</span>
                            <span class="stat-value"><?php echo number_format($stats['total_donations']); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Total Donation Amount</span>
                            <span class="stat-value">₹<?php echo number_format($stats['total_donation_amount']); ?></span>
                        </div>
                    </div>
                    <div class="form-actions mt-4">
                        <button class="btn btn-primary btn-block" onclick="downloadReport('statistics', '', '')">
                            <i class="fas fa-download"></i> Download Statistics Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Export Summary -->
            <div class="card">
                <div class="card-header">
                    <h3>Export Options</h3>
                    <p class="card-subtitle">Choose your preferred format</p>
                </div>
                <div class="card-body">
                    <div class="export-options">
                        <div class="option-item">
                            <div class="option-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="option-info">
                                <h4>PDF Format</h4>
                                <p class="text-sm text-gray">Print-friendly PDF documents with charts and tables</p>
                            </div>
                        </div>
                        <div class="option-item">
                            <div class="option-icon">
                                <i class="fas fa-file-excel"></i>
                            </div>
                            <div class="option-info">
                                <h4>Excel Format</h4>
                                <p class="text-sm text-gray">Spreadsheets for data analysis and custom reports</p>
                            </div>
                        </div>
                        <div class="option-item">
                            <div class="option-icon">
                                <i class="fas fa-file-csv"></i>
                            </div>
                            <div class="option-info">
                                <h4>CSV Format</h4>
                                <p class="text-sm text-gray">Raw data in comma-separated values format</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-gray text-center mt-4">
                        <i class="fas fa-lock-alt"></i> All reports are secure and private
                    </p>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="card mt-4 bg-info-light">
            <div class="card-body">
                <div class="flex items-start gap-3">
                    <i class="fas fa-info-circle text-info fa-lg"></i>
                    <div>
                        <h4 class="mb-1">How to Use Reports</h4>
                        <p class="text-sm">
                            <strong>Yearly Reports:</strong> Get a comprehensive overview of annual food distribution and donations.<br>
                            <strong>Monthly Reports:</strong> Track detailed month-by-month performance and trends.<br>
                            <strong>Statistics:</strong> View key platform metrics and performance indicators.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container">
            <div class="grid grid-3">
                <div>
                    <h4 class="mb-2">About</h4>
                    <p class="text-sm text-gray">Food-Saver connects restaurants, NGOs, and donors to reduce food waste and feed the hungry.</p>
                </div>
                <div>
                    <h4 class="mb-2">Quick Links</h4>
                    <ul class="text-sm text-gray">
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="../index.php#how-it-works">How It Works</a></li>
                        <li><a href="../index.php#about-us">About Us</a></li>
                        <li><a href="../index.php#reports">Reports</a></li>
                        <li><a href="../index.php#contact">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-2">Contact</h4>
                    <p class="text-sm text-gray">
                        <i class="fas fa-envelope"></i> info@foodsaver.com<br>
                        <i class="fas fa-phone"></i> +91 1234567890
                    </p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center text-sm text-gray">
                <p>&copy; <?php echo date('Y'); ?> Food-Saver. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <style>
        .reports-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            max-height: 600px;
            overflow-y: auto;
        }

        .report-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-200);
            transition: all var(--transition-base);
        }

        .report-item:hover {
            background: var(--gray-100);
            border-color: var(--primary-300);
            box-shadow: var(--shadow-md);
        }

        .report-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: var(--primary-100);
            color: var(--primary-600);
            border-radius: var(--radius-lg);
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .report-info {
            flex: 1;
            min-width: 0;
        }

        .report-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .report-description {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }

        .report-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .report-actions {
            flex-shrink: 0;
        }

        .stats-table {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: var(--gray-50);
            border-radius: var(--radius-md);
            border-left: 3px solid var(--primary-500);
        }

        .stat-label {
            font-weight: 500;
            color: var(--gray-700);
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-600);
        }

        .export-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .option-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: var(--radius-lg);
        }

        .option-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--primary-100);
            color: var(--primary-600);
            border-radius: var(--radius-md);
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .option-info h4 {
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .bg-info-light {
            background: rgba(59, 130, 246, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .btn-block {
            width: 100%;
        }

        @media (max-width: 768px) {
            .report-item {
                flex-direction: column;
            }

            .report-actions {
                width: 100%;
            }

            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script src="../assets/js/main.js"></script>
    <script>
        function downloadReport(type, year, month) {
            showNotification('Preparing your report...', 'info');
            
            setTimeout(() => {
                let url = 'pages/reports.php?download_pdf=' + type;
                if (year) url += '&year=' + year;
                if (month) url += '&month=' + month;
                
                window.location.href = url;
                showNotification('Report downloaded successfully!', 'success');
            }, 500);
        }
    </script>
</body>
</html>
