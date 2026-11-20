<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sabit pencere (fixed-window) IP rate limit.
 * Pencere başlangıcı transient içinde saklanır; her istekte TTL sıfırlanmadığı için
 * saldırgan kayan pencereyle limiti sürekli sürükleyemez.
 *
 * @return bool true = limit aşıldı (isteği reddet)
 */
function gnn_check_rate_limit( $key, $limit = 10, $window = 60 ) {
    $now  = time();
    $data = get_transient( $key );
    if ( ! is_array( $data ) || ! isset( $data['count'], $data['reset'] ) || $now > $data['reset'] ) {
        $data = [ 'count' => 0, 'reset' => $now + $window ];
    }
    if ( $data['count'] >= $limit ) {
        return true;
    }
    $data['count']++;
    set_transient( $key, $data, max( 1, $data['reset'] - $now ) );
    return false;
}

/**
 * Render Google reCAPTCHA widget and auto-theme detector once.
 */
function gnn_render_recaptcha_block( $theme_mode ) {
    $site_key = get_option( 'gnn_recaptcha_site_key' );
    if ( empty( $site_key ) ) {
        return;
    }
    ?>
    <div id="gnn-recaptcha-container" class="gnn-captcha-row">
        <div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $site_key ); ?>" data-theme="<?php echo ( 'dark' === $theme_mode ) ? 'dark' : 'light'; ?>"></div>
        <?php if ( 'auto' === $theme_mode ) : ?>
            <script>
                (function() {
                    var recaptchaDiv = document.querySelector('#gnn-recaptcha-container .g-recaptcha');
                    if (recaptchaDiv) {
                        var isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                        if (!isDark) {
                            var body = document.body;
                            if (body.classList.contains('dark') ||
                                body.classList.contains('dark-mode') ||
                                body.classList.contains('theme-dark') ||
                                body.getAttribute('data-theme') === 'dark' ||
                                document.querySelector('.dark') ||
                                document.querySelector('.dark-mode') ||
                                document.querySelector('.theme-dark')) {
                                isDark = true;
                            }
                        }
                        if (isDark) {
                            recaptchaDiv.setAttribute('data-theme', 'dark');
                        }
                    }
                })();
            </script>
        <?php endif; ?>
    </div>
    <?php
}

function gnn_display_shortner_form() {
    $theme_mode = get_option( 'gnn_frontend_theme_mode', 'auto' );
    $theme_class = 'gnn-theme-' . esc_attr( $theme_mode );

    // Access Control check
    $stored_hash = get_option( 'gnn_frontend_password' );
    $unlocked = true;
    if ( ! empty( $stored_hash ) ) {
        if ( ! is_admin() ) {
            if ( ! defined( 'DONOTCACHEPAGE' ) ) {
                define( 'DONOTCACHEPAGE', true );
            }
            if ( ! headers_sent() ) {
                nocache_headers();
            }
        }
        $unlocked = false;
        $expected_token = wp_hash( $stored_hash . wp_salt() );
        $cookie_token   = ( isset( $_COOKIE['gnn_unlocked'] ) && is_string( $_COOKIE['gnn_unlocked'] ) )
            ? wp_unslash( $_COOKIE['gnn_unlocked'] )
            : '';
        if ( '' !== $cookie_token && hash_equals( $expected_token, $cookie_token ) ) {
            $unlocked = true;
        }
    }
    ?>
    <div class="gnn-shortner-fixed-wrapper <?php echo esc_attr( $theme_class ); ?>">
        <div id="gnn-result" aria-live="polite"></div>

        <?php if ( $unlocked ) : ?>
            <!-- Shortener Form -->
            <form id="gnn-shortner-form" method="post">
                <div class="gnn-form-row">
                    <label for="long_url"><?php esc_html_e('Destination URL', 'gnn-shortner'); ?></label>
                    <input type="url" name="long_url" id="long_url" placeholder="https://..." required>
                </div>

                <div class="gnn-form-row">
                    <label for="custom_short_url"><?php esc_html_e('Custom Slug (Optional)', 'gnn-shortner'); ?></label>
                    <input type="text" name="custom_short_url" id="custom_short_url" placeholder="my-link">
                </div>

                <?php
                if ( empty( $stored_hash ) ) {
                    gnn_render_recaptcha_block( $theme_mode );
                }
                ?>

                <div class="gnn-form-row">
                    <button type="submit" id="gnn-submit-btn" class="button button-primary gnn-btn-fixed">
                        <span class="btn-text"><?php esc_html_e('Shorten URL', 'gnn-shortner'); ?></span>
                        <span class="btn-loader" style="display:none;"><?php esc_html_e('Processing...', 'gnn-shortner'); ?></span>
                    </button>
                </div>
            </form>
        <?php else : ?>
            <!-- Password Entry Form -->
            <form id="gnn-password-form" method="post">
                <div class="gnn-form-row">
                    <label for="frontend_password_input"><?php esc_html_e('Password', 'gnn-shortner'); ?></label>
                    <input type="password" name="password" id="frontend_password_input" placeholder="••••••••" required>
                </div>

                <?php gnn_render_recaptcha_block( $theme_mode ); ?>

                <div class="gnn-form-row">
                    <button type="submit" id="gnn-password-submit-btn" class="button button-primary gnn-btn-fixed">
                        <span class="btn-text"><?php esc_html_e('Login', 'gnn-shortner'); ?></span>
                        <span class="btn-loader" style="display:none;"><?php esc_html_e('Processing...', 'gnn-shortner'); ?></span>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <?php
}

