<?php
/**
 * RT-Linky - License Client
 * Lizenzprüfung für Rettoro License Manager
 * 
 * @package RT_Linky
 * @since 3.0.0
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
    
    /**
     * Instance tracking to prevent multiple initializations
     */
    private static $instance = null;
    
    /**
     * Plugin slug
     */
    private string $plugin_slug = 'rt-linky';
    
    /**
     * Option name for license key
     */
    private string $license_option;
    
    /**
     * API URL
     */
    private string $api_url;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->license_option = $this->plugin_slug . '_license_key';
        $this->api_url = RTLINKY_LICENSE_API_URL;
        
        add_action('admin_menu', [$this, 'add_license_page'], 99);
        add_action('admin_init', [$this, 'save_license']);
        add_action('admin_notices', [$this, 'license_notice']);
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
     * Render license page
     */
    public function render_license_page(): void {
        $license = get_option($this->license_option, '');
        $status = $this->check_license($license);
        $is_valid = !empty($status['valid']);
        ?>
        <div class="wrap">
            <h1><?php _e('Lizenz aktivieren', 'rt-linky'); ?></h1>
            
            <?php if (isset($_GET['license_error']) && $_GET['license_error'] === 'invalid_format'): ?>
                <div class="notice notice-error">
                    <p>❌ <?php _e('Ungültiges Lizenzschlüssel-Format! Der Schlüssel muss mit "RT-L-" beginnen.', 'rt-linky'); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($is_valid): ?>
                <div class="notice notice-success">
                    <p>✅ <?php _e('Lizenz ist aktiv und gültig!', 'rt-linky'); ?></p>
                    <?php if (!empty($status['license_info']['expires_at']) && $status['license_info']['expires_at'] !== 'lifetime'): ?>
                        <p><?php _e('Gültig bis:', 'rt-linky'); ?> <?php echo esc_html($status['license_info']['expires_at']); ?></p>
                    <?php else: ?>
                        <p>♾️ <?php _e('Lifetime-Lizenz', 'rt-linky'); ?></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="notice notice-error">
                    <p>❌ <?php echo esc_html($status['message'] ?? __('Lizenz ungültig oder nicht gefunden', 'rt-linky')); ?></p>
                </div>
                
                <div class="notice notice-warning">
                    <p><?php _e('Ohne gültige Lizenz funktioniert das Plugin nur eingeschränkt.', 'rt-linky'); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <?php wp_nonce_field('save_' . $this->plugin_slug . '_license'); ?>
                <style>
                    #rt_license_key {
                        font-family: monospace;
                        font-size: 14px;
                        width: 100%;
                        max-width: 600px;
                        padding: 10px;
                        letter-spacing: 0.5px;
                    }
                    .rt-license-input-wrap {
                        background: #f6f7f7;
                        padding: 15px;
                        border: 1px solid #c3c4c7;
                        border-radius: 4px;
                        max-width: 650px;
                    }
                    .rt-license-format-error {
                        color: #d63638;
                        font-size: 12px;
                        margin-top: 5px;
                        display: none;
                    }
                </style>
                <script>
                jQuery(document).ready(function($) {
                    $('#rt_license_key').on('input', function() {
                        var val = $(this).val().trim();
                        var $error = $('#rt-license-format-error');
                        
                        if (val === '') {
                            $error.hide();
                            return;
                        }
                        
                        // Prüfe Format: muss mit RT-L- beginnen
                        if (!val.match(/^RT-L-[a-zA-Z0-9!@#$%^&*()\-_=+\[\]{}<>?]{48,}$/)) {
                            $error.text('⚠️ Ungültiges Format! Lizenzschlüssel muss mit "RT-L-" beginnen und mindestens 56 Zeichen haben.').show();
                        } else {
                            $error.hide();
                        }
                    });
                });
                </script>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="rt_license_key"><?php _e('Lizenzschlüssel', 'rt-linky'); ?></label>
                        </th>
                        <td>
                            <div class="rt-license-input-wrap">
                                <input type="text" 
                                       name="rt_license_key" 
                                       id="rt_license_key"
                                       value="<?php echo esc_attr($license); ?>"
                                       class="large-text"
                                       placeholder="RT-L-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX..."
                                       autocomplete="off"
                                       spellcheck="false">
                                <div id="rt-license-format-error" class="rt-license-format-error"></div>
                                <p class="description" style="margin-top: 10px;">
                                    <?php _e('Gib deinen Lizenzschlüssel ein. Format: RT-L-xxxxxxxx... (56 Zeichen)', 'rt-linky'); ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Lizenz speichern', 'rt-linky')); ?>
            </form>
            
            <hr style="margin: 30px 0;">
            
            <h2><?php _e('Lizenz-Status', 'rt-linky'); ?></h2>
            <table class="widefat" style="max-width: 700px;">
                <tr>
                    <td><strong><?php _e('Plugin:', 'rt-linky'); ?></strong></td>
                    <td>RT-Linky</td>
                </tr>
                <tr>
                    <td><strong><?php _e('Plugin-Slug:', 'rt-linky'); ?></strong></td>
                    <td><code><?php echo esc_html($this->plugin_slug); ?></code></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Domain:', 'rt-linky'); ?></strong></td>
                    <td><code><?php echo esc_html($this->get_domain()); ?></code></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Status:', 'rt-linky'); ?></strong></td>
                    <td>
                        <?php if ($is_valid): ?>
                            <span style="color: #00a32a;">✅ <?php _e('Gültig', 'rt-linky'); ?></span>
                        <?php else: ?>
                            <span style="color: #d63638;">❌ <?php _e('Ungültig', 'rt-linky'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            
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
        
        // Trim license key to remove spaces
        $license = sanitize_text_field(trim($_POST['rt_license_key']));
        
        // Validate format
        if (!empty($license) && !preg_match('/^RT-L-[a-zA-Z0-9!@#$%^&*()\-_=+\[\]{}<>?]+$/', $license)) {
            wp_redirect(add_query_arg('license_error', 'invalid_format'));
            exit;
        }
        
        update_option($this->license_option, $license);
        
        // Clear cache
        delete_transient($this->plugin_slug . '_license_status');
        
        wp_redirect(add_query_arg('settings-updated', 'true'));
        exit;
    }
    
    /**
     * Check license via API (with caching)
     */
    public function check_license(string $license): array {
        // Return from cache if available
        $cached = get_transient($this->plugin_slug . '_license_status');
        if ($cached !== false) {
            return $cached;
        }
        
        // Trim license
        $license = trim($license);
        
        if (empty($license)) {
            return [
                'valid' => false, 
                'message' => __('Kein Lizenzschlüssel eingetragen', 'rt-linky')
            ];
        }
        
        // Validate format before API call
        if (!preg_match('/^RT-L-[a-zA-Z0-9!@#$%^&*()\-_=+\[\]{}<>?]+$/', $license)) {
            return [
                'valid' => false,
                'message' => __('Ungültiges Lizenzschlüssel-Format. Der Schlüssel muss mit "RT-L-" beginnen.', 'rt-linky')
            ];
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
            return [
                'valid' => false, 
                'message' => __('Verbindungsfehler:', 'rt-linky') . ' ' . $response->get_error_message()
            ];
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (empty($body)) {
            return [
                'valid' => false, 
                'message' => __('Ungültige API-Antwort. Prüfe ob die API-URL erreichbar ist.', 'rt-linky')
            ];
        }
        
        // Spezifische Fehlermeldungen übersetzen
        if (empty($body['valid']) && !empty($body['reason'])) {
            $error_messages = [
                'missing_parameters' => 'Fehlende Parameter. Bitte kontaktiere den Support.',
                'no_license_store' => 'Lizenz-Datenbank nicht gefunden. API nicht korrekt installiert.',
                'license_not_found' => 'Lizenz nicht gefunden. Bitte erstelle eine neue Lizenz im License Manager.',
                'license_inactive' => 'Lizenz ist deaktiviert oder gesperrt.',
                'license_expired' => 'Lizenz ist abgelaufen am: ' . ($body['expired_on'] ?? 'Unbekannt'),
                'domain_mismatch' => 'Domain stimmt nicht überein! Diese Lizenz wurde für "' . ($body['expected_domain'] ?? 'Unbekannt') . '" erstellt, aber du verwendest sie auf "' . $this->get_domain() . '".'
                    . ' Bitte erstelle eine neue Lizenz für diese Domain im License Manager.',
                'plugin_not_licensed' => 'Diese Lizenz gilt nicht für RT-Linky.',
                'corrupted_data' => 'Lizenz-Datenbank beschädigt.'
            ];
            
            if (isset($error_messages[$body['reason']])) {
                $body['message'] = $error_messages[$body['reason']];
            }
        }
        
        // Cache result (15 minutes on success, 5 minutes on error)
        $cache_time = !empty($body['valid']) ? 900 : 300;
        set_transient($this->plugin_slug . '_license_status', $body, $cache_time);
        
        return $body;
    }
    
    /**
     * Get current domain (normalized)
     * Entfernt www. und port für konsistente Prüfung
     */
    private function get_domain(): string {
        $domain = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        
        // Remove www.
        $domain = preg_replace('/^www\./', '', $domain);
        
        // Remove port if present
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
            echo '<div class="notice notice-warning">
                <p>🔑 <strong>RT-Linky</strong>: ' . 
                __('Bitte aktiviere deine Lizenz unter', 'rt-linky') . 
                ' <a href="' . esc_url(admin_url('admin.php?page=rt-linky-license')) . '">' . 
                __('RT-Linky → Lizenz', 'rt-linky') . '</a></p>
            </div>';
        }
    }
}

// Initialize license client only once
if (!isset($GLOBALS['rt_linky_license_client'])) {
    $GLOBALS['rt_linky_license_client'] = new RT_Linky_License_Client();
}
