<?php
namespace LizaSpotify\Widgets;

use Elementor\Plugin;

class WidgetLoader {
    public function __construct() {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    public function register_widgets($widgets_manager) {
        global $liza_spotify_fs;

        // Always load free widgets
        require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/SpotifyEmbed.php';
        require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/SpotifyProfile.php';
        
        // Register free widgets
        $widgets_manager->register(new SpotifyEmbed());
        $widgets_manager->register(new SpotifyProfile());

        // Only load and register premium widgets if user has premium version
        if ($liza_spotify_fs->can_use_premium_code()) {
            require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/SpotifyNowPlaying.php';
            require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/SpotifyArtist.php';
            
            $widgets_manager->register(new SpotifyNowPlaying());
            $widgets_manager->register(new SpotifyArtist());
        }
    }
} 