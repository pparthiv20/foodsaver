<?php
/**
 * Food-Saver - Help Center
 */

require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - <?php echo APP_NAME; ?></title>
    <meta name="description" content="Get help and answers to frequently asked questions about Food-Saver.">
    
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
        .help-center { min-height: 100vh; padding: 3rem 0; }
        .help-header { padding: 6rem 0 3rem; background: linear-gradient(135deg, rgba(22, 163, 74, 0.05), rgba(34, 197, 94, 0.05)); }
        .faq-container { margin-top: 3rem; }
        .faq-category { margin-bottom: 3rem; }
        .faq-category h3 { font-size: 1.5rem; font-weight: 600; color: #1f2937; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; }
        .faq-category h3 i { color: #16a34a; }
        .accordion-item { border: 1px solid #e5e7eb; border-radius: 0.5rem; margin-bottom: 1rem; transition: all 0.3s ease; }
        .accordion-item:hover { box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07); }
        .accordion-header { 
            padding: 1rem 1.5rem; 
            background: #f9fafb; 
            cursor: pointer; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            font-weight: 500;
            color: #1f2937;
        }
        .accordion-header:hover { background: #f3f4f6; }
        .accordion-header i { color: #6b7280; transition: transform 0.3s ease; }
        .accordion-item.active .accordion-header i { transform: rotate(180deg); color: #16a34a; }
        .accordion-item.active .accordion-header { background: #f0fdf4; }
        .accordion-content { 
            max-height: 0; 
            overflow: hidden; 
            transition: max-height 0.3s ease;
            padding: 0 1.5rem;
        }
        .accordion-item.active .accordion-content { 
            max-height: 500px;
            padding: 1.5rem;
        }
        .accordion-content p { color: #6b7280; line-height: 1.8; }
        .help-links { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 2rem; }
        .help-link-card { 
            padding: 1.5rem; 
            border: 2px solid #e5e7eb; 
            border-radius: 0.75rem; 
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .help-link-card:hover { 
            border-color: #16a34a; 
            background: #f0fdf4;
            transform: translateY(-4px);
        }
        .help-link-card i { font-size: 2rem; color: #16a34a; margin-bottom: 1rem; }
        .help-link-card h4 { color: #1f2937; font-weight: 600; margin-bottom: 0.5rem; }
        .help-link-card p { color: #6b7280; font-size: 0.875rem; margin: 0; }
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
    <section class="help-header">
        <div class="container">
            <div class="text-center scroll-animate">
                <h1 class="section-title">Help Center</h1>
                <p class="section-subtitle" style="max-width: 600px; margin: 1rem auto;">
                    Find answers to common questions about Food-Saver. Can't find what you're looking for? <a href="contact-page.php" style="color: #16a34a; font-weight: 600;">Contact Us</a>
                </p>
            </div>
        </div>
    </section>

    <!-- Content -->
    <section class="help-center">
        <div class="container" style="max-width: 900px;">
            
            <div class="faq-container">
                <!-- General Questions -->
                <div class="faq-category">
                    <h3><i class="fas fa-circle-question"></i> General Questions</h3>
                    
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>What is Food-Saver?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="accordion-content">
                            <p>Food-Saver is a platform that connects restaurants and food businesses with surplus food to NGOs, charities, and individuals in need. Our mission is to reduce food waste and help feed hungry communities by facilitating efficient food redistribution.</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>How does Food-Saver work?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="accordion-content">
                            <p>Restaurants and food businesses post available food donations on our platform. NGOs and charities can view these listings and request donations. Food-Saver facilitates the connection and handles coordination to ensure efficient food redistribution.</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>Is the service free?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="accordion-content">
                            <p>Yes! Food-Saver is a non-profit initiative. Registration and basic service usage are completely free for all users. We believe food belongs in people's stomachs, not in landfills.</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>Who can use Food-Saver?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="accordion-content">
                            <p>Anyone can register! We welcome restaurants, bakeries, caterers, grocery stores, NGOs, charities, and individuals willing to donate food. You just need to be committed to fighting food waste and hunger.</p>
                        </div>
                    </div>
                </div>

                <!-- Account & Registration -->
                <div class="faq-category">
                    <h3><i class="fas fa-user-circle"></i> Account & Registration</h3>
                    
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>How do I create an account?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="accordion-content">
                            <p>Click on the "Register" button on our homepage. Fill in your details, choose your user type (Restaurant, NGO, or Donor), and submit. You'll receive a verification email. Once verified, your account is ready to use!</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>How do I reset my password?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="accordion-content">
                            <p>On the login page, click "Forgot Password". Enter your email address, and we'll send you a password reset link. Follow the link to create a new password.</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>Can I change my account type?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="accordion-content">
                            <p>Account type changes may require creating a new account. Please contact our support team if you need to switch user types, and we'll assist you.</p>
                        </div>
                    </div>
                </div>

                <!-- Food Donations -->
                <div class="faq-category">
                    <h3><i class="fas fa-utensils"></i> Food Donations</h3>
                    
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>What types of food can be donated?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="accordion-content">
                            <p>Cooked meals, packaged foods, fruits, vegetables, baked goods, dairy products, and non-perishable items. All donations must be safe, fresh, and comply with local food safety regulations. We cannot accept items that are expired or damaged.</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>How do I post a food donation?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="accordion-content">
                            <p>Log into your dashboard and click "Post Donation". Fill in details about the food (type, quantity, expiry date, special notes). Add a photo if possible. Click "Submit" and it will be visible to NGOs and recipients.</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>Are there food safety requirements?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="accordion-content">
                            <p>Yes! All donated food must meet local health and safety standards. Donors are responsible for ensuring food is handled properly. We recommend proper packaging, temperature control, and clear labeling of contents and expiry dates.</p>
                        </div>
                    </div>
                </div>

                <!-- Safety & Security -->
                <div class="faq-category">
                    <h3><i class="fas fa-shield-alt"></i> Safety & Security</h3>
                    
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>Is my personal information safe?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="accordion-content">
                            <p>Yes! We use industry-standard encryption and security measures to protect your data. We never sell your information to third parties. Read our Privacy Policy for details on how we handle your information.</p>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <div class="accordion-header">
                            <span>What should I do if I suspect fraud?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="accordion-content">
                            <p>Contact us immediately through the report feature on the platform or email us at contact@foodsaver.org. We take fraud seriously and investigate all reports promptly.</p>
                        </div>
                    </div>
                </div>

                <!-- Support Resources -->
                <div class="faq-category" style="margin-bottom: 3rem;">
                    <h3><i class="fas fa-hands-helping"></i> Need More Help?</h3>
                    
                    <div class="help-links">
                        <a href="contact-page.php" class="help-link-card">
                            <i class="fas fa-envelope"></i>
                            <h4>Contact Us</h4>
                            <p>Get in touch with our support team</p>
                        </a>
                        <a href="privacy-policy.php" class="help-link-card">
                            <i class="fas fa-lock"></i>
                            <h4>Privacy Policy</h4>
                            <p>Learn about your data privacy</p>
                        </a>
                        <a href="terms-of-service.php" class="help-link-card">
                            <i class="fas fa-file-contract"></i>
                            <h4>Terms of Service</h4>
                            <p>Read our terms and conditions</p>
                        </a>
                    </div>
                </div>
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
    
    <script>
        // Accordion functionality
        document.querySelectorAll('.accordion-header').forEach(header => {
            header.addEventListener('click', function() {
                const item = this.parentElement;
                item.classList.toggle('active');
            });
        });
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
