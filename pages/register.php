<?php
/**
 * Food-Saver - Registration Page
 * Multi-role registration with OTP verification
 */

require_once '../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectBasedOnRole();
}

$error = '';
$success = '';
$step = 1; // 1: Registration form, 2: OTP verification

$userType = $_GET['type'] ?? 'user';
$validTypes = ['restaurant', 'ngo', 'user'];

if (!in_array($userType, $validTypes)) {
    $userType = 'user';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'verify_otp') {
            // Verify OTP
            $email = $_SESSION['otp_email'] ?? '';
            $otp = $_POST['otp'] ?? '';
            
            if (empty($email) || empty($otp)) {
                $error = 'Invalid verification request.';
                $step = 2;
            } else {
                $db = getDB();
                $stmt = $db->prepare("SELECT * FROM otp_verifications WHERE email = ? AND otp = ? AND purpose = 'registration' AND verified = FALSE AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([$email, $otp]);
                $otpRecord = $stmt->fetch();
                
                if ($otpRecord) {
                    // Mark OTP as verified
                    $db->prepare("UPDATE otp_verifications SET verified = TRUE WHERE id = ?")
                       ->execute([$otpRecord['id']]);
                    
                    // Complete registration
                    $userData = $_SESSION['pending_registration'] ?? [];
                    
                    if (!empty($userData)) {
                        $table = $userData['user_type'] . 's';
                        
                        try {
                            if ($userData['user_type'] === 'restaurant') {
                                $stmt = $db->prepare("INSERT INTO restaurants (username, email, password, restaurant_name, owner_name, phone, address, city, state, pincode, cuisine_type, license_number, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                $stmt->execute([
                                    $userData['username'],
                                    $userData['email'],
                                    $userData['password'],
                                    $userData['restaurant_name'],
                                    $userData['owner_name'],
                                    $userData['phone'],
                                    $userData['address'],
                                    $userData['city'],
                                    $userData['state'],
                                    $userData['pincode'],
                                    $userData['cuisine_type'] ?? null,
                                    $userData['license_number'] ?? null,
                                    $userData['description'] ?? null
                                ]);
                            } elseif ($userData['user_type'] === 'ngo') {
                                $stmt = $db->prepare("INSERT INTO ngos (username, email, password, ngo_name, registration_number, contact_person, phone, email_contact, address, city, state, pincode, description, service_areas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                $stmt->execute([
                                    $userData['username'],
                                    $userData['email'],
                                    $userData['password'],
                                    $userData['ngo_name'],
                                    $userData['registration_number'],
                                    $userData['contact_person'],
                                    $userData['phone'],
                                    $userData['email'],
                                    $userData['address'],
                                    $userData['city'],
                                    $userData['state'],
                                    $userData['pincode'],
                                    $userData['description'] ?? null,
                                    $userData['service_areas'] ?? null
                                ]);
                            } else {
                                $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, phone, address, city, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                $stmt->execute([
                                    $userData['username'],
                                    $userData['email'],
                                    $userData['password'],
                                    $userData['full_name'],
                                    $userData['phone'] ?? null,
                                    $userData['address'] ?? null,
                                    $userData['city'] ?? null,
                                    $userData['state'] ?? null
                                ]);
                            }
                            
                            // Clear session data
                            unset($_SESSION['pending_registration']);
                            unset($_SESSION['otp_email']);
                            
                            setFlashMessage('success', 'Registration successful! Please login to continue.');
                            header('Location: ' . APP_URL . '/pages/login.php?type=' . $userData['user_type']);
                            exit;
                            
                        } catch (PDOException $e) {
                            $error = 'Registration failed. Please try again.';
                            $step = 2;
                        }
                    }
                } else {
                    $error = 'Invalid or expired OTP. Please try again.';
                    $step = 2;
                }
            }
        } else {
            // Process registration form
            $email = sanitizeInput($_POST['email'] ?? '');
            $username = sanitizeInput($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validation
            if (empty($email) || empty($username) || empty($password)) {
                $error = 'Please fill in all required fields.';
            } elseif (!isValidEmail($email)) {
                $error = 'Please enter a valid email address.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters long.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } else {
                $db = getDB();
                
                // Check if email exists
                $tables = ['admins', 'restaurants', 'ngos', 'users'];
                $emailExists = false;
                
                foreach ($tables as $table) {
                    $stmt = $db->prepare("SELECT id FROM {$table} WHERE email = ? LIMIT 1");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $emailExists = true;
                        break;
                    }
                }
                
                if ($emailExists) {
                    $error = 'Email address is already registered.';
                } else {
                    // Generate OTP
                    $otp = generateOTP();

                    // Store OTP
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                    $stmt = $db->prepare("INSERT INTO otp_verifications (email, otp, purpose, expires_at) VALUES (?, ?, 'registration', ?)");
                    $stmt->execute([$email, $otp, $expiresAt]);

                    // Store registration data in session
                    $_SESSION['pending_registration'] = [
                        'user_type' => $userType,
                        'email' => $email,
                        'username' => $username,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'restaurant_name' => sanitizeInput($_POST['restaurant_name'] ?? ''),
                        'owner_name' => sanitizeInput($_POST['owner_name'] ?? ''),
                        'ngo_name' => sanitizeInput($_POST['ngo_name'] ?? ''),
                        'registration_number' => sanitizeInput($_POST['registration_number'] ?? ''),
                        'contact_person' => sanitizeInput($_POST['contact_person'] ?? ''),
                        'full_name' => sanitizeInput($_POST['full_name'] ?? ''),
                        'phone' => sanitizeInput($_POST['phone'] ?? ''),
                        'address' => sanitizeInput($_POST['address'] ?? ''),
                        'city' => sanitizeInput($_POST['city'] ?? ''),
                        'state' => sanitizeInput($_POST['state'] ?? ''),
                        'pincode' => sanitizeInput($_POST['pincode'] ?? ''),
                        'cuisine_type' => sanitizeInput($_POST['cuisine_type'] ?? ''),
                        'license_number' => sanitizeInput($_POST['license_number'] ?? ''),
                        'description' => sanitizeInput($_POST['description'] ?? ''),
                        'service_areas' => sanitizeInput($_POST['service_areas'] ?? '')
                    ];

                    $_SESSION['otp_email'] = $email;

                    // DEVELOPMENT MODE: Skip OTP verification
                    if (DEVELOPMENT_MODE) {
                        // Auto-verify and complete registration in development
                        $userData = $_SESSION['pending_registration'];

                        try {
                            if ($userData['user_type'] === 'restaurant') {
                                $stmt = $db->prepare("INSERT INTO restaurants (username, email, password, restaurant_name, owner_name, phone, address, city, state, pincode, cuisine_type, license_number, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                $stmt->execute([
                                    $userData['username'],
                                    $userData['email'],
                                    $userData['password'],
                                    $userData['restaurant_name'],
                                    $userData['owner_name'],
                                    $userData['phone'],
                                    $userData['address'],
                                    $userData['city'],
                                    $userData['state'],
                                    $userData['pincode'],
                                    $userData['cuisine_type'] ?? null,
                                    $userData['license_number'] ?? null,
                                    $userData['description'] ?? null
                                ]);
                            } elseif ($userData['user_type'] === 'ngo') {
                                $stmt = $db->prepare("INSERT INTO ngos (username, email, password, ngo_name, registration_number, contact_person, phone, email_contact, address, city, state, pincode, description, service_areas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                $stmt->execute([
                                    $userData['username'],
                                    $userData['email'],
                                    $userData['password'],
                                    $userData['ngo_name'],
                                    $userData['registration_number'],
                                    $userData['contact_person'],
                                    $userData['phone'],
                                    $userData['email'],
                                    $userData['address'],
                                    $userData['city'],
                                    $userData['state'],
                                    $userData['pincode'],
                                    $userData['description'] ?? null,
                                    $userData['service_areas'] ?? null
                                ]);
                            } else {
                                $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, phone, address, city, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                $stmt->execute([
                                    $userData['username'],
                                    $userData['email'],
                                    $userData['password'],
                                    $userData['full_name'],
                                    $userData['phone'] ?? null,
                                    $userData['address'] ?? null,
                                    $userData['city'] ?? null,
                                    $userData['state'] ?? null
                                ]);
                            }

                            // Clear session data
                            unset($_SESSION['pending_registration']);
                            unset($_SESSION['otp_email']);

                            setFlashMessage('success', '[Dev Mode] Registration successful! OTP verification skipped. Please login to continue.');
                            header('Location: ' . APP_URL . '/pages/login.php?type=' . $userData['user_type']);
                            exit;

                        } catch (PDOException $e) {
                            $error = 'Registration failed. Please try again. Error: ' . $e->getMessage();
                        }
                    } else {
                        // PRODUCTION MODE: Show OTP verification step
                        // Send OTP email with HTML template
                        $emailSubject = "Email Verification - Food-Saver";
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
                                    <h2>Welcome to Food-Saver!</h2>
                                    <p>Thank you for registering. Please use the OTP below to verify your email address. This OTP will expire in 10 minutes.</p>
                                    
                                    <div class='otp-box'>
                                        <p style='color: #6b7280; margin: 0 0 10px 0;'>Your OTP is:</p>
                                        <div class='otp-code'>$otp</div>
                                    </div>
                                    
                                    <p style='color: #dc2626; font-weight: bold;'>⚠️ Never share this OTP with anyone. We will never ask for it.</p>
                                    
                                    <p style='color: #6b7280; font-size: 14px;'>
                                        If you did not create this account, please ignore this email or contact support immediately.
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
                        
                        if ($emailSent) {
                            $success = "Registration OTP sent to your email. Please check your inbox and spam folder. Valid for 10 minutes.";
                        } else {
                            $success = "OTP generated. Check your email within 10 minutes.";
                        }
                        $step = 2;
                    }
                }
            }
        }
    }
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
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
            max-width: 600px;
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
        
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: var(--space-lg);
            margin-bottom: var(--space-xl);
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            color: var(--gray-400);
        }
        
        .step.active {
            color: var(--primary-600);
        }
        
        .step-number {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-full);
            background: var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .step.active .step-number {
            background: var(--primary-500);
            color: white;
        }
        
        .otp-inputs {
            display: flex;
            gap: var(--space-sm);
            justify-content: center;
        }
        
        .otp-inputs input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .role-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
        }
        
        .role-option {
            padding: var(--space-lg);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            text-align: center;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .role-option:hover {
            border-color: var(--primary-300);
        }
        
        .role-option.active {
            border-color: var(--primary-500);
            background: var(--primary-50);
        }
        
        .role-option i {
            font-size: 2rem;
            color: var(--gray-400);
            margin-bottom: var(--space-sm);
            display: block;
        }
        
        .role-option.active i {
            color: var(--primary-600);
        }
        
        .role-option h4 {
            font-size: 0.875rem;
            margin: 0;
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
            <div class="auth-card">
                <div class="auth-header">
                    <a href="../index.php" class="logo">
                        <div class="logo-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <span>Food-Saver</span>
                    </a>
                    <h2>Create Account</h2>
                    <p>Join our community to fight food waste</p>
                </div>
                
                <div class="auth-body">
                    <div class="step-indicator">
                        <div class="step <?php echo $step === 1 ? 'active' : ''; ?>">
                            <div class="step-number">1</div>
                            <span>Details</span>
                        </div>
                        <div class="step <?php echo $step === 2 ? 'active' : ''; ?>">
                            <div class="step-number">2</div>
                            <span>Verify</span>
                        </div>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error" data-auto-dismiss="30000">
                            <span><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></span>
                            <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success" data-auto-dismiss="30000">
                            <span><i class="fas fa-check-circle"></i> <?php echo $success; ?></span>
                            <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($step === 1): ?>
                        <!-- Registration Form -->
                        <form method="POST" action="" data-validate>
                            <?php echo csrfField(); ?>
                            
                            <div class="role-selector">
                                <a href="?type=restaurant" class="role-option <?php echo $userType === 'restaurant' ? 'active' : ''; ?>">
                                    <i class="fas fa-utensils"></i>
                                    <h4>Restaurant</h4>
                                </a>
                                <a href="?type=ngo" class="role-option <?php echo $userType === 'ngo' ? 'active' : ''; ?>">
                                    <i class="fas fa-hands-helping"></i>
                                    <h4>NGO</h4>
                                </a>
                                <a href="?type=user" class="role-option <?php echo $userType === 'user' ? 'active' : ''; ?>">
                                    <i class="fas fa-user"></i>
                                    <h4>Donor</h4>
                                </a>
                            </div>
                            
                            <input type="hidden" name="user_type" value="<?php echo $userType; ?>">
                            
                            <!-- Common Fields -->
                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label class="form-label required">Username</label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Email Address</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                            </div>
                            
                            <?php if ($userType === 'restaurant'): ?>
                                <!-- Restaurant Fields -->
                                <div class="form-group">
                                    <label class="form-label required">Restaurant Name</label>
                                    <input type="text" name="restaurant_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Owner Name</label>
                                    <input type="text" name="owner_name" class="form-control" required>
                                </div>
                                <div class="grid grid-2">
                                    <div class="form-group">
                                        <label class="form-label required">Phone</label>
                                        <input type="tel" name="phone" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Cuisine Type</label>
                                        <input type="text" name="cuisine_type" class="form-control" placeholder="e.g., Indian, Chinese">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">License Number</label>
                                    <input type="text" name="license_number" class="form-control">
                                </div>
                            <?php elseif ($userType === 'ngo'): ?>
                                <!-- NGO Fields -->
                                <div class="form-group">
                                    <label class="form-label required">NGO Name</label>
                                    <input type="text" name="ngo_name" class="form-control" required>
                                </div>
                                <div class="grid grid-2">
                                    <div class="form-group">
                                        <label class="form-label required">Registration Number</label>
                                        <input type="text" name="registration_number" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label required">Contact Person</label>
                                        <input type="text" name="contact_person" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Phone</label>
                                    <input type="tel" name="phone" class="form-control" required>
                                </div>
                            <?php else: ?>
                                <!-- User Fields -->
                                <div class="form-group">
                                    <label class="form-label required">Full Name</label>
                                    <input type="text" name="full_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="phone" class="form-control">
                                </div>
                            <?php endif; ?>
                            
                            <!-- Address Fields (for all) -->
                            <div class="form-group">
                                <label class="form-label <?php echo $userType !== 'user' ? 'required' : ''; ?>">Address</label>
                                <textarea name="address" class="form-control" rows="2" <?php echo $userType !== 'user' ? 'required' : ''; ?>></textarea>
                            </div>
                            
                            <div class="grid grid-3">
                                <div class="form-group">
                                    <label class="form-label <?php echo $userType !== 'user' ? 'required' : ''; ?>">City</label>
                                    <input type="text" name="city" class="form-control" <?php echo $userType !== 'user' ? 'required' : ''; ?>>
                                </div>
                                <div class="form-group">
                                    <label class="form-label <?php echo $userType !== 'user' ? 'required' : ''; ?>">State</label>
                                    <input type="text" name="state" class="form-control" <?php echo $userType !== 'user' ? 'required' : ''; ?>>
                                </div>
                                <div class="form-group">
                                    <label class="form-label <?php echo $userType !== 'user' ? 'required' : ''; ?>">Pincode</label>
                                    <input type="text" name="pincode" class="form-control" <?php echo $userType !== 'user' ? 'required' : ''; ?>>
                                </div>
                            </div>
                            
                            <!-- Password Fields -->
                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label class="form-label required">Password</label>
                                    <input type="password" name="password" class="form-control" minlength="8" required>
                                    <p class="form-text">Minimum 8 characters</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Confirm Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="terms" class="form-check-input" required>
                                    <span class="form-check-label">
                                        I agree to the <a href="#" target="_blank">Terms of Service</a> and 
                                        <a href="#" target="_blank">Privacy Policy</a>
                                    </span>
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-user-plus"></i>
                                Create Account
                            </button>
                        </form>
                        
                        <!-- Social Login Divider -->
                        <div class="oauth-divider">
                            <span>Or sign up with</span>
                        </div>
                        
                        <!-- Social Login Buttons -->
                        <div class="oauth-buttons">
                            <a href="#" class="oauth-btn oauth-google" data-provider="google" data-type="<?php echo $userType; ?>" title="Sign up with Google">
                                <svg class="oauth-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                <span>Google</span>
                            </a>
                            
                            <a href="#" class="oauth-btn oauth-facebook" data-provider="facebook" data-type="<?php echo $userType; ?>" title="Sign up with Facebook">
                                <svg class="oauth-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="currentColor" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                                <span>Facebook</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- OTP Verification Form -->
                        <form method="POST" action="">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="verify_otp">
                            
                            <div class="text-center mb-4">
                                <div class="mb-4">
                                    <i class="fas fa-envelope-open-text text-primary" style="font-size: 4rem;"></i>
                                </div>
                                <h3>Verify Your Email</h3>
                                <p class="text-gray">
                                    We've sent a 6-digit OTP to<br>
                                    <strong><?php echo htmlspecialchars($_SESSION['otp_email'] ?? ''); ?></strong>
                                </p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Enter OTP</label>
                                <input type="text" name="otp" class="form-control" maxlength="6" placeholder="Enter 6-digit OTP" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-check-circle"></i>
                                Verify & Complete Registration
                            </button>
                            
                            <div class="text-center mt-4">
                                <p class="text-gray">Didn't receive the code?</p>
                                <a href="register.php?type=<?php echo $userType; ?>" class="btn btn-ghost">
                                    <i class="fas fa-redo"></i> Resend OTP
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <div class="auth-footer">
                        <p class="text-gray">Already have an account? <a href="login.php?type=<?php echo $userType; ?>" class="text-primary">Sign in</a></p>
                    </div>
                </div>
            </div>
            
            <p class="text-center mt-4 text-gray text-sm">
                <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </p>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/micro-interactions.js"></script>
    <script>
        // OAuth button handlers for registration
        function setupOAuthButtons() {
            const oauthBtns = document.querySelectorAll('.oauth-btn[data-provider]');
            
            oauthBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const provider = this.dataset.provider;
                    const userType = this.dataset.type;
                    redirectToOAuth(provider, userType);
                });
            });
        }
        
        function redirectToOAuth(provider, userType) {
            // Redirect to OAuth start handler
            const url = window.location.origin + window.location.pathname.split('/pages')[0] + '/pages/oauth_start.php?provider=' + encodeURIComponent(provider) + '&type=' + encodeURIComponent(userType);
            window.location.href = url;
        }
        
        // Initialize OAuth buttons
        setupOAuthButtons();
    </script>
</body>
</html>
