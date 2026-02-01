<?php
/**
 * 
 * Plugin Name: Connectapre - Smart Contact Button
 * Description: Connectapre adds a smart click-to-chat button for WhatsApp, routing visitors to the right agent based on country, business hours, login status, scroll position, and more. With multi-agent support, dynamic greetings, and conversion tracking, Connectapre ensures conversations start at the right moment.
 * Plugin URI: https://connectapre.sujitmagar.com.np
 * Author: Sujit Ale Magar
 * Author URI: https://sujitmagar.com.np
 * Version: 1.0.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: connectapre
 * Domain Path: /languages
 * 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Constants
define('CONNECTAPRE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CONNECTAPRE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include autoloader
require_once CONNECTAPRE_PLUGIN_DIR . 'includes/core/class-loader.php';

// Register autoloader
\Connectapre\Core\Loader::register();

// Include main Connectapre class manually or let autoloader handle it
require_once CONNECTAPRE_PLUGIN_DIR . 'includes/class-connectapre.php';

// Register Activation Hook
register_activation_hook(__FILE__, [\Connectapre\Core\Activator::class, 'activate']);

// Register Deactivation Hook
register_deactivation_hook(__FILE__, [\Connectapre\Core\Deactivator::class, 'deactivate']);

// Initialize plugin
\Connectapre::get_instance();
