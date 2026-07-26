# ADR 0005: Plugin Updates (GitHub Releases)

* **Status:** Accepted
* **Confidence:** Verified

## Context
distributing custom or premium plugins outside the official WordPress.org plugin directory requires a mechanism to check, download, and apply updates safely.

## Decision
Integrate a custom updater library that hooks into WordPress core plugin update mechanisms, pointing to GitHub Releases.

## Consequences
- **Pros:** Full control over update distribution channels without relying on WordPress.org approvals.
- **Cons:** Requires correct configuration of tags, release assets, and release checks.

## Evidence
- Updater initialized in [gnnshortner.php](file:///c:/Users/bigde/.antigravity/gnn-shortner/gnnshortner.php#L28) and implemented in [inc/updater.php](file:///c:/Users/bigde/.antigravity/gnn-shortner/inc/updater.php).
