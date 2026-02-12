/**
 * Frontend JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initialize click tracking
    initClickTracking();
});

function initClickTracking(): void {
    const links = document.querySelectorAll<HTMLAnchorElement>('.rt-linky-link, .rt-linky-track');
    
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            const url = link.href;
            const label = link.getAttribute('data-link-label') || link.textContent?.trim() || '';
            const postId = link.closest('[data-post-id]')?.getAttribute('data-post-id') || 
                          document.querySelector('.rt-linky-container')?.getAttribute('data-post-id');
            
            if (!postId) return;
            
            // Send tracking request
            const ajaxUrl = (window as any).rtLinkyFrontend?.ajaxUrl || '/wp-admin/admin-ajax.php';
            const nonce = (window as any).rtLinkyFrontend?.trackNonce || '';
            
            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'rt_linky_track_click',
                    post_id: postId,
                    url: url,
                    label: label,
                    nonce: nonce,
                }),
            }).catch(() => {
                // Silent fail
            });
        });
    });
}
