<?php
require_once '../includes/config.php';

// Optional: capture type for messaging (user, ngo, restaurant)
$type = $_GET['type'] ?? '';

$titleMap = [
    'ngo' => 'NGO Account Suspended',
    'restaurant' => 'Restaurant Account Suspended',
    'user' => 'Donor Account Suspended',
];

$subtitleMap = [
    'ngo' => 'Your NGO profile has been suspended by the administrator.',
    'restaurant' => 'Your restaurant profile has been suspended by the administrator.',
    'user' => 'Your donor profile has been suspended by the administrator.',
];

$heading = $titleMap[$type] ?? 'Profile Suspended';
$subtitle = $subtitleMap[$type] ?? 'Your profile has been suspended by the administrator.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($heading); ?> - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .suspended-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-50), #ffffff, var(--secondary-50));
            padding: var(--space-lg);
        }
        .suspended-card {
            max-width: 640px;
            width: 100%;
            background: #ffffff;
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-xl);
            padding: var(--space-2xl);
            text-align: center;
        }
        .suspended-icon {
            width: 64px;
            height: 64px;
            border-radius: 999px;
            margin: 0 auto var(--space-md);
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(239, 68, 68, 0.1);
            color: #b91c1c;
            font-size: 1.75rem;
        }
        .suspended-title {
            font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--gray-900);
        }
        .suspended-subtitle {
            color: var(--gray-600);
            margin-bottom: 1.5rem;
        }
        .suspended-note {
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            color: var(--gray-700);
            font-size: 0.9rem;
            text-align: left;
        }
        .support-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
            margin-bottom: 0.75rem;
        }
        .support-meta {
            font-size: 0.85rem;
            color: var(--gray-500);
        }
        @media (max-width: 640px) {
            .suspended-card {
                padding: var(--space-xl);
            }
        }
    </style>
</head>
<body>
    <div class="suspended-page">
        <div class="suspended-card">
            <div class="suspended-icon">
                <i class="fas fa-user-slash"></i>
            </div>
            <h1 class="suspended-title"><?php echo htmlspecialchars($heading); ?></h1>
            <p class="suspended-subtitle">
                <?php echo htmlspecialchars($subtitle); ?>
            </p>

            <div class="suspended-note">
                <strong>Why am I seeing this?</strong>
                <p style="margin-top: 0.35rem;">
                    For security or policy reasons, your account access has been temporarily restricted.
                    You will not be able to access your dashboard or perform actions until this suspension is reviewed.
                </p>
            </div>

            <h2 style="font-size: 1rem; margin-bottom: 0.5rem;">
                Need help? <span style="color: var(--primary-600);">Contact Support</span>
            </h2>

            <div class="support-actions">
                <a href="<?php echo APP_URL; ?>/#contact" class="btn btn-primary">
                    <i class="fas fa-headset"></i> Contact Help &amp; Support
                </a>
                <a href="<?php echo APP_URL; ?>/pages/logout.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>

            <p class="support-meta">
                You can also reach us at
                <strong>info@foodsaver.com</strong> or call <strong>+91 1234567890</strong>.
            </p>
        </div>
    </div>
</body>
</html>

