# Cashfree Payment Gateway Integration Guide

## Overview
This document explains the Cashfree payment gateway integration for the Food-Saver application, enabling UPI-only donations.

## Features
- ✅ UPI transactions only
- ✅ Embedded payment form
- ✅ Real-time payment verification
- ✅ Webhook support for async notifications
- ✅ Session-based order tracking
- ✅ PCI-DSS compliant

## Setup Instructions

### 1. API Credentials
Your Cashfree credentials are configured in `includes/cashfree_config.php`:
- **App ID**: `cfsk_ma_test_f89d3bd994e08fdeb0183ab5370fd737_063b7269`
- **Secret Key**: `cfsk_ma_test_f89d3bd994e08fdeb0183ab5370fd737_063b7269`

**⚠️ IMPORTANT**: These are test credentials. For production, replace with production credentials from Cashfree dashboard.

### 2. Environment Configuration
The integration uses the **sandbox environment** by default for testing:
```php
define('CASHFREE_API_BASE_URL', 'https://sandbox.cashfree.com');
```

To switch to production, change to:
```php
define('CASHFREE_API_BASE_URL', 'https://api.cashfree.com');
```

## Payment Flow

### User Journey
```
1. User clicks "Donate" on homepage
   ↓
2. Guest? → Redirect to login/register
   Logged in? → Continue
   ↓
3. Redirect to donation page (pages/donate.php)
   ↓
4. Form submitted → Redirect to payment.php
   ↓
5. Payment form (pages/payment.php)
   - Select or enter amount (min ₹10)
   - Enter/confirm phone number
   - Add optional message
   ↓
6. Click "Proceed to Payment"
   - JavaScript creates order via API
   - API creates Cashfree order
   ↓
7. Redirected to Cashfree payment page
   - User completes UPI payment
   ↓
8. Redirected back to callback handler (includes/cashfree_callback.php)
   - Verify payment status
   - Create donation record
   - Show success message
```

## File Structure

### Core Integration Files
```
includes/
├── cashfree_config.php          # Configuration and API functions
├── cashfree_api.php              # REST API endpoints for frontend
├── cashfree_callback.php         # Payment redirect handler
└── cashfree_webhook.php          # Webhook receiver for async notifications

pages/
├── donate.php                   # Donation form (entry point)
├── payment.php                  # Embedded payment form
└── initiate_payment.php         # Redirect to payment form
```

## Configuration Options

### `includes/cashfree_config.php`

#### API Credentials
```php
define('CASHFREE_APP_ID', 'your_app_id');
define('CASHFREE_SECRET_KEY', 'your_secret_key');
```

#### Environment
```php
// Sandbox (testing)
define('CASHFREE_API_BASE_URL', 'https://sandbox.cashfree.com');

// Production
define('CASHFREE_API_BASE_URL', 'https://api.cashfree.com');
```

#### Payment Modes
```php
// Currently UPI only
define('CASHFREE_PAYMENT_MODES', ['upi']);

// To add more modes in future:
// define('CASHFREE_PAYMENT_MODES', ['upi', 'credit_card', 'debit_card']);
```

## Database Schema

### Donations Table
```sql
CREATE TABLE donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    ngo_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR',
    payment_method ENUM('upi', 'credit_card', 'debit_card', 'net_banking', 'online') DEFAULT 'online' NOT NULL,
    transaction_id VARCHAR(100),                    -- Cashfree order ID
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    message TEXT,
    anonymous BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (ngo_id) REFERENCES ngos(id) ON DELETE SET NULL
);
```

## API Endpoints

### 1. Create Order Endpoint
**URL**: `includes/cashfree_api.php?action=create_order`
**Method**: POST
**Parameters**:
- `csrf_token` (required) - CSRF token
- `amount` (required) - Donation amount (minimum ₹10)
- `phone` (optional) - 10-digit phone number
- `message` (optional) - Donation message

**Response**:
```json
{
    "success": true,
    "order_id": "FS1699564800000",
    "payment_session_id": "session_123456",
    "payment_link": "https://checkout.cashfree.com/pay/sessionid123"
}
```

### 2. Check Payment Status Endpoint
**URL**: `includes/cashfree_api.php?action=check_status`
**Method**: GET
**Parameters**:
- `order_id` (required) - Cashfree order ID

**Response**:
```json
{
    "success": true,
    "order_id": "FS1699564800000",
    "status": "PAID|PENDING|FAILED"
}
```

## Testing

### Test Credentials
- **Cashfree Environment**: Sandbox (Live UPI testing)
- **Test UPI IDs**: Use any active UPI ID for testing
- **Test Amount**: Use any amount ≥ ₹10

### Manual Testing Steps
1. Create a user account
2. Navigate to donation section
3. Click "Donate Now"
4. Fill in donation form (amount, phone, optional message)
5. Click "Proceed to Payment"
6. You'll be redirected to Cashfree payment page
7. Enter any active UPI ID and complete payment
8. You'll be redirected back and donation record will be created

### Test Payment IDs
For fully simulated testing on sandbox:
- **Success**: Use `success@upi` with any 6-digit OTP
- **Failure**: Use `failure@upi` to simulate failed payment

