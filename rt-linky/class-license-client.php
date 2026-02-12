<?php
/**
 * RT-Linky - License Client
 * Moderne Lizenzverwaltung im RT Stock Manager Style
 * 
 * @package RT_Linky
 * @since 3.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// API URL konfigurierbar machen
if (!defined('RTLINKY_LICENSE_API_URL')) {
    define('RTLINKY_LICENSE_API_URL', 'https://rettoro.de/license-api/verify.php');
}

/**
 * License Client Class
 */
class RT_Linky_License_Client {
    
    private static $instance = null;
    private string $plugin_slug = 'rt-linky';
    private string $license_option;
    private string $api_url;
    
    /**
     * Private constructor for singleton
     */
    private function __construct() {
        $this->license_option = $this->plugin_slug . '_license_key';
        $this->api_url = RTLINKY_LICENSE_API_URL;
        
        add_action('admin_menu', [$this, 'add_license_page'], 99);
        add_action('admin_init', [$this, 'save_license']);
        add_action('admin_notices', [$this, 'license_notice']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }
    
    /**
     * Get singleton instance
     */
    public static function get_instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Enqueue modern styles
     */
    public function enqueue_assets($hook): void {
        if ($hook !== 'rt-linky_page_' . $this->plugin_slug . '-license') {
            return;
        }
        
        wp_enqueue_style(
            'rt-linky-license',
            RT_LINKY_PLUGIN_URL . 'assets/css/license.css',
            [],
            RT_LINKY_VERSION
        );
    }
    
    /**
     * Add license submenu page
     */
    public function add_license_page(): void {
        add_submenu_page(
            'rt-linky',
            __('Lizenz', 'rt-linky'),
            '🔑 ' . __('Lizenz', 'rt-linky'),
            'manage_options',
            $this->plugin_slug . '-license',
            [$this, 'render_license_page']
        );
    }
    
    /**
     * Berechne verbleibende Tage
     */
    private function get_days_remaining(string $expires_at): int {
        $expiry = strtotime($expires_at);
        $today = strtotime('today midnight');
        $days = ceil(($expiry - $today) / 86400);
        return max(0, $days);
    }
    
    /**
     * Render modern license page
     */
    public function render_license_page(): void {
        $license = get_option($this->license_option, '');
        $status = $this->check_license($license);
        $is_valid = !empty($status['valid']);
        
        // Berechne Tage wenn gültig und nicht lifetime
        $days_remaining = null;
        $days_class = '';
        if ($is_valid && !empty($status['license_info']['expires_at']) && $status['license_info']['expires_at'] !== 'lifetime') {
            $days_remaining = $this->get_days_remaining($status['license_info']['expires_at']);
            if ($days_remaining <= 7) {
                $days_class = 'critical';
            } elseif ($days_remaining <= 30) {
                $days_class = 'warning';
            } else {
                $days_class = 'good';
            }
        }
        ?>
        <div class="wrap">
            <div class="rt-linky-license-container">
                <div class="rt-linky-license-card">
                    
                    <!-- Back Link -->
                    <a href="<?php echo admin_url('admin.php?page=rt-linky'); ?>" class="rt-linky-back-link">← Zurück</a>
                    
                    <!-- Version Badge -->
                    <div class="rt-linky-license-version">
                        v<?php echo RT_LINKY_VERSION; ?>
                    </div>
                    
                    <?php if ($is_valid): ?>
                        <!-- Valid License State -->
                        <div class="rt-linky-license-icon valid">✓</div>
                        <div class="rt-linky-license-status valid">
                            <span>●</span> Lizenz aktiv
                        </div>
                        <h2 class="rt-linky-license-title">RT-Linky Pro</h2>
                        <p class="rt-linky-license-subtitle">
                            Vielen Dank für deinen Kauf! Alle Premium-Funktionen sind freigeschaltet.
                        </p>
                        
                        <!-- Tage-Anzeige wenn nicht lifetime -->
                        <?php if ($days_remaining !== null): ?>
                        <div class="rt-linky-license-days <?php echo esc_attr($days_class); ?>">
                            <span class="rt-linky-license-days-number"><?php echo $days_remaining; ?></span>
                            <span class="rt-linky-license-days-label"><?php echo $days_remaining === 1 ? 'Tag' : 'Tage'; ?> verbleibend</span>
                        </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <!-- Invalid License State -->
                        <div class="rt-linky-license-icon invalid">🔒</div>
                        <div class="rt-linky-license-status invalid">
                            <span>●</span> Keine gültige Lizenz
                        </div>
                        <h2 class="rt-linky-license-title">RT-Linky aktivieren</h2>
                        <p class="rt-linky-license-subtitle">
                            Gib deinen Lizenzschlüssel ein, um alle Premium-Funktionen freizuschalten.
                        </p>
                    <?php endif; ?>
                    
                    <!-- Messages -->
                    <?php if (isset($_GET['settings-updated']) && !$is_valid): ?>
                        <div class="rt-linky-license-message error">
                            <?php echo esc_html($status['message'] ?? 'Lizenz ungültig oder nicht gefunden'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['license_error']) && $_GET['license_error'] === 'invalid_format'): ?>
                        <div class="rt-linky-license-message error">
                            Ungültiges Format. Der Schlüssel muss mit "RT-L-" beginnen.
                        </div>
                    <?php endif; ?>
                    
                    <!-- License Form -->
                    <?php if (!$is_valid): ?>
                        <form method="post">
                            <?php wp_nonce_field('save_' . $this->plugin_slug . '_license'); ?>
                            
                            <div class="rt-linky-license-input-wrapper">
                                <input 
                                    type="text" 
                                    name="rt_license_key" 
                                    id="rt_license_key"
                                    class="rt-linky-license-input"
                                    value="<?php echo esc_attr($license); ?>"
                                    placeholder="RT-L-XXXXXXXXXXXXXXXX..."
                                    autocomplete="off"
                                    spellcheck="false"
                                >
                            </div>
                            
                            <button type="submit" class="rt-linky-license-button">
                                Lizenz aktivieren
                            </button>
                        </form>
                        
                        <div class="rt-linky-license-help">
                            <p>Du hast noch keine Lizenz? <a href="https://rettoro.de" target="_blank">Jetzt kaufen</a></p>
                        </div>
                        
                    <?php else: ?>
                        <!-- Active License Info -->
                        <div class="rt-linky-license-info">
                            <div class="rt-linky-license-info-row">
                                <span class="rt-linky-license-info-label">Lizenzschlüssel</span>
                                <span class="rt-linky-license-info-value">
                                    <?php 
                                    $key = $license;
                                    echo substr($key, 0, 8) . '-****-****-' . substr($key, -4);
                                    ?>
                                </span>
                            </div>
                            <div class="rt-linky-license-info-row">
                                <span class="rt-linky-license-info-label">Status</span>
                                <span class="rt-linky-license-info-value" style="color: #00a32a;">✓ Gültig</span>
                            </div>
                            <?php if (!empty($status['license_info']['expires_at']) && $status['license_info']['expires_at'] !== 'lifetime'): ?>
                            <div class="rt-linky-license-info-row">
                                <span class="rt-linky-license-info-label">Gültig bis</span>
                                <span class="rt-linky-license-info-value">
                                    <?php echo esc_html(date_i18n('d.m.Y', strtotime($status['license_info']['expires_at']))); ?>
                                    <span class="rt-linky-license-info-days <?php echo esc_attr($days_class); ?>">(<?php echo $days_remaining; ?> <?php echo $days_remaining === 1 ? 'Tag' : 'Tage'; ?>)</span>
                                </span>
                            </div>
                            <?php else: ?>
                            <div class="rt-linky-license-info-row">
                                <span class="rt-linky-license-info-label">Lizenztyp</span>
                                <span class="rt-linky-license-info-value">♾️ Lifetime</span>
                            </div>
                            <?php endif; ?>
                            <div class="rt-linky-license-info-row">
                                <span class="rt-linky-license-info-label">Domain</span>
                                <span class="rt-linky-license-info-value"><code><?php echo esc_html($this->get_domain()); ?></code></span>
                            </div>
                        </div>
                        
                        <form method="post">
                            <?php wp_nonce_field('save_' . $this->plugin_slug . '_license'); ?>
                            <input type="hidden" name="rt_license_key" value="">
                            <button type="submit" class="rt-linky-license-button rt-linky-license-button--danger" onclick="return confirm('Bist du sicher, dass du die Lizenz entfernen möchtest?');">
                                Lizenz entfernen
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <!-- Features List -->
                    <div class="rt-linky-license-features">
                        <h3>Funktionen</h3>
                        <ul>
                            <li class="<?php echo $is_valid ? 'available' : 'locked'; ?>">
                                <span><?php echo $is_valid ? '✓' : '•'; ?></span>
                                Unbegrenzte Profile
                            </li>
                            <li class="<?php echo $is_valid ? 'available' : 'locked'; ?>">
                                <span><?php echo $is_valid ? '✓' : '•'; ?></span>
                                Eigene Hintergrundbilder
                            </li>
                            <li class="<?php echo $is_valid ? 'available' : 'locked'; ?>">
                                <span><?php echo $is_valid ? '✓' : '•'; ?></span>
                                Verifiziert-Badge
                            </li>
                            <li class="<?php echo $is_valid ? 'available' : 'locked'; ?>">
                                <span><?php echo $is_valid ? '✓' : '•'; ?></span>
                                Detaillierte Statistiken
                            </li>
                            <li class="<?php echo $is_valid ? 'available' : 'locked'; ?>">
                                <span><?php echo $is_valid ? '✓' : '•'; ?></span>
                                Link-Icons
                            </li>
                            <li class="<?php echo $is_valid ? 'available' : 'locked'; ?>">
                                <span><?php echo $is_valid ? '✓' : '•'; ?></span>
                                Premium Support
                            </li>
                        </ul>
                    </div>
                    
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Save license
     */
    public function save_license(): void {
        if (!isset($_POST['rt_license_key'])) {
            return;
        }
        
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'save_' . $this->plugin_slug . '_license')) {
            return;
        }
        
