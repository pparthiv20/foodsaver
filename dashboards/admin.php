<?php
/**
 * Food-Saver - Admin Dashboard
 * Full system management for administrators
 */

require_once '../includes/config.php';

// Require admin authentication
requireAuth(['admin']);

$db = getDB();
$user = getCurrentUser();

// Get statistics
$stats = [
    'total_restaurants' => $db->query("SELECT COUNT(*) FROM restaurants")->fetchColumn(),
    'pending_restaurants' => $db->query("SELECT COUNT(*) FROM restaurants WHERE status = 'pending'")->fetchColumn(),
    'total_ngos' => $db->query("SELECT COUNT(*) FROM ngos")->fetchColumn(),
    'pending_ngos' => $db->query("SELECT COUNT(*) FROM ngos WHERE status = 'pending'")->fetchColumn(),
    'total_food' => $db->query("SELECT COUNT(*) FROM food_listings")->fetchColumn(),
    'available_food' => $db->query("SELECT COUNT(*) FROM food_listings WHERE status = 'available'")->fetchColumn(),
    'total_donations' => $db->query("SELECT COUNT(*) FROM donations WHERE status = 'completed'")->fetchColumn(),
    'total_donation_amount' => $db->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'completed'")->fetchColumn(),
    'total_users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'pending_feedback' => $db->query("SELECT COUNT(*) FROM feedback WHERE status = 'new'")->fetchColumn()
];

// Get recent activities
$recentActivities = $db->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 10")->fetchAll();

