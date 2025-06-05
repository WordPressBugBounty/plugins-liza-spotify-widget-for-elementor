<?php
namespace LizaSpotifyWidget\SpotifyAPI;

class Client {
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $api_base = 'https://api.spotify.com/v1';
    private $auth_base = 'https://accounts.spotify.com';

    public function __construct() {
        try {
            $this->client_id = get_option('lizaspotifywidget_client_id');
            $this->client_secret = get_option('lizaspotifywidget_client_secret');
            $this->redirect_uri = admin_url('admin.php?page=lizaspotifywidget-settings');
        } catch (\Exception $e) {
            if (function_exists('lizaspotifywidget_write_log')) {
                lizaspotifywidget_write_log('Spotify Client Constructor Error: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    public function get_auth_url() {
        try {
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
        } catch (\Exception $e) {
            if (function_exists('lizaspotifywidget_write_log')) {
                lizaspotifywidget_write_log('Spotify Auth URL Error: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    public function handle_auth_callback($code) {
        try {
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
                if (function_exists('lizaspotifywidget_write_log')) {
                    lizaspotifywidget_write_log('Spotify Auth Callback Error: ' . $response->get_error_message());
                }
                return false;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            if (isset($data['access_token'])) {
                update_option('lizaspotifywidget_access_token', $data['access_token']);
                update_option('lizaspotifywidget_refresh_token', $data['refresh_token']);
                update_option('lizaspotifywidget_token_expiry', time() + $data['expires_in']);
                return true;
            }

            if (function_exists('lizaspotifywidget_write_log')) {
                lizaspotifywidget_write_log('Spotify Auth Callback Error: No access token in response');
            }
            return false;
        } catch (\Exception $e) {
            if (function_exists('lizaspotifywidget_write_log')) {
                lizaspotifywidget_write_log('Spotify Auth Callback Error: ' . $e->getMessage());
            }
            return false;
        }
    }

    public function refresh_token() {
        try {
            $refresh_token = get_option('lizaspotifywidget_refresh_token');
            if (!$refresh_token) {
                if (function_exists('lizaspotifywidget_write_log')) {
                    lizaspotifywidget_write_log('Spotify Refresh Token Error: No refresh token found');
                }
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
                if (function_exists('lizaspotifywidget_write_log')) {
                    lizaspotifywidget_write_log('Spotify Refresh Token Error: ' . $response->get_error_message());
                }
                return false;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            if (isset($data['access_token'])) {
                update_option('lizaspotifywidget_access_token', $data['access_token']);
                update_option('lizaspotifywidget_token_expiry', time() + $data['expires_in']);
                if (isset($data['refresh_token'])) {
                    update_option('lizaspotifywidget_refresh_token', $data['refresh_token']);
                }
                return true;
            }

            if (function_exists('lizaspotifywidget_write_log')) {
                lizaspotifywidget_write_log('Spotify Refresh Token Error: No access token in response');
            }
            return false;
        } catch (\Exception $e) {
            if (function_exists('lizaspotifywidget_write_log')) {
                lizaspotifywidget_write_log('Spotify Refresh Token Error: ' . $e->getMessage());
            }
            return false;
        }
    }

    public function get_user_profile() {
        return $this->make_request('GET', '/me');
    }

    public function get_currently_playing() {
        return $this->make_request('GET', '/me/player/currently-playing');
    }

    public function get_artist($artist_id) {
        return $this->make_request('GET', '/artists/' . $artist_id);
    }

    public function get_artist_top_tracks($artist_id, $market = 'US') {
        return $this->make_request('GET', '/artists/' . $artist_id . '/top-tracks', ['market' => $market]);
    }

    public function search($query, $type = 'track', $limit = 5) {
        return $this->make_request('GET', '/search', [
            'q' => $query,
            'type' => $type,
            'limit' => $limit
        ]);
    }

    private function make_request($method, $endpoint, $params = array()) {
        try {
            if (time() > get_option('lizaspotifywidget_token_expiry', 0)) {
                if (!$this->refresh_token()) {
                    if (function_exists('lizaspotifywidget_write_log')) {
                        lizaspotifywidget_write_log('Spotify Request Error: Failed to refresh token');
                    }
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
                    'Authorization' => 'Bearer ' . get_option('lizaspotifywidget_access_token'),
                    'Content-Type' => 'application/json'
                )
            );

            if (!empty($params) && $method !== 'GET') {
                $args['body'] = json_encode($params);
            }

            $response = wp_remote_request($url, $args);

            if (is_wp_error($response)) {
                if (function_exists('lizaspotifywidget_write_log')) {
                    lizaspotifywidget_write_log('Spotify Request Error: ' . $response->get_error_message());
                }
                return false;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            if (wp_remote_retrieve_response_code($response) !== 200) {
                if (function_exists('lizaspotifywidget_write_log')) {
                    lizaspotifywidget_write_log('Spotify Request Error: Invalid response code ' . wp_remote_retrieve_response_code($response));
                }
                return false;
            }

            return $data;
        } catch (\Exception $e) {
            if (function_exists('lizaspotifywidget_write_log')) {
                lizaspotifywidget_write_log('Spotify Request Error: ' . $e->getMessage());
            }
            return false;
        }
    }
} 