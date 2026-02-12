/**
 * RT-Linky Frontend App
 */
const { useState, useEffect } = useState, useEffect;
const { createElement: el } = wp.element;

const ICONS = {
    'link': '🔗',
    'email': '✉️',
    'phone': '📞',
    'whatsapp': '💬',
    'instagram': '📷',
    'twitter': '🐦',
    'facebook': '👍',
    'linkedin': '💼',
    'youtube': '▶️',
    'tiktok': '🎵',
    'spotify': '🎧',
    'github': '💻',
    'website': '🌐',
    'location': '📍',
    'calendar': '📅',
    'download': '⬇️',
    'document': '📄',
    'video': '🎬',
    'music': '🎼',
    'shop': '🛒',
    'coffee': '☕',
    'heart': '❤️',
    'star': '⭐',
    'bookmark': '🔖',
    'share': '📤',
    'rss': '📡',
};

const Frontend = () => {
    const [profile, setProfile] = useState(rtLinkyData.profile || {});
    const license = rtLinkyData.license;
    const showVerified = rtLinkyData.showVerified;
    const footerText = rtLinkyData.footerText;

    // Fallback für leere Daten
    const data = {
        title: profile.title || '',
        description: profile.description || '',
        avatar: profile.avatar || '',
        backgroundColor: profile.backgroundColor || '#ffffff',
        backgroundImage: profile.backgroundImage || '',
        links: profile.links || [],
    };

    return el('div', { className: 'rt-linky-profile' }, [
        // Header mit Avatar
        el('div', { key: 'header', className: 'rt-linky-header' }, [
            data.avatar && el('img', {
                key: 'avatar',
                src: data.avatar,
                alt: data.title,
                className: 'rt-linky-avatar',
            }),
            
            el('div', { key: 'title-wrap', className: 'rt-linky-title-wrap' }, [
                el('h1', { key: 'title', className: 'rt-linky-title' }, [
                    data.title,
                    showVerified && el('span', {
                        key: 'verified',
                        className: 'rt-linky-verified',
                        title: 'Verifiziert',
                    }, '✓'),
                ]),
                data.description && el('p', {
                    key: 'desc',
                    className: 'rt-linky-description',
                }, data.description),
            ]),
        ]),

        // Links
        el('div', { key: 'links', className: 'rt-linky-links' },
            data.links.map((link, index) => 
                el('a', {
                    key: link.id || index,
                    href: link.url,
                    className: 'rt-linky-link',
                    target: '_blank',
                    rel: 'noopener noreferrer',
                }, [
                    el('span', {
                        key: 'icon',
                        className: 'rt-linky-link-icon',
                    }, ICONS[link.icon] || '🔗'),
                    
                    el('span', {
                        key: 'content',
                        className: 'rt-linky-link-content',
                    }, [
                        el('span', {
                            key: 'title',
                            className: 'rt-linky-link-title',
                        }, link.title),
                        
                        link.subtitle && el('span', {
                            key: 'subtitle',
                            className: 'rt-linky-link-subtitle',
                        }, link.subtitle),
                    ]),
                ])
            )
        ),

        // Footer
        footerText && el('div', { key: 'footer', className: 'rt-linky-footer' }, [
            el('p', { key: 'text' }, footerText),
        ]),
    ]);
};

// Render
const container = document.getElementById('rt-linky-frontend');
if (container) {
    wp.element.render(el(Frontend), container);
}