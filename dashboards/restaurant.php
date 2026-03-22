<?php
/**
 * Food-Saver - Restaurant Dashboard
 * Manage surplus food listings and track donations
 */

require_once '../includes/config.php';

// Require restaurant authentication
requireAuth(['restaurant']);

$db = getDB();
$user = getCurrentUser();

// Check restaurant status
if (isset($user['status']) && $user['status'] !== 'approved') {
    if (in_array($user['status'], ['blocked', 'suspended', 'rejected'], true)) {
        header('Location: ../pages/suspended.php?type=restaurant');
        exit;
    }

    setFlashMessage('error', 'Your account is pending approval. Please wait for admin verification.');
    header('Location: ../pages/login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.');
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'post_food') {
            // Post new food listing
            $stmt = $db->prepare("
                INSERT INTO food_listings 
                (restaurant_id, food_name, food_type, category, quantity, servings, expiry_date, pickup_time_from, pickup_time_to, description, pickup_address)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            try {
                $stmt->execute([
                    $_SESSION['user_id'],
                    sanitizeInput($_POST['food_name']),
                    $_POST['food_type'],
                    sanitizeInput($_POST['category']),
                    sanitizeInput($_POST['quantity']),
                    intval($_POST['servings']),
                    $_POST['expiry_date'],
                    $_POST['pickup_time_from'],
                    $_POST['pickup_time_to'],
                    sanitizeInput($_POST['description'] ?? ''),
                    sanitizeInput($_POST['pickup_address'])
                ]);
                
                // Notify NGOs about new food
                $foodId = $db->lastInsertId();
                $ngos = $db->query("SELECT id FROM ngos WHERE status = 'approved'")->fetchAll();
                foreach ($ngos as $ngo) {
                    createNotification(
                        $ngo['id'], 
                        'ngo', 
                        'New Food Available', 
                        "{$user['restaurant_name']} has posted new food: {$_POST['food_name']}",
                        'info',
                        $foodId,
                        'food'
                    );
                }
                
                logActivity($_SESSION['user_id'], 'restaurant', 'post_food', 'Posted new food: ' . $_POST['food_name']);
                setFlashMessage('success', 'Food listing posted successfully!');
            } catch (PDOException $e) {
                setFlashMessage('error', 'Failed to post food. Please try again.');
            }
            
            header('Location: ?page=food');
            exit;
        }
        
        if ($action === 'update_status') {
            // Update food status
            $foodId = intval($_POST['food_id']);
            $newStatus = $_POST['status'];
            
            $stmt = $db->prepare("UPDATE food_listings SET status = ? WHERE id = ? AND restaurant_id = ?");
            $stmt->execute([$newStatus, $foodId, $_SESSION['user_id']]);
            
            // Update timestamps based on status
            if ($newStatus === 'picked_up') {
                $db->prepare("UPDATE food_listings SET picked_up_at = NOW() WHERE id = ?")
                   ->execute([$foodId]);
            } elseif ($newStatus === 'delivered') {
                $db->prepare("UPDATE food_listings SET delivered_at = NOW() WHERE id = ?")
                   ->execute([$foodId]);
            }
            
            setFlashMessage('success', 'Status updated successfully!');
            header('Location: ?page=food');
            exit;
        }
        
        if ($action === 'update_profile') {
            // Update profile
            $stmt = $db->prepare("
                UPDATE restaurants 
                SET restaurant_name = ?, owner_name = ?, phone = ?, address = ?, city = ?, state = ?, pincode = ?, cuisine_type = ?, description = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                sanitizeInput($_POST['restaurant_name']),
                sanitizeInput($_POST['owner_name']),
                sanitizeInput($_POST['phone']),
                sanitizeInput($_POST['address']),
                sanitizeInput($_POST['city']),
                sanitizeInput($_POST['state']),
                sanitizeInput($_POST['pincode']),
                sanitizeInput($_POST['cuisine_type']),
                sanitizeInput($_POST['description']),
                $_SESSION['user_id']
            ]);
            
            setFlashMessage('success', 'Profile updated successfully!');
            header('Location: ?page=profile');
            exit;
        }
        
        if ($action === 'change_password') {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            if ($newPassword !== $confirmPassword) {
                setFlashMessage('error', 'Passwords do not match.');
            } elseif (strlen($newPassword) < 8) {
                setFlashMessage('error', 'Password must be at least 8 characters.');
            } elseif (!password_verify($currentPassword, $user['password'])) {
                setFlashMessage('error', 'Current password is incorrect.');
            } else {
                $db->prepare("UPDATE restaurants SET password = ? WHERE id = ?")
                   ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $_SESSION['user_id']]);
                setFlashMessage('success', 'Password changed successfully!');
            }
            
            header('Location: ?page=profile');
            exit;
        }
    }
}

