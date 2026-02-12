<?php
/**
 * REST API Endpoints
 */

namespace RT\Linky\API;

use RT\Linky\LicenseConfig;

class RestAPI {
    
    private $namespace = 'rt-linky/v1';
    
    public function __construct() {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }
    
    public function registerRoutes(): void {
        // Profile speichern
        register_rest_route($this->namespace, '/profile/(?P<id>\d+)', [
            [
                'methods' => \WP_REST_Server::EDITABLE,
                'callback' => [$this, 'saveProfile'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);
        
        // Profile abrufen
        register_rest_route($this->namespace, '/profile/(?P<id>\d+)', [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'getProfile'],
                'permission_callback' => '__return_true',
            ],
        ]);
        
        // Lizenz-Status Endpoint
        register_rest_route($this->namespace, '/license', [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'getLicenseStatus'],
                'permission_callback' => '__return_true',
            ],
        ]);
    }
    
    /**
     * Berechtigungsprüfung
     */
    public function checkPermission(\WP_REST_Request $request): bool {
        $postId = $request->get_param('id');
        return current_user_can('edit_post', $postId);
    }
    
    /**
     * Profile speichern
     */
    public function saveProfile(\WP_REST_Request $request): \WP_REST_Response {
        $postId = $request->get_param('id');
        $params = $request->get_json_params();
        
        // Links validieren (Max-Limit)
        if (isset($params['links']) && is_array($params['links'])) {
            $maxLinks = LicenseConfig::getMaxLinks();
            if (count($params['links']) > $maxLinks) {
                return new \WP_REST_Response([
                    'success' => false,
                    'message' => sprintf('Maximal %d Links erlaubt', $maxLinks)
                ], 400);
            }
            
            // Icons validieren
            foreach ($params['links'] as $link) {
                if (isset($link['icon']) && !LicenseConfig::isIconAvailable($link['icon'])) {
                    return new \WP_REST_Response([
                        'success' => false,
                        'message' => 'Icon nicht verfügbar in Free-Version'
                    ], 400);
                }
            }
        }
        
        // Hintergrundbild validieren
        if (!LicenseConfig::allowBackgroundImage() && !empty($params['backgroundImage'])) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Hintergrundbild nur in Pro-Version verfügbar'
            ], 400);
        }
        
        // Link-Untertitel validieren
        if (!LicenseConfig::allowLinkSubtitle() && !empty($params['links'])) {
            foreach ($params['links'] as $link) {
                if (!empty($link['subtitle'])) {
                    return new \WP_REST_Response([
                        'success' => false,
                        'message' => 'Link-Untertitel nur in Pro-Version verfügbar'
                    ], 400);
                }
            }
        }
        
        // Daten speichern
        update_post_meta($postId, '_rt_linky_data', $params);
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => 'Profil gespeichert'
        ], 200);
    }
    
    /**
     * Profile abrufen
     */
    public function getProfile(\WP_REST_Request $request): \WP_REST_Response {
        $postId = $request->get_param('id');
        $data = get_post_meta($postId, '_rt_linky_data', true);
        
        if (empty($data)) {
            $data = [
                'title' => get_the_title($postId),
                'description' => '',
                'avatar' => '',
                'backgroundColor' => '#ffffff',
                'backgroundImage' => '',
                'links' => [],
            ];
        }
        
        // Lizenz-Info hinzufügen
        $data['license'] = LicenseConfig::toArray();
        $data['showVerified'] = LicenseConfig::allowVerifiedBadge() && get_post_meta($postId, '_rt_linky_verified_badge', true);
        
        return new \WP_REST_Response($data, 200);
    }
    
    /**
     * Lizenz-Status abrufen
     */
    public function getLicenseStatus(\WP_REST_Request $request): \WP_REST_Response {
        return new \WP_REST_Response(LicenseConfig::toArray(), 200);
    }
}