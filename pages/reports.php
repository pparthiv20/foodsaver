<?php
/**
 * Food-Saver - Public Reports Download
 * Allows users and visitors to download yearly and monthly reports
 */

require_once '../includes/config.php';

// Get database statistics
$db = getDB();
$stats = [
    'total_food_posted' => $db->query("SELECT COUNT(*) FROM food_listings")->fetchColumn(),
    'total_food_delivered' => $db->query("SELECT COUNT(*) FROM food_listings WHERE status = 'delivered'")->fetchColumn(),
    'total_donations' => $db->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'completed'")->fetchColumn(),
    'total_restaurants' => $db->query("SELECT COUNT(*) FROM restaurants WHERE status = 'approved'")->fetchColumn(),
    'total_ngos' => $db->query("SELECT COUNT(*) FROM ngos WHERE status = 'approved'")->fetchColumn(),
];

$currentYear = date('Y');

// Only current year for yearly report
$years = [$currentYear];

// Only Jan, Feb, Mar of current year for monthly reports
$months = [
    ['month' => '01', 'year' => $currentYear, 'name' => "January $currentYear"],
    ['month' => '02', 'year' => $currentYear, 'name' => "February $currentYear"],
    ['month' => '03', 'year' => $currentYear, 'name' => "March $currentYear"],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Reports - <?php echo APP_NAME; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index-styles.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌱</text></svg>">
    
    <style>
        .reports-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; border-radius: 12px; padding: 25px; border: 1px solid #e0e0e0; }
        .stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 15px; }
        .stat-icon.green { background: #e8f5e9; color: #2ecc71; }
        .stat-icon.blue { background: #e3f2fd; color: #2196f3; }
        .stat-value { font-size: 28px; font-weight: 700; color: #2ecc71; }
        .stat-label { font-size: 14px; color: #666; margin-top: 5px; }
        
        .reports-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 30px; margin-bottom: 40px; }
        .report-section { background: white; border-radius: 12px; padding: 25px; border: 1px solid #e0e0e0; }
        .section-header h2 { font-size: 20px; color: #333; margin-bottom: 5px; }
        .section-header p { font-size: 14px; color: #666; margin-bottom: 20px; }
        
        .report-item { display: flex; align-items: center; gap: 15px; padding: 15px; background: #f8f9fa; border-radius: 10px; margin-bottom: 15px; }
        .report-item:last-child { margin-bottom: 0; }
        .report-icon { width: 45px; height: 45px; background: linear-gradient(135deg, #e8f5e9, #c8e6c9); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .report-icon i { color: #2ecc71; font-size: 18px; }
        .report-info { flex: 1; min-width: 0; }
        .report-info h4 { font-size: 15px; color: #333; margin-bottom: 3px; }
        .report-info p { font-size: 12px; color: #888; }
        .report-meta { font-size: 11px; color: #aaa; margin-top: 5px; }
        .report-meta i { margin-right: 5px; }
        
        .btn-download { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background: #2ecc71; color: white; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 500; flex-shrink: 0; }
        .btn-download:hover { background: #27ae60; color: white; }
        
        .directories-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .directory-card { background: white; border-radius: 12px; padding: 25px; border: 1px solid #e0e0e0; display: flex; align-items: center; gap: 20px; }
        .directory-icon { width: 60px; height: 60px; background: linear-gradient(135deg, #2ecc71, #27ae60); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; flex-shrink: 0; }
        .directory-info { flex: 1; }
        .directory-info h3 { font-size: 18px; color: #333; margin-bottom: 5px; }
        .directory-info p { font-size: 13px; color: #666; }
        
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .reports-grid, .directories-grid { grid-template-columns: 1fr; }
            .report-item { flex-wrap: wrap; }
            .btn-download { width: 100%; justify-content: center; margin-top: 10px; }
        }
    </style>
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
                    <li><a href="reports.php" class="active">Reports</a></li>
                    <li><a href="contact-page.php">Contact</a></li>
                </ul>
                
                <div class="nav-actions">
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboards/<?php echo $_SESSION['user_type']; ?>.php" class="btn btn-primary btn-sm" role="button">
                            Dashboard
                        </a>
                        <a href="logout.php" class="btn btn-outline btn-sm" role="button">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline btn-sm nav-login-btn" role="button">Login</a>
                        <a href="pages/register.php" class="btn btn-primary btn-sm nav-joinus-btn" role="button">Join Us</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="section" style="padding-top: 8rem; padding-bottom: 2rem; background: linear-gradient(135deg, rgba(22, 163, 74, 0.05), rgba(34, 197, 94, 0.05));">
        <div class="container">
            <div class="text-center">
                <span class="section-label">Download Reports</span>
                <h1 class="section-title">System Reports</h1>
                <p class="section-subtitle">Download detailed reports about food donations, distributions, and platform statistics</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="section" style="padding-top: 2rem; background: #f5f7fa;">
        <div class="reports-container">
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-apple-alt"></i></div>
                    <div class="stat-value"><?php echo number_format($stats['total_food_posted']); ?></div>
                    <div class="stat-label">Total Food Posted</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value"><?php echo number_format($stats['total_food_delivered']); ?></div>
                    <div class="stat-label">Food Delivered</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-rupee-sign"></i></div>
                    <div class="stat-value">₹<?php echo number_format($stats['total_donations']); ?></div>
                    <div class="stat-label">Total Donations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                    <div class="stat-value"><?php echo number_format($stats['total_restaurants']); ?></div>
                    <div class="stat-label">Active Restaurants</div>
                </div>
            </div>
            
            <!-- Reports Grid -->
            <div class="reports-grid">
                <!-- Yearly Reports -->
                <div class="report-section">
                    <div class="section-header">
                        <h2>Yearly Reports</h2>
                        <p>Download comprehensive annual reports</p>
                    </div>
                    
                    <?php foreach ($years as $year): ?>
                    <div class="report-item">
                        <div class="report-icon"><i class="fas fa-file-pdf"></i></div>
                        <div class="report-info">
                            <h4>Annual Report <?php echo $year; ?></h4>
                            <p>Comprehensive yearly overview and achievements</p>
                            <div class="report-meta">
                                <i class="fas fa-calendar"></i> Year <?php echo $year; ?>
                                <span style="margin-left: 15px;"><i class="fas fa-file"></i> ~3.5 MB</span>
                            </div>
                        </div>
                        <a href="../public_report.php?type=yearly&year=<?php echo $year; ?>" class="btn-download" target="_blank">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Monthly Reports -->
                <div class="report-section">
                    <div class="section-header">
                        <h2>Monthly Reports</h2>
                        <p>Download month-by-month detailed analysis</p>
                    </div>
                    
                    <?php foreach ($months as $m): ?>
                    <div class="report-item">
                        <div class="report-icon"><i class="fas fa-file-pdf"></i></div>
                        <div class="report-info">
                            <h4>Monthly Report - <?php echo $m['name']; ?></h4>
                            <p>Detailed food distribution and donation statistics</p>
                            <div class="report-meta">
                                <i class="fas fa-calendar"></i> <?php echo $m['name']; ?>
                                <span style="margin-left: 15px;"><i class="fas fa-file"></i> ~1.8 MB</span>
                            </div>
                        </div>
                        <a href="../public_report.php?type=monthly&month=<?php echo $m['month']; ?>&year=<?php echo $m['year']; ?>" class="btn-download" target="_blank">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Partner Directories -->
            <h2 style="font-size: 20px; color: #333; margin-bottom: 20px;">Partner Directories</h2>
            <div class="directories-grid">
                <div class="directory-card">
                    <div class="directory-icon"><i class="fas fa-utensils"></i></div>
                    <div class="directory-info">
                        <h3>Restaurant Directory</h3>
                        <p><?php echo $stats['total_restaurants']; ?> restaurant partners with names and addresses</p>
                    </div>
                    <a href="../public_report.php?type=restaurants" class="btn-download" target="_blank">
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
                <div class="directory-card">
                    <div class="directory-icon"><i class="fas fa-hands-helping"></i></div>
                    <div class="directory-info">
                        <h3>NGO Directory</h3>
                        <p><?php echo $stats['total_ngos']; ?> NGO partners with names and addresses</p>
                    </div>
                    <a href="../public_report.php?type=ngos" class="btn-download" target="_blank">
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 40px; color: #999; font-size: 13px; padding-bottom: 40px;">
                <p>💡 Tip: Click "Print / Save as PDF" on the report page to save as PDF file</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="../index.php" class="logo">
                        <div class="logo-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <span>Food Saver</span>
                    </a>
                    <p>
                        Connecting restaurants, NGOs, and donors to redistribute surplus food 
                        efficiently and fight hunger in our communities.
                    </p>
                </div>
                
                
                <div>
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="../index.php#home">Home</a></li>
                        <li><a href="../index.php#how-it-works">How It Works</a></li>
                        <li><a href="../index.php#about-us">About Us</a></li>
                        <li><a href="../index.php#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Support</h4>
                    <ul class="footer-links">
                        <li><a href="contact-page.php">Contact Us</a></li>
                        <li><a href="help-center.php">Help Center</a></li>
                        <li><a href="privacy-policy.php">Privacy Policy</a></li>
                        <li><a href="terms-of-service.php">Terms of Service</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../assets/js/main.js"></script>
</body>
</html>