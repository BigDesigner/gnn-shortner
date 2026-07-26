# ADR 0001: Database Implementation

* **Status:** Accepted
* **Confidence:** Verified

## Context
High-volume URL mapping requires fast and efficient lookup times. Storing maps inside the standard WordPress options or post meta tables can pollute the default database schema and slow down query operations.

## Decision
We implement a custom database table named `wp_gnn_shorturls` specifically for GNN Shortner mappings. This keeps database queries isolated and allows indexing key fields like the `short_url` slug.

## Consequences
- **Pros:** Fast lookups, indexable schema, does not pollute WordPress native tables.
- **Cons:** Custom table requires activation-hook setup and cleanup on plugin uninstallation.

## Evidence
- Custom table activation setup is verified in [functions/db.php](file:///c:/Users/bigde/.antigravity/gnn-shortner/functions/db.php) and registered in [gnnshortner.php](file:///c:/Users/bigde/.antigravity/gnn-shortner/gnnshortner.php#L67).
