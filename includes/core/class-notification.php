<?php

namespace Connectapre\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Notification {

    const TRANSIENT_KEY = 'connectapre_flash_notifications';

    public static function init() {
        add_action('admin_footer', [__CLASS__, 'render']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    public static function enqueue_assets($hook) {
        if ('toplevel_page_connectapre' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'connectapre-notification-css',
            CONNECTAPRE_PLUGIN_URL . 'assets/css/notification.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'connectapre-notification-js',
            CONNECTAPRE_PLUGIN_URL . 'assets/js/notification.js',
            [],
            '1.0.0',
            true
        );

        $user_id = get_current_user_id();
        $key = self::TRANSIENT_KEY . '_' . $user_id;
        $notifications = get_transient($key);

        if (!empty($notifications)) {
            wp_add_inline_script(
                'connectapre-notification-js',
                'const ConnectapreMessages = ' . wp_json_encode($notifications) . ';',
                'before'
            );
            delete_transient($key);
        }
    }

    public static function success($message, $title = 'Success') {
        self::add('success', $message, $title);
    }

    public static function error($message, $title = 'Error') {
        self::add('error', $message, $title);
    }

    public static function warning($message, $title = 'Warning') {
        self::add('warning', $message, $title);
    }

    public static function info($message, $title = 'Info') {
        self::add('info', $message, $title);
    }

    private static function add($type, $message, $title) {
        $user_id = get_current_user_id();
        $key = self::TRANSIENT_KEY . '_' . $user_id;
        
        $notifications = get_transient($key);
        if (!is_array($notifications)) {
            $notifications = [];
        }

        $notifications[] = [
            'type'    => $type,
            'message' => $message,
            'title'   => $title
        ];

        set_transient($key, $notifications, 60); // Expires in 60 seconds
    }

    public static function render() {
        $screen = get_current_screen();
        if ( ! $screen || 'toplevel_page_connectapre' !== $screen->id ) {
            return;
        }
        ?>
        <div id="connectapre-toast-container" class="connectapre-toast-container"></div>
        <?php
    }
}

