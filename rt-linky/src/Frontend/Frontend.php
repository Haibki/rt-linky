<?php
/**
 * Frontend Handling
 */

namespace RT\Linky\Frontend;

use RT\Linky\LicenseConfig;

class Frontend {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_filter('template_include', [$this, 'loadTemplate']);
        add_action('wp_head', [$this, 'addCustomStyles']);
    }
    
    /**
     * Assets laden
     */
    public function enqueueAssets(): void {
        if (!is_singular('rt_linky_profile')) {
            return;
        }
        
        wp_enqueue_style(
            'rt-linky-frontend',
            RT_LINKY_PLUGIN_URL . 'build/frontend.css',
            [],
            RT_LINKY_VERSION
        );
        
        wp_enqueue_script(
            'rt-linky-frontend',
            RT_LINKY_PLUGIN_URL . 'build/frontend.js',
            [],
            RT_LINKY_VERSION,
            true
        );
        
        $postId = get_the_ID();
        $profileData = get_post_meta($postId, '_rt_linky_data', true);
        
        wp_localize_script('rt-linky-frontend', 'rtLinkyData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('rt-linky/v1/'),
            'postId' => $postId,
            'profile' => $profileData ?: [],
            'license' => LicenseConfig::toArray(),
            'showVerified' => LicenseConfig::allowVerifiedBadge() && get_post_meta($postId, '_rt_linky_verified_badge', true),
            'footerText' => LicenseConfig::getFooterText($postId),
        ]);
    }
    
    /**
     * Custom Template laden
     */
    public function loadTemplate($template): string {
        if (is_singular('rt_linky_profile')) {
            $customTemplate = RT_LINKY_PLUGIN_DIR . 'templates/single-rt_linky_profile.php';
            if (file_exists($customTemplate)) {
                return $customTemplate;
            }
        }
        return $template;
    }
    
    /**
     * Custom Styles im Head
     */
    public function addCustomStyles(): void {
        if (!is_singular('rt_linky_profile')) {
            return;
        }
        
        $postId = get_the_ID();
        $data = get_post_meta($postId, '_rt_linky_data', true);
        
        if (empty($data)) {
            return;
        }
        
        $styles = [];
        
        // Hintergrundfarbe
        if (!empty($data['backgroundColor'])) {
            $styles[] = 'body { background-color: ' . esc_attr($data['backgroundColor']) . '; }';
        }
        
        // Hintergrundbild (nur Pro)
        if (LicenseConfig::allowBackgroundImage() && !empty($data['backgroundImage'])) {
            $styles[] = 'body { background-image: url(' . esc_url($data['backgroundImage']) . '); background-size: cover; background-position: center; }';
        }
        
        if (!empty($styles)) {
            echo '<style>' . implode("\n", $styles) . '</style>';
        }
    }
}