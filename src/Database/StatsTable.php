<?php
/**
 * Database Tables for Statistics
 * 
 * @package RTLinky
 */

namespace RT_Linky\Database;

/**
 * Stats Table Handler
 */
class StatsTable
{
    /**
     * Get views table name
     *
     * @return string
     */
    public static function getViewsTable(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'rt_linky_views';
    }

    /**
     * Get clicks table name
     *
     * @return string
     */
    public static function getClicksTable(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'rt_linky_clicks';
    }

    /**
     * Create tables
     *
     * @return void
     */
    public static function create(): void
    {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $views_table = self::getViewsTable();
        $clicks_table = self::getClicksTable();

        $sql = "CREATE TABLE IF NOT EXISTS {$views_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT,
            referrer VARCHAR(500) DEFAULT NULL,
            country VARCHAR(10) DEFAULT NULL,
            device VARCHAR(20) DEFAULT NULL,
            browser VARCHAR(50) DEFAULT NULL,
            viewed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY viewed_at (viewed_at),
            KEY ip_address (ip_address)
        ) {$charset_collate};";

        $sql .= "CREATE TABLE IF NOT EXISTS {$clicks_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            link_url VARCHAR(500) NOT NULL,
            link_label VARCHAR(200) DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT,
            clicked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY clicked_at (clicked_at),
            KEY link_url (link_url(191))
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Drop tables
     *
     * @return void
     */
    public static function drop(): void
    {
        global $wpdb;
        
        $wpdb->query("DROP TABLE IF EXISTS " . self::getViewsTable());
        $wpdb->query("DROP TABLE IF EXISTS " . self::getClicksTable());
    }
}
