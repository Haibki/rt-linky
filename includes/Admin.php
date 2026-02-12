<?php
namespace RTLinky;

class Admin {
    private $license;
    
    public function __construct() {
        $this->license = License::getInstance();
        
        add_action('admin_menu', [$this, 'addMenuPages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('wp_ajax_rt_linky_save_license', [$this, 'ajaxSaveLicense']);
        add_action('wp_ajax_rt_linky_remove_license', [$this, 'ajaxRemoveLicense']);
        add_action('wp_ajax_rt_linky_save_settings', [$this, 'ajaxSaveSettings']);
    }
    
    public function addMenuPages() {
        // Hauptmenü
        add_menu_page(
            'RT-Linky',
            'RT-Linky',
            'manage_options',
            'rt-linky',
            [$this, 'renderMainPage'],
            'dashicons-admin-links',
            30
        );
        
        // Untermenü: Profile (CPT)
        add_submenu_page(
            'rt-linky',
            'Profile',
            'Profile',
            'manage_options',
            'edit.php?post_type=rt_linky_profile'
        );
        
        // Untermenü: Neu erstellen
        add_submenu_page(
            'rt-linky',
            'Neues Profil',
            'Neu erstellen',
            'manage_options',
            'post-new.php?post_type=rt_linky_profile'
        );
        
        // Untermenü: Lizenz (immer sichtbar)
        add_submenu_page(
            'rt-linky',
            'Lizenz',
            'Lizenz 🔑',
            'manage_options',
            'rt-linky-license',
            [$this, 'renderLicensePage']
        );
        
        // Untermenü: Einstellungen (nur Pro)
        if ($this->license->isPro()) {
            add_submenu_page(
                'rt-linky',
                'Einstellungen',
                'Einstellungen',
                'manage_options',
                'rt-linky-settings',
                [$this, 'renderSettingsPage']
            );
        }
    }
    
    public function enqueueAssets($hook) {
        if (strpos($hook, 'rt-linky') === false) {
            return;
        }
        
        wp_enqueue_style(
            'rt-linky-admin',
            RT_LINKY_PLUGIN_URL . 'assets/css/admin.css',
            [],
            RT_LINKY_VERSION
        );
        
        wp_enqueue_script(
            'rt-linky-admin',
            RT_LINKY_PLUGIN_URL . 'assets/js/admin-global.js',
            ['jquery'],
            RT_LINKY_VERSION,
            true
        );
        
        wp_localize_script('rt-linky-admin', 'rtLinkyAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rt_linky_admin_nonce'),
            'isPro' => $this->license->isPro(),
            'strings' => [
                'saveSuccess' => 'Einstellungen gespeichert.',
                'saveError' => 'Fehler beim Speichern.',
                'confirmRemove' => 'Lizenz wirklich entfernen? Alle Pro-Features werden deaktiviert.'
            ]
        ]);
    }
    
