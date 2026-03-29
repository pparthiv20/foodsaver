/**
 * ========================================
 * DEVELOPMENT MODE GUIDE
 * Food-Saver OTP Configuration
 * ========================================
 *
 * This guide explains how to use Development Mode to skip OTP verification
 * during local development and testing.
 */

/**
 * WHAT IS DEVELOPMENT MODE?
 * ========================================
 * Development Mode allows you to skip OTP (One-Time Password) verification
 * during the registration and password reset flows, making it faster to test
 * the application without waiting for or entering OTP codes.
 */

/**
 * HOW TO ENABLE DEVELOPMENT MODE
 * ========================================
 *
 * File: includes/config.php
 * Line: ~26 (after APP_NAME and APP_TAGLINE)
 *
 * Change this:
 *   define('DEVELOPMENT_MODE', false);
 *
 * To this:
 *   define('DEVELOPMENT_MODE', true);
 *
 * ⚠️ IMPORTANT: Always set to false in PRODUCTION
 */

/**
 * AFFECTED FEATURES
 * ========================================
 */

/* 1. REGISTRATION PAGE (pages/register.php) */
/*
   When DEVELOPMENT_MODE = true:
   ✓ User fills registration form
   ✓ System skips OTP email sending
   ✓ System skips OTP verification step
   ✓ User is immediately registered and logged in
   ✓ Quick redirect to respective dashboard

   When DEVELOPMENT_MODE = false:
   ✓ User fills registration form
   ✓ System generates and stores OTP
   ✓ OTP is sent via email (or shown in demo)
   ✓ User must enter OTP to verify email
   ✓ Then user is registered and logged in
*/

/* 2. PASSWORD RESET PAGE (pages/forgot-password.php) */
/*
   When DEVELOPMENT_MODE = true:
   ✓ User enters email to reset password
   ✓ System skips OTP email sending
   ✓ System skips OTP verification step (Step 2)
   ✓ User is immediately taken to "Enter New Password" form (Step 3)
   ✓ User sets new password and completes reset

   When DEVELOPMENT_MODE = false:
   ✓ User enters email to reset password
   ✓ System generates and sends OTP via email
   ✓ User enters OTP to verify identity
   ✓ System verifies OTP
   ✓ User then sets new password
*/

/**
 * TESTING CHECKLIST
 * ========================================
 */

/* With DEVELOPMENT_MODE = true */
[✓] Registration Form
    [ ] User Registration (without OTP step)
    [ ] Restaurant Registration (without OTP step)
    [ ] NGO Registration (without OTP step)

[✓] Password Reset
    [ ] Forgot Password Form (without OTP step)
    [ ] Password Change Process
    [ ] Redirect to login page

[✓] Data Validation
    [ ] All form validations still work
    [ ] Error messages display correctly
    [ ] Email duplicate checks still work
    [ ] Password mismatch errors still work


/* With DEVELOPMENT_MODE = false */
[✓] Registration with OTP
    [ ] User Registration Form
    [ ] OTP Generation & Storage
    [ ] OTP Display (in demo mode)
    [ ] OTP Verification Page
    [ ] OTP Validation
    [ ] Successful Registration

[✓] Password Reset with OTP
    [ ] Email Entry Form
    [ ] OTP Generation & Storage
    [ ] OTP Verification Page
    [ ] OTP Validation
    [ ] Password Reset Form
    [ ] Password Update


/**
 * CONFIGURATION CODE
 * ========================================
 */

// In includes/config.php
define('DEVELOPMENT_MODE', false); // Change to true for development

// Usage in register.php:
if (DEVELOPMENT_MODE) {
    // Auto-complete registration without OTP
    // Register user directly in database
} else {
    // Standard flow: Generate OTP -> Show OTP verification form
}

// Usage in forgot-password.php:
if (DEVELOPMENT_MODE) {
    // Skip to "Enter New Password" form (Step 3)
    $_SESSION['reset_step'] = 3;
} else {
    // Show OTP verification form (Step 2)
    $_SESSION['reset_step'] = 2;
}

/**
 * DATABASE IMPACT
 * ========================================
 *
 * When DEVELOPMENT_MODE = true:
 * - OTP entries are still created in otp_verifications table
 * - But they are skipped in the verification flow
 * - User records are created directly in users/restaurants/ngos tables
 *
 * When DEVELOPMENT_MODE = false:
 * - OTP entries are created in otp_verifications table
 * - Must be verified before user records are created
 * - Complete flow is enforced
 */

/**
 * ENVIRONMENT-SPECIFIC SETUP
 * ========================================
 */

/* LOCAL DEVELOPMENT (Your Machine) */
define('DEVELOPMENT_MODE', true);
// Faster testing, skip OTP steps

/* STAGING SERVER (Testing) */
define('DEVELOPMENT_MODE', false);
// Test full OTP flow with real email sending

/* PRODUCTION SERVER (Live) */
define('DEVELOPMENT_MODE', false);
// Must enforce OTP for security


/**
 * TROUBLESHOOTING
 * ========================================
 */

✗ Problem: Registration page still shows OTP form
  Solution: Verify DEVELOPMENT_MODE is set to true in config.php
  Location: includes/config.php line ~26

✗ Problem: Users can't register even in dev mode
  Solution: Check for PHP errors in browser console or server logs
  Verify: All sessions are working correctly

✗ Problem: Password reset not working in dev mode
  Solution: Check that reset email is being found in database
  Verify: Session variables are being set correctly

✗ Problem: OTP verification still required
  Solution: Clear browser cache and cookies
  Try: New incognito/private browser window


/**
 * SECURITY NOTES
 * ========================================
 */

⚠️ DEVELOPMENT MODE SECURITY:
   - NEVER enable in production
   - Only for local development
   - Bypasses email verification security layer
   - Users can register without verified emails

✓ PRODUCTION SECURITY:
   - Always set DEVELOPMENT_MODE = false
   - Users MUST verify email via OTP
   - Prevents spam registrations
   - Ensures valid email addresses
   - Prevents unauthorized account access

/**
 * SESSION VARIABLES
 * ========================================
 */

/* During Registration */
$_SESSION['pending_registration']  // Stores all registration data
$_SESSION['otp_email']             // Stores email for OTP verification

/* During Password Reset */
$_SESSION['reset_step']             // Current step (1, 2, or 3)
$_SESSION['reset_email']            // Email requesting password reset
$_SESSION['reset_type']             // User type (user, restaurant, ngo)
$_SESSION['reset_otp']              // Generated OTP (for testing)
$_SESSION['reset_otp_expiry']       // OTP expiration time


/**
 * QUICK START
 * ========================================
 */

1. Open includes/config.php
2. Find line with: define('DEVELOPMENT_MODE', false);
3. Change to: define('DEVELOPMENT_MODE', true);
4. Save file
5. Reload your application
6. Registration and password reset will now skip OTP verification

To go back to production mode:
7. Change back to: define('DEVELOPMENT_MODE', false);
8. Save file
9. Reload application


/**
 * FINAL CHECKLIST
 * ========================================
 */

Before committing to production:
[ ] DEVELOPMENT_MODE is set to false
[ ] OTP email sending is configured
[ ] Database has otp_verifications table
[ ] Email service is properly configured
[ ] All user types (user, restaurant, ngo) tested
[ ] Password reset flow tested
[ ] OTP expiration is set correctly (5-10 minutes)
[ ] Error messages are user-friendly
[ ] Security validations are in place
