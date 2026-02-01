<?php

namespace Connectapre\Services;

use Connectapre\Database\Repositories\ConnectapreAgentsRepository;

class AgentsService
{
    /**
     * Get agents with pagination and filtering
     * Returns the structure expected by the frontend/module
     */
    public static function get_agents($filters = [])
    {
        $repo = new ConnectapreAgentsRepository();

        $search = isset($filters['search']) ? sanitize_text_field($filters['search']) : '';
        $status = isset($filters['status']) ? sanitize_text_field($filters['status']) : ''; // 'online', 'offline', or empty
        $sort   = isset($filters['sort']) ? sanitize_text_field($filters['sort']) : '';
        
        $page  = isset($filters['page']) ? max(1, (int) $filters['page']) : 1;
        $limit = isset($filters['limit']) ? max(1, (int) $filters['limit']) : 5;
        $offset = ($page - 1) * $limit;

        // Fetch data
        $agents = $repo->all($search, $status, $sort, $limit, $offset);
        $total_agents = $repo->count($search, $status);
        $total_pages  = (int) ceil($total_agents / $limit);

        return [
            "agents"       => $agents,
            "total_pages"  => $total_pages,
            "total_agents" => $total_agents,
            "page"         => $page,
            "limit"        => $limit,
            "search"       => $search,
            "status"       => $status,
            "sort"         => $sort
        ];
    }

    public static function create_agent($data)
    {
        $repo = new ConnectapreAgentsRepository();

        // Handle Default Logic
        if (!empty($data['is_default']) && $data['is_default'] == 1) {
            $repo->reset_defaults();
        }

        // Check if this is the first agent, make it default automatically?
        if ($repo->count() === 0) {
            $data['is_default'] = 1;
        }

        return $repo->create($data);
    }

    public static function update_agent($id, $data)
    {
        $repo = new ConnectapreAgentsRepository();

        // Handle Default Logic
        if (!empty($data['is_default']) && $data['is_default'] == 1) {
            $repo->reset_defaults();
        }

        

        $repo->update($id, $data);
    }

    public static function delete_agent($id)
    {
        $repo = new ConnectapreAgentsRepository();
        $agents = $repo->count();
        $agent = $repo->find($id);

        
        if ($agents == 1) {
            return false;
        }
        
        if($agent['is_default'] == 1) {
            $repo->reset_defaults();
        }
        
        return $repo->delete($id);
    }
}

