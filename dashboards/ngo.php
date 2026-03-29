<?php
/**
 * Food-Saver - NGO Dashboard
 * Browse and claim food donations
 */

require_once '../includes/config.php';

// Require NGO authentication
requireAuth(['ngo']);

$db = getDB();
$user = getCurrentUser();

// Check NGO status
if (isset($user['status']) && $user['status'] !== 'approved') {
    if (in_array($user['status'], ['blocked', 'suspended', 'rejected'], true)) {
        header('Location: ' . APP_URL . '/pages/suspended.php?type=ngo');
        exit;
    }

    setFlashMessage('error', 'Your account is pending approval. Please wait for admin verification.');
    header('Location: ' . APP_URL . '/pages/login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.');
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'claim_food') {
            $foodId = intval($_POST['food_id']);
            
            // Check if food is still available
            $stmt = $db->prepare("SELECT * FROM food_listings WHERE id = ? AND status = 'available'");
            $stmt->execute([$foodId]);
            $food = $stmt->fetch();
            
            if ($food) {
                $stmt = $db->prepare("
                    UPDATE food_listings 
                    SET status = 'claimed', claimed_by = ?, claimed_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$_SESSION['user_id'], $foodId]);
                
                // Notify restaurant
                createNotification(
                    $food['restaurant_id'],
                    'restaurant',
                    'Food Claimed',
                    "{$user['ngo_name']} has claimed your food: {$food['food_name']}",
                    'success',
                    $foodId,
                    'food'
                );
                
                logActivity($_SESSION['user_id'], 'ngo', 'claim_food', 'Claimed food: ' . $food['food_name']);
                setFlashMessage('success', 'Food claimed successfully! You can now collect it from the restaurant.');
            } else {
                setFlashMessage('error', 'This food is no longer available.');
            }
            
            header('Location: ?page=available');
            exit;
        }
        
        if ($action === 'update_status') {
            $foodId = intval($_POST['food_id']);
            $newStatus = $_POST['status'];
            $peopleServed = intval($_POST['people_served'] ?? 0);
            
            $stmt = $db->prepare("
                UPDATE food_listings 
                SET status = ?, people_served = ? 
                WHERE id = ? AND claimed_by = ?
            ");
            $stmt->execute([$newStatus, $peopleServed, $foodId, $_SESSION['user_id']]);
            
            if ($newStatus === 'delivered') {
                $db->prepare("UPDATE food_listings SET delivered_at = NOW() WHERE id = ?")
                   ->execute([$foodId]);
            }
            
            setFlashMessage('success', 'Status updated successfully!');
            header('Location: ?page=my-claims');
            exit;
        }
        
        if ($action === 'update_profile') {
            $stmt = $db->prepare("
                UPDATE ngos 
                SET ngo_name = ?, contact_person = ?, phone = ?, email_contact = ?, 
                    address = ?, city = ?, state = ?, pincode = ?, description = ?, service_areas = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                sanitizeInput($_POST['ngo_name']),
                sanitizeInput($_POST['contact_person']),
                sanitizeInput($_POST['phone']),
                sanitizeInput($_POST['email_contact']),
                sanitizeInput($_POST['address']),
                sanitizeInput($_POST['city']),
                sanitizeInput($_POST['state']),
                sanitizeInput($_POST['pincode']),
                sanitizeInput($_POST['description']),
                sanitizeInput($_POST['service_areas']),
                $_SESSION['user_id']
            ]);
            
            setFlashMessage('success', 'Profile updated successfully!');
            header('Location: ?page=profile');
            exit;
        }
    }
}

// Get statistics
$stats = [
    'total_claimed' => 0,
    'collected' => 0,
    'delivered' => 0,
    'people_served' => 0
];

$stmt = $db->prepare("SELECT status, COUNT(*) as count, COALESCE(SUM(people_served), 0) as people FROM food_listings WHERE claimed_by = ? GROUP BY status");
$stmt->execute([$_SESSION['user_id']]);
foreach ($stmt->fetchAll() as $row) {
    $stats['total_claimed'] += $row['count'];
    if ($row['status'] === 'picked_up') $stats['collected'] = $row['count'];
    if ($row['status'] === 'delivered') {
        $stats['delivered'] = $row['count'];
        $stats['people_served'] = $row['people'];
    }
}

