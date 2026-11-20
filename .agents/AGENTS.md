# Agent Rules & Guidelines

## Communication Style Rules
- **No Fluff:** Completely omit introduction sentences, congratulations, apologies, and politeness templates.
- **Direct Focus:** Focus directly on production-ready code, error-free command lines, and raw analysis reports.
- **Error Handling:** When an error is made, do not apologize. Acknowledge the error and directly output the corrected code, command, or file replacement.

## Stack-Specific Agent Behaviors (WordPress PHP Plugin)
- **Syntax Check:** Always run syntax checks on PHP files before proposing or committing changes.
- **WordPress Security Best Practices:** Ensure all output is properly escaped (`esc_html()`, `esc_attr()`, `esc_url()`, `esc_html_e()`, etc.) and all inputs are sanitized.
- **SQL Sanitization:** Prepare all database queries using `$wpdb->prepare()`.
- **CSRF Protection:** Always use WordPress nonces for AJAX endpoints and forms.

## Git Commit Anonymity Rule
- **CRITICAL GIT PROHIBITION**: Never append, inject, or suggest any "Co-Authored-By" trailers, metadata, or attribution lines (e.g., "Co-Authored-By: Claude...") in git commit messages, code blocks, or automated git scripts. All git commit messages must remain completely anonymous or strictly limited to the user's explicit content.
