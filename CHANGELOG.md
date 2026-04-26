# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.5] - 2026-04-26
### Changed
- Standardized documentation and project structure.
- Updated Memory Bank for better AI context persistence.
- Refined redirection logic for better performance.

## [1.0.4] - 2026-04-20
### Added
- Integrated Google reCAPTCHA v2 on the frontend shortening form.
- Added reCAPTCHA settings to the admin panel.

## [1.0.3] - 2026-04-15
### Fixed
- Fixed slug collision issue where duplicate slugs could be created.
- Improved CSS responsiveness for mobile devices.

## [1.0.2] - 2026-04-10
### Added
- Introduced "GNN Premium" Glassmorphism UI for the shortcode form.
- Added nonce verification for AJAX shortening requests.

## [1.0.1] - 2026-04-05
### Changed
- Migrated redirection logic to `template_redirect` for better compatibility.
- Optimized database indexing for faster URL lookup.

## [1.0.0] - 2026-04-01
### Added
- Initial release of GNN Shortner.
- Core URL shortening functionality.
- Shortcode `[gnn_shortner]`.
- Admin management page.
