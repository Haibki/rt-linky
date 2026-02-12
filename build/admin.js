/**
 * RT-Linky Admin React App
 */
const { useState, useEffect } = wp.element;
const { createElement: el } = wp.element;
const { Button, TextControl, SelectControl, BaseControl } = wp.components;
const apiFetch = wp.apiFetch;

// Icons Mapping
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

const App = () => {
    const postId = rtLinkyAdmin.postId;
    const license = rtLinkyAdmin.license;
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [profile, setProfile] = useState({
        title: '',
        description: '',
        avatar: '',
        backgroundColor: '#ffffff',
        backgroundImage: '',
        links: [],
    });

    useEffect(() => {
        loadProfile();
    }, []);

    const loadProfile = async () => {
        try {
            const response = await apiFetch({
                path: `rt-linky/v1/profile/${postId}`,
            });
            setProfile({
                title: response.title || '',
                description: response.description || '',
                avatar: response.avatar || '',
                backgroundColor: response.backgroundColor || '#ffffff',
                backgroundImage: response.backgroundImage || '',
                links: response.links || [],
            });
        } catch (error) {
            console.error('Fehler beim Laden:', error);
        } finally {
            setLoading(false);
        }
    };

    const saveProfile = async () => {
        setSaving(true);
        try {
            await apiFetch({
                path: `rt-linky/v1/profile/${postId}`,
                method: 'POST',
                data: profile,
            });
            alert(rtLinkyAdmin.strings.saveSuccess);
        } catch (error) {
            alert(error.message || rtLinkyAdmin.strings.saveError);
        } finally {
            setSaving(false);
        }
    };

    const addLink = () => {
        if (!license.isPro && profile.links.length >= 2) {
            alert(rtLinkyAdmin.strings.maxLinksReached);
            return;
        }
        
        setProfile({
            ...profile,
            links: [
                ...profile.links,
                {
                    id: Date.now(),
                    title: '',
                    url: '',
                    icon: 'link',
                    subtitle: '',
                },
            ],
        });
    };

    const updateLink = (index, field, value) => {
        if (field === 'icon' && !license.isPro) {
            const freeIcons = ['link', 'email'];
            if (!freeIcons.includes(value)) {
                alert(rtLinkyAdmin.strings.proFeature);
                return;
            }
        }

        const newLinks = [...profile.links];
        newLinks[index][field] = value;
        setProfile({ ...profile, links: newLinks });
    };

    const removeLink = (index) => {
        const newLinks = profile.links.filter((_, i) => i !== index);
        setProfile({ ...profile, links: newLinks });
    };

    const moveLink = (index, direction) => {
        if (
            (direction === -1 && index === 0) ||
            (direction === 1 && index === profile.links.length - 1)
        ) {
            return;
        }

        const newLinks = [...profile.links];
        const temp = newLinks[index];
        newLinks[index] = newLinks[index + direction];
        newLinks[index + direction] = temp;
        setProfile({ ...profile, links: newLinks });
    };

    const uploadImage = (field) => {
        const frame = wp.media({
            title: 'Bild auswählen',
            button: { text: 'Bild verwenden' },
            multiple: false,
        });

        frame.on('select', () => {
            const attachment = frame.state().get('selection').first().toJSON();
            setProfile({ ...profile, [field]: attachment.url });
        });

        frame.open();
    };

    if (loading) {
        return el('div', { className: 'rt-linky-loading' }, 'Laden...');
    }

    return el('div', { className: 'rt-linky-admin' }, [
        // Header
        el('div', { key: 'header', className: 'rt-linky-header' }, [
            el('h2', { key: 'title' }, 'Link-in-Bio Editor'),
            el('span', {
                key: 'badge',
                className: `rt-linky-badge ${license.isPro ? 'pro' : 'free'}`,
            }, license.isPro ? '⭐ PRO' : '🔒 FREE'),
        ]),

        // Profil Einstellungen
        el('div', { key: 'profile', className: 'rt-linky-section' }, [
            el('h3', { key: 'title' }, 'Profil'),
            
            el(TextControl, {
                key: 'title-input',
                label: 'Titel',
                value: profile.title,
                onChange: (value) => setProfile({ ...profile, title: value }),
            }),
            
            el(TextControl, {
                key: 'desc-input',
                label: 'Beschreibung',
                value: profile.description,
                onChange: (value) => setProfile({ ...profile, description: value }),
            }),

            el(BaseControl, {
                key: 'avatar-control',
                label: 'Avatar',
            }, el('div', { className: 'rt-linky-media' }, [
                profile.avatar && el('img', {
                    key: 'avatar-img',
                    src: profile.avatar,
                    alt: 'Avatar',
                    className: 'rt-linky-avatar-preview',
                }),
                el(Button, {
                    key: 'avatar-btn',
                    onClick: () => uploadImage('avatar'),
                    variant: 'secondary',
                }, profile.avatar ? 'Avatar ändern' : 'Avatar hinzufügen'),
                profile.avatar && el(Button, {
                    key: 'avatar-remove',
                    onClick: () => setProfile({ ...profile, avatar: '' }),
                    variant: 'link',
                    isDestructive: true,
                }, 'Entfernen'),
            ])),

            el(TextControl, {
                key: 'bg-color',
                label: 'Hintergrundfarbe',
                type: 'color',
                value: profile.backgroundColor,
                onChange: (value) => setProfile({ ...profile, backgroundColor: value }),
            }),

            el(BaseControl, {
                key: 'bg-image-control',
                label: `Hintergrundbild ${!license.isPro ? '(Nur Pro)' : ''}`,
            }, el('div', { className: 'rt-linky-media' }, [
                !license.isPro && el('div', {
                    key: 'bg-locked',
                    className: 'rt-linky-locked',
                }, '🔒 Upgrade auf Pro für Hintergrundbilder'),
                
                license.isPro && profile.backgroundImage && el('img', {
                    key: 'bg-img',
                    src: profile.backgroundImage,
                    alt: 'Hintergrund',
                    className: 'rt-linky-bg-preview',
                }),
                
                license.isPro && el(Button, {
                    key: 'bg-btn',
                    onClick: () => uploadImage('backgroundImage'),
                    variant: 'secondary',
                }, profile.backgroundImage ? 'Hintergrund ändern' : 'Hintergrund hinzufügen'),
                
                license.isPro && profile.backgroundImage && el(Button, {
                    key: 'bg-remove',
                    onClick: () => setProfile({ ...profile, backgroundImage: '' }),
                    variant: 'link',
                    isDestructive: true,
                }, 'Entfernen'),
            ])),
        ]),

        // Links Section
        el('div', { key: 'links', className: 'rt-linky-section' }, [
            el('div', { key: 'links-header', className: 'rt-linky-links-header' }, [
                el('h3', { key: 'title' }, [
                    'Links ',
                    el('span', { key: 'count', className: 'rt-linky-count' },
                        `(${profile.links.length}${license.isPro ? '' : '/2'})`
                    ),
                ]),
                
                !license.isPro && profile.links.length >= 2 && el('div', {
                    key: 'limit',
                    className: 'rt-linky-limit-warning',
                }, 'Max. 2 Links in Free-Version'),
            ]),

            el('div', { key: 'links-list', className: 'rt-linky-links' },
                profile.links.map((link, index) => 
                    el('div', {
                        key: link.id,
                        className: 'rt-linky-link-item',
                    }, [
                        el('span', {
                            key: 'handle',
                            className: 'rt-linky-handle',
                        }, '⋮⋮'),

                        el(SelectControl, {
                            key: 'icon',
                            value: link.icon,
                            options: Object.entries(ICONS).map(([value, data]) => ({
                                value,
                                label: `${data} ${value}`,
                                disabled: !license.isPro && !['link', 'email'].includes(value),
                            })),
                            onChange: (value) => updateLink(index, 'icon', value),
                        }),

                        el(TextControl, {
                            key: 'title',
                            placeholder: 'Link-Titel',
                            value: link.title,
                            onChange: (value) => updateLink(index, 'title', value),
                        }),

                        el(TextControl, {
                            key: 'url',
                            placeholder: 'https://...',
                            value: link.url,
                            onChange: (value) => updateLink(index, 'url', value),
                        }),

                        license.isPro && el(TextControl, {
                            key: 'subtitle',
                            placeholder: 'Untertitel (optional)',
                            value: link.subtitle,
                            onChange: (value) => updateLink(index, 'subtitle', value),
                        }),

                        el('div', {
                            key: 'actions',
                            className: 'rt-linky-link-actions',
                        }, [
                            el(Button, {
                                key: 'up',
                                onClick: () => moveLink(index, -1),
                                disabled: index === 0,
                                variant: 'tertiary',
                                size: 'small',
                            }, '↑'),
                            el(Button, {
                                key: 'down',
                                onClick: () => moveLink(index, 1),
                                disabled: index === profile.links.length - 1,
                                variant: 'tertiary',
                                size: 'small',
                            }, '↓'),
                            el(Button, {
                                key: 'remove',
                                onClick: () => removeLink(index),
                                variant: 'tertiary',
                                isDestructive: true,
                                size: 'small',
                            }, '🗑️'),
                        ]),
                    ])
                )
            ),

            el(Button, {
                key: 'add-btn',
                onClick: addLink,
                variant: 'primary',
                disabled: !license.isPro && profile.links.length >= 2,
                className: 'rt-linky-add-btn',
            }, '+ Link hinzufügen'),
        ]),

        // Speichern Button
        el('div', { key: 'footer', className: 'rt-linky-footer' }, [
            el(Button, {
                key: 'save',
                onClick: saveProfile,
                isPrimary: true,
                isBusy: saving,
                disabled: saving,
            }, saving ? 'Speichern...' : 'Profil speichern'),
        ]),
    ]);
};

// Render App
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('rt-linky-root');
    if (root) {
        wp.element.render(el(App), root);
    }
});