<?php
namespace RTLinky;

class License {
    private static $instance = null;
    private $option_name = 'rt_linky_license';
    private $cache = null;
    
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Prüft ob Pro-Lizenz aktiv ist
     */
    public function isPro() {
        if ($this->cache !== null) {
            return $this->cache;
        }
        
        $license = get_option($this->option_name);
        
        if (empty($license) || empty($license['key'])) {
            $this->cache = false;
            return false;
        }
        
        // Prüfe ob Lizenz abgelaufen ist
        if (!empty($license['expires']) && strtotime($license['expires']) < time()) {
            // Lizenz abgelaufen - behalte Bestand aber kein Pro
            $this->cache = false;
            return false;
        }
        
        $this->cache = true;
        return true;
    }
    
    /**
     * Prüft ob Lizenz abgelaufen ist (für Bestandsschutz)
     */
    public function isExpired() {
        $license = get_option($this->option_name);
        
        if (empty($license) || empty($license['expires'])) {
            return false;
        }
        
        return strtotime($license['expires']) < time();
    }
    
    /**
     * Hat Benutzer Bestandsschutz (abgelaufen aber Links vorhanden)
     */
    public function hasLegacyAccess() {
        return $this->isExpired() && $this->getLinkCount() > 0;
    }
    
    /**
     * Anzahl existierender Links des Users
     */
    public function getLinkCount() {
        $count = wp_count_posts('rt_linky_profile');
        return intval($count->publish) + intval($count->draft);
    }
    
    /**
     * Kann neuer Link erstellt werden?
     */
    public function canCreateLink() {
        // Pro-User: immer erlaubt
        if ($this->isPro()) {
            return true;
        }
        
        // Abgelaufene Lizenz: nicht erlaubt, aber Bestand bleibt
        if ($this->isExpired()) {
            return false;
        }
        
        // Free-User: max 2 Links
        return $this->getLinkCount() < 2;
    }
    
    /**
     * Verbleibende Links für Free-Version
     */
    public function getRemainingLinks() {
        if ($this->isPro()) {
            return PHP_INT_MAX;
        }
        
        $current = $this->getLinkCount();
        return max(0, 2 - $current);
    }
    
    /**
     * Lizenz speichern
     */
    public function saveLicense($key, $expires = '') {
        $data = [
            'key' => sanitize_text_field($key),
            'activated' => true,
            'activated_at' => current_time('mysql'),
            'expires' => $expires ? sanitize_text_field($expires) : date('Y-m-d H:i:s', strtotime('+1 year'))
        ];
        
        update_option($this->option_name, $data);
        $this->cache = null; // Cache zurücksetzen
    }
    
    /**
     * Lizenz entfernen
     */
    public function removeLicense() {
        delete_option($this->option_name);
        $this->cache = null;
    }
    
    /**
     * Lizenz-Key abrufen (maskiert)
     */
    public function getLicenseKey() {
        $license = get_option($this->option_name);
        if (empty($license['key'])) {
            return '';
        }
        
        $key = $license['key'];
        // Zeige nur letzte 4 Zeichen
        return str_repeat('•', strlen($key) - 4) . substr($key, -4);
    }
    
    /**
     * Ablaufdatum formatiert
     */
    public function getExpiryDate() {
        $license = get_option($this->option_name);
        if (empty($license['expires'])) {
            return '';
        }
        
        return date_i18n(get_option('date_format'), strtotime($license['expires']));
    }
}