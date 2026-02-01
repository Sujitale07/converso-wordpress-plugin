<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://connectapre.com
 * @since      1.0.0
 *
 * @package    Connectapre
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// 1. Delete Options
$connectapre_options = [
	'connectapre_business_name',
	'connectapre_business_type',
	'connectapre_cta_text',
	'connectapre_display_delay',
	'connectapre_scroll_delay',
	'connectapre_enable_whatsapp',
	'connectapre_primary_number',
	'connectapre_fallback_primary_number',
	'connectapre_default_greeting',
	'connectapre_offline_behavior',
	'connectapre_offline_message',
	'connectapre_sap_button_style_data',
	'connectapre_sap_button_position_data',
    'connectapre_db_version'
];

foreach ( $connectapre_options as $connectapre_option ) {
	delete_option( $connectapre_option );
	delete_site_option( $connectapre_option );
}

// 2. Drop Tables
$connectapre_tables = [
	$wpdb->prefix . 'connectapre_agents',
	$wpdb->prefix . 'connectapre_agent_presence',
	$wpdb->prefix . 'connectapre_clicks',
	$wpdb->prefix . 'connectapre_dynamic_fields'
];

foreach ( $connectapre_tables as $connectapre_table ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS $connectapre_table" );
}

