/**
 * ========================================
 * MOBILE RESPONSIVE GUIDE
 * Food-Saver Mobile Optimization
 * ========================================
 *
 * This document explains the mobile-responsive CSS architecture
 * and how it optimizes the application for mobile devices.
 */

/**
 * OVERVIEW
 * ========================================
 *
 * The mobile-responsive.css file provides comprehensive styling
 * for mobile devices (320px - 768px) without affecting desktop/laptop views.
 *
 * All desktop styles are preserved.
 * Mobile devices get optimized sizing, spacing, and touch targets.
 */

/**
 * DEVICE BREAKPOINTS
 * ========================================
 */

/* Mobile First Approach */
@media (max-width: 768px)  /* Tablet & Mobile */
@media (max-width: 480px)  /* Small Phones */
@media (min-width: 481px) and (max-width: 568px)  /* Large Phones */
@media (min-width: 569px) and (max-width: 768px)  /* Tablets */
@media (max-height: 500px) and (orientation: landscape)  /* Landscape Mode */
@media (hover: none) and (pointer: coarse)  /* Touch Devices */

/**
 * WHAT'S OPTIMIZED FOR MOBILE
 * ========================================
 */

✓ TYPOGRAPHY
  - h1: 1.75rem on mobile
  - h2: 1.5rem on mobile
  - h3: 1.25rem on mobile
  - h4: 1.1rem on mobile
  - p: 0.95rem on mobile
  - small: 0.8rem on mobile

  ✓ Desktop remains unchanged (no override)

✓ BUTTONS
  - Min height: 44px (touch-friendly)
  - Min width: 44px (touch-friendly)
  - Font size: 0.95rem
  - Full width on mobile (btn-block)
  - Reduced padding for compact layouts
  - Active state: scale(0.98) for touch feedback

✓ FORMS
  - Input height: 44px (touch-friendly)
  - Font size: 16px (prevents zoom on focus)
  - Padding: 0.7rem 1rem
  - Full width on mobile
  - Focus states optimized (no transform on mobile)

✓ SPACING & PADDING
  - Container padding: 0 1rem (mobile), 0 0.75rem (small phones)
  - Card padding: 1.5rem 1rem (mobile), 1.25rem 0.85rem (small phones)
  - Form groups: 1.25rem margin-bottom
  - Gap between elements: 1rem (mobile), 0.75rem (small phones)

✓ NAVIGATION
  - Mobile menu height: 60px
  - Logo size: 1.25rem (mobile)
  - User badge: 0.85rem font size
  - Responsive dropdown menu

✓ HERO SECTION
  - Padding: 2rem 0 1.5rem 0 (mobile)
  - H1 font size: 1.75rem
  - Text centered
  - Emoji size reduced

✓ STAT CARDS
  - Single column layout (grid-template-columns: 1fr)
  - Padding: 1.5rem 1rem
  - Stat value: 1.75rem
  - Gap between cards: 1rem

✓ DONATION CARDS
  - Full width (100%)
  - Padding: 1.25rem (card-body)
  - Preset buttons: 2-column layout
  - Donate button: 48px height

✓ PARTNER CARDS
  - Single column layout
  - Centered alignment
  - Full-width buttons
  - Emoji: 2.5rem

✓ TABLES
  - Responsive text: 0.85rem
  - Compact padding: 0.75rem
  - May scroll horizontally on small screens

✓ MODALS
  - Width: 95% of screen
  - Max-width: 100%
  - Padding: 1rem

/**
 * DESKTOP BEHAVIOR (NOT AFFECTED)
 * ========================================
 */

✗ Desktop displays (769px+) are NOT affected
✗ All desktop styles remain unchanged
✗ Media queries only target mobile: (max-width: 768px)
✗ No CSS is applied to desktop when viewing on laptop/desktop

Example:
  .container on desktop: padding: 0 var(--space-lg)
  .container on mobile: padding: 0 1rem
  (Desktop remains unchanged)

/**
 * TOUCH DEVICE OPTIMIZATIONS
 * ========================================
 */

@media (hover: none) and (pointer: coarse)
  - Hover effects disabled on touch devices
  - Active/press effects enabled
  - Larger touch targets (44px minimum)
  - Removed visual hover states

