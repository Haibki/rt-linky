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
        
        wp_enqueue_media();
        
        wp_enqueue_style(
            'rt-linky-admin',
            RT_LINKY_PLUGIN_URL . 'build/admin.css',
            [],
            RT_LINKY_VERSION
        );
        
        wp_enqueue_script(
            'rt-linky-admin',
            RT_LINKY_PLUGIN_URL . 'build/admin.js',
            ['wp-element', 'wp-components', 'wp-i18n', 'wp-api-fetch'],
            RT_LINKY_VERSION,
            true
        );
        
        wp_localize_script('rt-linky-admin', 'rtLinkyAdmin', [
            'restUrl' => rest_url('rt-linky/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'postId' => get_the_ID(),
            'license' => LicenseConfig::toArray(),
            'strings' => [
                'maxLinksReached' => sprintf('Maximal %d Links in Free-Version. Upgrade auf Pro für unbegrenzte Links.', LicenseConfig::getMaxLinks()),
                'proFeature' => 'Nur in Pro-Version verfügbar',
            ],
        ]);
    }
    
    /**
     * Meta Boxes hinzufügen
     */
    public function addMetaBoxes(): void {
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