<?php
namespace RTLinky;

class MetaBoxes {
    private $license;
    private $settings;
    
    public function __construct() {
        $this->license = License::getInstance();
        $this->settings = get_option('rt_linky_settings', []);
        
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post', [$this, 'saveMetaBoxes'], 10, 2);
    }
    
    public function addMetaBoxes() {
        add_meta_box(
            'rt_linky_links',
            'Links',
            [$this, 'renderLinksMetaBox'],
            'rt_linky_profile',
            'normal',
            'high'
        );
        
        add_meta_box(
            'rt_linky_appearance',
            'Erscheinungsbild',
            [$this, 'renderAppearanceMetaBox'],
            'rt_linky_profile',
            'side',
            'default'
        );
        
        // Untertitel nur wenn Pro und aktiviert
        if ($this->license->isPro() && !empty($this->settings['enable_subtitles'])) {
            add_meta_box(
                'rt_linky_subtitle',
                'Link-Untertitel',
                [$this, 'renderSubtitleMetaBox'],
                'rt_linky_profile',
                'normal',
                'default'
            );
        }
    }
    
    public function renderLinksMetaBox($post) {
        wp_nonce_field('rt_linky_save_meta', 'rt_linky_meta_nonce');
        
        $links = get_post_meta($post->ID, '_rt_linky_links', true) ?: [];
        $isPro = $this->license->isPro();
        
        // Icon-Liste (25+ Icons)
        $icons = $this->getAvailableIcons();
        ?>
        
        <div id="rt-linky-links-container" data-is-pro="<?php echo $isPro ? '1' : '0'; ?>">
            <div class="links-list">
                <?php foreach ($links as $index => $link): ?>
                    <div class="link-item" data-index="<?php echo $index; ?>">
                        <div class="link-row">
                            <input type="text" 
                                   name="rt_linky_links[<?php echo $index; ?>][title]" 
                                   value="<?php echo esc_attr($link['title'] ?? ''); ?>" 
                                   placeholder="Link-Titel"
                                   class="regular-text">
                            
                            <input type="url" 
                                   name="rt_linky_links[<?php echo $index; ?>][url]" 
                                   value="<?php echo esc_attr($link['url'] ?? ''); ?>" 
                                   placeholder="https://..."
                                   class="regular-text">
                            
                            <button type="button" class="button remove-link">🗑️</button>
                        </div>
                        
                        <?php if ($isPro && !empty($this->settings['enable_subtitles'])): ?>
                            <div class="link-subtitle-row">
                                <input type="text" 
                                       name="rt_linky_links[<?php echo $index; ?>][subtitle]" 
                                       value="<?php echo esc_attr($link['subtitle'] ?? ''); ?>" 
                                       placeholder="Untertitel (optional)"
                                       class="regular-text">
                            </div>
                        <?php endif; ?>
                        
                        <div class="link-icon-selector">
                            <p>Icon wählen:</p>
                            <div class="rt-linky-icon-grid">
                                <?php foreach ($icons as $i => $icon): 
                                    $locked = !$isPro && $i >= 2;
                                    $selected = ($link['icon'] ?? '') === $icon['id'];
                                ?>
                                    <div class="icon-item <?php echo $locked ? 'locked' : ''; ?> <?php echo $selected ? 'selected' : ''; ?>" 
                                         data-icon="<?php echo esc_attr($icon['id']); ?>"
                                         title="<?php echo $locked ? '🔒 Nur in Pro verfügbar' : esc_attr($icon['name']); ?>">
                                        <?php echo $icon['svg']; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="rt_linky_links[<?php echo $index; ?>][icon]" 
                                   value="<?php echo esc_attr($link['icon'] ?? ''); ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($this->license->canCreateLink() || count($links) < 2): ?>
                <button type="button" class="button button-secondary add-link">
                    ➕ Link hinzufügen
                </button>
            <?php else: ?>
                <div class="link-limit-reached">
                    <p>⚠️ Du hast das Limit von 2 Links erreicht.</p>
                    <a href="<?php echo admin_url('admin.php?page=rt-linky-license'); ?>" class="button">
                        Auf Pro upgraden
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <script>
            window.rtLinkyEditor = {
                isPro: <?php echo $isPro ? 'true' : 'false'; ?>,
                subtitlesEnabled: <?php echo !empty($this->settings['enable_subtitles']) ? 'true' : 'false'; ?>
            };
        </script>
        <?php
    }
    
