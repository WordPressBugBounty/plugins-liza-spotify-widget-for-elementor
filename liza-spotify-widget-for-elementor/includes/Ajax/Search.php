<?php
namespace LizaSpotify\Ajax;

use LizaSpotify\SpotifyAPI\Client;

class Search {
    public function __construct() {
        add_action('wp_ajax_spotify_search', [$this, 'handle_search']);
        add_action('wp_ajax_nopriv_spotify_search', [$this, 'handle_search']);
    }

    public function handle_search() {
        check_ajax_referer('liza_spotify_nonce', 'nonce');

        // Validate and sanitize input
        if (!isset($_POST['query']) || empty($_POST['query'])) {
            wp_send_json_error(['message' => esc_html__('Please enter a search query.', 'liza-spotify-widget-for-elementor')]);
            return;
        }

        $query = sanitize_text_field(wp_unslash($_POST['query']));
        $type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : 'track,artist,album,playlist';
        $limit = isset($_POST['limit']) ? intval(wp_unslash($_POST['limit'])) : 5;

        try {
            $client = new Client();
            $results = $client->search($query, $type, $limit);
            wp_send_json_success($results);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => esc_html__('Failed to search Spotify.', 'liza-spotify-widget-for-elementor')]);
        }
    }
} 