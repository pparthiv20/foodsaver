<?php
/**
 * Food-Saver - Landing Page
 * Modern, elegant landing page with all sections
 */

require_once 'includes/config.php';

// Get site statistics
$stats = getSiteStatistics();
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
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌱</text></svg>">
</head>
<body>
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
                    <li><a href="#about">About</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#restaurants">Restaurants</a></li>
                    <li><a href="#ngos">NGOs</a></li>
                    <li><a href="#donate">Donate</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                
                <div class="nav-actions">
                    <a href="pages/login.php" class="btn btn-ghost">Login</a>
                    <a href="pages/register.php" class="btn btn-primary">Join Now</a>
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
                        <a href="#donate" class="btn btn-primary btn-lg">
                            <i class="fas fa-heart"></i>
                            Donate Now
                        </a>
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

    <!-- Impact Statistics Section -->
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

    <!-- Restaurant Benefits Section -->
    <section id="restaurants" class="section">
        <div class="container">
            <div class="section-header scroll-animate">
                <span class="section-label">For Restaurants</span>
                <h2 class="section-title">Why Join as a Restaurant?</h2>
                <p class="section-subtitle">
                    Make a positive impact while reducing waste and building your brand.
                </p>
            </div>
            
            <div class="grid grid-3">
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <h3>Reduce Food Waste</h3>
                    <p>Turn surplus food into meals for the hungry instead of throwing it away.</p>
                </div>
                
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Social Impact</h3>
                    <p>Contribute to social welfare and help feed underprivileged communities.</p>
                </div>
                
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-list-check"></i>
                    </div>
                    <h3>Easy Listing</h3>
                    <p>Simple and quick process to list surplus food with all necessary details.</p>
                </div>
                
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Verified NGOs</h3>
                    <p>All NGOs are verified by our team to ensure safe food distribution.</p>
                </div>
                
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Track Impact</h3>
                    <p>See the real impact of your donations with detailed statistics.</p>
                </div>
                
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3>Recognition</h3>
                    <p>Get recognized as a socially responsible business in your community.</p>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="pages/register.php?type=restaurant" class="btn btn-primary btn-lg">
                    Register Your Restaurant
                </a>
            </div>
        </div>
    </section>

    <!-- NGO Benefits Section -->
    <section id="ngos" class="section" style="background: var(--gray-50);">
        <div class="container">
            <div class="section-header scroll-animate">
                <span class="section-label">For NGOs</span>
                <h2 class="section-title">Why Join as an NGO?</h2>
                <p class="section-subtitle">
                    Access a steady stream of food donations to support your community work.
                </p>
            </div>
            
            <div class="grid grid-3">
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Access Nearby Food</h3>
                    <p>Browse and claim food donations from restaurants in your area.</p>
                </div>
                
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Organized Collection</h3>
                    <p>Schedule pickups at convenient times with all details provided.</p>
                </div>
                
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Community Support</h3>
                    <p>Get support from donors to fund your food distribution logistics.</p>
                </div>
                
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Transparent Tracking</h3>
                    <p>Track food from donation to delivery with our transparent system.</p>
                </div>
                
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <h3>Serve More People</h3>
                    <p>Increase your capacity to serve more people in need.</p>
                </div>
                
                <div class="feature-card scroll-animate">
                    <div class="feature-icon">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <h3>Network Growth</h3>
                    <p>Connect with more restaurants and expand your food sources.</p>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="pages/register.php?type=ngo" class="btn btn-secondary btn-lg">
                    Register Your NGO
                </a>
            </div>
        </div>
    </section>

    <!-- Donation Section -->
    <section id="donate" class="section donation-section">
        <div class="container">
            <div class="section-header scroll-animate">
                <span class="section-label">Support Our Mission</span>
                <h2 class="section-title">Make a Donation</h2>
                <p class="section-subtitle">
                    Your financial contribution helps us cover logistics and expand our reach to more communities.
                </p>
            </div>
            
            <div class="card" style="max-width: 600px; margin: 0 auto;">
                <div class="card-body">
                    <form action="pages/donate.php" method="POST">
                        <?php echo csrfField(); ?>
                        
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
                            <div class="grid grid-2 gap-3">
                                <label class="form-check" style="padding: var(--space-md); border: 2px solid var(--gray-200); border-radius: var(--radius-lg); cursor: pointer;">
                                    <input type="radio" name="payment_method" value="upi" class="form-check-input" checked>
                                    <span class="form-check-label">
                                        <i class="fas fa-mobile-alt"></i> UPI
                                    </span>
                                </label>
                                <label class="form-check" style="padding: var(--space-md); border: 2px solid var(--gray-200); border-radius: var(--radius-lg); cursor: pointer;">
                                    <input type="radio" name="payment_method" value="credit_card" class="form-check-input">
                                    <span class="form-check-label">
                                        <i class="fas fa-credit-card"></i> Credit Card
                                    </span>
                                </label>
                                <label class="form-check" style="padding: var(--space-md); border: 2px solid var(--gray-200); border-radius: var(--radius-lg); cursor: pointer;">
                                    <input type="radio" name="payment_method" value="debit_card" class="form-check-input">
                                    <span class="form-check-label">
                                        <i class="fas fa-credit-card"></i> Debit Card
                                    </span>
                                </label>
                                <label class="form-check" style="padding: var(--space-md); border: 2px solid var(--gray-200); border-radius: var(--radius-lg); cursor: pointer;">
                                    <input type="radio" name="payment_method" value="net_banking" class="form-check-input">
                                    <span class="form-check-label">
                                        <i class="fas fa-university"></i> Net Banking
                                    </span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Message (Optional)</label>
                            <textarea name="message" class="form-control" rows="3" placeholder="Leave a message with your donation..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-heart"></i>
                            Donate Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="section testimonials-section">
        <div class="container">
            <div class="section-header scroll-animate">
                <span class="section-label">Testimonials</span>
                <h2 class="section-title">What Our Partners Say</h2>
                <p class="section-subtitle">
                    Hear from the restaurants, NGOs, and volunteers who are making a difference.
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
                        I've been volunteering with Food-Saver for 6 months. The platform is so well-organized 
                        that we can focus on what matters most - getting food to people who need it.
                    </div>
                    <div class="testimonial-author">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face" 
                             alt="Amit Patel" class="testimonial-avatar">
                        <div class="testimonial-info">
                            <h4>Amit Patel</h4>
                            <p>Volunteer, Food-Saver</p>
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

    <!-- Contact Section -->
    <section id="contact" class="section">
        <div class="container">
            <div class="section-header scroll-animate">
                <span class="section-label">Get In Touch</span>
                <h2 class="section-title">Contact Us</h2>
                <p class="section-subtitle">
                    Have questions or want to learn more? We'd love to hear from you.
                </p>
            </div>
            
            <div class="grid grid-2" style="max-width: 900px; margin: 0 auto;">
                <div class="card scroll-animate">
                    <div class="card-body">
                        <form action="pages/contact.php" method="POST" data-validate>
                            <?php echo csrfField(); ?>
                            
                            <div class="form-group">
                                <label class="form-label required">Your Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Email Address</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Subject</label>
                                <input type="text" name="subject" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Message</label>
                                <textarea name="message" class="form-control" rows="5" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane"></i>
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="scroll-animate">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="mb-4"><i class="fas fa-map-marker-alt text-primary"></i> Our Office</h3>
                            <p class="text-gray">
                                123 Green Street, Eco Park<br>
                                Mumbai, Maharashtra 400001<br>
                                India
                            </p>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="mb-4"><i class="fas fa-envelope text-primary"></i> Email Us</h3>
                            <p class="text-gray">
                                General: contact@foodsaver.org<br>
                                Support: support@foodsaver.org<br>
                                Partnerships: partner@foodsaver.org
                            </p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h3 class="mb-4"><i class="fas fa-phone text-primary"></i> Call Us</h3>
                            <p class="text-gray">
                                Phone: +91 98765 43210<br>
                                Mon - Sat: 9:00 AM - 6:00 PM
                            </p>
                        </div>
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
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#restaurants">For Restaurants</a></li>
                        <li><a href="#ngos">For NGOs</a></li>
                        <li><a href="#donate">Donate</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Support</h4>
                    <ul class="footer-links">
                        <li><a href="#contact">Contact Us</a></li>
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

    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
</body>
</html>
