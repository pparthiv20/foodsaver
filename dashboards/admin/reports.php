<?php
/**
 * Food-Saver - Admin Reports Page
 * Display and download various system reports in PDF and Excel formats
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
    'people_served' => $db->query("SELECT COALESCE(SUM(people_served), 0) FROM food_listings")->fetchColumn(),
];
?>

<!-- Reports Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-chart-bar"></i> Reports & Analytics
    </h1>
    <p class="page-subtitle">Download system reports in PDF and Excel formats</p>
</div>

<!-- Summary Cards -->
<div class="dashboard-stats mb-4">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-utensils"></i>
        </div>
        <div class="stat-value"><?php echo number_format($reportStats['total_restaurants']); ?></div>
        <div class="stat-label">Total Restaurants</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon secondary">
            <i class="fas fa-hands-helping"></i>
        </div>
        <div class="stat-value"><?php echo number_format($reportStats['total_ngos']); ?></div>
        <div class="stat-label">Total NGOs</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-apple-alt"></i>
        </div>
        <div class="stat-value"><?php echo number_format($reportStats['food_delivered']); ?></div>
        <div class="stat-label">Food Delivered</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-donate"></i>
        </div>
        <div class="stat-value">₹<?php echo number_format($reportStats['total_donation_amount']); ?></div>
        <div class="stat-label">Total Donations</div>
    </div>
</div>

<!-- Main Reports Section -->
<div class="grid grid-2">
    
    <!-- Donation Report Card -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-donate"></i> Donation Report</h3>
            <p class="card-subtitle">Complete donation records with donor and NGO details</p>
        </div>
        <div class="card-body">
            <div class="report-preview">
                <div class="report-stats">
                    <div class="report-stat">
                        <span class="stat-number"><?php echo $reportStats['total_donations']; ?></span>
                        <span class="stat-text">Total Donations</span>
                    </div>
                    <div class="report-stat">
                        <span class="stat-number">₹<?php echo number_format($reportStats['total_donation_amount']); ?></span>
                        <span class="stat-text">Amount Raised</span>
                    </div>
                </div>
                <p class="report-description">
                    <strong>Includes:</strong> Donor name, NGO recipient, amount, payment method, transaction status, and date.
                </p>
            </div>
        </div>
        <div class="card-footer">
            <a href="<?php echo APP_URL; ?>/generate_report.php?type=donation&format=pdf" class="btn btn-primary" target="_blank">
                <i class="fas fa-file-pdf"></i> View PDF
            </a>
            <a href="<?php echo APP_URL; ?>/generate_report.php?type=donation&format=excel" class="btn btn-secondary">
                <i class="fas fa-file-excel"></i> Download Excel
            </a>
        </div>
    </div>
    
    <!-- Request Fulfillment Report Card -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-clipboard-check"></i> Request Fulfillment Report</h3>
            <p class="card-subtitle">Food listings, claims, and delivery status</p>
        </div>
        <div class="card-body">
            <div class="report-preview">
                <div class="report-stats">
                    <div class="report-stat">
                        <span class="stat-number"><?php echo $reportStats['total_food_listings']; ?></span>
                        <span class="stat-text">Total Listings</span>
                    </div>
                    <div class="report-stat">
                        <span class="stat-number"><?php echo number_format($reportStats['people_served']); ?></span>
                        <span class="stat-text">People Served</span>
                    </div>
                </div>
                <p class="report-description">
                    <strong>Includes:</strong> Food details, restaurant source, claiming NGO, status, and delivery confirmation.
                </p>
            </div>
        </div>
        <div class="card-footer">
            <a href="<?php echo APP_URL; ?>/generate_report.php?type=request&format=pdf" class="btn btn-primary" target="_blank">
                <i class="fas fa-file-pdf"></i> View PDF
            </a>
            <a href="<?php echo APP_URL; ?>/generate_report.php?type=request&format=excel" class="btn btn-secondary">
                <i class="fas fa-file-excel"></i> Download Excel
            </a>
        </div>
    </div>
    
    <!-- Transaction Summary Report Card -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-receipt"></i> Transaction Summary Report</h3>
            <p class="card-subtitle">Payment transactions and financial overview</p>
        </div>
        <div class="card-body">
            <div class="report-preview">
                <div class="report-stats">
                    <div class="report-stat">
                        <span class="stat-number"><?php echo $reportStats['total_donations']; ?></span>
                        <span class="stat-text">Transactions</span>
                    </div>
                    <div class="report-stat">
                        <span class="stat-number">₹<?php echo number_format($reportStats['total_donation_amount']); ?></span>
                        <span class="stat-text">Completed Value</span>
                    </div>
                </div>
                <p class="report-description">
                    <strong>Includes:</strong> Transaction ID, payer, recipient, amount, payment method, status, and timestamp.
                </p>
            </div>
        </div>
        <div class="card-footer">
            <a href="<?php echo APP_URL; ?>/generate_report.php?type=transaction&format=pdf" class="btn btn-primary" target="_blank">
                <i class="fas fa-file-pdf"></i> View PDF
            </a>
            <a href="<?php echo APP_URL; ?>/generate_report.php?type=transaction&format=excel" class="btn btn-secondary">
                <i class="fas fa-file-excel"></i> Download Excel
            </a>
        </div>
    </div>
    
    <!-- Impact Summary Card -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie"></i> Impact Summary</h3>
            <p class="card-subtitle">Overall platform impact and statistics</p>
        </div>
        <div class="card-body">
            <div class="impact-grid">
                <div class="impact-item">
                    <i class="fas fa-users impact-icon"></i>
                    <div class="impact-value"><?php echo number_format($reportStats['total_users']); ?></div>
                    <div class="impact-label">Registered Users</div>
                </div>
                <div class="impact-item">
                    <i class="fas fa-store impact-icon"></i>
                    <div class="impact-value"><?php echo number_format($reportStats['approved_restaurants']); ?></div>
                    <div class="impact-label">Active Restaurants</div>
                </div>
                <div class="impact-item">
                    <i class="fas fa-heart impact-icon"></i>
                    <div class="impact-value"><?php echo number_format($reportStats['approved_ngos']); ?></div>
                    <div class="impact-label">Active NGOs</div>
                </div>
                <div class="impact-item">
                    <i class="fas fa-seedling impact-icon"></i>
                    <div class="impact-value"><?php echo number_format($reportStats['people_served']); ?></div>
                    <div class="impact-label">People Fed</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Report Preview Styles */
