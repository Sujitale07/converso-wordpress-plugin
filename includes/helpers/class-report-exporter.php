<?php

namespace Connectapre\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connectapre\Services\ClicksService;

class ReportExporter {

    public static function init() {
        add_action('admin_post_connectapre_export_report', [__CLASS__, 'handle_export']);
    }

    public static function handle_export() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized', 'connectapre'));
        }

        check_admin_referer('connectapre_export_report_nonce');

        $filters = [
            'agent_id'   => isset($_POST['agent_id']) ? intval($_POST['agent_id']) : 0,
            'start_date' => isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '',
            'end_date'   => isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '',
        ];

        $data = ClicksService::get_filtered_clicks($filters);

        self::generate_csv($data);
    }

    private static function generate_csv($data) {
        $filename = 'connectapre-report-' . gmdate('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w'); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

        // CSV Headers
        fputcsv($output, [ // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fputcsv
            'ID',
            'Time',
            'Date',
            'Agent Name',
            'Visitor ID',
            'Page Path',
            'Country',
            'City',
            'Source'
        ]);

        if (!empty($data)) {
            foreach ($data as $row) {
                fputcsv($output, [ // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fputcsv
                    $row->id,
                    $row->created_at,
                    $row->stat_date,
                    $row->agent_name ?: 'System/Fallback',
                    $row->visitor_id,
                    $row->page_path,
                    $row->location_country,
                    $row->location_city,
                    $row->source
                ]);
            }
        }

        fclose($output); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        exit;
    }
}

