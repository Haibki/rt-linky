<?php
namespace RTLinky;

class Admin {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'addMenuPages'], 9);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }
    
    public function addMenuPages() {
        if (!post_type_exists('rt_linky_profile')) {
            return;
        }
        
        if (!class_exists('RTLinky\License')) {
            return;
        }
        
        add_submenu_page(
            'edit.php?post_type=rt_linky_profile',
            'Lizenz & Upgrade',
            'Lizenz 🔑',
            'manage_options',
            'rt-linky-license',
            [$this, 'renderLicensePage']
        );
        
        if (License::getInstance()->isPro()) {
            add_submenu_page(
                'edit.php?post_type=rt_linky_profile',
                'Einstellungen',
                'Einstellungen',
                'manage_options',
                'rt-linky-settings',
                [$this, 'renderSettingsPage']
            );
        }
    }
    
    public function enqueueAssets($hook) {
        if (strpos($hook, 'rt-linky-license') === false && 
            strpos($hook, 'rt-linky-settings') === false) {
            return;
        }
        
        wp_enqueue_style(
            'rt-linky-admin-global',
            RT_LINKY_PLUGIN_URL . 'assets/admin-global.css',
            [],
            RT_LINKY_VERSION
        );
    }
    
    public function renderLicensePage() {
        if (!class_exists('RTLinky\License')) {
            echo '<div class="wrap"><h1>Fehler</h1><p>License-Klasse nicht gefunden.</p></div>';
            return;
        }
        
        $license = License::getInstance();
        $isPro = $license->isPro();
        $count = intval($license->getProfileCount());
        $remaining = $license->getRemainingProfiles();
        ?>
        <div class="wrap rt-linky-license-page">
            <h1>🔑 RT-Linky Lizenz & Upgrade</h1>
            
            <div class="license-status-card <?php echo $isPro ? 'pro' : 'free'; ?>">
                <div class="status-header">
                    <h2><?php echo $isPro ? '✅ Pro Version Aktiv' : '⭐ Free Version'; ?></h2>
                    <span class="status-badge"><?php echo $isPro ? 'PRO' : 'FREE'; ?></span>
                </div>
                
                <div class="usage-stats">
                    <?php if ($isPro): ?>
                        <p>Deine Profile: <strong><?php echo $count; ?></strong> (unbegrenzt)</p>
                    <?php else: ?>
                        <p>Deine Profile: <strong><?php echo $count; ?></strong> / 2</p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo min(100, ($count / 2) * 100); ?>%"></div>
                        </div>
                        <p class="remaining"><?php echo $remaining; ?> verbleibend</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!$isPro): ?>
            <div class="free-vs-pro-table">
                <h2>Free vs Pro - Funktionsübersicht</h2>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Feature</th>
                            <th class="column-free">Free</th>
                            <th class="column-pro">Pro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Links erstellen</strong></td>
                            <td class="feature-limited">Max. 2 Links</td>
                            <td class="feature-yes">✅ Unbegrenzt</td>
                        </tr>
                        <tr>
                            <td><strong>Bestehende Links</strong></td>
                            <td class="feature-yes">✅ Bleiben erhalten</td>
                            <td class="feature-yes">✅ Bleiben erhalten</td>
                        </tr>
                        <tr>
                            <td><strong>Profilbild</strong></td>
                            <td class="feature-yes">✅ Erlaubt</td>
                            <td class="feature-yes">✅ Erlaubt</td>
                        </tr>
                        <tr>
                            <td><strong>Icons auswählen</strong></td>
                            <td class="feature-no">❌ Nur 2 verfügbar</td>
                            <td class="feature-yes">✅ Alle 25+ Icons</td>
                        </tr>
                        <tr>
                            <td><strong>Hintergrundbild</strong></td>
                            <td class="feature-no">❌ Nur Farben</td>
                            <td class="feature-yes">✅ Bild-Upload</td>
                        </tr>
                        <tr>
                            <td><strong>Verifiziert-Badge</strong></td>
                            <td class="feature-no">❌ Nicht verfügbar</td>
                            <td class="feature-yes">✅ An/aus</td>
                        </tr>
                        <tr>
                            <td><strong>Link-Untertitel</strong></td>
                            <td class="feature-no">❌ Nicht verfügbar</td>
                            <td class="feature-yes">✅ Pro Link</td>
                        </tr>
                        <tr>
                            <td><strong>Footer "Erstellt mit"</strong></td>
                            <td class="feature-no">❌ Immer an</td>
                            <td class="feature-yes">✅ Abschaltbar</td>
                        </tr>
                        <tr>
                            <td><strong>Statistiken</strong></td>
                            <td class="feature-limited">Basis</td>
                            <td class="feature-yes">✅ Detailliert</td>
                        </tr>
                        <tr>
                            <td><strong>Support</strong></td>
                            <td class="feature-no">❌ Community</td>
                            <td class="feature-yes">✅ Premium</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="upgrade-cta">
                    <h3>Bereit für mehr?</h3>
                    <p>Schalte alle Pro-Features frei und erstelle unbegrenzt viele Links.</p>
                    <a href="https://rettoro.de/rt-linky" target="_blank" class="button button-primary button-hero">
                        🚀 Jetzt auf Pro upgraden
                    </a>
                    <p class="price-note">ab 29€ / Jahr • 30 Tage Geld-zurück-Garantie</p>
                </div>
            </div>
            <?php else: ?>
            <div class="pro-management">
                <h2>Pro-Lizenz verwalten</h2>
                <p>Deine Lizenz ist aktiv und gültig.</p>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function renderSettingsPage() {
        if (!class_exists('RTLinky\License') || !License::getInstance()->isPro()) {
            wp_die('Nur für Pro-Nutzer verfügbar.');
        }
        
        if (isset($_POST['rt_linky_save_settings'])) {
            check_admin_referer('rt_linky_settings');
            
            update_option('rt_linky_settings', [
                'show_footer' => isset($_POST['show_footer']),
                'footer_text' => sanitize_text_field($_POST['footer_text'] ?? 'Erstellt mit RT-Linky'),
                'enable_subtitles' => isset($_POST['enable_subtitles']),
                'enable_verified_badge' => isset($_POST['enable_verified_badge']),
            ]);
            
            echo '<div class="notice notice-success"><p>Einstellungen gespeichert.</p></div>';
        }
        
        $settings = get_option('rt_linky_settings', [
            'show_footer' => true,
            'footer_text' => 'Erstellt mit RT-Linky',
            'enable_subtitles' => true,
            'enable_verified_badge' => false,
        ]);
        ?>
        <div class="wrap">
            <h1>⚙️ Einstellungen</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('rt_linky_settings'); ?>
                <input type="hidden" name="rt_linky_save_settings" value="1">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Footer anzeigen</th>
                        <td>
                            <label>
                                <input type="checkbox" name="show_footer" <?php checked($settings['show_footer']); ?>>
                                "Erstellt mit RT-Linky" im Footer anzeigen
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Footer-Text</th>
                        <td>
                            <input type="text" name="footer_text" value="<?php echo esc_attr($settings['footer_text']); ?>" class="regular-text">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Link-Untertitel</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_subtitles" <?php checked($settings['enable_subtitles']); ?>>
                                Untertitel-Funktion aktivieren
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Verifiziert-Badge</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_verified_badge" <?php checked($settings['enable_verified_badge']); ?>>
                                Verifiziert-Haken im Profil anzeigen
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Einstellungen speichern'); ?>
            </form>
        </div>
        <?php
    }
}