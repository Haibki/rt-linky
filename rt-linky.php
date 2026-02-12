<?php
/**
 * Plugin Name: RT-Linky
 * Plugin URI: https://rettoro.de/rt-linky
 * Description: Ein moderner Link-in-Bio Generator für WordPress
 * Version: 3.0.0
 * Author: Haibki für Rettoro
 * Author URI: https://www.rettoro.de
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rt-linky
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Verhindere direkten Zugriff
if (!defined('ABSPATH')) {
    exit;
}

// Konstanten definieren
define('RT_LINKY_VERSION', '3.0.0');
define('RT_LINKY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RT_LINKY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RT_LINKY_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
spl_autoload_register(function ($class) {
    // Namespace Prefix
    $prefix = 'RTLinky\\';
    $base_dir = RT_LINKY_PLUGIN_DIR . 'includes/';
    
    // Prüfe ob Klasse den Namespace verwendet
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Relativer Klassenname
    $relative_class = substr($class, $len);
    
    // Dateipfad erstellen
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Datei laden wenn vorhanden
    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Hauptklasse des Plugins
 */
class RTLinkyPlugin {
    
    private static $instance = null;
    
    /**
     * Singleton Pattern
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Konstruktor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Plugin initialisieren
     */
    private function init() {
        // Aktivierung/Deaktivierung
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // Hooks
        add_action('plugins_loaded', [$this, 'loadPlugin']);
        add_action('init', [$this, 'registerPostType']);
        add_action('admin_init', [$this, 'checkLicenseStatus']);
        add_action('admin_notices', [$this, 'showAdminNotices']);
        
        // Textdomain laden
        add_action('plugins_loaded', [$this, 'loadTextdomain']);
    }
    
    /**
     * Plugin laden
     */
    public function loadPlugin() {
        // Admin-Bereich
        if (is_admin()) {
            new RTLinky\Admin();
            new RTLinky\MetaBoxes();
        }
        
        // Frontend
        new RTLinky\Frontend();
        
        // REST API
        add_action('rest_api_init', [$this, 'registerRestApi']);
    }
    
    /**
     * Custom Post Type registrieren
     */
    public function registerPostType() {
        $labels = [
            'name'                  => 'RT-Linky Profile',
            'singular_name'         => 'Profil',
            'menu_name'             => 'RT-Linky',
            'add_new'               => 'Neues Profil',
            'add_new_item'          => 'Neues Profil erstellen',
            'edit_item'             => 'Profil bearbeiten',
            'new_item'              => 'Neues Profil',
            'view_item'             => 'Profil ansehen',
            'search_items'          => 'Profile suchen',
            'not_found'             => 'Keine Profile gefunden',
            'not_found_in_trash'    => 'Keine Profile im Papierkorb',
            'parent_item_colon'     => '',
            'all_items'             => 'Alle Profile'
        ];
        
        $args = [
            'labels'                => $labels,
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => false, // Eigenes Menü verwenden
            'capability_type'       => 'post',
            'has_archive'           => false,
            'hierarchical'          => false,
            'menu_position'         => null,
            'supports'              => ['title', 'editor', 'thumbnail'],
            'rewrite'               => ['slug' => 'link', 'with_front' => false],
            'show_in_rest'          => true,
        ];
        
        register_post_type('rt_linky_profile', $args);
    }
    
