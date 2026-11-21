<?php
/*
Plugin Name: GNN Shortner
Plugin URI: https://www.bigdesigner.com
Description: WordPress için kısa URL oluşturma eklentisi. Signature GNN Glassmorphism UI ve SEO dostu 301 yönlendirmesi ile.
Version: 1.5.11
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Author: BigDesigner
Author URI: https://www.bigdesigner.com
License: GPL2
Text Domain: gnn-shortner
Domain Path: /languages
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('GNN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GNN_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include function files
require_once GNN_PLUGIN_DIR . 'functions/db.php';
require_once GNN_PLUGIN_DIR . 'functions/shortner.php';
require_once GNN_PLUGIN_DIR . 'functions/admin.php';

// Include the GitHub updater
require_once GNN_PLUGIN_DIR . 'inc/updater.php';

// Load plugin textdomain on init (WP 6.7+ requirement)
function gnn_shortner_load_textdomain()
{
    load_plugin_textdomain('gnn-shortner', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'gnn_shortner_load_textdomain', 1);

// Register styles and scripts; enqueue only where needed
function gnn_enqueue_assets()
{
    wp_register_style('gnn-shortner-frontend', GNN_PLUGIN_URL . 'css/frontend.css', [], '1.5.11');
    wp_register_style('gnn-shortner-admin', GNN_PLUGIN_URL . 'css/admin.css', [], '1.5.11');
    wp_register_script('gnn-shortner-js', GNN_PLUGIN_URL . 'js/gnn-shortner.js', [], '1.5.11', true);

    wp_localize_script('gnn-shortner-js', 'gnn_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('gnn_shortner_nonce'),
        'i18n'     => [
            'copied'        => __('Copied to clipboard:', 'gnn-shortner'),
            'copy_label'    => __('Copy short URL to clipboard', 'gnn-shortner'),
            'error_generic' => __('An error occurred. Please try again.', 'gnn-shortner'),
            'deleted'       => __('URL deleted successfully.', 'gnn-shortner'),
            'delete_failed' => __('Failed to delete.', 'gnn-shortner'),
            'updated'       => __('URL updated successfully.', 'gnn-shortner'),
            'update_failed' => __('Failed to update.', 'gnn-shortner'),
            'request_failed'=> __('Request failed. Please try again.', 'gnn-shortner'),
        ],
    ]);

    if (!is_admin()) {
        global $post;
        if (is_singular() && $post instanceof WP_Post && has_shortcode($post->post_content, 'gnn_shortner')) {
            gnn_enqueue_frontend_assets();
        }
    } else {
        $screen = get_current_screen();
        // Sadece eklenti sayfalarındayken admin.css ve js yükle
        if ($screen && strpos($screen->id, 'gnn-shortner') !== false) {
            wp_enqueue_style('gnn-shortner-admin');
            wp_enqueue_script('gnn-shortner-js');
        }
    }
}
add_action('wp_enqueue_scripts', 'gnn_enqueue_assets');
add_action('admin_enqueue_scripts', 'gnn_enqueue_assets');

/**
 * Enqueue frontend assets (and reCAPTCHA only when configured and required).
 */
