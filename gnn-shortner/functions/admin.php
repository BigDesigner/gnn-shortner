<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function gnn_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to view this page.', 'gnn-shortner' ) );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'gnn_shorturls';

    // Get total number of records for pagination
    $total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    $per_page = 50;
    $current_page = max(1, isset($_GET['paged']) ? absint($_GET['paged']) : 1);
    $offset = ($current_page - 1) * $per_page;
    $total_pages = ceil($total_items / $per_page);

    $urls = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset) );
    if ( $wpdb->last_error ) {
        echo '<div class="error"><p>Database error: ' . esc_html( $wpdb->last_error ) . '</p></div>';
        return;
    }

    if ( ! function_exists( 'get_plugin_data' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/gnn-shortner/gnnshortner.php' );
    $plugin_version = isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '—';
    ?>
    <div class="wrap gnn-admin-container">

        <!-- Header -->
        <div class="gnn-admin-header">
            <div class="gnn-admin-header-left">
                <span class="dashicons dashicons-admin-links gnn-admin-logo"></span>
                <div>
                    <h1 class="gnn-admin-title"><?php esc_html_e( 'GNN Shortner', 'gnn-shortner' ); ?></h1>
                    <p class="gnn-admin-subtitle"><?php esc_html_e( 'Manage your short URLs', 'gnn-shortner' ); ?></p>
                </div>
            </div>
            <div class="gnn-admin-header-right">
                <span class="gnn-badge"><?php echo esc_html( count( $urls ) ); ?> <?php esc_html_e( 'Links', 'gnn-shortner' ); ?></span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="gnn-stats-row">
            <div class="gnn-stat-card">
                <span class="dashicons dashicons-admin-links"></span>
                <div>
                    <span class="gnn-stat-number"><?php echo esc_html( count( $urls ) ); ?></span>
                    <span class="gnn-stat-label"><?php esc_html_e( 'Total URLs', 'gnn-shortner' ); ?></span>
                </div>
            </div>
            <div class="gnn-stat-card">
                <span class="dashicons dashicons-shield-alt"></span>
                <div>
                    <span class="gnn-stat-number">301</span>
                    <span class="gnn-stat-label"><?php esc_html_e( 'Redirect Type', 'gnn-shortner' ); ?></span>
                </div>
            </div>
            <div class="gnn-stat-card">
                <span class="dashicons dashicons-update"></span>
                <div>
                    <span class="gnn-stat-number">v<?php echo esc_html( $plugin_version ); ?></span>
                    <span class="gnn-stat-label"><?php esc_html_e( 'Plugin Version', 'gnn-shortner' ); ?></span>
                </div>
            </div>
        </div>

        <!-- URL Table -->
        <div class="gnn-table-card">
            <div class="gnn-table-header">
                <h2><?php esc_html_e( 'All Short URLs', 'gnn-shortner' ); ?></h2>
            </div>
            <table class="gnn-url-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Short URL', 'gnn-shortner' ); ?></th>
                        <th><?php esc_html_e( 'Destination URL', 'gnn-shortner' ); ?></th>
                        <th><?php esc_html_e( 'Created', 'gnn-shortner' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'gnn-shortner' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( ! empty( $urls ) && is_array( $urls ) ) : ?>
                        <?php foreach ( $urls as $url ) : ?>
                            <tr data-id="<?php echo esc_attr( $url->id ); ?>">
                                <td>
                                    <a class="gnn-short-link" href="<?php echo esc_url( home_url( '/' ) . $url->short_url ); ?>" target="_blank">
                                        <?php echo esc_html( home_url( '/' ) . $url->short_url ); ?>
                                    </a>
                                </td>
                                <td class="gnn-long-url" title="<?php echo esc_attr( $url->long_url ); ?>">
                                    <?php echo esc_html( $url->long_url ); ?>
                                </td>
                                <td class="gnn-date">
                                    <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $url->created_at ) ) ); ?>
                                </td>
                                <td class="gnn-actions">
                                    <button class="gnn-btn-icon gnn-edit-url" data-id="<?php echo esc_attr( $url->id ); ?>" title="<?php esc_attr_e( 'Edit', 'gnn-shortner' ); ?>">
                                        <span class="dashicons dashicons-edit"></span>
                                    </button>
                                    <button class="gnn-btn-icon gnn-btn-danger gnn-delete-url" data-id="<?php echo esc_attr( $url->id ); ?>" title="<?php esc_attr_e( 'Delete', 'gnn-shortner' ); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" class="gnn-empty-state">
                                <span class="dashicons dashicons-admin-links"></span>
                                <p><?php esc_html_e( 'No short URLs found. Use the shortcode [gnn_shortner] on any page to start creating links.', 'gnn-shortner' ); ?></p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ( $total_pages > 1 ) : ?>
                <div class="gnn-pagination">
                    <?php
                    echo paginate_links( [
                        'base'      => add_query_arg( 'paged', '%#%' ),
                        'format'    => '',
                        'prev_text' => __( '&laquo; Prev', 'gnn-shortner' ),
                        'next_text' => __( 'Next &raquo;', 'gnn-shortner' ),
                        'total'     => $total_pages,
                        'current'   => $current_page,
                    ] );
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Edit Modal -->
        <div id="gnn-edit-modal" class="gnn-modal-overlay" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Edit URL', 'gnn-shortner' ); ?>">
            <div class="gnn-modal-box">
                <div class="gnn-modal-header">
                    <h3><?php esc_html_e( 'Edit Short URL', 'gnn-shortner' ); ?></h3>
                    <button class="gnn-modal-close" id="gnn-edit-close" aria-label="<?php esc_attr_e( 'Close', 'gnn-shortner' ); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <form id="gnn-edit-url-form" class="gnn-modal-form">
                    <input type="hidden" name="id">
                    <div class="gnn-field">
                        <label for="gnn-edit-short-url"><?php esc_html_e( 'Short URL Slug', 'gnn-shortner' ); ?></label>
                        <div class="gnn-input-prefix">
                            <span class="gnn-prefix-text"><?php echo esc_html( home_url( '/' ) ); ?></span>
                            <input type="text" id="gnn-edit-short-url" name="short_url" required>
                        </div>
                    </div>
                    <div class="gnn-field">
                        <label for="gnn-edit-long-url"><?php esc_html_e( 'Destination URL', 'gnn-shortner' ); ?></label>
                        <input type="url" id="gnn-edit-long-url" name="long_url" placeholder="https://..." required>
                    </div>
                    <div class="gnn-modal-footer">
                        <button type="button" class="gnn-btn-secondary" id="gnn-edit-cancel"><?php esc_html_e( 'Cancel', 'gnn-shortner' ); ?></button>
                        <button type="submit" class="gnn-btn-primary"><?php esc_html_e( 'Save Changes', 'gnn-shortner' ); ?></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirm Modal -->
        <div id="gnn-delete-modal" class="gnn-modal-overlay" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Confirm Delete', 'gnn-shortner' ); ?>">
            <div class="gnn-modal-box gnn-modal-sm">
                <div class="gnn-modal-header">
                    <h3><?php esc_html_e( 'Confirm Delete', 'gnn-shortner' ); ?></h3>
                </div>
                <div class="gnn-modal-body">
                    <span class="dashicons dashicons-warning gnn-warning-icon"></span>
                    <p><?php esc_html_e( 'Are you sure you want to permanently delete this short URL? This action cannot be undone.', 'gnn-shortner' ); ?></p>
                </div>
                <div class="gnn-modal-footer">
                    <button id="gnn-delete-no" class="gnn-btn-secondary"><?php esc_html_e( 'Cancel', 'gnn-shortner' ); ?></button>
                    <button id="gnn-delete-yes" class="gnn-btn-danger"><?php esc_html_e( 'Yes, Delete', 'gnn-shortner' ); ?></button>
                </div>
            </div>
        </div>

    </div>
    <?php
}

