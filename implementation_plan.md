# GNN Shortner — Frontend Password Protection Feature

This plan details how to implement password protection for the public URL shortening form. If enabled, the page will present a secure password screen (with a "Login" button). Upon correct verification, the shortener form is unlocked and displayed (with a "Shorten URL" button).

## User Review Required

> [!IMPORTANT]
> - **Secure Verification Method:** To prevent bypassing the lock via simple client-side edits, the actual shortener form HTML is conditionally outputted from the server-side only when verified.
> - **Cookie Storage:** Verification state is stored in a secure `HttpOnly` browser session cookie, which automatically expires when the browser is closed.
> - **Hashing Standard:** Passwords are never stored in plaintext. They are salted and hashed using WordPress's native `wp_hash_password()`.

## Proposed Changes

### 1. WordPress Admin Settings
#### [MODIFY] [admin.php](file:///c:/Users/bigde/.antigravity/gnn-shortner/functions/admin.php)
- Add the **Access Control** settings section.
- Handle saving of the password:
  - If empty input: delete the password option (public access).
  - If value is the masked `••••••••` string: make no changes.
  - If any other value: hash the value using `wp_hash_password()` and save.

### 2. Frontend Layout & AJAX handlers
#### [MODIFY] [shortner.php](file:///c:/Users/bigde/.antigravity/gnn-shortner/functions/shortner.php)
- Update `gnn_display_shortner_form()`:
  - Check if `gnn_frontend_password` option is empty. If so, render the shortener form directly (including reCAPTCHA).
  - If password option exists, verify the cookie `gnn_unlocked` against `wp_hash( get_option('gnn_frontend_password') . wp_salt() )`.
  - If the cookie matches: render the shortener form **WITHOUT** the reCAPTCHA widget.
  - If the cookie is missing or mismatch: render a clean password input form (including reCAPTCHA) with button text "Login".
- Create `gnn_verify_password()` AJAX action:
  - Validate referrer nonce.
  - Apply transient IP-based rate limiting (similar to URL shortener AJAX action) to prevent brute-forcing of the password.
  - Verify reCAPTCHA response if keys are configured.
  - Check the input password using `wp_check_password()`.
  - If correct, set secure cookie `gnn_unlocked` containing the expected token.
- Update `gnn_process_shortner()` AJAX action:
  - Validate referrer nonce and rate limiting.
  - Check if password protection is enabled:
    - **If Enabled:** Verify the `gnn_unlocked` cookie. If missing/invalid, reject immediately (ensures bypass is impossible). Skip reCAPTCHA check (already verified at login).
    - **If Disabled:** Proceed with the standard reCAPTCHA verification checks.

### 3. Frontend Interactions
#### [MODIFY] [gnn-shortner.js](file:///c:/Users/bigde/.antigravity/gnn-shortner/js/gnn-shortner.js)
- Handle submit handler for `#gnn-password-form`:
  - Show loading state on button text/loader.
  - Submit the password and `g-recaptcha-response` via AJAX to `gnn_verify_password`.
  - On success: call `location.reload();` to refresh the page and load the shortener form naturally.
  - On error: display error toast, reset reCAPTCHA widget, and reset button state.

### 4. Styles Modernization
#### [MODIFY] [frontend.css](file:///c:/Users/bigde/.antigravity/gnn-shortner/css/frontend.css)
- Extend form input styling to include `input[type="password"]` and apply appropriate scoped margins.

---

## Verification Plan

### Automated Tests
- Perform a syntax check on modified PHP files:
  ```powershell
  php -l functions/admin.php
  php -l functions/shortner.php
  ```

### Manual Verification
1. Open GNN Shortner Settings page in WP Admin.
2. Enter a password under "Access Control". Save.
3. Open the public page containing the shortcode `[gnn_shortner]`.
4. Verify that only the password form is displayed with a "Login" button and a reCAPTCHA widget (if keys are set).
5. Enter a wrong password. Verify that a toast error appears.
6. Enter the correct password and solve reCAPTCHA. Verify that the page reloads, unlocking the shortener form with the button "Shorten URL" and **WITHOUT** any reCAPTCHA widget.
7. Perform a mock URL shortening request. Confirm that the URL is successfully shortened (proves reCAPTCHA is not blockingly required post-unlock).
8. Clear the cookie `gnn_unlocked` in the browser console. Confirm that the password form lock is instantly restored.
9. Attempt an unauthenticated direct POST request to the AJAX endpoint `gnn_shortner` (simulating bypass). Verify it is rejected with access denied.
10. Clear the password in admin settings. Verify that the shortcode page displays the public shortener form directly (with reCAPTCHA widget visible and operational).

### Audit Notes

- **Conditional reCAPTCHA Flow:** Form security is optimized by showing reCAPTCHA only during the login phase. Once verified, the user is authenticated via cookie, and reCAPTCHA is hidden to provide a cleaner UX during URL shortening.
- **Bypass Protection:** Direct backend API protection has been implemented in `gnn_process_shortner()`. Without a cryptographically valid `gnn_unlocked` session cookie, URL shortening requests are strictly rejected.
- **Brute Force Protection:** Added transient IP-based rate limiting to the password verification endpoint (`gnn_verify_password`) to mitigate brute-force attempts.
- **Output Escaping:** Enforced strict WordPress output escaping (`esc_attr_e`, `esc_html_e`) on all output components of the new password login form.
- **Secure State Storage:** Checked that the lock bypass token is cryptographically salted and cannot be forged.