function gnn_enqueue_frontend_assets()
{
    wp_enqueue_style('gnn-shortner-frontend');
    wp_enqueue_script('gnn-shortner-js');

    $site_key    = get_option('gnn_recaptcha_site_key');
    $stored_hash = get_option('gnn_frontend_password');
    $needs_captcha = !empty($site_key);

    if ($needs_captcha && !empty($stored_hash)) {
        $expected_token = wp_hash($stored_hash . wp_salt());
        $cookie_token   = (isset($_COOKIE['gnn_unlocked']) && is_string($_COOKIE['gnn_unlocked']))
            ? wp_unslash($_COOKIE['gnn_unlocked'])
            : '';
        if ('' !== $cookie_token && hash_equals($expected_token, $cookie_token)) {
            $needs_captcha = false;
        }
    }

    if ($needs_captcha) {
        wp_enqueue_script('gnn-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true);
    }
}

// Send nocache headers early when password-protected shortcode page is served
function gnn_shortner_maybe_nocache()
{
    if (is_admin() || empty(get_option('gnn_frontend_password'))) {
        return;
    }

    global $post;
    if (is_singular() && $post instanceof WP_Post && has_shortcode($post->post_content, 'gnn_shortner')) {
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
        if (!headers_sent()) {
            nocache_headers();
        }
    }
}
add_action('template_redirect', 'gnn_shortner_maybe_nocache', 5);

// Register shortcode for frontend
function gnn_shortner_shortcode()
{
    gnn_enqueue_frontend_assets();
    ob_start();
    gnn_display_shortner_form();
    return ob_get_clean();
}
add_shortcode('gnn_shortner', 'gnn_shortner_shortcode');

// Activation hook
register_activation_hook(__FILE__, 'gnn_create_tables');

// Temizlik uninstall.php içinde ele alınır (WP_UNINSTALL_PLUGIN korumalı)

// Yönlendirme işlemi (Güvenlik yaması uygulanmış hali)
function gnn_short_url_redirect()
{
    $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
    // Sadece yol kısmını al; sona eklenen ?utm_source=... gibi query string'leri yok say
    $path        = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
    $home_path   = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');

    if ('' !== $home_path && 0 === strpos($path, $home_path . '/')) {
        $path = substr($path, strlen($home_path) + 1);
    }

    $short_url = $path;

    // Sadece alfasayısal, tire ve alt çizgi içeren kısa URL'leri işle
    if (!empty($short_url) && preg_match('/^[a-zA-Z0-9_\-]+$/', $short_url)) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gnn_shorturls';
        $result = $wpdb->get_row($wpdb->prepare('SELECT long_url FROM %i WHERE short_url = %s', $table_name, $short_url));

        if ($result) {
            $long_url = esc_url_raw($result->long_url);
            
            // XSS ve open redirect önlemek için sadece http ve https protokollerine izin ver
            if (filter_var($long_url, FILTER_VALIDATE_URL) && in_array(wp_parse_url($long_url, PHP_URL_SCHEME), ['http', 'https'], true)) {
                wp_redirect($long_url, 301);
                exit;
            }
        }
    }
}
add_action('template_redirect', 'gnn_short_url_redirect');

// Admin menu
function gnn_admin_menu()
{
    add_menu_page(
        'GNN Shortner',           // Sayfa başlığı
        'GNN Shortner',           // Menü adı
        'manage_options',         // Yetki seviyesi
        'gnn-shortner',           // Menü slug
        'gnn_admin_page',         // Geri çağrı fonksiyonu
        'dashicons-admin-links',  // İkon
        '79.102'                  // Pozisyon
    );
    add_submenu_page(
        'gnn-shortner',           // Üst menü slug
        'Settings',               // Sayfa başlığı
        'Settings',               // Menü adı
        'manage_options',         // Yetki seviyesi
        'gnn-shortner-settings',  // Alt menü slug
        'gnn_settings_page'       // Geri çağrı fonksiyonu
    );
}
add_action('admin_menu', 'gnn_admin_menu');

/**
 * Add plugin action links
 */
function gnn_shortner_plugin_links($links)
{
    $donate_link = '<a href="https://buymeacoffee.com/bigdesigner" target="_blank" rel="noopener noreferrer" style="font-weight:bold; color:#d63638;">' . esc_html__('Donate', 'gnn-shortner') . '</a>';

    $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=gnn-shortner-settings')) . '">' . esc_html__('Settings', 'gnn-shortner') . '</a>';

    $update_url = wp_nonce_url(admin_url('plugins.php?gnn_shortner_check_update=1'), 'gnn_shortner_manual_update');
    $update_link = '<a href="' . esc_url($update_url) . '">' . esc_html__('Check Updates', 'gnn-shortner') . '</a>';

    array_unshift($links, $donate_link, $settings_link, $update_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'gnn_shortner_plugin_links');