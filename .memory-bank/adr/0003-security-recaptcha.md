# ADR 0003: Security (reCAPTCHA)

* **Status:** Accepted
* **Confidence:** Verified

## Context
Publicly accessible URL shortener forms are highly prone to abuse by spam bots and automated scripts.

## Decision
Integrate Google reCAPTCHA v2 on the frontend shortening form, with customizable settings keys in the WordPress admin panel.

## Consequences
- **Pros:** Effectively stops automatic form-submission spam.
- **Cons:** Adds an external script dependency on Google's APIs.

## Evidence
- Custom settings in [functions/admin.php](file:///c:/Users/bigde/.antigravity/gnn-shortner/functions/admin.php) and verification code in [functions/shortner.php](file:///c:/Users/bigde/.antigravity/gnn-shortner/functions/shortner.php).
