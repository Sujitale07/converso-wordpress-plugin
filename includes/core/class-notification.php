<?php

namespace Converso\Core;

class Notification {

    const TRANSIENT_KEY = 'converso_flash_notifications';

    public static function init() {
        add_action('admin_footer', [__CLASS__, 'render']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    public static function enqueue_assets() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'converso') {
            return;
        }

        wp_enqueue_style(
            'converso-notification-css',
            CONVERSO_PLUGIN_URL . 'assets/css/notification.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'converso-notification-js',
            CONVERSO_PLUGIN_URL . 'assets/js/notification.js',
            [],
            '1.0.0',
            true
        );
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
        if (!isset($_GET['page']) || $_GET['page'] !== 'converso') {
            return;
        }

        $user_id = get_current_user_id();
        $key = self::TRANSIENT_KEY . '_' . $user_id;

        $notifications = get_transient($key);
        
        if (empty($notifications)) {
            return;
        }

        delete_transient($key);

        ?>
        <div id="converso-toast-container" class="converso-toast-container"></div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof ConversoNotification !== 'undefined') {
                    const messages = <?php echo json_encode($notifications); ?>;
                    messages.forEach(msg => {
                        ConversoNotification.show(msg.type, msg.message, msg.title);
                    });
                }
            });
        </script>
        <?php
    }
}
