<?php

namespace Converso\Database;

class Migrations {

    public static function run() {
        self::create_agents_table();
        self::create_agent_presence_table();
        self::create_clicks_table();
        self::create_dynamic_fields_table();
    }

    private static function create_agents_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'converso_agents';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            uuid char(36) NOT NULL,
            name varchar(191) NOT NULL,
            phone varchar(50) DEFAULT NULL,
            redirect_url varchar(255) DEFAULT NULL,
            photo_url varchar(255) DEFAULT NULL,
            greeting varchar(255) DEFAULT NULL,
            location_city varchar(100) DEFAULT NULL,
            location_state varchar(100) DEFAULT NULL,
            location_country varchar(100) DEFAULT NULL,
            is_default tinyint(1) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY uniq_uuid (uuid),
            KEY idx_location (location_country, location_state, location_city),
            KEY idx_active (is_active)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function create_agent_presence_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'converso_agent_presence';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            agent_id bigint(20) UNSIGNED NOT NULL,
            is_online tinyint(1) DEFAULT 0,
            last_seen datetime DEFAULT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (agent_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function create_clicks_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'converso_clicks';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            agent_id bigint(20) UNSIGNED NOT NULL,
            visitor_id varchar(100) NOT NULL,
            page_path varchar(191) NOT NULL,
            location_country varchar(100) DEFAULT NULL,
            location_state varchar(100) DEFAULT NULL,
            location_city varchar(100) DEFAULT NULL,
            stat_date date NOT NULL,
            source varchar(20) DEFAULT 'widget',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY idx_date (stat_date),
            KEY idx_agent (agent_id),
            KEY idx_page (page_path),
            KEY idx_location (location_country, location_city)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function create_dynamic_fields_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'converso_dynamic_fields';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(191) NOT NULL,
            value varchar(191) NOT NULL,
            callable varchar(191) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
