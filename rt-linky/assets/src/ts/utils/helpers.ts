/**
 * Helper utilities
 */

import { Design, Profile } from '../types';

/**
 * Generate slug from title
 */
export function generateSlug(title: string): string {
    return title
        .toLowerCase()
        .replace(/ä/g, 'ae')
        .replace(/ö/g, 'oe')
        .replace(/ü/g, 'ue')
        .replace(/ß/g, 'ss')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

/**
 * Generate unique ID
 */
export function generateId(): string {
    return 'id_' + Math.random().toString(36).substring(2, 11);
}

/**
 * Get CSS for design
 */
export function getDesignCSS(design: Design): string {
    const bg = design.bg_type === 'gradient'
        ? `linear-gradient(135deg, ${design.color1}, ${design.color2})`
        : design.bg_type === 'image'
        ? `url('${design.bg_image}')`
        : design.color1;

    return `
        background: ${bg};
        color: ${design.text_color};
    `;
}

/**
 * Generate preview HTML for iframe
 */
export function generatePreviewHTML(profile: Profile): string {
    const design = profile.design || {};
    const bg = design.bg_type === 'gradient'
        ? `linear-gradient(135deg, ${design.color1}, ${design.color2})`
        : design.bg_type === 'image'
        ? `url('${design.bg_image}')`
        : design.color1;

    return `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${escapeHtml(profile.title)}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: ${bg};
            color: ${design.text_color || '#ffffff'};
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container { width: 100%; max-width: 400px; text-align: center; }
        .avatar { 
            width: 120px; height: 120px; border-radius: 50%; 
            margin: 0 auto 20px; border: 4px solid rgba(255,255,255,0.3);
            overflow: hidden;
        }
        .avatar img { width: 100%; height: 100%; object-fit: cover; }
        h1 { font-size: 28px; margin-bottom: 10px; }
        .bio { font-size: 16px; opacity: 0.9; margin-bottom: 30px; }
        .links { display: flex; flex-direction: column; gap: 15px; }
        .link {
            display: flex; align-items: center; gap: 15px;
            background: ${design.button_color || '#ffffff'};
            color: #333; text-decoration: none;
            padding: 18px 25px;
            border-radius: ${design.button_radius || 12}px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        ${profile.avatar_url ? `<div class="avatar"><img src="${profile.avatar_url}"></div>` : ''}
        <h1>${escapeHtml(profile.title)}</h1>
        ${profile.bio ? `<p class="bio">${escapeHtml(profile.bio)}</p>` : ''}
        <div class="links">
            ${profile.links?.map(link => `
                <a href="${link.url}" class="link" target="_blank">
                    ${link.icon ? `<span>${link.icon}</span>` : ''}
                    <span>${escapeHtml(link.title)}</span>
                </a>
            `).join('') || '<p style="opacity:0.7">No links yet</p>'}
        </div>
    </div>
</body>
</html>`;
}

/**
 * Escape HTML entities
 */
export function escapeHtml(text: string): string {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Format number with locale
 */
export function formatNumber(num: number): string {
    return new Intl.NumberFormat().format(num);
}

/**
 * Format date
 */
export function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString();
}

/**
 * Copy to clipboard
 */
export async function copyToClipboard(text: string): Promise<boolean> {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch {
        return false;
    }
}

/**
 * Download JSON file
 */
export function downloadJSON(data: object, filename: string): void {
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
