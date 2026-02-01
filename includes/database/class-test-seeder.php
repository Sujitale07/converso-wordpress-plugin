<?php

namespace Connectapre\Database;

class TestSeeder {

    public static function seed_test_data($count = 15000) {
        global $wpdb;
        $agents_table = $wpdb->prefix . 'connectapre_agents';
        $clicks_table = $wpdb->prefix . 'connectapre_clicks';

        // Get existing agents
        $agents = $wpdb->get_results("SELECT id FROM $agents_table");

        if (empty($agents)) {
            // Seed at least one agent if none exist
            Seeder::run();
            $agents = $wpdb->get_results("SELECT id FROM $agents_table");
        }

        $agent_ids = array_column($agents, 'id');
        $countries = ['USA', 'UK', 'Canada', 'Australia', 'Germany', 'France', 'India', 'Japan', 'Brazil', 'Nepal'];
        $cities = ['New York', 'London', 'Toronto', 'Sydney', 'Berlin', 'Paris', 'Mumbai', 'Tokyo', 'Sao Paulo', 'Kathmandu'];
        $pages = ['/', '/shop/', '/contact/', '/about-us/', '/services/', '/blog/'];
        $sources = ['widget', 'direct', 'qr'];

        for ($i = 0; $i < $count; $i++) {
            $agent_id = $agent_ids[array_rand($agent_ids)];
            $country = $countries[array_rand($countries)];
            $city = $cities[array_rand($cities)];
            $page = $pages[array_rand($pages)];
            $source = $sources[array_rand($sources)];
            
            // Random date within the last 30 days
            $days_ago = rand(0, 30);
            $date = date('Y-m-d', strtotime("-$days_ago days"));
            $created_at = date('Y-m-d H:i:s', strtotime("-$days_ago days" . " +" . rand(0, 23) . " hours" . " +" . rand(0, 59) . " minutes"));

            $wpdb->insert(
                $clicks_table,
                [
                    'agent_id' => $agent_id,
                    'visitor_id' => 'test-visitor-' . uniqid(),
                    'page_path' => $page,
                    'location_country' => $country,
                    'location_city' => $city,
                    'stat_date' => $date,
                    'source' => $source,
                    'created_at' => $created_at
                ]
            );
        }

        return $count;
    }

    public static function seed_extra_agents($count = 500) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'connectapre_agents';
        $presence_table = $wpdb->prefix . 'connectapre_agent_presence';

        $names = ['John Doe', 'Jane Smith', 'Alex Johnson', 'Sarah Wilson', 'Michael Brown'];
        $cities = ['Miami', 'Chicago', 'Los Angeles', 'Seattle', 'Austin'];

        for ($i = 0; $i < $count; $i++) {
            $name = $names[$i % count($names)] . ' ' . ($i + 1);
            $wpdb->insert(
                $table_name,
                [
                    'uuid' => wp_generate_uuid4(),
                    'name' => $name,
                    'phone' => '98' . rand(10000000, 99999999),
                    'greeting' => "Hi, I'm $name. How can I help?",
                    'location_city' => $cities[$i % count($cities)],
                    'location_country' => 'USA',
                    'is_default' => 0,
                    'is_active' => 1,
                    'created_at' => current_time('mysql')
                ]
            );

            $agent_id = $wpdb->insert_id;
            $wpdb->insert(
                $presence_table,
                [
                    'agent_id' => $agent_id,
                    'is_online' => rand(0, 1),
                    'last_seen' => current_time('mysql')
                ]
            );
        }
    }
}
