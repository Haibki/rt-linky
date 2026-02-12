<?php
/**
 * License View
 * 
 * @package RTLinky
 */

if (!defined('ABSPATH')) {
    exit;
}

$license = get_option('rt_linky_license', array(
    'key' => '',
    'status' => 'inactive',
    'activated_at' => '',
    'expires_at' => '',
    'customer_name' => '',
    'customer_email' => ''
));

$is_active = $license['status'] === 'active';

// Generate fresh nonce for this page load
$ajax_nonce = wp_create_nonce('rt_linky_nonce');
?>
<div class="wrap rt-linky-wrap rt-linky-license-page">
    <div class="rt-linky-header">
        <div class="rt-linky-brand">
            <a href="<?php echo admin_url('admin.php?page=rt-linky'); ?>" class="back-link">← Zurück</a>
            <h1>Lizenz-Verwaltung</h1>
        </div>
    </div>

    <div class="rt-linky-license-content">
        
        <!-- License Status Card -->
        <div class="license-status-card <?php echo $is_active ? 'active' : 'inactive'; ?>">
            <div class="status-icon">
                <?php if ($is_active): ?>
                    ✅
                <?php else: ?>
                    🔒
                <?php endif; ?>
            </div>
            <div class="status-text">
                <h2>Lizenz Status: <span class="status-badge <?php echo $is_active ? 'active' : 'inactive'; ?>"><?php echo $is_active ? 'AKTIV' : 'INAKTIV'; ?></span></h2>
                <?php if ($is_active): ?>
                    <p>Deine Lizenz ist gültig und alle Funktionen sind freigeschaltet.</p>
                <?php else: ?>
                    <p>Bitte aktiviere deine Lizenz um alle Funktionen zu nutzen.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($is_active): ?>
            <!-- License Info (Active) -->
            <div class="license-info-grid">
                <div class="info-card">
                    <div class="info-label">Lizenzschlüssel</div>
                    <div class="info-value">
                        <?php 
                        $key = $license['key'];
                        if (strlen($key) > 12) {
                            echo substr($key, 0, 8) . '-****-****-' . substr($key, -4);
                        } else {
                            echo $key;
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Version</div>
                    <div class="info-value"><?php echo RT_LINKY_VERSION; ?></div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Aktiviert am</div>
                    <div class="info-value">
                        <?php 
                        echo $license['activated_at'] 
                            ? date_i18n('d.m.Y H:i', strtotime($license['activated_at'])) 
                            : '-'; 
                        ?>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Gültig bis</div>
                    <div class="info-value">
                        <?php 
                        echo $license['expires_at'] 
                            ? date_i18n('d.m.Y', strtotime($license['expires_at'])) 
                            : 'Unbegrenzt'; 
                        ?>
                    </div>
                </div>

                <?php if (!empty($license['customer_name'])): ?>
                <div class="info-card">
                    <div class="info-label">Kunde</div>
                    <div class="info-value"><?php echo esc_html($license['customer_name']); ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($license['customer_email'])): ?>
                <div class="info-card">
                    <div class="info-label">E-Mail</div>
                    <div class="info-value"><?php echo esc_html($license['customer_email']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($license['type'])): ?>
                <div class="info-card">
                    <div class="info-label">Lizenztyp</div>
                    <div class="info-value"><?php echo esc_html(ucfirst($license['type'])); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="license-actions">
                <button type="button" id="deactivate-license" class="button button-secondary">
                    Lizenz deaktivieren
                </button>
            </div>

        <?php else: ?>
            <!-- License Activation Form -->
            <div class="license-activation-form">
                <h3>Lizenz aktivieren</h3>
                <p>Gib deinen Lizenzschlüssel ein um das Plugin zu aktivieren.</p>
                
                <div class="form-group">
                    <label for="license-key">Lizenzschlüssel *</label>
                    <input type="text" id="license-key" class="large-text" placeholder="RT-L-..." autocomplete="off">
                    <p class="help-text">Füge hier deinen Lizenzschlüssel ein</p>
                </div>

                <div class="form-group">
                    <label for="customer-name">Name (optional)</label>
                    <input type="text" id="customer-name" class="regular-text" placeholder="Max Mustermann">
                </div>

                <div class="form-group">
                    <label for="customer-email">E-Mail (optional)</label>
                    <input type="email" id="customer-email" class="regular-text" placeholder="max@beispiel.de">
                </div>

                <button type="button" id="activate-license" class="button button-primary button-lg">
                    Lizenz aktivieren
                </button>
                
                <div id="license-error" style="color: #d63638; margin-top: 10px; display: none;"></div>
                <div id="license-success" style="color: #00a32a; margin-top: 10px; display: none;"></div>
                
                <!-- Debug info (hidden) -->
                <div id="license-debug" style="margin-top: 20px; padding: 10px; background: #f0f0f0; font-size: 12px; display: none;">
                    <strong>Debug:</strong>
                    <pre></pre>
                </div>
            </div>

            <!-- Help Section -->
            <div class="license-help">
                <h4>❓ Du hast noch keine Lizenz?</h4>
                <p>Besuche <a href="https://rettoro.de" target="_blank">rettoro.de</a> um eine Lizenz zu erwerben.</p>
                <p class="help-text">Nach dem Kauf erhältst du deinen persönlichen Lizenzschlüssel per E-Mail.</p>
            </div>
        <?php endif; ?>

        <!-- Features List -->
        <div class="license-features">
            <h3>Funktionen</h3>
            <ul>
                <li class="<?php echo $is_active ? 'available' : 'locked'; ?>">
                    <span class="feature-icon"><?php echo $is_active ? '✅' : '🔒'; ?></span>
                    <span class="feature-text">Unbegrenzte Profile erstellen</span>
                </li>
                <li class="<?php echo $is_active ? 'available' : 'locked'; ?>">
                    <span class="feature-icon"><?php echo $is_active ? '✅' : '🔒'; ?></span>
                    <span class="feature-text">Eigene Hintergrundbilder</span>
                </li>
                <li class="<?php echo $is_active ? 'available' : 'locked'; ?>">
                    <span class="feature-icon"><?php echo $is_active ? '✅' : '🔒'; ?></span>
                    <span class="feature-text">Verifiziert-Badge</span>
                </li>
                <li class="<?php echo $is_active ? 'available' : 'locked'; ?>">
                    <span class="feature-icon"><?php echo $is_active ? '✅' : '🔒'; ?></span>
                    <span class="feature-text">Detaillierte Statistiken</span>
                </li>
                <li class="<?php echo $is_active ? 'available' : 'locked'; ?>">
                    <span class="feature-icon"><?php echo $is_active ? '✅' : '🔒'; ?></span>
                    <span class="feature-text">Link-Icons</span>
                </li>
                <li class="<?php echo $is_active ? 'available' : 'locked'; ?>">
                    <span class="feature-icon"><?php echo $is_active ? '✅' : '🔒'; ?></span>
                    <span class="feature-text">Premium Support</span>
                </li>
            </ul>
        </div>

    </div>
