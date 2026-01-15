<?php

namespace Converso\Services;
use Converso\Database\Repositories\ConversoClicksRepository;

class ClicksService{
    public static function create_click($data){
        $repo = new ConversoClicksRepository();
        return $repo->create($data);
    }

    public static function get_total_clicks() {
        $repo = new ConversoClicksRepository();
        return $repo->total_clicks();
    }

    public static function get_total_unique_visitors() {
        $repo = new ConversoClicksRepository();
        return $repo->total_unique_visitors();
    }

    public static function get_total_clicks_today() {
        $repo = new ConversoClicksRepository();
        return $repo->total_clicks_today();
    }

    public static function get_most_active_page() {
        $repo = new ConversoClicksRepository();
        return $repo->get_most_active_page();
    }

    public static function get_clicks_by_agent() {
        $repo = new ConversoClicksRepository();
        return $repo->get_clicks_by_agent();
    }

    public static function get_top_pages($limit = 5) {
        $repo = new ConversoClicksRepository();
        return $repo->get_top_pages($limit);
    }

    public static function get_top_locations($type = 'country', $limit = 5) {
        $repo = new ConversoClicksRepository();
        return $repo->get_top_locations($type, $limit);
    }

    public static function get_recent_clicks($limit = 5) {
        $repo = new ConversoClicksRepository();
        return $repo->get_recent_clicks($limit);
    }

    public static function get_visitors($id){
        
    }
    public static function get_filtered_clicks($filters = []) {
        $repo = new ConversoClicksRepository();
        return $repo->get_filtered_clicks($filters);
    }
}
