<?php
namespace LizaSpotify\SpotifyAPI;

class Client {
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $api_base = 'https://api.spotify.com/v1';
    private $auth_base = 'https://accounts.spotify.com';

    const CACHE_TTL_SHORT  = 10;   // seconds — now-playing, currently-playing
    const CACHE_TTL_MEDIUM = 300;  // 5 min — user profile
    const CACHE_TTL_LONG   = 3600; // 1 hr — artist data

    public function __construct() {
        $this->client_id     = get_option('liza_spotify_client_id', '');
        $this->client_secret = get_option('liza_spotify_client_secret', '');
        $this->redirect_uri  = admin_url('admin.php?page=liza-spotify-settings');
    }

    public function has_credentials() {
        return !empty($this->client_id) && !empty($this->client_secret);
    }

    public function get_auth_url() {
        if (!$this->has_credentials()) {
            return '';
        }
        $state = wp_create_nonce('spotify_auth');
        $scope = 'user-read-private user-read-email user-read-currently-playing user-read-playback-state';
        
        $params = array(
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'scope' => $scope,
            'redirect_uri' => $this->redirect_uri,
            'state' => $state
        );

        return $this->auth_base . '/authorize?' . http_build_query($params);
    }

    public function handle_auth_callback($code) {
        if (!$this->has_credentials()) {
            return false;
        }
        $code      = sanitize_text_field($code);
        $token_url = $this->auth_base . '/api/token';
        
        $headers = array(
            'Authorization' => 'Basic ' . base64_encode($this->client_id . ':' . $this->client_secret)
        );
        
        $body = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirect_uri
        );

        $response = wp_remote_post($token_url, array(
            'headers' => $headers,
            'body' => $body
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($data['access_token'])) {
            update_option('liza_spotify_access_token', $data['access_token']);
            update_option('liza_spotify_refresh_token', $data['refresh_token']);
            update_option('liza_spotify_token_expiry', time() + $data['expires_in']);
            return true;
        }

        return false;
    }

    public function refresh_token() {
        $refresh_token = get_option('liza_spotify_refresh_token');
        if (!$refresh_token) {
            return false;
        }

        $token_url = $this->auth_base . '/api/token';
        
        $headers = array(
            'Authorization' => 'Basic ' . base64_encode($this->client_id . ':' . $this->client_secret)
        );
        
        $body = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token
        );

        $response = wp_remote_post($token_url, array(
            'headers' => $headers,
            'body' => $body
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($data['access_token'])) {
            update_option('liza_spotify_access_token', $data['access_token']);
            update_option('liza_spotify_token_expiry', time() + $data['expires_in']);
            if (isset($data['refresh_token'])) {
                update_option('liza_spotify_refresh_token', $data['refresh_token']);
            }
            return true;
        }

        return false;
    }

    public function get_user_profile() {
        $cache_key = 'liza_spotify_profile_' . md5(get_option('liza_spotify_access_token', ''));
        $cached    = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
        $data = $this->make_request('GET', '/me');
        if ($data) {
            set_transient($cache_key, $data, self::CACHE_TTL_MEDIUM);
        }
        return $data;
    }

    public function get_currently_playing() {
        $cache_key = 'liza_spotify_now_playing_' . md5(get_option('liza_spotify_access_token', ''));
        $cached    = get_transient($cache_key);
        if ($cached !== false) {
            // '__none__' is the sentinel we store when nothing is playing (Spotify 204)
            return $cached === '__none__' ? null : $cached;
        }
        $data = $this->make_request('GET', '/me/player/currently-playing');
        // Store a sentinel so we don't hammer the API when nothing is playing
        set_transient($cache_key, $data === false || $data === null ? '__none__' : $data, self::CACHE_TTL_SHORT);
        return $data;
    }

    public function get_artist($artist_id) {
        $artist_id = sanitize_text_field($artist_id);
        $cache_key = 'liza_spotify_artist_' . md5($artist_id);
        $cached    = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
        $data = $this->make_request('GET', '/artists/' . $artist_id);
        if ($data) {
            set_transient($cache_key, $data, self::CACHE_TTL_LONG);
        }
        return $data;
    }

    public function get_artist_top_tracks($artist_id, $market = 'US') {
        $artist_id = sanitize_text_field($artist_id);
        $cache_key = 'liza_spotify_top_tracks_' . md5($artist_id . $market);
        $cached    = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
        $response = $this->make_request('GET', '/artists/' . $artist_id . '/top-tracks', ['market' => $market]);
        // Spotify returns {"tracks": [...]} — extract the array
        $data = (is_array($response) && isset($response['tracks'])) ? $response['tracks'] : [];
        if (!empty($data)) {
            set_transient($cache_key, $data, self::CACHE_TTL_LONG);
        }
        return $data;
    }

    public function clear_cache() {
        $token     = get_option('liza_spotify_access_token', '');
        $cache_key = 'liza_spotify_profile_' . md5($token);
        delete_transient($cache_key);
        $cache_key = 'liza_spotify_now_playing_' . md5($token);
        delete_transient($cache_key);
    }

    private function make_request($method, $endpoint, $params = array()) {
        if (!$this->has_credentials()) {
            return false;
        }
        if (time() > get_option('liza_spotify_token_expiry', 0)) {
            if (!$this->refresh_token()) {
                return false;
            }
        }

        $url = $this->api_base . $endpoint;
        if (!empty($params) && $method === 'GET') {
            $url .= '?' . http_build_query($params);
        }

        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . get_option('liza_spotify_access_token'),
                'Content-Type' => 'application/json'
            )
        );

        if (!empty($params) && $method !== 'GET') {
            $args['body'] = json_encode($params);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        return $data;
    }
} 