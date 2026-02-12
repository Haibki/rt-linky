<?php
/**
 * Plugin Name: RT-Linky
 * Plugin URI: https://rettoro.de/rt-linky
 * Description: Ein moderner Link-in-Bio Generator für WordPress
 * Version: 3.0.1
 * Author: Haibki by Rettoro
 * Author URI: https://www.rettoro.de
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rt-linky
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('RT_LINKY_VERSION', '3.0.1');
define('RT_LINKY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RT_LINKY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RT_LINKY_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader für includes/
spl_autoload_register(function ($class) {
    $prefix = 'RTLinky\\';
    $base_dir = RT_LINKY_PLUGIN_DIR . 'includes/';
    
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

// Bestehende License Client laden (falls vorhanden)
if (file_exists(__DIR__ . '/class-license-client.php')) {
    require_once __DIR__ . '/class-license-client.php';
}

// Unsere neuen Klassen laden - nur wenn Dateien existieren
if (is_dir(__DIR__ . '/includes')) {
    require_once __DIR__ . '/includes/License.php';
    require_once __DIR__ . '/includes/Admin.php';
    require_once __DIR__ . '/includes/RestApi.php';
}

/**
 * Hauptklasse
 */
class RTLinkyPlugin {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'init']);
        add_action('admin_init', [$this, 'checkLicense']);
        add_action('admin_notices', [$this, 'showNotices']);
    }
    
    public function init() {
        $this->registerPostType();
        
        if (is_admin()) {
            new RTLinky\Admin();
        }
        new RTLinky\RestApi();
        
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontend']);
        add_shortcode('rt_linky', [$this, 'renderShortcode']);
    }
    
    private function registerPostType() {
        register_post_type('rt_linky_profile', [
            'labels' => [
                'name' => 'RT-Linky Profile',
                'singular_name' => 'Profil',
                'add_new' => 'Neues Profil',
                'add_new_item' => 'Neues Profil erstellen',
                'edit_item' => 'Profil bearbeiten',
            ],
            'public' => true,
            'has_archive' => false,
            'supports' => ['title', 'editor', 'thumbnail'],
            'rewrite' => ['slug' => 'link', 'with_front' => false],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-admin-links',
            'menu_position' => 30
        ]);
    }
    
    public function enqueueFrontend() {
        wp_enqueue_style(
            'rt-linky-frontend',
            RT_LINKY_PLUGIN_URL . 'build/style.css',
            [],
            RT_LINKY_VERSION
        );
    }
    
    public function renderShortcode($atts) {
        $atts = shortcode_atts(['id' => 0, 'slug' => ''], $atts);
        
        if (!empty($atts['slug'])) {
            $profile = get_page_by_path($atts['slug'], OBJECT, 'rt_linky_profile');
            $id = $profile ? $profile->ID : 0;
        } else {
            $id = intval($atts['id']);
        }
        
        if (!$id) return '<p>Profil nicht gefunden.</p>';
        
        return '<div id="rt-linky-profile-' . $id . '" class="rt-linky-profile-container"></div>';
    }
    
    public function checkLicense() {
        $license = RTLinky\License::getInstance();
        
        if (!$license->isPro() && $license->getProfileCount() >= 2) {
            set_transient('rt_linky_limit_notice', true, DAY_IN_SECONDS);
        }
    }
    
    public function showNotices() {
        if (!get_transient('rt_linky_limit_notice')) return;
        
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'rt_linky_profile') return;
        
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>RT-Linky:</strong> Du hast das Limit von 2 Links erreicht. ';
        echo '<a href="' . admin_url('admin.php?page=rt-linky-license') . '">Auf Pro upgraden</a> für unbegrenzte Links.</p>';
        echo '</div>';
    }
}

// Starten
RTLinkyPlugin::getInstance();