<?php
/**
 * Main Plugin Class
 * 
 * @package RTLinky
 */

namespace RT_Linky;

use RT_Linky\Admin\Admin;
use RT_Linky\API\RestAPI;
use RT_Linky\Blocks\BlockRegistrar;
use RT_Linky\Database\Migration;
use RT_Linky\Frontend\Frontend;
use RT_Linky\PostType\ProfilePostType;
use RT_Linky\Rewrite\RewriteRules;
use RT_Linky\Stats\Tracker;

/**
 * Main Plugin Class
 */
class Plugin
{
    /**
     * Plugin instance
     *
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;

    /**
     * Get plugin instance (Singleton)
     *
     * @return Plugin
     */
    public static function instance(): Plugin
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * Initialize plugin
     *
     * @return void
     */
    private function init(): void
    {
        // Load text domain
        add_action('init', [$this, 'loadTextDomain']);

        // Check for migrations
        add_action('init', [$this, 'checkMigrations']);

        // Initialize components
        new ProfilePostType();
        new RewriteRules();
        new Tracker();
        new Admin();
        new Frontend();
        new RestAPI();
        new BlockRegistrar();

        // Load assets - ABER NICHT auf rt-linky Seiten (da Admin.php das übernimmt)
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
    }

    /**
     * Load plugin text domain
     *
     * @return void
     */
    public function loadTextDomain(): void
    {
        load_plugin_textdomain(
            'rt-linky',
            false,
            dirname(plugin_basename(RT_LINKY_PLUGIN_FILE)) . '/languages/'
        );
    }

    /**
     * Check and run database migrations
     *
     * @return void
     */
    public function checkMigrations(): void
    {
        $migration = new Migration();
        $migration->run();
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page
     * @return void
     */
    public function enqueueAdminAssets(string $hook): void
    {
        // WICHTIG: Nicht auf RT-Linky Seiten laden - Admin.php macht das!
        if (str_contains($hook, 'rt-linky')) {
            return;
        }

        // Nur für Gutenberg Block Editor laden
        if (!str_contains($hook, 'post.php') && !str_contains($hook, 'post-new.php')) {
            return;
        }

        $manifest = $this->getManifest();

        // Nur für Block Editor
        if (isset($manifest['js/block-editor.js'])) {
            wp_enqueue_script(
                'rt-linky-block-editor',
                RT_LINKY_BUILD_URL . $manifest['js/block-editor.js']['file'],
                ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
                RT_LINKY_VERSION,
                true
            );

            wp_localize_script('rt-linky-block-editor', 'rtLinkyBlockData', [
                'restUrl'   => rest_url('rt-linky/v1/'),
                'restNonce' => wp_create_nonce('wp_rest'),
                'profiles'  => $this->getProfilesForBlock(),
            ]);
        }
    }

    /**
     * Enqueue frontend assets
     *
     * @return void
     */
    public function enqueueFrontendAssets(): void
    {
        if (!is_singular('rt_linky_profile')) {
            return;
        }

        $manifest = $this->getManifest();

        if (isset($manifest['js/frontend.js'])) {
            wp_enqueue_script(
                'rt-linky-frontend',
                RT_LINKY_BUILD_URL . $manifest['js/frontend.js']['file'],
                [],
                RT_LINKY_VERSION,
                true
            );

            wp_localize_script('rt-linky-frontend', 'rtLinkyFrontend', [
                'restUrl' => rest_url('rt-linky/v1/'),
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'trackNonce' => wp_create_nonce('rt_linky_track'),
            ]);
        }
    }

    /**
     * Get Vite manifest
     *
     * @return array
     */
    private function getManifest(): array
    {
        $manifest_path = RT_LINKY_BUILD_DIR . '.vite/manifest.json';
        
        if (!file_exists($manifest_path)) {
            return [];
        }

        $content = file_get_contents($manifest_path);
        return json_decode($content, true) ?: [];
    }

    /**
     * Get profiles for block selector
     *
     * @return array
     */
    private function getProfilesForBlock(): array
    {
        $args = [
            'post_type'      => \RT_Linky\PostType\ProfilePostType::POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];

        $query = new \WP_Query($args);
        $profiles = [];

        foreach ($query->posts as $post) {
            $profiles[] = [
                'id'    => $post->ID,
                'title' => $post->post_title,
                'slug'  => get_post_meta($post->ID, '_rt_linky_slug', true),
            ];
        }

        return $profiles;
    }
}