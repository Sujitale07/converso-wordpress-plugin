<?php
/**
 * 
 * Plugin Name: Converso - Whatsapp Chat Plugin
 * Description: Converso makes WhatsApp chat smarter - showing the right agent at the right time, based on visitor country, business hours, login status, scroll position, and more.With multi-agent support, dynamic greetings, and Google Ads conversion tracking, Converso ensures every chat starts at the perfect moment.
 * Plugin URI: https://sujitmagar.com.np
 * Author: Sujit Ale Magar
 * Author URI: https://sujitmagar.com.np
 * Version: 1.0.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Constants
define('CONVERSO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CONVERSO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include autoloader
require_once CONVERSO_PLUGIN_DIR . 'includes/core/class-loader.php';

// Register autoloader
\Converso\Core\Loader::register();

// Include main Converso class manually or let autoloader handle it
require_once CONVERSO_PLUGIN_DIR . 'includes/class-converso.php';

// Register Activation Hook
register_activation_hook(__FILE__, [\Converso\Core\Activator::class, 'activate']);

// Register Deactivation Hook
register_deactivation_hook(__FILE__, [\Converso\Core\Deactivator::class, 'deactivate']);

// Initialize plugin
\Converso::get_instance();