<?php
/**
 * Food-Saver - Logout
 * Secure logout functionality
 */

require_once '../includes/config.php';

// Log the logout activity
if (isLoggedIn()) {
    logActivity($_SESSION['user_id'], $_SESSION['user_type'], 'logout', 'User logged out');
}

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', array(
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ));
}

// Destroy the session
session_destroy();

// Set success message
setFlashMessage('success', 'You have been logged out successfully.');

// Redirect to login page
header('Location: login.php');
exit;
?>
