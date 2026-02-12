<?php
/**
 * Database Migration Handler
 * 
 * @package RTLinky
 */

namespace RT_Linky\Database;

/**
 * Migration Class
 */
class Migration
{
    /**
     * Current database version
     *
     * @var string
     */
    private string $db_version = '3.0.0';

    /**
     * Run migrations
     *
     * @return void
     */
    public function run(): void
    {
        $installed_version = get_option('rt_linky_db_version', '0');
        
        if (version_compare($installed_version, $this->db_version, '>=')) {
            return;
        }
        
        // Run migrations
        if (version_compare($installed_version, '3.0.0', '<')) {
            $this->migrateTo300();
        }
        
        update_option('rt_linky_db_version', $this->db_version);
    }

    /**
     * Migration to version 3.0.0
     * - Migrate JSON data to Custom Post Type
     *
     * @return void
     */
    private function migrateTo300(): void
    {
        // Old data file path
        $old_file = WP_CONTENT_DIR . '/linky-data/profiles.json';
        
        if (!file_exists($old_file)) {
            return;
        }
        
        $content = file_get_contents($old_file);
        $data = json_decode($content, true);
        
        if (empty($data['profiles'])) {
            return;
        }
        
        foreach ($data['profiles'] as $profile) {
            $this->migrateProfile($profile);
        }
        
        // Backup old file
        rename($old_file, $old_file . '.backup.' . time());
    }

    /**
     * Migrate single profile
     *
     * @param array $profile Profile data
     * @return void
     */
    private function migrateProfile(array $profile): void
    {
        $slug = sanitize_title($profile['slug'] ?? $profile['title'] ?? 'profile');
        
        // Check if profile already exists
        $existing = get_posts([
            'post_type'      => \RT_Linky\PostType\ProfilePostType::POST_TYPE,
            'meta_query'     => [
                [
                    'key'     => '_rt_linky_slug',
                    'value'   => $slug,
                    'compare' => '=',
                ],
            ],
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ]);
        
        if (!empty($existing)) {
            return;
        }
        
        // Create post
        $post_id = wp_insert_post([
            'post_title'  => sanitize_text_field($profile['title'] ?? 'Untitled'),
            'post_type'   => \RT_Linky\PostType\ProfilePostType::POST_TYPE,
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ]);
        
        if (is_wp_error($post_id)) {
            return;
        }
        
        // Save profile data
        update_post_meta($post_id, '_rt_linky_slug', $slug);
        update_post_meta($post_id, '_rt_linky_bio', sanitize_textarea_field($profile['bio'] ?? ''));
        update_post_meta($post_id, '_rt_linky_avatar', esc_url_raw($profile['avatar_url'] ?? ''));
        update_post_meta($post_id, '_rt_linky_verified', !empty($profile['verified']));
        update_post_meta($post_id, '_rt_linky_design', $profile['design'] ?? []);
        update_post_meta($post_id, '_rt_linky_links', $profile['links'] ?? []);
    }
}
