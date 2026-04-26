# Security Audit: GNN Shortner

## Threat Models & Mitigations

| Threat | Mitigation Strategy | Status |
|--------|---------------------|--------|
| **SQL Injection** | **MANDATORY** use of `$wpdb->prepare()` for all custom table queries. | ✅ Pass |
| **Cross-Site Scripting (XSS)** | All user-supplied data (long URLs, slugs) is escaped with `esc_url()`, `esc_html()`, or `esc_attr()` before output. | ✅ Pass |
| **Cross-Site Request Forgery (CSRF)** | AJAX requests and settings updates are protected via WordPress Nonces. | ✅ Pass |
| **Automated Abuse (Spam)** | Google reCAPTCHA v2 implemented on the frontend shortening form. | ✅ Pass |
| **Unauthorized Access** | Admin management restricted via `manage_options` capability check. | ✅ Pass |
| **Information Disclosure** | `ABSPATH` guard prevents direct file access in all PHP components. | ✅ Pass |

## Constraints & Rules
1. **Prepared SQL:** Never concatenate variables directly into SQL strings.
2. **Strict Escaping:** Every `echo` statement MUST have an escaping function.
3. **Nonce Verification:** Every AJAX handler and form submission MUST verify the nonce.
4. **Input Sanitization:** Use `sanitize_text_field()`, `esc_url_raw()`, and `absint()` appropriately.
5. **Capability Checks:** Always check `current_user_can()` before performing sensitive actions.
