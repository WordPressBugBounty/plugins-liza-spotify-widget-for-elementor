<?php
namespace LizaSpotify\SpotifyAPI;

class Client {
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $access_token;
    private $refresh_token;

    public function __construct() {
        $this->client_id = get_option('liza_spotify_client_id');
        $this->client_secret = get_option('liza_spotify_client_secret');
        $this->redirect_uri = admin_url('admin.php?page=liza-spotify-settings');
        $this->access_token = get_option('liza_spotify_access_token');
        $this->refresh_token = get_option('liza_spotify_refresh_token');
    }

    public function get_auth_url() {
        $scope = 'user-read-private user-read-email';
        $state = wp_create_nonce('spotify_auth');
        
        return 'https://accounts.spotify.com/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'scope' => $scope,
            'redirect_uri' => $this->redirect_uri,
            'state' => $state
        ]);
    }

    public function handle_auth_callback($code) {
        $token_url = 'https://accounts.spotify.com/api/token';
        
        $response = wp_remote_post($token_url, [
            'body' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirect_uri,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret
            ]
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['access_token'])) {
            update_option('liza_spotify_access_token', $body['access_token']);
            update_option('liza_spotify_refresh_token', $body['refresh_token']);
            update_option('liza_spotify_token_expiry', time() + $body['expires_in']);
            return true;
        }

        return false;
    }

    public function refresh_access_token() {
        if (!$this->refresh_token) {
            return false;
        }

        $token_url = 'https://accounts.spotify.com/api/token';
        
        $response = wp_remote_post($token_url, [
            'body' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->refresh_token,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret
            ]
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['access_token'])) {
            update_option('liza_spotify_access_token', $body['access_token']);
            update_option('liza_spotify_token_expiry', time() + $body['expires_in']);
            
            if (isset($body['refresh_token'])) {
                update_option('liza_spotify_refresh_token', $body['refresh_token']);
            }
            
            return true;
        }

        return false;
    }

    public function get_user_profile() {
        if ($this->should_refresh_token()) {
            $this->refresh_access_token();
        }

        $response = wp_remote_get('https://api.spotify.com/v1/me', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->access_token
            ]
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    private function should_refresh_token() {
        $expiry = get_option('liza_spotify_token_expiry');
        return !$expiry || time() > $expiry - 300; // Refresh if within 5 minutes of expiry
    }

    public function get_access_token() {
        if ($this->should_refresh_token()) {
            $this->refresh_access_token();
        }
        return $this->access_token;
    }
} 