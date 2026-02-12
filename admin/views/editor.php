<?php
/**
 * Editor View
 * 
 * @package RTLinky
 */

if (!defined('ABSPATH')) {
    exit;
}

// BACKUP NONCE direkt im Template
$backup_nonce = wp_create_nonce('rt_linky_nonce');

// WICHTIG: Prüfe ob wir bearbeiten oder neu erstellen
$is_edit = !empty($profile) && is_object($profile);
$profile_id = $is_edit ? $profile->ID : 0;

// Werte laden oder Defaults setzen
if ($is_edit) {
    $title = $profile->post_title;
    $slug = get_post_meta($profile->ID, '_rt_linky_slug', true);
    $bio = get_post_meta($profile->ID, '_rt_linky_bio', true);
    $avatar_url = get_post_meta($profile->ID, '_rt_linky_avatar', true);
    $verified = get_post_meta($profile->ID, '_rt_linky_verified', true);
    $design = get_post_meta($profile->ID, '_rt_linky_design', true);
    $links = get_post_meta($profile->ID, '_rt_linky_links', true);
} else {
    // NEUES PROFIL - alles leer
    $title = '';
    $slug = '';
    $bio = '';
    $avatar_url = '';
    $verified = false;
    $design = array();
    $links = array();
}

// Design Defaults
$design = wp_parse_args($design, array(
    'bg_type' => 'gradient',
    'color1' => '#667eea',
    'color2' => '#764ba2',
    'bg_image' => '',
    'text_color' => '#ffffff',
    'button_color' => '#ffffff',
    'button_radius' => 12,
));

if (empty($links)) {
    $links = array();
}

