<?php
/**
 * OAuth Start Handler
 * Initiates OAuth flow by redirecting to OAuth provider
 */

require_once '../includes/config.php';
require_once '../includes/oauth_handler.php';

// Get provider and user type from query string
$provider = $_GET['provider'] ?? null;
$userType = $_GET['type'] ?? 'user';

// Validate inputs
if (!in_array($provider, ['google', 'facebook'])) {
    die('Invalid OAuth provider');
}

if (!in_array($userType, ['user', 'restaurant', 'ngo', 'admin'])) {
    $userType = 'user';
}

// Generate authorization URL
$authURL = OAuthHandler::getAuthURL($provider, $userType);

if (!$authURL) {
    die('Failed to generate authorization URL');
}

// Redirect to OAuth provider
header('Location: ' . $authURL);
exit;
?>