// Get statistics
$stats = [
    'total_posted' => $db->prepare("SELECT COUNT(*) FROM food_listings WHERE restaurant_id = ?")
                         ->execute([$_SESSION['user_id']]) ? $db->prepare("SELECT COUNT(*) FROM food_listings WHERE restaurant_id = ?")
                         ->execute([$_SESSION['user_id']]) && ($stmt = $db->prepare("SELECT COUNT(*) FROM food_listings WHERE restaurant_id = ?")) 
                         && $stmt->execute([$_SESSION['user_id']]) ? $stmt->fetchColumn() : 0 : 0,
    'available' => 0,
    'claimed' => 0,
    'delivered' => 0,
    'people_fed' => 0
];

$stmt = $db->prepare("SELECT status, COUNT(*) as count FROM food_listings WHERE restaurant_id = ? GROUP BY status");
$stmt->execute([$_SESSION['user_id']]);
foreach ($stmt->fetchAll() as $row) {
    if ($row['status'] === 'available') $stats['available'] = $row['count'];
    if ($row['status'] === 'claimed') $stats['claimed'] = $row['count'];
    if ($row['status'] === 'delivered') $stats['delivered'] = $row['count'];
}

$stats['people_fed'] = $db->prepare("SELECT COALESCE(SUM(people_served), 0) FROM food_listings WHERE restaurant_id = ? AND status = 'delivered'")
                           ->execute([$_SESSION['user_id']]) ? $db->prepare("SELECT COALESCE(SUM(people_served), 0) FROM food_listings WHERE restaurant_id = ? AND status = 'delivered'")
                           ->fetchColumn() : 0;

