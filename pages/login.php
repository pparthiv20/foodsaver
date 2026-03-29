<?php
/**
 * Food-Saver - Login Page
 * Multi-role authentication system
 */

require_once '../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectBasedOnRole();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $userType = $_POST['user_type'] ?? '';
        
        if (empty($email) || empty($password) || empty($userType)) {
            $error = 'Please fill in all fields.';
        } else {
            $db = getDB();
            $tableName = '';

            // Validate user type and get table name safely
            $tableName = getTableNameForUserType($userType);
            if (!$tableName) {
                $error = 'Invalid user type.';
            }

            if (empty($error)) {
                // Rate limiting check
                if (!checkRateLimit($email, 'login')) {
                    $remaining = getRemainingAttempts($email, 'login');
                    $error = 'Too many login attempts. Please try again in a few minutes.';
                    // Log suspicious activity
                    logActivity(0, 'system', 'brute_force_attempt', 'Multiple failed login attempts from: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                } else {
                    $stmt = $db->prepare("SELECT * FROM {$tableName} WHERE email = ? LIMIT 1");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();

                    if ($user && password_verify($password, $user['password'])) {
                        // Valid credentials - reset rate limit
                        unset($_SESSION['rate_limit'][$email]['login']);

                        // Check account status before logging in
                        if (isset($user['status'])) {
                            if ($user['status'] === 'pending') {
                                $error = 'Your account is pending approval. Please wait for admin verification.';
                            } elseif (in_array($user['status'], ['rejected', 'suspended', 'blocked'], true)) {
                                // Redirect blocked/suspended/rejected accounts to suspended page
                                header('Location: ' . APP_URL . '/pages/suspended.php?type=' . urlencode($userType));
                                exit;
                            }
                        }

                        if (empty($error)) {
                            // Set session variables
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_type'] = $userType;
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['user_name'] = $user['full_name'] ?? $user['restaurant_name'] ?? $user['ngo_name'] ?? $user['username'];

                            // Update last login
                            $db->prepare("UPDATE {$tableName} SET last_login = NOW() WHERE id = ?")
                               ->execute([$user['id']]);

                            // Log activity
                            logActivity($user['id'], $userType, 'login', 'User logged in successfully');

                            // Redirect
                            $redirect = $_SESSION['redirect_url'] ?? null;
                            unset($_SESSION['redirect_url']);

                            if ($redirect) {
                                header('Location: ' . $redirect);
                            } else {
                                redirectBasedOnRole();
                            }
                            exit;
                        }
                    } else {
                        // Record failed attempt for rate limiting
                        recordAttempt($email, 'login');
                        $error = 'Invalid email or password.';
                    }
                }
            }
        }
    }
}