function gnn_verify_password() {
    if (!check_ajax_referer('gnn_shortner_nonce', 'nonce', false)) {
        wp_send_json_error(__('Security check failed.', 'gnn-shortner'));
    }

    // IP tabanlı sabit pencere rate limit: 60 saniyede en fazla 10 istek
    $ip  = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? ''));
    $key = 'gnn_pass_rl_' . md5($ip);
    if ( gnn_check_rate_limit( $key ) ) {
        wp_send_json_error(__('Too many attempts. Please try again later.', 'gnn-shortner'));
    }

    // Parola yalnızca hash'lenip karşılaştırılır, hiç echo edilmez; sanitize entropiyi düşüreceğinden sadece unslash uygula
    $password = (isset($_POST['password']) && is_string($_POST['password'])) ? wp_unslash($_POST['password']) : '';
    $recaptcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) : '';

    // Verify reCAPTCHA
    $secret_key = get_option('gnn_recaptcha_secret_key');
    if ($secret_key) {
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret'   => $secret_key,
                'response' => $recaptcha_response,
                'remoteip' => $ip,
            ]
        ]);
        if (is_wp_error($response)) {
            wp_send_json_error(__('reCAPTCHA verification failed.', 'gnn-shortner'));
        }
        $result = json_decode(wp_remote_retrieve_body($response));
        if (!$result || empty($result->success)) {
            wp_send_json_error(__('reCAPTCHA verification failed.', 'gnn-shortner'));
        }
    }

    $stored_hash = get_option('gnn_frontend_password');
    if (empty($stored_hash)) {
        wp_send_json_success();
    }

    if (wp_check_password($password, $stored_hash)) {
        $expected_token = wp_hash($stored_hash . wp_salt());
        // TLS-terminating proxy arkasında is_ssl() false dönebilir; istemci HTTPS ise Secure bayrağını yine de ayarla
        $is_secure = is_ssl();
        if ( ! $is_secure && isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) {
            $is_secure = 'https' === strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) );
        }
        // Set secure HttpOnly SameSite=Lax session cookie with root path for robustness across subfolders/admin-ajax
        setcookie('gnn_unlocked', $expected_token, [
            'expires'  => 0,
            'path'     => '/',
            'domain'   => COOKIE_DOMAIN,
            'secure'   => $is_secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        // Clear rate limit transient on successful verification
        delete_transient($key);
        wp_send_json_success();
    } else {
        wp_send_json_error(__('Incorrect password.', 'gnn-shortner'));
    }
}
add_action('wp_ajax_gnn_verify_password', 'gnn_verify_password');
add_action('wp_ajax_nopriv_gnn_verify_password', 'gnn_verify_password');

