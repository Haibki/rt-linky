<?php
/**
 * Plugin Activator
 * 
 * @package RTLinky
 */

namespace RT_Linky\Database;

/**
 * Plugin Activator Class
 */
class Activator
{
    /**
     * Activate plugin
     *
     * @return void
     */
    public static function activate(): void
    {
        // Create database tables
        StatsTable::create();
        
        // Register post type and flush rewrite rules
        $post_type = new \RT_Linky\PostType\ProfilePostType();
        $post_type->register();
        
        flush_rewrite_rules();
        
        // Set default capabilities
        $role = get_role('administrator');
        if ($role) {
            $role->add_cap('edit_rt_linky_profile');
            $role->add_cap('read_rt_linky_profile');
            $role->add_cap('delete_rt_linky_profile');
            $role->add_cap('edit_rt_linky_profiles');
            $role->add_cap('edit_others_rt_linky_profiles');
            $role->add_cap('publish_rt_linky_profiles');
            $role->add_cap('read_private_rt_linky_profiles');
        }
        
        // Set version
        update_option('rt_linky_version', RT_LINKY_VERSION);
    }
}
