<?php

namespace Converso\Services;

use Converso\Database\Repositories\ConversoDynamicFieldsRepository;

class DynamicFieldsService
{
    /**
     * Get fields with pagination and filtering
     */
    public static function get_fields($filters = [])
    {
        $repo = new ConversoDynamicFieldsRepository();

        $search = isset($filters['search']) ? sanitize_text_field($filters['search']) : '';
        $sort   = isset($filters['sort']) ? sanitize_text_field($filters['sort']) : '';
        
        $page  = isset($filters['page']) ? max(1, (int) $filters['page']) : 1;
        $limit = isset($filters['limit']) ? max(1, (int) $filters['limit']) : 10;
        $offset = ($page - 1) * $limit;

        // Fetch data
        $fields = $repo->all($search, $sort, $limit, $offset);
        $total_fields = $repo->count($search);
        $total_pages  = (int) ceil($total_fields / $limit);

        return [
            "fields"       => $fields,
            "total_pages"  => $total_pages,
            "total_fields" => $total_fields,
            "page"         => $page,
            "limit"        => $limit,
            "search"       => $search,
            "sort"         => $sort
        ];
    }

    public static function create_field($data)
    {
        $repo = new ConversoDynamicFieldsRepository();
        
        // Auto-generate callable if empty
        if (empty($data['callable'])) {
            $slug = sanitize_title($data['name']);
            $slug = str_replace('-', '_', $slug);
            $data['callable'] = '{' . $slug . '}';
        }

        // Check for duplicate callable
        $existing = $repo->find_by_callable($data['callable']);
        if ($existing) {
            return new \WP_Error('duplicate_callable', 'Callable name must be unique.');
        }

        return $repo->create($data);
    }

    public static function update_field($id, $data)
    {
        $repo = new ConversoDynamicFieldsRepository();

        // Check uniqueness if callable is being updated
        if (isset($data['callable'])) {
            $current = $repo->find($id);
            if ($current && $current['callable'] !== $data['callable']) {
                $existing = $repo->find_by_callable($data['callable']);
                if ($existing) {
                     return new \WP_Error('duplicate_callable', 'Callable name must be unique.');
                }
            }
        }

        return $repo->update($id, $data);
    }

    public static function delete_field($id)
    {
        $repo = new ConversoDynamicFieldsRepository();
        return $repo->delete($id);
    }
}