## Response Handling

### Success Flow
```php
// Cashfree redirects to callback
// Callback verifies payment status
// Creates donation record with status = 'completed'
// Redirects to user dashboard with success message
```

### Failure Flow
```php
// Cashfree redirects to callback  
// Callback detects failed payment
// Shows error message
// Redirects back to donation page
```

### Webhook (Async Notification)
```php
// Cashfree sends webhook notification
// Verifies signature and payment status
// Updates donation record if needed
// Returns 200 OK
```

## Security Considerations

### Current Implementation
- ✅ CSRF token verification on all POST requests
- ✅ Session-based order tracking
- ✅ HTTPS required for API endpoints
- ✅ Sanitized user inputs
- ✅ Secret key never exposed to frontend
- ✅ Payment verification via backend API

### Production Checklist
- [ ] Replace test credentials with production credentials
- [ ] Change API base URL to production
- [ ] Enable SSL/TLS for all endpoints
- [ ] Implement webhook signature verification
- [ ] Set up error logging and monitoring
- [ ] Configure return/callback URLs in Cashfree dashboard
- [ ] Test webhook notifications
- [ ] Implement rate limiting on API endpoints
- [ ] Regular security audits

### Webhook Security
Implement signature verification (currently basic):
```php
// In cashfree_webhook.php, add:
function verifyWebhookSignature($data, $signature) {
    $message = json_encode($data);
    $computedSignature = hash_hmac('sha256', $message, CASHFREE_SECRET_KEY);
    return hash_equals($signature, $computedSignature);
}
```

## Error Handling

### Common Errors

#### "Minimum donation amount is ₹10"
- Cause: User entered amount < ₹10
- Fix: Increase amount to ₹10 or more

#### "Invalid phone number format"
- Cause: Phone not exactly 10 digits
- Fix: Enter valid 10-digit phone number (can be left empty)

#### "Failed to create order"
- Cause: Cashfree API error or network issue
- Fix: Check Cashfree credentials, network connectivity, and API logs

#### "Payment verification failed"
- Cause: Session expired or order not found
- Fix: Start fresh donation process

#### "User not found"
- Cause: Backend error or corrupted session
- Fix: Logout and login again

## Troubleshooting

### Payment Not Processing
1. Check if credentials are correct in `cashfree_config.php`
2. Verify API base URL matches environment (sandbox vs production)
3. Check PHP logs for CURL errors
4. Verify network connectivity to Cashfree servers
5. Test with simple amounts first

### Callback Not Being Called
1. Ensure callback URL is correctly configured: `{APP_URL}/includes/cashfree_callback.php`
2. Check if payment actually completed in Cashfree dashboard
3. Verify session is not expired
4. Check server logs for errors in callback handler

### Webhook Not Receiving
1. Whitelist Cashfree webhook IP addresses
2. Verify webhook URL in Cashfree dashboard
3. Implement proper webhook logging to debug
4. Test using Cashfree's webhook testing tool

### Database Errors
1. Ensure donations table exists with correct schema
2. Check database connection in `config.php`
3. Verify user_id is valid in session

## Advanced Configuration

### Custom Return URLs
Modify in `cashfree_config.php`:
```php
'order_meta' => [
    'return_url' => 'https://yourdomain.com/custom-callback',
    'notify_url' => 'https://yourdomain.com/custom-webhook'
]
```

### Additional Logging
Add logging to track transactions:
```php
// In cashfree_callback.php
error_log("Payment received - Order: $orderId, Amount: $amount, Status: $status");
```

### Rate Limiting
Add to `cashfree_api.php`:
```php
// Implement rate limiting per user
$userUsage = apcu_fetch("payment_api_" . $_SESSION['user_id']);
if ($userUsage > 10) { // 10 requests per minute
    http_response_code(429);
    exit;
}
```

## Support & Resources

- **Cashfree Documentation**: https://docs.cashfree.com
- **Cashfree Dashboard**: https://sandbox.cashfree.com (sandbox) or https://dashboard.cashfree.com (production)
- **Webhook Testing**: Use Cashfree's webhook testing tool in dashboard
- **API Reference**: https://docs.cashfree.com/api/reference

## Migration from Test to Production

### Steps
1. Create production Cashfree account
2. Get production App ID and Secret Key
3. Update `cashfree_config.php`:
   ```php
   define('CASHFREE_APP_ID', 'YOUR_PRODUCTION_APP_ID');
   define('CASHFREE_SECRET_KEY', 'YOUR_PRODUCTION_SECRET_KEY');
   define('CASHFREE_API_BASE_URL', 'https://api.cashfree.com');
   ```
4. Configure return/callback URLs in Cashfree dashboard
5. Configure webhook URL in Cashfree dashboard
6. Test with real transactions
7. Monitor logs and error rates

## Version History
- **v1.0** (2024): Initial Cashfree integration with UPI support
- Support for embedded payment form
- Real-time payment verification
- Webhook support

---
**Last Updated**: 2024
**Maintained By**: Food-Saver Development Team
