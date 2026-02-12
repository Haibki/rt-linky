/**
 * Type definitions for Linky Generator
 */

export interface Link {
    id: string;
    title: string;
    url: string;
    icon?: string;
}

export interface Design {
    bg_type: 'gradient' | 'solid' | 'image';
    color1: string;
    color2: string;
    text_color: string;
    button_color: string;
    button_radius: number;
    bg_image?: string;
}

export interface Profile {
    id: number;
    title: string;
    slug: string;
    bio: string;
    avatar_url: string;
    verified: boolean;
    design: Design;
    links: Link[];
    views: number;
    clicks: number;
    created_at: string;
    updated_at: string;
    edit_link: string;
    view_link: string;
}

export interface ProfileStats {
    views: number;
    clicks: number;
    period: Array<{ date: string; count: number }>;
    top_links: Array<{ link_label: string; link_url: string; clicks: number }>;
    devices: Array<{ device: string; count: number }>;
    browsers: Array<{ browser: string; count: number }>;
}

export interface GlobalStats {
    total_profiles: number;
    total_views: number;
    unique_visitors: number;
}

export interface Icon {
    name: string;
    svg: string;
}

export type ViewMode = 'grid' | 'list';
export type EditorTab = 'editor' | 'design' | 'links' | 'settings';
export type StatsPeriod = 7 | 30 | 90 | 365;
