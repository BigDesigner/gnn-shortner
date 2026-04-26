# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
