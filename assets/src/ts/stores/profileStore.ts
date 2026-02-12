/**
 * Profile Store using Zustand
 */

import { create } from 'zustand';
import { Profile, ProfileStats, GlobalStats, Design } from '../types';

interface ProfileState {
    profiles: Profile[];
    currentProfile: Profile | null;
    isLoading: boolean;
    error: string | null;
    
    // Actions
    setProfiles: (profiles: Profile[]) => void;
    setCurrentProfile: (profile: Profile | null) => void;
    setLoading: (loading: boolean) => void;
    setError: (error: string | null) => void;
    
    // Profile operations
    createProfile: (data: Partial<Profile>) => Promise<Profile | null>;
    updateProfile: (id: number, data: Partial<Profile>) => Promise<Profile | null>;
    deleteProfile: (id: number) => Promise<boolean>;
    fetchProfiles: () => Promise<void>;
    fetchProfile: (id: number) => Promise<void>;
    
    // Stats
    profileStats: ProfileStats | null;
    globalStats: GlobalStats | null;
    fetchProfileStats: (id: number, period?: number) => Promise<void>;
    fetchGlobalStats: (period?: number) => Promise<void>;
}

const API_URL = (window as any).rtLinkyData?.restUrl || '/wp-json/rt-linky/v1/';
const NONCE = (window as any).rtLinkyData?.restNonce || '';

export const useProfileStore = create<ProfileState>((set, get) => ({
    profiles: [],
    currentProfile: null,
    isLoading: false,
    error: null,
    profileStats: null,
    globalStats: null,

    setProfiles: (profiles) => set({ profiles }),
    setCurrentProfile: (profile) => set({ currentProfile: profile }),
    setLoading: (loading) => set({ isLoading: loading }),
    setError: (error) => set({ error }),

    createProfile: async (data) => {
        set({ isLoading: true, error: null });
        
        try {
            const response = await fetch(`${API_URL}profiles`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': NONCE,
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) throw new Error('Failed to create profile');
            
            const profile = await response.json();
            set(state => ({ 
                profiles: [...state.profiles, profile],
                isLoading: false 
            }));
            return profile;
        } catch (err) {
            set({ error: (err as Error).message, isLoading: false });
            return null;
        }
    },

    updateProfile: async (id, data) => {
        set({ isLoading: true, error: null });
        
        try {
            const response = await fetch(`${API_URL}profiles/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': NONCE,
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) throw new Error('Failed to update profile');
            
            const profile = await response.json();
            set(state => ({
                profiles: state.profiles.map(p => p.id === id ? profile : p),
                currentProfile: state.currentProfile?.id === id ? profile : state.currentProfile,
                isLoading: false,
            }));
            return profile;
        } catch (err) {
            set({ error: (err as Error).message, isLoading: false });
            return null;
        }
    },

    deleteProfile: async (id) => {
        set({ isLoading: true, error: null });
        
        try {
            const response = await fetch(`${API_URL}profiles/${id}`, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': NONCE },
            });

            if (!response.ok) throw new Error('Failed to delete profile');
            
            set(state => ({
                profiles: state.profiles.filter(p => p.id !== id),
                currentProfile: state.currentProfile?.id === id ? null : state.currentProfile,
                isLoading: false,
            }));
            return true;
        } catch (err) {
            set({ error: (err as Error).message, isLoading: false });
            return false;
        }
    },

    fetchProfiles: async () => {
        set({ isLoading: true, error: null });
        
        try {
            const response = await fetch(`${API_URL}profiles`, {
                headers: { 'X-WP-Nonce': NONCE },
            });

            if (!response.ok) throw new Error('Failed to fetch profiles');
            
            const profiles = await response.json();
            set({ profiles, isLoading: false });
        } catch (err) {
            set({ error: (err as Error).message, isLoading: false });
        }
    },

    fetchProfile: async (id) => {
        set({ isLoading: true, error: null });
        
        try {
            const response = await fetch(`${API_URL}profiles/${id}`, {
                headers: { 'X-WP-Nonce': NONCE },
            });

            if (!response.ok) throw new Error('Failed to fetch profile');
            
            const profile = await response.json();
            set({ currentProfile: profile, isLoading: false });
        } catch (err) {
            set({ error: (err as Error).message, isLoading: false });
        }
    },

    fetchProfileStats: async (id, period = 30) => {
        try {
            const response = await fetch(`${API_URL}profiles/${id}/stats?period=${period}`, {
                headers: { 'X-WP-Nonce': NONCE },
            });

            if (!response.ok) throw new Error('Failed to fetch stats');
            
            const stats = await response.json();
            set({ profileStats: stats });
        } catch (err) {
            console.error('Failed to fetch profile stats:', err);
        }
    },

    fetchGlobalStats: async (period = 30) => {
        try {
            const response = await fetch(`${API_URL}stats?period=${period}`, {
                headers: { 'X-WP-Nonce': NONCE },
            });

            if (!response.ok) throw new Error('Failed to fetch global stats');
            
            const stats = await response.json();
            set({ globalStats: stats });
        } catch (err) {
            console.error('Failed to fetch global stats:', err);
        }
    },
}));