// Get pending approvals
$pendingRestaurants = $db->query("SELECT * FROM restaurants WHERE status = 'pending' ORDER BY created_at DESC LIMIT 5")->fetchAll();
$pendingNGOs = $db->query("SELECT * FROM ngos WHERE status = 'pending' ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Get recent food listings
$recentFood = $db->query("
    SELECT f.*, r.restaurant_name 
    FROM food_listings f 
    JOIN restaurants r ON f.restaurant_id = r.id 
    ORDER BY f.created_at DESC 
    LIMIT 5
")->fetchAll();

// Get recent donations
$recentDonations = $db->query("
    SELECT d.*, u.full_name as user_name 
    FROM donations d 
    LEFT JOIN users u ON d.user_id = u.id 
    WHERE d.status = 'completed'
    ORDER BY d.created_at DESC 
    LIMIT 5
")->fetchAll();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $action = $_GET['action'];
    $id = $_GET['id'] ?? 0;
    $type = $_GET['type'] ?? '';
    
    try {
        switch ($action) {
            case 'approve':
                if ($type === 'restaurant') {
                    $db->prepare("UPDATE restaurants SET status = 'approved' WHERE id = ?")
                       ->execute([$id]);
                    createNotification($id, 'restaurant', 'Account Approved', 'Your restaurant account has been approved. You can now start posting food donations.', 'success');
                } elseif ($type === 'ngo') {
                    $db->prepare("UPDATE ngos SET status = 'approved' WHERE id = ?")
                       ->execute([$id]);
                    createNotification($id, 'ngo', 'Account Approved', 'Your NGO account has been approved. You can now start claiming food donations.', 'success');
                }
                logActivity($_SESSION['user_id'], 'admin', 'approve_' . $type, "Approved {$type} ID: {$id}");
                echo json_encode(['success' => true, 'message' => 'Approved successfully']);
                break;
                
            case 'reject':
                if ($type === 'restaurant') {
                    $db->prepare("UPDATE restaurants SET status = 'rejected' WHERE id = ?")
                       ->execute([$id]);
                    createNotification($id, 'restaurant', 'Account Rejected', 'Your restaurant account registration has been rejected. Please contact support for more information.', 'error');
                } elseif ($type === 'ngo') {
                    $db->prepare("UPDATE ngos SET status = 'rejected' WHERE id = ?")
                       ->execute([$id]);
                    createNotification($id, 'ngo', 'Account Rejected', 'Your NGO account registration has been rejected. Please contact support for more information.', 'error');
                }
                logActivity($_SESSION['user_id'], 'admin', 'reject_' . $type, "Rejected {$type} ID: {$id}");
                echo json_encode(['success' => true, 'message' => 'Rejected successfully']);
                break;
                
            case 'suspend':
                if ($type === 'restaurant') {
                    $db->prepare("UPDATE restaurants SET status = 'suspended' WHERE id = ?")
                       ->execute([$id]);
                } elseif ($type === 'ngo') {
                    $db->prepare("UPDATE ngos SET status = 'blocked' WHERE id = ?")
                       ->execute([$id]);
                }
                logActivity($_SESSION['user_id'], 'admin', 'suspend_' . $type, "Suspended {$type} ID: {$id}");
                echo json_encode(['success' => true, 'message' => 'Suspended successfully']);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="../index.php" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <span>Food-Saver</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="sidebar-menu">
                    <li class="sidebar-item">
                        <a href="?page=dashboard" class="sidebar-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-home"></i></span>
                            Dashboard
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=restaurants" class="sidebar-link <?php echo $page === 'restaurants' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-utensils"></i></span>
                            Restaurants
                            <?php if ($stats['pending_restaurants'] > 0): ?>
                                <span class="notification-badge" style="position: static; margin-left: auto;"><?php echo $stats['pending_restaurants']; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=ngos" class="sidebar-link <?php echo $page === 'ngos' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-hands-helping"></i></span>
                            NGOs
                            <?php if ($stats['pending_ngos'] > 0): ?>
                                <span class="notification-badge" style="position: static; margin-left: auto;"><?php echo $stats['pending_ngos']; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=food" class="sidebar-link <?php echo $page === 'food' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-apple-alt"></i></span>
                            Food Listings
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=donations" class="sidebar-link <?php echo $page === 'donations' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-donate"></i></span>
                            Donations
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=users" class="sidebar-link <?php echo $page === 'users' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-users"></i></span>
                            Users
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=feedback" class="sidebar-link <?php echo $page === 'feedback' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-comments"></i></span>
                            Feedback
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=reports" class="sidebar-link <?php echo $page === 'reports' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-chart-bar"></i></span>
                            Reports
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=settings" class="sidebar-link <?php echo $page === 'settings' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-cog"></i></span>
                            Settings
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="../pages/logout.php" class="sidebar-link">
                            <span class="sidebar-icon"><i class="fas fa-sign-out-alt"></i></span>
                            Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="dashboard-header">
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="header-search">
                    <i class="fas fa-search header-search-icon"></i>
                    <input type="text" placeholder="Search...">
                </div>
                
                <div class="header-actions">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    
                    <div class="user-menu">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=10b981&color=fff" 
                             alt="<?php echo htmlspecialchars($user['full_name']); ?>" class="user-avatar">
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            <div class="user-role">Administrator</div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Dashboard Body -->
            <div class="dashboard-body">
                <?php if ($page === 'dashboard'): ?>
                    <!-- Dashboard Overview -->
                    <div class="page-header">
                        <h1 class="page-title">Dashboard Overview</h1>
                        <div class="flex gap-2">
                            <button class="btn btn-outline btn-sm" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="dashboard-stats">
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-utensils"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['total_restaurants']); ?></div>
                            <div class="stat-label">Total Restaurants</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon secondary">
                                <i class="fas fa-hands-helping"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['total_ngos']); ?></div>
                            <div class="stat-label">Total NGOs</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-apple-alt"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['total_food']); ?></div>
                            <div class="stat-label">Food Listings</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon info">
                                <i class="fas fa-donate"></i>
                            </div>
                            <div class="stat-value">₹<?php echo number_format($stats['total_donation_amount']); ?></div>
                            <div class="stat-label">Total Donations</div>
                        </div>
                    </div>
                    
                    <!-- Charts Row -->
                    <div class="grid grid-2 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h3>Food Distribution Overview</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="foodChart" height="250"></canvas>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h3>Donation Trends</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="donationChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pending Approvals -->
                    <div class="grid grid-2">
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-clock text-warning"></i> Pending Restaurant Approvals</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Restaurant</th>
                                            <th>Owner</th>
                                            <th>City</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingRestaurants as $restaurant): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($restaurant['restaurant_name']); ?></td>
                                            <td><?php echo htmlspecialchars($restaurant['owner_name']); ?></td>
                                            <td><?php echo htmlspecialchars($restaurant['city']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-success" onclick="handleAction('approve', 'restaurant', <?php echo $restaurant['id']; ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-error" onclick="handleAction('reject', 'restaurant', <?php echo $restaurant['id']; ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($pendingRestaurants)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-gray">No pending approvals</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-clock text-warning"></i> Pending NGO Approvals</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>NGO</th>
                                            <th>Contact</th>
                                            <th>City</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingNGOs as $ngo): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ngo['ngo_name']); ?></td>
                                            <td><?php echo htmlspecialchars($ngo['contact_person']); ?></td>
                                            <td><?php echo htmlspecialchars($ngo['city']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-success" onclick="handleAction('approve', 'ngo', <?php echo $ngo['id']; ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-error" onclick="handleAction('reject', 'ngo', <?php echo $ngo['id']; ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($pendingNGOs)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-gray">No pending approvals</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($page === 'restaurants'): ?>
                    <?php include 'admin/restaurants.php'; ?>
                <?php elseif ($page === 'ngos'): ?>
                    <?php include 'admin/ngos.php'; ?>
                <?php elseif ($page === 'food'): ?>
                    <?php include 'admin/food.php'; ?>
                <?php elseif ($page === 'donations'): ?>
                    <?php include 'admin/donations.php'; ?>
                <?php elseif ($page === 'users'): ?>
                    <?php include 'admin/users.php'; ?>
                <?php elseif ($page === 'feedback'): ?>
                    <?php include 'admin/feedback.php'; ?>
                <?php elseif ($page === 'reports'): ?>
                    <?php include 'admin/reports.php'; ?>
                <?php elseif ($page === 'settings'): ?>
                    <?php include 'admin/settings.php'; ?>
                <?php endif; ?>
            </div>
            
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        // Handle approve/reject actions
        async function handleAction(action, type, id) {
            if (!confirm(`Are you sure you want to ${action} this ${type}?`)) return;
            
            try {
                const response = await fetch(`?action=${action}&type=${type}&id=${id}`);
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('An error occurred', 'error');
            }
        }
        
        <?php if ($page === 'dashboard'): ?>
        // Food Chart
        const foodCtx = document.getElementById('foodChart').getContext('2d');
        new Chart(foodCtx, {
            type: 'doughnut',
            data: {
                labels: ['Available', 'Claimed', 'Picked Up', 'Delivered'],
                datasets: [{
                    data: [
                        <?php echo $db->query("SELECT COUNT(*) FROM food_listings WHERE status = 'available'")->fetchColumn(); ?>,
                        <?php echo $db->query("SELECT COUNT(*) FROM food_listings WHERE status = 'claimed'")->fetchColumn(); ?>,
                        <?php echo $db->query("SELECT COUNT(*) FROM food_listings WHERE status = 'picked_up'")->fetchColumn(); ?>,
                        <?php echo $db->query("SELECT COUNT(*) FROM food_listings WHERE status = 'delivered'")->fetchColumn(); ?>
                    ],
                    backgroundColor: ['#3b82f6', '#f59e0b', '#8b5cf6', '#10b981']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        
        // Donation Chart
        const donationCtx = document.getElementById('donationChart').getContext('2d');
        new Chart(donationCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Donations (₹)',
                    data: [15000, 22000, 18000, 28000, 32000, 35000],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
