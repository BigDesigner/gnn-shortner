# ADR 0004: Design Philosophy (Glassmorphism)

* **Status:** Accepted
* **Confidence:** Verified

## Context
WordPress plugins must run alongside various themes. Standard solid colors often clash with theme-specific light/dark background designs.

## Decision
Adopt a "GNN Premium" Glassmorphism UI using semi-transparent background colors (`rgba`), background blur (`backdrop-filter`), and font inheritance.

## Consequences
- **Pros:** Native visual integration that blends with both dark and light themes without requiring duplicate CSS rules.
- **Cons:** Dependent on browser support for backdrop CSS properties (graceful fallback styling is applied).

## Evidence
- Frontend layout styling is defined in [css/frontend.css](file:///c:/Users/bigde/.antigravity/gnn-shortner/css/frontend.css).
