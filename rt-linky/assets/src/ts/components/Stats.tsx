/**
 * Statistics Component
 */

import React, { useEffect } from 'react';
import { useProfileStore } from '../stores/profileStore';
import { useUIStore } from '../stores/uiStore';

const Stats: React.FC = () => {
    const { profiles, globalStats, fetchProfiles, fetchGlobalStats } = useProfileStore();
    const { statsPeriod, setStatsPeriod } = useUIStore();

    useEffect(() => {
        fetchProfiles();
        fetchGlobalStats(statsPeriod);
    }, [fetchProfiles, fetchGlobalStats, statsPeriod]);

    const totalViews = profiles.reduce((sum, p) => sum + p.views, 0);
    const totalClicks = profiles.reduce((sum, p) => sum + p.clicks, 0);

    return (
        <div className="linky-stats-page">
            <h1>Statistics</h1>

            <div className="linky-stats-filter">
                <label>Period:</label>
                <select 
                    value={statsPeriod} 
                    onChange={(e) => setStatsPeriod(parseInt(e.target.value) as any)}
                >
                    <option value={7}>Last 7 days</option>
                    <option value={30}>Last 30 days</option>
                    <option value={90}>Last 90 days</option>
                    <option value={365}>Last year</option>
                </select>
            </div>

            <div className="linky-stats-grid">
                <div className="linky-stat-card large">
                    <div className="linky-stat-icon">👁️</div>
                    <div className="linky-stat-content">
                        <span className="linky-stat-value">{totalViews.toLocaleString()}</span>
                        <span className="linky-stat-label">Total Views</span>
                    </div>
                </div>

                <div className="linky-stat-card large">
                    <div className="linky-stat-icon">🖱️</div>
                    <div className="linky-stat-content">
                        <span className="linky-stat-value">{totalClicks.toLocaleString()}</span>
                        <span className="linky-stat-label">Total Clicks</span>
                    </div>
                </div>

                <div className="linky-stat-card large">
                    <div className="linky-stat-icon">📊</div>
                    <div className="linky-stat-content">
                        <span className="linky-stat-value">
                            {totalViews > 0 ? Math.round((totalClicks / totalViews) * 100) : 0}%
                        </span>
                        <span className="linky-stat-label">Click Rate</span>
                    </div>
                </div>
            </div>

            <h2>Top Performing Profiles</h2>
            <table className="linky-stats-table">
                <thead>
                    <tr>
                        <th>Profile</th>
                        <th>Views</th>
                        <th>Clicks</th>
                        <th>Click Rate</th>
                    </tr>
                </thead>
                <tbody>
                    {profiles
                        .sort((a, b) => b.views - a.views)
                        .slice(0, 10)
                        .map(profile => (
                            <tr key={profile.id}>
                                <td>{profile.title}</td>
                                <td>{profile.views.toLocaleString()}</td>
                                <td>{profile.clicks.toLocaleString()}</td>
                                <td>
                                    {profile.views > 0 
                                        ? Math.round((profile.clicks / profile.views) * 100) 
                                        : 0}%
                                </td>
                            </tr>
                        ))}
                </tbody>
            </table>
        </div>
    );
};

export default Stats;
