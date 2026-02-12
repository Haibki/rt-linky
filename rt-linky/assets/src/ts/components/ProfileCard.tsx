/**
 * Profile Card Component
 */

import React from 'react';
import { Profile } from '../types';
import { useProfileStore } from '../stores/profileStore';
import { formatNumber } from '../utils/helpers';

interface ProfileCardProps {
    profile: Profile;
}

const ProfileCard: React.FC<ProfileCardProps> = ({ profile }) => {
    const { setCurrentProfile, deleteProfile } = useProfileStore();

    const handleEdit = () => {
        setCurrentProfile(profile);
        // Update URL
        const url = new URL(window.location.href);
        url.searchParams.set('profile', profile.id.toString());
        window.history.pushState({}, '', url);
    };

    const handleDelete = async () => {
        if (confirm('Are you sure you want to delete this profile?')) {
            await deleteProfile(profile.id);
        }
    };

    const clickRate = profile.views > 0 
        ? Math.round((profile.clicks / profile.views) * 100) 
        : 0;

    return (
        <div className="linky-profile-card">
            <div className="linky-profile-card-header">
                {profile.avatar_url ? (
                    <img 
                        src={profile.avatar_url} 
                        alt={profile.title}
                        className="linky-profile-avatar"
                    />
                ) : (
                    <div className="linky-profile-avatar-default">
                        {profile.title.charAt(0).toUpperCase()}
                    </div>
                )}
                <div className="linky-profile-info">
                    <h3>
                        {profile.title}
                        {profile.verified && <span className="linky-verified">✓</span>}
                    </h3>
                    <code className="linky-profile-slug">/{profile.slug}</code>
                </div>
            </div>

            <div className="linky-profile-stats">
                <div className="linky-profile-stat">
                    <span className="linky-stat-number">{formatNumber(profile.views)}</span>
                    <span className="linky-stat-label">Views</span>
                </div>
                <div className="linky-profile-stat">
                    <span className="linky-stat-number">{formatNumber(profile.clicks)}</span>
                    <span className="linky-stat-label">Clicks</span>
                </div>
                <div className="linky-profile-stat">
                    <span className="linky-stat-number">{clickRate}%</span>
                    <span className="linky-stat-label">Rate</span>
                </div>
                <div className="linky-profile-stat">
                    <span className="linky-stat-number">{profile.links?.length || 0}</span>
                    <span className="linky-stat-label">Links</span>
                </div>
            </div>

            <div className="linky-profile-actions">
                <button 
                    className="linky-btn linky-btn-secondary"
                    onClick={handleEdit}
                >
                    ✏️ Edit
                </button>
                <a 
                    href={profile.view_link}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="linky-btn linky-btn-secondary"
                >
                    👁️ View
                </a>
                <button 
                    className="linky-btn linky-btn-danger"
                    onClick={handleDelete}
                >
                    🗑️ Delete
                </button>
            </div>
        </div>
    );
};

export default ProfileCard;
