<?php

namespace Converso\Database;

class Seeder {

    public static function run() {
        self::seed_default_agent();
        self::seed_dynamic_fields();
        self::seed_settings();
    }

    private static function seed_default_agent() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'converso_agents';

        // Check if table is empty
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        if ($count == 0) {
            $agent_id = $wpdb->insert(
                $table_name,
                [
                    'uuid' => wp_generate_uuid4(),
                    'name' => 'Support Agent',
                    'phone' => '1234567890',
                    'greeting' => 'Hello! How can I help you?',
                    'location_city' => 'New York',
                    'location_country' => 'USA',
                    'is_default' => 1,
                    'is_active' => 1,
                    'created_at' => current_time('mysql')
                ]
            );

            // Seed presence for this agent
            if ($agent_id) {
                $presence_table = $wpdb->prefix . 'converso_agent_presence';
                $wpdb->insert(
                    $presence_table,
                    [
                        'agent_id' => $wpdb->insert_id,
                        'is_online' => 1,
                        'last_seen' => current_time('mysql')
                    ]
                );
            }
        }
    }

    private static function seed_dynamic_fields() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'converso_dynamic_fields';

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        if ($count == 0) {
            $fields = [
                ['name' => 'Business Hours', 'value' => '9 AM - 5 PM', "callable"=>"{business_hour}"],
                ['name' => 'Support Email', 'value' => 'support@example.com', "callable"=>"{support_email}"],
                ['name' => 'Welcome Message', 'value' => 'Welcome to our chat!', "callable"=>"{welcome_message}"]
            ];

            foreach ($fields as $field) {
                $wpdb->insert(
                    $table_name,
                    [
                        'name' => $field['name'],
                        'value' => $field['value'],
                        'callable' => $field['callable'],
                        'created_at' => current_time('mysql')
                    ]
                );
            }
        }
    }

    private static function seed_settings() {
        $settings = [
            'converso_business_name' => 'ABCD Inc.',
            'converso_business_type' => 'Ecommerce',
            'converso_cta_text' => 'Chat with us',
            'converso_primary_number'=>"9847385841",
            'converso_fallback_primary_number'=>true,
            "converso_default_greeting"=>"Hello! How can I help you?",
            "converso_offline_behavior"=>true,
            "converso_offline_message"=>"Hii, Our team is currently offline.",
            'converso_display_delay' => '1000',
            'converso_scroll_delay' => '1000',
            'converso_enable_whatsapp' => 1,
            'converso_hide_when_offline' => '',
            'converso_sap_button_style_data' => 'btn-1',
            'converso_sap_button_position_data' => 'bottom-right',
        ];

        foreach ($settings as $key => $value) {
            if (get_option($key) === false) {
                update_option($key, $value);
            }
        }
    }
}
