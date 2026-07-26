# Boundary Conditions

This document defines the security boundaries, data constraints, performance budgets, and operational limitations of the GNN Shortner plugin.

## Security Constraints

### 1. Database Safety
* **Requirement:** All SQL interactions with the custom table `wp_gnn_shorturls` MUST use `$wpdb->prepare()` to protect against SQL Injection attacks `[Verified]`.
* **Standard:** Never concatenate variables directly into SQL queries.

### 2. Output Escaping
* **Requirement:** All variables output to the browser MUST use standard WordPress escaping helpers (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`) to defend against Cross-Site Scripting (XSS) `[Verified]`.
* **Standard:** Every `echo` statement must be escaped.

### 3. State-Changing Validation
* **Requirement:** Nonces MUST be verified for all AJAX handlers and options updates to prevent Cross-Site Request Forgery (CSRF) `[Verified]`.
* **Nonce Name:** `gnn_shortner_nonce` `[Verified]`

### 4. Admin Access Controls
* **Requirement:** User capabilities MUST be checked before executing any administrative or setting update action `[Verified]`.
* **Target Role:** `manage_options` (Admin role) `[Verified]`
* **Helper:** `current_user_can('manage_options')` `[Verified]`

### 5. Direct Access Protection
* **Requirement:** All PHP component entry files MUST start with an ABSPATH check to prevent direct execution by external visitors `[Verified]`.
* **Syntax:** `if ( ! defined( 'ABSPATH' ) ) exit;` `[Verified]`

---

## Data & Database Constraints
* **Table Name:** `wp_gnn_shorturls` `[Verified]`
* **Columns:**
  - `id`: Auto-increment primary key.
  - `long_url`: Text representation of the target URL.
  - `short_url`: Unique index representing the slug.
* **Slug Constraints:** Slugs must be unique to avoid redirect collisions `[Verified]`.

---

## Performance Budgets
* **Assets:** Zero-dependency frontend scripts. The only external assets permitted are the Google reCAPTCHA script and Google fonts.
* **Load Time:** Interceptions occur early in the WordPress execution flow (`template_redirect` hook), minimizing redirect latencies `[Verified]`.

---

## Deployment & CI/CD Boundaries
* **Build Exclusions:** Development files must never be packaged into the release archive. This includes:
  - `.git/` and `.github/`
  - `.memory-bank/` (Project Memory Bank files)
  - `.specs/`
  - `.agents/`
  - `.tasks/`
  - `docs/` and `archive/`
  - `.gitignore`, and all markdown `*.md` files (except `CHANGELOG.md`) `[Verified]`
