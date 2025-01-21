<?php
namespace LizaSpotify\Widgets;

use Elementor\Plugin;

class WidgetLoader {
    public function __construct() {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        
        // Add debugging
        add_action('admin_notices', [$this, 'debug_widget_registration']);

        // Remove Go Pro button for pro users
        global $liza_spotify_fs;
        if ($liza_spotify_fs->can_use_premium_code()) {
            remove_action('elementor/editor/footer', [Plugin::instance()->common, 'print_template_views']);
            add_action('elementor/editor/footer', [$this, 'print_template_views_without_pro']);
        }
    }

    public function register_widgets($widgets_manager) {
        global $liza_spotify_fs;

        // Always register free widgets
        require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/SpotifyEmbed.php';
        require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/SpotifyProfile.php';

        $widgets_manager->register(new SpotifyEmbed());
        $widgets_manager->register(new SpotifyProfile());

        // Register premium widgets only if user has premium access
        if ($liza_spotify_fs->can_use_premium_code() || $liza_spotify_fs->is_trial()) {
            require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/SpotifyNowPlaying.php';
            require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/SpotifyArtist.php';
            require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/AppleMusicEmbed.php';
            
            $widgets_manager->register(new SpotifyNowPlaying());
            $widgets_manager->register(new SpotifyArtist());
            $widgets_manager->register(new AppleMusicEmbed());
        }
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
        if (!isset($registered_widgets['apple-music-embed']) && current_user_can('manage_options')) {
            global $liza_spotify_fs;
            if ($liza_spotify_fs->can_use_premium_code() || $liza_spotify_fs->is_trial()) {
                echo '<div class="notice notice-error"><p>Apple Music Embed widget is not registered properly.</p></div>';
            }
        }
    }

    public function print_template_views_without_pro() {
        // Get all template views except the pro button
        $template_views = Plugin::instance()->common->get_template_views();
        unset($template_views['go-pro']);
        
        foreach ($template_views as $view) {
            $view->print_template();
        }
    }
} 