    /**
     * REST API Endpunkte registrieren
     */
    public function registerRestApi() {
        $namespace = 'rt-linky/v1';
        
        // Profile abrufen
        register_rest_route($namespace, '/profiles', [
            'methods' => 'GET',
            'callback' => [$this, 'getProfiles'],
            'permission_callback' => '__return_true'
        ]);
        
        // Einzelnes Profil abrufen
        register_rest_route($namespace, '/profiles/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getProfile'],
            'permission_callback' => '__return_true'
        ]);
        
        // Profil erstellen (nur mit Berechtigung)
        register_rest_route($namespace, '/profiles', [
            'methods' => 'POST',
            'callback' => [$this, 'createProfile'],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            }
        ]);
        
        // Statistiken
        register_rest_route($namespace, '/stats', [
            'methods' => 'GET',
            'callback' => [$this, 'getStats'],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            }
        ]);
    }
    
    /**
     * Profile abrufen (REST)
     */
    public function getProfiles($request) {
        $args = [
            'post_type' => 'rt_linky_profile',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ];
        
        $query = new WP_Query($args);
        $profiles = [];
        
        foreach ($query->posts as $post) {
            $profiles[] = $this->formatProfile($post);
        }
        
        return rest_ensure_response($profiles);
    }
    
    /**
     * Einzelnes Profil abrufen (REST)
     */
    public function getProfile($request) {
        $id = $request->get_param('id');
        $post = get_post($id);
        
        if (!$post || $post->post_type !== 'rt_linky_profile') {
            return new WP_Error('not_found', 'Profil nicht gefunden', ['status' => 404]);
        }
        
        return rest_ensure_response($this->formatProfile($post));
    }
    
    /**
     * Profil erstellen (REST)
     */
    public function createProfile($request) {
        $license = RTLinky\License::getInstance();
        
        // Prüfe ob neuer Link erstellt werden darf
        if (!$license->canCreateLink()) {
            return new WP_Error(
                'limit_reached', 
                'Du hast das Limit erreicht. Upgrade auf Pro für unbegrenzte Links.', 
                ['status' => 403]
            );
        }
        
        $params = $request->get_json_params();
        
        $post_data = [
            'post_title'   => sanitize_text_field($params['title'] ?? ''),
            'post_content' => sanitize_textarea_field($params['content'] ?? ''),
            'post_status'  => 'publish',
            'post_type'    => 'rt_linky_profile'
        ];
        
        $post_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Meta-Daten speichern
        if (!empty($params['links'])) {
            update_post_meta($post_id, '_rt_linky_links', $params['links']);
        }
        
        if (!empty($params['appearance'])) {
            update_post_meta($post_id, '_rt_linky_appearance', $params['appearance']);
        }
        
        return rest_ensure_response([
            'id' => $post_id,
            'message' => 'Profil erstellt',
            'remaining' => $license->isPro() ? 'unlimited' : $license->getRemainingLinks()
        ]);
    }
    
    /**
     * Statistiken abrufen (REST)
     */
    public function getStats($request) {
        $license = RTLinky\License::getInstance();
        
        // Basis-Statistiken für alle
        $stats = [
            'total_profiles' => wp_count_posts('rt_linky_profile')->publish,
            'total_clicks'   => $this->getTotalClicks(),
        ];
        
        // Detaillierte Analytics nur für Pro
        if ($license->isPro()) {
            $stats['detailed'] = [
                'clicks_by_day' => $this->getClicksByDay(),
                'top_links'     => $this->getTopLinks(),
                'referrers'     => $this->getReferrers()
            ];
        }
        
        return rest_ensure_response($stats);
    }
    
    /**
     * Profil formatieren
     */
    private function formatProfile($post) {
        return [
            'id'          => $post->ID,
            'title'       => $post->post_title,
            'content'     => $post->post_content,
            'slug'        => $post->post_name,
            'permalink'   => get_permalink($post->ID),
            'avatar'      => get_the_post_thumbnail_url($post->ID, 'medium'),
            'links'       => get_post_meta($post->ID, '_rt_linky_links', true) ?: [],
            'appearance'  => get_post_meta($post->ID, '_rt_linky_appearance', true) ?: [],
            'subtitle'    => get_post_meta($post->ID, '_rt_linky_subtitle', true) ?: '',
            'created'     => $post->post_date,
            'modified'    => $post->post_modified
        ];
    }
    
    /**
     * Gesamtklicks berechnen
     */
    private function getTotalClicks() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rt_linky_clicks';
        
        // Prüfe ob Tabelle existiert
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return 0;
        }
        
        return intval($wpdb->get_var("SELECT COUNT(*) FROM $table_name"));
    }
    
    /**
     * Klicks pro Tag (Pro)
     */
    private function getClicksByDay() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rt_linky_clicks';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return [];
        }
        
        $results = $wpdb->get_results("
            SELECT DATE(created_at) as date, COUNT(*) as clicks 
            FROM $table_name 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ");
        
        return $results;
    }
    
    /**
     * Top Links (Pro)
     */
    private function getTopLinks() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rt_linky_clicks';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return [];
        }
        
        $results = $wpdb->get_results("
            SELECT link_id, COUNT(*) as clicks 
            FROM $table_name 
            GROUP BY link_id 
            ORDER BY clicks DESC 
            LIMIT 10
        ");
        
        return $results;
    }
    
    /**
     * Referrer (Pro)
     */
    private function getReferrers() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rt_linky_clicks';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return [];
        }
        
        $results = $wpdb->get_results("
            SELECT referrer, COUNT(*) as count 
            FROM $table_name 
            WHERE referrer != ''
            GROUP BY referrer 
            ORDER BY count DESC 
            LIMIT 10
        ");
        
        return $results;
    }
    
    /**
     * Lizenz-Status prüfen und Notices anzeigen
     */
    public function checkLicenseStatus() {
        $license = RTLinky\License::getInstance();
        
        // Wenn Lizenz abgelaufen, setze Transient für Notice
        if ($license->isExpired()) {
            set_transient('rt_linky_license_expired', true, DAY_IN_SECONDS);
        } else {
            delete_transient('rt_linky_license_expired');
        }
    }
    
    /**
     * Admin Notices anzeigen
     */
    public function showAdminNotices() {
        $license = RTLinky\License::getInstance();
        $screen = get_current_screen();
        
        // Nur auf RT-Linky Seiten anzeigen
        if (!$screen || strpos($screen->id, 'rt-linky') === false) {
            return;
        }
        
        // Lizenz abgelaufen Notice
        if (get_transient('rt_linky_license_expired')) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>⏰ RT-Linky Pro abgelaufen</strong><br>';
            echo 'Deine Pro-Lizenz ist abgelaufen. Deine bestehenden Links bleiben erhalten, ';
            echo 'aber du kannst keine neuen Links erstellen oder Pro-Features nutzen.<br>';
            echo '<a href="' . admin_url('admin.php?page=rt-linky-license') . '" class="button button-primary" style="margin-top: 10px;">Lizenz erneuern</a> ';
            echo '<a href="https://rettoro.de/rt-linky" target="_blank" class="button" style="margin-top: 10px; margin-left: 5px;">Neuen Key kaufen</a>';
            echo '</p></div>';
        }
        
        // Free-Version Limit Notice
        if (!$license->isPro() && !$license->isExpired()) {
            $remaining = $license->getRemainingLinks();
            
            if ($remaining === 0) {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p><strong>🚫 Link-Limit erreicht</strong><br>';
                echo 'Du hast das Maximum von 2 Links in der Free-Version erreicht.<br>';
                echo '<a href="' . admin_url('admin.php?page=rt-linky-license') . '" class="button button-primary" style="margin-top: 10px;">Auf Pro upgraden</a>';
                echo '</p></div>';
            } elseif ($remaining === 1) {
                echo '<div class="notice notice-warning is-dismissible">';
                echo '<p><strong>⚠️ Noch 1 Link verfügbar</strong><br>';
                echo 'Du hast noch 1 Link übrig in der Free-Version. ';
                echo '<a href="' . admin_url('admin.php?page=rt-linky-license') . '">Upgrade jetzt für unbegrenzte Links</a>.';
                echo '</p></div>';
            }
        }
    }
    
    /**
     * Textdomain laden
     */
    public function loadTextdomain() {
        load_plugin_textdomain(
            'rt-linky',
            false,
            dirname(RT_LINKY_PLUGIN_BASENAME) . '/languages/'
        );
    }
    
    /**
     * Plugin Aktivierung
     */
    public function activate() {
        // Post Type Rewrite Rules flushen
        $this->registerPostType();
        flush_rewrite_rules();
        
        // Datenbank-Tabelle für Statistiken erstellen (falls Pro)
        $this->createStatsTable();
        
        // Standard-Einstellungen setzen
        if (false === get_option('rt_linky_settings')) {
            update_option('rt_linky_settings', [
                'show_footer' => true,
                'footer_text' => 'Erstellt mit RT-Linky',
                'enable_subtitles' => true,
                'default_subtitle' => '',
                'enable_verified_badge' => false,
                'analytics_enabled' => true
            ]);
        }
        
        // Aktivierungs-Zeitpunkt speichern
        set_transient('rt_linky_activated', true, 30);
    }
    
    /**
     * Plugin Deaktivierung
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Statistiken-Tabelle erstellen
     */
    private function createStatsTable() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rt_linky_clicks';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            profile_id bigint(20) NOT NULL,
            link_id varchar(50) NOT NULL,
            ip_address varchar(100) DEFAULT '',
            user_agent text,
            referrer varchar(255) DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY profile_id (profile_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Plugin initialisieren
RTLinkyPlugin::getInstance();