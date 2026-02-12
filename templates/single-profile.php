<?php
/**
 * Single Profile Template
 * 
 * @package RTLinky
 */

if (!defined('ABSPATH')) {
    exit;
}

$slug = get_query_var('rt_linky_slug');

if (!$slug) {
    return;
}

$args = [
    'post_type'      => \RT_Linky\PostType\ProfilePostType::POST_TYPE,
    'posts_per_page' => 1,
    'meta_query'     => [
        [
            'key'     => '_rt_linky_slug',
            'value'   => $slug,
            'compare' => '=',
        ],
    ],
];

$query = new WP_Query($args);

if (!$query->have_posts()) {
    status_header(404);
    echo '404 - Profil nicht gefunden';
    exit;
}

$profile = $query->posts[0];

// Get profile data
$title = $profile->post_title;
$bio = get_post_meta($profile->ID, '_rt_linky_bio', true);
$avatar = get_post_meta($profile->ID, '_rt_linky_avatar', true);
$verified = get_post_meta($profile->ID, '_rt_linky_verified', true);
$design = get_post_meta($profile->ID, '_rt_linky_design', true) ?: [];
$links = get_post_meta($profile->ID, '_rt_linky_links', true) ?: [];
$post_id = $profile->ID;

// Design settings
$bg_type = $design['bg_type'] ?? 'gradient';
$color1 = $design['color1'] ?? '#667eea';
$color2 = $design['color2'] ?? '#764ba2';
$bg_image = $design['bg_image'] ?? '';
$text_color = $design['text_color'] ?? '#ffffff';
$button_color = $design['button_color'] ?? '#ffffff';
$button_radius = isset($design['button_radius']) ? intval($design['button_radius']) : 12;

// Background
if ($bg_type === 'gradient') {
    $background = "linear-gradient(135deg, {$color1}, {$color2})";
} elseif ($bg_type === 'image' && $bg_image) {
    $background = "url('{$bg_image}') center/cover no-repeat fixed";
} else {
    $background = $color1;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($title); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: <?php echo $background; ?>;
            color: <?php echo $text_color; ?>;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
            line-height: 1.6;
        }
        
        .rt-linky-container {
            width: 100%;
            max-width: 420px;
            text-align: center;
            animation: fadeIn 0.6s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .rt-linky-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            margin: 0 auto 30px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: scaleIn 0.5s ease 0.1s both;
        }
        
        @keyframes scaleIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .rt-linky-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .rt-linky-avatar-default {
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
        }
        
        .rt-linky-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            animation: fadeIn 0.5s ease 0.2s both;
        }
        
        .rt-linky-verified {
            color: #10b981;
            font-size: 24px;
        }
        
        .rt-linky-bio {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 40px;
            line-height: 1.6;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeIn 0.5s ease 0.3s both;
        }
        
        .rt-linky-links {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .rt-linky-link {
            display: flex;
            align-items: center;
            gap: 20px;
            background: <?php echo $button_color; ?>;
            color: #1f2937;
            text-decoration: none;
            padding: 20px 30px;
            border-radius: <?php echo $button_radius; ?>px;
            font-weight: 600;
            font-size: 17px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid transparent;
            animation: slideUp 0.5s ease both;
            cursor: pointer;
        }
        
        .rt-linky-link:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            border-color: rgba(0, 0, 0, 0.1);
        }
        
        .rt-linky-link:active {
            transform: translateY(-1px) scale(0.98);
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .rt-linky-link-icon {
            width: 28px;
            height: 28px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .rt-linky-link-icon svg {
            width: 100%;
            height: 100%;
        }
        
        .rt-linky-link-text {
            flex: 1;
            text-align: left;
        }
        
        .rt-linky-link-arrow {
            opacity: 0.6;
            font-size: 20px;
            transition: transform 0.3s ease;
        }
        
        .rt-linky-link:hover .rt-linky-link-arrow {
            transform: translateX(5px);
        }
        
        .rt-linky-footer {
            margin-top: 50px;
            font-size: 14px;
            opacity: 0.7;
            animation: fadeIn 0.5s ease 0.8s both;
        }
        
        @media (max-width: 480px) {
            .rt-linky-avatar {
                width: 120px;
                height: 120px;
            }
            
            .rt-linky-title {
                font-size: 28px;
            }
            
            .rt-linky-bio {
                font-size: 16px;
            }
            
            .rt-linky-link {
                padding: 18px 24px;
                font-size: 16px;
            }
        }
        
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        .rt-linky-link:focus {
            outline: 3px solid rgba(255, 255, 255, 0.5);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="rt-linky-container">
        <?php if ($avatar): ?>
            <div class="rt-linky-avatar">
                <img src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
            </div>
        <?php else: ?>
            <div class="rt-linky-avatar">
                <div class="rt-linky-avatar-default">
                    <span>👤</span>
                </div>
            </div>
        <?php endif; ?>
        
        <h1 class="rt-linky-title">
            <?php echo esc_html($title); ?>
            <?php if ($verified): ?>
                <span class="rt-linky-verified" title="Verifiziert">✓</span>
            <?php endif; ?>
        </h1>
        
        <?php if ($bio): ?>
            <p class="rt-linky-bio"><?php echo nl2br(esc_html($bio)); ?></p>
        <?php endif; ?>
        
        <div class="rt-linky-links">
            <?php if (!empty($links)): ?>
                <?php foreach ($links as $index => $link): ?>
                    <a href="<?php echo esc_url($link['url']); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="rt-linky-link"
                       style="animation-delay: <?php echo 0.4 + ($index * 0.1); ?>s"
                       data-link-label="<?php echo esc_attr($link['title']); ?>">
                        <div class="rt-linky-link-icon">
                            <?php if (!empty($link['icon'])): ?>
                                <?php echo $link['icon']; ?>
                            <?php else: ?>
                                🔗
                            <?php endif; ?>
                        </div>
                        <div class="rt-linky-link-text"><?php echo esc_html($link['title']); ?></div>
                        <div class="rt-linky-link-arrow">→</div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="opacity: 0.7; font-style: italic; padding: 40px 20px;">
                    Noch keine Links verfügbar
                </div>
            <?php endif; ?>
        </div>
        
        <div class="rt-linky-footer">
            Erstellt mit RT-Linky
        </div>
    </div>
    
    <?php wp_footer(); ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Click tracking
        document.querySelectorAll('.rt-linky-link').forEach(function(link) {
            link.addEventListener('click', function(e) {
                var label = this.getAttribute('data-link-label');
                var url = this.href;
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'rt_linky_track_click',
                        post_id: '<?php echo $post_id; ?>',
                        url: url,
                        label: label,
                        nonce: '<?php echo wp_create_nonce('rt_linky_track'); ?>'
                    }),
                }).catch(function() {
                    // Silent fail
                });
            });
        });
        
        document.body.style.opacity = '0';
        requestAnimationFrame(function() {
            document.body.style.transition = 'opacity 0.3s ease';
            document.body.style.opacity = '1';
        });
    });
    </script>
</body>
</html>
