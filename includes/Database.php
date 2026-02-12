<?php
namespace RTLinky;

class Database {
    
    /**
     * Tabellenname für Klicks
     */
    public static function getClicksTable() {
        global $wpdb;
        return $wpdb->prefix . 'rt_linky_clicks';
    }
    
    /**
     * Klick tracken
     */
    public static function trackClick($profile_id, $link_id) {
        global $wpdb;
        $table_name = self::getClicksTable();
        
        // Prüfe ob Tabelle existiert
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return false;
        }
        
        $wpdb->insert($table_name, [
            'profile_id' => intval($profile_id),
            'link_id' => sanitize_text_field($link_id),
            'ip_address' => self::getClientIp(),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'referrer' => sanitize_text_field($_SERVER['HTTP_REFERER'] ?? ''),
            'created_at' => current_time('mysql')
        ]);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Client IP ermitteln
     */
    private static function getClientIp() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }
}