Benefits:
  ✓ No "ghost" hover effects on mobile
  ✓ Responsive to actual touch
  ✓ Comfortable tap targets for fat fingers
  ✓ No unnecessary animations on touch

/**
 * RESPONSIVE GRID SYSTEM
 * ========================================
 */

Desktop: grid-template-columns: repeat(4, 1fr)  → grid-4
Tablet:  grid-template-columns: repeat(2, 1fr)  → grid-2 or grid-3
Mobile:  grid-template-columns: 1fr              → Single column

Examples:
  .grid-4 on laptop: 4 columns
  .grid-4 on tablet: 2 columns
  .grid-4 on mobile: 1 column

  .grid-3 on laptop: 3 columns
  .grid-3 on tablet: 2 columns
  .grid-3 on mobile: 1 column

  .grid-2 on laptop: 2 columns
  .grid-2 on tablet: 2 columns
  .grid-2 on mobile: 1 column

/**
 * LANDSCAPE MODE OPTIMIZATION
 * ========================================
 */

@media (max-height: 500px) and (orientation: landscape)
  - Reduced padding and margins
  - Smaller font sizes
  - Compact spacing
  - Optimal for small screen height

Scenarios:
  ✓ Mobile in landscape mode
  ✓ Small tablets in landscape
  ✓ Maintains readability

/**
 * BREAKPOINT DETAILS
 * ========================================
 */

/* Standard Mobile (320px - 768px) */
  base mobile styling
  applies to all phones and tablets

/* Small Phones (320px - 480px) */
  additional size reductions
  smaller spacing
  more compact layout

  Examples:
    h1: 1.5rem
    Container padding: 0 0.75rem
    Stat value: 1.5rem
    Button: 40px height

/* Large Phones (481px - 568px) */
  adjusted font sizes
  preset buttons: 2-column
  hero h1: 1.6rem

/* Tablets (569px - 768px) */
  allows 2-column layouts
  larger font sizes
  grid-4 becomes grid-2
  grid-3 stays 2 columns

/**
 * FONT SIZE OPTIMIZATION
 * ========================================
 */

Principle: Larger fonts on small screens for readability

Desktop:    h1 = 2.5rem, h2 = 2rem, p = 1rem
Mobile:     h1 = 1.75rem, h2 = 1.5rem, p = 0.95rem
Sm Phone:   h1 = 1.5rem, h2 = 1.3rem, p = 0.9rem

Input:      16px on mobile (prevents auto-zoom)
Button:     0.95rem on mobile, 0.9rem on small phones

Note:
  ✓ 16px font size prevents iOS auto-zoom on input focus
  ✓ All fonts readable on small screens
  ✓ Desktop fonts untouched

/**
 * SPACING & PADDING GUIDE
 * ========================================
 */

Desktop Container:  padding: 0 var(--space-lg) = 0 2rem
Mobile Container:   padding: 0 1rem
Sm Phone:           padding: 0 0.75rem

Desktop Card Padding:       2rem
Mobile Card Padding:        1.5rem 1rem
Small Phone Card Padding:   1.25rem 0.85rem

Desktop Gap (grid):  1.5rem
Mobile Gap (grid):   1rem
Sm Phone Gap:        0.75rem

Result: Consistent whitespace proportional to screen size

/**
 * TESTING CHECKLIST
 * ========================================
 */

Mobile Testing (max-width: 768px)
  [ ] Typography sizes correct
  [ ] Button heights: 44px+ for touch
  [ ] Form inputs: 44px+ height
  [ ] Spacing proportional
  [ ] Single column layouts on mobile
  [ ] Full-width buttons
  [ ] Cards have proper padding

Tablet Testing (569px - 768px)
  [ ] Layout switches to 2-column
  [ ] Preset buttons in 4 columns
  [ ] Readable font sizes
  [ ] Proper grid adjustments

Small Phone Testing (max-width: 480px)
  [ ] Extra spacing reductions applied
  [ ] Compact layout
  [ ] All text readable
  [ ] Buttons still tappable

Landscape Testing (mobile landscape)
  [ ] Reduced padding applied
  [ ] Compact spacing
  [ ] Scrollable if needed

