# GNN Shortner

A premium, minimalist WordPress plugin for creating and managing short URLs. Built with performance, security, and the signature GNN glassmorphism design.

## Features
- **URL Shortening:** Create short, memorable links from long URLs.
- **Custom Slugs:** Specify your own custom short URL path.
- **Premium UI:** Modern Glassmorphism design that adapts to any Light or Dark theme.
- **Security:** Google reCAPTCHA integration to prevent bot abuse.
- **Redirection:** SEO-friendly 301 redirects for all shortened links.
- **GitHub Updates:** Built-in updater for easy management via GitHub Releases.
- **Admin Dashboard:** Manage and delete shortened links from the WordPress admin panel.

## Installation
1. Upload the `gnn-shortner` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. (Optional) Configure reCAPTCHA keys in `Settings > GNN Shortner`.

## Usage
Simply use the shortcode `[gnn_shortner]` anywhere in your posts, pages, or widgets to display the shortening form.

## Security & Rules
- **Prepared Statements:** All database queries are handled via `$wpdb->prepare()`.
- **Nonce Protection:** AJAX and form submissions are secured with WordPress nonces.
- **Strict Escaping:** All outputs are escaped with `esc_html()`, `esc_attr()`, or `esc_url()`.
- **ABSPATH Guard:** Direct file access is forbidden.

## License
GPLv2 or later.
