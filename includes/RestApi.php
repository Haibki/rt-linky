<?php
namespace RTLinky;

class RestApi {
    
    public function __construct() {
        add_action('rest_api_init', [$this, 'registerRoutes']);
        add_filter('rest_prepare_rt_linky_profile', [$this, 'addLicenseData'], 10, 3);
    }
    
    public function registerRoutes() {
        $namespace = 'rt-linky/v1';
        
        register_rest_route($namespace, '/license', [
            'methods' => 'GET',
            'callback' => [$this, 'getLicenseStatus'],
            'permission_callback' => '__return_true'
        ]);
        
        register_rest_route($namespace, '/can-create', [
            'methods' => 'GET',
            'callback' => [$this, 'canCreateCheck'],
            'permission_callback' => function() {
                return current_user_can('publish_posts');
            }
        ]);
    }
    
    public function getLicenseStatus() {
        if (!class_exists('RTLinky\License')) {
            return rest_ensure_response([
                'is_pro' => false,
                'can_create' => true,
                'remaining' => 2,
                'total_profiles' => 0,
                'error' => 'License class not found'
            ]);
        }
        
        $license = License::getInstance();
        
        return rest_ensure_response([
            'is_pro' => $license->isPro(),
            'can_create' => $license->canCreateProfile(),
            'remaining' => $license->getRemainingProfiles(),
            'total_profiles' => $license->getProfileCount(),
            'features' => [
                'unlimited_links' => $license->isPro(),
                'all_icons' => $license->isPro(),
                'background_image' => $license->isPro(),
                'subtitles' => $license->isPro(),
                'verified_badge' => $license->isPro(),
                'custom_footer' => $license->isPro()
            ]
        ]);
    }
    
    public function canCreateCheck() {
        if (!class_exists('RTLinky\License')) {
            return rest_ensure_response(['allowed' => true]);
        }
        
        $license = License::getInstance();
        
        if (!$license->canCreateProfile()) {
            return new \WP_Error(
                'limit_reached',
                'Du hast das Limit von 2 Links in der Free-Version erreicht.',
                [
                    'status' => 403,
                    'upgrade_url' => 'https://rettoro.de/rt-linky'
                ]
            );
        }
        
        return rest_ensure_response(['allowed' => true]);
    }
    
    public function addLicenseData($response, $post, $request) {
        $data = $response->get_data();
        
        $data['license'] = [
            'is_pro' => false,
            'can_edit' => true,
            'features' => [
                'subtitles' => false,
                'background_image' => false,
                'verified_badge' => false,
                'all_icons' => false
            ]
        ];
        
        if (class_exists('RTLinky\License')) {
            $license = License::getInstance();
            $data['license'] = [
                'is_pro' => $license->isPro(),
                'can_edit' => true,
                'features' => [
                    'subtitles' => $license->isPro(),
                    'background_image' => $license->isPro(),
                    'verified_badge' => $license->isPro(),
                    'all_icons' => $license->isPro()
                ]
            ];
        }
        
        $response->set_data($data);
        return $response;
    }
}