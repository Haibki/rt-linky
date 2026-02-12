<?php
/**
 * Dashboard View
 * 
 * @package RTLinky
 */

if (!defined('ABSPATH')) {
    exit;
}

$profiles = get_posts(array(
    'post_type' => 'rt_linky_profile',
    'posts_per_page' => -1,
    'post_status' => 'publish',
));

$total_views = 0;
$total_clicks = 0;
foreach ($profiles as $profile) {
    $total_views += (int) get_post_meta($profile->ID, '_rt_linky_views', true);
    $total_clicks += (int) get_post_meta($profile->ID, '_rt_linky_clicks', true);
}
?>
<div class="wrap rt-linky-wrap">
    <div class="rt-linky-header">
        <div class="rt-linky-brand">
            <span class="rt-linky-logo">🔗</span>
            <div>
                <h1>RT-Linky</h1>
                <p>Deine Link-in-Bio Profile</p>
            </div>
        </div>
        <a href="<?php echo admin_url('admin.php?page=rt-linky-new'); ?>" class="button button-primary button-lg">
            <span class="dashicons dashicons-plus"></span>
            Neues Profil erstellen
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="rt-linky-stats-bar">
        <div class="rt-linky-stat-box">
            <span class="stat-number"><?php echo count($profiles); ?></span>
            <span class="stat-label">Profile</span>
        </div>
        <div class="rt-linky-stat-box">
            <span class="stat-number"><?php echo number_format($total_views); ?></span>
            <span class="stat-label">Aufrufe</span>
        </div>
        <div class="rt-linky-stat-box">
            <span class="stat-number"><?php echo number_format($total_clicks); ?></span>
            <span class="stat-label">Klicks</span>
        </div>
        <div class="rt-linky-stat-box">
            <span class="stat-number">
                <?php echo $total_views > 0 ? round(($total_clicks / $total_views) * 100) : 0; ?>%
            </span>
            <span class="stat-label">Klickrate</span>
        </div>
    </div>

    <!-- Profiles Grid -->
    <div class="rt-linky-profiles">
        <h2>Deine Profile</h2>
        
        <?php if (empty($profiles)): ?>
            <div class="rt-linky-empty">
                <div class="empty-icon">📝</div>
                <h3>Noch keine Profile</h3>
                <p>Erstelle dein erstes Link-in-Bio Profil um zu starten.</p>
                <a href="<?php echo admin_url('admin.php?page=rt-linky-new'); ?>" class="button button-primary">
                    Erstes Profil erstellen
                </a>
            </div>
        <?php else: ?>
            <div class="rt-linky-grid">
                <?php foreach ($profiles as $profile): 
                    $slug = get_post_meta($profile->ID, '_rt_linky_slug', true);
                    $views = (int) get_post_meta($profile->ID, '_rt_linky_views', true);
                    $clicks = (int) get_post_meta($profile->ID, '_rt_linky_clicks', true);
                    $avatar = get_post_meta($profile->ID, '_rt_linky_avatar', true);
                    $verified = get_post_meta($profile->ID, '_rt_linky_verified', true);
                    $links = get_post_meta($profile->ID, '_rt_linky_links', true) ?: array();
                ?>
                    <div class="rt-linky-card" data-id="<?php echo $profile->ID; ?>">
                        <div class="card-header">
                            <?php if ($avatar): ?>
                                <img src="<?php echo esc_url($avatar); ?>" alt="" class="card-avatar">
                            <?php else: ?>
                                <div class="card-avatar-placeholder">
                                    <?php echo strtoupper(substr($profile->post_title, 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div class="card-info">
                                <h3>
                                    <?php echo esc_html($profile->post_title); ?>
                                    <?php if ($verified): ?>
                                        <span class="verified-badge">✓</span>
                                    <?php endif; ?>
                                </h3>
                                <code class="card-slug">/link/<?php echo esc_html($slug); ?>/</code>
                            </div>
                        </div>
                        
                        <div class="card-stats">
                            <div class="stat">
                                <span class="stat-value"><?php echo number_format($views); ?></span>
                                <span class="stat-name">Aufrufe</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value"><?php echo number_format($clicks); ?></span>
                                <span class="stat-name">Klicks</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value"><?php echo count($links); ?></span>
                                <span class="stat-name">Links</span>
                            </div>
                        </div>
                        
                        <div class="card-actions">
                            <!-- WICHTIG: Korrekter Bearbeiten-Link mit id Parameter -->
                            <a href="<?php echo admin_url('admin.php?page=rt-linky-edit&id=' . $profile->ID); ?>" class="button">
                                <span class="dashicons dashicons-edit"></span>
                                Bearbeiten
                            </a>
                            <a href="<?php echo site_url('/link/' . $slug . '/'); ?>" target="_blank" class="button">
                                <span class="dashicons dashicons-visibility"></span>
                                Ansehen
                            </a>
                            <button type="button" class="button button-link-delete delete-profile" data-id="<?php echo $profile->ID; ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>