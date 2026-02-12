/**
 * UI Store using Zustand
 */

import { create } from 'zustand';
import { ViewMode, EditorTab, StatsPeriod } from '../types';

interface UIState {
    // Dashboard
    viewMode: ViewMode;
    searchQuery: string;
    setViewMode: (mode: ViewMode) => void;
    setSearchQuery: (query: string) => void;
    
    // Editor
    activeTab: EditorTab;
    isPreviewMobile: boolean;
    setActiveTab: (tab: EditorTab) => void;
    setPreviewMobile: (mobile: boolean) => void;
    
    // Stats
    statsPeriod: StatsPeriod;
    setStatsPeriod: (period: StatsPeriod) => void;
    
    // Modals
    showIconModal: boolean;
    showColorModal: boolean;
    activeLinkId: string | null;
    setShowIconModal: (show: boolean) => void;
    setShowColorModal: (show: boolean) => void;
    setActiveLinkId: (id: string | null) => void;
}

export const useUIStore = create<UIState>((set) => ({
    viewMode: 'grid',
    searchQuery: '',
    setViewMode: (mode) => set({ viewMode: mode }),
    setSearchQuery: (query) => set({ searchQuery: query }),
    
    activeTab: 'editor',
    isPreviewMobile: true,
    setActiveTab: (tab) => set({ activeTab: tab }),
    setPreviewMobile: (mobile) => set({ isPreviewMobile: mobile }),
    
    statsPeriod: 30,
    setStatsPeriod: (period) => set({ statsPeriod: period }),
    
    showIconModal: false,
    showColorModal: false,
    activeLinkId: null,
    setShowIconModal: (show) => set({ showIconModal: show }),
    setShowColorModal: (show) => set({ showColorModal: show }),
    setActiveLinkId: (id) => set({ activeLinkId: id }),
}));
