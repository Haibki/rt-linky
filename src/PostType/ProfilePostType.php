<?php
/**
 * Custom Post Type für RT-Linky Profile
 */

namespace RT\Linky\PostType;

class ProfilePostType {
    
    public function __construct() {
        add_action('init', [$this, 'register'], 0);
    }
    
    public function register(): void {
        $labels = [
            'name'                  => _x('Link-in-Bio Profile', 'Post Type General Name', 'rt-linky'),
            'singular_name'         => _x('Profil', 'Post Type Singular Name', 'rt-linky'),
            'menu_name'             => __('RT-Linky', 'rt-linky'),
            'name_admin_bar'        => __('RT-Linky Profil', 'rt-linky'),
            'archives'              => __('Profil Archive', 'rt-linky'),
            'attributes'            => __('Profil Attribute', 'rt-linky'),
            'parent_item_colon'     => __('Eltern Profil:', 'rt-linky'),
            'all_items'             => __('Alle Profile', 'rt-linky'),
            'add_new_item'          => __('Neues Profil erstellen', 'rt-linky'),
            'add_new'               => __('Neu erstellen', 'rt-linky'),
            'new_item'              => __('Neues Profil', 'rt-linky'),
            'edit_item'             => __('Profil bearbeiten', 'rt-linky'),
            'update_item'           => __('Profil aktualisieren', 'rt-linky'),
            'view_item'             => __('Profil ansehen', 'rt-linky'),
            'view_items'            => __('Profile ansehen', 'rt-linky'),
            'search_items'          => __('Profil suchen', 'rt-linky'),
            'not_found'             => __('Nicht gefunden', 'rt-linky'),
            'not_found_in_trash'    => __('Nicht im Papierkorb gefunden', 'rt-linky'),
            'featured_image'        => __('Avatar Bild', 'rt-linky'),
            'set_featured_image'    => __('Avatar setzen', 'rt-linky'),
            'remove_featured_image' => __('Avatar entfernen', 'rt-linky'),
            'use_featured_image'    => __('Als Avatar verwenden', 'rt-linky'),
            'insert_into_item'      => __('In Profil einfügen', 'rt-linky'),
            'uploaded_to_this_item' => __('Zu diesem Profil hochgeladen', 'rt-linky'),
            'items_list'            => __('Profile Liste', 'rt-linky'),
            'items_list_navigation' => __('Profile Liste Navigation', 'rt-linky'),
            'filter_items_list'     => __('Filter Profile Liste', 'rt-linky'),
        ];
        
        $args = [
            'label'                 => __('Profil', 'rt-linky'),
            'description'           => __('RT-Linky Profile', 'rt-linky'),
            'labels'                => $labels,
            'supports'              => ['title', 'thumbnail', 'author'],
            'taxonomies'            => [],
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 25,
            'menu_icon'             => 'dashicons-admin-links',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'rewrite'               => ['slug' => 'bio', 'with_front' => false],
            'capability_type'       => 'post',
            'show_in_rest'          => true,
            'rest_base'             => 'rt_linky_profiles',
        ];
        
        register_post_type('rt_linky_profile', $args);
    }
}