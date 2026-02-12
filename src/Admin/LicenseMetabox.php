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
    
    public function renderMetabox($post): void {
        wp_nonce_field('rt_linky_license_nonce', 'rt_linky_license_nonce');
        
        $isPro = LicenseConfig::isPro();
        $showFooter = get_post_meta($post->ID, '_rt_linky_show_footer', true);
        $showVerified = get_post_meta($post->ID, '_rt_linky_verified_badge', true);
        
        if (!$isPro) {
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
                
                <div class="rt-linky-upgrade-box">
                    <h4>🚀 Upgrade auf PRO</h4>
                    <ul>
                        <li>✓ Unbegrenzte Links</li>
                        <li>✓ 25+ Icons</li>
                        <li>✓ Hintergrundbild</li>
                        <li>✓ Footer ausblendbar</li>
                        <li>✓ Link-Untertitel</li>
                        <li>✓ Verifiziert-Badge</li>
                    </ul>
                    <a href="#" class="button button-primary rt-linky-upgrade-btn">
                        Jetzt upgraden
                    </a>
                </div>
                
                <div class="rt-linky-dev-tools" style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #ccc;">
                    <small>Entwickler-Optionen:</small><br>
                    <button type="button" class="button" id="rt-linky-activate-pro" style="margin-top: 5px;">
                        Pro aktivieren (Test)
                    </button>
                </div>
            </div>
            
            <style>
                .rt-linky-license-box { padding: 15px; background: #f0f0f1; border-radius: 4px; }
                .rt-linky-license-badge { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #fff; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #ffb900; }
                .rt-linky-features-list { margin: 0 0 20px 0; padding-left: 0; list-style: none; }
                .rt-linky-features-list li { padding: 6px 0; border-bottom: 1px solid #e0e0e0; }
                .rt-linky-features-list li:last-child { border-bottom: none; }
                .rt-linky-features-list .available { color: #00a32a; }
                .rt-linky-features-list .locked { color: #999; text-decoration: line-through; }
                .rt-linky-upgrade-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 15px; border-radius: 8px; margin-top: 15px; }
                .rt-linky-upgrade-box h4 { margin: 0 0 10px 0; color: #fff; }
                .rt-linky-upgrade-box ul { margin: 0 0 15px 0; padding-left: 0; list-style: none; font-size: 13px; }
                .rt-linky-upgrade-box li { padding: 3px 0; }
                .rt-linky-upgrade-btn { width: 100%; text-align: center; background: #fff !important; color: #764ba2 !important; border: none !important; font-weight: bold; }
                .rt-linky-upgrade-btn:hover { background: #f0f0f0 !important; }
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
            ?>
            <div class="rt-linky-license-box rt-linky-pro">
                <div class="rt-linky-license-badge pro">
                    <span class="dashicons dashicons-star-filled"></span>
                    <strong>PRO Version</strong>
                    <span class="rt-linky-pro-tag">AKTIV</span>
                </div>
                
                <div class="rt-linky-pro-settings">
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
                
                <div class="rt-linky-dev-tools" style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #ccc;">
                    <small>Entwickler-Optionen:</small><br>
                    <button type="button" class="button" id="rt-linky-deactivate-pro" style="margin-top: 5px;">
                        Pro deaktivieren (Test)
                    </button>
                </div>
            </div>
            
            <style>
                .rt-linky-license-box { padding: 15px; background: #f0f6fc; border-radius: 4px; }
                .rt-linky-license-badge.pro { border-left-color: #00a32a; background: #edfaef; }
                .rt-linky-pro-tag { margin-left: auto; background: #00a32a; color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 11px; }
                .rt-linky-pro-settings { margin-top: 15px; }
                .rt-linky-pro-settings label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
            </style>
            
            <script>
            jQuery(document).ready(function($) {
                $('#rt-linky-deactivate-pro').on('click', function() {
                    if (confirm('Pro-Modus deaktivieren?')) {
                        wp.ajax.post('rt_linky_deactivate_pro', {
                            nonce: '<?php echo wp_create_nonce("rt_linky_pro_nonce"); ?>'
                        }).done(function(response) {
                            location.reload();
                        }).fail(function() {
                            alert('Fehler beim Deaktivieren');
                        });
                    }
                });
            });
            </script>
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