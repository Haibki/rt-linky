<?php
/**
 * REST API Handler
 * 
 * @package RTLinky
 */

namespace RT_Linky\API;

/**
 * REST API Class
 */
class RestAPI
{
    /**
     * API Namespace
     *
     * @var string
     */
    const NAMESPACE = 'rt-linky/v1';

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    /**
     * Register REST routes
     *
     * @return void
     */
    public function registerRoutes(): void
    {
        // Profiles endpoints
        register_rest_route(self::NAMESPACE, '/profiles', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'getProfiles'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'createProfile'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/profiles/(?P<id>\d+)', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'getProfile'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
            [
                'methods'             => \WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'updateProfile'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
            [
                'methods'             => \WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'deleteProfile'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/profiles/(?P<id>\d+)/stats', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'getProfileStats'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/stats', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'getGlobalStats'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/import', [
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'importProfiles'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/export', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'exportProfiles'],
                'permission_callback' => [$this, 'checkPermission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/icons', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'getIcons'],
                'permission_callback' => '__return_true',
            ],
        ]);
    }

    /**
     * Check permission
     *
     * @return bool|\WP_Error
     */
    public function checkPermission(): bool|\WP_Error
    {
        if (!current_user_can('manage_options')) {
            return new \WP_Error(
                'rest_forbidden',
                __('You do not have permissions to access this endpoint.', 'rt-linky'),
                ['status' => 403]
            );
        }
        return true;
    }

