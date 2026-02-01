<?php
use Connectapre\Admin\Admin;
use Connectapre\Frontend\Frontend;

final class Connectapre{
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

    protected function init_hooks() {
        
        new Frontend();

        if ( is_admin() ) {
            new Admin();    
        }
    }

    private function __clone() {}

    public function __wakeup() {
        // Prevent unserialization
        throw new \Exception('Cannot unserialize singleton.');

    }

}

