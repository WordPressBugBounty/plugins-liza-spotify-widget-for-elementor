<?php
namespace LizaSpotify\Widgets;

use Elementor\Plugin;

class WidgetLoader {
    public function __construct() {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        
        // Add debugging
        add_action('admin_notices', [$this, 'debug_widget_registration']);

        // Remove Elementor admin bar and Go Pro button for non-Elementor pages
        add_action('init', function() {
            // Check if we're in admin area
            if (!is_admin()) {
                return;
            }

            // Verify nonce for admin requests
            if (isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'elementor_admin_nonce')) {
                // Get current page with proper sanitization
                $current_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
                $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
                $is_elementor_editor = $action === 'elementor';
                $is_our_plugin_page = strpos($current_page, 'liza-spotify') !== false;

                // If we're on our plugin's page or not in Elementor editor
                if ($is_our_plugin_page || !$is_elementor_editor) {
                    // Remove Elementor admin bar
                    remove_action('admin_bar_menu', [\Elementor\Plugin::instance()->admin_bar, 'add_menu_in_admin_bar'], 100);
                    
                    // Remove Go Pro button
                    remove_action('elementor/editor/footer', [\Elementor\Plugin::instance()->common, 'print_template_views']);
                    
                    // Remove Elementor admin bar from frontend
                    remove_action('wp_footer', [\Elementor\Plugin::instance()->admin_bar, 'print_style']);
                    remove_action('wp_footer', [\Elementor\Plugin::instance()->admin_bar, 'print_script']);
                    
                    // Remove Elementor admin bar from admin
                    remove_action('admin_footer', [\Elementor\Plugin::instance()->admin_bar, 'print_style']);
                    remove_action('admin_footer', [\Elementor\Plugin::instance()->admin_bar, 'print_script']);
                }
            }
        }, 5); // Priority 5 to run before Elementor's init
    }

    public function register_widgets($widgets_manager) {
        // Register only free widgets
        require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/SpotifyEmbed.php';
        require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/SpotifyProfile.php';
        
        $widgets_manager->register(new \LizaSpotify\Widgets\SpotifyEmbed());
        $widgets_manager->register(new \LizaSpotify\Widgets\SpotifyProfile());
    }

    public function debug_widget_registration() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!class_exists('\Elementor\Plugin')) {
            echo '<div class="notice notice-error"><p>Elementor is not active. Please install and activate Elementor to use Liza Spotify widgets.</p></div>';
            return;
        }

        $registered_widgets = \Elementor\Plugin::instance()->widgets_manager->get_widget_types();
        if (!isset($registered_widgets['apple-music-embed'])) {
            global $liza_spotify_fs;
            // Only show error if Freemius is available and user has premium access
            if (isset($liza_spotify_fs) && is_object($liza_spotify_fs) && 
                (method_exists($liza_spotify_fs, 'can_use_premium_code') && $liza_spotify_fs->can_use_premium_code() || 
                 method_exists($liza_spotify_fs, 'is_trial') && $liza_spotify_fs->is_trial())) {
                echo '<div class="notice notice-error"><p>Apple Music Embed widget is not registered properly.</p></div>';
            }
        }
    }
} 