<?php
/**
 * Main Plugin Class
 */

namespace RT\Linky;

class Plugin {
    
    private static $instance = null;
    
    public static function getInstance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'init']);
    }
    
    public function init(): void {
        // Post Type registrieren
        new PostType\ProfilePostType();
        
        // Admin-Bereich
        if (is_admin()) {
            new Admin\Admin();
            new Admin\LicenseMetabox();
        }
        
        // REST API
        new API\RestAPI();
        
        // Frontend
        new Frontend\Frontend();
        
        // Pro-Lizenz AJAX Handler
        add_action('wp_ajax_rt_linky_activate_pro', [$this, 'ajaxActivatePro']);
        add_action('wp_ajax_rt_linky_deactivate_pro', [$this, 'ajaxDeactivatePro']);
    }
    
    /**
     * Pro-Lizenz aktivieren (Ajax)
     */
    public function ajaxActivatePro(): void {
        check_ajax_referer('rt_linky_pro_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }
        
        LicenseConfig::activatePro();
        wp_send_json_success(['message' => 'Pro aktiviert']);
    }

    /**
     * Pro-Lizenz deaktivieren (Ajax)
     */
    public function ajaxDeactivatePro(): void {
        check_ajax_referer('rt_linky_pro_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }
        
        LicenseConfig::deactivatePro();
        wp_send_json_success(['message' => 'Pro deaktiviert']);
    }
}