<?php
namespace RTLinky;

class License {
    private static $instance = null;
    private $cache = null;
    
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function isPro() {
        if ($this->cache !== null) {
            return $this->cache;
        }
        
        $this->cache = false;
        
        if (class_exists('RT_Linky_License_Client')) {
            $client = \RT_Linky_License_Client::get_instance();
            
            if (!is_object($client)) {
                return false;
            }
            
            if (method_exists($client, 'is_active')) {
                $this->cache = (bool) $client->is_active();
                return $this->cache;
            }
            
            $license_data = get_option('rt_linky_license_client_license_data');
            if (is_array($license_data) && isset($license_data['status'])) {
                $this->cache = ($license_data['status'] === 'active');
                return $this->cache;
            }
        }
        
        $license = get_option('rt_linky_license');
        if (is_array($license) && !empty($license['active'])) {
            $this->cache = true;
        }
        
        return $this->cache;
    }
    
    public function getProfileCount() {
        $count = wp_count_posts('rt_linky_profile');
        if (!is_object($count)) {
            return 0;
        }
        return intval($count->publish) + intval($count->draft);
    }
    
    public function canCreateProfile() {
        if ($this->isPro()) {
            return true;
        }
        return $this->getProfileCount() < 2;
    }
    
    public function getRemainingProfiles() {
        if ($this->isPro()) {
            return 'unlimited';
        }
        $remaining = 2 - $this->getProfileCount();
        return max(0, $remaining);
    }
}