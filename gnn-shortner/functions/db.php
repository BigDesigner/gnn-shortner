<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function gnn_create_tables() {
	global $wpdb;
	$table_name      = $wpdb->prefix . 'gnn_shorturls';
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
	dbDelta( $sql );
}

function gnn_get_long_url( $short_url ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'gnn_shorturls';
	return $wpdb->get_var( $wpdb->prepare( "SELECT long_url FROM $table_name WHERE short_url = %s", $short_url ) );
}

function gnn_save_url( $short_url, $long_url ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'gnn_shorturls';
	// Dönüş: eklenen satır sayısı ya da hata durumunda false (UNIQUE çakışması dahil)
	return $wpdb->insert(
		$table_name,
		[
			'short_url' => sanitize_text_field( $short_url ),
			'long_url'  => esc_url_raw( $long_url ),
		],
		[ '%s', '%s' ]
	);
}