Desktop Testing (min-width: 769px)
  [ ] NO mobile styles applied
  [ ] Desktop styles unchanged
  [ ] Original layouts 100% same
  [ ] No visual regression

Touch Device Testing
  [ ] Hover effects disabled
  [ ] Active states work
  [ ] Touch feedback clear
  [ ] No ghost hovers

/**
 * CSS LOAD ORDER
 * ========================================
 */

All dashboard files include CSS in this order:
  1. style.css              (base, desktop-first)
  2. dashboards.css         (extensions)
  3. user-dashboard.css     (user-specific, dashboards only)
  4. micro-interactions.css (animations)
  5. mobile-responsive.css  (mobile overrides)

This ensures:
  ✓ Mobile overrides desktop (correct cascade)
  ✓ Desktop styles preserved
  ✓ Mobile gets last say
  ✓ No conflicts

/**
 * COMMON MOBILE ISSUES FIXED
 * ========================================
 */

Issue 1: Text too small on mobile
  Solution: Font sizes scaled down proportionally
  Files: mobile-responsive.css

Issue 2: Buttons too small to tap
  Solution: Min height/width 44px on mobile
  Standard: Web Content Accessibility Guidelines

Issue 3: Inputs cause zoom on iOS
  Solution: Font size 16px on mobile inputs
  Result: No unwanted zoom on focus

Issue 4: Form too wide on mobile
  Solution: Full width inputs and buttons
  Result: Comfortable to use on small screens

Issue 5: Cards cramped on mobile
  Solution: Reduced padding, single column
  Result: Better readability

Issue 6: Navigation cluttered on mobile
  Solution: Mobile menu button, hidden nav-links
  Result: Clean, navigable interface

Issue 7: Hover effects confusing on touch
  Solution: Touch device queries disable hovers
  Result: Clear touch feedback instead

Issue 8: Landscape mode partially cut off
  Solution: Landscape-specific media query
  Result: Usable in landscape orientation

/**
 * PERFORMANCE IMPACT
 * ========================================
 */

File Size: ~16 KB (minimal impact)
Gzip: ~2-3 KB (excellent compression)
Load Time: <100ms additional (negligible)

Benefit: Massively improved mobile experience
Trade-off: Tiny file size increase (worth it!)

/**
 * BEST PRACTICES APPLIED
 * ========================================
 */

✓ Mobile-first media queries (max-width)
✓ Progressive enhancement (base → mobile → tablet)
✓ Touch-friendly targets (44px WCAG AA standard)
✓ Readable font sizes (16px minimum)
✓ Proper contrast ratios
✓ Flexible layouts (single → multi-column)
✓ Optimized spacing (responsive gutters)
✓ Touch feedback (active states)
✓ No hover on touch devices
✓ Landscape support
✓ Accessibility support
✓ Performance optimized

/**
 * FUTURE ENHANCEMENTS
 * ======================================== */

Potential additions:
  - Dark mode media query support
  - Print styles optimization
  - High DPI device optimization
  - PWA-specific styling
  - Custom properties for easier maintenance

/**
 * FILE REFERENCE
 * ========================================
 */

File: assets/css/mobile-responsive.css
Size: ~16 KB
Lines: 800+
Coverage: All components
Breakpoints: 320px - 768px+

Included in:
  ✓ dashboards/user.php
  ✓ dashboards/admin.php
  ✓ dashboards/ngo.php
  ✓ dashboards/restaurant.php
  ✓ pages/login.php
  ✓ pages/register.php
  ✓ index.php
  ✓ All other pages

/**
 * QUICK REFERENCE
 * ========================================
 */

Mobile Breakpoints:
  320px - 480px   = Small Phones (extra optimizations)
  481px - 568px   = Large Phones (moderate adjustments)
  569px - 768px   = Tablets (2-column layouts)
  769px+          = Desktop (NO mobile styles applied)

Key Changes:
  - All font sizes reduced by 10-20%
  - Spacing reduced by 15-25%
  - Buttons: 44px minimum height
  - Forms: 16px font (no zoom)
  - Grids: 1 column on mobile
  - Padding: Responsive reduction

Result: Beautiful, responsive interface on any device! 📱💻
