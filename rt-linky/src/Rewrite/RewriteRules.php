<?php
/**
 * Custom Rewrite Rules
 * 
 * @package RTLinky
 */

namespace RT_Linky\Rewrite;

use RT_Linky\PostType\ProfilePostType;

/**
 * Rewrite Rules Handler
 */
class RewriteRules
{
    /**
     * Query var for profile slug
     *
     * @var string
     */
    const QUERY_VAR = 'rt_linky_slug';

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('init', [$this, 'registerRewriteRules']);
        add_filter('query_vars', [$this, 'registerQueryVars']);
        add_action('template_redirect', [$this, 'handleProfileRequest']);
        add_filter('template_include', [$this, 'loadTemplate']);
    }

    /**
     * Register rewrite rules
     *
     * @return void
     */
    public function registerRewriteRules(): void
    {
        add_rewrite_rule(
            '^link/([^/]+)/?$',
            'index.php?' . self::QUERY_VAR . '=$matches[1]',
            'top'
        );
        
        add_rewrite_tag('%' . self::QUERY_VAR . '%', '([^&]+)');
    }

    /**
     * Register custom query vars
     *
     * @param array $vars Query vars
     * @return array
     */
    public function registerQueryVars(array $vars): array
    {
        $vars[] = self::QUERY_VAR;
        return $vars;
    }

    /**
     * Handle profile request
     *
     * @return void
     */
    public function handleProfileRequest(): void
    {
        $slug = get_query_var(self::QUERY_VAR);
        
        if (!$slug) {
            return;
        }

        $profile = $this->getProfileBySlug($slug);
        
        if (!$profile) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            return;
        }

        // Track view
        do_action('rt_linky_track_view', $profile->ID);
    }

    /**
     * Load custom template for profiles
     *
     * @param string $template Current template
     * @return string
     */
    public function loadTemplate(string $template): string
    {
        $slug = get_query_var(self::QUERY_VAR);
        
        if (!$slug) {
            return $template;
        }

        $profile = $this->getProfileBySlug($slug);
        
        if (!$profile) {
            return $template;
        }

        $custom_template = RT_LINKY_PLUGIN_DIR . 'templates/single-profile.php';
        
        if (file_exists($custom_template)) {
            return $custom_template;
        }

        return $template;
    }

    /**
     * Get profile by slug
     *
     * @param string $slug Profile slug
     * @return \WP_Post|null
     */
    private function getProfileBySlug(string $slug): ?\WP_Post
    {
        $args = [
            'post_type'      => ProfilePostType::POST_TYPE,
            'posts_per_page' => 1,
            'meta_query'     => [
                [
                    'key'     => '_rt_linky_slug',
                    'value'   => $slug,
                    'compare' => '=',
                ],
            ],
            'post_status'    => 'publish',
        ];

        $query = new \WP_Query($args);

        if ($query->have_posts()) {
            return $query->posts[0];
        }

        return null;
    }
}
