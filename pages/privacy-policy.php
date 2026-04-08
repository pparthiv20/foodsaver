<?php
/**
 * Food-Saver - Privacy Policy
 */

require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - <?php echo APP_NAME; ?></title>
    <meta name="description" content="Read our Privacy Policy. Learn how we collect, use, and protect your personal information.">
    
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
                <h1 class="section-title">Privacy Policy</h1>
                <p class="section-subtitle" style="max-width: 600px; margin: 1rem auto;">
                    Your privacy is important to us. Learn how we collect and use your data.
                </p>
            </div>
        </div>
    </section>

    <!-- Content -->
    <section class="legal-page">
        <div class="container" style="max-width: 900px;">
            <div class="legal-content">
                <div class="legal-section">
                    <h2>1. Introduction</h2>
                    <p>Food-Saver ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services.</p>
                </div>

                <div class="legal-section">
                    <h2>2. Information We Collect</h2>
                    <h3>2.1 Information You Provide Directly</h3>
                    <p>We collect information you voluntarily provide, including:</p>
                    <ul>
                        <li>Name, email address, and contact information</li>
                        <li>Account registration and profile information</li>
                        <li>Food donation details and descriptions</li>
                        <li>Payment and billing information</li>
                        <li>Communication preferences</li>
                        <li>Any information you provide through contact forms or customer support</li>
                    </ul>
                    
                    <h3>2.2 Information Collected Automatically</h3>
                    <p>We may automatically collect certain information about your device and usage, including:</p>
                    <ul>
                        <li>IP address and browser type</li>
                        <li>Pages visited and time spent on our website</li>
                        <li>Cookies and similar tracking technologies</li>
                        <li>Device information and operating system</li>
                        <li>Geographic location data (with your permission)</li>
                    </ul>
                </div>

                <div class="legal-section">
                    <h2>3. How We Use Your Information</h2>
                    <p>We use the information we collect for various purposes:</p>
                    <ul>
                        <li>To create and maintain your account</li>
                        <li>To process donations and coordinate food redistribution</li>
                        <li>To send you important updates and notifications</li>
                        <li>To respond to your inquiries and provide customer support</li>
                        <li>To improve our services and website functionality</li>
                        <li>To comply with legal obligations</li>
                        <li>To prevent fraudulent activities and ensure security</li>
                    </ul>
                </div>

                <div class="legal-section">
                    <h2>4. How We Protect Your Information</h2>
                    <p>We implement appropriate technical and organizational security measures to protect your personal information from unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the Internet is completely secure.</p>
                </div>

                <div class="legal-section">
                    <h2>5. Sharing Your Information</h2>
                    <p>We do not sell, trade, or rent your personal information to third parties. We may share your information in the following circumstances:</p>
                    <ul>
                        <li>With restaurants, NGOs, and other parties necessary to facilitate food donations</li>
                        <li>With service providers who assist in operating our website and providing services</li>
                        <li>When required by law or to protect our legal rights</li>
                        <li>With your consent for specific purposes</li>
                    </ul>
                </div>

                <div class="legal-section">
                    <h2>6. Cookies and Tracking</h2>
                    <p>We use cookies to enhance your experience on our website. You can control cookie preferences through your browser settings. Some cookies are essential for site functionality, while others help us understand usage patterns.</p>
                </div>

                <div class="legal-section">
                    <h2>7. Your Rights</h2>
                    <p>Depending on your location, you may have certain rights regarding your personal information:</p>
                    <ul>
                        <li>Right to access your information</li>
                        <li>Right to correct inaccurate information</li>
                        <li>Right to request deletion of your information</li>
                        <li>Right to opt-out of marketing communications</li>
                        <li>Right to data portability</li>
                    </ul>
                </div>

                <div class="legal-section">
                    <h2>8. Data Retention</h2>
                    <p>We retain your personal information only for as long as necessary to provide our services or as required by law. You can request deletion of your account and associated data at any time by contacting us.</p>
                </div>

                <div class="legal-section">
                    <h2>9. Children's Privacy</h2>
                    <p>Our services are not intended for individuals under 13 years of age. We do not knowingly collect personal information from children. If we become aware that a child has provided us with personal information, we will delete such information immediately.</p>
                </div>

                <div class="legal-section">
                    <h2>10. Contact Us</h2>
                    <p>If you have questions about this Privacy Policy or our privacy practices, please contact us at:</p>
                    <p>
                        <strong>Email:</strong> privacy@foodsaver.org<br>
                        <strong>Address:</strong> Mumbai, India<br>
                        <strong>Phone:</strong> +91 98765 43210
                    </p>
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
