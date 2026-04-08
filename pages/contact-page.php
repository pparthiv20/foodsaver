<?php
/**
 * Food-Saver - Contact Page
 */

require_once '../includes/config.php';

// Check for flash messages
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo APP_NAME; ?></title>
    <meta name="description" content="Get in touch with FoodSaver. We're here to help with any questions or inquiries.">
    
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
</head>
<body>
    <!-- Thank you modal -->
    <?php if ($flash && $flash['type'] === 'success' && $flash['message'] === 'contact_thanks'): ?>
        <div id="contactThankYouModal" class="thank-you-modal-overlay">
            <div class="thank-you-modal-content">
                <button type="button" onclick="closeContactThankYouModal()" class="thank-you-close-btn" aria-label="Close">
                    &times;
                </button>
                <div class="thank-you-modal-icon">
                    <span>😊</span>
                </div>
                <h2 class="thank-you-modal-title">Thank you!</h2>
                <p class="thank-you-modal-text">A real human from our team will see this and get back to you soon.</p>
                <button type="button" onclick="closeContactThankYouModal()" class="thank-you-modal-btn">
                    Got it
                </button>
            </div>
        </div>
    <?php endif; ?>

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
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="../index.php#contact">Contact</a></li>
                </ul>
                
                <div class="nav-actions">
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboards/<?php echo $_SESSION['user_type']; ?>.php" class="btn btn-primary btn-sm" role="button">
                            Dashboard
                        </a>
                        <a href="logout.php" class="btn btn-outline btn-sm" role="button">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline btn-sm nav-login-btn" role="button">Login</a>
                        <a href="pages/register.php" class="btn btn-primary btn-sm nav-joinus-btn" role="button">Join Us</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contact Header -->
    <section class="section" style="padding-top: 8rem; padding-bottom: 3rem; background: linear-gradient(135deg, rgba(22, 163, 74, 0.05), rgba(34, 197, 94, 0.05));">
        <div class="container">
            <div class="text-center scroll-animate">
                <span class="section-label">Get In Touch</span>
                <h1 class="section-title">Contact Us</h1>
                <p class="section-subtitle" style="max-width: 600px; margin: 1rem auto;">
                    Have a question? Need assistance? Fill out the form below and we'll get back to you as soon as possible. We typically respond within 24-48 hours.
                </p>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="section">
        <div class="container">
            <div class="grid grid-2 contact-grid" style="gap: 2rem;">
                <!-- Contact Form -->
                <div class="card scroll-animate">
                    <div class="card-body">
                        <h3 style="margin-top: 0; margin-bottom: 1.5rem; display: flex; align-items: center;">
                            <i class="fas fa-paper-plane" style="color: #16a34a; margin-right: 0.75rem;"></i>
                            Send us a Message
                        </h3>
                        <form action="contact.php" method="POST">
                            <?php echo csrfField(); ?>
                            
                            <div class="form-group">
                                <label class="form-label required">Your Name</label>
                                <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Subject</label>
                                <select name="subject" class="form-control" required>
                                    <option value="">Select a subject</option>
                                    <option value="General Inquiry">General Inquiry</option>
                                    <option value="Partnership">Partnership Opportunity</option>
                                    <option value="Support">Support Request</option>
                                    <option value="Feedback">Feedback</option>
                                    <option value="Bug Report">Bug Report</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Message</label>
                                <textarea name="message" class="form-control" rows="6" placeholder="Tell us how we can help..." required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block" style="padding: 0.75rem; font-weight: 600;">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="scroll-animate">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 style="margin-top: 0; margin-bottom: 1rem; display: flex; align-items: center;">
                                <i class="fas fa-map-marker-alt" style="color: #16a34a; margin-right: 0.75rem;"></i>
                                Our Office
                            </h3>
                            <p class="text-gray">
                                123 Green Street, Eco Park<br>
                                Mumbai, Maharashtra 400001<br>
                                India
                            </p>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 style="margin-top: 0; margin-bottom: 1rem; display: flex; align-items: center;">
                                <i class="fas fa-envelope" style="color: #16a34a; margin-right: 0.75rem;"></i>
                                Email Us
                            </h3>
                            <p class="text-gray">
                                <strong>General:</strong> contact@foodsaver.org<br>
                                <strong>Support:</strong> support@foodsaver.org<br>
                                <strong>Partnerships:</strong> partner@foodsaver.org
                            </p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h3 style="margin-top: 0; margin-bottom: 1rem; display: flex; align-items: center;">
                                <i class="fas fa-phone" style="color: #16a34a; margin-right: 0.75rem;"></i>
                                Call Us
                            </h3>
                            <p class="text-gray">
                                <strong>Phone:</strong> +91 82380 14262<br>
                                <strong>Hours:</strong> Mon to Sat: 9:00 AM to 6:00 PM<br>
                                <strong>Timezone:</strong> IST (UTC+5:30)
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section" style="background: var(--primary-50);">
        <div class="container">
            <div class="text-center scroll-animate">
                <h2 class="section-title">Common Questions</h2>
                <p class="section-subtitle" style="max-width: 600px; margin: 1rem auto;">
                    Find answers to frequently asked questions below
                </p>
            </div>

            <div style="max-width: 700px; margin: 3rem auto;">
                <div class="card scroll-animate mb-4">
                    <div class="card-body">
                        <h4 style="margin-top: 0; color: #16a34a;">
                            <i class="fas fa-question-circle"></i> How quickly will I hear back?
                        </h4>
                        <p class="text-gray">
                            We aim to respond to all inquiries within 24 to 48 business hours. During peak times, it may take up to 72 hours.
                        </p>
                    </div>
                </div>

                <div class="card scroll-animate mb-4">
                    <div class="card-body">
                        <h4 style="margin-top: 0; color: #16a34a;">
                            <i class="fas fa-question-circle"></i> What if I don't receive a response?
                        </h4>
                        <p class="text-gray">
                            Check your spam folder first. If you still haven't received a response after 72 hours, try calling us directly at +91 98765 43210.
                        </p>
                    </div>
                </div>

                <div class="card scroll-animate">
                    <div class="card-body">
                        <h4 style="margin-top: 0; color: #16a34a;">
                            <i class="fas fa-question-circle"></i> Can I call instead of emailing?
                        </h4>
                        <p class="text-gray">
                            Absolutely! You can reach us by phone during business hours (Monday to Saturday, 9 AM - 6 PM IST). We also accept emails anytime.
                        </p>
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
                        <span>Food Saver</span>
                    </a>
                    <p>
                        Connecting restaurants, NGOs, and donors to redistribute surplus food 
                        efficiently and fight hunger in our communities.
                    </p>
                </div>
                
                <div>
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="../index.php#home">Home</a></li>
                        <li><a href="../index.php#how-it-works">How It Works</a></li>
                        <li><a href="../index.php#about-us">About Us</a></li>
                        <li><a href="../index.php#contact">Contact</a></li>
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
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal.show { display: flex !important; }
    </style>

    <!-- Scripts -->
    <script src="../assets/js/main.js"></script>
    <script>
        function closeContactThankYouModal() {
            const modal = document.getElementById('contactThankYouModal');
            if (modal) {
                modal.classList.remove('show');
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
