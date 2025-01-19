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

        $client = new Client();
        $current_track = $client->get_currently_playing();

        if ($current_track && isset($current_track['item'])) {
            wp_send_json_success($current_track);
        } else {
            wp_send_json_success(null);
        }
    }

    protected function render_track($track_data) {
        $item = $track_data['item'];
        $is_playing = $track_data['is_playing'];
        $progress_ms = $track_data['progress_ms'];
        $duration_ms = $item['duration_ms'];
        $progress_percent = ($progress_ms / $duration_ms) * 100;
        
        ?>
        <div class="track-info">
            <div class="track-artwork">
                <img src="<?php echo esc_url($item['album']['images'][0]['url']); ?>" alt="<?php echo esc_attr($item['name']); ?>">
            </div>
            <div class="track-details">
                <div class="track-name"><?php echo esc_html($item['name']); ?></div>
                <div class="track-artist">
                    <?php echo esc_html(implode(', ', array_map(function($artist) { 
                        return $artist['name']; 
                    }, $item['artists']))); ?>
                </div>
                <div class="track-album"><?php echo esc_html($item['album']['name']); ?></div>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo esc_attr($progress_percent); ?>%"></div>
                </div>
                <a href="<?php echo esc_url($item['external_urls']['spotify']); ?>" target="_blank" class="listen-now">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                    <?php _e('Listen Now', 'liza-spotify'); ?>
                </a>
            </div>
        </div>
        <?php
    }
} 