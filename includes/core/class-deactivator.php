<?php

namespace Connectapre\Core;

use Connectapre\Core\Log;
use Connectapre\Database\Demigration;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class Deactivator {

	public static function deactivate() {

        // Flush rewrite rules to ensure any custom rules from the plugin are removed.
        flush_rewrite_rules();

        Demigration::run();

        // Log the deactivation
        Log::info("Plugin Deactivated. Tables dropped and options deleted.");
	}

}

