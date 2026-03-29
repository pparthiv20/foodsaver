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
                    
                    // Send OTP email (placeholder)
                    // sendEmail($email, 'Verify Your Email - Food-Saver', "Your OTP is: {$otp}");
                    
                    // For demo, show OTP in success message
                    $success = "OTP sent to your email. For demo, use OTP: {$otp}";
                    $step = 2;
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
                        <div class="alert alert-error" data-auto-dismiss="5000">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success" data-auto-dismiss="10000">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $success; ?>
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
</body>
</html>