// AJAX handler for Delete
function gnn_delete_url() {
    if ( ! check_ajax_referer( 'gnn_shortner_nonce', 'nonce', false ) ) {
        wp_send_json_error( 'Nonce verification failed.' );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized.' );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'gnn_shorturls';
    $id         = absint( $_POST['id'] );

    if ( ! $id ) {
        wp_send_json_error( 'Invalid ID.' );
    }

    $deleted = $wpdb->delete( $table_name, [ 'id' => $id ], [ '%d' ] );

    if ( $deleted ) {
        wp_send_json_success( 'URL deleted.' );
    } else {
        wp_send_json_error( 'Failed to delete URL.' );
    }
}
add_action( 'wp_ajax_gnn_delete_url', 'gnn_delete_url' );

// AJAX handler for Edit
function gnn_edit_url() {
    check_ajax_referer( 'gnn_shortner_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized.' );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'gnn_shorturls';
    $id         = absint( $_POST['id'] );
    $short_url  = sanitize_text_field( wp_unslash( $_POST['short_url'] ) );
    $long_url   = esc_url_raw( wp_unslash( $_POST['long_url'] ) );

    if ( ! $id ) {
        wp_send_json_error( 'Invalid ID.' );
    }

    if ( ! filter_var( $long_url, FILTER_VALIDATE_URL ) || ! in_array( parse_url( $long_url, PHP_URL_SCHEME ), [ 'http', 'https' ], true ) ) {
        wp_send_json_error( 'Invalid URL format. Only HTTP/HTTPS URLs are allowed.' );
    }

    // Enforce length and character whitelist on short_url
    if ( strlen( $short_url ) > 50 || ! preg_match( '/^[a-zA-Z0-9_\-]+$/', $short_url ) ) {
        wp_send_json_error( 'Invalid custom slug format. Use only letters, numbers, hyphens, and underscores (max 50 chars).' );
    }

    // Check against reserved slugs list
    $reserved = [ 'wp-admin', 'wp-login', 'wp-json', 'feed', 'sitemap', 'xmlrpc', 'wp-content', 'wp-includes', 'wp-cron', 'index.php', 'wp-login.php', 'robots.txt', 'favicon.ico' ];
    if ( in_array( strtolower( $short_url ), $reserved, true ) ) {
        wp_send_json_error( 'This custom slug is reserved. Please choose another.' );
    }

    // Check if duplicate exists for another row
    $exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE short_url = %s AND id != %d", $short_url, $id ) );
    if ( $exists ) {
        wp_send_json_error( 'This short URL already exists.' );
    }

    $wpdb->update(
        $table_name,
        [ 'short_url' => $short_url, 'long_url' => $long_url ],
        [ 'id' => $id ],
        [ '%s', '%s' ],
        [ '%d' ]
    );

    wp_send_json_success( [
        'short_url' => home_url( '/' ) . $short_url,
        'long_url'  => $long_url,
    ] );
}
add_action( 'wp_ajax_gnn_edit_url', 'gnn_edit_url' );

// Settings page
function gnn_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to view this page.', 'gnn-shortner' ) );
    }

    if ( isset( $_POST['gnn_settings'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gnn_settings_nonce'] ) ), 'gnn_settings_action' ) ) {
        $theme_mode = sanitize_text_field( wp_unslash( $_POST['frontend_theme_mode'] ?? 'auto' ) );
        if ( ! in_array( $theme_mode, [ 'auto', 'light', 'dark' ], true ) ) {
            $theme_mode = 'auto';
        }
        update_option( 'gnn_recaptcha_site_key', sanitize_text_field( wp_unslash( $_POST['recaptcha_site_key'] ) ) );
        update_option( 'gnn_recaptcha_secret_key', sanitize_text_field( wp_unslash( $_POST['recaptcha_secret_key'] ) ) );
        update_option( 'gnn_frontend_theme_mode', $theme_mode );

        // Data retention on uninstall (opt-in)
        update_option( 'gnn_delete_data_on_uninstall', isset( $_POST['delete_data_on_uninstall'] ) ? 'yes' : 'no' );

        // Password hashing and storage
        $frontend_password = isset( $_POST['frontend_password'] ) ? wp_unslash( $_POST['frontend_password'] ) : '';
        if ( $frontend_password !== '••••••••' ) {
            if ( empty( $frontend_password ) ) {
                delete_option( 'gnn_frontend_password' );
            } else {
                update_option( 'gnn_frontend_password', wp_hash_password( $frontend_password ) );
            }
        }

        echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'gnn-shortner' ) . '</p></div>';
    }
    ?>
    <div class="wrap gnn-admin-container">

        <!-- Settings Header -->
        <div class="gnn-admin-header">
            <div class="gnn-admin-header-left">
                <span class="dashicons dashicons-admin-settings gnn-admin-logo"></span>
                <div>
                    <h1 class="gnn-admin-title"><?php esc_html_e( 'GNN Shortner Settings', 'gnn-shortner' ); ?></h1>
                    <p class="gnn-admin-subtitle"><?php esc_html_e( 'Configure plugin options', 'gnn-shortner' ); ?></p>
                </div>
            </div>
        </div>

        <div class="gnn-settings-card">
            <form method="post">
                <?php wp_nonce_field( 'gnn_settings_action', 'gnn_settings_nonce' ); ?>

                <div class="gnn-settings-section">
                    <h2 class="gnn-settings-section-title">
                        <span class="dashicons dashicons-shield-alt"></span>
                        <?php esc_html_e( 'Google reCAPTCHA v2', 'gnn-shortner' ); ?>
                    </h2>
                    <p class="gnn-settings-description"><?php esc_html_e( 'Enter your reCAPTCHA keys to protect the URL shortening form from bots.', 'gnn-shortner' ); ?></p>

                    <div class="gnn-field">
                        <label for="recaptcha_site_key"><?php esc_html_e( 'Site Key', 'gnn-shortner' ); ?></label>
                        <input type="text" id="recaptcha_site_key" name="recaptcha_site_key" value="<?php echo esc_attr( get_option( 'gnn_recaptcha_site_key' ) ); ?>" placeholder="6Lc...">
                        <p class="gnn-field-hint"><?php esc_html_e( 'Your reCAPTCHA Site Key from Google Console.', 'gnn-shortner' ); ?></p>
                    </div>

                    <div class="gnn-field">
                        <label for="recaptcha_secret_key"><?php esc_html_e( 'Secret Key', 'gnn-shortner' ); ?></label>
                        <input type="password" id="recaptcha_secret_key" name="recaptcha_secret_key" value="<?php echo esc_attr( get_option( 'gnn_recaptcha_secret_key' ) ); ?>" placeholder="6Lc...">
                        <p class="gnn-field-hint"><?php esc_html_e( 'Your reCAPTCHA Secret Key. Keep this private.', 'gnn-shortner' ); ?></p>
                    </div>
                </div>

                <div class="gnn-settings-section" style="border-top: 1px solid var(--gnn-border);">
                    <h2 class="gnn-settings-section-title">
                        <span class="dashicons dashicons-admin-appearance"></span>
                        <?php esc_html_e( 'Theme Settings', 'gnn-shortner' ); ?>
                    </h2>
                    <p class="gnn-settings-description"><?php esc_html_e( 'Select the design mode for the public frontend shortening form.', 'gnn-shortner' ); ?></p>

                    <div class="gnn-field">
                        <label for="frontend_theme_mode"><?php esc_html_e( 'Frontend Theme Mode', 'gnn-shortner' ); ?></label>
                        <?php $current_mode = get_option( 'gnn_frontend_theme_mode', 'auto' ); ?>
                        <select id="frontend_theme_mode" name="frontend_theme_mode" style="width:100%;">
                            <option value="auto" <?php selected( $current_mode, 'auto' ); ?>><?php esc_html_e( 'Auto (System / Theme Dependent)', 'gnn-shortner' ); ?></option>
                            <option value="light" <?php selected( $current_mode, 'light' ); ?>><?php esc_html_e( 'Force Light Mode', 'gnn-shortner' ); ?></option>
                            <option value="dark" <?php selected( $current_mode, 'dark' ); ?>><?php esc_html_e( 'Force Dark Mode', 'gnn-shortner' ); ?></option>
                        </select>
                        <p class="gnn-field-hint"><?php esc_html_e( 'Choose between Light Mode, Dark Mode, or Auto (detects user browser preference).', 'gnn-shortner' ); ?></p>
                    </div>
                </div>

                <div class="gnn-settings-section" style="border-top: 1px solid var(--gnn-border);">
                    <h2 class="gnn-settings-section-title">
                        <span class="dashicons dashicons-lock"></span>
                        <?php esc_html_e( 'Access Control', 'gnn-shortner' ); ?>
                    </h2>
                    <p class="gnn-settings-description"><?php esc_html_e( 'Configure access protection for the frontend URL shortening form.', 'gnn-shortner' ); ?></p>

                    <div class="gnn-field">
                        <label for="frontend_password"><?php esc_html_e( 'Frontend Form Password', 'gnn-shortner' ); ?></label>
                        <?php
                        $has_password = ! empty( get_option( 'gnn_frontend_password' ) );
                        $pwd_value = $has_password ? '••••••••' : '';
                        ?>
                        <input type="password" id="frontend_password" name="frontend_password" value="<?php echo esc_attr( $pwd_value ); ?>" placeholder="<?php esc_attr_e( 'Leave empty for public access', 'gnn-shortner' ); ?>" style="width:100%;">
                        <p class="gnn-field-hint"><?php esc_html_e( 'If set, users must enter this password to view and use the URL shortening form. Leave empty for public access.', 'gnn-shortner' ); ?></p>
                    </div>
                </div>

                <div class="gnn-settings-section" style="border-top: 1px solid var(--gnn-border);">
                    <h2 class="gnn-settings-section-title">
                        <span class="dashicons dashicons-database"></span>
                        <?php esc_html_e( 'Data Management', 'gnn-shortner' ); ?>
                    </h2>
                    <p class="gnn-settings-description"><?php esc_html_e( 'Control what happens to your data when the plugin is deleted.', 'gnn-shortner' ); ?></p>

                    <div class="gnn-field">
                        <label>
                            <input type="checkbox" name="delete_data_on_uninstall" value="yes" <?php checked( get_option( 'gnn_delete_data_on_uninstall' ), 'yes' ); ?>>
                            <?php esc_html_e( 'Delete all short links and settings when the plugin is uninstalled', 'gnn-shortner' ); ?>
                        </label>
                        <p class="gnn-field-hint"><?php esc_html_e( 'If unchecked (default), your short links are kept in the database even after uninstalling, so they remain if you reinstall.', 'gnn-shortner' ); ?></p>
                    </div>
                </div>

                <div class="gnn-settings-footer">
                    <input type="submit" name="gnn_settings" class="gnn-btn-primary" value="<?php esc_attr_e( 'Save Changes', 'gnn-shortner' ); ?>">
                </div>
            </form>
        </div>

    </div>
    <?php
}