<?php
/**
 * Unauthorized Access Page
 * Displayed when user lacks required permissions
 */

require_once '../includes/config.php';

// If logged in, this is a permission error. Otherwise, redirect to login
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . APP_URL . '/pages/login.php');
    exit;
}

$pageTitle = 'Access Denied';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="error-container">
            <h1>Access Denied</h1>
            <p>You do not have permission to access this page.</p>
            <p>Your account type is: <strong><?php echo htmlspecialchars($_SESSION['user_type']); ?></strong></p>
            <div class="action-buttons">
                <a href="<?php echo APP_URL; ?>/index.php" class="btn btn-primary">Go to Home</a>
                <a href="<?php echo APP_URL; ?>/pages/logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </div>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 600px;
        }
        
        .error-container {
            background: white;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .error-container h1 {
            color: #dc3545;
            font-size: 2em;
            margin-bottom: 20px;
        }
        
        .error-container p {
            color: #666;
            font-size: 1.1em;
            margin: 15px 0;
        }
        
        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }
    </style>
</body>
</html>