    /**
     * Hauptseite (Dashboard)
     */
    public function renderMainPage() {
        $linkCount = $this->license->getLinkCount();
        $canCreate = $this->license->canCreateLink();
        $remaining = $this->license->getRemainingLinks();
        $isPro = $this->license->isPro();
        $isExpired = $this->license->isExpired();
        ?>
        <div class="wrap rt-linky-dashboard">
            <h1>RT-Linky Dashboard</h1>
            
            <div class="rt-linky-stats-grid">
                <div class="stat-card">
                    <h3>Deine Links</h3>
                    <div class="stat-number"><?php echo esc_html($linkCount); ?></div>
                    <?php if (!$isPro): ?>
                        <p class="stat-limit <?php echo $remaining === 0 ? 'limit-reached' : ''; ?>">
                            <?php if ($isExpired): ?>
                                ⏰ Lizenz abgelaufen - Keine neuen Links möglich
                            <?php else: ?>
                                <?php echo esc_html($remaining); ?> von 2 Links verfügbar
                            <?php endif; ?>
                        </p>
                    <?php else: ?>
                        <p class="stat-pro">✨ Unbegrenzte Links (Pro)</p>
                    <?php endif; ?>
                </div>
                
                <div class="stat-card">
                    <h3>Status</h3>
                    <div class="status-badge <?php echo $isPro ? 'pro' : 'free'; ?>">
                        <?php echo $isPro ? '✅ Pro Aktiv' : '⭐ Free Version'; ?>
                    </div>
                    <?php if ($isExpired): ?>
                        <p class="status-expired">Lizenz abgelaufen</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="rt-linky-actions">
                <?php if ($canCreate): ?>
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=rt_linky_profile')); ?>" class="button button-primary button-hero">
                        ➕ Neues Profil erstellen
                    </a>
                <?php else: ?>
                    <div class="upgrade-notice">
                        <p>🚫 Du hast das Limit erreicht.</p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=rt-linky-license')); ?>" class="button button-primary">
                            Auf Pro upgraden
                        </a>
                    </div>
                <?php endif; ?>
                
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=rt_linky_profile')); ?>" class="button button-secondary">
                    📋 Alle Profile anzeigen
                </a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Lizenz-Seite mit Free vs Pro Vergleich
     */
    public function renderLicensePage() {
        $isPro = $this->license->isPro();
        $isExpired = $this->license->isExpired();
        $licenseKey = $this->license->getLicenseKey();
        $expiryDate = $this->license->getExpiryDate();
        ?>
        <div class="wrap rt-linky-license-page">
            <h1>🔑 Lizenz & Upgrade</h1>
            
            <?php if ($isExpired): ?>
                <div class="notice notice-warning">
                    <p><strong>⚠️ Lizenz abgelaufen</strong> - Deine bestehenden Links bleiben erhalten, aber du kannst keine neuen erstellen oder Pro-Features nutzen.</p>
                </div>
            <?php endif; ?>
            
            <!-- Lizenz-Status -->
            <div class="license-status-box <?php echo $isPro ? 'active' : 'inactive'; ?>">
                <h2>Aktueller Status: <?php echo $isPro ? 'Pro ✅' : 'Free ⭐'; ?></h2>
                
                <?php if ($isPro): ?>
                    <p>Lizenz-Key: <code><?php echo esc_html($licenseKey); ?></code></p>
                    <p>Gültig bis: <?php echo esc_html($expiryDate); ?></p>
                    <button type="button" class="button" id="rt-linky-remove-license">
                        Lizenz entfernen
                    </button>
                <?php else: ?>
                    <div class="license-activate-form">
                        <input type="text" id="rt-linky-license-key" placeholder="Pro-Lizenz-Key eingeben..." class="regular-text">
                        <button type="button" class="button button-primary" id="rt-linky-activate-license">
                            Aktivieren
                        </button>
                        <p class="description">
                            Du hast noch keinen Key? <a href="https://rettoro.de/rt-linky" target="_blank">Hier upgraden</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Free vs Pro Vergleich -->
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
                            <td class="feature-no">❌ Gesperrt (nur 2 sichtbar)</td>
                            <td class="feature-yes">✅ Alle 25+ Icons</td>
                        </tr>
                        <tr>
                            <td><strong>Hintergrundbild</strong></td>
                            <td class="feature-no">❌ Nur Farben/Gradient</td>
                            <td class="feature-yes">✅ Bild-Upload</td>
                        </tr>
                        <tr>
                            <td><strong>Verifiziert-Badge</strong></td>
                            <td class="feature-no">❌ Nicht verfügbar</td>
                            <td class="feature-yes">✅ An/aus schaltbar</td>
                        </tr>
                        <tr>
                            <td><strong>Link-Untertitel</strong></td>
                            <td class="feature-no">❌ Nicht verfügbar</td>
                            <td class="feature-yes">✅ Pro Link individuell</td>
                        </tr>
                        <tr>
                            <td><strong>Einstellungen-Seite</strong></td>
                            <td class="feature-no">❌ Nicht vorhanden</td>
                            <td class="feature-yes">✅ Vorhanden</td>
                        </tr>
                        <tr>
                            <td><strong>Footer "Erstellt mit RT-Linky"</strong></td>
                            <td class="feature-no">❌ Immer an (nicht abschaltbar)</td>
                            <td class="feature-yes">✅ An/aus schaltbar</td>
                        </tr>
                        <tr>
                            <td><strong>Statistiken</strong></td>
                            <td class="feature-limited">Basis-Zahlen</td>
                            <td class="feature-yes">✅ Detaillierte Analytics</td>
                        </tr>
                        <tr>
                            <td><strong>Premium Support</strong></td>
                            <td class="feature-no">❌ Nicht verfügbar</td>
                            <td class="feature-yes">✅ Inklusive</td>
                        </tr>
                    </tbody>
                </table>
                
                <?php if (!$isPro): ?>
                    <div class="upgrade-cta">
                        <h3>Bereit für mehr?</h3>
                        <p>Schalte alle Pro-Features frei und erstelle unbegrenzt viele Links.</p>
                        <a href="https://rettoro.de/rt-linky" target="_blank" class="button button-primary button-hero">
                            🚀 Jetzt auf Pro upgraden
                        </a>
                        <p class="price-hint">ab 29€ / Jahr</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Einstellungen-Seite (nur Pro)
     */
    public function renderSettingsPage() {
        if (!$this->license->isPro()) {
            wp_die('Nur für Pro-Nutzer verfügbar.');
        }
        
        $settings = get_option('rt_linky_settings', [
            'show_footer' => true,
            'footer_text' => 'Erstellt mit RT-Linky',
            'enable_subtitles' => true,
            'default_subtitle' => '',
            'enable_verified_badge' => false,
            'analytics_enabled' => true
        ]);
        ?>
        <div class="wrap rt-linky-settings-page">
            <h1>⚙️ Einstellungen</h1>
            
            <form id="rt-linky-settings-form">
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
                            <p class="description">Standard: "Erstellt mit RT-Linky"</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Link-Untertitel</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_subtitles" <?php checked($settings['enable_subtitles']); ?>>
                                Untertitel-Funktion aktivieren
                            </label>
                            <p class="description">Ermöglicht zusätzlichen Text unter jedem Link</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Standard-Untertitel</th>
                        <td>
                            <input type="text" name="default_subtitle" value="<?php echo esc_attr($settings['default_subtitle']); ?>" class="regular-text">
                            <p class="description">Wird verwendet wenn kein individueller Text gesetzt ist</p>
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
                    
                    <tr>
                        <th scope="row">Detaillierte Analytics</th>
                        <td>
                            <label>
                                <input type="checkbox" name="analytics_enabled" <?php checked($settings['analytics_enabled']); ?>>
                                Erweiterte Statistiken aktivieren
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">Änderungen speichern</button>
                    <span class="spinner" style="float: none; margin-top: 0;"></span>
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * AJAX: Lizenz speichern
     */
    public function ajaxSaveLicense() {
        check_ajax_referer('rt_linky_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }
        
        $key = sanitize_text_field($_POST['license_key'] ?? '');
        
        if (empty($key)) {
            wp_send_json_error('Bitte Lizenz-Key eingeben');
        }
        
        // Hier sollte eigentlich eine API-Validierung stattfinden
        // Für Demo-Zwecke: Key akzeptieren wenn er mit "RT-" beginnt
        if (strpos($key, 'RT-') !== 0) {
            wp_send_json_error('Ungültiger Lizenz-Key');
        }
        
        $this->license->saveLicense($key);
        wp_send_json_success('Lizenz aktiviert');
    }
    
    /**
     * AJAX: Lizenz entfernen
     */
    public function ajaxRemoveLicense() {
        check_ajax_referer('rt_linky_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }
        
        $this->license->removeLicense();
        wp_send_json_success('Lizenz entfernt');
    }
    
    /**
     * AJAX: Einstellungen speichern
     */
    public function ajaxSaveSettings() {
        check_ajax_referer('rt_linky_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }
        
        if (!$this->license->isPro()) {
            wp_send_json_error('Nur für Pro-Nutzer');
        }
        
        $settings = [
            'show_footer' => isset($_POST['show_footer']),
            'footer_text' => sanitize_text_field($_POST['footer_text'] ?? 'Erstellt mit RT-Linky'),
            'enable_subtitles' => isset($_POST['enable_subtitles']),
            'default_subtitle' => sanitize_text_field($_POST['default_subtitle'] ?? ''),
            'enable_verified_badge' => isset($_POST['enable_verified_badge']),
            'analytics_enabled' => isset($_POST['analytics_enabled'])
        ];
        
        update_option('rt_linky_settings', $settings);
        wp_send_json_success('Einstellungen gespeichert');
    }
}