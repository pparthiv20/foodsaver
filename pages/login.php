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
            $table = '';
            
            switch ($userType) {
                case 'admin':
                    $table = 'admins';
                    break;
                case 'restaurant':
                    $table = 'restaurants';
                    break;
                case 'ngo':
                    $table = 'ngos';
                    break;
                case 'user':
                    $table = 'users';
                    break;
                default:
                    $error = 'Invalid user type.';
            }
            
            if (empty($error) && !empty($table)) {
                $stmt = $db->prepare("SELECT * FROM {$table} WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    // Check status for restaurants and NGOs
                    if (isset($user['status'])) {
                        if ($user['status'] === 'pending') {
                            $error = 'Your account is pending approval. Please wait for admin verification.';
                        } elseif ($user['status'] === 'rejected' || $user['status'] === 'suspended' || $user['status'] === 'blocked') {
                            $error = 'Your account has been ' . $user['status'] . '. Please contact support.';
                        }
                    }
                    
                    if (empty($error)) {
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_type'] = $userType;
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_name'] = $user['full_name'] ?? $user['restaurant_name'] ?? $user['ngo_name'] ?? $user['username'];
                        
                        // Update last login
                        $db->prepare("UPDATE {$table} SET last_login = NOW() WHERE id = ?")
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
                    $error = 'Invalid email or password.';
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
        }
        
        .auth-header .logo-icon {
            background: rgba(255, 255, 255, 0.2);
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
                        
                        <div class="form-group">
                            <label class="form-label required">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                                <button type="button" class="btn btn-ghost" data-toggle-password="password" style="border-radius: 0 var(--radius-lg) var(--radius-lg) 0;">
                                    <i class="fas fa-eye"></i>
                                </button>
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
    </script>
</body>
</html>
