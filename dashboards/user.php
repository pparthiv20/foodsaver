<?php
/**
 * Food-Saver - User Home
 * Welcome page for donors with donation and partner viewing
 */

require_once '../includes/config.php';
requireAuth(['user']);

$db = getDB();
$user = getCurrentUser();

// If donor profile is blocked/suspended, redirect to suspended page
if (isset($user['status']) && in_array($user['status'], ['blocked', 'suspended'], true)) {
    header('Location: ../pages/suspended.php?type=user');
    exit;
}

// Get current page
$page = $_GET['page'] ?? 'dashboard';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Invalid request.');
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'make_donation') {
        $amount = floatval($_POST['amount'] ?? 0);
        $ngoId = intval($_POST['ngo_id'] ?? 0);
        $message = sanitizeInput($_POST['message'] ?? '');
        
        if ($amount < 10) {
            setFlashMessage('error', 'Minimum donation amount is ₹10.');
        } else {
            $transactionId = 'TXN' . time() . rand(1000, 9999);
            $stmt = $db->prepare("
                INSERT INTO donations (user_id, ngo_id, amount, payment_method, message, status, transaction_id)
                VALUES (?, ?, ?, 'online', ?, 'completed', ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $ngoId ?: null, $amount, $message, $transactionId]);
            
            logActivity($_SESSION['user_id'], 'user', 'make_donation', 'Donated ₹' . $amount);
            setFlashMessage('success', 'Thank you for your donation of ₹' . number_format($amount, 2) . '!');
            $_SESSION['show_donation_thank_you'] = true;
            $_SESSION['donation_amount'] = $amount;
        }
        header('Location: user.php');
        exit;
    }
    
    if ($action === 'update_profile') {
        $stmt = $db->prepare("
            UPDATE users 
            SET full_name = ?, phone = ?, address = ?, city = ?, state = ?, pincode = ?, bio = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            sanitizeInput($_POST['full_name']),
            sanitizeInput($_POST['phone'] ?? ''),
            sanitizeInput($_POST['address'] ?? ''),
            sanitizeInput($_POST['city'] ?? ''),
            sanitizeInput($_POST['state'] ?? ''),
            sanitizeInput($_POST['pincode'] ?? ''),
            sanitizeInput($_POST['bio'] ?? ''),
            $_SESSION['user_id']
        ]);
        
        logActivity($_SESSION['user_id'], 'user', 'update_profile', 'Updated profile information');
        setFlashMessage('success', 'Profile updated successfully!');
        header('Location: user.php?page=profile');
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
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")
               ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $_SESSION['user_id']]);
            logActivity($_SESSION['user_id'], 'user', 'change_password', 'Changed password');
            setFlashMessage('success', 'Password changed successfully!');
        }
        
        header('Location: user.php?page=profile');
        exit;
    }
}

// Get stats
$stats = [
    'donated' => $db->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE user_id = " . $_SESSION['user_id'])->fetchColumn(),
    'count' => $db->query("SELECT COUNT(*) FROM donations WHERE user_id = " . $_SESSION['user_id'])->fetchColumn(),
    'helped' => $db->query("SELECT COALESCE(SUM(people_served), 0) FROM food_listings WHERE status = 'delivered'")->fetchColumn()
];

