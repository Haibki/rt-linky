<?php
/**
 * Plugin Name: RT-Linky v3.0.9
 * Description: Modern Link-in-Bio Generator mit Lizenz-Manager
 * Version: 3.0.9
 * Author: Haibki
 * Author URI: https://www.rettoro.de
 * Plugin URI: https://www.rettoro.de
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rt-linky
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * 
 * @package RTLinky
 */

if (!defined('ABSPATH')) {
    exit;
}

// Autoloader
$autoload_file = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload_file)) {
    require_once $autoload_file;
} else {
    require_once __DIR__ . '/src/Plugin.php';
    require_once __DIR__ . '/src/PostType/ProfilePostType.php';
    require_once __DIR__ . '/src/API/RestAPI.php';
    require_once __DIR__ . '/src/Admin/Admin.php';
    require_once __DIR__ . '/src/Frontend/Frontend.php';
    require_once __DIR__ . '/src/Blocks/BlockRegistrar.php';
    require_once __DIR__ . '/src/Rewrite/RewriteRules.php';
    require_once __DIR__ . '/src/Stats/Tracker.php';
    require_once __DIR__ . '/src/Database/StatsTable.php';
    require_once __DIR__ . '/src/Database/Activator.php';
    require_once __DIR__ . '/src/Database/Deactivator.php';
    require_once __DIR__ . '/src/Database/Migration.php';
}

// Constants
define('RT_LINKY_VERSION', '3.0.9');
define('RT_LINKY_PLUGIN_FILE', __FILE__);
define('RT_LINKY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RT_LINKY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RT_LINKY_BUILD_DIR', RT_LINKY_PLUGIN_DIR . 'build/');
define('RT_LINKY_BUILD_URL', RT_LINKY_PLUGIN_URL . 'build/');

// Initialize Plugin
add_action('plugins_loaded', function() {
    \RT_Linky\Plugin::instance();
}, 10);

// Activation Hook
register_activation_hook(__FILE__, function () {
    require_once RT_LINKY_PLUGIN_DIR . 'src/Database/Activator.php';
    \RT_Linky\Database\Activator::activate();
});

// Deactivation Hook
register_deactivation_hook(__FILE__, function () {
    require_once RT_LINKY_PLUGIN_DIR . 'src/Database/Deactivator.php';
    \RT_Linky\Database\Deactivator::deactivate();
});