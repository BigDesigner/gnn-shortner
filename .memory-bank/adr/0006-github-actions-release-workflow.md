# ADR 0006: GitHub Actions Release Workflow

* **Status:** Accepted
* **Confidence:** Verified

## Context
Deploying tagged releases manually can lead to human errors (e.g. failing to exclude dev files or packaging incorrect files).

## Decision
Create a GitHub Actions workflow `.github/workflows/release.yml` triggered manually (`workflow_dispatch`). This workflow checks out the repository, extracts the version from `gnnshortner.php`, builds a clean `.zip` distribution archive (excluding developer files like tests, configs, memory bank), and creates/attaches it to a GitHub release tag.

## Consequences
- **Pros:** Consistent, error-free packaging and automatic release tagging.
- **Cons:** Dependent on GitHub Actions runner availability and write permissions.

## Evidence
- Implemented and verified in [.github/workflows/release.yml](file:///c:/Users/bigde/.antigravity/gnn-shortner/.github/workflows/release.yml).
