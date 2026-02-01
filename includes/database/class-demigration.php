<?php

namespace Connectapre\Database;

class Demigration {

    public static function run() {
        self::demigrate_agents_table();
        self::demigrate_agent_presence_table();
        self::demigrate_clicks_table();
        self::demigrate_dynamic_fields_table();
        self::demigrate_all_options_column();
    }

    private static function demigrate_agents_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'connectapre_agents';
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name from prefix.
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    private static function demigrate_agent_presence_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'connectapre_agent_presence';
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name from prefix.
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    private static function demigrate_clicks_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'connectapre_clicks';
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name from prefix.
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    private static function demigrate_dynamic_fields_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'connectapre_dynamic_fields';
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name from prefix.
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    private static function demigrate_all_options_column(){
        $options = [
            // General Settings
            'connectapre_business_name',
            'connectapre_business_type',
            'connectapre_cta_text',
            'connectapre_display_delay',
            'connectapre_scroll_delay',
            'connectapre_hide_when_offline',
            'connectapre_enable_whatsapp',
            
            // Styling & Position
            'connectapre_sap_button_style_data',
            'connectapre_sap_button_position_data',
            
            // Data
            'connectapre_agents_data',
            'connectapre_dynamic_fields_data',
            'connectapre_wizard_completed',
        ];

        foreach ($options as $option) {
            delete_option($option);
        }

    }
}

