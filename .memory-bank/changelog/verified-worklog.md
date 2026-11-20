# Verified Worklog

This log tracks all historically completed work, verified sprints, and milestones for GNN Shortner.

## Completed Milestones

### Foundation & Infrastructure (MB)
- **MB-001:** Initialized project structure as a WordPress Plugin.
- **MB-002:** Configured GitHub Actions automation (`actions/checkout@v7`, `softprops/action-gh-release@v3`, `php -l` gate) for zipped releases.
- **MB-003:** Separated shippable plugin files into `gnn-shortner/`, added root `VERSION` file, and established `languages/gnn-shortner.pot`.

### Security & Hardening (SEC)
- **SEC-001:** Nonce verification implemented on AJAX operations and form submissions.
- **SEC-002:** Google reCAPTCHA v2 integrated on the public URL shortener form with `is_wp_error()` handling and masked secret key storage in admin settings.
- **SEC-003:** File security guard `defined('ABSPATH') || exit;` applied at the beginning of all PHP scripts (`WP_UNINSTALL_PLUGIN` in `uninstall.php`).
- **SEC-004:** Forced SQL statement preparation using `$wpdb->prepare()` (including `%i` table identifiers for WP 6.2+) for all custom table actions.
- **SEC-005:** Prevented DOM XSS via `document.createElement('code')` + `.textContent`, hardened `hash_equals()` and password handlers with `is_string()` guards against PHP 8+ `TypeError`, and added `SameSite=Lax` cookie attributes.
- **SEC-006:** Enforced HTTPS GitHub host allowlisting (`get_package_url()`) in `GNN_Shortner_Updater` and `rel="noopener noreferrer"` on external `target="_blank"` links.

### Performance & Optimization (PERF)
- **PERF-001:** Added index coverage on the database table for faster lookup speeds.
- **PERF-002:** Native URL interception and redirection implemented via `template_redirect` with subfolder and query-string support.
- **PERF-003:** Removed jQuery dependency (`gnn-shortner.js` rewritten in Vanilla JS with `fetch()`) and restricted frontend/reCAPTCHA asset loading to pages using `[gnn_shortner]`.

### Design & User Experience (UI)
- **UI-001:** Crafted Sleek Minimalist SaaS UI for shortcodes with Auto/Light/Dark mode support.
- **UI-002:** Refined WP Admin management list interface with pagination, `$total_items` stats, responsive tables, and `-webkit-backdrop-filter` / `100dvh` modals.
- **UI-003:** Added WCAG accessibility (`aria-live="polite"`, keyboard copy, modal focus trap/restore, `:focus-visible`).

---

## Release History

### v1.5.10 (2026-11-20)
- **Security & Type Safety:** Added `is_string()` guards on password/secret POST inputs, `SameSite=Lax` cookie options array, `esc_url()` on Settings action link, masked reCAPTCHA secret input, and `$wpdb->prepare('%i')` on remaining queries.
- **Updater & i18n:** Added `empty($args->slug)` guard, `$transient->no_update` support, `function_exists('is_plugin_active')` guard, `wp_parse_url()` consistency, `__()`-wrapped AJAX messages, complete `languages/gnn-shortner.pot`, and root `VERSION` file.

### v1.5.9 (2026-11-18)
- **WP 7.1 & PHP 8.5 Hardening:** Added `Requires at least: 6.2`, `Tested up to: 7.1`, `Requires PHP: 7.4`, `init` (priority 1) `load_plugin_textdomain()`, DOM XSS `.textContent` fix, cookie `is_string()` guard, subfolder redirect/edit fix, conditional asset/reCAPTCHA loading, early `nocache_headers()`, and `GNN_Shortner_Updater` hardening.
- **Vanilla JS & Accessibility:** Rewrote `gnn-shortner.js` in Vanilla JS (removed jQuery), added modal focus trap, keyboard copy, `:focus-visible`, and responsive admin CSS.
- **Workflow:** Added `php -l` syntax check and CRLF cleanup to `.github/workflows/release.yml`.

### v1.5.8 (2026-08-05)
- Moved shippable plugin files into `gnn-shortner/` subfolder and simplified `release.yml` packaging.

### v1.5.7 (2026-08-02)
- Assigned top-level admin menu position `'79.102'` per ADR 0009 (GNN Product Family Admin Menu Position Registry).

### v1.5.6 (2026-07-15)
- Fixed query-string redirects (`wp_parse_url`), duplicate slug race condition, password entropy, fixed-window rate limiting, proxy `X-Forwarded-Proto` cookie flag, and opt-in `uninstall.php`.

### v1.5.5 (2026-07-15)
- Added `DONOTCACHEPAGE` / `nocache_headers()`, root cookie path `/`, and JS syntax fix.

### v1.3.0 (2026-04-26)
- Implemented zero-collision native CSS, `.gnn-admin-container` scoping, and spacing fixes.

### v1.2.0 (2026-04-26)
- Released Premium UI v2.0, loading spinner, plugin action links, and GitHub updater.

### v1.1.0 (2026-04-26)
- Standardized documentation layout and refactored updater check.

### v1.0.5 (2026-04-20)
- Google reCAPTCHA v2 frontend integration and settings configuration panel.

### v1.0.0 (2026-04-01)
- Initial release featuring database tables, core shortening logic, shortcode layout, and settings CRUD.
