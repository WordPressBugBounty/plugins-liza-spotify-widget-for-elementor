<?php
namespace LizaSpotify\Widgets;

class WidgetLoader {
    public function __construct() {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    public function register_widgets($widgets_manager) {
        require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/SpotifyEmbed.php';
        require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/SpotifyProfile.php';

        $widgets_manager->register(new \LizaSpotify\Widgets\SpotifyEmbed());
        $widgets_manager->register(new \LizaSpotify\Widgets\SpotifyProfile());
    }
} 