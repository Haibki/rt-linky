<?php
/**
 * Stats View
 * 
 * @package RTLinky
 */

if (!defined('ABSPATH')) {
    exit;
}

$profiles = get_posts([
    'post_type' => 'rt_linky_profile',
    'posts_per_page' => -1,
    'post_status' => 'publish',
]);

$total_views = 0;
$total_clicks = 0;
foreach ($profiles as $profile) {
    $total_views += (int) get_post_meta($profile->ID, '_rt_linky_views', true);
    $total_clicks += (int) get_post_meta($profile->ID, '_rt_linky_clicks', true);
}

$ctr = $total_views > 0 ? round(($total_clicks / $total_views) * 100, 1) : 0;
?>
<div class="wrap rt-linky-wrap">
    <div class="rt-linky-header">
        <div class="rt-linky-brand">
            <a href="<?php echo admin_url('admin.php?page=rt-linky'); ?>" class="back-link">← Zurück</a>
            <h1>Statistiken</h1>
        </div>
    </div>

    <div class="rt-linky-stats-overview">
        <div class="stat-card large">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <span class="stat-number"><?php echo count($profiles); ?></span>
                <span class="stat-label">Profile gesamt</span>
            </div>
        </div>
        <div class="stat-card large">
            <div class="stat-icon">👁️</div>
            <div class="stat-content">
                <span class="stat-number"><?php echo number_format($total_views); ?></span>
                <span class="stat-label">Aufrufe gesamt</span>
            </div>
        </div>
        <div class="stat-card large">
            <div class="stat-icon">🖱️</div>
            <div class="stat-content">
                <span class="stat-number"><?php echo number_format($total_clicks); ?></span>
                <span class="stat-label">Klicks gesamt</span>
            </div>
        </div>
        <div class="stat-card large">
            <div class="stat-icon">📈</div>
            <div class="stat-content">
                <span class="stat-number"><?php echo $ctr; ?>%</span>
                <span class="stat-label">Klickrate (CTR)</span>
            </div>
        </div>
    </div>

    <div class="rt-linky-stats-table">
        <h2>Profile Performance</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Profil</th>
                    <th>Slug</th>
                    <th>Aufrufe</th>
                    <th>Klicks</th>
                    <th>CTR</th>
                    <th>Links</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($profiles as $profile): 
                    $slug = get_post_meta($profile->ID, '_rt_linky_slug', true);
                    $views = (int) get_post_meta($profile->ID, '_rt_linky_views', true);
                    $clicks = (int) get_post_meta($profile->ID, '_rt_linky_clicks', true);
                    $links = get_post_meta($profile->ID, '_rt_linky_links', true) ?: [];
                    $profile_ctr = $views > 0 ? round(($clicks / $views) * 100, 1) : 0;
                ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($profile->post_title); ?></strong>
                            <div class="row-actions">
                                <a href="<?php echo admin_url('admin.php?page=rt-linky-edit&id=' . $profile->ID); ?>">Bearbeiten</a> | 
                                <a href="<?php echo site_url('/link/' . $slug . '/'); ?>" target="_blank">Ansehen</a>
                            </div>
                        </td>
                        <td><code><?php echo esc_html($slug); ?></code></td>
                        <td><?php echo number_format($views); ?></td>
                        <td><?php echo number_format($clicks); ?></td>
                        <td><?php echo $profile_ctr; ?>%</td>
                        <td><?php echo count($links); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($profiles)): ?>
                    <tr>
                        <td colspan="6">Keine Profile gefunden.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
