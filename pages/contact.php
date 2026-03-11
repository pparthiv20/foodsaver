<?php
/**
 * Food-Saver - Contact Form Handler
 */

require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php#contact');
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Invalid request. Please try again.');
    header('Location: ../index.php#contact');
    exit;
}

$name = sanitizeInput($_POST['name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$subject = sanitizeInput($_POST['subject'] ?? '');
$message = sanitizeInput($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    setFlashMessage('error', 'Please fill in all required fields.');
    header('Location: ../index.php#contact');
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $subject, $message]);
    
    setFlashMessage('success', 'Thank you for contacting us! We will get back to you soon.');
} catch (PDOException $e) {
    setFlashMessage('error', 'Failed to send message. Please try again later.');
}

header('Location: ../index.php#contact');
exit;
?>
