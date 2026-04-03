# OTP Email Troubleshooting Guide

## Issue
Users are not receiving OTP (One-Time Password) emails during registration and password reset.

## Root Cause Found
The `sendEmail()` function call was **commented out** in [pages/register.php](pages/register.php#L261), preventing OTP emails from being sent to users during the registration process.

## Fix Applied
✅ **FIXED**: Uncommented and properly implemented the `sendEmail()` call in register.php with:
- Professional HTML email template
- Clear OTP display with styling
- 10-minute expiration notice
- Enhanced error feedback to users

## Verification Checklist

### 1. Email Configuration (config.php)
Check that SMTP credentials are correct:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'pcparthiv20@gmail.com');
define('SMTP_PASSWORD', 'waxm qzdl tyrn atlk'); // App password
define('FROM_EMAIL', 'pcparthiv20@gmail.com');
define('FROM_NAME', 'Food-Saver');
```

⚠️ **Important for Gmail**:
- If using Gmail, you MUST use an **App Password**, not your regular password
- Enable 2-factor authentication on your Google account
- Generate app password at: https://myaccount.google.com/apppasswords
- App password looks like: `xxxx xxxx xxxx xxxx` (16 characters with spaces)

### 2. Development Mode Setting
Check `DEVELOPMENT_MODE` in [includes/config.php](includes/config.php#L26):
```php
define('DEVELOPMENT_MODE', false); // Must be false for OTP email to be sent
```
- If `true`: OTP verification is skipped (development only)
- If `false`: OTP email will be sent (production mode - correct setting)

### 3. PHPMailer Installation
Verify PHPMailer files exist:
- ✓ `includes/PHPMailer-master/src/PHPMailer.php`
- ✓ `includes/PHPMailer-master/src/SMTP.php`
- ✓ `includes/PHPMailer-master/src/Exception.php`

### 4. Database Table
Verify `otp_verifications` table exists with proper schema:
```sql
CREATE TABLE otp_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    purpose VARCHAR(50), -- 'registration', 'password_reset'
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    INDEX (email)
);
```

### 5. Test Email Sending

Create a test file `includes/test_email.php`:
```php
<?php
require_once 'config.php';

$testEmail = 'your-email@example.com';
$testSubject = 'Test Email - Food-Saver';
$testBody = '<h1>Test Email</h1><p>If you see this, email sending works!</p>';

$result = sendEmail($testEmail, $testSubject, $testBody);

if ($result) {
    echo "✓ Email sent successfully to $testEmail";
} else {
    echo "✗ Email sending failed";
    echo "<br>Check your email credentials in config.php";
}
?>
```

Access it at: `http://localhost/food-saver-php/includes/test_email.php`

## Common Issues & Solutions

### Issue: "Failed to connect to server" or SMTP timeout
**Solution:**
- Verify Gmail app password is correct
- Check if 2FA is enabled on Gmail account
- Ensure SMTP_PORT is 587 (TLS) not 465 (SSL)
- Check firewall/hosting provider SMTP settings

### Issue: "Authentication failed"
**Solution:**
- Verify SMTP_USERNAME and SMTP_PASSWORD match exactly (case-sensitive)
- For Gmail, must use app password, not regular password
- Ensure no extra spaces in credentials

### Issue: "Recipient rejected"
**Solution:**
- Verify the recipient email is valid
- Check FROM_EMAIL is authorized to send (must be the Gmail account itself)
- Confirm no typos in email addresses

### Issue: Email sent but ends up in Spam
**Solution:**
- Add SPF and DKIM records for your domain
- Configure proper From/Reply-To headers
- Ask users to add you to safe senders list
- Use a professional email service (SendGrid, AWS SES, Mailgun) for better delivery

## Where OTP Email is Sent

1. **Registration**: [pages/register.php](pages/register.php) - Lines 256-300
   - Triggered when user submits registration form
   - OTP stored in: `otp_verifications` table
   - Purpose: 'registration'
   - Expires: 10 minutes

2. **Forgot Password**: [pages/forgot-password.php](pages/forgot-password.php) - Lines 60-105
   - Triggered when user requests password reset
   - OTP stored in: `otp_verifications` table
   - Purpose: 'password_reset'
   - Expires: 5 minutes

## Next Steps

1. ✓ Uncommented `sendEmail()` in register.php
2. Test registration on your local environment
3. Check email receives OTP within 2 minutes
4. If still not receiving, run the test email script above
5. Check XAMPP/server error logs for PHPMailer errors
6. Verify Gmail app password and 2FA settings

## Debugging

Enable error logging in [includes/config.php](includes/config.php#L7-L8):
```php
error_reporting(E_ALL);
ini_set('display_errors', 1); // Set to 1 for debugging, 0 for production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');
```

Check `error.log` file for detailed error messages from PHPMailer.

---

**Last Updated**: March 30, 2026  
**Fix Applied**: OTP email sending implemented in register.php
