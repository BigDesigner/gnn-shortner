# Task Pipeline

## Current Project State
The project is at **v1.5.10** (released 2026-11-20), fully compatible with **WordPress 7.1** and **PHP 7.4–8.5**. The plugin uses a zero-jQuery Vanilla JS frontend/admin script, a Sleek Minimalist SaaS UI with Auto/Light/Dark modes, hardened security controls (DOM XSS protection, `is_string()` type guards, `SameSite=Lax` cookies, `$wpdb->prepare('%i')`, HTTPS GitHub updater allowlisting, fixed-window rate limiting), subfolder install support, WCAG keyboard/focus accessibility, and complete i18n (`languages/gnn-shortner.pot`).

* **Active Sprint:** Maintenance & Support (`v1.5.10` published)
* **Release Readiness:** `v1.5.10` tagged and released on GitHub (`gnn-shortner-1.5.10.zip`).

---

## Active Checklist

### Immediate Priorities
- [ ] **DEPLOY:** Monitor `v1.5.10` production rollout and self-hosted GitHub updater behavior on WordPress 7.1 sites.

### Feature Backlog
- [ ] **FEAT-STATS:** Implement click tracking and counter database columns for each short URL slug.
- [ ] **FEAT-QR:** Generate custom QR codes for shortened links directly inside the WordPress administrative URLs table.
- [ ] **FEAT-L10N:** Add Turkish (`tr_TR`) `.po`/`.mo` translation files compiled from `gnn-shortner/languages/gnn-shortner.pot`.

---

## Blockers
* No critical blockers or runtime failures detected.

---

## Validation Plan

### Automated Validation
- **PHP Syntax Check:**
  ```powershell
  Get-ChildItem -Path "gnn-shortner" -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
  ```
- **JS Syntax Check:**
  ```powershell
  node --check gnn-shortner/js/gnn-shortner.js
  ```

### Manual Verification
- Deploy `gnn-shortner-1.5.10.zip` to a WordPress 7.1 instance (both root and subfolder installs).
- Verify `[gnn_shortner]` form submission, password gate, reCAPTCHA conditional loading, and copy-to-clipboard via mouse and keyboard (`Enter`/`Space`).
- Verify admin table CRUD, pagination, masked reCAPTCHA secret saving, and 301 redirects with query strings (e.g. `?utm_source=test`).
