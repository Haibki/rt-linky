<?php
/**
 * Server-side render for RT-Linky Profile block
 *
 * @package RTLinky
 */

$profile_id = $attributes['profileId'] ?? 0;
$slug = $attributes['slug'] ?? '';

if (!$profile_id && !$slug) {
    return '<p>' . __('Please select a profile.', 'rt-linky') . '</p>';
}

$args = [
    'post_type'      => 'rt_linky_profile',
    'posts_per_page' => 1,
    'post_status'    => 'publish',
];

if ($profile_id) {
    $args['p'] = $profile_id;
} else {
    $args['meta_query'] = [
        [
            'key'     => '_rt_linky_slug',
            'value'   => $slug,
            'compare' => '=',
        ],
    ];
}

$query = new WP_Query($args);

if (!$query->have_posts()) {
    return '<p>' . __('Profile not found.', 'rt-linky') . '</p>';
}

$profile = $query->posts[0];

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
