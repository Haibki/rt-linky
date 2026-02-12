<?php
/**
 * Statistics Tracker
 * 
 * @package RTLinky
 */

namespace RT_Linky\Stats;

use RT_Linky\Database\StatsTable;

/**
 * Stats Tracker Class
 */
class Tracker
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('rt_linky_track_view', [$this, 'trackView']);
        add_action('wp_ajax_rt_linky_track_click', [$this, 'trackClick']);
        add_action('wp_ajax_nopriv_rt_linky_track_click', [$this, 'trackClick']);
        add_action('wp', [$this, 'maybeTrackView']);
    }

    /**
     * Maybe track view from query var
     *
     * @return void
     */
    public function maybeTrackView(): void
    {
        $slug = get_query_var('rt_linky_slug');
        
        if ($slug) {
            $this->trackViewBySlug($slug);
        }
    }

    /**
     * Track view by post ID
     *
     * @param int $post_id Post ID
     * @return void
     */
    public function trackView(int $post_id): void
    {
        // Update post meta counter
        $views = (int) get_post_meta($post_id, '_rt_linky_views', true);
        update_post_meta($post_id, '_rt_linky_views', $views + 1);

        // Insert detailed stats
        global $wpdb;
        $table = StatsTable::getViewsTable();
        
        $wpdb->insert(
            $table,
            [
                'post_id'    => $post_id,
                'ip_address' => $this->getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'referrer'   => $_SERVER['HTTP_REFERER'] ?? '',
                'country'    => $this->getCountryFromIP(),
                'device'     => $this->getDeviceType(),
                'browser'    => $this->getBrowser(),
                'viewed_at'  => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
    }

    /**
     * Track click via AJAX
     *
     * @return void
     */
    public function trackClick(): void
    {
        check_ajax_referer('rt_linky_track', 'nonce');

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        $label = isset($_POST['label']) ? sanitize_text_field($_POST['label']) : '';

        if (!$post_id || !$url) {
            wp_send_json_error('Missing data');
        }

        // Update post meta counter
        $clicks = (int) get_post_meta($post_id, '_rt_linky_clicks', true);
        update_post_meta($post_id, '_rt_linky_clicks', $clicks + 1);

        // Insert detailed click stats
        global $wpdb;
        $table = StatsTable::getClicksTable();
        
        $wpdb->insert(
            $table,
            [
                'post_id'    => $post_id,
                'link_url'   => $url,
                'link_label' => $label,
                'ip_address' => $this->getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'clicked_at' => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s']
        );

        wp_send_json_success(['tracked' => true]);
    }

    /**
     * Track view by slug
     *
     * @param string $slug Profile slug
     * @return void
     */
    private function trackViewBySlug(string $slug): void
    {
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
            'post_status'    => 'publish',
            'fields'         => 'ids',
        ];

        $query = new \WP_Query($args);

        if (!empty($query->posts)) {
            $this->trackView($query->posts[0]);
        }
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    private function getClientIP(): string
    {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                return sanitize_text_field($ip);
            }
        }
        
        return '';
    }

    /**
     * Get device type
     *
     * @return string
     */
    private function getDeviceType(): string
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/', $user_agent)) {
            return 'mobile';
        }
        
        if (preg_match('/Tablet|iPad/', $user_agent)) {
            return 'tablet';
        }
        
        return 'desktop';
    }

    /**
     * Get browser
     *
     * @return string
     */
    private function getBrowser(): string
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/Chrome/', $user_agent)) {
            return 'chrome';
        }
        if (preg_match('/Firefox/', $user_agent)) {
            return 'firefox';
        }
        if (preg_match('/Safari/', $user_agent)) {
            return 'safari';
        }
        if (preg_match('/Edge/', $user_agent)) {
            return 'edge';
        }
        
        return 'other';
    }

    /**
     * Get country from IP (placeholder for GeoIP)
     *
     * @return string
     */
    private function getCountryFromIP(): string
    {
        return '';
    }
}
