<?php
namespace LizaSpotify\Ajax;

use LizaSpotify\SpotifyAPI\Client;

class NowPlaying {
    public function __construct() {
        add_action('wp_ajax_get_now_playing_data', [$this, 'get_now_playing_data']);
        add_action('wp_ajax_nopriv_get_now_playing_data', [$this, 'get_now_playing_data']);
    }

    public function get_now_playing_data() {
        check_ajax_referer('spotify_now_playing', 'nonce');

        // Caching is handled inside Client::get_currently_playing() via transients
        $client        = new Client();
        $current_track = $client->get_currently_playing();

        if ($current_track && isset($current_track['item'])) {
            wp_send_json_success($current_track);
        } else {
            wp_send_json_success(null);
        }
    }
} 