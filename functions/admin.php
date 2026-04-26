<?php
function gnn_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gnn_shorturls';
    
    $urls = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
    if ($wpdb->last_error) {
        echo '<div class="error"><p>Database error: ' . esc_html($wpdb->last_error) . '</p></div>';
        return;
    }
    ?>
    <div class="wrap gnn-admin-container">
        <h1>GNN Shortner - All URLs</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Short URL</th>
                    <th>Long URL</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (!empty($urls) && is_array($urls)) {
                    foreach ($urls as $url) : ?>
                        <tr data-id="<?php echo esc_attr($url->id); ?>">
                            <td><?php echo esc_html(home_url('/') . $url->short_url); ?></td>
                            <td><?php echo esc_html($url->long_url); ?></td>
                            <td><?php echo esc_html($url->created_at); ?></td>
                            <td>
                                <a href="#" class="gnn-edit-url" data-id="<?php echo esc_attr($url->id); ?>">Edit</a> |
                                <a href="#" class="gnn-delete-url" data-id="<?php echo esc_attr($url->id); ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach;
                } else { ?>
                    <tr>
                        <td colspan="4">No URLs found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <div id="gnn-edit-form" style="display:none;">
            <h2>Edit URL</h2>
            <form id="gnn-edit-url-form">
                <input type="hidden" name="id">
                <label>Short URL: <input type="text" name="short_url"></label><br>
                <label>Long URL: <input type="url" name="long_url"></label><br>
                <button type="submit">Save</button>
            </form>
        </div>
        <!-- Delete Onay Modalı -->
        <div id="gnn-delete-modal" class="gnn-modal" style="display:none;">
            <div class="gnn-modal-content">
                <p>Are you sure you want to delete this URL?</p>
                <button id="gnn-delete-yes" class="button button-primary">Yes</button>
                <button id="gnn-delete-no" class="button">No</button>
            </div>
        </div>
    </div>
    <?php
}

// AJAX handler for Delete
function gnn_delete_url() {
    if (!check_ajax_referer('gnn_shortner_nonce', 'nonce', false)) {
        wp_send_json_error('Nonce verification failed.');
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'gnn_shorturls';
    $id = intval($_POST['id']);
    $deleted = $wpdb->delete($table_name, ['id' => $id], ['%d']);
    
    if ($deleted) {
        wp_send_json_success('URL deleted.');
    } else {
        wp_send_json_error('Failed to delete URL.');
    }
}
add_action('wp_ajax_gnn_delete_url', 'gnn_delete_url');

// AJAX handler for Edit (Değişmedi)
function gnn_edit_url() {
    check_ajax_referer('gnn_shortner_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'gnn_shorturls';
    $id = intval($_POST['id']);
    $short_url = sanitize_text_field($_POST['short_url']);
    $long_url = esc_url_raw($_POST['long_url']);

    if (!filter_var($long_url, FILTER_VALIDATE_URL)) {
        wp_send_json_error('Invalid URL format.');
    }

    $wpdb->update(
        $table_name,
        ['short_url' => $short_url, 'long_url' => $long_url],
        ['id' => $id],
        ['%s', '%s'],
        ['%d']
    );
    wp_send_json_success(['short_url' => home_url('/') . $short_url, 'long_url' => $long_url]);
}
add_action('wp_ajax_gnn_edit_url', 'gnn_edit_url');

// Settings page (Değişmedi)
function gnn_settings_page() {
    if (isset($_POST['gnn_settings']) && wp_verify_nonce($_POST['gnn_settings_nonce'], 'gnn_settings_action')) {
        update_option('gnn_recaptcha_site_key', sanitize_text_field($_POST['recaptcha_site_key']));
        update_option('gnn_recaptcha_secret_key', sanitize_text_field($_POST['recaptcha_secret_key']));
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }
    ?>
    <div class="wrap gnn-admin-container">
        <h1>GNN Shortner Settings</h1>
        <form method="post">
            <?php wp_nonce_field('gnn_settings_action', 'gnn_settings_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th>Google reCAPTCHA Site Key</th>
                    <td><input type="text" name="recaptcha_site_key" value="<?php echo esc_attr(get_option('gnn_recaptcha_site_key')); ?>"></td>
                </tr>
                <tr>
                    <th>Google reCAPTCHA Secret Key</th>
                    <td><input type="text" name="recaptcha_secret_key" value="<?php echo esc_attr(get_option('gnn_recaptcha_secret_key')); ?>"></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="gnn_settings" class="button-primary" value="Save Changes"></p>
        </form>
    </div>
    <?php
}