    /**
     * Get all profiles
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function getProfiles(\WP_REST_Request $request): \WP_REST_Response
    {
        $args = [
            'post_type'      => \RT_Linky\PostType\ProfilePostType::POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];

        $query = new \WP_Query($args);
        $profiles = [];

        foreach ($query->posts as $post) {
            $profiles[] = $this->formatProfile($post);
        }

        return new \WP_REST_Response($profiles, 200);
    }

    /**
     * Get single profile
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function getProfile(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $id = $request->get_param('id');
        $post = get_post($id);

        if (!$post || $post->post_type !== 'rt_linky_profile') {
            return new \WP_Error('not_found', __('Profile not found.', 'rt-linky'), ['status' => 404]);
        }

        return new \WP_REST_Response($this->formatProfile($post), 200);
    }

    /**
     * Create profile
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function createProfile(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $data = $request->get_json_params();
        
        $post_id = wp_insert_post([
            'post_title'  => sanitize_text_field($data['title'] ?? 'Untitled'),
            'post_type'   => 'rt_linky_profile',
            'post_status' => 'publish',
        ]);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        $this->saveProfileData($post_id, $data);

        return new \WP_REST_Response($this->formatProfile(get_post($post_id)), 201);
    }

    /**
     * Update profile
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function updateProfile(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $id = $request->get_param('id');
        $data = $request->get_json_params();

        wp_update_post([
            'ID'         => $id,
            'post_title' => sanitize_text_field($data['title'] ?? 'Untitled'),
        ]);

        $this->saveProfileData($id, $data);

        return new \WP_REST_Response($this->formatProfile(get_post($id)), 200);
    }

    /**
     * Delete profile
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function deleteProfile(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $id = $request->get_param('id');
        
        $result = wp_delete_post($id, true);
        
        if (!$result) {
            return new \WP_Error('delete_failed', __('Failed to delete profile.', 'rt-linky'), ['status' => 500]);
        }

        return new \WP_REST_Response(['deleted' => true], 200);
    }

    /**
     * Get profile stats
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function getProfileStats(\WP_REST_Request $request): \WP_REST_Response
    {
        $id = $request->get_param('id');
        $period = $request->get_param('period') ?? 30;

        $stats = [
            'views'           => (int) get_post_meta($id, '_rt_linky_views', true),
            'clicks'          => (int) get_post_meta($id, '_rt_linky_clicks', true),
            'period'          => $this->getStatsForPeriod($id, $period),
            'top_links'       => $this->getTopLinks($id, $period),
            'devices'         => $this->getDeviceStats($id, $period),
            'browsers'        => $this->getBrowserStats($id, $period),
        ];

        return new \WP_REST_Response($stats, 200);
    }

    /**
     * Get global stats
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function getGlobalStats(\WP_REST_Request $request): \WP_REST_Response
    {
        $period = $request->get_param('period') ?? 30;
        
        global $wpdb;
        $views_table = \RT_Linky\Database\StatsTable::getViewsTable();
        
        $date_where = $wpdb->prepare(
            "viewed_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $period
        );

        $total_views = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$views_table} WHERE {$date_where}");
        $unique_visitors = (int) $wpdb->get_var("SELECT COUNT(DISTINCT ip_address) FROM {$views_table} WHERE {$date_where}");
        
        $stats = [
            'total_profiles'  => wp_count_posts('rt_linky_profile')->publish,
            'total_views'     => $total_views,
            'unique_visitors' => $unique_visitors,
        ];

        return new \WP_REST_Response($stats, 200);
    }

    /**
     * Import profiles
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function importProfiles(\WP_REST_Request $request): \WP_REST_Response
    {
        $files = $request->get_file_params();
        
        if (empty($files['file'])) {
            return new \WP_REST_Response(['error' => 'No file uploaded'], 400);
        }

        $content = file_get_contents($files['file']['tmp_name']);
        $data = json_decode($content, true);

        if (!$data || !isset($data['profiles'])) {
            return new \WP_REST_Response(['error' => 'Invalid JSON'], 400);
        }

        $imported = 0;
        
        foreach ($data['profiles'] as $profile_data) {
            $post_id = wp_insert_post([
                'post_title'  => sanitize_text_field($profile_data['title'] ?? 'Untitled'),
                'post_type'   => 'rt_linky_profile',
                'post_status' => 'publish',
            ]);

            if (!is_wp_error($post_id)) {
                $this->saveProfileData($post_id, $profile_data);
                $imported++;
            }
        }

        return new \WP_REST_Response(['imported' => $imported], 200);
    }

    /**
     * Export profiles
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function exportProfiles(\WP_REST_Request $request): \WP_REST_Response
    {
        $args = [
            'post_type'      => 'rt_linky_profile',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];

        $query = new \WP_Query($args);
        $profiles = [];

        foreach ($query->posts as $post) {
            $profiles[] = $this->formatProfile($post);
        }

        $export = [
            'version'     => RT_LINKY_VERSION,
            'export_date' => current_time('mysql'),
            'profiles'    => $profiles,
        ];

        return new \WP_REST_Response($export, 200);
    }

    /**
     * Get icons
     *
     * @return \WP_REST_Response
     */
    public function getIcons(): \WP_REST_Response
    {
        $icons = [];
        $icon_dir = RT_LINKY_PLUGIN_DIR . 'assets/icons/';
        
        if (is_dir($icon_dir)) {
            $files = glob($icon_dir . '*.svg');
            foreach ($files as $file) {
                $icons[] = [
                    'name' => basename($file, '.svg'),
                    'svg'  => file_get_contents($file),
                ];
            }
        }

        return new \WP_REST_Response($icons, 200);
    }

    /**
     * Format profile for API response
     *
     * @param \WP_Post $post Post object
     * @return array
     */
    private function formatProfile(\WP_Post $post): array
    {
        return [
            'id'            => $post->ID,
            'title'         => $post->post_title,
            'slug'          => get_post_meta($post->ID, '_rt_linky_slug', true),
            'bio'           => get_post_meta($post->ID, '_rt_linky_bio', true),
            'avatar_url'    => get_post_meta($post->ID, '_rt_linky_avatar', true),
            'verified'      => (bool) get_post_meta($post->ID, '_rt_linky_verified', true),
            'design'        => get_post_meta($post->ID, '_rt_linky_design', true) ?: $this->getDefaultDesign(),
            'links'         => get_post_meta($post->ID, '_rt_linky_links', true) ?: [],
            'views'         => (int) get_post_meta($post->ID, '_rt_linky_views', true),
            'clicks'        => (int) get_post_meta($post->ID, '_rt_linky_clicks', true),
            'created_at'    => $post->post_date,
            'updated_at'    => $post->post_modified,
            'edit_link'     => admin_url('admin.php?page=rt-linky&profile=' . $post->ID),
            'view_link'     => site_url('/link/' . get_post_meta($post->ID, '_rt_linky_slug', true) . '/'),
        ];
    }

