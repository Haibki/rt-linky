/**
 * Admin Entry Point
 */

import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './components/App';

// Initialize React app
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('rt-linky-admin-root');
    
    if (container) {
        const root = createRoot(container);
        root.render(<App />);
    }
});
