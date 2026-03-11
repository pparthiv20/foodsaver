<?php
/**
 * Food-Saver - User/Donor Dashboard
 * Browse NGOs and make donations
 */

require_once '../includes/config.php';

// Require user authentication
requireAuth(['user']);

$db = getDB();
$user = getCurrentUser();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.');
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'make_donation') {
            $amount = floatval($_POST['amount']);
            $paymentMethod = $_POST['payment_method'];
            $message = sanitizeInput($_POST['message'] ?? '');
            $anonymous = isset($_POST['anonymous']) ? 1 : 0;
            
            if ($amount < 10) {
                setFlashMessage('error', 'Minimum donation amount is ₹10.');
            } else {
                $stmt = $db->prepare("
                    INSERT INTO donations (user_id, amount, payment_method, message, anonymous, status, transaction_id)
                    VALUES (?, ?, ?, ?, ?, 'completed', ?)
                ");
                
                $transactionId = 'TXN' . time() . rand(1000, 9999);
                $stmt->execute([$_SESSION['user_id'], $amount, $paymentMethod, $message, $anonymous, $transactionId]);
                
                logActivity($_SESSION['user_id'], 'user', 'make_donation', 'Donated ₹' . $amount);
                setFlashMessage('success', 'Thank you for your donation of ₹' . number_format($amount, 2) . '! Your transaction ID is ' . $transactionId);
            }
            
            header('Location: ?page=donations');
            exit;
        }
        
        if ($action === 'submit_feedback') {
            $stmt = $db->prepare("
                INSERT INTO feedback (user_id, user_type, name, email, subject, message, rating)
                VALUES (?, 'user', ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                sanitizeInput($_POST['name']),
                sanitizeInput($_POST['email']),
                sanitizeInput($_POST['subject']),
                sanitizeInput($_POST['message']),
                intval($_POST['rating'])
            ]);
            
            setFlashMessage('success', 'Thank you for your feedback!');
            header('Location: ?page=feedback');
            exit;
        }
        
        if ($action === 'update_profile') {
            $stmt = $db->prepare("
                UPDATE users 
                SET full_name = ?, phone = ?, address = ?, city = ?, state = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                sanitizeInput($_POST['full_name']),
                sanitizeInput($_POST['phone']),
                sanitizeInput($_POST['address']),
                sanitizeInput($_POST['city']),
                sanitizeInput($_POST['state']),
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
    'total_donated' => $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE user_id = ? AND status = 'completed'")
                          ->execute([$_SESSION['user_id']]) ? $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE user_id = ? AND status = 'completed'")->fetchColumn() : 0,
    'donation_count' => $db->prepare("SELECT COUNT(*) FROM donations WHERE user_id = ? AND status = 'completed'")
                           ->execute([$_SESSION['user_id']]) ? $db->prepare("SELECT COUNT(*) FROM donations WHERE user_id = ? AND status = 'completed'")->fetchColumn() : 0
];

// Get NGOs
$ngos = $db->query("SELECT * FROM ngos WHERE status = 'approved' ORDER BY ngo_name")->fetchAll();

// Get user's donations
$donations = $db->prepare("SELECT * FROM donations WHERE user_id = ? ORDER BY created_at DESC");
$donations->execute([$_SESSION['user_id']]);
$donations = $donations->fetchAll();

// Get site statistics
$siteStats = getSiteStatistics();

$page = $_GET['page'] ?? 'dashboard';
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
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
                        <a href="?page=donate" class="sidebar-link <?php echo $page === 'donate' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-heart"></i></span>
                            Make Donation
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=donations" class="sidebar-link <?php echo $page === 'donations' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-history"></i></span>
                            My Donations
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=ngos" class="sidebar-link <?php echo $page === 'ngos' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-hands-helping"></i></span>
                            Our NGOs
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=impact" class="sidebar-link <?php echo $page === 'impact' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-chart-line"></i></span>
                            Impact Report
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?page=feedback" class="sidebar-link <?php echo $page === 'feedback' ? 'active' : ''; ?>">
                            <span class="sidebar-icon"><i class="fas fa-comment"></i></span>
                            Feedback
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
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                    </button>
                    
                    <div class="user-menu">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=3b82f6&color=fff" 
                             alt="<?php echo htmlspecialchars($user['full_name']); ?>" class="user-avatar">
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            <div class="user-role">Donor</div>
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
                        <h1 class="page-title">Welcome, <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>!</h1>
                        <a href="?page=donate" class="btn btn-primary">
                            <i class="fas fa-heart"></i> Donate Now
                        </a>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="dashboard-stats">
                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-donate"></i>
                            </div>
                            <div class="stat-value">₹<?php echo number_format($stats['total_donated'], 2); ?></div>
                            <div class="stat-label">Total Donated</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon secondary">
                                <i class="fas fa-gift"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($stats['donation_count']); ?></div>
                            <div class="stat-label">Donations Made</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-utensils"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($siteStats['meals_saved']); ?></div>
                            <div class="stat-label">Meals Saved</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon info">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($siteStats['restaurants'] + $siteStats['ngos']); ?></div>
                            <div class="stat-label">Our Partners</div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="grid grid-3 mb-4">
                        <a href="?page=donate" class="card text-center p-4" style="text-decoration: none;">
                            <div class="feature-icon primary mb-3" style="margin: 0 auto;">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h4>Make a Donation</h4>
                            <p class="text-gray">Support our mission with a financial contribution</p>
                        </a>
                        <a href="?page=ngos" class="card text-center p-4" style="text-decoration: none;">
                            <div class="feature-icon secondary mb-3" style="margin: 0 auto;">
                                <i class="fas fa-hands-helping"></i>
                            </div>
                            <h4>Browse NGOs</h4>
                            <p class="text-gray">Learn about our partner organizations</p>
                        </a>
                        <a href="?page=impact" class="card text-center p-4" style="text-decoration: none;">
                            <div class="feature-icon success mb-3" style="margin: 0 auto;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4>View Impact</h4>
                            <p class="text-gray">See how your contributions make a difference</p>
                        </a>
                    </div>
                    
                    <!-- Recent Donations -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-history text-primary"></i> Recent Donations</h3>
                            <a href="?page=donations" class="btn btn-outline btn-sm">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Payment Method</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($donations, 0, 5) as $donation): ?>
                                    <tr>
                                        <td><?php echo formatDate($donation['created_at']); ?></td>
                                        <td class="font-semibold">₹<?php echo number_format($donation['amount'], 2); ?></td>
                                        <td><?php echo strtoupper(str_replace('_', ' ', $donation['payment_method'])); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $donation['status']; ?>">
                                                <?php echo ucfirst($donation['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($donations)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-gray py-4">
                                            <i class="fas fa-gift fa-2x mb-2"></i>
                                            <p>No donations yet. <a href="?page=donate">Make your first donation!</a></p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                <?php elseif ($page === 'donate'): ?>
                    <div class="page-header">
                        <h1 class="page-title">Make a Donation</h1>
                    </div>
                    
                    <div class="card" style="max-width: 600px; margin: 0 auto;">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <i class="fas fa-heart text-error fa-3x mb-3"></i>
                                <h3>Support Our Mission</h3>
                                <p class="text-gray">Your donation helps us cover logistics and expand our reach to more communities.</p>
                            </div>
                            
                            <form method="POST" action="">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="make_donation">
                                
                                <div class="form-group">
                                    <label class="form-label">Select Amount</label>
                                    <div class="donation-amounts">
                                        <button type="button" class="donation-amount" data-amount="100">₹100</button>
                                        <button type="button" class="donation-amount" data-amount="500">₹500</button>
                                        <button type="button" class="donation-amount active" data-amount="1000">₹1000</button>
                                        <button type="button" class="donation-amount" data-amount="5000">₹5000</button>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Or Enter Custom Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" name="amount" class="form-control" value="1000" min="10" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method" class="form-control" required>
                                        <option value="upi">UPI</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="debit_card">Debit Card</option>
                                        <option value="net_banking">Net Banking</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Message (Optional)</label>
                                    <textarea name="message" class="form-control" rows="3" placeholder="Leave a message with your donation..."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-check">
                                        <input type="checkbox" name="anonymous" class="form-check-input">
                                        <span class="form-check-label">Make this donation anonymous</span>
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-lg btn-block">
                                    <i class="fas fa-heart"></i> Complete Donation
                                </button>
                            </form>
                        </div>
                    </div>
                    
                <?php elseif ($page === 'donations'): ?>
                    <div class="page-header">
                        <h1 class="page-title">My Donations</h1>
                        <a href="?page=donate" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Donation
                        </a>
                    </div>
                    
                    <div class="card">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Transaction ID</th>
                                        <th>Amount</th>
                                        <th>Payment Method</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($donations as $donation): ?>
                                    <tr>
                                        <td><?php echo formatDateTime($donation['created_at']); ?></td>
                                        <td><code><?php echo htmlspecialchars($donation['transaction_id']); ?></code></td>
                                        <td class="font-semibold">₹<?php echo number_format($donation['amount'], 2); ?></td>
                                        <td><?php echo strtoupper(str_replace('_', ' ', $donation['payment_method'])); ?></td>
                                        <td><?php echo $donation['message'] ? htmlspecialchars(substr($donation['message'], 0, 50)) . '...' : '-'; ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $donation['status']; ?>">
                                                <?php echo ucfirst($donation['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($donations)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-gray py-4">
                                            <i class="fas fa-gift fa-2x mb-2"></i>
                                            <p>No donations yet. <a href="?page=donate">Make your first donation!</a></p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                <?php elseif ($page === 'ngos'): ?>
                    <div class="page-header">
                        <h1 class="page-title">Our Partner NGOs</h1>
                    </div>
                    
                    <div class="grid grid-3">
                        <?php foreach ($ngos as $ngo): ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="stat-icon primary">
                                        <i class="fas fa-hands-helping"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0"><?php echo htmlspecialchars($ngo['ngo_name']); ?></h4>
                                        <small class="text-gray"><?php echo htmlspecialchars($ngo['city']); ?></small>
                                    </div>
                                </div>
                                <p class="text-gray text-sm mb-3">
                                    <?php echo htmlspecialchars(substr($ngo['description'] ?? 'Working to feed the hungry and reduce food waste.', 0, 100)); ?>...
                                </p>
                                <div class="text-sm mb-3">
                                    <p><i class="fas fa-user text-primary"></i> <?php echo htmlspecialchars($ngo['contact_person']); ?></p>
                                    <p><i class="fas fa-map-marker-alt text-primary"></i> <?php echo htmlspecialchars($ngo['service_areas'] ?? $ngo['city']); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                <?php elseif ($page === 'impact'): ?>
                    <div class="page-header">
                        <h1 class="page-title">Impact Report</h1>
                    </div>
                    
                    <div class="grid grid-2 mb-4">
                        <div class="card text-center p-5">
                            <i class="fas fa-utensils text-primary fa-3x mb-3"></i>
                            <div class="stat-value" style="font-size: 3rem;"><?php echo number_format($siteStats['meals_saved']); ?></div>
                            <div class="stat-label">Meals Saved</div>
                        </div>
                        <div class="card text-center p-5">
                            <i class="fas fa-donate text-success fa-3x mb-3"></i>
                            <div class="stat-value" style="font-size: 3rem;">₹<?php echo number_format($siteStats['total_donated']); ?></div>
                            <div class="stat-label">Total Donations</div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3>Your Contribution</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-3 text-center">
                                <div>
                                    <div class="stat-value text-primary">₹<?php echo number_format($stats['total_donated'], 2); ?></div>
                                    <div class="stat-label">Your Total Donations</div>
                                </div>
                                <div>
                                    <div class="stat-value text-secondary"><?php echo number_format($stats['donation_count']); ?></div>
                                    <div class="stat-label">Times You Donated</div>
                                </div>
                                <div>
                                    <div class="stat-value text-success">
                                        <?php 
                                        $avgDonation = $stats['donation_count'] > 0 ? $stats['total_donated'] / $stats['donation_count'] : 0;
                                        echo '₹' . number_format($avgDonation, 2);
                                        ?>
                                    </div>
                                    <div class="stat-label">Average Donation</div>
                                </div>
                            </div>
                            
                            <div class="mt-4 p-4 bg-primary-50 rounded-lg">
                                <h4 class="text-primary mb-2"><i class="fas fa-heart"></i> Thank You!</h4>
                                <p>Your contributions have helped us save food and feed people in need. Every donation makes a difference!</p>
                            </div>
                        </div>
                    </div>
                    
                <?php elseif ($page === 'feedback'): ?>
                    <div class="page-header">
                        <h1 class="page-title">Send Feedback</h1>
                    </div>
                    
                    <div class="card" style="max-width: 600px;">
                        <div class="card-body">
                            <form method="POST" action="">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="submit_feedback">
                                
                                <div class="grid grid-2">
                                    <div class="form-group">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Subject</label>
                                    <input type="text" name="subject" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Rating</label>
                                    <div class="flex gap-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <label class="form-check" style="padding: var(--space-md); border: 2px solid var(--gray-200); border-radius: var(--radius-lg); cursor: pointer;">
                                            <input type="radio" name="rating" value="<?php echo $i; ?>" class="form-check-input" <?php echo $i === 5 ? 'checked' : ''; ?>>
                                            <span class="form-check-label"><?php echo $i; ?> <i class="fas fa-star text-warning"></i></span>
                                        </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Message</label>
                                    <textarea name="message" class="form-control" rows="5" required></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane"></i> Submit Feedback
                                </button>
                            </form>
                        </div>
                    </div>
                    
                <?php elseif ($page === 'profile'): ?>
                    <div class="page-header">
                        <h1 class="page-title">Profile Settings</h1>
                    </div>
                    
                    <div class="card" style="max-width: 600px;">
                        <div class="card-body">
                            <form method="POST" action="">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                    <p class="form-text">Email cannot be changed</p>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="grid grid-2">
                                    <div class="form-group">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">State</label>
                                        <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
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
</body>
</html>
