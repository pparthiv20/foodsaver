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

// Handle donations
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
        }
        header('Location: user.php');
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
    <link rel="stylesheet" href="../assets/css/mobile-responsive.css">
    <link rel="stylesheet" href="../assets/css/mobile-advanced.css">
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
                        <span class="user-badge">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>
                        </span>
                        <a href="../pages/logout.php" class="btn btn-outline btn-sm">Logout</a>
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

    <script src="../assets/js/main.js"></script>
    <script>
        document.querySelectorAll('.preset').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.preset').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                this.closest('form').querySelector('input[name="amount"]').value = this.dataset.val;
            });
        });
    </script>
</body>
</html>
