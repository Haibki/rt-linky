<?php
/**
 * Plugin Name: RT-Linky
 * Description: Link-in-Bio Generator für WordPress mit Free/Pro Lizenz-System
 * Version: 2.0.0
 * Author: RT
 * Text Domain: rt-linky
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Konstanten definieren
define('RT_LINKY_VERSION', '2.0.0');
define('RT_LINKY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RT_LINKY_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'RT\\Linky\\';
    $base_dir = RT_LINKY_PLUGIN_DIR . 'src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Plugin initialisieren
add_action('plugins_loaded', function() {
    require_once RT_LINKY_PLUGIN_DIR . 'src/LicenseConfig.php';
    require_once RT_LINKY_PLUGIN_DIR . 'src/Plugin.php';
    \RT\Linky\Plugin::getInstance();
});

// Aktivierung
register_activation_hook(__FILE__, function() {
    require_once RT_LINKY_PLUGIN_DIR . 'src/PostType/ProfilePostType.php';
    \RT\Linky\PostType\ProfilePostType::register();
    flush_rewrite_rules();
});

// Deaktivierung
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});