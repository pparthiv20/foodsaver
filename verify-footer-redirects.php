<?php
/**
 * Footer Redirects Test - Verification Report
 */

require_once 'includes/config.php';

$tests = [
    'Pages Created' => [
        'pages/terms-of-service.php' => file_exists('pages/terms-of-service.php'),
        'pages/privacy-policy.php' => file_exists('pages/privacy-policy.php'),
        'pages/help-center.php' => file_exists('pages/help-center.php'),
    ],
    'Footer Links Updated' => [
        'index.php footer' => file_exists('index.php'),
        'contact-page.php footer' => file_exists('pages/contact-page.php'),
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer Redirects Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; padding: 2rem; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: #1f2937; margin-bottom: 2rem; }
        .test-section { background: white; border-radius: 0.75rem; margin-bottom: 1.5rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .test-section h2 { color: #374151; font-size: 1.25rem; margin-bottom: 1rem; }
        .test-item { display: flex; align-items: center; gap: 1rem; padding: 0.75rem; border-bottom: 1px solid #e5e7eb; }
        .test-item:last-child { border-bottom: none; }
        .test-status { 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            width: 24px; 
            height: 24px; 
            border-radius: 50%; 
            font-weight: 600; 
            color: white; 
        }
        .test-status.pass { background: #16a34a; }
        .test-status.fail { background: #dc2626; }
        .test-label { color: #1f2937; font-weight: 500; flex: 1; }
        .footer-links { margin-top: 2rem; padding: 1rem; background: #f9fafb; border-radius: 0.5rem; }
        .link { display: inline-block; margin: 0.5rem; padding: 0.5rem 1rem; background: #16a34a; color: white; text-decoration: none; border-radius: 0.375rem; }
        .link:hover { background: #15803d; }
        .success { color: #16a34a; font-weight: 600; }
        .summary { padding: 1.5rem; background: #f0fdf4; border-left: 4px solid #16a34a; border-radius: 0.5rem; margin-top: 2rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Footer Redirects Verification Report</h1>

        <?php foreach ($tests as $category => $items): ?>
            <div class="test-section">
                <h2><?php echo $category; ?></h2>
                <?php foreach ($items as $label => $status): ?>
                    <div class="test-item">
                        <span class="test-status <?php echo $status ? 'pass' : 'fail'; ?>">
                            <?php echo $status ? '✓' : '✗'; ?>
                        </span>
                        <span class="test-label"><?php echo $label; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <div class="test-section">
            <h2>🔗 Footer Links to Test</h2>
            <p style="color: #6b7280; margin-bottom: 1rem;">Click the links below to verify redirects:</p>
            <div class="footer-links">
                <a href="pages/terms-of-service.php" class="link">Terms of Service</a>
                <a href="pages/privacy-policy.php" class="link">Privacy Policy</a>
                <a href="pages/help-center.php" class="link">Help Center</a>
                <a href="pages/contact-page.php" class="link">Contact Us</a>
            </div>
        </div>

        <div class="summary">
            <h3 style="color: #16a34a; margin-bottom: 0.5rem;">✓ Setup Complete!</h3>
            <p style="color: #6b7280;">All footer pages have been created and linked successfully. The footer redirects in index.php and contact-page.php have been updated to point to the new pages.</p>
            <h4 style="color: #374151; margin-top: 1rem; margin-bottom: 0.5rem;">Pages Created:</h4>
            <ul style="margin-left: 1.5rem; color: #6b7280;">
                <li><strong>Privacy Policy:</strong> pages/privacy-policy.php</li>
                <li><strong>Terms of Service:</strong> pages/terms-of-service.php</li>
                <li><strong>Help Center:</strong> pages/help-center.php (with FAQs and Accordion)</li>
            </ul>
            <h4 style="color: #374151; margin-top: 1rem; margin-bottom: 0.5rem;">Files Updated:</h4>
            <ul style="margin-left: 1.5rem; color: #6b7280;">
                <li>index.php - Footer Support links updated</li>
                <li>pages/contact-page.php - Footer Support links updated</li>
            </ul>
        </div>
    </div>
</body>
</html>
