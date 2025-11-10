<?php
use Converso\Admin\Admin;
use Converso\Frontend\Frontend;

final class Converso{
    private static $instance = null;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // Example:
        if ( is_admin() ) {
            new Admin();
        } else {
            new Frontend();
        }
    }

    private function __clone() {}

    public function __wakeup() {
        // Prevent unserialization
        throw new \Exception('Cannot unserialize singleton.');

    }

}