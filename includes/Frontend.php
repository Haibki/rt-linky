<?php
namespace RTLinky;

class Frontend {
    private $license;
    private $settings;
    
    public function __construct() {
        $this->license = License::getInstance();
        $this->settings = get_option('rt_linky_settings', [
            'show_footer' => true,
            'footer_text' => 'Erstellt mit RT-Linky'
        ]);
        
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_shortcode('rt_linky', [$this, 'renderShortcode']);
        add_action('init', [$this, 'registerRewriteRules']);
        add_filter('template_include', [$this, 'loadProfileTemplate']);
    }
    
    public function renderShortcode($atts) {
        $atts = shortcode_atts([
            'id' => 0,
            'slug' => ''
        ], $atts);
        
        if (!empty($atts['slug'])) {
            $profile = get_page_by_path($atts['slug'], OBJECT, 'rt_linky_profile');
            $profile_id = $profile ? $profile->ID : 0;
        } else {
            $profile_id = intval($atts['id']);
        }
        
        if (!$profile_id) {
            return '<p>Profil nicht gefunden.</p>';
        }
        
        return $this->getProfileOutput($profile_id);
    }
    
    private function getProfileOutput($profile_id) {
        $profile = get_post($profile_id);
        if (!$profile) return '';
        
        $links = get_post_meta($profile_id, '_rt_linky_links', true) ?: [];
        $appearance = get_post_meta($profile_id, '_rt_linky_appearance', true) ?: [];
        $subtitle = get_post_meta($profile_id, '_rt_linky_subtitle', true) ?: '';
        
        $avatar = get_the_post_thumbnail_url($profile_id, 'medium') ?: '';
        $isPro = $this->license->isPro();
        
        // Hintergrund
        $bg_style = '';
        switch ($appearance['bg_type'] ?? 'color') {
            case 'gradient':
                $bg_style = 'background: ' . esc_attr($appearance['bg_gradient'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)') . ';';
                break;
            case 'image':
                if ($isPro && !empty($appearance['bg_image'])) {
                    $img_url = wp_get_attachment_url($appearance['bg_image']);
                    $bg_style = 'background-image: url(' . esc_url($img_url) . '); background-size: cover;';
                }
                break;
            default:
                $bg_style = 'background-color: ' . esc_attr($appearance['bg_color'] ?? '#ffffff') . ';';
        }
        
        ob_start();
        ?>
        <div class="rt-linky-profile" style="<?php echo $bg_style; ?>">
            <div class="rt-linky-container">
                <?php if ($avatar): ?>
                    <div class="rt-linky-avatar">
                        <img src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($profile->post_title); ?>">
                    </div>
                <?php endif; ?>
                
                <h1 class="rt-linky-title">
                    <?php echo esc_html($profile->post_title); ?>
                    <?php if ($isPro && !empty($appearance['verified_badge'])): ?>
                        <span class="verified-badge" title="Verifiziert">✓</span>
                    <?php endif; ?>
                </h1>
                
                <?php if ($subtitle): ?>
                    <p class="rt-linky-subtitle"><?php echo esc_html($subtitle); ?></p>
                <?php endif; ?>
                
                <div class="rt-linky-bio">
                    <?php echo wpautop(esc_html($profile->post_content)); ?>
                </div>
                
                <div class="rt-linky-links">
                    <?php foreach ($links as $link): 
                        if (empty($link['url'])) continue;
                    ?>
                        <a href="<?php echo esc_url($link['url']); ?>" 
                           class="rt-linky-link"
                           target="_blank"
                           rel="noopener">
                            <?php if (!empty($link['icon'])): ?>
                                <span class="link-icon"><?php echo $this->getIconSvg($link['icon']); ?></span>
                            <?php endif; ?>
                            <span class="link-text">
                                <?php echo esc_html($link['title']); ?>
                                <?php if ($isPro && !empty($this->settings['enable_subtitles']) && !empty($link['subtitle'])): ?>
                                    <span class="link-subtitle"><?php echo esc_html($link['subtitle']); ?></span>
                                <?php endif; ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
                
                <?php 
                // Footer-Logik: Free immer an, Pro konfigurierbar
                $show_footer = !$isPro ? true : $this->settings['show_footer'];
                if ($show_footer): 
                ?>
                    <div class="rt-linky-footer">
                        <a href="https://rettoro.de/rt-linky" target="_blank" rel="noopener">
                            <?php echo esc_html($this->settings['footer_text'] ?? 'Erstellt mit RT-Linky'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function getIconSvg($icon_id) {
        // Einfache Emoji-Rückgabe für Demo
        $icons = [
            'link' => '🔗', 'home' => '🏠', 'user' => '👤', 'mail' => '✉️',
            'phone' => '📞', 'video' => '🎥', 'music' => '🎵', 'photo' => '📷',
            'shop' => '🛒', 'heart' => '❤️', 'star' => '⭐', 'fire' => '🔥',
            'rocket' => '🚀', 'gift' => '🎁', 'calendar' => '📅', 'map' => '🗺️',
            'bookmark' => '🔖', 'bell' => '🔔', 'flag' => '🚩', 'tag' => '🏷️',
            'briefcase' => '💼', 'chart' => '📊', 'code' => '💻', 'coffee' => '☕',
            'globe' => '🌍'
        ];
        return $icons[$icon_id] ?? '🔗';
    }
}