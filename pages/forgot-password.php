<?php
/**
 * Food-Saver - Forgot Password Page
 * Multi-step password reset with OTP verification
 */

require_once '../includes/config.php';

if (isLoggedIn()) {
    redirectBasedOnRole();
}

$error = '';
$success = '';
$step = $_SESSION['reset_step'] ?? 1;
$resetEmail = $_SESSION['reset_email'] ?? '';
$resetType = $_SESSION['reset_type'] ?? '';
$resetOtp = $_SESSION['reset_otp'] ?? '';
$resetExpiry = $_SESSION['reset_otp_expiry'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        // Step 1: Request OTP
        if ($action === 'request_otp') {
            $email = sanitizeInput($_POST['email'] ?? '');
            $userType = $_POST['user_type'] ?? '';
            
            if (empty($email) || empty($userType)) {
                $error = 'Please fill in all fields.';
            } else {
                $table = '';
                switch ($userType) {
                    case 'user': $table = 'users'; break;
                    case 'restaurant': $table = 'restaurants'; break;
                    case 'ngo': $table = 'ngos'; break;
                    default: $error = 'Invalid user type.';
                }
                
                if (empty($error)) {
                    $db = getDB();
                    $stmt = $db->prepare("SELECT id FROM {$table} WHERE email = ? LIMIT 1");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();
                    
                    if ($user) {
                        // Generate OTP
                        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                        $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));
                        
                        // Delete old OTPs for this email
                        $db->prepare("DELETE FROM otp_verifications WHERE email = ? AND purpose = 'password_reset'")->execute([$email]);
                        
                        // Store OTP in database
                        $db->prepare("INSERT INTO otp_verifications (email, otp, purpose, expires_at) VALUES (?, ?, 'password_reset', ?)")
                           ->execute([$email, $otp, $expiry]);
                        
                        // Send OTP via email
                        $emailSubject = "Food-Saver - Password Reset OTP";
                        $emailBody = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 20px; text-align: center; border-radius: 8px; margin-bottom: 20px; }
                                .content { background: #f9fafb; padding: 20px; border-radius: 8px; }
                                .otp-box { background: white; padding: 20px; text-align: center; border: 2px solid #10b981; border-radius: 8px; margin: 20px 0; }
                                .otp-code { font-size: 32px; font-weight: bold; color: #10b981; letter-spacing: 5px; }
                                .footer { text-align: center; color: #6b7280; font-size: 12px; margin-top: 20px; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>🍃 Food-Saver</h1>
                                </div>
                                <div class='content'>
                                    <h2>Password Reset Request</h2>
                                    <p>You requested to reset your password. Please use the OTP below to verify your identity. This OTP will expire in 5 minutes.</p>
                                    
                                    <div class='otp-box'>
                                        <p style='color: #6b7280; margin: 0 0 10px 0;'>Your OTP is:</p>
                                        <div class='otp-code'>$otp</div>
                                    </div>
                                    
                                    <p style='color: #dc2626; font-weight: bold;'>⚠️ Never share this OTP with anyone. We will never ask for it.</p>
                                    
                                    <p style='color: #6b7280; font-size: 14px;'>
                                        If you did not request this, please ignore this email or contact support immediately.
                                    </p>
                                </div>
                                <div class='footer'>
                                    <p>&copy; " . date('Y') . " Food-Saver. All rights reserved.</p>
                                    <p>This is an automated message. Please do not reply to this email.</p>
                                </div>
                            </div>
                        </body>
                        </html>
                        ";
                        
                        $emailSent = sendEmail($email, $emailSubject, $emailBody);
                        
                        // Set session variables
                        $_SESSION['reset_step'] = 2;
                        $_SESSION['reset_email'] = $email;
                        $_SESSION['reset_type'] = $userType;
                        $_SESSION['reset_otp'] = $otp; // For testing only
                        $_SESSION['reset_otp_expiry'] = $expiry;
                        
                        if ($emailSent) {
                            $success = "OTP sent to $email. Valid for 5 minutes.";
                        } else {
                            $success = "OTP generated. Check your email within 5 minutes.";
                        }
                        $step = 2;
                    } else {
                        $error = 'Email not found. Please check and try again.';
                    }
                }
            }
        }
        
        // Step 2: Verify OTP
        elseif ($action === 'verify_otp') {
            $otp = sanitizeInput($_POST['otp'] ?? '');
            
            if (empty($otp)) {
                $error = 'Please enter OTP.';
            } else {
                $db = getDB();
                $stmt = $db->prepare("SELECT * FROM otp_verifications WHERE email = ? AND otp = ? AND purpose = 'password_reset' AND verified = FALSE");
                $stmt->execute([$resetEmail, $otp]);
                $otpRecord = $stmt->fetch();
                
                if (!$otpRecord) {
                    $error = 'Invalid OTP. Please try again.';
                } elseif (time() > strtotime($otpRecord['expires_at'])) {
                    $error = 'OTP expired. Please request a new one.';
                    $_SESSION['reset_step'] = 1;
                    unset($_SESSION['reset_email'], $_SESSION['reset_type'], $_SESSION['reset_otp'], $_SESSION['reset_otp_expiry']);
                } else {
                    // Mark OTP as verified
                    $db->prepare("UPDATE otp_verifications SET verified = TRUE WHERE id = ?")->execute([$otpRecord['id']]);
                    
                    $_SESSION['reset_step'] = 3;
                    $success = 'OTP verified successfully! Please enter your new password.';
                    $step = 3;
                }
            }
        }
        
        // Step 3: Set New Password
        elseif ($action === 'reset_password') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($password) || empty($confirmPassword)) {
                $error = 'Please fill in all fields.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } else {
                $db = getDB();
                
                // Get table name
                $table = '';
                switch ($resetType) {
                    case 'user': $table = 'users'; break;
                    case 'restaurant': $table = 'restaurants'; break;
                    case 'ngo': $table = 'ngos'; break;
                }
                
                if (!empty($table)) {
                    // Update password
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $db->prepare("UPDATE {$table} SET password = ? WHERE email = ?")->execute([$hashedPassword, $resetEmail]);
                    
                    // Mark all OTPs as verified (used)
                    $db->prepare("UPDATE otp_verifications SET verified = TRUE WHERE email = ? AND purpose = 'password_reset'")->execute([$resetEmail]);
                    
                    // Send confirmation email
                    $confirmSubject = "Food-Saver - Password Changed Successfully";
                    $confirmBody = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 20px; text-align: center; border-radius: 8px; margin-bottom: 20px; }
                            .content { background: #f9fafb; padding: 20px; border-radius: 8px; }
                            .success-box { background: #ecfdf5; border-left: 4px solid #10b981; padding: 15px; border-radius: 4px; margin: 20px 0; }
                            .footer { text-align: center; color: #6b7280; font-size: 12px; margin-top: 20px; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>🍃 Food-Saver</h1>
                            </div>
                            <div class='content'>
                                <h2>Password Changed Successfully</h2>
                                <div class='success-box'>
                                    ✓ Your password has been successfully reset.
                                </div>
                                <p>You can now login to your account with your new password.</p>
                                <p><strong>Login URL:</strong> <a href='" . APP_URL . "/pages/login.php'>" . APP_URL . "/pages/login.php</a></p>
                                <p style='color: #6b7280; font-size: 14px;'>
                                    If you did not make this change, please contact support immediately.
                                </p>
                            </div>
                            <div class='footer'>
                                <p>&copy; " . date('Y') . " Food-Saver. All rights reserved.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ";
                    
                    sendEmail($resetEmail, $confirmSubject, $confirmBody);
                    
                    // Clear session
                    unset($_SESSION['reset_step'], $_SESSION['reset_email'], $_SESSION['reset_type'], $_SESSION['reset_otp'], $_SESSION['reset_otp_expiry']);
                    
                    // Log activity
                    $stmt = $db->prepare("SELECT id FROM {$table} WHERE email = ? LIMIT 1");
                    $stmt->execute([$resetEmail]);
                    $user = $stmt->fetch();
                    if ($user) {
                        logActivity($user['id'], $resetType, 'password_reset', 'User reset password successfully');
                    }
                    
                    setFlashMessage('success', 'Password reset successfully! You can now login with your new password.');
                    header('Location: login.php');
                    exit;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/mobile-responsive.css">
    <link rel="stylesheet" href="../assets/css/mobile-advanced.css">
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
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: white;
            padding: var(--space-2xl);
            text-align: center;
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
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <a href="../index.php" class="logo">
                        <div class="logo-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <span>Food-Saver</span>
                    </a>
                    <h2>Reset Password</h2>
                    <p>Enter your email to receive reset instructions</p>
                </div>
                
                <div class="auth-body">
                    <!-- Step Indicator -->
                    <div style="display: flex; gap: 8px; margin-bottom: 24px; justify-content: center;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo $step >= 1 ? 'var(--primary-600)' : 'var(--gray-200)'; ?>; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">1</div>
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo $step >= 2 ? 'var(--primary-600)' : 'var(--gray-200)'; ?>; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">2</div>
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo $step >= 3 ? 'var(--primary-600)' : 'var(--gray-200)'; ?>; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">3</div>
                    </div>

                    <?php if ($error): ?>
                        <div style="padding: 12px; background: #fee; border-left: 4px solid #dc2626; border-radius: 6px; margin-bottom: 16px; color: #dc2626; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div style="padding: 12px; background: #efe; border-left: 4px solid #10b981; border-radius: 6px; margin-bottom: 16px; color: #10b981; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Step 1: Enter Email -->
                    <?php if ($step === 1): ?>
                    <form method="POST" action="" data-validate>
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="request_otp">
                        
                        <h3 style="margin-bottom: 16px; font-size: 1.1rem;">Step 1: Verify Your Account</h3>
                        
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
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block mt-4">
                            <i class="fas fa-paper-plane"></i>
                            Send OTP
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <!-- Step 2: Enter OTP -->
                    <?php if ($step === 2): ?>
                    <form method="POST" action="" data-validate>
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="verify_otp">
                        
                        <h3 style="margin-bottom: 16px; font-size: 1.1rem;">Step 2: Verify OTP</h3>
                        <p style="color: var(--gray-600); margin-bottom: 16px; font-size: 0.9rem;">
                            An OTP has been sent to <strong><?php echo htmlspecialchars($resetEmail); ?></strong>
                        </p>
                        
                        <?php if ($_SESSION['reset_otp'] ?? false): ?>
                        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                            <p style="margin: 0; color: #92400e; font-weight: 500;">
                                <i class="fas fa-info-circle"></i> <strong>Development Mode:</strong> Your OTP is: <code style="background: white; padding: 4px 8px; border-radius: 4px; font-size: 1.1rem; font-weight: bold; color: #059669;"><?php echo $_SESSION['reset_otp']; ?></code>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label class="form-label required">Enter OTP (6 digits)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="text" name="otp" class="form-control" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autofocus>
                            </div>
                        </div>
                        
                        <p style="font-size: 0.85rem; color: var(--gray-600); margin-bottom: 16px;">
                            OTP expires in 5 minutes
                        </p>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-check-circle"></i>
                            Verify OTP
                        </button>
                        
                        <a href="forgot-password.php" class="btn btn-outline btn-block mt-2">
                            <i class="fas fa-arrow-left"></i> Request New OTP
                        </a>
                    </form>
                    <?php endif; ?>
                    
                    <!-- Step 3: Set New Password -->
                    <?php if ($step === 3): ?>
                    <form method="POST" action="" data-validate>
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="reset_password">
                        
                        <h3 style="margin-bottom: 16px; font-size: 1.1rem;">Step 3: Set New Password</h3>
                        <p style="color: var(--gray-600); margin-bottom: 16px; font-size: 0.9rem;">
                            Please enter a strong password for your account
                        </p>
                        
                        <div class="form-group">
                            <label class="form-label required">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" id="new-password" class="form-control" placeholder="Enter new password" required>
                                <button type="button" class="btn btn-ghost" data-toggle-password="new-password" style="border-radius: 0 var(--radius-lg) var(--radius-lg) 0;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small style="color: var(--gray-600);">Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="confirm_password" id="confirm-password" class="form-control" placeholder="Confirm new password" required>
                                <button type="button" class="btn btn-ghost" data-toggle-password="confirm-password" style="border-radius: 0 var(--radius-lg) var(--radius-lg) 0;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block mt-4">
                            <i class="fas fa-key"></i>
                            Reset Password
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <div style="text-align: center; padding-top: 16px; border-top: 1px solid var(--gray-200); margin-top: 16px;">
                        <a href="login.php" class="text-sm" style="color: var(--primary-600); text-decoration: none;">
                            <i class="fas fa-arrow-left"></i> Back to Login
                        </a>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 24px;">
                <p class="text-sm text-gray">
                    <a href="../index.php" style="color: var(--primary-600); text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </p>
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
        
        // Real-time password matching validation
        const confirmPassword = document.querySelector('input[name="confirm_password"]');
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                const newPass = document.querySelector('input[name="password"]');
                if (newPass && newPass.value && this.value) {
                    if (newPass.value !== this.value) {
                        this.style.borderColor = '#dc2626';
                    } else {
                        this.style.borderColor = '#10b981';
                    }
                }
            });
        }
    </script>
</body>
</html>