    /**
     * Save profile data
     *
     * @param int   $post_id Post ID
     * @param array $data    Profile data
     * @return void
     */
    private function saveProfileData(int $post_id, array $data): void
    {
        update_post_meta($post_id, '_rt_linky_slug', sanitize_title($data['slug'] ?? $data['title'] ?? 'profile'));
        update_post_meta($post_id, '_rt_linky_bio', sanitize_textarea_field($data['bio'] ?? ''));
        update_post_meta($post_id, '_rt_linky_avatar', esc_url_raw($data['avatar_url'] ?? ''));
        update_post_meta($post_id, '_rt_linky_verified', !empty($data['verified']));
        update_post_meta($post_id, '_rt_linky_design', $data['design'] ?? $this->getDefaultDesign());
        update_post_meta($post_id, '_rt_linky_links', $data['links'] ?? []);
    }

    /**
     * Get default design
     *
     * @return array
     */
    private function getDefaultDesign(): array
    {
        return [
            'bg_type'       => 'gradient',
            'color1'        => '#667eea',
            'color2'        => '#764ba2',
            'text_color'    => '#ffffff',
            'button_color'  => '#ffffff',
            'button_radius' => 12,
        ];
    }

    /**
     * Get stats for period
     *
     * @param int $post_id Post ID
     * @param int $period  Days
     * @return array
     */
    private function getStatsForPeriod(int $post_id, int $period): array
    {
        global $wpdb;
        $table = \RT_Linky\Database\StatsTable::getViewsTable();
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(viewed_at) as date, COUNT(*) as count 
             FROM {$table} 
             WHERE post_id = %d AND viewed_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
             GROUP BY DATE(viewed_at)
             ORDER BY date ASC",
            $post_id,
            $period
        ));

        return $results ?: [];
    }

    /**
     * Get top links
     *
     * @param int $post_id Post ID
     * @param int $period  Days
     * @return array
     */
    private function getTopLinks(int $post_id, int $period): array
    {
        global $wpdb;
        $table = \RT_Linky\Database\StatsTable::getClicksTable();
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT link_label, link_url, COUNT(*) as clicks 
             FROM {$table} 
             WHERE post_id = %d AND clicked_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
             GROUP BY link_url 
             ORDER BY clicks DESC 
             LIMIT 10",
            $post_id,
            $period
        )) ?: [];
    }

    /**
     * Get device stats
     *
     * @param int $post_id Post ID
     * @param int $period  Days
     * @return array
     */
    private function getDeviceStats(int $post_id, int $period): array
    {
        global $wpdb;
        $table = \RT_Linky\Database\StatsTable::getViewsTable();
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT device, COUNT(*) as count 
             FROM {$table} 
             WHERE post_id = %d AND viewed_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
             GROUP BY device",
            $post_id,
            $period
        )) ?: [];
    }

    /**
     * Get browser stats
     *
     * @param int $post_id Post ID
     * @param int $period  Days
     * @return array
     */
    private function getBrowserStats(int $post_id, int $period): array
    {
        global $wpdb;
        $table = \RT_Linky\Database\StatsTable::getViewsTable();
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT browser, COUNT(*) as count 
             FROM {$table} 
             WHERE post_id = %d AND viewed_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
             GROUP BY browser",
            $post_id,
            $period
        )) ?: [];
    }
}
