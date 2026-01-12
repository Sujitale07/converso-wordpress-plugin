<?php

namespace Converso\Database;

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
        $table_name = $wpdb->prefix . 'converso_agents';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    private static function demigrate_agent_presence_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'converso_agent_presence';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    private static function demigrate_clicks_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'converso_clicks';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    private static function demigrate_dynamic_fields_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'converso_dynamic_fields';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    private static function demigrate_all_options_column(){
        $options = [
            // General Settings
            'converso_business_name',
            'converso_business_type',
            'converso_cta_text',
            'converso_display_delay',
            'converso_scroll_delay',
            'converso_hide_when_offline',
            'converso_enable_whatsapp',
            
            // Styling & Position
            'converso_sap_button_style_data',
            'converso_sap_button_position_data',
            
            // Data
            'converso_agents_data',
            'converso_dynamic_fields_data',
            'converso_wizard_completed',
        ];

        foreach ($options as $option) {
            delete_option($option);
        }

    }
}