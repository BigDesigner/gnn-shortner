<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function gnn_display_shortner_form() {
    ?>
    <div class="gnn-shortner-fixed-wrapper">
        <div id="gnn-result"></div>

        <form id="gnn-shortner-form" method="post">
            <div class="gnn-form-row">
                <label for="long_url"><?php _e('Destination URL', 'gnn-shortner'); ?></label>
                <input type="url" name="long_url" id="long_url" placeholder="https://..." required>
            </div>

            <div class="gnn-form-row">
                <label for="custom_short_url"><?php _e('Custom Slug (Optional)', 'gnn-shortner'); ?></label>
                <input type="text" name="custom_short_url" id="custom_short_url" placeholder="my-link">
            </div>

            <div class="gnn-form-row gnn-captcha-row">
                <div class="g-recaptcha" data-sitekey="<?php echo esc_attr(get_option('gnn_recaptcha_site_key')); ?>" data-theme="light"></div>
            </div>

            <div class="gnn-form-row">
                <button type="submit" id="gnn-submit-btn" class="button button-primary gnn-btn-fixed">
                    <span class="btn-text"><?php _e('Shorten URL', 'gnn-shortner'); ?></span>
                    <span class="btn-loader" style="display:none;"><?php _e('Processing...', 'gnn-shortner'); ?></span>
                </button>
            </div>
        </form>
    </div>
    <?php
}

// ... (rest of the file remains same)
function gnn_process_shortner() {
    if (!check_ajax_referer('gnn_shortner_nonce', 'nonce', false)) {
        wp_send_json_error('Security check failed.');
    }

    $long_url = isset($_POST['long_url']) ? esc_url_raw($_POST['long_url']) : '';
    $custom_short_url = isset($_POST['custom_short_url']) ? sanitize_text_field($_POST['custom_short_url']) : '';
    $recaptcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : '';

    $secret_key = get_option('gnn_recaptcha_secret_key');
    if ($secret_key) {
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret'   => $secret_key,
                'response' => $recaptcha_response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ]
        ]);
        $result = json_decode(wp_remote_retrieve_body($response));
        if (!$result->success) {
            wp_send_json_error('reCAPTCHA verification failed.');
        }
    }

    if (!filter_var($long_url, FILTER_VALIDATE_URL)) {
        wp_send_json_error('Invalid URL format.');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'gnn_shorturls';

    $short_url = $custom_short_url ?: wp_generate_password(6, false);
    if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE short_url = %s", $short_url))) {
        wp_send_json_error('Short URL already exists.');
    }

    gnn_save_url($short_url, $long_url);
    $full_short_url = home_url('/') . $short_url;
    wp_send_json_success(['short_url' => $full_short_url]);
}
add_action('wp_ajax_gnn_shortner', 'gnn_process_shortner');
add_action('wp_ajax_nopriv_gnn_shortner', 'gnn_process_shortner');