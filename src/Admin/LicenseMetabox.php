<?php
/**
 * RT-Linky License Metabox
 */

namespace RT\Linky\Admin;

use RT\Linky\LicenseConfig;

class LicenseMetabox {
    
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'registerMetabox']);
        add_action('save_post', [$this, 'saveMetabox'], 10, 2);
        add_action('admin_init', [$this, 'handleLicenseActivation']);
    }
    
    public function registerMetabox(): void {
        add_meta_box(
            'rt_linky_license',
            __('RT-Linky Lizenz', 'rt-linky'),
            [$this, 'renderMetabox'],
            'rt_linky_profile',
            'side',
            'high'
        );
    }
    
    /**
     * Lizenz-Aktivierung über POST Request
     */
    public function handleLicenseActivation(): void {
        if (!isset($_POST['rt_linky_license_action']) || !isset($_POST['rt_linky_license_nonce'])) {
            return;
        }
        
        if (!wp_verify_nonce($_POST['rt_linky_license_nonce'], 'rt_linky_license_action')) {
            add_settings_error('rt_linky_license', 'invalid_nonce', 'Sicherheitsprüfung fehlgeschlagen.', 'error');
            return;
        }
        
        if (!current_user_can('manage_options')) {
            add_settings_error('rt_linky_license', 'no_permission', 'Keine Berechtigung.', 'error');
            return;
        }
        
        $action = sanitize_text_field($_POST['rt_linky_license_action']);
        
        if ($action === 'activate' && isset($_POST['rt_linky_license_key'])) {
            $licenseKey = sanitize_text_field($_POST['rt_linky_license_key']);
            
            // Hier später echte Lizenz-Prüfung implementieren
            // Für jetzt: Aktivieren wenn Key nicht leer
            if (!empty($licenseKey)) {
                update_option('rt_linky_license_key', $licenseKey);
                LicenseConfig::activatePro();
                add_settings_error('rt_linky_license', 'activated', 'Pro-Lizenz erfolgreich aktiviert!', 'success');
            } else {
                add_settings_error('rt_linky_license', 'empty_key', 'Bitte Lizenz-Key eingeben.', 'error');
            }
        }
        
        if ($action === 'deactivate') {
            delete_option('rt_linky_license_key');
            LicenseConfig::deactivatePro();
            add_settings_error('rt_linky_license', 'deactivated', 'Pro-Lizenz deaktiviert.', 'info');
        }
    }
    
    public function renderMetabox($post): void {
        wp_nonce_field('rt_linky_license_nonce', 'rt_linky_license_nonce');
        
        $isPro = LicenseConfig::isPro();
        $licenseKey = get_option('rt_linky_license_key', '');
        $showFooter = get_post_meta($post->ID, '_rt_linky_show_footer', true);
        $showVerified = get_post_meta($post->ID, '_rt_linky_verified_badge', true);
        
        // Settings Errors anzeigen
        settings_errors('rt_linky_license');
        
        if (!$isPro) {
            // FREE Version - Lizenz eingeben
            ?>
            <div class="rt-linky-license-box rt-linky-free">
                <div class="rt-linky-license-badge">
                    <span class="dashicons dashicons-lock"></span>
                    <strong>FREE Version</strong>
                </div>
                
                <ul class="rt-linky-features-list">
                    <li class="available">✓ Max. 2 Links</li>
                    <li class="available">✓ 2 Icons (Link, E-Mail)</li>
                    <li class="locked">✗ Kein Hintergrundbild</li>
                    <li class="locked">✗ Footer immer sichtbar</li>
                    <li class="locked">✗ Keine Link-Untertitel</li>
                    <li class="locked">✗ Kein Verifiziert-Badge</li>
                </ul>
                
                <div class="rt-linky-license-form">
                    <h4>🔑 Lizenz aktivieren</h4>
                    <form method="post" action="">
                        <?php wp_nonce_field('rt_linky_license_action', 'rt_linky_license_nonce'); ?>
                        <input type="hidden" name="rt_linky_license_action" value="activate">
                        
                        <p>
                            <input type="text" 
                                   name="rt_linky_license_key" 
                                   class="widefat" 
                                   placeholder="XXXX-XXXX-XXXX-XXXX"
                                   value="<?php echo esc_attr($licenseKey); ?>">
                        </p>
                        
                        <p>
                            <button type="submit" class="button button-primary widefat">
                                Lizenz aktivieren
                            </button>
                        </p>
                    </form>
                </div>
                
                <div class="rt-linky-upgrade-box">
                    <h4>🚀 Noch keine Lizenz?</h4>
                    <p>Upgrade auf Pro für alle Features:</p>
                    <ul>
                        <li>✓ Unbegrenzte Links</li>
                        <li>✓ 25+ Icons</li>
                        <li>✓ Hintergrundbild</li>
                        <li>✓ Footer ausblendbar</li>
                        <li>✓ Link-Untertitel</li>
                        <li>✓ Verifiziert-Badge</li>
                    </ul>
                    <a href="https://rettoro.de/rt-linky" target="_blank" class="button button-primary rt-linky-upgrade-btn">
                        Jetzt Pro kaufen
                    </a>
                </div>
                
                <div class="rt-linky-dev-tools">
                    <small>Entwickler-Optionen:</small><br>
                    <button type="button" class="button" id="rt-linky-activate-pro" style="margin-top: 5px;">
                        Pro aktivieren (Test)
                    </button>
                </div>
            </div>
            
            <style>
                .rt-linky-license-box { padding: 15px; background: #f0f0f1; border-radius: 4px; }
                .rt-linky-license-badge { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #fff; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #ffb900; }
                .rt-linky-features-list { margin: 0 0 20px; padding-left: 0; list-style: none; }
                .rt-linky-features-list li { padding: 6px 0; border-bottom: 1px solid #e0e0e0; }
                .rt-linky-features-list li:last-child { border-bottom: none; }
                .rt-linky-features-list .available { color: #00a32a; }
                .rt-linky-features-list .locked { color: #999; text-decoration: line-through; }
                .rt-linky-license-form { background: #fff; padding: 15px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #c3c4c7; }
                .rt-linky-license-form h4 { margin: 0 0 10px; }
                .rt-linky-license-form p { margin: 0 0 10px; }
                .rt-linky-license-form p:last-child { margin-bottom: 0; }
                .rt-linky-upgrade-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 15px; border-radius: 8px; margin-top: 15px; }
                .rt-linky-upgrade-box h4 { margin: 0 0 10px; color: #fff; }
                .rt-linky-upgrade-box p { margin: 0 0 10px; font-size: 13px; }
                .rt-linky-upgrade-box ul { margin: 0 0 15px; padding-left: 0; list-style: none; font-size: 13px; }
                .rt-linky-upgrade-box li { padding: 3px 0; }
                .rt-linky-upgrade-btn { width: 100%; text-align: center; background: #fff !important; color: #764ba2 !important; border: none !important; font-weight: bold; }
                .rt-linky-upgrade-btn:hover { background: #f0f0f0 !important; }
                .rt-linky-dev-tools { margin-top: 20px; padding-top: 15px; border-top: 1px dashed #ccc; }
            </style>
            
            <script>
            jQuery(document).ready(function($) {
                $('#rt-linky-activate-pro').on('click', function() {
                    if (confirm('Pro-Modus für Testzwecke aktivieren?')) {
                        wp.ajax.post('rt_linky_activate_pro', {
                            nonce: '<?php echo wp_create_nonce("rt_linky_pro_nonce"); ?>'
                        }).done(function(response) {
                            location.reload();
                        }).fail(function() {
                            alert('Fehler beim Aktivieren');
                        });
                    }
                });
            });
            </script>
            <?php
        } else {
            // PRO Version - Lizenz anzeigen + Einstellungen
            ?>
            <div class="rt-linky-license-box rt-linky-pro">
                <div class="rt-linky-license-badge pro">
                    <span class="dashicons dashicons-star-filled"></span>
                    <strong>PRO Version</strong>
                    <span class="rt-linky-pro-tag">AKTIV</span>
                </div>
                
                <div class="rt-linky-license-info">
                    <h4>🔑 Lizenz-Information</h4>
                    <p>
                        <code><?php echo esc_html(substr($licenseKey, 0, 8) . '****'); ?></code>
                    </p>
                    <form method="post" action="" style="margin-top: 10px;">
                        <?php wp_nonce_field('rt_linky_license_action', 'rt_linky_license_nonce'); ?>
                        <input type="hidden" name="rt_linky_license_action" value="deactivate">
                        <button type="submit" class="button button-secondary">
                            Lizenz deaktivieren
                        </button>
                    </form>
                </div>
                
                <div class="rt-linky-pro-settings">
                    <h4>⚙️ Pro Einstellungen</h4>
                    <p>
                        <label>
                            <input type="checkbox" name="rt_linky_show_footer" value="1" <?php checked($showFooter !== '0'); ?>>
                            Footer anzeigen
                        </label>
                    </p>
                    
                    <p>
                        <label>
                            <input type="checkbox" name="rt_linky_verified_badge" value="1" <?php checked($showVerified); ?>>
                            Verifiziert-Badge anzeigen
                        </label>
                    </p>
                </div>
            </div>
            
            <style>
                .rt-linky-license-box { padding: 15px; background: #f0f6fc; border-radius: 4px; }
                .rt-linky-license-badge.pro { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #edfaef; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #00a32a; }
                .rt-linky-pro-tag { margin-left: auto; background: #00a32a; color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 11px; }
                .rt-linky-license-info { background: #fff; padding: 15px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #c3c4c7; }
                .rt-linky-license-info h4 { margin: 0 0 10px; }
                .rt-linky-license-info p { margin: 0; }
                .rt-linky-pro-settings { background: #fff; padding: 15px; border-radius: 4px; border: 1px solid #c3c4c7; }
                .rt-linky-pro-settings h4 { margin: 0 0 15px; }
                .rt-linky-pro-settings p { margin: 0 0 10px; }
                .rt-linky-pro-settings label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
            </style>
            <?php
        }
    }
    
    public function saveMetabox($postId, $post): void {
        if (!isset($_POST['rt_linky_license_nonce']) || !wp_verify_nonce($_POST['rt_linky_license_nonce'], 'rt_linky_license_nonce')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $postId)) {
            return;
        }
        
        if (LicenseConfig::isPro()) {
            $showFooter = isset($_POST['rt_linky_show_footer']) ? '1' : '0';
            update_post_meta($postId, '_rt_linky_show_footer', $showFooter);
            
            $verifiedBadge = isset($_POST['rt_linky_verified_badge']) ? '1' : '';
            update_post_meta($postId, '_rt_linky_verified_badge', $verifiedBadge);
        }
    }
}