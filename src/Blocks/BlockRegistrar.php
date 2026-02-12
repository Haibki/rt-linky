<?php
/**
 * Gutenberg Block Registrar
 * 
 * @package RTLinky
 */

namespace RT_Linky\Blocks;

/**
 * Block Registrar Class
 */
class BlockRegistrar
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('init', [$this, 'registerBlocks']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockAssets']);
    }

    /**
     * Register blocks
     *
     * @return void
     */
    public function registerBlocks(): void
    {
        register_block_type(RT_LINKY_PLUGIN_DIR . 'build/blocks/rt-linky-block');
    }

    /**
     * Enqueue block assets
     *
     * @return void
     */
    public function enqueueBlockAssets(): void
    {
        $manifest = $this->getManifest();

        if (isset($manifest['js/block-editor.js'])) {
            wp_enqueue_script(
                'rt-linky-block-editor',
                RT_LINKY_BUILD_URL . $manifest['js/block-editor.js']['file'],
                ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
                RT_LINKY_VERSION,
                true
            );

            wp_localize_script('rt-linky-block-editor', 'rtLinkyBlockData', [
                'restUrl'   => rest_url('rt-linky/v1/'),
                'restNonce' => wp_create_nonce('wp_rest'),
                'profiles'  => $this->getProfilesForBlock(),
            ]);
        }
    }

    /**
     * Get profiles for block selector
     *
     * @return array
     */
    private function getProfilesForBlock(): array
    {
        $args = [
            'post_type'      => \RT_Linky\PostType\ProfilePostType::POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];

        $query = new \WP_Query($args);
        $profiles = [];

        foreach ($query->posts as $post) {
            $profiles[] = [
                'id'    => $post->ID,
                'title' => $post->post_title,
                'slug'  => get_post_meta($post->ID, '_rt_linky_slug', true),
            ];
        }

        return $profiles;
    }

    /**
     * Get Vite manifest
     *
     * @return array
     */
    private function getManifest(): array
    {
        $manifest_path = RT_LINKY_BUILD_DIR . '.vite/manifest.json';
        
        if (!file_exists($manifest_path)) {
            return [];
        }

        $content = file_get_contents($manifest_path);
        return json_decode($content, true) ?: [];
    }
}
