<?php
/**
 * Custom Post Type für RT-Linky Profile
 */

namespace RT\Linky\PostType;

class ProfilePostType {
    
    public function __construct() {
        add_action('init', [$this, 'register']);
    }
    
    public static function register(): void {
        $labels = [
            'name'                  => __('Link-in-Bio Profile', 'rt-linky'),
            'singular_name'         => __('Profil', 'rt-linky'),
            'menu_name'             => __('RT-Linky', 'rt-linky'),
            'add_new'               => __('Neues Profil', 'rt-linky'),
            'add_new_item'          => __('Neues Profil erstellen', 'rt-linky'),
            'edit_item'             => __('Profil bearbeiten', 'rt-linky'),
            'new_item'              => __('Neues Profil', 'rt-linky'),
            'view_item'             => __('Profil ansehen', 'rt-linky'),
            'search_items'          => __('Profile suchen', 'rt-linky'),
            'not_found'             => __('Keine Profile gefunden', 'rt-linky'),
            'not_found_in_trash'    => __('Keine Profile im Papierkorb', 'rt-linky'),
        ];
        
        $args = [
            'labels'                => $labels,
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 30,
            'menu_icon'             => 'dashicons-admin-links',
            'query_var'             => true,
            'rewrite'               => ['slug' => 'bio'],
            'capability_type'       => 'post',
            'has_archive'           => false,
            'hierarchical'          => false,
            'supports'              => ['title'],
            'show_in_rest'          => false,
        ];
        
        register_post_type('rt_linky_profile', $args);
    }
}