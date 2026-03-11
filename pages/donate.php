<?php
/**
 * Food-Saver - Donation Handler
 */

require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php#donate');
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Invalid request. Please try again.');
    header('Location: ../index.php#donate');
    exit;
}

$amount = floatval($_POST['amount'] ?? 0);
$paymentMethod = $_POST['payment_method'] ?? 'upi';
$message = sanitizeInput($_POST['message'] ?? '');

if ($amount < 10) {
    setFlashMessage('error', 'Minimum donation amount is ₹10.');
    header('Location: ../index.php#donate');
    exit;
}

// For guests (not logged in), store in session and redirect to register/login
if (!isLoggedIn()) {
    $_SESSION['pending_donation'] = [
        'amount' => $amount,
        'payment_method' => $paymentMethod,
        'message' => $message
    ];
    setFlashMessage('info', 'Please login or register to complete your donation.');
    header('Location: register.php?type=user');
    exit;
}

// Process donation for logged in users
try {
    $db = getDB();
    $transactionId = 'TXN' . time() . rand(1000, 9999);
    
    $stmt = $db->prepare("
        INSERT INTO donations (user_id, amount, payment_method, message, status, transaction_id)
        VALUES (?, ?, ?, ?, 'completed', ?)
    ");
    
    $stmt->execute([$_SESSION['user_id'], $amount, $paymentMethod, $message, $transactionId]);
    
    logActivity($_SESSION['user_id'], $_SESSION['user_type'], 'make_donation', 'Donated ₹' . $amount);
    
    setFlashMessage('success', 'Thank you for your donation of ₹' . number_format($amount, 2) . '! Your transaction ID is ' . $transactionId);
    
    // Redirect based on user type
    if ($_SESSION['user_type'] === 'user') {
        header('Location: ../dashboards/user.php?page=donations');
    } else {
        header('Location: ../index.php#donate');
    }
    exit;
    
} catch (PDOException $e) {
    setFlashMessage('error', 'Failed to process donation. Please try again.');
    header('Location: ../index.php#donate');
    exit;
}
?>