$icons = array(
    'link' => '🔗', 'website' => '🌐', 'instagram' => '📸', 'facebook' => '📘',
    'twitter' => '🐦', 'youtube' => '📺', 'tiktok' => '🎵', 'linkedin' => '💼',
    'github' => '💻', 'email' => '✉️', 'phone' => '📱', 'whatsapp' => '💬',
    'telegram' => '✈️', 'snapchat' => '👻', 'pinterest' => '📌', 'spotify' => '🎧',
    'apple' => '🍎', 'android' => '🤖', 'shop' => '🛒', 'download' => '⬇️',
    'video' => '🎬', 'music' => '🎵', 'heart' => '❤️', 'star' => '⭐',
    'fire' => '🔥', 'rocket' => '🚀', 'calendar' => '📅', 'location' => '📍',
    'document' => '📄', 'gift' => '🎁'
);
?>
<div class="wrap rt-linky-wrap rt-linky-editor-page">
    <!-- BACKUP NONCE als data-attribute -->
    <div id="rt-linky-backup-nonce" data-nonce="<?php echo esc_attr($backup_nonce); ?>" style="display:none;"></div>
    
    <div class="rt-linky-header">
        <div class="rt-linky-brand">
            <a href="<?php echo admin_url('admin.php?page=rt-linky'); ?>" class="back-link">← Zurück</a>
            <h1><?php echo $is_edit ? 'Profil bearbeiten' : 'Neues Profil erstellen'; ?></h1>
        </div>
        <div class="header-actions">
            <button type="button" id="save-profile" class="button button-primary button-lg">
                Profil speichern
            </button>
        </div>
    </div>

    <div class="rt-linky-editor">
        <div class="editor-sidebar">
            <div class="editor-tabs">
                <button type="button" class="tab-btn active" data-tab="content">Inhalt</button>
                <button type="button" class="tab-btn" data-tab="design">Design</button>
                <button type="button" class="tab-btn" data-tab="links">Links (<?php echo count($links); ?>)</button>
            </div>

            <div class="tab-content">
                <!-- Content Tab -->
                <div class="tab-panel active" id="tab-content">
                    <div class="form-group">
                        <label>Profil Titel *</label>
                        <input type="text" id="profile-title" value="<?php echo esc_attr($title); ?>" placeholder="z.B. Max Mustermann">
                    </div>

                    <div class="form-group">
                        <label>URL Slug</label>
                        <input type="text" id="profile-slug" value="<?php echo esc_attr($slug); ?>" placeholder="max-mustermann">
                        <p class="help-text">Dein Profil ist erreichbar unter: <code><?php echo site_url('/link/'); ?><span id="slug-preview"><?php echo $slug ?: 'dein-slug'; ?></span>/</code></p>
                    </div>

                    <div class="form-group">
                        <label>Bio / Beschreibung</label>
                        <textarea id="profile-bio" rows="3" placeholder="Erzähle etwas über dich..."><?php echo esc_textarea($bio); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Profilbild</label>
                        <div class="avatar-upload">
                            <div class="avatar-preview" id="avatar-preview">
                                <?php if ($avatar_url): ?>
                                    <img src="<?php echo esc_url($avatar_url); ?>" alt="">
                                <?php else: ?>
                                    <span class="avatar-placeholder">👤</span>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" id="avatar-url" value="<?php echo esc_url($avatar_url); ?>">
                            <button type="button" class="button" id="upload-avatar">Bild hochladen</button>
                            <button type="button" class="button" id="remove-avatar" style="<?php echo $avatar_url ? '' : 'display:none;'; ?>">Entfernen</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="profile-verified" <?php checked($verified); ?>>
                            <span>Verifiziert-Badge anzeigen ✓</span>
                        </label>
                    </div>
                </div>

                <!-- Design Tab -->
                <div class="tab-panel" id="tab-design">
                    <div class="form-group">
                        <label>Hintergrund Typ</label>
                        <select id="design-bg-type">
                            <option value="gradient" <?php selected($design['bg_type'], 'gradient'); ?>>Farbverlauf</option>
                            <option value="solid" <?php selected($design['bg_type'], 'solid'); ?>>Einfarbig</option>
                            <option value="image" <?php selected($design['bg_type'], 'image'); ?>>Bild</option>
                        </select>
                    </div>

                    <div class="color-row" id="gradient-colors" style="<?php echo $design['bg_type'] === 'gradient' ? '' : 'display:none;'; ?>">
                        <div class="form-group color-picker">
                            <label>Farbe 1</label>
                            <input type="color" id="design-color1" value="<?php echo esc_attr($design['color1']); ?>">
                        </div>
                        <div class="form-group color-picker">
                            <label>Farbe 2</label>
                            <input type="color" id="design-color2" value="<?php echo esc_attr($design['color2']); ?>">
                        </div>
                    </div>

                    <div class="form-group color-picker" id="solid-color" style="<?php echo $design['bg_type'] === 'solid' ? '' : 'display:none;'; ?>">
                        <label>Hintergrundfarbe</label>
                        <input type="color" id="design-solid" value="<?php echo esc_attr($design['color1']); ?>">
                    </div>

                    <div id="image-upload-group" style="<?php echo $design['bg_type'] === 'image' ? '' : 'display:none;'; ?>">
                        <div class="form-group">
                            <label>Hintergrundbild</label>
                            <div class="bg-image-upload">
                                <div class="bg-image-preview" id="bg-image-preview">
                                    <?php if ($design['bg_image']): ?>
                                        <img src="<?php echo esc_url($design['bg_image']); ?>" alt="">
                                    <?php else: ?>
                                        <span class="bg-image-placeholder">🖼️</span>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" id="bg-image-url" value="<?php echo esc_url($design['bg_image']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="button" class="button button-primary" id="upload-bg-image">
                                <span class="dashicons dashicons-upload" style="margin-top: 3px;"></span>
                                Hintergrundbild hochladen
                            </button>
                            <button type="button" class="button" id="remove-bg-image" style="<?php echo $design['bg_image'] ? '' : 'display:none;'; ?>">
                                Bild entfernen
                            </button>
                        </div>
                        <p class="help-text">Empfohlene Größe: 1920x1080 Pixel oder größer</p>
                    </div>

                    <div class="form-group color-picker">
                        <label>Text Farbe</label>
                        <input type="color" id="design-text" value="<?php echo esc_attr($design['text_color']); ?>">
                    </div>

                    <div class="form-group color-picker">
                        <label>Button Farbe</label>
                        <input type="color" id="design-button" value="<?php echo esc_attr($design['button_color']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Button Radius: <span id="radius-value"><?php echo $design['button_radius']; ?></span>px</label>
                        <input type="range" id="design-radius" min="0" max="50" value="<?php echo esc_attr($design['button_radius']); ?>">
                    </div>
                </div>

                <!-- Links Tab -->
                <div class="tab-panel" id="tab-links">
                    <div class="links-header">
                        <h3>Deine Links</h3>
                        <button type="button" class="button" id="add-link">+ Link hinzufügen</button>
                    </div>
                    
                    <div class="links-list" id="links-list">
                        <?php foreach ($links as $index => $link): 
                            $link_icon = $link['icon'] ?? '🔗';
                            $icon_key = array_search($link_icon, $icons) ?: 'link';
                        ?>
                            <div class="link-item" data-id="<?php echo esc_attr($link['id']); ?>">
                                <div class="link-handle">⋮⋮</div>
                                <div class="link-fields">
                                    <div class="link-row">
                                        <select class="link-icon-select">
                                            <?php foreach ($icons as $key => $emoji): ?>
                                                <option value="<?php echo $key; ?>" <?php selected($icon_key, $key); ?>>
                                                    <?php echo $emoji . ' ' . $key; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="text" class="link-title" value="<?php echo esc_attr($link['title']); ?>" placeholder="Link Titel">
                                    </div>
                                    <input type="url" class="link-url" value="<?php echo esc_attr($link['url']); ?>" placeholder="https://...">
                                </div>
                                <button type="button" class="button-link delete-link" title="Löschen">×</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="links-empty" id="links-empty" style="<?php echo empty($links) ? '' : 'display:none;'; ?>">
                        <p>Noch keine Links vorhanden. Klicke "Link hinzufügen" um zu starten.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="editor-preview">
            <div class="preview-header">
                <span>Live Vorschau</span>
                <div class="device-toggle">
                    <button type="button" class="device-btn active" data-device="mobile">📱 Handy</button>
                    <button type="button" class="device-btn" data-device="desktop">💻 Desktop</button>
                </div>
            </div>
            <div class="preview-frame mobile" id="preview-frame" style="width: 375px; max-width: 375px;">
                <iframe id="live-preview" src="about:blank"></iframe>
            </div>
        </div>
    </div>

    <!-- WICHTIG: profile-id muss gesetzt sein für Update -->
    <input type="hidden" id="profile-id" value="<?php echo $profile_id; ?>">
</div>