// Get available food (not claimed, not expired)
$availableFood = $db->query("
    SELECT f.*, r.restaurant_name, r.phone as restaurant_phone, r.address as restaurant_address, r.city
    FROM food_listings f 
    JOIN restaurants r ON f.restaurant_id = r.id 
    WHERE f.status = 'available' AND f.expiry_date > NOW()
    ORDER BY f.created_at DESC
")->fetchAll();

// Get my claims
$myClaims = $db->prepare("
    SELECT f.*, r.restaurant_name, r.phone as restaurant_phone, r.address as restaurant_address
    FROM food_listings f 
    JOIN restaurants r ON f.restaurant_id = r.id 
    WHERE f.claimed_by = ? 
    ORDER BY f.claimed_at DESC
");
$myClaims->execute([$_SESSION['user_id']]);
$myClaims = $myClaims->fetchAll();

// Get notifications
$notifications = getUnreadNotifications($_SESSION['user_id'], 'ngo');

$page = $_GET['page'] ?? 'dashboard';
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NGO Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboards.css">
    <link rel="stylesheet" href="../assets/css/micro-interactions.css">
    <link rel="stylesheet" href="../assets/css/mobile-responsive.css">
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
                        <a href="?page=available" class="sidebar-link <?php echo $page === 'available' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-search"></i></span>
                            Available Food
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=my-claims" class="sidebar-link <?php echo $page === 'my-claims' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-hand-holding-heart"></i></span>
                            My Claims
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=history" class="sidebar-link <?php echo $page === 'history' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-history"></i></span>
                            Distribution History
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=restaurant-profiles" class="sidebar-link <?php echo $page === 'restaurant-profiles' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-utensils"></i></span>
                            Restaurant Profiles
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=profile" class="sidebar-link <?php echo $page === 'profile' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-user-circle"></i></span>
                            Profile
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
                    <input type="text" placeholder="Search available food...">
                </div>
                
                <div class="header-actions">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <?php if (count($notifications) > 0): ?>
                            <span class="notification-badge"><?php echo count($notifications); ?></span>
                        <?php endif; ?>
                    </button>
                    
                    <div class="user-menu">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['ngo_name']); ?>&background=f59e0b&color=fff" 
                             alt="<?php echo htmlspecialchars($user['ngo_name']); ?>" class="user-avatar">
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($user['ngo_name']); ?></div>
                            <div class="user-role">NGO Partner</div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Dashboard Body -->
            <div class="dashboard-body">
                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>" data-auto-dismiss="5000">
                        <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($page === 'dashboard'): ?>
                    <div class="page-header">
                        <h1 class="page-title">Dashboard</h1>
                        <a href="?page=available" class="btn btn-primary">
                            <i class="fas fa-search"></i> Find Food
                        </a>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="dashboard-stats">
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-hand-holding"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['total_claimed']); ?></div>
                            <div class="stat-label">Total Claimed</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon secondary">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['collected']); ?></div>
                            <div class="stat-label">Collected</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['delivered']); ?></div>
                            <div class="stat-label">Delivered</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon info">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['people_served']); ?></div>
                            <div class="stat-label">People Served</div>
                        </div>
                    </div>
                    
                    <!-- Available Food Preview -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-apple-alt text-primary"></i> Recently Available Food</h3>
                            <a href="?page=available" class="btn btn-outline btn-sm">View All</a>
                        </div>
                        <div class="grid grid-3" style="padding: var(--space-lg);">
                            <?php foreach (array_slice($availableFood, 0, 3) as $food): ?>
                            <div class="card">
                                <div class="card-body">
                                    <div class="flex justify-between items-start mb-3">
                                        <span class="status-badge status-available">Available</span>
                                        <span class="text-sm text-gray"><i class="fas fa-clock"></i> <?php echo timeAgo($food['created_at']); ?></span>
                                    </div>
                                    <h4><?php echo htmlspecialchars($food['food_name']); ?></h4>
                                    <p class="text-gray text-sm mb-2"><?php echo htmlspecialchars($food['restaurant_name']); ?></p>
                                    <p class="text-sm mb-3">
                                        <i class="fas fa-map-marker-alt text-primary"></i> 
                                        <?php echo htmlspecialchars($food['city']); ?>
                                    </p>
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold"><?php echo htmlspecialchars($food['quantity']); ?></span>
                                        <form method="POST" action="">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="claim_food">
                                            <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-hand-holding"></i> Claim
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($availableFood)): ?>
                            <div class="text-center text-gray py-4" style="grid-column: 1 / -1;">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No food available at the moment. Check back later!</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                <?php elseif ($page === 'available'): ?>
                    <div class="page-header">
                        <h1 class="page-title">Available Food</h1>
                        <span class="text-gray"><?php echo count($availableFood); ?> items available</span>
                    </div>
                    
                    <!-- Filters -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="flex gap-3 flex-wrap">
                                <select class="form-control" style="width: auto;">
                                    <option>All Locations</option>
                                    <option>Mumbai</option>
                                    <option>Delhi</option>
                                    <option>Bangalore</option>
                                </select>
                                <select class="form-control" style="width: auto;">
                                    <option>All Types</option>
                                    <option>Vegetarian</option>
                                    <option>Non-Vegetarian</option>
                                    <option>Vegan</option>
                                </select>
                                <select class="form-control" style="width: auto;">
                                    <option>Sort by: Newest</option>
                                    <option>Expiry: Soonest</option>
                                    <option>Quantity: Most</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Food Grid -->
                    <div class="grid grid-3">
                        <?php foreach ($availableFood as $food): ?>
                        <div class="card scroll-animate">
                            <div class="card-body">
                                <div class="flex justify-between items-start mb-3">
                                    <span class="status-badge status-available">Available</span>
                                    <span class="text-sm text-gray">
                                        <i class="fas fa-leaf text-success"></i> 
                                        <?php echo ucfirst($food['food_type']); ?>
                                    </span>
                                </div>
                                
                                <h4 class="mb-1"><?php echo htmlspecialchars($food['food_name']); ?></h4>
                                <p class="text-gray text-sm mb-2"><?php echo htmlspecialchars($food['category']); ?></p>
                                
                                <div class="mb-3">
                                    <p class="text-sm">
                                        <i class="fas fa-store text-primary"></i> 
                                        <?php echo htmlspecialchars($food['restaurant_name']); ?>
                                    </p>
                                    <p class="text-sm">
                                        <i class="fas fa-map-marker-alt text-primary"></i> 
                                        <?php echo htmlspecialchars($food['restaurant_address']); ?>, 
                                        <?php echo htmlspecialchars($food['city']); ?>
                                    </p>
                                    <p class="text-sm">
                                        <i class="fas fa-phone text-primary"></i> 
                                        <?php echo htmlspecialchars($food['restaurant_phone']); ?>
                                    </p>
                                </div>
                                
                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        <span class="font-semibold"><?php echo htmlspecialchars($food['quantity']); ?></span>
                                        <span class="text-gray text-sm"> • <?php echo $food['servings']; ?> servings</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center text-sm text-gray mb-3">
                                    <span><i class="fas fa-clock"></i> Pickup: <?php echo $food['pickup_time_from']; ?> - <?php echo $food['pickup_time_to']; ?></span>
                                </div>
                                
                                <p class="text-sm text-gray mb-3">
                                    <i class="fas fa-hourglass-half text-warning"></i> 
                                    Expires: <?php echo formatDateTime($food['expiry_date']); ?>
                                </p>
                                
                                <form method="POST" action="">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="claim_food">
                                    <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-hand-holding"></i> Claim This Food
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (empty($availableFood)): ?>
                    <div class="card text-center py-5">
                        <div class="card-body">
                            <i class="fas fa-inbox fa-3x text-gray mb-3"></i>
                            <h3>No Food Available</h3>
                            <p class="text-gray">There are no available food donations at the moment.</p>
                            <p class="text-gray">Check back later or enable notifications to get alerted!</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                <?php elseif ($page === 'my-claims'): ?>
                    <div class="page-header">
                        <h1 class="page-title">My Claims</h1>
                    </div>
                    
                    <div class="card">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Food</th>
                                        <th>Restaurant</th>
                                        <th>Status</th>
                                        <th>Claimed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myClaims as $claim): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($claim['food_name']); ?></strong>
                                            <br><small class="text-gray"><?php echo htmlspecialchars($claim['quantity']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($claim['restaurant_name']); ?>
                                            <br><small class="text-gray"><?php echo htmlspecialchars($claim['restaurant_phone']); ?></small>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $claim['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $claim['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo timeAgo($claim['claimed_at']); ?></td>
                                        <td>
                                            <?php if ($claim['status'] === 'claimed'): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="food_id" value="<?php echo $claim['id']; ?>">
                                                    <input type="hidden" name="status" value="picked_up">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-truck"></i> Mark Collected
                                                    </button>
                                                </form>
                                            <?php elseif ($claim['status'] === 'picked_up'): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="food_id" value="<?php echo $claim['id']; ?>">
                                                    <input type="hidden" name="status" value="delivered">
                                                    <div class="flex gap-2">
                                                        <input type="number" name="people_served" class="form-control" style="width: 80px;" placeholder="People" min="1" required>
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fas fa-check-double"></i> Mark Delivered
                                                        </button>
                                                    </div>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-gray">
                                                    <i class="fas fa-check-circle text-success"></i> Completed
                                                    <?php if ($claim['people_served'] > 0): ?>
                                                        <br><small><?php echo $claim['people_served']; ?> people served</small>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($myClaims)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-gray py-4">
                                            <i class="fas fa-hand-holding fa-2x mb-2"></i>
                                            <p>No claims yet. <a href="?page=available">Browse available food!</a></p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                <?php elseif ($page === 'restaurant-profiles'): ?>
                    <?php include 'ngo/restaurant-profiles.php'; ?>
                    
                <?php elseif ($page === 'profile'): ?>
                    <div class="page-header">
                        <h1 class="page-title">Profile Settings</h1>
                    </div>
                    
                    <div class="card" style="max-width: 800px;">
                        <div class="card-body">
                            <form method="POST" action="">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="grid grid-2">
                                    <div class="form-group">
                                        <label class="form-label">NGO Name</label>
                                        <input type="text" name="ngo_name" class="form-control" value="<?php echo htmlspecialchars($user['ngo_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Registration Number</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['registration_number']); ?>" disabled>
                                    </div>
                                </div>
                                
                                <div class="grid grid-2">
                                    <div class="form-group">
                                        <label class="form-label">Contact Person</label>
                                        <input type="text" name="contact_person" class="form-control" value="<?php echo htmlspecialchars($user['contact_person']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email_contact" class="form-control" value="<?php echo htmlspecialchars($user['email_contact']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($user['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Service Areas</label>
                                    <textarea name="service_areas" class="form-control" rows="2" placeholder="Areas where you distribute food..."><?php echo htmlspecialchars($user['service_areas'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="2" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                                </div>
                                
                                <div class="grid grid-3">
                                    <div class="form-group">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">State</label>
                                        <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($user['state']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Pincode</label>
                                        <input type="text" name="pincode" class="form-control" value="<?php echo htmlspecialchars($user['pincode']); ?>" required>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>

    <!-- Form Validation & Micro-interactions -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');

            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    clearPreviousErrors();
                    let isValid = true;

                    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
                    inputs.forEach(input => {
                        if (!input.value.trim()) {
                            showFieldError(input, 'This field is required');
                            isValid = false;
                        } else {
                            clearFieldError(input);
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            });

            function showFieldError(field, message) {
                field.classList.add('error');
                const existingError = field.parentElement.querySelector('.form-error');
                if (existingError) existingError.remove();

                const errorDiv = document.createElement('div');
                errorDiv.className = 'form-error';
                errorDiv.textContent = message;
                field.parentElement.appendChild(errorDiv);
            }

            function clearFieldError(field) {
                field.classList.remove('error');
                const error = field.parentElement.querySelector('.form-error');
                if (error) error.remove();
            }

            function clearPreviousErrors() {
                document.querySelectorAll('.form-control.error').forEach(el => {
                    el.classList.remove('error');
                    const error = el.parentElement.querySelector('.form-error');
                    if (error) error.remove();
                });
            }

            // Form focus animations
            document.querySelectorAll('input, textarea, select').forEach(field => {
                field.addEventListener('focus', function() {
                    this.parentElement.classList.add('focus-visible');
                });
                field.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focus-visible');
                });
            });

            // Alert auto-dismiss
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 300ms ease';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>