        $license = sanitize_text_field(trim($_POST['rt_license_key']));
        
        // Validate format only if not empty (empty = delete license)
        if (!empty($license) && !preg_match('/^RT-L-[a-zA-Z0-9!@#$%^&*()\-_=+\[\]{}<>?]+$/', $license)) {
            wp_redirect(add_query_arg('license_error', 'invalid_format'));
            exit;
        }
        
        update_option($this->license_option, $license);
        delete_transient($this->plugin_slug . '_license_status');
        
        wp_redirect(add_query_arg('settings-updated', 'true'));
        exit;
    }
    
    /**
     * Check license via API (with caching)
     */
    public function check_license(string $license): array {
        $cached = get_transient($this->plugin_slug . '_license_status');
        if ($cached !== false) {
            return $cached;
        }
        
        $license = trim($license);
        
        if (empty($license)) {
            return ['valid' => false, 'message' => 'Kein Lizenzschlüssel eingetragen'];
        }
        
        if (!preg_match('/^RT-L-[a-zA-Z0-9!@#$%^&*()\-_=+\[\]{}<>?]+$/', $license)) {
            return ['valid' => false, 'message' => 'Ungültiges Lizenzschlüssel-Format'];
        }
        
        $response = wp_remote_post($this->api_url, [
            'timeout' => 30,
            'sslverify' => true,
            'body' => [
                'license_key' => $license,
                'plugin_slug' => $this->plugin_slug,
                'domain' => $this->get_domain()
            ]
        ]);
        
        if (is_wp_error($response)) {
            return ['valid' => false, 'message' => 'Verbindungsfehler: ' . $response->get_error_message()];
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (empty($body)) {
            return ['valid' => false, 'message' => 'Ungültige API-Antwort'];
        }
        
        // Spezifische Fehlermeldungen übersetzen
        if (empty($body['valid']) && !empty($body['reason'])) {
            $error_messages = [
                'missing_parameters' => 'Fehlende Parameter. Bitte kontaktiere den Support.',
                'no_license_store' => 'Lizenz-Datenbank nicht gefunden.',
                'license_not_found' => 'Lizenz nicht gefunden.',
                'license_inactive' => 'Lizenz ist deaktiviert oder gesperrt.',
                'license_expired' => 'Lizenz ist abgelaufen.',
                'domain_mismatch' => 'Domain stimmt nicht überein.',
                'plugin_not_licensed' => 'Diese Lizenz gilt nicht für RT-Linky.',
                'corrupted_data' => 'Lizenz-Datenbank beschädigt.'
            ];
            
            if (isset($error_messages[$body['reason']])) {
                $body['message'] = $error_messages[$body['reason']];
            }
        }
        
        $cache_time = !empty($body['valid']) ? 900 : 300;
        set_transient($this->plugin_slug . '_license_status', $body, $cache_time);
        
        return $body;
    }
    
    /**
     * Get current domain
     */
    private function get_domain(): string {
        $domain = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $domain = preg_replace('/^www\./', '', $domain);
        $domain = preg_replace('/:\d+$/', '', $domain);
        return strtolower(trim($domain));
    }
    
    /**
     * Check if license is valid
     */
    public function is_valid(): bool {
        $license = get_option($this->license_option, '');
        $status = $this->check_license($license);
        return !empty($status['valid']);
    }
    
    /**
     * Admin notice for missing license
     */
    public function license_notice(): void {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'rt-linky_page_' . $this->plugin_slug . '-license') {
            return;
        }
        
        $license = get_option($this->license_option, '');
        if (empty($license)) {
            echo '<div class="notice notice-warning is-dismissible">
                <p><strong>RT-Linky:</strong> ' . 
                __('Bitte aktiviere deine Lizenz:', 'rt-linky') . 
                ' <a href="' . esc_url(admin_url('admin.php?page=rt-linky-license')) . '">' . 
                __('Lizenz aktivieren', 'rt-linky') . '</a></p>
            </div>';
        }
    }
    
    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}

// Initialize ONLY ONCE using singleton pattern
if (!isset($GLOBALS['rt_linky_license_client'])) {
    $GLOBALS['rt_linky_license_client'] = RT_Linky_License_Client::get_instance();
}