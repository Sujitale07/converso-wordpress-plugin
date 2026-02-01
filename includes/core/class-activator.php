<?php

namespace Connectapre\Core;
use Connectapre\Core\Log;
use Connectapre\Database\Migrations;
use Connectapre\Database\Seeder;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {        
    
        Migrations::run();
        Seeder::run();

        Log::info("Plugin Activated. Migrations and Seeders ran.");
	}

}

