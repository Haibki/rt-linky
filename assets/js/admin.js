    function initEditor() {
        var isUpdatingPreview = false;
        var slugManuallyEdited = false; // NEU: Merkt ob Slug manuell bearbeitet wurde

        $('.tab-btn').on('click', function() {
            var tab = $(this).data('tab');
            $('.tab-btn').removeClass('active');
            $(this).addClass('active');
            $('.tab-panel').removeClass('active');
            $('#tab-' + tab).addClass('active');
        });

        $('#design-bg-type').on('change', function() {
            var type = $(this).val();
            
            if (type === 'gradient') {
                $('#gradient-colors').show();
                $('#solid-color').hide();
                $('#image-upload-group').hide();
            } else if (type === 'solid') {
                $('#gradient-colors').hide();
                $('#solid-color').show();
                $('#image-upload-group').hide();
            } else if (type === 'image') {
                $('#gradient-colors').hide();
                $('#solid-color').hide();
                $('#image-upload-group').show();
            }
            updatePreview();
        });

        $('#design-radius').on('input', function() {
            $('#radius-value').text($(this).val());
            updatePreview();
        });

        // TITEL ÄNDERUNG - nur Slug aktualisieren wenn NICHT manuell bearbeitet
        $('#profile-title').on('input', function() {
            var title = $(this).val();
            updatePreview();
            
            // Nur auto-generieren wenn Slug noch nie manuell bearbeitet wurde UND leer ist
            if (!slugManuallyEdited) {
                var currentSlug = $('#profile-slug').val().trim();
                if (!currentSlug) {
                    var slug = generateSlug(title);
                    $('#profile-slug').val(slug);
                    $('#slug-preview').text(slug);
                }
            }
        });

        // SLUG MANUELL BEARBEITET - Flag setzen
        $('#profile-slug').on('input', function() {
            slugManuallyEdited = true; // WICHTIG: Ab jetzt nicht mehr auto-ändern
            $('#slug-preview').text($(this).val() || 'dein-slug');
        });

        // Rest der Funktion bleibt gleich...
        $('#upload-avatar').on('click', function(e) {
            e.preventDefault();
            
            var mediaUploader = wp.media({
                title: 'Profilbild auswählen',
                button: { text: 'Dieses Bild verwenden' },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#avatar-url').val(attachment.url);
                $('#avatar-preview').html('<img src="' + attachment.url + '" alt="">');
                $('#remove-avatar').show();
                updatePreview();
            });

            mediaUploader.open();
        });

        $('#remove-avatar').on('click', function() {
            $('#avatar-url').val('');
            $('#avatar-preview').html('<span class="avatar-placeholder">👤</span>');
            $(this).hide();
            updatePreview();
        });

        $('#upload-bg-image').on('click', function(e) {
            e.preventDefault();
            
            var mediaUploader = wp.media({
                title: 'Hintergrundbild auswählen',
                button: { text: 'Dieses Bild verwenden' },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#bg-image-url').val(attachment.url);
                $('#bg-image-preview').html('<img src="' + attachment.url + '" alt="">');
                $('#remove-bg-image').show();
                updatePreview();
            });

            mediaUploader.open();
        });

        $('#remove-bg-image').on('click', function() {
            $('#bg-image-url').val('');
            $('#bg-image-preview').html('<span class="bg-image-placeholder">🖼️</span>');
            $(this).hide();
            updatePreview();
        });

        $('#add-link').on('click', function() {
            var id = 'link_' + Date.now();
            var iconOptions = '';
            
            for (var key in availableIcons) {
                iconOptions += '<option value="' + key + '">' + availableIcons[key] + ' ' + key + '</option>';
            }
            
            var template = 
                '<div class="link-item" data-id="' + id + '">' +
                    '<div class="link-handle">⋮⋮</div>' +
                    '<div class="link-fields">' +
                        '<div class="link-row">' +
                            '<select class="link-icon-select">' + iconOptions + '</select>' +
                            '<input type="text" class="link-title" placeholder="Link Titel">' +
                        '</div>' +
                        '<input type="url" class="link-url" placeholder="https://...">' +
                    '</div>' +
                    '<button type="button" class="button-link delete-link" title="Löschen">×</button>' +
                '</div>';
            
            $('#links-list').append(template);
            $('#links-empty').hide();
            updateLinkCount();
            updatePreview();
        });

        $(document).on('click', '.delete-link', function() {
            $(this).closest('.link-item').remove();
            if ($('.link-item').length === 0) {
                $('#links-empty').show();
            }
            updateLinkCount();
            updatePreview();
        });

        $(document).on('input', '.link-title, .link-url', function() {
            updatePreview();
        });
        
        $(document).on('change', '.link-icon-select', function() {
            updatePreview();
        });

        $('#design-color1, #design-color2, #design-solid, #design-text, #design-button').on('input', function() {
            updatePreview();
        });
        
        $('#profile-bio').on('input', function() {
            updatePreview();
        });
        
        $('#profile-verified').on('change', function() {
            updatePreview();
        });

        $('.device-btn').on('click', function() {
            var device = $(this).data('device');
            
            $('.device-btn').removeClass('active');
            $(this).addClass('active');
            
            var $frame = $('#preview-frame');
            
            if (device === 'mobile') {
                $frame.removeClass('desktop').addClass('mobile');
                $frame.css({
                    'width': '375px',
                    'max-width': '375px'
                });
            } else {
                $frame.removeClass('mobile').addClass('desktop');
                $frame.css({
                    'width': '100%',
                    'max-width': '100%'
                });
            }
            
            setTimeout(updatePreview, 50);
        });

        $('#save-profile').on('click', function() {
            var $btn = $(this);
            var originalText = $btn.text();
            
            var title = $('#profile-title').val().trim();
            if (!title) {
                alert('Bitte gib einen Profil-Titel ein');
                $('#profile-title').focus();
                return;
            }

            $btn.prop('disabled', true).text('Speichern...');

            var links = [];
            $('.link-item').each(function() {
                var $item = $(this);
                var iconKey = $item.find('.link-icon-select').val() || 'link';
                var linkTitle = $item.find('.link-title').val().trim();
                var linkUrl = $item.find('.link-url').val().trim();
                
                if (linkTitle || linkUrl) {
                    links.push({
                        id: $item.data('id'),
                        title: linkTitle,
                        url: linkUrl,
                        icon: availableIcons[iconKey] || '🔗'
                    });
                }
            });

            var nonce = '';
            if (typeof rtLinkyData !== 'undefined' && rtLinkyData.nonce) {
                nonce = rtLinkyData.nonce;
            } else {
                alert('Fehler: Sicherheitsdaten nicht geladen. Bitte Seite neu laden.');
                $btn.prop('disabled', false).text(originalText);
                return;
            }

            var ajaxUrl = (typeof rtLinkyData !== 'undefined' && rtLinkyData.ajaxUrl) ? rtLinkyData.ajaxUrl : '/wp-admin/admin-ajax.php';

            var data = {
                action: 'rt_linky_save_profile',
                nonce: nonce,
                profile_id: $('#profile-id').val(),
                title: title,
                slug: $('#profile-slug').val() || generateSlug(title),
                bio: $('#profile-bio').val(),
                avatar_url: $('#avatar-url').val(),
                verified: $('#profile-verified').is(':checked') ? 1 : 0,
                design: {
                    bg_type: $('#design-bg-type').val(),
                    color1: $('#design-color1').val(),
                    color2: $('#design-color2').val(),
                    bg_image: $('#bg-image-url').val(),
                    text_color: $('#design-text').val(),
                    button_color: $('#design-button').val(),
                    button_radius: $('#design-radius').val()
                },
                links: links
            };

            if (data.design.bg_type === 'solid') {
                data.design.color1 = $('#design-solid').val();
                data.design.color2 = $('#design-solid').val();
            }

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $btn.text('Gespeichert!');
                        
                        if (!$('#profile-id').val() && response.data && response.data.id) {
                            $('#profile-id').val(response.data.id);
                        }
                        
                        setTimeout(function() {
                            $btn.prop('disabled', false).text(originalText);
                        }, 1500);
                    } else {
                        var errorMsg = response.data || 'Speichern fehlgeschlagen';
                        alert('Fehler: ' + errorMsg);
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Save error:', status, error);
                    alert('Fehler beim Speichern.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        });

        setTimeout(function() {
            updatePreview();
        }, 100);

        function updateLinkCount() {
            var count = $('.link-item').length;
            $('.tab-btn[data-tab="links"]').text('Links (' + count + ')');
        }

        function updatePreview() {
            if (isUpdatingPreview) return;
            isUpdatingPreview = true;

            var title = $('#profile-title').val() || 'Dein Name';
            var bio = $('#profile-bio').val() || '';
            var avatar = $('#avatar-url').val();
            var verified = $('#profile-verified').is(':checked');
            
            var bgType = $('#design-bg-type').val();
            var color1 = bgType === 'solid' ? $('#design-solid').val() : $('#design-color1').val();
            var color2 = $('#design-color2').val();
            var bgImage = $('#bg-image-url').val();
            var textColor = $('#design-text').val();
            var buttonColor = $('#design-button').val();
            var radius = $('#design-radius').val();

            var $frame = $('#preview-frame');
            var isDesktop = $frame.hasClass('desktop');

            var bg;
            if (bgType === 'gradient') {
                bg = 'linear-gradient(135deg, ' + color1 + ', ' + color2 + ')';
            } else if (bgType === 'solid') {
                bg = color1;
            } else if (bgType === 'image') {
                bg = bgImage ? 'url(' + bgImage + ') center/cover no-repeat' : '#1f2937';
            }

            var linksHtml = '';
            $('.link-item').each(function(index) {
                var $item = $(this);
                var linkTitle = $item.find('.link-title').val() || 'Link ' + (index + 1);
                var linkUrl = $item.find('.link-url').val() || '#';
                var iconKey = $item.find('.link-icon-select').val() || 'link';
                var icon = availableIcons[iconKey] || '🔗';
                
                linksHtml += 
                    '<a href="' + linkUrl + '" target="_blank" class="preview-link" style="' +
                        'display: flex;' +
                        'align-items: center;' +
                        'gap: 15px;' +
                        'background: ' + buttonColor + ';' +
                        'color: #1f2937;' +
                        'text-decoration: none;' +
                        'padding: ' + (isDesktop ? '18px 28px' : '16px 22px') + ';' +
                        'border-radius: ' + radius + 'px;' +
                        'font-weight: 600;' +
                        'font-size: ' + (isDesktop ? '17px' : '16px') + ';' +
                        'transition: all 0.3s ease;' +
                        'box-shadow: 0 4px 15px rgba(0,0,0,0.1);' +
                        'margin-bottom: 12px;' +
                    '" onmouseover="this.style.transform=\'translateY(-2px)\';this.style.boxShadow=\'0 8px 25px rgba(0,0,0,0.15)\'" ' +
                    'onmouseout="this.style.transform=\'translateY(0)\';this.style.boxShadow=\'0 4px 15px rgba(0,0,0,0.1)\'">' +
                        '<span style="font-size: 22px;">' + icon + '</span>' +
                        '<span style="flex: 1; text-align: left;">' + linkTitle + '</span>' +
                        '<span style="opacity: 0.6;">→</span>' +
                    '</a>';
            });

            if (!linksHtml) {
                linksHtml = '<p style="opacity: 0.7; font-style: italic; padding: 40px 20px; color: ' + textColor + ';">Noch keine Links vorhanden.</p>';
            }

            var avatarSize = isDesktop ? '120px' : '100px';
            var avatarHtml;
            if (avatar) {
                avatarHtml = '<div style="width: ' + avatarSize + '; height: ' + avatarSize + '; border-radius: 50%; margin: 0 auto 20px; border: 4px solid rgba(255,255,255,0.3); overflow: hidden;">' +
                    '<img src="' + avatar + '" style="width: 100%; height: 100%; object-fit: cover;">' +
                '</div>';
            } else {
                avatarHtml = '<div style="width: ' + avatarSize + '; height: ' + avatarSize + '; border-radius: 50%; margin: 0 auto 20px; border: 4px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 50px;">👤</div>';
            }

            var verifiedHtml = verified ? '<span style="color: #10b981; margin-left: 8px;">✓</span>' : '';
            var titleSize = isDesktop ? '30px' : '26px';

            var html = '<!DOCTYPE html>' +
            '<html>' +
            '<head>' +
                '<meta charset="UTF-8">' +
                '<meta name="viewport" content="width=device-width, initial-scale=1.0">' +
                '<title>' + title + '</title>' +
                '<style>' +
                    '* { margin: 0; padding: 0; box-sizing: border-box; }' +
                    'body {' +
                        'font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;' +
                        'background: ' + bg + ';' +
                        'color: ' + textColor + ';' +
                        'min-height: 100vh;' +
                        'padding: 40px 20px;' +
                        'line-height: 1.6;' +
                    '}' +
                    '.container {' +
                        'max-width: 500px;' +
                        'margin: 0 auto;' +
                        'text-align: center;' +
                    '}' +
                    'h1 {' +
                        'font-size: ' + titleSize + ';' +
                        'margin-bottom: 10px;' +
                        'display: flex;' +
                        'align-items: center;' +
                        'justify-content: center;' +
                        'flex-wrap: wrap;' +
                    '}' +
                    '.bio {' +
                        'font-size: 15px;' +
                        'opacity: 0.9;' +
                        'margin-bottom: 25px;' +
                    '}' +
                    '.links {' +
                        'display: flex;' +
                        'flex-direction: column;' +
                    '}' +
                '</style>' +
            '</head>' +
            '<body>' +
                '<div class="container">' +
                    avatarHtml +
                    '<h1>' + title + verifiedHtml + '</h1>' +
                    (bio ? '<p class="bio">' + bio + '</p>' : '') +
                    '<div class="links">' + linksHtml + '</div>' +
                '</div>' +
            '</body>' +
            '</html>';

            var iframe = document.getElementById('live-preview');
            if (iframe) {
                iframe.srcdoc = html;
            }

            isUpdatingPreview = false;
        }

        function generateSlug(text) {
            if (!text) return '';
            return text
                .toLowerCase()
                .replace(/[äÄ]/g, 'ae')
                .replace(/[öÖ]/g, 'oe')
                .replace(/[üÜ]/g, 'ue')
                .replace(/ß/g, 'ss')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
        }
    }