    public function renderAppearanceMetaBox($post) {
        $appearance = get_post_meta($post->ID, '_rt_linky_appearance', true) ?: [];
        $isPro = $this->license->isPro();
        ?>
        
        <p>
            <label>Hintergrund-Typ:</label><br>
            <select name="rt_linky_appearance[bg_type]" id="rt-linky-bg-type">
                <option value="color" <?php selected($appearance['bg_type'] ?? 'color', 'color'); ?>>Farbe</option>
                <option value="gradient" <?php selected($appearance['bg_type'] ?? '', 'gradient'); ?>>Gradient</option>
                <?php if ($isPro): ?>
                    <option value="image" <?php selected($appearance['bg_type'] ?? '', 'image'); ?>>Bild (Pro)</option>
                <?php endif; ?>
            </select>
        </p>
        
        <div id="bg-color-picker" style="<?php echo ($appearance['bg_type'] ?? 'color') !== 'color' ? 'display:none;' : ''; ?>">
            <label>Hintergrund-Farbe:</label><br>
            <input type="color" name="rt_linky_appearance[bg_color]" 
                   value="<?php echo esc_attr($appearance['bg_color'] ?? '#ffffff'); ?>">
        </div>
        
        <div id="bg-gradient-picker" style="<?php echo ($appearance['bg_type'] ?? '') !== 'gradient' ? 'display:none;' : ''; ?>">
            <label>Gradient:</label><br>
            <input type="text" name="rt_linky_appearance[bg_gradient]" 
                   value="<?php echo esc_attr($appearance['bg_gradient'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'); ?>"
                   class="widefat">
        </div>
        
        <?php if ($isPro): ?>
            <div id="bg-image-upload" style="<?php echo ($appearance['bg_type'] ?? '') !== 'image' ? 'display:none;' : ''; ?>">
                <label>Hintergrund-Bild:</label><br>
                <input type="hidden" name="rt_linky_appearance[bg_image]" id="rt-linky-bg-image-id"
                       value="<?php echo esc_attr($appearance['bg_image'] ?? ''); ?>">
                <button type="button" class="button" id="rt-linky-upload-bg">
                    Bild auswählen
                </button>
                <div id="rt-linky-bg-preview" style="margin-top: 10px;">
                    <?php if (!empty($appearance['bg_image'])): ?>
                        <?php echo wp_get_attachment_image($appearance['bg_image'], 'medium'); ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="rt-linky-bg-upload locked">
                <label>Hintergrund-Bild:</label><br>
                <button type="button" class="button" disabled>Bild auswählen</button>
                <p class="description">🔒 Nur in Pro verfügbar</p>
            </div>
        <?php endif; ?>
        
        <hr>
        
        <p>
            <label>
                <input type="checkbox" name="rt_linky_appearance[verified_badge]" 
                       <?php checked(!empty($appearance['verified_badge'])); ?>
                       <?php echo !$isPro ? 'disabled' : ''; ?>>
                Verifiziert-Badge anzeigen
                <?php if (!$isPro): ?>
                    <span class="pro-badge">🔒 Pro</span>
                <?php endif; ?>
            </label>
        </p>
        
        <?php
    }
    
    public function renderSubtitleMetaBox($post) {
        $subtitle = get_post_meta($post->ID, '_rt_linky_subtitle', true) ?: '';
        ?>
        <p>
            <label for="rt-linky-profile-subtitle">Profil-Untertitel:</label><br>
            <input type="text" id="rt-linky-profile-subtitle" name="rt_linky_subtitle" 
                   value="<?php echo esc_attr($subtitle); ?>" class="widefat">
            <span class="description">Wird unter dem Profilnamen angezeigt</span>
        </p>
        <?php
    }
    
