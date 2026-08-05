# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.5.8] - 2026-08-05
### Changed
- **Repository Structure:** Moved the shippable plugin files into a dedicated `gnn-shortner/` subfolder, separating plugin code from development/meta directories. No user-facing changes — the packaged plugin contents are identical.
- **Release Workflow:** Simplified the release build to zip the `gnn-shortner/` folder directly instead of rsync-copying with exclude rules.

## [1.5.7] - 2026-08-02
### Changed
- **Admin Menu Registration:** Updated the top-level admin menu position to `'79.102'` to conform with the GNN Product Family Admin Menu Position Registry. This ensures a predictable placement within the WordPress admin dashboard across all GNN products.


## [1.5.6] - 2026-07-15
### Fixed
- **Redirect Reliability:** Short links now resolve correctly even when tracking query strings are appended (e.g. `?utm_source=…`, `?fbclid=…`). The redirect handler now parses only the URL path via `wp_parse_url()` instead of the full request URI.
- **Duplicate Slug Race Condition:** `gnn_save_url()` now returns the insert result, and `gnn_process_shortner()` reports an error if the insert fails (e.g. a concurrent request claimed the same custom slug), preventing a success response that points to another user's destination.
- **Password Entropy:** Removed `sanitize_text_field()` from the frontend password on both save and verification. The password is only hashed/compared and never output, so stripping characters needlessly reduced the effective password space. Existing passwords containing stripped characters may need to be re-saved.
- **Rate Limiting:** Replaced the sliding-window transient limiter with a shared fixed-window helper (`gnn_check_rate_limit()`), so the 60-second window can no longer be dragged indefinitely by sustained requests.
- **Secure Cookie Behind Proxy:** The unlock cookie's `Secure` flag now also honors `X-Forwarded-Proto: https`, so it is set correctly behind TLS-terminating reverse proxies where `is_ssl()` returns false.
- **Input Consistency:** Added missing `wp_unslash()` before `esc_url_raw()` on the destination URL in `gnn_process_shortner()`.

### Added
- **Data Management Setting:** New opt-in "Delete all data on uninstall" option. When unchecked (default), short links are preserved in the database after uninstall; when checked, the table and settings are removed. Cleanup is now handled by a proper `uninstall.php` (replacing the previous non-functional uninstall hook).

## [1.5.5] - 2026-07-15
### Added
- **Frontend Password Protection:** Added secure access control to the frontend URL shortener form, allowing administrators to lock form access with a hashed password stored in the database.
- **Conditional reCAPTCHA Flow:** Optimized reCAPTCHA so it is only displayed and verified during the password login phase. Once logged in, the widget is hidden.
- **Bypass Prevention:** Integrated backend unlock checks directly into `gnn_process_shortner()` using HttpOnly session cookies to prevent password screen bypass.

### Fixed
- **AJAX and Caching Reliability:**
  - Implemented `DONOTCACHEPAGE` and `nocache_headers()` on the shortcode page to prevent caching eklentisi collisions.
  - Standardized the unlock cookie path to root (`/`) to prevent cross-path submission failures in `admin-ajax.php`.
  - Fixed syntax error in javascript AJAX success handler causing CodeQL compilation issues.

## [1.5.3] - 2026-07-08
### Fixed
- **Sentinel Audit Compliance Hardening:**
  - Migrated standard translation `_e()` functions on the frontend form to safe, HTML-escaped `esc_html_e()` functions.
  - Implemented late-escaping logic on `$theme_class` frontend container output to comply with WPCS best practices.

## [1.5.2] - 2026-07-06
### Added
- **IP-Based AJAX Rate Limiting:** Added a transient-based rate limiter to prevent URL shortening spam requests (max 10 submissions per 60 seconds).
- **Admin Warning Notice:** Added a dashboard warning notice when Google reCAPTCHA keys are missing or incomplete.

### Fixed
- **Security Audit Hardening:**
  - Resolved potential open redirects and XSS vectors by enforcing strict HTTP/HTTPS scheme whitelisting on redirected URLs and sanitizing/escaping all output data.
  - Added custom slug whitelist and reserved keyword check (e.g. `wp-admin`, `wp-login`) to prevent routing interference.
  - Implemented pagination for admin URL management tables to prevent performance issues with large datasets.
  - Restricted asset enqueuing so scripts/styles only load on their relevant admin pages.

