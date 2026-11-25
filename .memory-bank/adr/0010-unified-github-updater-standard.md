# ADR 0010: Unified GNN GitHub Updater Standard

- **Status**: Accepted
- **Confidence**: Verified
- **Date**: 2026-11-21
- **Category**: Maintainability & Security
- **Supersedes**: [ADR 0005](0005-plugin-updates-github.md)
- **Superseded By**: None

## Context
The previous `GNN_Shortner_Updater` implementation in `gnn-shortner/inc/updater.php` had several gaps compared to the Unified GNN GitHub Updater standard:
1. No HTML/HEAD fallback when `api.github.com` is rate-limited or unreachable.
2. Asset selection used `strpos($asset->name, '.zip') !== false` instead of a strict `.zip` suffix check, and version validation lacked semver regex enforcement.
3. Host allowlist omitted `release-assets.githubusercontent.com` and `*.githubusercontent.com` subdomains.
4. `check_for_update()` did not guard `!is_object($transient)`, did not initialize `$transient->response`/`$transient->no_update` as arrays, omitted `id`/`requires`/`tested`/`requires_php`, and did not unset `response` when up to date.
5. `after_install()` checked `is_plugin_active()` post-install instead of capturing activation state in `upgrader_pre_install`, did not normalize paths with `wp_normalize_path()`, did not guard `is_wp_error($response)`/`is_wp_error($result)`, and did not call `wp_clean_plugins_cache(true)`.
6. `handle_manual_check()` lacked `403` response codes on `wp_die()` and did not `wp_die()` on insufficient `update_plugins` capability.

## Decision
Standardize `GNN_Shortner_Updater` (`gnn-shortner/inc/updater.php`) on the Unified GNN GitHub Updater architecture while preserving existing identifiers (`gnn_shortner_github_update_check`, `gnn_shortner_check_update`, `gnn_shortner_manual_update`):
1. Add `upgrader_pre_install` (`before_install`) to record `$this->was_active` before files are replaced, and restore activation + `wp_clean_plugins_cache(true)` in `after_install()`.
2. Add `wp_remote_head()` (`redirection => 0`) + `/releases/expanded_assets/{tag}` HTML scraping fallback when the GitHub REST API fails or is rate-limited.
3. Enforce semver tag validation, `.zip` suffix preference, SSRF HTTPS host allowlist (`sanitize_github_url()`), and 300s negative cache sentinel `(object)['failed' => true]`.
4. Populate `response` and `no_update` cleanly in `check_for_update()`, normalize filesystem paths in `after_install()`, and keep `clear_cache()` scoped strictly to `$this->transient_key`.

## Verification
- `php -l gnn-shortner/inc/updater.php` passed with zero syntax errors.


## Amendment (2026-11-25): Disk-First Local Version Detection
- **Root Cause:** Reading cached/in-memory version values inside `get_local_version()` could return the pre-upgrade version when WordPress fired `pre_set_site_transient_update_plugins` within the same PHP request right after overwriting plugin files, causing the update banner to persist until a second update run.
- **Resolution:** `get_local_version()` now reads the live `Version` header directly from `WP_PLUGIN_DIR . '/' . $this->plugin_basename` via `clearstatcache( true, $main_file )` and `get_file_data()` first.