// Get food listings
$foodListings = $db->prepare("
    SELECT f.*, n.ngo_name, n.phone as ngo_phone 
    FROM food_listings f 
    LEFT JOIN ngos n ON f.claimed_by = n.id 
    WHERE f.restaurant_id = ? 
    ORDER BY f.created_at DESC
");
$foodListings->execute([$_SESSION['user_id']]);
$foodListings = $foodListings->fetchAll();

// Get notifications
$notifications = getUnreadNotifications($_SESSION['user_id'], 'restaurant');

$page = $_GET['page'] ?? 'dashboard';
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/mobile-responsive.css">
    <link rel="stylesheet" href="../assets/css/mobile-advanced.css">
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
                        <a href="?page=post-food" class="sidebar-link <?php echo $page === 'post-food' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-plus-circle"></i></span>
                            Post Food
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=food" class="sidebar-link <?php echo $page === 'food' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-apple-alt"></i></span>
                            My Food Listings
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=history" class="sidebar-link <?php echo $page === 'history' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-history"></i></span>
                            Donation History
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=ngo-profiles" class="sidebar-link <?php echo $page === 'ngo-profiles' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-hands-helping"></i></span>
                            NGO Profiles
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
                    <input type="text" placeholder="Search...">
                </div>
                
                <div class="header-actions">
                    <button class="notification-btn" onclick="toggleNotifications()">
                        <i class="fas fa-bell"></i>
                        <?php if (count($notifications) > 0): ?>
                            <span class="notification-badge"><?php echo count($notifications); ?></span>
                        <?php endif; ?>
                    </button>
                    
                    <div class="user-menu">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['restaurant_name']); ?>&background=10b981&color=fff" 
                             alt="<?php echo htmlspecialchars($user['restaurant_name']); ?>" class="user-avatar">
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($user['restaurant_name']); ?></div>
                            <div class="user-role">Restaurant</div>
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
                        <a href="?page=post-food" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Post New Food
                        </a>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="dashboard-stats">
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-apple-alt"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['total_posted']); ?></div>
                            <div class="stat-label">Total Food Posted</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon secondary">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['available']); ?></div>
                            <div class="stat-label">Available</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-hand-holding-heart"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['delivered']); ?></div>
                            <div class="stat-label">Delivered</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon info">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['people_fed']); ?></div>
                            <div class="stat-label">People Fed</div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-clock text-primary"></i> Recent Food Listings</h3>
                            <a href="?page=food" class="btn btn-outline btn-sm">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Food</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Posted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($foodListings, 0, 5) as $food): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($food['food_name']); ?></strong>
                                            <br><small class="text-gray"><?php echo htmlspecialchars($food['category']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($food['quantity']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $food['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $food['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo timeAgo($food['created_at']); ?></td>
                                        <td>
                                            <a href="?page=food&view=<?php echo $food['id']; ?>" class="btn btn-sm btn-ghost">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($foodListings)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-gray py-4">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>No food listings yet. <a href="?page=post-food">Post your first donation!</a></p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                <?php elseif ($page === 'post-food'): ?>
                    <div class="page-header">
                        <h1 class="page-title">Post Surplus Food</h1>
                        <a href="?page=dashboard" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                    
                    <div class="card" style="max-width: 800px;">
                        <div class="card-body">
                            <form method="POST" action="" data-validate>
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="post_food">
                                
                                <div class="grid grid-2">
                                    <div class="form-group">
                                        <label class="form-label required">Food Name</label>
                                        <input type="text" name="food_name" class="form-control" placeholder="e.g., Vegetable Biryani" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label required">Category</label>
                                        <input type="text" name="category" class="form-control" placeholder="e.g., Rice, Bread, Curry" required>
                                    </div>
                                </div>
                                
                                <div class="grid grid-3">
                                    <div class="form-group">
                                        <label class="form-label required">Food Type</label>
                                        <select name="food_type" class="form-control" required>
                                            <option value="veg">Vegetarian</option>
                                            <option value="non-veg">Non-Vegetarian</option>
                                            <option value="vegan">Vegan</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label required">Quantity</label>
                                        <input type="text" name="quantity" class="form-control" placeholder="e.g., 5 kg, 20 packets" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label required">Servings</label>
                                        <input type="number" name="servings" class="form-control" min="1" required>
                                    </div>
                                </div>
                                
                                <div class="grid grid-2">
                                    <div class="form-group">
                                        <label class="form-label required">Expiry Date & Time</label>
                                        <input type="datetime-local" name="expiry_date" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label required">Pickup Time</label>
                                        <div class="flex gap-2">
                                            <input type="time" name="pickup_time_from" class="form-control" required>
                                            <span class="flex items-center">to</span>
                                            <input type="time" name="pickup_time_to" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label required">Pickup Address</label>
                                    <textarea name="pickup_address" class="form-control" rows="2" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Additional details about the food..."></textarea>
                                </div>
                                
                                <div class="flex gap-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane"></i> Post Food
                                    </button>
                                    <a href="?page=dashboard" class="btn btn-ghost btn-lg">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                <?php elseif ($page === 'food'): ?>
                    <div class="page-header">
                        <h1 class="page-title">My Food Listings</h1>
                        <a href="?page=post-food" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Post New
                        </a>
                    </div>
                    
                    <div class="card">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Food</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Claimed By</th>
                                        <th>Posted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($foodListings as $food): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($food['food_name']); ?></strong>
                                            <br><small class="text-gray"><?php echo htmlspecialchars($food['category']); ?> • <?php echo ucfirst($food['food_type']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($food['quantity']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $food['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $food['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($food['ngo_name']): ?>
                                                <?php echo htmlspecialchars($food['ngo_name']); ?>
                                                <br><small class="text-gray"><?php echo htmlspecialchars($food['ngo_phone']); ?></small>
                                            <?php else: ?>
                                                <span class="text-gray">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDate($food['created_at']); ?></td>
                                        <td>
                                            <?php if ($food['status'] === 'claimed'): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
                                                    <input type="hidden" name="status" value="picked_up">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-check"></i> Mark Picked Up
                                                    </button>
                                                </form>
                                            <?php elseif ($food['status'] === 'picked_up'): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
                                                    <input type="hidden" name="status" value="delivered">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fas fa-check-double"></i> Mark Delivered
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-ghost" disabled>
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                <?php elseif ($page === 'ngo-profiles'): ?>
                    <?php include 'restaurant/ngo-profiles.php'; ?>
                    
                <?php elseif ($page === 'profile'): ?>
                    <div class="page-header">
                        <h1 class="page-title">Profile Settings</h1>
                    </div>
                    
                    <div class="grid grid-2">
                        <div class="card">
                            <div class="card-header">
                                <h3>Restaurant Information</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="update_profile">
                                    
                                    <div class="form-group">
                                        <label class="form-label">Restaurant Name</label>
                                        <input type="text" name="restaurant_name" class="form-control" value="<?php echo htmlspecialchars($user['restaurant_name']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Owner Name</label>
                                        <input type="text" name="owner_name" class="form-control" value="<?php echo htmlspecialchars($user['owner_name']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Cuisine Type</label>
                                        <input type="text" name="cuisine_type" class="form-control" value="<?php echo htmlspecialchars($user['cuisine_type'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($user['description'] ?? ''); ?></textarea>
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
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3>Change Password</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="change_password">
                                    
                                    <div class="form-group">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key"></i> Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
