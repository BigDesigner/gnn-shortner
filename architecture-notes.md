# Architecture Notes: GNN Shortner

## System Design
GNN Shortner is a lightweight, high-performance WordPress plugin for URL shortening. It utilizes a custom database table for efficient mapping and follows WordPress best practices for security and modularity.

## Data Flow
1. **Creation:**
    - User submits a long URL via the `[gnn_shortner]` form.
    - AJAX request is sent to `admin-ajax.php`.
    - Nonce and reCAPTCHA are verified.
    - Logic checks if the URL is valid and generates/verifies the slug.
    - URL and slug are stored in `wp_gnn_shorturls`.
2. **Redirection:**
    - Visitor accesses `example.com/slug`.
    - `template_redirect` hook intercepts the request.
    - Logic queries the database for the corresponding long URL.
    - If found, a 301 (Permanent) redirect is issued.
    - If not found, WordPress continues with its standard 404 handling.

## Key Components
- **Custom Database Table:** `wp_gnn_shorturls` stores the mapping between slugs and long URLs.
- **Shortcode API:** `[gnn_shortner]` renders the glassmorphism shortening form.
- **AJAX API:** Handles real-time URL creation without page reloads.
- **Settings API:** Manages global configuration like reCAPTCHA keys.
- **GNN UI Library:** Shared CSS/JS patterns for glassmorphism and modern aesthetics.

## Core Principles
1. **Security First:**
    - SQL preparation (`$wpdb->prepare`) for all database interactions.
    - Mandatory Nonce verification for all state-changing operations.
    - reCAPTCHA integration to mitigate automated abuse.
2. **Performance:**
    - Minimal database footprint.
    - Efficient lookup using indexed columns.
    - No heavy external dependencies (vanilla JS/CSS).
3. **Universal Compatibility:**
    - Works with any theme (Light/Dark) via semi-transparent UI layers.
    - Follows native WordPress standards for the best user experience.
