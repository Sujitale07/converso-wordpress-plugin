<?php 

namespace Converso\Core;

class Log {

    protected static $filepath = CONVERSO_PLUGIN_DIR . '/storage/logs.txt';

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

        $logLine = '[' . date('Y-m-d H:i:s') . '] ' . $level . ': ' . $message . PHP_EOL;

        // Create directory if not exists
        $dir = dirname(self::$filepath);
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }

        // Append log
        file_put_contents(self::$filepath, $logLine, FILE_APPEND);
    }
}
