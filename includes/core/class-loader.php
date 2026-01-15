<?php

namespace Converso\Core;

class Loader{
    private static $class_map = [];
    
    public static function register() {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

   public static function autoload($class) {
        // Only autoload Converso namespace
        if (strpos($class, 'Converso\\') !== 0) {
            return;
        }

        if (isset(self::$class_map[$class])) {
            require_once self::$class_map[$class];
            return;
        }

        $relative_class = str_replace('Converso\\', '', $class);

        $relative_class_path = str_replace('\\', DIRECTORY_SEPARATOR, $relative_class);

        $basename = basename($relative_class_path);
        $hyphenated = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $basename));
        $file_name = 'class-' . $hyphenated . '.php';

        $base_dir = CONVERSO_PLUGIN_DIR . 'includes/';

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base_dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === $file_name) {
                // Cache it for next time
                self::$class_map[$class] = $file->getPathname();
                require_once $file->getPathname();
                return;
            }
        }

        // Optional: log if class not found
        error_log("[Converso Loader] Could not find file for class: {$class}");
    }






}