<?php
function gnn_create_tables() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gnn_shorturls';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        short_url VARCHAR(255) NOT NULL,
        long_url TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY short_url (short_url)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

function gnn_uninstall_prompt() {
    if (isset($_POST['gnn_uninstall']) && wp_verify_nonce($_POST['gnn_uninstall_nonce'], 'gnn_uninstall_action')) {
        if ($_POST['delete_data'] === 'yes') {
            global $wpdb;
            $table_name = $wpdb->prefix . 'gnn_shorturls';
            $wpdb->query("DROP TABLE IF EXISTS $table_name");
            delete_option('gnn_settings');
        }
    }
}

function gnn_get_long_url($short_url) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gnn_shorturls';
    return $wpdb->get_var($wpdb->prepare("SELECT long_url FROM $table_name WHERE short_url = %s", $short_url));
}

function gnn_save_url($short_url, $long_url) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gnn_shorturls';
    $wpdb->insert(
        $table_name,
        [
            'short_url' => sanitize_text_field($short_url),
            'long_url'  => esc_url_raw($long_url),
        ],
        ['%s', '%s']
    );
}