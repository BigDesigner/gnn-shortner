<?php
/*
Plugin Name: GNN Shortner
Plugin URI: https://www.bigdesigner.com
Description: WordPress için kısa URL oluşturma eklentisi. Signature GNN Glassmorphism UI ve SEO dostu 301 yönlendirmesi ile.
Version: 1.2.0
Author: BigDesigner
Author URI: https://www.bigdesigner.com
License: GPL2
Text Domain: gnn-shortner
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

// Enqueue styles and scripts
function gnn_enqueue_assets() {
    if (!is_admin()) {
        wp_enqueue_style('gnn-shortner-frontend', GNN_PLUGIN_URL . 'css/frontend.css', [], '1.2.0');
    } else {
        $screen = get_current_screen();
        // Sadece eklenti sayfalarındayken admin.css yükle
        if (strpos($screen->id, 'gnn-shortner') !== false) {
            wp_enqueue_style('gnn-shortner-admin', GNN_PLUGIN_URL . 'css/admin.css', [], '1.2.0');
        }
    }
    
    wp_enqueue_script('gnn-shortner-js', GNN_PLUGIN_URL . 'js/gnn-shortner.js', ['jquery'], '1.2.0', true);
    wp_localize_script('gnn-shortner-js', 'gnn_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('gnn_shortner_nonce'),
    ]);

    // reCAPTCHA'yı yalnızca frontend'de yükle
    if (!is_admin()) {
        wp_enqueue_script('gnn-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true);
    }
}
add_action('wp_enqueue_scripts', 'gnn_enqueue_assets');
add_action('admin_enqueue_scripts', 'gnn_enqueue_assets');

// Register shortcode for frontend
function gnn_shortner_shortcode() {
    ob_start();
    gnn_display_shortner_form();
    return ob_get_clean();
}
add_shortcode('gnn_shortner', 'gnn_shortner_shortcode');

// Activation hook
register_activation_hook(__FILE__, 'gnn_create_tables');

// Deactivation hook (prompt for deletion on uninstall)
register_uninstall_hook(__FILE__, 'gnn_uninstall_prompt');

// Yönlendirme işlemi (Çalıştığını doğruladığınız yöntem)
function gnn_short_url_redirect() {
    $short_url = trim($_SERVER['REQUEST_URI'], '/');
    if (!empty($short_url)) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gnn_shorturls';
        $result = $wpdb->get_row($wpdb->prepare("SELECT long_url FROM $table_name WHERE short_url = %s", $short_url));

        if ($result) {
            wp_redirect($result->long_url, 301); // Orijinal URL'ye yönlendir
            exit;
        }
    }
}
add_action('template_redirect', 'gnn_short_url_redirect');

// Admin menu
function gnn_admin_menu() {
    add_menu_page(
        'GNN Shortner',           // Sayfa başlığı
        'GNN Shortner',           // Menü adı
        'manage_options',         // Yetki seviyesi
        'gnn-shortner',           // Menü slug
        'gnn_admin_page',         // Geri çağrı fonksiyonu
        'dashicons-admin-links'   // İkon
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
function gnn_shortner_plugin_links($links) {
    $donate_link = '<a href="https://buymeacoffee.com/bigdesigner" target="_blank" style="font-weight:bold; color:#d63638;">' . esc_html__('Donate', 'gnn-shortner') . '</a>';
    
    $settings_link = '<a href="' . admin_url('admin.php?page=gnn-shortner-settings') . '">' . esc_html__('Settings', 'gnn-shortner') . '</a>';
    
    $update_url = wp_nonce_url(admin_url('plugins.php?gnn_shortner_check_update=1'), 'gnn_shortner_manual_update');
    $update_link = '<a href="' . esc_url($update_url) . '">' . esc_html__('Check Updates', 'gnn-shortner') . '</a>';
    
    array_unshift($links, $donate_link, $settings_link, $update_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'gnn_shortner_plugin_links');