$flash = getFlashMessage();
if ($flash) {
    if ($flash['type'] === 'success') {
        $success = $flash['message'];
    } else {
        $error = $flash['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/mobile-responsive.css">
    <link rel="stylesheet" href="../assets/css/mobile-advanced.css">
    <link rel="stylesheet" href="../assets/css/micro-interactions.css">
    <style>
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-50) 0%, white 50%, var(--secondary-50) 100%);
            padding: var(--space-lg);
        }
        
        .auth-container {
            width: 100%;
            max-width: 480px;
        }
        
        .auth-card {
            background: white;
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            transition: all 300ms ease;
            display: none;
        }
        
        .auth-card.active {
            display: block;
        }
        
        .auth-card.admin-card {
            background: linear-gradient(135deg, #1f2937, #111827);
            color: white;
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: white;
            padding: var(--space-2xl);
            text-align: center;
        }
        
        .admin-card .auth-header {
            background: linear-gradient(135deg, #dc2626, #991b1b);
        }
        
        .auth-header .logo {
            color: white;
            justify-content: center;
            margin-bottom: var(--space-md);
            background: rgba(255, 255, 255, 0.15);
            padding: var(--space-sm) var(--space-lg);
            border-radius: var(--radius-lg);
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .auth-header .logo-icon {
            background: rgba(255, 255, 255, 0.25);
            border-radius: 8px;
        }
        
        .auth-body {
            padding: var(--space-2xl);
        }
        
        .admin-card .auth-body {
            color: white;
        }
        
        .user-type-tabs {
            display: flex;
            gap: var(--space-xs);
            margin-bottom: var(--space-lg);
            background: var(--gray-100);
            padding: var(--space-xs);
            border-radius: var(--radius-lg);
        }
        
        .user-type-tab {
            flex: 1;
            padding: var(--space-sm) var(--space-md);
            border: none;
            background: transparent;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-600);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .user-type-tab:hover {
            color: var(--gray-900);
        }
        
        .user-type-tab.active {
            background: white;
            color: var(--primary-600);
            box-shadow: var(--shadow-sm);
        }
        
        .admin-card .form-label {
            color: #e5e7eb;
        }
        
        .admin-card .form-control {
            background: #374151;
            border-color: #4b5563;
            color: white;
        }
        
        .admin-card .form-control::placeholder {
            color: #9ca3af;
        }
        
        .admin-card .form-control:focus {
            background: #4b5563;
            border-color: #dc2626;
            color: white;
        }
        
        .admin-card .input-group-text {
            background: #374151;
            border-color: #4b5563;
            color: #9ca3af;
        }
        
        .admin-card .form-check-label {
            color: #d1d5db;
        }
        
        .auth-footer {
            text-align: center;
            padding-top: var(--space-lg);
            border-top: 1px solid var(--gray-100);
        }
        
        .admin-card .auth-footer {
            border-top-color: #374151;
        }
        
        .admin-mode-indicator {
            background: #dc2626;
            color: white;
            padding: 8px 0;
            text-align: center;
            font-weight: 600;
            border-bottom: 3px solid #991b1b;
        }
        
        .form-group .form-label {
            display: block;
            margin-bottom: 0.5rem;
        }
        
        /* Combined Input Box Container */
        .input-box-wrapper {
            border: 2px solid #e5e7eb;
            border-radius: var(--radius-2xl);
            padding: var(--space-lg);
            background: white;
        }
        
        .input-box-wrapper .form-group {
            margin-bottom: 0;
        }
        
        .input-box-wrapper .form-group:first-child {
            /* margin-bottom: var(--space-lg); */
            padding-bottom: var(--space-lg);
            /* border-bottom: 1px solid #e5e7eb; */
        }
        
        .input-box-wrapper .form-group label {
            font-size: 0.95rem;
            color: #374151;
            margin-bottom: 0.75rem;
        }
        
        .input-box-wrapper .input-group {
            /* border-radius: var(--radius-lg); */
            border: 1px solid #d1d5db;
            display: flex;
            overflow: hidden;
        }
        
        .input-box-wrapper .input-group-text {
            background: #f3f4f6;
            border: none;
            border-right: 1px solid #d1d5db;
            color: #6b7280;
            padding: var(--space-md);
        }
        
        .input-box-wrapper .form-control {
            border: none;
            padding: var(--space-md);
            flex: 1;
            background: white;
            color: #1f2937;
        }
        
        .input-box-wrapper .form-control::placeholder {
            color: #9ca3af;
        }
        
        .input-box-wrapper .form-control:focus {
            outline: none;
            background: #f9fafb;
        }
        
        .input-box-wrapper .btn {
            border: none;
            background: white;
            color: #6b7280;
            padding: var(--space-md);
            cursor: pointer;
            border-left: 1px solid #d1d5db;
        }
        
        .input-box-wrapper .btn:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }
        
        /* OAuth Styling */
        .oauth-divider {
            display: flex;
            align-items: center;
            margin: var(--space-lg) 0;
            gap: var(--space-md);
        }
        
        .oauth-divider::before,
        .oauth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--gray-200);
        }
        
        .oauth-divider span {
            font-size: 0.875rem;
            color: var(--gray-500);
            font-weight: 500;
        }
        
        .admin-card .oauth-divider::before,
        .admin-card .oauth-divider::after {
            background: #4b5563;
        }
        
        .oauth-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
        }
        
        .oauth-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-sm);
            padding: var(--space-md) var(--space-lg);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            background: white;
            color: var(--gray-700);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all var(--transition-fast);
            cursor: pointer;
        }
        
        .oauth-btn:hover {
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
        }
        
        .oauth-btn span {
            font-size: 0.875rem;
        }
        
        .oauth-icon {
            width: 18px;
            height: 18px;
        }
        
        .oauth-google:hover {
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.2);
        }

        .oauth-facebook:hover {
            box-shadow: 0 4px 12px rgba(24, 119, 242, 0.2);
        }
        
        .admin-card .oauth-btn {
            background: #374151;
            border-color: #4b5563;
            color: #d1d5db;
        }
        
        .admin-card .oauth-btn:hover {
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }
        
        /* Responsive Design */
        @media (max-width: 600px) {
            .oauth-buttons {
                grid-template-columns: 1fr;
            }
            
            .oauth-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-container">
            <!-- Public Login Card -->
            <div class="auth-card active">
                <div class="auth-header">
                    <a href="../index.php" class="logo">
                        <div class="logo-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <span>Food-Saver</span>
                    </a>
                    <h2>Welcome Back!</h2>
                    <p>Sign in to your account to continue</p>
                </div>
                
                <div class="auth-body">
                    <?php if ($error): ?>
                        <div style="padding: 12px; background: #fee; border-left: 4px solid #dc2626; border-radius: 6px; margin-bottom: 16px; color: #dc2626; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success" data-auto-dismiss="5000">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" data-validate id="public-form">
                        <?php echo csrfField(); ?>
                        
                        <div class="user-type-tabs">
                            <button type="button" class="user-type-tab active" data-type="user">
                                <i class="fas fa-user"></i> Donor
                            </button>
                            <button type="button" class="user-type-tab" data-type="restaurant">
                                <i class="fas fa-utensils"></i> Restaurant
                            </button>
                            <button type="button" class="user-type-tab" data-type="ngo">
                                <i class="fas fa-hands-helping"></i> NGO
                            </button>
                        </div>
                        
                        <input type="hidden" name="user_type" id="user_type" value="user">
                        
                        <div class="input-box-wrapper">
                            <div class="form-group" id="email-group">
                                <label class="form-label required">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                                </div>
                            </div>
                            
                            <div class="form-group" id="password-group">
                                <label class="form-label required">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                                    <button type="button" class="btn btn-ghost" data-toggle-password="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center mb-4">
                            <label class="form-check">
                                <input type="checkbox" name="remember" class="form-check-input">
                                <span class="form-check-label">Remember me</span>
                            </label>
                            <a href="forgot-password.php" class="text-sm text-primary">Forgot password?</a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-sign-in-alt"></i>
                            Sign In
                        </button>
                    </form>
                    
                    <!-- Social Login Divider -->
                    <div class="oauth-divider">
                        <span>Or continue with</span>
                    </div>
                    
                    <!-- Social Login Buttons -->
                    <div class="oauth-buttons">
                        <a href="#" id="google-login-btn" class="oauth-btn oauth-google" title="Continue with Google">
                            <svg class="oauth-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span>Google</span>
                        </a>
                        
                        <a href="#" id="facebook-login-btn" class="oauth-btn oauth-facebook" title="Continue with Facebook">
                            <svg class="oauth-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            <span>Facebook</span>
                        </a>
                    </div>
                    
                    <div class="auth-footer">
                        <p class="text-gray">Don't have an account?</p>
                        <div class="flex gap-3 justify-center mt-3">
                            <a href="register.php?type=user" class="btn btn-outline btn-sm">Join as Donor</a>
                            <a href="register.php?type=restaurant" class="btn btn-outline btn-sm">Join as Restaurant</a>
                            <a href="register.php?type=ngo" class="btn btn-outline btn-sm">Join as NGO</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Login Card -->
            <div class="auth-card admin-card">
                <div class="admin-mode-indicator">
                    <i class="fas fa-shield-alt"></i> ADMIN ACCESS
                </div>
                <div class="auth-header">
                    <a href="../index.php" class="logo">
                        <div class="logo-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <span>Food-Saver</span>
                    </a>
                    <h2>Admin Portal</h2>
                    <p>Restricted Access - Administrators Only</p>
                </div>
                
                <div class="auth-body">
                    <?php if ($error): ?>
                        <div style="padding: 12px; background: rgba(220, 38, 38, 0.2); border-left: 4px solid #dc2626; border-radius: 6px; margin-bottom: 16px; color: #fca5a5; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" data-validate id="admin-form">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="user_type" value="admin">
                        
                        <div class="form-group">
                            <label class="form-label required">Admin Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="Enter admin email" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Admin Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" id="admin-password" class="form-control" placeholder="Enter admin password" required>
                                <button type="button" class="btn btn-ghost" data-toggle-password="admin-password" style="border-radius: 0 var(--radius-lg) var(--radius-lg) 0); background: #374151; border-color: #4b5563; color: #9ca3af;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-lg btn-block" style="background: #dc2626; color: white; border: none; margin-top: 1.5rem;">
                            <i class="fas fa-sign-in-alt"></i>
                            Admin Login
                        </button>
                    </form>
                    
                    <!-- Social Login Divider -->
                    <div class="oauth-divider" style="color: #e5e7eb;">
                        <span>Or continue with</span>
                    </div>
                    
                    <!-- Social Login Buttons -->
                    <div class="oauth-buttons">
                        <a href="#" id="admin-google-login-btn" class="oauth-btn oauth-google" title="Continue with Google">
                            <svg class="oauth-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span>Google</span>
                        </a>
                        
                        <a href="#" id="admin-facebook-login-btn" class="oauth-btn oauth-facebook" title="Continue with Facebook">
                            <svg class="oauth-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            <span>Facebook</span>
                        </a>
                    </div>
                    
                    <div class="auth-footer">
                        <button type="button" id="back-to-public" class="btn btn-sm" style="background: transparent; color: #9ca3af; border: 1px solid #4b5563;">
                            <i class="fas fa-arrow-left"></i> Back to Public Login
                        </button>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 24px; display: flex; justify-content: space-between; align-items: center; gap: 16px;" id="footer-links">
                <p class="text-sm text-gray">
                    <a href="../index.php" style="color: var(--primary-600); text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </p>
                <a href="#" id="admin-login-link" class="text-sm text-gray" style="color: var(--gray-600); text-decoration: none; cursor: pointer;">
                    Admin Login
                </a>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/micro-interactions.js"></script>
    <script>
        // User type tab switching
        document.querySelectorAll('.user-type-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.user-type-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('user_type').value = this.dataset.type;
            });
        });
        
        // Set active tab from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const type = urlParams.get('type');
        if (type && ['user', 'restaurant', 'ngo'].includes(type)) {
            document.querySelectorAll('.user-type-tab').forEach(tab => {
                tab.classList.toggle('active', tab.dataset.type === type);
            });
            document.getElementById('user_type').value = type;
        }
        
        // Admin login switch
        document.getElementById('admin-login-link').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('.auth-card.active').classList.remove('active');
            document.querySelector('.auth-card.admin-card').classList.add('active');
            document.getElementById('footer-links').style.display = 'none';
        });
        
        // Back to public login
        document.getElementById('back-to-public').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('.auth-card.admin-card').classList.remove('active');
            document.querySelector('.auth-card:not(.admin-card)').classList.add('active');
            document.getElementById('footer-links').style.display = 'flex';
        });
        
        // OAuth button handlers
        function setupOAuthButtons() {
            const googleBtns = document.querySelectorAll('#google-login-btn, #admin-google-login-btn');
            const facebookBtns = document.querySelectorAll('#facebook-login-btn, #admin-facebook-login-btn');
            
            googleBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    redirectToOAuth('google', 'admin');
                });
            });
            
            facebookBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    redirectToOAuth('facebook', 'admin');
                });
            });
        }
        
        function redirectToOAuth(provider, userType) {
            // Store current user type in session via query parameter
            const url = window.location.origin + window.location.pathname.split('/pages')[0] + '/pages/oauth_start.php?provider=' + encodeURIComponent(provider) + '&type=' + encodeURIComponent(userType);
            window.location.href = url;
        }
        
        // Initialize OAuth buttons
        setupOAuthButtons();
    </script>
</body>
</html>
