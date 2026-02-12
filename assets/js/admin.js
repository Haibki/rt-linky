// Bearbeiten-Link Fix: ID korrekt hinzufügen
function editProfile(profileId) {
    if (!profileId) {
        console.error('Keine Profil-ID angegeben');
        return;
    }
    
    // Korrekte URL mit ID
    const editUrl = 'post.php?post=' + encodeURIComponent(profileId) + '&action=edit';
    window.location.href = editUrl;
}

// Icon-Auswahl mit Free/Pro-Logik
function initIconSelector() {
    const iconGrid = document.querySelector('.rt-linky-icon-grid');
    if (!iconGrid) return;
    
    const isPro = window.rtLinkyEditor?.isPro || false;
    const freeIconLimit = 2; // Nur erste 2 Icons für Free
    
    const icons = iconGrid.querySelectorAll('.icon-item');
    
    icons.forEach((icon, index) => {
        const iconId = icon.dataset.icon;
        
        // Free: Nur erste 2 Icons erlaubt
        if (!isPro && index >= freeIconLimit) {
            icon.classList.add('locked');
            icon.setAttribute('title', '🔒 Nur in Pro verfügbar');
            
            // WICHTIG: Event-Listener verhindern
            icon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Visuelles Feedback
                showProTooltip(icon, 'Dieses Icon ist nur in der Pro-Version verfügbar');
                return false;
            });
        } else {
            // Normale Auswahl für erlaubte Icons
            icon.addEventListener('click', function() {
                selectIcon(iconId);
            });
        }
    });
}

// Pro-Tooltip anzeigen
function showProTooltip(element, text) {
    // Bestehende Tooltips entfernen
    document.querySelectorAll('.rt-linky-pro-tooltip').forEach(t => t.remove());
    
    const tooltip = document.createElement('div');
    tooltip.className = 'rt-linky-pro-tooltip';
    tooltip.innerHTML = text + '<br><small>Klicke für Upgrade-Info</small>';
    
    const rect = element.getBoundingClientRect();
    tooltip.style.position = 'fixed';
    tooltip.style.left = rect.left + 'px';
    tooltip.style.top = (rect.bottom + 5) + 'px';
    tooltip.style.zIndex = '9999';
    
    document.body.appendChild(tooltip);
    
    // Upgrade-Redirect bei Klick auf Tooltip
    tooltip.addEventListener('click', function() {
        window.open('https://rettoro.de/rt-linky', '_blank');
    });
    
    // Auto-remove nach 3 Sekunden
    setTimeout(() => tooltip.remove(), 3000);
    
    // Remove bei Klick woanders
    document.addEventListener('click', function removeTooltip(e) {
        if (!tooltip.contains(e.target)) {
            tooltip.remove();
            document.removeEventListener('click', removeTooltip);
        }
    });
}

// Untertitel-Feld (nur Pro)
function initSubtitleField() {
    const subtitleContainer = document.getElementById('rt-linky-subtitle-container');
    if (!subtitleContainer) return;
    
    const isPro = window.rtLinkyEditor?.isPro || false;
    const subtitlesEnabled = window.rtLinkyEditor?.subtitlesEnabled || false;
    
    if (!isPro || !subtitlesEnabled) {
        subtitleContainer.style.display = 'none';
        return;
    }
    
    // Pro: Feld anzeigen und initialisieren
    subtitleContainer.style.display = 'block';
    const subtitleInput = document.getElementById('rt-linky-subtitle');
    
    // Live-Preview Update
    subtitleInput.addEventListener('input', function() {
        updatePreview('subtitle', this.value);
    });
}