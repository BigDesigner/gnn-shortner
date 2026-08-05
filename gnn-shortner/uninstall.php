<?php
/**
 * GNN Shortner uninstall cleanup.
 *
 * WordPress bu dosyayı yalnızca eklenti kaldırılırken (delete) çalıştırır.
 */

// Doğrudan erişimi ve yanlış bağlamda çalışmayı engelle
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Eklenti ayarlarını her zaman temizle (yeniden yapılandırılabilir veriler)
delete_option( 'gnn_recaptcha_site_key' );
delete_option( 'gnn_recaptcha_secret_key' );
delete_option( 'gnn_frontend_theme_mode' );
delete_option( 'gnn_frontend_password' );
delete_transient( 'gnn_shortner_github_update_check' );

// Kısa link tablosu KULLANICI VERİSİDİR; yalnızca kullanıcı açıkça istediyse sil
if ( 'yes' === get_option( 'gnn_delete_data_on_uninstall' ) ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gnn_shorturls';
    // Tablo adı $wpdb->prefix ile oluşturulur (kullanıcı girdisi değil, güvenli)
    $wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" );
}

delete_option( 'gnn_delete_data_on_uninstall' );
