<?php
/**
 * Admin Handling
 */

namespace RT\Linky\Admin;

use RT\Linky\LicenseConfig;

class Admin {
    
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
    }
    
    /**
     * Admin Assets laden
     */
    public function enqueueAssets($hook): void {
        $screen = get_current_screen();
        
        if (!$screen || $screen->post_type !== 'rt_linky_profile') {
            return;
        }
        
        // WordPress Media Uploader
        wp_enqueue_media();
        
        // React und WordPress Komponenten
        wp_enqueue_script('wp-element');
        wp_enqueue_script('wp-components');
        wp_enqueue_script('wp-i18n');
        wp_enqueue_script('wp-api-fetch');
        
        // Plugin CSS
        wp_enqueue_style(
            'rt-linky-admin',
            RT_LINKY_PLUGIN_URL . 'build/admin.css',
            ['wp-components'],
            RT_LINKY_VERSION
        );
        
        // Plugin JS
        wp_enqueue_script(
            'rt-linky-admin',
            RT_LINKY_PLUGIN_URL . 'build/admin.js',
            ['wp-element', 'wp-components', 'wp-i18n', 'wp-api-fetch', 'jquery'],
            RT_LINKY_VERSION,
            true
        );
        
        // Lokalisierung
        wp_localize_script('rt-linky-admin', 'rtLinkyAdmin', [
            'restUrl' => rest_url('rt-linky/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'postId' => get_the_ID(),
            'license' => LicenseConfig::toArray(),
            'strings' => [
                'maxLinksReached' => sprintf('Maximal %d Links in Free-Version. Upgrade auf Pro für unbegrenzte Links.', LicenseConfig::getMaxLinks()),
                'proFeature' => 'Nur in Pro-Version verfügbar',
                'saveSuccess' => 'Profil gespeichert!',
                'saveError' => 'Fehler beim Speichern',
            ],
        ]);
    }
    
    /**
     * Meta Boxes hinzufügen
     */
    public function addMetaBoxes(): void {
        // Haupt Editor Meta Box
        add_meta_box(
            'rt_linky_editor',
            __('RT-Linky Editor', 'rt-linky'),
            [$this, 'renderEditor'],
            'rt_linky_profile',
            'normal',
            'high'
        );
    }
    
    /**
     * Editor rendern
     */
    public function renderEditor($post): void {
        echo '<div id="rt-linky-root"></div>';
    }
}