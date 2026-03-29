<?php
/**
 * OAuth Callback Handler
 * Processes OAuth provider responses and authenticates users
 */

require_once '../includes/config.php';
require_once '../includes/oauth_handler.php';

// Security checks
if (!isset($_GET['code']) || !isset($_GET['state'])) {
    die('Missing authorization code or state parameter');
}

if (!isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die('State mismatch - potential CSRF attack');
}

$provider = $_GET['provider'] ?? null;
$code = $_GET['code'];
$error = $_GET['error'] ?? null;

if (!in_array($provider, ['google', 'facebook'])) {
    die('Invalid OAuth provider');
}

if ($error) {
    $error_description = $_GET['error_description'] ?? 'Authorization failed';
    setFlashMessage('error', 'OAuth authentication failed: ' . htmlspecialchars($error_description));
    header('Location: ' . APP_URL . '/pages/login.php');
    exit;
}

// Get access token
$tokenResponse = OAuthHandler::getAccessToken($code, $provider);

if (isset($tokenResponse['error'])) {
    setFlashMessage('error', 'Failed to get access token: ' . htmlspecialchars($tokenResponse['error']));
    header('Location: ' . APP_URL . '/pages/login.php');
    exit;
}

$accessToken = $tokenResponse['access_token'] ?? null;

if (!$accessToken) {
    setFlashMessage('error', 'No access token received from provider');
    header('Location: ' . APP_URL . '/pages/login.php');
    exit;
}

// Get user profile
$profile = OAuthHandler::getUserProfile($accessToken, $provider);

if (isset($profile['error'])) {
    setFlashMessage('error', 'Failed to get user profile: ' . htmlspecialchars($profile['error']));
    header('Location: ' . APP_URL . '/pages/login.php');
    exit;
}

// Get user type from session or default to 'user'
$userType = $_SESSION['oauth_user_type'] ?? 'user';

// Find or create user
$result = OAuthHandler::findOrCreateUser($profile, $provider, $userType);

if (isset($result['error'])) {
    setFlashMessage('error', $result['error']);
    header('Location: ' . APP_URL . '/pages/login.php?type=' . $userType);
    exit;
}

if (!$result['success']) {
    setFlashMessage('error', 'Failed to authenticate with ' . ucfirst($provider));
    header('Location: ' . APP_URL . '/pages/login.php?type=' . $userType);
    exit;
}

// Set session and log in
$_SESSION['user_id'] = $result['user_id'];
$_SESSION['user_type'] = $result['user_type'];
$_SESSION['user_email'] = $result['user_email'];
$_SESSION['user_name'] = $result['user_name'];

// Update last login
$db = getDB();
$tableNameForUserType = getTableNameForUserType($result['user_type']);
$db->prepare("UPDATE {$tableNameForUserType} SET last_login = NOW() WHERE id = ?")
   ->execute([$result['user_id']]);

// Log activity
logActivity($result['user_id'], $result['user_type'], 'oauth_login', 'User logged in via ' . ucfirst($provider));

// Clear OAuth session data
unset($_SESSION['oauth_state']);
unset($_SESSION['oauth_user_type']);

// Determine redirect
if ($result['is_new']) {
    setFlashMessage('success', 'Account created and logged in successfully via ' . ucfirst($provider) . '!');
} else {
    setFlashMessage('success', 'Logged in successfully via ' . ucfirst($provider) . '!');
}

$redirect = $_SESSION['redirect_url'] ?? null;
unset($_SESSION['redirect_url']);

if ($redirect) {
    header('Location: ' . $redirect);
} else {
    redirectBasedOnRole();
}

exit;
?>
