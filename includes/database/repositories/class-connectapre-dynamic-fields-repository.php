<?php

namespace Connectapre\Database\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class ConnectapreDynamicFieldsRepository
{
    private $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'connectapre_dynamic_fields';
    }

    public function all($search = "", $sort = "", $limit = 10, $offset = 0)
    {
        global $wpdb;

        $where = ["1=1"];
        $args = [];

        if (!empty($search)) {
            $where[] = "(name LIKE %s OR value LIKE %s OR callable LIKE %s)";
            $args[] = '%' . $wpdb->esc_like($search) . '%';
            $args[] = '%' . $wpdb->esc_like($search) . '%';
            $args[] = '%' . $wpdb->esc_like($search) . '%';
        }

        $order_by = "created_at DESC"; // Default
        if ($sort === 'name') {
            $order_by = "name ASC";
        } elseif ($sort === 'oldest') {
             $order_by = "created_at ASC";
        }

        $where_sql = implode(' AND ', $where);
        
        $args[] = (int) $limit;
        $args[] = (int) $offset;

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE {$where_sql} ORDER BY {$order_by} LIMIT %d OFFSET %d", $args), ARRAY_A);
    }

    public function count($search = "")
    {
        global $wpdb;
        $where = ["1=1"];
        $args = [];

        if (!empty($search)) {
            $where[] = "(name LIKE %s OR value LIKE %s OR callable LIKE %s)";
            $args[] = '%' . $wpdb->esc_like($search) . '%';
            $args[] = '%' . $wpdb->esc_like($search) . '%';
            $args[] = '%' . $wpdb->esc_like($search) . '%';
        }

        $where_sql = implode(' AND ', $where);
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_sql}";
        
        if (!empty($args)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            return (int) $wpdb->get_var($wpdb->prepare($sql, $args));
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return (int) $wpdb->get_var($sql);
    }

    public function create($data)
    {
        global $wpdb;
        $wpdb->insert($this->table_name, $data);
        return $wpdb->insert_id;
    }

    public function update($id, $data)
    {
        global $wpdb;
        return $wpdb->update($this->table_name, $data, ['id' => $id]);
    }

    public function delete($id)
    {
        global $wpdb;
        return $wpdb->delete($this->table_name, ['id' => $id]);
    }

    public function find($id)
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id), ARRAY_A);
    }

    public function find_by_callable($callable)
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE callable = %s", $callable), ARRAY_A);
    }
}

