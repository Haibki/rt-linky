<?php
/**
 * Frontend Handler
 * 
 * @package RTLinky
 */

namespace RT_Linky\Frontend;

/**
 * Frontend Class
 */
class Frontend
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_shortcode('rt-linky', [$this, 'renderShortcode']);
        add_action('wp_head', [$this, 'addSeoMeta']);
    }

    /**
     * Render shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function renderShortcode(array $atts): string
    {
        $atts = shortcode_atts([
            'id'   => 0,
            'slug' => '',
        ], $atts, 'rt-linky');

        if (!$atts['id'] && !$atts['slug']) {
            return '<p>' . __('Please provide a profile ID or slug.', 'rt-linky') . '</p>';
        }

        if ($atts['slug']) {
            $args = [
                'post_type'      => \RT_Linky\PostType\ProfilePostType::POST_TYPE,
                'posts_per_page' => 1,
                'meta_query'     => [
                    [
                        'key'     => '_rt_linky_slug',
                        'value'   => $atts['slug'],
                        'compare' => '=',
                    ],
                ],
            ];
            $query = new \WP_Query($args);
            $profile = $query->have_posts() ? $query->posts[0] : null;
        } else {
            $profile = get_post($atts['id']);
        }

        if (!$profile || $profile->post_type !== 'rt_linky_profile') {
            return '<p>' . __('Profile not found.', 'rt-linky') . '</p>';
        }

        return $this->getProfileHTML($profile);
    }

    /**
     * Get profile HTML
     *
     * @param \WP_Post $profile Profile post
     * @return string
     */
    private function getProfileHTML(\WP_Post $profile): string
    {
        $data = [
            'title'      => $profile->post_title,
            'bio'        => get_post_meta($profile->ID, '_rt_linky_bio', true),
            'avatar_url' => get_post_meta($profile->ID, '_rt_linky_avatar', true),
            'verified'   => get_post_meta($profile->ID, '_rt_linky_verified', true),
            'design'     => get_post_meta($profile->ID, '_rt_linky_design', true),
            'links'      => get_post_meta($profile->ID, '_rt_linky_links', true),
            'post_id'    => $profile->ID,
        ];

        ob_start();
        include RT_LINKY_PLUGIN_DIR . 'templates/profile-card.php';
        return ob_get_clean();
    }

    /**
     * Add SEO meta tags
     *
     * @return void
     */
    public function addSeoMeta(): void
    {
        $slug = get_query_var('rt_linky_slug');
        
        if (!$slug) {
            return;
        }

        $args = [
            'post_type'      => \RT_Linky\PostType\ProfilePostType::POST_TYPE,
            'posts_per_page' => 1,
            'meta_query'     => [
                [
                    'key'     => '_rt_linky_slug',
                    'value'   => $slug,
                    'compare' => '=',
                ],
            ],
        ];
        
        $query = new \WP_Query($args);
        
        if (!$query->have_posts()) {
            return;
        }

        $profile = $query->posts[0];
        $title = $profile->post_title;
        $bio = get_post_meta($profile->ID, '_rt_linky_bio', true);
        $avatar = get_post_meta($profile->ID, '_rt_linky_avatar', true);
        $url = site_url('/link/' . $slug . '/');
        
        ?>
        <meta name="description" content="<?php echo esc_attr(wp_trim_words($bio, 20)); ?>">
        <meta property="og:title" content="<?php echo esc_attr($title); ?>">
        <meta property="og:description" content="<?php echo esc_attr($bio); ?>">
        <meta property="og:url" content="<?php echo esc_url($url); ?>">
        <meta property="og:type" content="profile">
        <?php if ($avatar): ?>
        <meta property="og:image" content="<?php echo esc_url($avatar); ?>">
        <?php endif; ?>
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="<?php echo esc_attr($title); ?>">
        <meta name="twitter:description" content="<?php echo esc_attr($bio); ?>">
        <?php if ($avatar): ?>
        <meta name="twitter:image" content="<?php echo esc_url($avatar); ?>">
        <?php endif; ?>
        <link rel="canonical" href="<?php echo esc_url($url); ?>">
        <?php
    }
}
