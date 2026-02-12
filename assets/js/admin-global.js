jQuery(document).ready(function($) {
    
    // Lizenz aktivieren
    $('#rt-linky-activate-license').on('click', function() {
        const $button = $(this);
        const key = $('#rt-linky-license-key').val().trim();
        
        if (!key) {
            alert('Bitte Lizenz-Key eingeben');
            return;
        }
        
        $button.prop('disabled', true).text('Aktiviere...');
        
        $.ajax({
            url: rtLinkyAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'rt_linky_save_license',
                nonce: rtLinkyAdmin.nonce,
                license_key: key
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ Lizenz erfolgreich aktiviert!');
                    location.reload();
                } else {
                    alert('❌ ' + response.data);
                    $button.prop('disabled', false).text('Aktivieren');
                }
            },
            error: function() {
                alert('❌ Fehler bei der Aktivierung');
                $button.prop('disabled', false).text('Aktivieren');
            }
        });
    });
    
    // Lizenz entfernen
    $('#rt-linky-remove-license').on('click', function() {
        if (!confirm(rtLinkyAdmin.strings.confirmRemove)) {
            return;
        }
        
        const $button = $(this);
        $button.prop('disabled', true).text('Entferne...');
        
        $.ajax({
            url: rtLinkyAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'rt_linky_remove_license',
                nonce: rtLinkyAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Lizenz entfernt');
                    location.reload();
                } else {
                    alert('Fehler: ' + response.data);
                    $button.prop('disabled', false).text('Lizenz entfernen');
                }
            }
        });
    });
    
    // Einstellungen speichern
    $('#rt-linky-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $spinner = $form.find('.spinner');
        const $button = $form.find('button[type="submit"]');
        
        $spinner.addClass('is-active');
        $button.prop('disabled', true);
        
        $.ajax({
            url: rtLinkyAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'rt_linky_save_settings',
                nonce: rtLinkyAdmin.nonce,
                show_footer: $form.find('[name="show_footer"]').is(':checked'),
                footer_text: $form.find('[name="footer_text"]').val(),
                enable_subtitles: $form.find('[name="enable_subtitles"]').is(':checked'),
                default_subtitle: $form.find('[name="default_subtitle"]').val(),
                enable_verified_badge: $form.find('[name="enable_verified_badge"]').is(':checked'),
                analytics_enabled: $form.find('[name="analytics_enabled"]').is(':checked')
            },
            success: function(response) {
                $spinner.removeClass('is-active');
                $button.prop('disabled', false);
                
                if (response.success) {
                    showNotice(rtLinkyAdmin.strings.saveSuccess, 'success');
                } else {
                    showNotice(response.data || rtLinkyAdmin.strings.saveError, 'error');
                }
            },
            error: function() {
                $spinner.removeClass('is-active');
                $button.prop('disabled', false);
                showNotice(rtLinkyAdmin.strings.saveError, 'error');
            }
        });
    });
    
    function showNotice(message, type) {
        const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.rt-linky-settings-page h1').after($notice);
        
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
});