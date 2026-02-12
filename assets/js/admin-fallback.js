/**
 * RT-Linky Admin Fallback (wenn kein Build vorhanden)
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Prüfe ob React-App existiert
        if (document.getElementById('rt-linky-admin-root')) {
            // Wenn React nicht geladen ist, zeige Fallback
            if (typeof React === 'undefined') {
                showFallbackUI();
            }
        }
    });

    function showFallbackUI() {
        var $container = $('#rt-linky-admin-root');
        var data = window.rtLinkyData || {};
        
        $container.html(
            '<div class="rt-linky-fallback">' +
                '<div class="rt-linky-fallback-header">' +
                    '<h1>RT-Linky</h1>' +
                    '<p>Bitte führen Sie <code>npm run build</code> aus, um die moderne Oberfläche zu nutzen.</p>' +
                '</div>' +
                '<div class="rt-linky-fallback-content">' +
                    '<h2>Profile verwalten</h2>' +
                    '<p><a href="' + data.adminUrl + 'edit.php?post_type=rt_linky_profile" class="button button-primary">Profile anzeigen</a></p>' +
                    '<p><a href="' + data.adminUrl + 'post-new.php?post_type=rt_linky_profile" class="button">Neues Profil erstellen</a></p>' +
                '</div>' +
            '</div>'
        );
    }

})(jQuery);