</div>

<script>
// Inline nonce for this page - ensures fresh nonce
var rtLinkyLicenseNonce = '<?php echo esc_js($ajax_nonce); ?>';

jQuery(document).ready(function($) {
    
    // Activate License
    $('#activate-license').on('click', function() {
        var $btn = $(this);
        var $error = $('#license-error');
        var $success = $('#license-success');
        var $debug = $('#license-debug pre');
        var key = $('#license-key').val().trim();
        var name = $('#customer-name').val().trim();
        var email = $('#customer-email').val().trim();
        
        // Hide previous messages
        $error.hide();
        $success.hide();
        
        if (!key) {
            $error.text('Bitte gib einen Lizenzschlüssel ein').show();
            return;
        }
        
        $btn.prop('disabled', true).text('Aktiviere...');
        
        // Use inline nonce if rtLinkyData is not available or nonce is undefined
        var nonce = (typeof rtLinkyData !== 'undefined' && rtLinkyData.nonce) ? rtLinkyData.nonce : rtLinkyLicenseNonce;
        var ajaxUrl = (typeof rtLinkyData !== 'undefined' && rtLinkyData.ajaxUrl) ? rtLinkyData.ajaxUrl : '<?php echo admin_url('admin-ajax.php'); ?>';
        
        console.log('=== RT-Linky License Activation ===');
        console.log('License Key:', key);
        console.log('Nonce Source:', (typeof rtLinkyData !== 'undefined' && rtLinkyData.nonce) ? 'rtLinkyData' : 'inline');
        console.log('Nonce:', nonce);
        console.log('AJAX URL:', ajaxUrl);
        
        var requestData = {
            action: 'rt_linky_activate_license',
            nonce: nonce,
            license_key: key,
            customer_name: name,
            customer_email: email
        };
        
        console.log('Request Data:', requestData);
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: requestData,
            success: function(response) {
                console.log('Response:', response);
                if (response.success) {
                    $success.text('Lizenz erfolgreich aktiviert! Seite wird neu geladen...').show();
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    var errorMsg = response.data || 'Aktivierung fehlgeschlagen';
                    $error.text('Fehler: ' + errorMsg).show();
                    $debug.text('Error: ' + errorMsg + '\n\nResponse:\n' + JSON.stringify(response, null, 2));
                    $debug.parent().show();
                    $btn.prop('disabled', false).text('Lizenz aktivieren');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response Text:', xhr.responseText);
                
                var debugInfo = 'Status: ' + status + '\n';
                debugInfo += 'Error: ' + error + '\n';
                debugInfo += 'Response Code: ' + xhr.status + '\n';
                debugInfo += 'Response Text:\n' + xhr.responseText;
                
                $error.text('Fehler bei der Aktivierung. Siehe Console für Details.').show();
                $debug.text(debugInfo);
                $debug.parent().show();
                $btn.prop('disabled', false).text('Lizenz aktivieren');
            }
        });
    });
    
    // Deactivate License
    $('#deactivate-license').on('click', function() {
        if (!confirm('Bist du sicher, dass du die Lizenz deaktivieren möchtest?')) {
            return;
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).text('Deaktiviere...');
        
        // Use inline nonce if rtLinkyData is not available
        var nonce = (typeof rtLinkyData !== 'undefined' && rtLinkyData.nonce) ? rtLinkyData.nonce : rtLinkyLicenseNonce;
        var ajaxUrl = (typeof rtLinkyData !== 'undefined' && rtLinkyData.ajaxUrl) ? rtLinkyData.ajaxUrl : '<?php echo admin_url('admin-ajax.php'); ?>';
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'rt_linky_deactivate_license',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Lizenz deaktiviert');
                    location.reload();
                } else {
                    alert('Fehler: ' + (response.data || 'Deaktivierung fehlgeschlagen'));
                    $btn.prop('disabled', false).text('Lizenz deaktivieren');
                }
            },
            error: function() {
                alert('Fehler bei der Deaktivierung');
                $btn.prop('disabled', false).text('Lizenz deaktivieren');
            }
        });
    });
    
});
</script>
