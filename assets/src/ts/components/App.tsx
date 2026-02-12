/**
 * Main App Component
 */

import React, { useEffect } from 'react';
import { useProfileStore } from '../stores/profileStore';
import Dashboard from './Dashboard';
import Editor from './Editor';
import Stats from './Stats';

interface AppProps {
    initialProfileId?: number;
}

const App: React.FC<AppProps> = ({ initialProfileId }) => {
    const { currentProfile, fetchProfiles, fetchProfile, setCurrentProfile } = useProfileStore();

    useEffect(() => {
        fetchProfiles();
        
        // Check for profile ID in URL
        const urlParams = new URLSearchParams(window.location.search);
        const profileId = urlParams.get('profile');
        
        if (profileId) {
            fetchProfile(parseInt(profileId, 10));
        } else if (initialProfileId) {
            fetchProfile(initialProfileId);
        }
    }, [fetchProfiles, fetchProfile, initialProfileId]);

    // Get current view based on state
    const getCurrentView = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const view = urlParams.get('view');
        
        if (view === 'stats') {
            return <Stats />;
        }
        
        if (currentProfile) {
            return <Editor />;
        }
        
        return <Dashboard />;
    };

    return (
        <div className="rt-linky-admin-app">
            {getCurrentView()}
        </div>
    );
};

export default App;
