<?php
/**
 * Food-Saver - Terms of Service
 */

require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - <?php echo APP_NAME; ?></title>
    <meta name="description" content="Read our Terms of Service. Review the rules and regulations governing your use of FoodSaver.">
    
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
        .legal-page { min-height: 100vh; }
        .legal-header { padding: 6rem 0 3rem; background: linear-gradient(135deg, rgba(22, 163, 74, 0.05), rgba(34, 197, 94, 0.05)); }
        .legal-content { padding: 3rem 0; }
        .legal-section { margin-bottom: 2rem; }
        .legal-section h2 { font-size: 1.5rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem; margin-top: 2rem; }
        .legal-section h3 { font-size: 1.125rem; font-weight: 600; color: #374151; margin: 1.5rem 0 0.75rem; }
        .legal-section p { color: #6b7280; line-height: 1.8; margin-bottom: 1rem; }
        .legal-section ul, .legal-section ol { color: #6b7280; line-height: 1.8; margin-left: 1.5rem; margin-bottom: 1rem; }
        .legal-section li { margin-bottom: 0.5rem; }
        .last-updated { color: #9ca3af; font-size: 0.875rem; padding-top: 2rem; border-top: 1px solid #e5e7eb; }
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
                    <li><a href="../index.php#reports">Reports</a></li>
                    <li><a href="../index.php#contact">Contact</a></li>
                </ul>
                
                <div class="nav-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="logout.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="register.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
                <button class="mobile-menu-btn" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="legal-header">
        <div class="container">
            <div class="text-center scroll-animate">
                <h1 class="section-title">Terms of Service</h1>
                <p class="section-subtitle" style="max-width: 600px; margin: 1rem auto;">
                    Please read these Terms of Service carefully before using Food-Saver
                </p>
            </div>
        </div>
    </section>

    <!-- Content -->
    <section class="legal-page">
        <div class="container" style="max-width: 900px;">
            <div class="legal-content">
                <div class="legal-section">
                    <h2>1. Acceptance of Terms</h2>
                    <p>By accessing and using Food-Saver, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
                </div>

                <div class="legal-section">
                    <h2>2. Use License</h2>
                    <p>Permission is granted to temporarily download one copy of the materials (information or software) on Food-Saver for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
                    <ul>
                        <li>Modify or copy the materials</li>
                        <li>Use the materials for any commercial purpose or for any public display</li>
                        <li>Attempt to decompile or reverse engineer any software contained on Food-Saver</li>
                        <li>Remove any copyright or other proprietary notations from the materials</li>
                        <li>Transfer the materials to another person or "mirror" the materials on any other server</li>
                        <li>Use the services to engage in any form of harassment or abuse</li>
                        <li>Provide false or misleading information about food donations</li>
                    </ul>
                </div>

                <div class="legal-section">
                    <h2>3. Disclaimer</h2>
                    <p>The materials on Food-Saver are provided on an 'as is' basis. Food-Saver makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
                </div>

                <div class="legal-section">
                    <h2>4. Limitations</h2>
                    <p>In no event shall Food-Saver or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on Food-Saver, even if Food-Saver or an authorized representative has been notified orally or in writing of the possibility of such damage.</p>
                </div>

                <div class="legal-section">
                    <h2>5. Accuracy of Materials</h2>
                    <p>The materials appearing on Food-Saver could include technical, typographical, or photographic errors. Food-Saver does not warrant that any of the materials on its website are accurate, complete, or current. Food-Saver may make changes to the materials contained on its website at any time without notice.</p>
                </div>

                <div class="legal-section">
                    <h2>6. Links</h2>
                    <p>Food-Saver has not reviewed all of the sites linked to its website and is not responsible for the contents of any such linked site. The inclusion of any link does not imply endorsement by Food-Saver of the site. Use of any such linked website is at the user's own risk.</p>
                </div>

                <div class="legal-section">
                    <h2>7. Modifications</h2>
                    <p>Food-Saver may revise these terms of service for its website at any time without notice. By using this website, you are agreeing to be bound by the then current version of these terms of service.</p>
                </div>

                <div class="legal-section">
                    <h2>8. Governing Law</h2>
                    <p>These terms and conditions are governed by and construed in accordance with the laws of India, and you irrevocably submit to the exclusive jurisdiction of the courts in that location.</p>
                </div>

                <div class="legal-section">
                    <h2>9. User Accounts</h2>
                    <p>When you create an account on Food-Saver, you are responsible for maintaining the confidentiality of your account information and password. You agree to accept responsibility for all activities that occur under your account. You must notify us immediately of any unauthorized use of your account.</p>
                </div>

                <div class="legal-section">
                    <h2>10. Food Safety Responsibility</h2>
                    <p>All food donors and recipients agree to comply with local and national food safety regulations. Food-Saver is not responsible for food safety issues. All participants must ensure that food donations are safe, properly stored, and handled according to applicable food safety standards.</p>
                </div>

                <p class="last-updated">Last Updated: <?php echo date('F d, Y'); ?></p>
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
                        <span>Food-Saver</span>
                    </a>
                    <p>
                        Connecting restaurants, NGOs, and donors to redistribute surplus food 
                        efficiently and fight hunger in our communities.
                    </p>
                    <div class="footer-social">
                        <!-- <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a> -->
                        <a href="https://x.com/foodsaver333" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.instagram.com/fo0dsaver" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.linkedin.com/in/parthivvala/" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="../index.php">← Home</a></li>
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
                
                <div>
                    <h4 class="footer-title">Contact</h4>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope"></i> foodsaver33@gmail.com</li>
                        <li><i class="fas fa-phone"></i> +91 82380 14262</li>
                        <li><i class="fas fa-map-marker-alt"></i> Ahmedabad, India</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Food-Saver. All rights reserved.</p>
                <p>Made with <i class="fas fa-heart text-error"></i> for a hunger-free world</p>
            </div>
        </div>
    </footer>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
