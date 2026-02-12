<?php
/**
 * Custom Post Type for RT-Linky Profiles
 * 
 * @package RTLinky
 */

namespace RT_Linky\PostType;

/**
 * Profile Post Type Class
 */
class ProfilePostType
{
    /**
     * Post type name
     *
     * @var string
     */
    const POST_TYPE = 'rt_linky_profile';

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('init', [$this, 'register']);
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post', [$this, 'saveMeta'], 10, 2);
        add_filter('manage_rt_linky_profile_posts_columns', [$this, 'addCustomColumns']);
        add_action('manage_rt_linky_profile_posts_custom_column', [$this, 'renderCustomColumns'], 10, 2);
    }

    /**
     * Register post type
     *
     * @return void
     */
    public function register(): void
    {
        $labels = [
            'name'                  => __('Linky Profile', 'rt-linky'),
            'singular_name'         => __('Profile', 'rt-linky'),
            'menu_name'             => __('RT-Linky', 'rt-linky'),
            'name_admin_bar'        => __('Profile', 'rt-linky'),
            'add_new'               => __('Add New', 'rt-linky'),
            'add_new_item'          => __('Add New Profile', 'rt-linky'),
            'new_item'              => __('New Profile', 'rt-linky'),
            'edit_item'             => __('Edit Profile', 'rt-linky'),
            'view_item'             => __('View Profile', 'rt-linky'),
            'all_items'             => __('All Profiles', 'rt-linky'),
            'search_items'          => __('Search Profiles', 'rt-linky'),
            'parent_item_colon'     => __('Parent Profiles:', 'rt-linky'),
            'not_found'             => __('No profiles found.', 'rt-linky'),
            'not_found_in_trash'    => __('No profiles found in Trash.', 'rt-linky'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'link', 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 26,
            'menu_icon'          => 'dashicons-admin-links',
            'supports'           => ['title', 'author', 'thumbnail'],
            'show_in_rest'       => true,
            'rest_base'          => 'rt-linky-profiles',
        ];

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Add meta boxes
     *
     * @return void
     */
    public function addMetaBoxes(): void
    {
        add_meta_box(
            'rt_linky_profile_data',
            __('Profile Data', 'rt-linky'),
            [$this, 'renderMetaBox'],
            self::POST_TYPE,
            'normal',
            'high'
        );
    }

    /**
     * Render meta box
     *
     * @param \WP_Post $post Post object
     * @return void
     */
    public function renderMetaBox(\WP_Post $post): void
    {
        wp_nonce_field('rt_linky_profile_meta', 'rt_linky_profile_meta_nonce');
        
        $slug = get_post_meta($post->ID, '_rt_linky_slug', true);
        
        ?>
        <p>
            <label for="rt_linky_slug"><?php _e('Profile Slug:', 'rt-linky'); ?></label>
            <input type="text" id="rt_linky_slug" name="rt_linky_slug" 
                   value="<?php echo esc_attr($slug); ?>" class="widefat">
            <span class="description">
                <?php echo site_url('/link/'); ?><span id="slug-preview"><?php echo esc_html($slug); ?></span>/
            </span>
        </p>
        <p class="description">
            <?php _e('Use the RT-Linky Editor for full profile editing capabilities.', 'rt-linky'); ?>
        </p>
        <?php
    }

    /**
     * Save meta data
     *
     * @param int     $post_id Post ID
     * @param \WP_Post $post   Post object
     * @return void
     */
    public function saveMeta(int $post_id, \WP_Post $post): void
    {
        if (!isset($_POST['rt_linky_profile_meta_nonce']) || 
            !wp_verify_nonce($_POST['rt_linky_profile_meta_nonce'], 'rt_linky_profile_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['rt_linky_slug'])) {
            update_post_meta($post_id, '_rt_linky_slug', sanitize_title($_POST['rt_linky_slug']));
        }
    }

    /**
     * Add custom columns
     *
     * @param array $columns Existing columns
     * @return array
     */
    public function addCustomColumns(array $columns): array
    {
        $new_columns = [];
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['slug'] = __('Slug', 'rt-linky');
                $new_columns['views'] = __('Views', 'rt-linky');
                $new_columns['clicks'] = __('Clicks', 'rt-linky');
                $new_columns['shortcode'] = __('Shortcode', 'rt-linky');
            }
        }
        return $new_columns;
    }

    /**
     * Render custom columns
     *
     * @param string $column  Column name
     * @param int    $post_id Post ID
     * @return void
     */
    public function renderCustomColumns(string $column, int $post_id): void
    {
        switch ($column) {
            case 'slug':
                $slug = get_post_meta($post_id, '_rt_linky_slug', true);
                echo '<code>' . esc_html($slug) . '</code>';
                break;
            case 'views':
                $views = (int) get_post_meta($post_id, '_rt_linky_views', true);
                echo number_format_i18n($views);
                break;
            case 'clicks':
                $clicks = (int) get_post_meta($post_id, '_rt_linky_clicks', true);
                echo number_format_i18n($clicks);
                break;
            case 'shortcode':
                echo '<code>[rt-linky id="' . $post_id . '"]</code>';
                break;
        }
    }
}
