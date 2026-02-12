/**
 * Dashboard Component
 */

import React, { useState } from 'react';
import { useProfileStore } from '../stores/profileStore';
import { useUIStore } from '../stores/uiStore';
import ProfileCard from './ProfileCard';
import CreateProfileModal from './CreateProfileModal';
import { formatNumber } from '../utils/helpers';

const Dashboard: React.FC = () => {
    const { profiles, isLoading } = useProfileStore();
    const { viewMode, searchQuery, setViewMode, setSearchQuery } = useUIStore();
    const [showCreateModal, setShowCreateModal] = useState(false);

    const filteredProfiles = profiles.filter(profile =>
        profile.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
        profile.slug.toLowerCase().includes(searchQuery.toLowerCase())
    );

    const totalViews = profiles.reduce((sum, p) => sum + p.views, 0);
    const totalClicks = profiles.reduce((sum, p) => sum + p.clicks, 0);

    return (
        <div className="rt-linky-dashboard">
            {/* Header */}
            <div className="rt-linky-dashboard-header">
                <div className="rt-linky-dashboard-brand">
                    <span className="rt-linky-logo">🔗</span>
                    <div>
                        <h1>RT-Linky</h1>
                        <p>Create beautiful link-in-bio pages</p>
                    </div>
                </div>
                <button 
                    className="rt-linky-btn rt-linky-btn-primary"
                    onClick={() => setShowCreateModal(true)}
                >
                    <span>+</span> Create Profile
                </button>
            </div>

            {/* Stats Cards */}
            <div className="rt-linky-stats-row">
                <div className="rt-linky-stat-card">
                    <div className="rt-linky-stat-icon">📊</div>
                    <div className="rt-linky-stat-content">
                        <span className="rt-linky-stat-value">{formatNumber(profiles.length)}</span>
                        <span className="rt-linky-stat-label">Profiles</span>
                    </div>
                </div>
                <div className="rt-linky-stat-card">
                    <div className="rt-linky-stat-icon">👁️</div>
                    <div className="rt-linky-stat-content">
                        <span className="rt-linky-stat-value">{formatNumber(totalViews)}</span>
                        <span className="rt-linky-stat-label">Total Views</span>
                    </div>
                </div>
                <div className="rt-linky-stat-card">
                    <div className="rt-linky-stat-icon">🖱️</div>
                    <div className="rt-linky-stat-content">
                        <span className="rt-linky-stat-value">{formatNumber(totalClicks)}</span>
                        <span className="rt-linky-stat-label">Total Clicks</span>
                    </div>
                </div>
                <div className="rt-linky-stat-card">
                    <div className="rt-linky-stat-icon">📈</div>
                    <div className="rt-linky-stat-content">
                        <span className="rt-linky-stat-value">
                            {totalViews > 0 ? Math.round((totalClicks / totalViews) * 100) : 0}%
                        </span>
                        <span className="rt-linky-stat-label">Click Rate</span>
                    </div>
                </div>
            </div>

            {/* Toolbar */}
            <div className="rt-linky-toolbar">
                <div className="rt-linky-search">
                    <input
                        type="text"
                        placeholder="Search profiles..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                    />
                </div>
                <div className="rt-linky-view-toggle">
                    <button
                        className={viewMode === 'grid' ? 'active' : ''}
                        onClick={() => setViewMode('grid')}
                        title="Grid View"
                    >
                        ⊞
                    </button>
                    <button
                        className={viewMode === 'list' ? 'active' : ''}
                        onClick={() => setViewMode('list')}
                        title="List View"
                    >
                        ☰
                    </button>
                </div>
            </div>

            {/* Profiles Grid */}
            {isLoading ? (
                <div className="rt-linky-loading">
                    <div className="rt-linky-spinner"></div>
                    <p>Loading profiles...</p>
                </div>
            ) : filteredProfiles.length === 0 ? (
                <div className="rt-linky-empty">
                    <div className="rt-linky-empty-icon">📝</div>
                    <h3>No profiles yet</h3>
                    <p>Create your first link-in-bio profile to get started</p>
                    <button 
                        className="rt-linky-btn rt-linky-btn-primary"
                        onClick={() => setShowCreateModal(true)}
                    >
                        Create First Profile
                    </button>
                </div>
            ) : (
                <div className={`rt-linky-profiles-${viewMode}`}>
                    {filteredProfiles.map(profile => (
                        <ProfileCard key={profile.id} profile={profile} />
                    ))}
                </div>
            )}

            {/* Create Modal */}
            {showCreateModal && (
                <CreateProfileModal onClose={() => setShowCreateModal(false)} />
            )}
        </div>
    );
};

export default Dashboard;
