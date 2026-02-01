<?php 

namespace Connectapre\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Log {

    protected static $filepath = CONNECTAPRE_PLUGIN_DIR . '/storage/logs.txt';

    public static function info($data) {
        self::writeLog('INFO', $data);
    }

    public static function error($data) {
        self::writeLog('ERROR', $data);
    }

    public static function debug($data) {
        self::writeLog('DEBUG', $data);
    }

    protected static function writeLog($level, $data) {
        $message = is_array($data) || is_object($data)
            ? print_r($data, true)
            : $data;

        $logLine = '[' . gmdate('Y-m-d H:i:s') . '] ' . $level . ': ' . $message . PHP_EOL;

        // Create directory if not exists
        $dir = dirname(self::$filepath);
        if (!file_exists($dir)) {
            // Using wp_mkdir_p for better compatibility
            wp_mkdir_p($dir);
        }

        // Append log using direct file access is discouraged but sometimes necessary for logs
        // We'll use @file_put_contents to suppress errors if not writable
        @file_put_contents(self::$filepath, $logLine, FILE_APPEND);
    }
}