## [1.5.1] - 2026-07-05
### Fixed
- **Dynamic reCAPTCHA Theme Mode:** Configured the reCAPTCHA widget to dynamically match the active design theme (light or dark mode) instead of rendering in forced light theme, ensuring visual consistency inside dark wrappers.

## [1.5.0] - 2026-07-05
### Added
- **Sleek Minimalist SaaS Frontend Styling:** Replaced the legacy Glassmorphism design on the frontend with a clean, high-performance SaaS aesthetic (Linear/Vercel inspired). Features thin slate borders, soft micro drop shadows, spacious layouts, and clean slate monochrome focus fields.
- **Frontend Theme Mode Customization:** Added a new theme settings controller in the WordPress admin panel. Site administrators can now set the public frontend form to **Auto** (follows user system/theme preference), **Force Light Mode**, or **Force Dark Mode**.

## [1.4.3] - 2026-07-05
### Added
- **Premium Frontend UI Modernization:** Re-engineered the public-facing URL shortener form with GNN Premium Glassmorphism styling, including background radial glows, backdrop blur, glowing focus borders, premium gradients, and smooth hover state transitions.
- **Frontend Toast Notifications:** Added dynamic styled toast alerts on the public form for AJAX status errors and copy-to-clipboard actions.

## [1.4.2] - 2026-07-05
### Fixed
- **Inline Display None Override:** Removed hardcoded inline `style="display:none;"` from edit and delete modal overlay wrappers in `functions/admin.php`, allowing the class-based transition styles (`.gnn-active`) to correctly toggle visibility.

## [1.4.1] - 2026-07-05
### Fixed
- **Modal Display Override:** Resolved a CSS specificity issue where `display: flex !important` caused confirmation modals to display permanently on page load and settings page. Replaced jQuery animation timers with a pure CSS-driven transition class (`.gnn-active`).

## [1.4.0] - 2026-07-05
### Added
- **Modern Admin UI:** Re-architected admin panel with summary stats cards, high-fidelity responsive URL table, and smooth overlay modals.
- **Interactive Modals:** Edit and delete confirmation modals with backdrop blur, fade/slide animations, keyboard escape hook, and click-outside dismissal.
- **Toast Notifications:** Dynamic, non-blocking toast notifications replacing generic browser alerts.
### Fixed
- **Security Hardening:** Enforced ABSPATH direct-access guards on all core files and implemented strict privilege checks (`current_user_can('manage_options')`) on all settings page functions and admin AJAX endpoints.
- **Input Sanitization:** Secured reCAPTCHA lookup client IP parameters via proper escaping wrappers.

## [1.3.0] - 2026-04-26
### Added
- **Native Stability CSS:** Completely rewritten CSS based on working theme reference (800px width, standardized spacing).
- **Full-Width Shorten Button:** Button now matches input fields for a symmetrical and professional look.
- **Admin CSS Scoping:** All admin styles are now scoped to `.gnn-admin-container` to prevent global UI collisions.
### Changed
- Refactored CSS into `frontend.css` and `admin.css` for better performance and separation of concerns.
- Optimized reCAPTCHA scaling for mobile devices.
### Fixed
- Resolved vertical gap issues when shortcodes were placed inside `pre` blocks.
- Fixed global admin table style collisions with WordPress core.

## [1.2.0] - 2026-04-26
### Added
- **Premium UI v2.0:** Completely redesigned frontend with advanced Glassmorphism effects.
- **Button Loading State:** Added "Creating..." state to shortening button for better UX.
- **Action Links:** Added "Donate", "Settings", and "Check Updates" links to the WordPress plugin list.
- **GitHub Updater:** Fully integrated GitHub updater in the main plugin file.
### Changed
- Standardized CSS to use theme variables and font inheritance.
- Improved mobile responsiveness for the shortcode form.

## [1.1.0] - 2026-04-26
### Changed
- Standardized documentation and project structure.
- Updated Memory Bank for better AI context persistence.
- Refactored updater and project identity.

## [1.0.5] - 2026-04-20
### Added
- Integrated Google reCAPTCHA v2 on the frontend shortening form.
- Added reCAPTCHA settings to the admin panel.

## [1.0.0] - 2026-04-01
### Added
- Initial release of GNN Shortner.
- Core URL shortening functionality.
- Shortcode `[gnn_shortner]`.
- Admin management page.
