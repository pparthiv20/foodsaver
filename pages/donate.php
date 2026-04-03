<?php
/**
 * Food-Saver - Simple Donation Handler
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
$paymentMethod = $_POST['payment_method'] ?? 'online';
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

// Process donation for logged in users - simple direct approach
try {
    $db = getDB();
    $transactionId = 'DONATION' . time() . rand(10000, 99999);
    
    // Create donation record
    $stmt = $db->prepare("
        INSERT INTO donations (user_id, amount, payment_method, message, status, transaction_id)
        VALUES (?, ?, ?, ?, 'completed', ?)
    ");
    
    $stmt->execute([$_SESSION['user_id'], $amount, $paymentMethod, $message, $transactionId]);
    
    // Log activity
    logActivity($_SESSION['user_id'], $_SESSION['user_type'], 'make_donation', 'Donated ₹' . number_format($amount, 2));
    
    // Set session variables for thank you
    $_SESSION['show_donation_thank_you'] = true;
    $_SESSION['donation_amount'] = $amount;
    $_SESSION['donation_transaction_id'] = $transactionId;
    
    setFlashMessage('success', '✓ Thank you for your donation of ₹' . number_format($amount, 2) . '! Transaction ID: ' . $transactionId);
    
    // Redirect back to home to show thank you modal
    header('Location: ../index.php');
    exit;
    
} catch (PDOException $e) {
    error_log("Donation Error: " . $e->getMessage());
    setFlashMessage('error', 'Failed to process donation. Please try again.');
    header('Location: ../index.php#donate');
    exit;
}
?>
?>
