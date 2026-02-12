<?php
/**
 * Admin Handler
 * 
 * @package RTLinky
 */

namespace RT_Linky\Admin;

/**
 * Admin Class
 */
class Admin
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addMenuPages']);
        // WICHTIG: Priorität 20 damit wir ZULETZT ausgeführt werden
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets'], 20);
        add_action('wp_ajax_rt_linky_save_profile', [$this, 'ajaxSaveProfile']);
        add_action('wp_ajax_rt_linky_delete_profile', [$this, 'ajaxDeleteProfile']);
        
        // Load license client if exists
        $license_client_file = RT_LINKY_PLUGIN_DIR . 'class-license-client.php';
        if (file_exists($license_client_file)) {
            require_once $license_client_file;
        }
    }

    /**
     * Check if license is active
     *
     * @return bool
     */
    public static function isLicenseActive(): bool
    {
        if (isset($GLOBALS['rt_linky_license_client'])) {
            return $GLOBALS['rt_linky_license_client']->is_valid();
        }
        
        $license = get_option('rt_linky_license', array());
        if (isset($license['status']) && $license['status'] === 'active') {
            return true;
        }
        
        $license_key = get_option('rt-linky_license_key', '');
        if (!empty($license_key)) {
            if (class_exists('RT_Linky_License_Client')) {
                $client = new \RT_Linky_License_Client();
                return $client->is_valid();
            }
        }
        
        return false;
    }

    /**
     * Add menu pages
     *
     * @return void
     */
    public function addMenuPages(): void
    {
        // Main menu page
        add_menu_page(
            'RT-Linky',
            'RT-Linky',
            'manage_options',
            'rt-linky',
            [$this, 'renderDashboard'],
            'dashicons-admin-links',
            26
        );
        
        // Submenu: Add New
        add_submenu_page(
            'rt-linky',
            'Neues Profil',
            'Neues Profil',
            'manage_options',
            'rt-linky-new',
            [$this, 'renderEditor']
        );
        
        // Submenu: Edit (hidden)
        add_submenu_page(
            'rt-linky',
            'Profil bearbeiten',
            'Bearbeiten',
            'manage_options',
            'rt-linky-edit',
            [$this, 'renderEditor']
        );
        
        // Submenu: Stats
        add_submenu_page(
            'rt-linky',
            'Statistiken',
            'Statistiken',
            'manage_options',
            'rt-linky-stats',
            [$this, 'renderStats']
        );
        
        // Remove the default CPT menu
        remove_menu_page('edit.php?post_type=rt_linky_profile');
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page
     * @return void
     */
    public function enqueueAssets(string $hook): void
    {
        if (!str_contains($hook, 'rt-linky')) {
            return;
        }

        // WordPress Media Uploader
        wp_enqueue_media();
        
        // Custom Admin CSS
        wp_enqueue_style(
            'rt-linky-admin',
            RT_LINKY_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            RT_LINKY_VERSION
        );
        
        // Custom Admin JS
        wp_enqueue_script(
            'rt-linky-admin',
            RT_LINKY_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            RT_LINKY_VERSION,
            true
        );
        
        // Generate fresh nonce
        $ajax_nonce = wp_create_nonce('rt_linky_nonce');
        
        // WICHTIG: Relativer Pfad für AJAX um www/non-www Probleme zu vermeiden
        wp_localize_script('rt-linky-admin', 'rtLinkyData', array(
            'ajaxUrl' => admin_url('admin-ajax.php', 'relative'),
            'nonce' => $ajax_nonce,
            'siteUrl' => site_url('/'),
            'pluginUrl' => RT_LINKY_PLUGIN_URL,
            'licenseStatus' => self::isLicenseActive() ? 'active' : 'inactive',
        ));
    }

    /**
     * Render Dashboard
     *
     * @return void
     */
    public function renderDashboard(): void
    {
        // Check license - show notice if not active
        if (!self::isLicenseActive()) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-warning"><p><strong>RT-Linky:</strong> Bitte aktiviere deine Lizenz um alle Funktionen zu nutzen. <a href="' . admin_url('admin.php?page=rt-linky-license') . '">Lizenz aktivieren</a></p></div>';
            });
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
        
        include RT_LINKY_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Render Editor
     *
     * @return void
     */
    public function renderEditor(): void
    {
        // WICHTIG: Prüfe ob id Parameter gesetzt ist
        $profile_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $profile = null;
        
        if ($profile_id) {
            $profile = get_post($profile_id);
            
            // Prüfe ob Profil existiert und vom richtigen Typ ist
            if (!$profile || $profile->post_type !== 'rt_linky_profile') {
                // Zeige Fehler oder leite um
                wp_redirect(admin_url('admin.php?page=rt-linky&error=not_found'));
                exit;
            }
        }
        
        // Lade Editor View
        include RT_LINKY_PLUGIN_DIR . 'admin/views/editor.php';
    }

    /**
     * Render Stats
     *
     * @return void
     */
    public function renderStats(): void
    {
        include RT_LINKY_PLUGIN_DIR . 'admin/views/stats.php';
    }

    /**
     * AJAX: Save Profile
     *
     * @return void
     */
    public function ajaxSaveProfile(): void
    {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rt_linky_nonce')) {
            wp_send_json_error('Sicherheitsprüfung fehlgeschlagen');
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
            return;
        }
        
        $profile_id = intval($_POST['profile_id'] ?? 0);
        $title = sanitize_text_field($_POST['title'] ?? '');
        
        if (empty($title)) {
            wp_send_json_error('Bitte gib einen Titel ein');
            return;
        }
        
        $post_data = array(
            'post_title'  => $title,
            'post_type'   => 'rt_linky_profile',
            'post_status' => 'publish',
        );
        
        if ($profile_id) {
            $post_data['ID'] = $profile_id;
            $post_id = wp_update_post($post_data, true);
        } else {
            $post_id = wp_insert_post($post_data, true);
        }
        
        if (is_wp_error($post_id)) {
            wp_send_json_error($post_id->get_error_message());
            return;
        }
        
        $slug = sanitize_title($_POST['slug'] ?? $title);
        
        update_post_meta($post_id, '_rt_linky_slug', $slug);
        update_post_meta($post_id, '_rt_linky_bio', sanitize_textarea_field($_POST['bio'] ?? ''));
        update_post_meta($post_id, '_rt_linky_avatar', esc_url_raw($_POST['avatar_url'] ?? ''));
        update_post_meta($post_id, '_rt_linky_verified', !empty($_POST['verified']));
        
        $design = array(
            'bg_type'       => sanitize_text_field($_POST['design']['bg_type'] ?? 'gradient'),
            'color1'        => sanitize_hex_color($_POST['design']['color1'] ?? '#667eea'),
            'color2'        => sanitize_hex_color($_POST['design']['color2'] ?? '#764ba2'),
            'bg_image'      => esc_url_raw($_POST['design']['bg_image'] ?? ''),
            'text_color'    => sanitize_hex_color($_POST['design']['text_color'] ?? '#ffffff'),
            'button_color'  => sanitize_hex_color($_POST['design']['button_color'] ?? '#ffffff'),
            'button_radius' => intval($_POST['design']['button_radius'] ?? 12),
        );
        update_post_meta($post_id, '_rt_linky_design', $design);
        
        $links = array();
        if (!empty($_POST['links']) && is_array($_POST['links'])) {
            foreach ($_POST['links'] as $link) {
                $links[] = array(
                    'id'    => sanitize_text_field($link['id'] ?? uniqid()),
                    'title' => sanitize_text_field($link['title'] ?? ''),
                    'url'   => esc_url_raw($link['url'] ?? ''),
                    'icon'  => sanitize_text_field($link['icon'] ?? '🔗'),
                );
            }
        }
        update_post_meta($post_id, '_rt_linky_links', $links);
        
        wp_send_json_success(array(
            'id' => $post_id,
            'message' => 'Gespeichert',
        ));
    }

    /**
     * AJAX: Delete Profile
     *
     * @return void
     */
    public function ajaxDeleteProfile(): void
    {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rt_linky_nonce')) {
            wp_send_json_error('Sicherheitsprüfung fehlgeschlagen');
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
            return;
        }
        
        $profile_id = isset($_POST['profile_id']) ? intval($_POST['profile_id']) : 0;
        
        if (!$profile_id) {
            wp_send_json_error('Ungültige Profil-ID');
            return;
        }
        
        $result = wp_delete_post($profile_id, true);
        
        if ($result) {
            wp_send_json_success(array('deleted' => true));
        } else {
            wp_send_json_error('Löschen fehlgeschlagen');
        }
    }
}