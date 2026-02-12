<?php
/**
 * RT-Linky License Configuration
 */

namespace RT\Linky;

class LicenseConfig {
    
    public static function isPro(): bool {
        return (bool) get_option('rt_linky_pro_license_active', false);
    }
    
    public static function activatePro(): void {
        update_option('rt_linky_pro_license_active', true);
    }
    
    public static function deactivatePro(): void {
        update_option('rt_linky_pro_license_active', false);
    }
    
    public static function getMaxLinks(): int {
        return self::isPro() ? PHP_INT_MAX : 2;
    }
    
    public static function getAvailableIcons(): array {
        $freeIcons = [
            'link' => ['label' => 'Link', 'icon' => '🔗'],
            'email' => ['label' => 'E-Mail', 'icon' => '✉️'],
        ];
        
        $proIcons = [
            'phone' => ['label' => 'Telefon', 'icon' => '📞'],
            'whatsapp' => ['label' => 'WhatsApp', 'icon' => '💬'],
            'instagram' => ['label' => 'Instagram', 'icon' => '📷'],
            'twitter' => ['label' => 'Twitter/X', 'icon' => '🐦'],
            'facebook' => ['label' => 'Facebook', 'icon' => '👍'],
            'linkedin' => ['label' => 'LinkedIn', 'icon' => '💼'],
            'youtube' => ['label' => 'YouTube', 'icon' => '▶️'],
            'tiktok' => ['label' => 'TikTok', 'icon' => '🎵'],
            'spotify' => ['label' => 'Spotify', 'icon' => '🎧'],
            'github' => ['label' => 'GitHub', 'icon' => '💻'],
            'website' => ['label' => 'Website', 'icon' => '🌐'],
            'location' => ['label' => 'Standort', 'icon' => '📍'],
            'calendar' => ['label' => 'Kalender', 'icon' => '📅'],
            'download' => ['label' => 'Download', 'icon' => '⬇️'],
            'document' => ['label' => 'Dokument', 'icon' => '📄'],
            'video' => ['label' => 'Video', 'icon' => '🎬'],
            'music' => ['label' => 'Musik', 'icon' => '🎼'],
            'shop' => ['label' => 'Shop', 'icon' => '🛒'],
            'coffee' => ['label' => 'Kaffee/Buy Me', 'icon' => '☕'],
            'heart' => ['label' => 'Herz', 'icon' => '❤️'],
            'star' => ['label' => 'Stern', 'icon' => '⭐'],
            'bookmark' => ['label' => 'Lesezeichen', 'icon' => '🔖'],
            'share' => ['label' => 'Teilen', 'icon' => '📤'],
            'rss' => ['label' => 'RSS', 'icon' => '📡'],
        ];
        
        return self::isPro() ? array_merge($freeIcons, $proIcons) : $freeIcons;
    }
    
    public static function isIconAvailable(string $iconKey): bool {
        return isset(self::getAvailableIcons()[$iconKey]);
    }
    
    public static function allowBackgroundImage(): bool {
        return self::isPro();
    }
    
    public static function canDisableFooter(): bool {
        return self::isPro();
    }
    
    public static function allowLinkSubtitle(): bool {
        return self::isPro();
    }
    
    public static function allowVerifiedBadge(): bool {
        return self::isPro();
    }
    
    public static function getFooterText(int $postId): string {
        if (!self::isPro()) {
            return 'Erstellt mit RT-Linky';
        }
        
        $showFooter = get_post_meta($postId, '_rt_linky_show_footer', true);
        if ($showFooter === '0') {
            return '';
        }
        
        return get_post_meta($postId, '_rt_linky_footer_text', true) ?: 'Erstellt mit RT-Linky';
    }
    
    public static function toArray(): array {
        $isPro = self::isPro();
        
        return [
            'isPro' => $isPro,
            'maxLinks' => $isPro ? null : 2,
            'availableIcons' => array_keys(self::getAvailableIcons()),
            'features' => [
                'backgroundImage' => $isPro,
                'disableFooter' => $isPro,
                'linkSubtitle' => $isPro,
                'verifiedBadge' => $isPro,
                'unlimitedLinks' => $isPro,
            ]
        ];
    }
}