/**
 * Create Profile Modal
 */

import React, { useState } from 'react';
import { useProfileStore } from '../stores/profileStore';
import { generateSlug } from '../utils/helpers';

interface CreateProfileModalProps {
    onClose: () => void;
}

const CreateProfileModal: React.FC<CreateProfileModalProps> = ({ onClose }) => {
    const { createProfile, setCurrentProfile } = useProfileStore();
    const [title, setTitle] = useState('');
    const [slug, setSlug] = useState('');
    const [isCreating, setIsCreating] = useState(false);

    const handleTitleChange = (value: string) => {
        setTitle(value);
        if (!slug) {
            setSlug(generateSlug(value));
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        if (!title) return;
        
        setIsCreating(true);
        
        const profile = await createProfile({
            title,
            slug: slug || generateSlug(title),
        });
        
        setIsCreating(false);
        
        if (profile) {
            setCurrentProfile(profile);
            onClose();
        }
    };

    return (
        <div className="linky-modal-overlay" onClick={onClose}>
            <div className="linky-modal" onClick={(e) => e.stopPropagation()}>
                <div className="linky-modal-header">
                    <h2>Create New Profile</h2>
                    <button className="linky-modal-close" onClick={onClose}>×</button>
                </div>
                
                <form onSubmit={handleSubmit}>
                    <div className="linky-form-group">
                        <label htmlFor="profile-title">Profile Name *</label>
                        <input
                            type="text"
                            id="profile-title"
                            value={title}
                            onChange={(e) => handleTitleChange(e.target.value)}
                            placeholder="e.g., My Awesome Profile"
                            required
                            autoFocus
                        />
                    </div>
                    
                    <div className="linky-form-group">
                        <label htmlFor="profile-slug">URL Slug</label>
                        <input
                            type="text"
                            id="profile-slug"
                            value={slug}
                            onChange={(e) => setSlug(e.target.value)}
                            placeholder="my-awesome-profile"
                        />
                        <span className="linky-form-help">
                            URL will be: /linky/{slug || 'my-awesome-profile'}/
                        </span>
                    </div>
                    
                    <div className="linky-modal-actions">
                        <button 
                            type="button" 
                            className="linky-btn linky-btn-secondary"
                            onClick={onClose}
                            disabled={isCreating}
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            className="linky-btn linky-btn-primary"
                            disabled={!title || isCreating}
                        >
                            {isCreating ? 'Creating...' : 'Create Profile'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default CreateProfileModal;
