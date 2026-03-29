/**
 * ========================================
 * CSS ARCHITECTURE & LOAD ORDER
 * Food-Saver Dashboard System
 * ========================================
 *
 * This document defines the CSS structure to prevent conflicts and collisions.
 * Follow this order STRICTLY in all HTML files.
 */

/**
 * LOAD ORDER (DO NOT CHANGE):
 * ========================================
 * 1. style.css               - Base styles (buttons, cards, forms)
 * 2. dashboards.css          - Extensions only (hover states, utilities)
 * 3. user-dashboard.css      - User dashboard specific (if needed)
 * 4. micro-interactions.css  - Animations only
 */

/**
 * FILE RESPONSIBILITIES
 * ========================================
 */

/* style.css - BASE STYLES (DO NOT DUPLICATE) */
/*
  Contains:
  - Colors & variables
  - Typography
  - Base button styles (.btn, .btn-primary, .btn-secondary, etc)
  - Base card styles (.card, .card-header, .card-body, .card-footer)
  - Base form styles (.form-group, .form-label, .form-control)
  - Navigation
  - Layout grid system
  - Responsive utilities

  DO NOT REDEFINE in other files.
*/

/* dashboards.css - EXTENSIONS ONLY */
/*
  Contains:
  - Hover state enhancements for .card
  - Hover state enhancements for .btn
  - Error state styles for .form-control
  - New utility classes (.badge, .action-btn, .grid-2, etc)
  - Specific dashboard enhancements
  - Stat cards (.stat-card)
  - Table styles (.data-table)

  NEVER redefine .btn, .card, .form-control base styles.
  ONLY add / extend.
*/

/* user-dashboard.css - USER DASHBOARD SPECIFIC */
/*
  Contains:
  - User dashboard hero section
  - User stats section styling
  - User dashboard layout specific styles
  - User donation card styling
  - User partner cards styling

  Should NOT conflict with base .btn, .card, .form-control.
  Use specific selectors like .donation-card-centered, .partner-card, etc.
*/

/* micro-interactions.css - ANIMATIONS ONLY */
/*
  Contains:
  - Keyframe animations (@keyframes)
  - Animation application via .class and :hover
  - Transition timing
  - Transform effects

  Should NOT duplicate .btn, .card, .form-control styles.
*/

/**
 * SPECIFICITY RULES
 * ========================================
 */

/*
  1. Base styles (style.css): Single class selectors
     Example: .btn { ... }

  2. Extensions (dashboards.css): Same specificity, no overrides
     Example: .btn:hover { ... }  [NEW HOVER STATE]

  3. Special cases (user-dashboard.css): More specific selectors
     Example: .donation-card .button { ... }

  4. Animations: Applied via class addition, not selector override
     Example: .card { animation: fadeInScale 600ms; }
*/

/**
 * NAMING CONVENTIONS
 * ========================================
 */

/*
  Global utilities from style.css:
  - .btn, .btn-primary, .btn-secondary, .btn-outline, .btn-sm, .btn-lg
  - .card, .card-header, .card-body, .card-footer
  - .form-group, .form-label, .form-control
  - .grid, .grid-2, .grid-3, .grid-4
  - .flex, .gap-*, .mb-*

  Dashboard extensions from dashboards.css:
  - .stat-card, .stat-icon, .stat-value, .stat-label
  - .data-table, .badge, .action-btn

  User dashboard specific from user-dashboard.css:
  - .donation-card-centered, .donation-section-header
  - .partner-card, .partner-header, .partner-body
  - .section-header

  User stats from user-dashboard.css:
  - .stat-card-user, .stat-value.primary, .stat-value.success
*/

/**
 * CSS CONFLICT CHECKLIST
 * ========================================
 */

/*
  ✓ style.css defines base .btn styles
  ✗ dashboards.css does NOT redefine .btn
  ✓ dashboards.css only adds .btn:hover enhancements

  ✓ style.css defines base .card styles
  ✗ dashboards.css does NOT redefine .card
  ✓ dashboards.css only adds .card:hover enhancements

  ✓ style.css defines base .form-control styles
  ✗ dashboards.css does NOT redefine .form-control
  ✓ dashboards.css only adds .form-control.error states

  ✓ user-dashboard.css uses specific selectors (.donation-card-centered)
  ✗ user-dashboard.css does NOT override base .btn, .card, .form-control

  ✓ micro-interactions.css defines animations
  ✗ micro-interactions.css does NOT redefine layouts or base styles
*/

/**
 * CORRECT LINK ORDER IN HTML
 * ========================================
 */

/*
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/dashboards.css">
  <link rel="stylesheet" href="../assets/css/user-dashboard.css">
  <link rel="stylesheet" href="../assets/css/micro-interactions.css">

  NEVER change this order.
  NEVER skip any file if it's needed.
  NEVER add styles to wrong files.
*/

/**
 * DEBUGGING CSS CONFLICTS
 * ========================================
 */

/*
  To find conflicts:
  1. Open browser DevTools (F12)
  2. Inspect element
  3. Check "Styles" panel
  4. Look for strikethrough (overridden) styles
  5. Check source file in blue link
  6. If conflict found:
     - Check load order
     - Check specificity (use fewer classes)
     - Move styles to appropriate file
*/
