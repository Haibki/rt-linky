<?php
/**
 * Plugin Deactivator
 * 
 * @package RTLinky
 */

namespace RT_Linky\Database;

/**
 * Plugin Deactivator Class
 */
class Deactivator
{
    /**
     * Deactivate plugin
     *
     * @return void
     */
    public static function deactivate(): void
    {
        flush_rewrite_rules();
        
        // Clear scheduled events if any
        wp_clear_scheduled_hook('rt_linky_daily_cleanup');
    }
}