    public function saveMetaBoxes($post_id, $post) {
        if (!isset($_POST['rt_linky_meta_nonce']) || 
            !wp_verify_nonce($_POST['rt_linky_meta_nonce'], 'rt_linky_save_meta')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if ($post->post_type !== 'rt_linky_profile') {
            return;
        }
        
        // Links speichern
        if (isset($_POST['rt_linky_links'])) {
            $links = [];
            foreach ($_POST['rt_linky_links'] as $link) {
                $links[] = [
                    'title' => sanitize_text_field($link['title'] ?? ''),
                    'url' => esc_url_raw($link['url'] ?? ''),
                    'icon' => sanitize_text_field($link['icon'] ?? ''),
                    'subtitle' => $this->license->isPro() ? sanitize_text_field($link['subtitle'] ?? '') : ''
                ];
            }
            update_post_meta($post_id, '_rt_linky_links', $links);
        }
        
        // Erscheinungsbild speichern
        if (isset($_POST['rt_linky_appearance'])) {
            $appearance = [
                'bg_type' => sanitize_text_field($_POST['rt_linky_appearance']['bg_type'] ?? 'color'),
                'bg_color' => sanitize_hex_color($_POST['rt_linky_appearance']['bg_color'] ?? '#ffffff'),
                'bg_gradient' => sanitize_text_field($_POST['rt_linky_appearance']['bg_gradient'] ?? ''),
            ];
            
            // Nur Pro: Bild und Badge
            if ($this->license->isPro()) {
                $appearance['bg_image'] = intval($_POST['rt_linky_appearance']['bg_image'] ?? 0);
                $appearance['verified_badge'] = !empty($_POST['rt_linky_appearance']['verified_badge']);
            }
            
            update_post_meta($post_id, '_rt_linky_appearance', $appearance);
        }
        
        // Untertitel speichern (nur Pro)
        if ($this->license->isPro() && isset($_POST['rt_linky_subtitle'])) {
            update_post_meta($post_id, '_rt_linky_subtitle', sanitize_text_field($_POST['rt_linky_subtitle']));
        }
    }
    
    private function getAvailableIcons() {
        // 25+ Icons zurückgeben
        return [
            ['id' => 'link', 'name' => 'Link', 'svg' => '🔗'],
            ['id' => 'home', 'name' => 'Home', 'svg' => '🏠'],
            ['id' => 'user', 'name' => 'Person', 'svg' => '👤'],
            ['id' => 'mail', 'name' => 'E-Mail', 'svg' => '✉️'],
            ['id' => 'phone', 'name' => 'Telefon', 'svg' => '📞'],
            ['id' => 'video', 'name' => 'Video', 'svg' => '🎥'],
            ['id' => 'music', 'name' => 'Musik', 'svg' => '🎵'],
            ['id' => 'photo', 'name' => 'Foto', 'svg' => '📷'],
            ['id' => 'shop', 'name' => 'Shop', 'svg' => '🛒'],
            ['id' => 'heart', 'name' => 'Herz', 'svg' => '❤️'],
            ['id' => 'star', 'name' => 'Stern', 'svg' => '⭐'],
            ['id' => 'fire', 'name' => 'Feuer', 'svg' => '🔥'],
            ['id' => 'rocket', 'name' => 'Rakete', 'svg' => '🚀'],
            ['id' => 'gift', 'name' => 'Geschenk', 'svg' => '🎁'],
            ['id' => 'calendar', 'name' => 'Kalender', 'svg' => '📅'],
            ['id' => 'map', 'name' => 'Karte', 'svg' => '🗺️'],
            ['id' => 'bookmark', 'name' => 'Lesezeichen', 'svg' => '🔖'],
            ['id' => 'bell', 'name' => 'Glocke', 'svg' => '🔔'],
            ['id' => 'flag', 'name' => 'Flagge', 'svg' => '🚩'],
            ['id' => 'tag', 'name' => 'Tag', 'svg' => '🏷️'],
            ['id' => 'briefcase', 'name' => 'Aktentasche', 'svg' => '💼'],
            ['id' => 'chart', 'name' => 'Diagramm', 'svg' => '📊'],
            ['id' => 'code', 'name' => 'Code', 'svg' => '💻'],
            ['id' => 'coffee', 'name' => 'Kaffee', 'svg' => '☕'],
            ['id' => 'globe', 'name' => 'Globus', 'svg' => '🌍'],
        ];
    }
}