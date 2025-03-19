<?php
namespace LizaSpotify\Ajax;

use LizaSpotify\SpotifyAPI\Client;

class Search {
    public function __construct() {
        add_action('wp_ajax_spotify_search', [$this, 'handle_search']);
        add_action('wp_ajax_nopriv_spotify_search', [$this, 'handle_search']);
    }

    public function handle_search() {
        check_ajax_referer('spotify_search', 'nonce');

        $query = sanitize_text_field($_POST['query'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? 'track');
        $limit = intval($_POST['limit'] ?? 5);

        if (empty($query)) {
            wp_send_json_error(['message' => __('Please enter a search query.', 'liza-spotify')]);
            return;
        }

        $client = new Client();
        $results = $client->search($query, $type, $limit);

        if (!$results) {
            wp_send_json_error(['message' => __('Failed to search Spotify.', 'liza-spotify')]);
            return;
        }

        wp_send_json_success($results);
    }
} 