$ngos = $db->query("SELECT id, ngo_name, city, description, email_contact, contact_person FROM ngos WHERE status = 'approved' ORDER BY ngo_name")->fetchAll();
$restaurants = $db->query("SELECT id, restaurant_name, city, description, email FROM restaurants WHERE status = 'approved' ORDER BY restaurant_name")->fetchAll();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user-dashboard.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar user-navbar">
        <div class="container">
            <div class="navbar-inner">
                <a href="../index.php" class="logo">
                    <div class="logo-icon"><i class="fas fa-leaf"></i></div>
                    <span>Food-Saver</span>
                </a>
                <div class="nav-actions">
                    <div class="user-menu">
                        <button class="user-badge" onclick="toggleUserMenu()" style="background: none; border: none; cursor: pointer; padding: 0.5rem 1rem; border-radius: 8px; transition: background 200ms;">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>
                            <i class="fas fa-chevron-down" style="font-size: 0.8rem; margin-left: 0.5rem;"></i>
                        </button>
                        <div id="userDropdown" class="user-dropdown" style="display: none; position: absolute; top: 100%; right: 1rem; background: white; border: 1px solid #E5E7EB; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 1000; min-width: 180px; margin-top: 0.5rem;">
                            <a href="user.php?page=profile" class="dropdown-item" style="display: block; padding: 0.75rem 1rem; border-bottom: 1px solid #E5E7EB; color: #374151; text-decoration: none; transition: background 200ms;">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            <a href="../pages/logout.php" class="dropdown-item" style="display: block; padding: 0.75rem 1rem; color: #374151; text-decoration: none; transition: background 200ms;">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Welcome Hero -->
    <section class="dashboard-hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Welcome back, <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>! 👋</h1>
                    <p>Together we're making a difference. Let's fight food waste today.</p>
                </div>
                <div class="hero-emoji">🍃</div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <div class="container stats-container">
        <div class="grid grid-4">
            <div class="card stat-card-user">
                <div class="stat-value primary">₹<?php echo number_format($stats['donated']); ?></div>
                <div class="stat-label">Total Donated</div>
                <div class="stat-description">
                    <i class="fas fa-arrow-up" style="color: var(--success);"></i> Making impact
                </div>
            </div>
            <div class="card stat-card-user secondary">
                <div class="stat-value secondary"><?php echo $stats['count']; ?></div>
                <div class="stat-label">Donations</div>
                <div class="stat-description">
                    <i class="fas fa-gift"></i> Your generosity
                </div>
            </div>
            <div class="card stat-card-user success">
                <div class="stat-value success"><?php echo number_format($stats['helped']); ?></div>
                <div class="stat-label">People Helped</div>
                <div class="stat-description">
                    <i class="fas fa-hand-holding-heart"></i> Lives touched
                </div>
            </div>
            <div class="card stat-card-user warning">
                <div class="stat-value warning"><?php echo count($ngos) + count($restaurants); ?></div>
                <div class="stat-label">Partners</div>
                <div class="stat-description">
                    <i class="fas fa-network-wired"></i> Ecosystem
                </div>
            </div>
        </div>
    </div>

    <main class="container dashboard-main">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?> flash-message">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <?php if ($page === 'profile'): ?>
            <!-- Profile Settings Page -->
            <div class="page-header">
                <h1 class="page-title">Profile Settings</h1>
            </div>
            
            <div class="grid grid-2">
                <div class="card">
                    <div class="card-header">
                        <h3>Personal Information</h3>
                    </div>
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
                                <small style="color: #6B7280; display: block; margin-top: 0.5rem;">Email cannot be changed</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Bio / About You</label>
                                <textarea name="bio" class="form-control" rows="3" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="grid grid-3">
                                <div class="form-group">
                                    <label class="form-label">City</label>
                                    <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">State</label>
                                    <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Pincode</label>
                                    <input type="text" name="pincode" class="form-control" value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>">
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
            
            <a href="user.php" class="btn btn-outline" style="margin-top: 2rem;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>

        <?php else: ?>
            <!-- Donation Section -->
        <section class="donate-section" id="donate">
            <h2><i class="fas fa-hand-holding-heart"></i> Make a Donation</h2>
            <div class="grid grid-2 donation-cards">
                <!-- Platform Donation -->
                <div class="card donation-card">
                    <div class="donation-header platform">
                        <h3><i class="fas fa-heart"></i> FoodSaver Platform</h3>
                        <p>Support our mission directly</p>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="make_donation">
                            <input type="hidden" name="ngo_id" value="0">
                            
                            <div class="donation-form-group">
                                <label class="donation-form-label">Choose Amount</label>
                                <div class="preset-buttons four-col">
                                    <button type="button" class="preset" data-val="100">₹100</button>
                                    <button type="button" class="preset" data-val="500">₹500</button>
                                    <button type="button" class="preset active" data-val="1000">₹1000</button>
                                    <button type="button" class="preset" data-val="5000">₹5000</button>
                                </div>
                                <input type="number" name="amount" class="donation-input form-control" value="1000" min="10" required>
                            </div>
                            <div class="donation-form-group">
                                <textarea name="message" class="donation-textarea form-control" placeholder="Add a message (optional)..." rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary donate-btn btn-block">
                                <i class="fas fa-donate"></i> Donate Now
                            </button>
                        </form>
                    </div>
                </div>

                <!-- NGO Donation -->
                <div class="card donation-card ngo-card">
                    <div class="donation-header ngo">
                        <h3><i class="fas fa-hands-helping"></i> Support an NGO</h3>
                        <p>Help organizations directly</p>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="make_donation">
                            
                            <div class="donation-form-group">
                                <label class="donation-form-label">Select NGO</label>
                                <select name="ngo_id" class="form-control donation-input" required>
                                    <option value="">-- Choose an NGO --</option>
                                    <?php foreach ($ngos as $ngo): ?>
                                        <option value="<?php echo $ngo['id']; ?>"><?php echo htmlspecialchars($ngo['ngo_name']); ?> · <?php echo htmlspecialchars($ngo['city']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="donation-form-group">
                                <label class="donation-form-label">Amount</label>
                                <div class="preset-buttons three-col">
                                    <button type="button" class="preset" data-val="500">₹500</button>
                                    <button type="button" class="preset active" data-val="1000">₹1000</button>
                                    <button type="button" class="preset" data-val="2000">₹2000</button>
                                </div>
                                <input type="number" name="amount" class="donation-input form-control" value="1000" min="10" required>
                            </div>
                            <button type="submit" class="donate-btn ngo">
                                <i class="fas fa-donate"></i> Donate to NGO
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <?php endif; ?>

        <!-- NGOs -->
        <section class="partners-section" id="ngos">
            <div class="section-header">
                <h2><i class="fas fa-hands-helping ngo"></i> Partner NGOs</h2>
                <a href="#">View all →</a>
            </div>
            <div class="grid grid-3 partner-cards">
                <?php foreach (array_slice($ngos, 0, 3) as $ngo): 
                    $peopleServed = $db->query("SELECT COALESCE(SUM(people_served), 0) FROM food_listings WHERE status = 'delivered' AND claimed_by IN (SELECT id FROM ngos WHERE id = " . $ngo['id'] . ")")->fetchColumn();
                ?>
                <div class="card partner-card">
                    <div class="partner-header ngo">
                        <div class="partner-emoji">🤝</div>
                        <h4 class="partner-name"><?php echo htmlspecialchars($ngo['ngo_name']); ?></h4>
                        <p class="partner-location">
                            <i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($ngo['city']); ?>
                        </p>
                    </div>
                    <div class="partner-body">
                        <p class="partner-description">
                            <?php echo htmlspecialchars(substr($ngo['description'] ?? 'Dedicated to serving the community', 0, 80)); ?>...
                        </p>
                        <div class="partner-stats">
                            <div class="stat-box success">
                                <div class="stat-number"><?php echo number_format($peopleServed); ?></div>
                                <div class="stat-text">People Served</div>
                            </div>
                        </div>
                        <a href="mailto:<?php echo htmlspecialchars($ngo['email_contact']); ?>" class="partner-contact">
                            <i class="fas fa-envelope"></i> Get in Touch
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Restaurants -->
        <section class="partners-section last" id="restaurants">
            <div class="section-header">
                <h2><i class="fas fa-utensils restaurant"></i> Partner Restaurants</h2>
                <a href="#">View all →</a>
            </div>
            <div class="grid grid-3 partner-cards">
                <?php foreach (array_slice($restaurants, 0, 3) as $rest): 
                    $foodCount = $db->query("SELECT COUNT(*) FROM food_listings WHERE restaurant_id = " . $rest['id'])->fetchColumn();
                    $delivered = $db->query("SELECT COUNT(*) FROM food_listings WHERE restaurant_id = " . $rest['id'] . " AND status = 'delivered'")->fetchColumn();
                ?>
                <div class="card partner-card">
                    <div class="partner-header restaurant">
                        <div class="partner-emoji">🍽️</div>
                        <h4 class="partner-name"><?php echo htmlspecialchars($rest['restaurant_name']); ?></h4>
                        <p class="partner-location">
                            <i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($rest['city']); ?>
                        </p>
                    </div>
                    <div class="partner-body">
                        <p class="partner-description">
                            <?php echo htmlspecialchars(substr($rest['description'] ?? 'Quality dining for good cause', 0, 80)); ?>...
                        </p>
                        <div class="partner-stats two-col">
                            <div class="stat-box warning">
                                <div class="stat-number"><?php echo $foodCount; ?></div>
                                <div class="stat-text">Foods Posted</div>
                            </div>
                            <div class="stat-box success">
                                <div class="stat-number"><?php echo $delivered; ?></div>
                                <div class="stat-text">Delivered</div>
                            </div>
                        </div>
                        <a href="mailto:<?php echo htmlspecialchars($rest['email']); ?>" class="partner-contact restaurant">
                            <i class="fas fa-envelope"></i> Get in Touch
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="dashboard-footer">
        <div class="container">
            <div class="grid grid-3 footer-grid">
                <div class="footer-section">
                    <h4>About</h4>
                    <p>Food-Saver reduces food waste and feeds the hungry through community partnerships.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="../index.php">→ Home</a></li>
                        <li><a href="../pages/reports.php">→ Reports</a></li>
                        <li><a href="../pages/logout.php">→ Logout</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>
                        📧 info@foodsaver.com<br>
                        📞 +91 1234567890
                    </p>
                </div>
            </div>
            <hr class="footer-divider">
            <p class="footer-copyright">
                &copy; <?php echo date('Y'); ?> Food-Saver. All rights reserved.
            </p>
        </div>
    </footer>

    <!-- Thank You Donation Modal -->
    <?php if (isset($_SESSION['show_donation_thank_you']) && $_SESSION['show_donation_thank_you']): ?>
        <div id="donationThankYouModal" class="modal-overlay" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 9999; justify-content: center; align-items: center;">
            <div class="modal-content" style="background: white; border-radius: 16px; width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); animation: slideUp 300ms ease; overflow: hidden; text-align: center;">
                <!-- Modal Header -->
                <div style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; padding: 2rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎉</div>
                    <h2 style="margin: 0; font-size: 1.75rem; font-weight: 700;">Thank You!</h2>
                </div>

                <!-- Modal Body -->
                <div style="padding: 2rem;">
                    <p style="font-size: 1.1rem; color: #374151; margin-bottom: 1rem;">
                        Your generous donation of <strong>₹<?php echo number_format($_SESSION['donation_amount'], 2); ?></strong> will make a real difference in fighting hunger and food waste.
                    </p>
                    <p style="color: #6B7280; margin-bottom: 2rem;">
                        We've sent you a confirmation email with all the details about your contribution. Together, we're creating positive change in our community.
                    </p>
                    
                    <div style="background: #F0FDF4; border-left: 4px solid #10B981; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: left;">
                        <p style="margin: 0; color: #374151; font-size: 0.9rem;">
                            <i class="fas fa-check-circle" style="color: #10B981; margin-right: 0.5rem;"></i>
                            <strong>Your donation receipt has been saved to your profile.</strong>
                        </p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div style="padding: 1.5rem; background: #F9FAFB; border-top: 1px solid #E5E7EB;">
                    <button onclick="closeDonationThankYouModal()" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-check"></i> Got It!
                    </button>
                </div>
            </div>
        </div>
        
        <script>
            function closeDonationThankYouModal() {
                const modal = document.getElementById('donationThankYouModal');
                if (modal) {
                    modal.style.display = 'none';
                    // Clear the session flag
                    fetch('user.php?clear_donation_flag=1');
                }
            }

            // Auto-show modal on page load
            window.addEventListener('load', function() {
                const modal = document.getElementById('donationThankYouModal');
                if (modal) {
                    modal.style.display = 'flex';
                }
            });

            // Close on Escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeDonationThankYouModal();
                }
            });

            // Close when clicking outside
            const modal = document.getElementById('donationThankYouModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeDonationThankYouModal();
                    }
                });
            }
        </script>
        <?php unset($_SESSION['show_donation_thank_you']); ?>
    <?php endif; ?>

    <!-- User Menu Dropdown Script -->
    <script>
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown) {
                dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');
            
            if (userMenu && dropdown && !userMenu.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Add hover effect to dropdown items
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('mouseover', function() {
                this.style.background = '#F3F4F6';
            });
            item.addEventListener('mouseout', function() {
                this.style.background = 'transparent';
            });
        });

        // Dismiss flash messages after 5 seconds
        setTimeout(function() {
            const flashMsg = document.querySelector('.flash-message');
            if (flashMsg) {
                flashMsg.style.opacity = '0';
                flashMsg.style.transition = 'opacity 300ms ease';
                setTimeout(function() {
                    flashMsg.style.display = 'none';
                }, 300);
            }
        }, 5000);
    </script>
</body>
</html>
