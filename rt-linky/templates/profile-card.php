<?php
/**
 * Profile Card Template
 * 
 * @package RTLinky
 */

if (!defined('ABSPATH')) {
    exit;
}

$title = $data['title'] ?? '';
$bio = $data['bio'] ?? '';
$avatar = $data['avatar_url'] ?? '';
$verified = $data['verified'] ?? false;
$design = $data['design'] ?? [];
$links = $data['links'] ?? [];
$post_id = $data['post_id'] ?? 0;

$bg_type = $design['bg_type'] ?? 'gradient';
$color1 = $design['color1'] ?? '#667eea';
$color2 = $design['color2'] ?? '#764ba2';
$text_color = $design['text_color'] ?? '#ffffff';
$button_color = $design['button_color'] ?? '#ffffff';
$button_radius = isset($design['button_radius']) ? intval($design['button_radius']) : 12;

if ($bg_type === 'gradient') {
    $background = "linear-gradient(135deg, {$color1}, {$color2})";
} elseif ($bg_type === 'image') {
    $background = "url('{$design['bg_image']}') center/cover";
} else {
    $background = $color1;
}

$unique_id = 'rt-linky-' . $post_id . '-' . wp_rand(1000, 9999);
?>
<div id="<?php echo esc_attr($unique_id); ?>" class="rt-linky-card-embed" style="
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: <?php echo $background; ?>;
    color: <?php echo $text_color; ?>;
    padding: 40px 20px;
    border-radius: 16px;
    text-align: center;
    max-width: 400px;
    margin: 0 auto;
">
    <?php if ($avatar): ?>
        <div class="rt-linky-card-avatar" style="
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 20px;
            border: 4px solid rgba(255,255,255,0.3);
            overflow: hidden;
        ">
            <img src="<?php echo esc_url($avatar); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
    <?php endif; ?>
    
    <h3 style="margin: 0 0 10px; font-size: 24px; display: flex; align-items: center; justify-content: center; gap: 8px;">
        <?php echo esc_html($title); ?>
        <?php if ($verified): ?>
            <span style="color: #10b981;">✓</span>
        <?php endif; ?>
    </h3>
    
    <?php if ($bio): ?>
        <p style="margin: 0 0 30px; opacity: 0.9; font-size: 14px;"><?php echo esc_html($bio); ?></p>
    <?php endif; ?>
    
    <div style="display: flex; flex-direction: column; gap: 12px;">
        <?php foreach (array_slice($links, 0, 3) as $link): ?>
            <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener" style="
                display: flex;
                align-items: center;
                gap: 12px;
                background: <?php echo $button_color; ?>;
                color: #1f2937;
                text-decoration: none;
                padding: 14px 20px;
                border-radius: <?php echo $button_radius; ?>px;
                font-weight: 600;
                font-size: 14px;
                transition: transform 0.2s;
            " onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <span style="flex: 1; text-align: left;"><?php echo esc_html($link['title']); ?></span>
                <span>→</span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
