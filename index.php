<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
/**
 * Food-Saver - Landing Page
 * Modern landing page with all sections: Home, About, Reports, Contact, Objective with donation modal
 */

require_once 'includes/config.php';

// Get site statistics
$stats = getSiteStatistics();
$isLoggedIn = isLoggedIn();

// Contact-form flash handling
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - <?php echo APP_TAGLINE; ?></title>
    <meta name="description" content="Connect restaurants, NGOs, and donors to redistribute surplus food efficiently. Reduce food waste and feed the hungry.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/index-styles.css">
    <link rel="stylesheet" href="assets/css/mobile-responsive.css">
    <link rel="stylesheet" href="assets/css/mobile-advanced.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌱</text></svg>">
</head>
<body>
    <!-- Contact thank-you modal -->
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
                <a href="index.php" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <span>Food-Saver</span>
                </a>
                
                <ul class="nav-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#about-us">About Us</a></li>
                    <li><a href="#objective">Objective</a></li>
                    <li><a href="#reports">Reports</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                
                <div class="nav-actions">
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboards/<?php echo $_SESSION['user_type']; ?>.php" class="btn btn-primary btn-sm">
                            Dashboard
                        </a>
                        <a href="pages/logout.php" class="btn btn-outline btn-sm">Logout</a>
                    <?php else: ?>
                        <a href="pages/login.php" class="btn btn-outline btn-sm">Login</a>
                        <a href="pages/register.php" class="btn btn-primary btn-sm">Join Us</a>
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

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-bg-pattern"></div>
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="hero-badge">
                        <i class="fas fa-heart text-primary"></i>
                        <span>Join 500+ restaurants fighting hunger</span>
                    </div>
                    
                    <h1 class="hero-title">
                        Reduce Food Waste.<br>
                        <span>Feed the Hungry.</span>
                    </h1>
                    
                    <p class="hero-subtitle">
                        Connect restaurants, NGOs, and donors to redistribute surplus food efficiently. 
                        Together, we can make a difference in our communities.
                    </p>
                    
                    <div class="hero-actions">
                        <button onclick="openDonationModal()" class="btn btn-primary btn-lg">
                            <i class="fas fa-heart"></i>
                            Donate Now
                        </button>
                        <a href="pages/register.php?type=restaurant" class="btn btn-outline btn-lg">
                            Join as Restaurant
                        </a>
                        <a href="pages/register.php?type=ngo" class="btn btn-secondary btn-lg">
                            Join as NGO
                        </a>
                    </div>
                </div>
                
                <div class="hero-image">
                    <img src="https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=600&h=500&fit=crop" 
                         alt="Food donation illustration">
                    
                    <div class="hero-float-card card-1">
                        <div class="flex items-center gap-3">
                            <div class="stat-icon primary">
                                <i class="fas fa-utensils"></i>
                            </div>
                            <div>
                                <div class="font-bold text-lg"><?php echo number_format($stats['meals_saved']); ?></div>
                                <div class="text-sm text-gray">Meals Saved</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="hero-float-card card-2">
                        <div class="flex items-center gap-3">
                            <div class="stat-icon secondary">
                                <i class="fas fa-hands-helping"></i>
                            </div>
                            <div>
                                <div class="font-bold text-lg"><?php echo number_format($stats['restaurants'] + $stats['ngos']); ?></div>
                                <div class="text-sm text-gray">Partners</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="section how-it-works">
        <div class="container">
            <div class="section-header scroll-animate">
                <span class="section-label">How It Works</span>
                <h2 class="section-title">Simple Steps to Make a Difference</h2>
                <p class="section-subtitle">
                    Our platform makes it easy for restaurants to donate surplus food and for NGOs to distribute it to those in need.
                </p>
            </div>
            
            <div class="steps-grid">
                <div class="step-card scroll-animate">
                    <div class="step-number">1</div>
                    <h3 class="step-title">Restaurants List Food</h3>
                    <p class="step-description">
                        Restaurants post surplus food with details like quantity, type, and pickup time.
                    </p>
                </div>
                
                <div class="step-card scroll-animate">
                    <div class="step-number">2</div>
                    <h3 class="step-title">NGOs Browse & Claim</h3>
                    <p class="step-description">
                        NGOs browse available food listings and claim donations that match their needs.
                    </p>
                </div>
                
                <div class="step-card scroll-animate">
                    <div class="step-number">3</div>
                    <h3 class="step-title">Food is Collected</h3>
                    <p class="step-description">
                        NGOs collect the food from restaurants at the scheduled pickup time.
                    </p>
                </div>
                
                <div class="step-card scroll-animate">
                    <div class="step-number">4</div>
                    <h3 class="step-title">Distributed to Communities</h3>
                    <p class="step-description">
                        Food is distributed to needy communities, feeding the hungry and reducing waste.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section id="about-us" class="section about-section">
        <div class="container">
            <div class="section-header scroll-animate">
                <span class="section-label">About Us</span>
                <h2 class="section-title">Who We Are?</h2>
            </div>
            
            <div class="grid grid-2 about-grid">
                <div class="scroll-animate">
                    <h3 class="about-heading">
                        Fighting Hunger Through Innovation
                    </h3>
                    <p class="about-text">
                        Food-Saver is a mission-driven platform dedicated to reducing food waste while addressing hunger in our communities. We believe that surplus food from restaurants should never go to waste when hungry families need nourishment.
                    </p>
                    <p class="about-text-last">
                        By connecting restaurants, NGOs, and donors, we create a sustainable ecosystem where everyone benefits. Our technology makes it simple, efficient, and impactful to redistribute food and save lives.
                    </p>
                    <button onclick="openDonationModal()" class="btn btn-primary btn-lg">
                        <i class="fas fa-heart"></i>
                        Support Our Mission
                    </button>
                </div>
                
                <div class="scroll-animate">
                    <img src="https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=500&h=500&fit=crop" 
                         alt="About Us" class="about-image">
                </div>
            </div>
        </div>
    </section>

    <!-- Meals Saved Counter -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item scroll-animate">
                    <div class="stat-number" data-counter="<?php echo $stats['meals_saved']; ?>" data-suffix="+">0</div>
                    <div class="stat-text">Meals Saved</div>
                </div>
                <div class="stat-item scroll-animate">
                    <div class="stat-number" data-counter="<?php echo $stats['restaurants']; ?>" data-suffix="+">0</div>
                    <div class="stat-text">Restaurants Registered</div>
                </div>
                <div class="stat-item scroll-animate">
                    <div class="stat-number" data-counter="<?php echo $stats['ngos']; ?>" data-suffix="+">0</div>
                    <div class="stat-text">NGOs Partnered</div>
                </div>
                <div class="stat-item scroll-animate">
                    <div class="stat-number" data-counter="<?php echo $stats['donations']; ?>" data-suffix="+">0</div>
                    <div class="stat-text">Donations Completed</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Objective Section (with Donate CTA) -->
    <section id="objective" class="section objective-section">
        <div class="container">
            <div class="section-header scroll-animate text-center">
                <span class="section-label">Our Mission</span>
                <h2 class="section-title">Our Objective</h2>
                <p class="section-subtitle objective-subtitle">
                    We believe food waste is a social problem. By bridging the gap between restaurants with surplus food and NGOs serving communities, we create a sustainable ecosystem where everyone benefits.
                </p>
            </div>
            
            <div class="grid grid-3 objective-grid">
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <h3>Reduce Waste</h3>
                    <p>Decrease food waste in restaurants and landfills through efficient redistribution.</p>
                </div>
                
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <h3>Feed Communities</h3>
                    <p>Ensure nutritious meals reach underprivileged communities consistently.</p>
                </div>
                
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-people-arrows"></i>
                    </div>
                    <h3>Build Impact</h3>
                    <p>Create a sustainable network of restaurants, NGOs, and donors working together.</p>
                </div>
            </div>
            
            <div class="text-center objective-cta">
                <button onclick="openDonationModal()" class="btn btn-primary btn-lg">
                    <i class="fas fa-heart"></i>
                    Contribute to Our Mission
                </button>
            </div>
        </div>
    </section>

    <!-- Reports Section -->
    <section id="reports" class="section reports-section">
        <div class="container">
            <div class="section-header scroll-animate">
                <span class="section-label">Analytics</span>
                <h2 class="section-title">Our Reports</h2>
                <p class="section-subtitle">
                    Track food distribution, donations, and the impact we're making together.
                </p>
            </div>
            
            <div class="grid grid-2 reports-grid">
                <div class="card scroll-animate">
                    <div class="card-header">
                        <div class="report-card-header">
                            <i class="fas fa-file-pdf report-icon"></i>
                            <h3>Annual Report 2025</h3>
                        </div>
                        <p class="card-subtitle">Comprehensive yearly overview</p>
                    </div>
                    <div class="card-body">
                        <p class="report-card-text">
                            Complete analysis of food donations, distribution, and community impact throughout 2025.
                        </p>
                        <div class="report-card-meta">
                            <span><i class="fas fa-calendar"></i> Year 2025</span>
                            <span><i class="fas fa-download"></i> ~3.5 MB</span>
                        </div>
                        <a href="pages/reports.php" class="btn btn-primary btn-sm report-card-button">
                            <i class="fas fa-download"></i> Download PDF
                        </a>
                    </div>
                </div>
                
                <div class="card scroll-animate">
                    <div class="card-header">
                        <div class="report-card-header">
                            <i class="fas fa-file-pdf report-icon"></i>
                            <h3>February 2026 Report</h3>
                        </div>
                        <p class="card-subtitle">Monthly detailed analysis</p>
                    </div>
                    <div class="card-body">
                        <p class="report-card-text">
                            Detailed month-by-month performance metrics, food distribution statistics, and partner activities.
                        </p>
                        <div class="report-card-meta">
                            <span><i class="fas fa-calendar"></i> February 2026</span>
                            <span><i class="fas fa-download"></i> ~1.8 MB</span>
                        </div>
                        <a href="pages/reports.php" class="btn btn-primary btn-sm report-card-button">
                            <i class="fas fa-download"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center reports-cta-container">
                <a href="pages/reports.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-chart-line"></i> View All Reports & Statistics
                </a>
            </div>
        </div>
    </section>

    <!-- Have a Query Section -->
    <section id="contact" class="section">
        <div class="container">
            <div class="scroll-animate">
                <div style="text-align: center; padding: 3rem;">
                    <span class="section-label">Questions?</span>
                    <h2 class="section-title">Have a Query?</h2>
                    <p class="section-subtitle" style="max-width: 600px; margin: 1.5rem auto;">
                        Got questions about FoodSaver or need assistance? Our dedicated support team is here to help you.
                        Reach out to us anytime and we'll get back to you promptly.
                    </p>
                    <a href="pages/contact-page.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-envelope"></i> Contact Us
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Restaurant Testimonials Section -->
    <section class="section testimonials-section restaurant-testimonials-section">
        <div class="container">
            <div class="section-header scroll-animate">
                <span class="section-label">Testimonials</span>
                <h2 class="section-title">What Restaurants Say</h2>
                <p class="section-subtitle">
                    Hear from restaurant partners who are reducing waste and making a social impact.
                </p>
            </div>
            
            <div class="grid grid-3">
                <div class="testimonial-card scroll-animate">
                    <div class="testimonial-content">
                        Food-Saver has transformed how we handle surplus food. Instead of wasting it, 
                        we now feed hundreds of people every month. It's fulfilling and good for business.
                    </div>
                    <div class="testimonial-author">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face" 
                             alt="Rajesh Kumar" class="testimonial-avatar">
                        <div class="testimonial-info">
                            <h4>Rajesh Kumar</h4>
                            <p>Owner, Spice Garden Restaurant</p>
                            <div class="testimonial-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card scroll-animate">
                    <div class="testimonial-content">
                        The platform is incredibly user-friendly. Posting food donations takes just a few minutes, 
                        and I love seeing the impact we're making. Our customers appreciate our commitment to sustainability.
                    </div>
                    <div class="testimonial-author">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face" 
                             alt="Arjun Verma" class="testimonial-avatar">
                        <div class="testimonial-info">
                            <h4>Arjun Verma</h4>
                            <p>Manager, The Golden Fork</p>
                            <div class="testimonial-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card scroll-animate">
                    <div class="testimonial-content">
                        We were struggling with food waste until we joined Food-Saver. Now we get tax benefits 
                        and our waste has reduced by 40%. It's a win-win for everyone involved.
                    </div>
                    <div class="testimonial-author">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face" 
                             alt="Simran Gupta" class="testimonial-avatar">
                        <div class="testimonial-info">
                            <h4>Simran Gupta</h4>
                            <p>Owner, Urban Eats Cafe</p>
                            <div class="testimonial-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NGO Testimonials Section -->
    <section class="section testimonials-section ngo-testimonials-section">
        <div class="container">
            <div class="section-header scroll-animate">
                <span class="section-label">Testimonials</span>
                <h2 class="section-title">What NGOs Say</h2>
                <p class="section-subtitle">
                    Hear from NGO partners who are using Food-Saver to serve their communities.
                </p>
            </div>
            
            <div class="grid grid-3">
                <div class="testimonial-card scroll-animate">
                    <div class="testimonial-content">
                        As an NGO, finding consistent food sources was always a challenge. 
                        Food-Saver has made it so much easier to access quality food donations regularly.
                    </div>
                    <div class="testimonial-author">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face" 
                             alt="Priya Sharma" class="testimonial-avatar">
                        <div class="testimonial-info">
                            <h4>Priya Sharma</h4>
                            <p>Director, Hope Foundation</p>
                            <div class="testimonial-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card scroll-animate">
                    <div class="testimonial-content">
                        The scheduled pickup feature has been a game-changer for us. We can now plan our operations 
                        better and serve more people without having to worry about logistics.
                    </div>
                    <div class="testimonial-author">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face" 
                             alt="Vikram Singh" class="testimonial-avatar">
                        <div class="testimonial-info">
                            <h4>Vikram Singh</h4>
                            <p>Executive Secretary, Helping Hands NGO</p>
                            <div class="testimonial-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card scroll-animate">
                    <div class="testimonial-content">
                        With Food-Saver, we've been able to increase our outreach by 60%. The connections we make 
                        with restaurants have become long-term partnerships that benefit our entire community.
                    </div>
                    <div class="testimonial-author">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop&crop=face" 
                             alt="Neha Patel" class="testimonial-avatar">
                        <div class="testimonial-info">
                            <h4>Neha Patel</h4>
                            <p>Program Manager, Community Care Alliance</p>
                            <div class="testimonial-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Start Your Journey Section -->
    <section class="section start-journey-section">
        <div class="container">
            <div class="scroll-animate">
                <h2 class="section-title start-journey-title">Start Your Journey Now</h2>
                <p class="start-journey-text">
                    Join thousands of restaurants, NGOs, and volunteers who are making a real difference in fighting hunger and reducing food waste. Together, we can create a sustainable future where no food goes to waste.
                </p>
                <a href="pages/register.php" class="btn btn-white btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Join Us Now
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="index.php" class="logo">
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
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#about-us">About Us</a></li>
                        <li><a href="#objective">Objective</a></li>
                        <li><a href="#reports">Reports</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Support</h4>
                    <ul class="footer-links">
                        <li><a href="pages/contact-page.php">Contact Us</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Contact</h4>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope"></i> contact@foodsaver.org</li>
                        <li><i class="fas fa-phone"></i> +91 98765 43210</li>
                        <li><i class="fas fa-map-marker-alt"></i> Mumbai, India</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Food-Saver. All rights reserved.</p>
                <p>Made with <i class="fas fa-heart text-error"></i> for a hunger-free world</p>
            </div>
        </div>
    </footer>

    <!-- Donation Modal -->
    <div id="donationModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); animation: fadeIn 300ms ease; justify-content: center; align-items: center;">
        <div class="modal-content" style="background-color: white; margin: auto; padding: 0; border-radius: var(--radius-xl); width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); animation: slideUp 300ms ease;">
            <div style="padding: 2rem; background: linear-gradient(135deg, var(--primary-500), var(--primary-600)); color: white; border-radius: var(--radius-xl) var(--radius-xl) 0 0; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin: 0; font-size: 1.5rem;"><i class="fas fa-heart"></i> Make a Donation</h2>
                <button onclick="closeDonationModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; transition: transform 200ms;">&times;</button>
            </div>
            <div style="padding: 2rem;">
                <?php if ($isLoggedIn): ?>
                    <form method="POST" action="dashboards/user.php">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="make_donation">
                        <input type="hidden" name="ngo_id" value="0">
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-weight: 600; margin-bottom: 1rem;">Choose Amount</label>
                            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                                <button type="button" class="donation-amount" data-val="100" style="padding: 1rem; border: 2px solid var(--gray-200); background: white; border-radius: var(--radius-lg); cursor: pointer; font-weight: 600; transition: all 200ms; color: var(--gray-700);">₹100</button>
                                <button type="button" class="donation-amount" data-val="500" style="padding: 1rem; border: 2px solid var(--gray-200); background: white; border-radius: var(--radius-lg); cursor: pointer; font-weight: 600; transition: all 200ms; color: var(--gray-700);">₹500</button>
                                <button type="button" class="donation-amount active" data-val="1000" style="padding: 1rem; border: 2px solid var(--primary-600); background: var(--primary-600); border-radius: var(--radius-lg); cursor: pointer; font-weight: 600; transition: all 200ms; color: white; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">₹1000</button>
                                <button type="button" class="donation-amount" data-val="5000" style="padding: 1rem; border: 2px solid var(--gray-200); background: white; border-radius: var(--radius-lg); cursor: pointer; font-weight: 600; transition: all 200ms; color: var(--gray-700);">₹5000</button>
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label for="customAmount" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Or Enter Custom Amount</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-weight: 600;">₹</span>
                                <input type="number" id="customAmount" name="amount" class="form-control" value="1000" min="10" required style="padding: 0.75rem; border: 1px solid var(--gray-300); border-radius: var(--radius-md);">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block" style="padding: 1rem; font-weight: 600; width: 100%;">
                            <i class="fas fa-donate"></i> Donate Now
                        </button>
                    </form>
                <?php else: ?>
                    <div style="background: var(--primary-50); border: 1px solid var(--primary-200); border-radius: var(--radius-lg); padding: 1.5rem; text-align: center; color: var(--primary-700);">
                        <i class="fas fa-info-circle" style="font-size: 1.5rem; margin-bottom: 1rem; display: block;"></i>
                        <p style="margin: 0 0 1rem 0;">To contribute to our mission, please log in first.</p>
                        <a href="pages/login.php" style="color: var(--primary-600); font-weight: 600; text-decoration: none; display: inline-block; padding: 0.75rem 1.5rem; background: white; border-radius: var(--radius-lg); border: 1px solid var(--primary-300);">
                            Login to Contribute →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

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
        @media (max-width: 768px) {
            #donationModal > div > div:first-child {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
    </style>

    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
    <script>
        function closeContactThankYouModal() {
            const modal = document.getElementById('contactThankYouModal');
            if (modal) {
                modal.classList.remove('show');
                modal.style.display = 'none';
            }
        }

        function openDonationModal() {
            document.getElementById('donationModal').classList.add('show');
        }

        function closeDonationModal() {
            document.getElementById('donationModal').classList.remove('show');
        }

        document.getElementById('donationModal').addEventListener('click', function(e) {
            if (e.target === this) closeDonationModal();
        });

        document.querySelectorAll('.donation-amount').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.donation-amount').forEach(b => {
                    b.style.background = 'white';
                    b.style.borderColor = 'var(--gray-200)';
                    b.style.color = 'var(--gray-700)';
                    b.classList.remove('active');
                });
                this.style.background = 'var(--primary-600)';
                this.style.borderColor = 'var(--primary-600)';
                this.style.color = 'white';
                this.style.boxShadow = '0 4px 12px rgba(16, 185, 129, 0.3)';
                this.classList.add('active');
                document.getElementById('customAmount').value = this.dataset.val;
            });
        });

        document.getElementById('customAmount')?.addEventListener('input', function() {
            document.querySelectorAll('.donation-amount').forEach(b => {
                b.style.background = 'white';
                b.style.borderColor = 'var(--gray-200)';
                b.style.color = 'var(--gray-700)';
                b.classList.remove('active');
            });
        });
    </script>
</body>
</html>
