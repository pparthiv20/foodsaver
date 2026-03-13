```php
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

    // Admin email
    $adminEmail = 'pcparthiv20@gmail.com';
    $emailSubject = 'New contact form submission - FoodSaver';

    $emailBody = '
    <div style="background-color:#f3f4f6;padding:24px 0;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center">
                    <table width="100%" style="max-width:600px;background:#ffffff;border-radius:24px;box-shadow:0 10px 40px rgba(15,23,42,0.16);overflow:hidden;">

                        <tr>
                            <td style="padding:20px 32px;background:linear-gradient(135deg,#16a34a,#22c55e);color:#ffffff;">
                                <h2 style="margin:0;">🌱 FoodSaver</h2>
                                <p style="margin:5px 0 0;font-size:14px;">New contact request from your website</p>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:24px 32px;">
                                <h3 style="margin-top:0;color:#111827;">New Contact Form Submission</h3>
                                <p style="font-size:14px;color:#6b7280;">
                                Someone filled out the contact form on your Food-Saver website.
                                </p>

                                <table width="100%" style="font-size:14px;color:#111827;">
                                    <tr>
                                        <td style="font-weight:600;width:120px;">Name</td>
                                        <td>' . htmlspecialchars($name) . '</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:600;">Email</td>
                                        <td>
                                            <a href="mailto:' . htmlspecialchars($email) . '" style="color:#16a34a;">
                                                ' . htmlspecialchars($email) . '
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:600;">Phone</td>
                                        <td>' . (!empty($phone) ? htmlspecialchars($phone) : 'N/A') . '</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:600;">Subject</td>
                                        <td>' . htmlspecialchars($subject) . '</td>
                                    </tr>
                                </table>

                                <div style="margin-top:20px;font-weight:600;">Message</div>

                                <div style="margin-top:6px;padding:12px;border-radius:10px;background:#f9fafb;border:1px solid #e5e7eb;">
                                    ' . nl2br(htmlspecialchars($message)) . '
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:16px 32px;font-size:12px;color:#9ca3af;border-top:1px solid #e5e7eb;">
                                This person contacted you through the FoodSaver website.
                                You can reply directly to this email to respond.
                            </td>
                        </tr>

                    </table>

                    <div style="margin-top:12px;font-size:11px;color:#9ca3af;">
                        &copy; ' . date('Y') . ' FoodSaver. All rights reserved.
                    </div>

                </td>
            </tr>
        </table>
    </div>
    ';

    // Send email
    @sendEmail($adminEmail, $emailSubject, $emailBody);

    setFlashMessage('success', 'contact_thanks');

} catch (PDOException $e) {

    setFlashMessage('error', 'Failed to send message. Please try again later.');

}

header('Location: ../index.php#contact');
exit;
?>
```