function gnn_process_shortner() {
    if (!check_ajax_referer('gnn_shortner_nonce', 'nonce', false)) {
        wp_send_json_error(__('Security check failed.', 'gnn-shortner'));
    }

    // IP tabanlı sabit pencere rate limit: 60 saniyede en fazla 10 istek
    $ip  = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? ''));
    $key = 'gnn_rl_' . md5($ip);
    if ( gnn_check_rate_limit( $key ) ) {
        wp_send_json_error(__('Too many requests. Please try again later.', 'gnn-shortner'));
    }

    // Bypass check: if password is set, verify the cookie
    $stored_hash = get_option( 'gnn_frontend_password' );
    if ( ! empty( $stored_hash ) ) {
        $expected_token = wp_hash( $stored_hash . wp_salt() );
        $cookie_token   = ( isset( $_COOKIE['gnn_unlocked'] ) && is_string( $_COOKIE['gnn_unlocked'] ) )
            ? wp_unslash( $_COOKIE['gnn_unlocked'] )
            : '';
        if ( '' === $cookie_token || ! hash_equals( $expected_token, $cookie_token ) ) {
            wp_send_json_error( __('Access denied. Please enter the correct password.', 'gnn-shortner') );
        }
    } else {
        // Password is disabled: verify reCAPTCHA if keys are set
        $secret_key = get_option('gnn_recaptcha_secret_key');
        if ($secret_key) {
            $recaptcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) : '';
            $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret'   => $secret_key,
                    'response' => $recaptcha_response,
                    'remoteip' => $ip,
                ]
            ]);
            if (is_wp_error($response)) {
                wp_send_json_error(__('reCAPTCHA verification failed.', 'gnn-shortner'));
            }
            $result = json_decode(wp_remote_retrieve_body($response));
            if (!$result || empty($result->success)) {
                wp_send_json_error(__('reCAPTCHA verification failed.', 'gnn-shortner'));
            }
        }
    }

    $long_url = isset($_POST['long_url']) ? esc_url_raw(wp_unslash($_POST['long_url'])) : '';
    $custom_short_url = isset($_POST['custom_short_url']) ? sanitize_text_field(wp_unslash($_POST['custom_short_url'])) : '';

    // Force scheme validation on Destination URL
    if (!filter_var($long_url, FILTER_VALIDATE_URL) || !in_array(wp_parse_url($long_url, PHP_URL_SCHEME), ['http', 'https'], true)) {
        wp_send_json_error(__('Invalid URL format. Only HTTP/HTTPS URLs are allowed.', 'gnn-shortner'));
    }

    // Validate Custom Slug
    if ($custom_short_url) {
        // Enforce length and character whitelist
        if (strlen($custom_short_url) > 50 || !preg_match('/^[a-zA-Z0-9_\-]+$/', $custom_short_url)) {
            wp_send_json_error(__('Invalid custom slug format. Use only letters, numbers, hyphens, and underscores (max 50 chars).', 'gnn-shortner'));
        }

        // Check against reserved slugs list
        $reserved = ['wp-admin', 'wp-login', 'wp-json', 'feed', 'sitemap', 'xmlrpc', 'wp-content', 'wp-includes', 'wp-cron', 'index.php', 'wp-login.php', 'robots.txt', 'favicon.ico'];
        if (in_array(strtolower($custom_short_url), $reserved, true)) {
            wp_send_json_error(__('This custom slug is reserved. Please choose another.', 'gnn-shortner'));
        }
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'gnn_shorturls';

    $short_url = $custom_short_url ?: wp_generate_password(6, false);
    if ($wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM %i WHERE short_url = %s', $table_name, $short_url))) {
        wp_send_json_error(__('Short URL already exists.', 'gnn-shortner'));
    }

    // Insert başarısız olursa (ör. eşzamanlı istekte UNIQUE çakışması) yanlış link döndürme
    $inserted = gnn_save_url($short_url, $long_url);
    if ( ! $inserted ) {
        wp_send_json_error(__('Short URL already exists.', 'gnn-shortner'));
    }

    $full_short_url = home_url('/') . $short_url;
    wp_send_json_success(['short_url' => $full_short_url]);
}
add_action('wp_ajax_gnn_shortner', 'gnn_process_shortner');
add_action('wp_ajax_nopriv_gnn_shortner', 'gnn_process_shortner');

// Warning admin notice if reCAPTCHA is not configured
function gnn_recaptcha_missing_admin_notice() {
    if (!get_option('gnn_recaptcha_secret_key') && current_user_can('manage_options')) {
        echo '<div class="notice notice-warning is-dismissible"><p>';
        echo wp_kses(
            sprintf(
                __('<strong>GNN Shortner:</strong> Google reCAPTCHA is not configured. The public URL shortening form is currently unprotected from bot abuse. Please configure it in the <a href="%s">plugin settings</a>.', 'gnn-shortner'),
                esc_url(admin_url('admin.php?page=gnn-shortner-settings'))
            ),
            [
                'strong' => [],
                'a'      => [ 'href' => [] ],
            ]
        );
        echo '</p></div>';
    }
}
add_action('admin_notices', 'gnn_recaptcha_missing_admin_notice');