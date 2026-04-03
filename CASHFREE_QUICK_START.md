# Cashfree Payment Integration - Quick Start Guide

## What Was Integrated?

✅ **Cashfree Payment Gateway** with **UPI-only transactions**

## Key Files Created/Modified

### New Files (Core Integration)
```
includes/
  ├── cashfree_config.php      - Cashfree API configuration & core functions
  ├── cashfree_api.php         - REST API endpoints for payment operations
  ├── cashfree_callback.php    - Handles payment completion redirects
  └── cashfree_webhook.php     - Receives async payment notifications

pages/
  ├── payment.php              - NEW: Embedded payment form with validation
  ├── initiate_payment.php     - Redirects to payment form
  └── donate.php               - MODIFIED: Now redirects to payment form

Documentation/
  └── CASHFREE_INTEGRATION.md  - Complete integration documentation
  └── CASHFREE_QUICK_START.md  - This file
```

## How to Use

### For Users (Donor Perspective)
```
1. Click "Donate Now" button on homepage
2. If not logged in → Redirect to login/register
3. Fill donation form:
   - Select preset amount (₹50, ₹100, ₹500, etc.) or enter custom
   - Phone number (optional)
   - Message (optional)
4. Click "Proceed to Payment"
5. Enter UPI ID and complete payment
6. Automatic redirect to success page
```

### For Developers

#### Initialize Payment
```php
require_once 'includes/cashfree_config.php';

// Create order
$orderData = createCashfreeOrder(
    $amount = 100,
    $user_id = 1,
    $email = 'user@example.com',
    $phone = '9876543210',
    $message = 'Support message'
);

// Redirect to payment
header('Location: ' . $orderData['payment_link']);
```

#### Verify Payment
```php
$paymentStatus = getCashfreePaymentStatus($orderId);

if ($paymentStatus === 'PAID') {
    // Process successful payment
} else if ($paymentStatus === 'FAILED') {
    // Handle failed payment
}
```

## Configuration

### Step 1: Update Credentials
Edit `includes/cashfree_config.php`:
```php
// Line 3-4: Your Cashfree credentials
define('CASHFREE_APP_ID', 'cfsk_ma_test_...');
define('CASHFREE_SECRET_KEY', 'cfsk_ma_test_...');
```

### Step 2: Choose Environment
Edit `includes/cashfree_config.php` Line 6:
```php
// For Testing (Sandbox)
define('CASHFREE_API_BASE_URL', 'https://sandbox.cashfree.com');

// For Production
define('CASHFREE_API_BASE_URL', 'https://api.cashfree.com');
```

### Step 3: Configure Callback URLs
In Cashfree Dashboard → Settings:
- **Return URL**: `https://yourdomain.com/includes/cashfree_callback.php`
- **Webhook URL**: `https://yourdomain.com/includes/cashfree_webhook.php`

## Testing

### Test UPI IDs (Sandbox)
- Any active UPI ID for real testing
- Or use `success@upi` for simulated success
- Or use `failure@upi` for simulated failure

### Test Flow
```
Create Account → Donate Page → Payment Form → Cashfree Payment → Success
```

## Payment Status Values
- **PAID** - Payment successful, donation recorded
- **PENDING** - Payment awaiting user action
- **FAILED** - Transaction failed, try again
- **CANCELLED** - User cancelled payment

## Main Features

| Feature | Status | Notes |
|---------|--------|-------|
| UPI Payments | ✅ | Primary payment method |
| Embedded Form | ✅ | User stays on site |
| Real-time Verification | ✅ | Instant status check |
| Webhook Support | ✅ | Async notifications |
| Error Handling | ✅ | Comprehensive |
| Security | ✅ | CSRF, HTTPS, Sanitization |
| Mobile Responsive | ✅ | Works on all devices |

## Payment Flow Diagram

```
User ──→ Donation Page ──→ Login Check
                             │
                          Logged In?
                             │
                    ┌─────────┴─────────┐
                    │                   │
                   No                  Yes
                    │                   │
                 Redirect            Payment Form
                to Login                 │
                    │                    │
                    └─────────┬─────────┘
                              │
                         Form Validate
                              │
                    Create Cashfree Order
                              │
                  Redirect to Cashfree ↔ User Completes UPI Pay
                              │
                       Cashfree Redirects
                              │
                       Verify Payment Status
                              │
                         ┌────┴────┐
                         │          │
                       PAID      FAILED
                         │          │
                      Create    Show Error
                    Donation   Redirect
                      Record
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "Failed to create order" | Check API credentials and network connectivity |
| "Payment verification failed" | Session may have expired, start fresh |
| "Invalid phone format" | Use exactly 10 digits or leave empty |
| "Minimum amount" | Use ₹10 or more |
| "User not found" | Logout/login and try again |

## Next Steps

1. ✅ Map credentials to test environment
2. ✅ Test complete donation flow
3. ✅ Verify database records creation
4. ✅ Check webhook reception (optional but recommended)
5. ✅ Configure production credentials when ready
6. ✅ Monitor transactions and error logs

## Important Notes

⚠️ **Test Credentials Included** - Replace with production before going live
⚠️ **Sandbox Environment** - Currently set to sandbox for testing
⚠️ **HTTPS Required** - Need SSL/TLS in production
⚠️ **Webhook Optional** - Currently basic, implement signature verification in production

## API Reference

### Functions Available in cashfree_config.php

```php
// Create new order
createCashfreeOrder($amount, $user_id, $email, $phone, $message)

// Verify payment
verifyCashfreePayment($orderId)

// Get payment status
getCashfreePaymentStatus($orderId)

// Generate unique order ID
generateCashfreeOrderId()

// Get app URL
getAppUrl()
```

## Support Files

- 📖 Full Documentation: `CASHFREE_INTEGRATION.md`
- 🚀 This Quick Start: `CASHFREE_QUICK_START.md`
- 📊 Implementation Files: See "New Files" section above

---
**Ready to Test?** Navigate to `/pages/donate.php` or click "Donate Now" on the homepage!
