# ADR 0002: Redirection Method

* **Status:** Accepted
* **Confidence:** Verified

## Context
Redirection needs to capture inbound traffic matching a slug and route it to the long URL efficiently. Doing this via custom rewrite rules can collide with other plugins.

## Decision
We hook into the WordPress `template_redirect` action to intercept request paths. If a slug matches our database records, we perform a 301 redirection and exit.

## Consequences
- **Pros:** Native, early-stage intercept without complex WordPress rewrite rules.
- **Cons:** Must handle case-sensitivity and sanitize inputs carefully to prevent redirect loops.

## Evidence
- Implemented in [gnnshortner.php](file:///c:/Users/bigde/.antigravity/gnn-shortner/gnnshortner.php#L73-L87).