.report-preview {
    padding: 10px 0;
}

.report-stats {
    display: flex;
    gap: 30px;
    margin-bottom: 15px;
}

.report-stat {
    display: flex;
    flex-direction: column;
}

.report-stat .stat-number {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-color, #2ecc71);
}

.report-stat .stat-text {
    font-size: 12px;
    color: #666;
}

.report-description {
    font-size: 13px;
    color: #555;
    line-height: 1.5;
}

.card-footer {
    display: flex;
    gap: 10px;
    padding: 15px 20px;
    border-top: 1px solid #eee;
    background: #fafafa;
}

.card-footer .btn {
    flex: 1;
    text-align: center;
}

/* Impact Grid */
.impact-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.impact-item {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.impact-icon {
    font-size: 24px;
    color: var(--primary-color, #2ecc71);
    margin-bottom: 10px;
}

.impact-value {
    font-size: 28px;
    font-weight: 700;
    color: #333;
}

.impact-label {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

/* Button colors */
.btn-primary {
    background: #2ecc71;
    color: white;
}

.btn-primary:hover {
    background: #27ae60;
}

.btn-secondary {
    background: #34495e;
    color: white;
}

.btn-secondary:hover {
    background: #2c3e50;
}

@media (max-width: 768px) {
    .report-stats {
        flex-direction: column;
        gap: 15px;
    }
    
    .card-footer {
        flex-direction: column;
    }
    
    .impact-grid {
        grid-template-columns: 1fr;
    }
}
</style>
