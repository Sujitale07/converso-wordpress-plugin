<?php

namespace Converso\Database\Repositories;

class ConversoClicksRepository
{
    private $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'converso_clicks';        
    }

    public function create($data)
    {
        global $wpdb;
        // Map keys if necessary, or assume $data keys match column names
        $wpdb->insert($this->table_name, $data);
        return $wpdb->insert_id;
    }

    public function get_all()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->table_name}");
    }

    public function get_all_visitors(){

        global $wpdb;

        $sql = "SELECT * FROM {$this->table_name} GROUP BY visitor_id";

        return $wpdb->get_results($sql);
    }

    public function get_visitors_by_page(){
        global $wpdb;

        $sql = "SELECT page_path, COUNT(DISTINCT visitor_id) as visitors FROM {$this->table_name} GROUP BY page_path";

        return $wpdb->get_results($sql);
    }

    public function total_clicks(){
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
    }

    public function total_unique_visitors(){
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(DISTINCT visitor_id) FROM {$this->table_name}");
    }

    public function get_clicks_by_agent() {
        global $wpdb;
        $agents_table = $wpdb->prefix . 'converso_agents';
        $sql = "SELECT a.name, COUNT(c.id) as clicks 
                FROM {$this->table_name} c
                LEFT JOIN {$agents_table} a ON c.agent_id = a.id
                GROUP BY c.agent_id
                ORDER BY clicks DESC";
        return $wpdb->get_results($sql);
    }

    public function total_clicks_today(){
        global $wpdb;
        $today = current_time('Y-m-d');
        return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE stat_date = %s", $today));
    }

    public function get_most_active_page(){
        global $wpdb;
        $sql = "SELECT page_path, COUNT(*) as count 
                FROM {$this->table_name} 
                GROUP BY page_path 
                ORDER BY count DESC 
                LIMIT 1";
        return $wpdb->get_row($sql);
    }

    public function get_top_pages($limit = 5) {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT page_path, COUNT(*) as count 
             FROM {$this->table_name} 
             GROUP BY page_path 
             ORDER BY count DESC 
             LIMIT %d",
            $limit
        );
        return $wpdb->get_results($sql);
    }

    public function get_top_locations($type = 'country', $limit = 5) {
        global $wpdb;
        $column = ($type === 'city') ? 'location_city' : 'location_country';
        $sql = $wpdb->prepare(
            "SELECT {$column} as label, COUNT(*) as count 
             FROM {$this->table_name} 
             WHERE {$column} IS NOT NULL AND {$column} != '' 
             GROUP BY {$column} 
             ORDER BY count DESC 
             LIMIT %d",
            $limit
        );
        return $wpdb->get_results($sql);
    }

    public function get_recent_clicks($limit = 5) {
        global $wpdb;
        $agents_table = $wpdb->prefix . 'converso_agents';
        $sql = $wpdb->prepare(
            "SELECT c.*, a.name as agent_name 
             FROM {$this->table_name} c 
             LEFT JOIN {$agents_table} a ON c.agent_id = a.id 
             ORDER BY c.created_at DESC 
             LIMIT %d",
            $limit
        );
        return $wpdb->get_results($sql);
    }

}
