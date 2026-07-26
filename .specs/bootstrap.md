# Bootstrap Specifications

This document outlines the local setup, prerequisites, build steps, and environment setup for GNN Shortner.

## Prerequisites
* **WordPress:** 5.0 or higher `[Verified]`
* **PHP:** 7.4 or higher `[Verified]`
* **MySQL/MariaDB:** 5.6 / 10.1 or higher `[Verified]`

## Package Manager & Dependencies
* **Dependencies:** None `[Verified]`. All frontend components use vanilla JS and CSS. Google reCAPTCHA v2 is loaded dynamically over HTTPS.
* **Build Tools:** No package managers (like npm/yarn/composer) are active at the repository root `[Verified]`.

## Local Setup & Installation
1. Clone or download the repository into your local WordPress plugins directory: `/wp-content/plugins/gnn-shortner/` `[Verified]`.
2. Activate the plugin via the WordPress Admin dashboard (`Plugins > Installed Plugins`) `[Verified]`.
3. The custom database table `wp_gnn_shorturls` will be created automatically upon activation `[Verified]`.

## Configuration & Environment Variables
* **WordPress Options:** Configured dynamically from the admin panel settings page `[Verified]`.
* **reCAPTCHA Keys:** Must be specified under `Settings > GNN Shortner` to enable form verification `[Verified]`.

## Local Development Commands
* **Dev Server:** Run any local WordPress development stack (e.g., LocalWP, Docker/Lando, XAMPP) `[Inferred]`.
* **Asset Compilation:** None. CSS files in [css/](file:///c:/Users/bigde/.antigravity/gnn-shortner/css/) and JavaScript files in [js/](file:///c:/Users/bigde/.antigravity/gnn-shortner/js/) are loaded directly by the browser `[Verified]`.

## CI/CD Pipelines
* **Pipeline Name:** Manual Release `[Verified]`
* **Trigger:** `workflow_dispatch` (Manual trigger from GitHub UI) `[Verified]`
* **Workflow Logic:**
  1. Checks out the branch.
  2. Extracts version number from `gnnshortner.php`.
  3. Builds a plugin release package by running `rsync` to isolate necessary files (excluding dev files and project memory bank files).
  4. Bundles the directory structure into a `.zip` package.
  5. Deploys the package as an asset attached to a new GitHub Release corresponding to the version.
* **File Reference:** [.github/workflows/release.yml](file:///c:/Users/bigde/.antigravity/gnn-shortner/.github/workflows/release.yml)

## Deployment targets
* **Target:** Any standard WordPress installation hosting server `[Verified]`.
* **Mechanism:** Direct upload of the release ZIP or using the custom GitHub check-update updater library in the admin panel `[Verified]`.
