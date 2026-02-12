/**
 * Profile Editor Component
 */

import React, { useEffect, useState, useCallback } from 'react';
import { useProfileStore } from '../stores/profileStore';
import { useUIStore } from '../stores/uiStore';
import { Profile, Link, Design } from '../types';
import { generatePreviewHTML, generateSlug, generateId } from '../utils/helpers';

const Editor: React.FC = () => {
    const { currentProfile, updateProfile, setCurrentProfile } = useProfileStore();
    const { activeTab, setActiveTab, isPreviewMobile } = useUIStore();
    const [formData, setFormData] = useState<Partial<Profile>>({});
    const [isSaving, setIsSaving] = useState(false);

    useEffect(() => {
        if (currentProfile) {
            setFormData({ ...currentProfile });
        }
    }, [currentProfile]);

    const handleBack = () => {
        setCurrentProfile(null);
        const url = new URL(window.location.href);
        url.searchParams.delete('profile');
        window.history.pushState({}, '', url);
    };

    const handleSave = async () => {
        if (!currentProfile || !formData) return;
        
        setIsSaving(true);
        await updateProfile(currentProfile.id, formData);
        setIsSaving(false);
    };

    const handleChange = useCallback((field: keyof Profile, value: any) => {
        setFormData(prev => ({ ...prev, [field]: value }));
    }, []);

    const handleDesignChange = useCallback((field: keyof Design, value: any) => {
        setFormData(prev => ({
            ...prev,
            design: { ...prev.design, [field]: value } as Design
        }));
    }, []);

    const addLink = () => {
        const newLink: Link = {
            id: generateId(),
            title: 'New Link',
            url: 'https://',
        };
        setFormData(prev => ({
            ...prev,
            links: [...(prev.links || []), newLink]
        }));
    };

    const updateLink = (id: string, field: keyof Link, value: string) => {
        setFormData(prev => ({
            ...prev,
            links: prev.links?.map(link => 
                link.id === id ? { ...link, [field]: value } : link
            ) || []
        }));
    };

    const removeLink = (id: string) => {
        setFormData(prev => ({
            ...prev,
            links: prev.links?.filter(link => link.id !== id) || []
        }));
    };

    if (!currentProfile || !formData) {
        return <div>Loading...</div>;
    }

    const previewHtml = generatePreviewHTML(formData as Profile);

    return (
        <div className="linky-editor">
            {/* Header */}
            <div className="linky-editor-header">
                <button className="linky-btn linky-btn-secondary" onClick={handleBack}>
                    ← Back
                </button>
                <h1>{formData.title || 'Untitled'}</h1>
                <button 
                    className="linky-btn linky-btn-primary"
                    onClick={handleSave}
                    disabled={isSaving}
                >
                    {isSaving ? 'Saving...' : '💾 Save'}
                </button>
            </div>

            <div className="linky-editor-layout">
                {/* Sidebar */}
                <div className="linky-editor-sidebar">
                    {/* Tabs */}
                    <div className="linky-editor-tabs">
                        {(['editor', 'design', 'links'] as const).map(tab => (
                            <button
                                key={tab}
                                className={activeTab === tab ? 'active' : ''}
                                onClick={() => setActiveTab(tab)}
                            >
                                {tab.charAt(0).toUpperCase() + tab.slice(1)}
                            </button>
                        ))}
                    </div>

                    {/* Tab Content */}
                    <div className="linky-editor-tab-content">
                        {activeTab === 'editor' && (
                            <div className="linky-tab-panel">
                                <div className="linky-form-group">
                                    <label>Profile Name</label>
                                    <input
                                        type="text"
                                        value={formData.title || ''}
                                        onChange={(e) => handleChange('title', e.target.value)}
                                    />
                                </div>

                                <div className="linky-form-group">
                                    <label>Slug</label>
                                    <input
                                        type="text"
                                        value={formData.slug || ''}
                                        onChange={(e) => handleChange('slug', e.target.value)}
                                    />
                                    <span className="linky-help">
                                        /linky/{formData.slug || 'profile'}/
                                    </span>
                                </div>

                                <div className="linky-form-group">
                                    <label>Bio</label>
                                    <textarea
                                        rows={4}
                                        value={formData.bio || ''}
                                        onChange={(e) => handleChange('bio', e.target.value)}
                                        placeholder="Tell people about yourself..."
                                    />
                                </div>

                                <div className="linky-form-group">
                                    <label>Avatar URL</label>
                                    <input
                                        type="url"
                                        value={formData.avatar_url || ''}
                                        onChange={(e) => handleChange('avatar_url', e.target.value)}
                                        placeholder="https://..."
                                    />
                                </div>

                                <label className="linky-checkbox">
                                    <input
                                        type="checkbox"
                                        checked={formData.verified || false}
                                        onChange={(e) => handleChange('verified', e.target.checked)}
                                    />
                                    <span>Verified Badge</span>
                                </label>
                            </div>
                        )}

                        {activeTab === 'design' && (
                            <div className="linky-tab-panel">
                                <div className="linky-form-group">
                                    <label>Background Type</label>
                                    <select
                                        value={formData.design?.bg_type || 'gradient'}
                                        onChange={(e) => handleDesignChange('bg_type', e.target.value)}
                                    >
                                        <option value="gradient">Gradient</option>
                                        <option value="solid">Solid Color</option>
                                        <option value="image">Image</option>
                                    </select>
                                </div>

                                {formData.design?.bg_type === 'gradient' && (
                                    <>
                                        <div className="linky-color-field">
                                            <label>Color 1</label>
                                            <input
                                                type="color"
                                                value={formData.design?.color1 || '#667eea'}
                                                onChange={(e) => handleDesignChange('color1', e.target.value)}
                                            />
                                        </div>
                                        <div className="linky-color-field">
                                            <label>Color 2</label>
                                            <input
                                                type="color"
                                                value={formData.design?.color2 || '#764ba2'}
                                                onChange={(e) => handleDesignChange('color2', e.target.value)}
                                            />
                                        </div>
                                    </>
                                )}

                                <div className="linky-color-field">
                                    <label>Text Color</label>
                                    <input
                                        type="color"
                                        value={formData.design?.text_color || '#ffffff'}
                                        onChange={(e) => handleDesignChange('text_color', e.target.value)}
                                    />
                                </div>

                                <div className="linky-color-field">
                                    <label>Button Color</label>
                                    <input
                                        type="color"
                                        value={formData.design?.button_color || '#ffffff'}
                                        onChange={(e) => handleDesignChange('button_color', e.target.value)}
                                    />
                                </div>

                                <div className="linky-form-group">
                                    <label>Button Radius: {formData.design?.button_radius || 12}px</label>
                                    <input
                                        type="range"
                                        min="0"
                                        max="50"
                                        value={formData.design?.button_radius || 12}
                                        onChange={(e) => handleDesignChange('button_radius', parseInt(e.target.value))}
                                    />
                                </div>
                            </div>
                        )}

                        {activeTab === 'links' && (
                            <div className="linky-tab-panel">
                                <button className="linky-btn linky-btn-secondary" onClick={addLink}>
                                    + Add Link
                                </button>

                                <div className="linky-links-list">
                                    {formData.links?.map((link, index) => (
                                        <div key={link.id} className="linky-link-editor">
                                            <div className="linky-link-editor-header">
                                                <span>Link #{index + 1}</span>
                                                <button 
                                                    className="linky-btn linky-btn-danger linky-btn-sm"
                                                    onClick={() => removeLink(link.id)}
                                                >
                                                    Remove
                                                </button>
                                            </div>
                                            <input
                                                type="text"
                                                placeholder="Title"
                                                value={link.title}
                                                onChange={(e) => updateLink(link.id, 'title', e.target.value)}
                                            />
                                            <input
                                                type="url"
                                                placeholder="https://..."
                                                value={link.url}
                                                onChange={(e) => updateLink(link.id, 'url', e.target.value)}
                                            />
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* Preview */}
                <div className={`linky-editor-preview ${isPreviewMobile ? 'mobile' : ''}`}>
                    <div className="linky-preview-header">
                        <span>Preview</span>
                        <div className="linky-device-toggle">
                            <button className={isPreviewMobile ? 'active' : ''}>📱</button>
                            <button>💻</button>
                        </div>
                    </div>
                    <iframe
                        srcDoc={previewHtml}
                        title="Preview"
                        sandbox="allow-scripts"
                    />
                </div>
            </div>
        </div>
    );
};

export default Editor;
