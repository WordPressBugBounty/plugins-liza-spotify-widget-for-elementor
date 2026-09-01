<?php
namespace LizaSpotify\Blocks;

use LizaSpotify\SpotifyAPI\Client;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gutenberg version of the Spotify Now Playing widget (premium).
 */
class SpotifyNowPlayingBlock extends AbstractBlock {

    public function get_name() {
        return 'spotify-now-playing';
    }

    public function get_title() {
        return __( 'Spotify Now Playing', 'liza-spotify-widget-for-elementor' );
    }

    public function is_premium() {
        return true;
    }

    public function get_style_handles() {
        return [ 'liza-spotify-now-playing' ];
    }

    public function get_attributes() {
        return [
            'layout'            => [ 'type' => 'string', 'default' => 'card' ],
            'showArtwork'       => [ 'type' => 'boolean', 'default' => true ],
            'showListenNow'     => [ 'type' => 'boolean', 'default' => true ],
            'refreshInterval'   => [ 'type' => 'number', 'default' => 5 ],
            'placeholderText'   => [ 'type' => 'string', 'default' => '' ],
            // Style attributes (defaults match the Elementor controls)
            'backgroundColor'   => [ 'type' => 'string', 'default' => '#282828' ],
            'borderRadius'      => [ 'type' => 'number', 'default' => 8 ],
            'padding'           => [ 'type' => 'number', 'default' => 20 ],
            'artworkSize'       => [ 'type' => 'number', 'default' => 100 ],
            'trackNameColor'    => [ 'type' => 'string', 'default' => '#FFFFFF' ],
            'artistColor'       => [ 'type' => 'string', 'default' => '#FFFFFF' ],
            'albumColor'        => [ 'type' => 'string', 'default' => '#FFFFFF' ],
            'placeholderColor'  => [ 'type' => 'string', 'default' => '#FFFFFF' ],
            'progressHeight'    => [ 'type' => 'number', 'default' => 4 ],
            'progressBgColor'   => [ 'type' => 'string', 'default' => 'rgba(255, 255, 255, 0.1)' ],
            'progressColor'     => [ 'type' => 'string', 'default' => '#1DB954' ],
            'buttonBgColor'     => [ 'type' => 'string', 'default' => '#1DB954' ],
            'buttonTextColor'   => [ 'type' => 'string', 'default' => '#FFFFFF' ],
            'buttonHoverBgColor' => [ 'type' => 'string', 'default' => '#1ed760' ],
        ];
    }

    public function render( $attributes ) {
        $client        = new Client();
        $current_track = $client->get_currently_playing();

        $uid         = wp_unique_id( 'liza-spotify-now-playing-' );
        $placeholder = '' !== trim( (string) $attributes['placeholderText'] )
            ? $attributes['placeholderText']
            : __( 'No track currently playing', 'liza-spotify-widget-for-elementor' );
        $refresh     = max( 1, min( 60, (int) $attributes['refreshInterval'] ) );

        $layout = isset( $attributes['layout'] ) && in_array( $attributes['layout'], [ 'compact', 'inline' ], true )
            ? $attributes['layout']
            : 'card';

        $show_artwork = $this->truthy( $attributes['showArtwork'] ) && 'inline' !== $layout;
        $show_listen  = $this->truthy( $attributes['showListenNow'] );

        if ( 'inline' === $layout ) {
            // Inline text inherits the surrounding typography and colors.
            $css = '';
        } else {
            $rules = [
                '&' => [ 'background-color' => $this->css_color( $attributes['backgroundColor'] ) ],
                '.track-name'   => [ 'color' => $this->css_color( $attributes['trackNameColor'] ) ],
                '.track-artist' => [ 'color' => $this->css_color( $attributes['artistColor'] ) ],
                '.track-album'  => [ 'color' => $this->css_color( $attributes['albumColor'] ) ],
                '.no-track-playing' => [ 'color' => $this->css_color( $attributes['placeholderColor'] ) ],
                '.progress-bar' => [ 'background-color' => $this->css_color( $attributes['progressBgColor'] ) ],
                '.progress-bar .progress' => [ 'background-color' => $this->css_color( $attributes['progressColor'] ) ],
                '.listen-now' => [
                    'background-color' => $this->css_color( $attributes['buttonBgColor'] ),
                    'color'            => $this->css_color( $attributes['buttonTextColor'] ),
                ],
                '.listen-now:hover' => [ 'background-color' => $this->css_color( $attributes['buttonHoverBgColor'] ) ],
            ];

            // Structural values only apply to the card layout; compact uses the stylesheet preset.
            if ( 'card' === $layout ) {
                $rules['&']['border-radius'] = max( 0, (int) $attributes['borderRadius'] ) . 'px';
                $rules['&']['padding']       = max( 0, (int) $attributes['padding'] ) . 'px';
                $rules['.track-artwork img'] = [
                    'width'  => max( 40, (int) $attributes['artworkSize'] ) . 'px',
                    'height' => max( 40, (int) $attributes['artworkSize'] ) . 'px',
                ];
                $rules['.progress-bar']['height'] = max( 1, (int) $attributes['progressHeight'] ) . 'px';
            }

            $css = $this->build_scoped_css( '#' . $uid, $rules );
        }

        $html  = '<div class="wp-block-liza-spotify-now-playing">';
        $html .= '<div class="spotify-now-playing layout-' . esc_attr( $layout ) . '" id="' . esc_attr( $uid ) . '"'
            . ' data-refresh="' . esc_attr( $refresh ) . '"'
            . ' data-placeholder="' . esc_attr( $placeholder ) . '"'
            . ' data-show-artwork="' . ( $show_artwork ? '1' : '0' ) . '"'
            . ' data-show-listen="' . ( $show_listen ? '1' : '0' ) . '">';

        if ( $current_track && isset( $current_track['item'] ) ) {
            $html .= $this->render_track( $current_track, $show_artwork, $show_listen );
        } else {
            $html .= '<div class="no-track-playing">' . esc_html( $placeholder ) . '</div>';
        }

        $html .= '</div></div>';
        $html .= $css;
        $html .= $this->render_refresh_script( $uid );

        return $html;
    }

    /**
     * Same markup as SpotifyNowPlaying::render_track().
     */
    private function render_track( $track_data, $show_artwork, $show_listen ) {
        $item             = $track_data['item'];
        $progress_ms      = isset( $track_data['progress_ms'] ) ? (int) $track_data['progress_ms'] : 0;
        $duration_ms      = isset( $item['duration_ms'] ) && $item['duration_ms'] > 0 ? (int) $item['duration_ms'] : 1;
        $progress_percent = ( $progress_ms / $duration_ms ) * 100;

        $artists = isset( $item['artists'] ) && is_array( $item['artists'] )
            ? implode( ', ', array_map( function ( $artist ) {
                return isset( $artist['name'] ) ? $artist['name'] : '';
            }, $item['artists'] ) )
            : '';

        ob_start();
        ?>
        <div class="track-info">
            <?php if ( $show_artwork && ! empty( $item['album']['images'][0]['url'] ) ) : ?>
            <div class="track-artwork">
                <img src="<?php echo esc_url( $item['album']['images'][0]['url'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>">
            </div>
            <?php endif; ?>
            <div class="track-details">
                <div class="track-name"><?php echo esc_html( $item['name'] ); ?></div>
                <div class="track-artist"><?php echo esc_html( $artists ); ?></div>
                <div class="track-album"><?php echo esc_html( isset( $item['album']['name'] ) ? $item['album']['name'] : '' ); ?></div>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo esc_attr( $progress_percent ); ?>%"></div>
                </div>
                <?php if ( $show_listen && ! empty( $item['external_urls']['spotify'] ) ) : ?>
                <a href="<?php echo esc_url( $item['external_urls']['spotify'] ); ?>" target="_blank" rel="noopener noreferrer" class="listen-now">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                    <?php esc_html_e( 'Listen Now', 'liza-spotify-widget-for-elementor' ); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Per-instance vanilla JS refresher (no jQuery dependency, so it works on
     * Gutenberg-only sites). Rebuilds the track markup when playback starts
     * after the placeholder was shown.
     */
    private function render_refresh_script( $uid ) {
        $config = wp_json_encode( [
            'id'      => $uid,
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'spotify_now_playing' ),
            'listen'  => __( 'Listen Now', 'liza-spotify-widget-for-elementor' ),
        ] );

        return '<script>(function(){
            var cfg = ' . $config . ';
            var root = document.getElementById(cfg.id);
            if (!root) { return; }
            var interval = Math.max(5, parseInt(root.getAttribute("data-refresh"), 10) || 5) * 1000;
            var showArtwork = root.getAttribute("data-show-artwork") === "1";
            var showListen = root.getAttribute("data-show-listen") === "1";
            var svg = \'<svg viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>\';

            function buildTrackDom() {
                var artworkHtml = showArtwork ? \'<div class="track-artwork"><img src="" alt=""></div>\' : "";
                var listenHtml = showListen ? \'<a href="#" target="_blank" rel="noopener noreferrer" class="listen-now">\' + svg + " " + cfg.listen + "</a>" : "";
                root.innerHTML = \'<div class="track-info">\' + artworkHtml +
                    \'<div class="track-details"><div class="track-name"></div><div class="track-artist"></div><div class="track-album"></div>\' +
                    \'<div class="progress-bar"><div class="progress" style="width:0%"></div></div>\' + listenHtml + "</div></div>";
            }

            function showPlaceholder() {
                if (!root.querySelector(".no-track-playing")) {
                    root.innerHTML = "";
                    var div = document.createElement("div");
                    div.className = "no-track-playing";
                    div.textContent = root.getAttribute("data-placeholder") || "";
                    root.appendChild(div);
                }
            }

            function update(data) {
                if (!data || !data.item) { showPlaceholder(); return; }
                if (!root.querySelector(".track-info")) { buildTrackDom(); }
                var item = data.item;
                var pct = item.duration_ms > 0 ? (data.progress_ms / item.duration_ms) * 100 : 0;
                var img = root.querySelector(".track-artwork img");
                var art = item.album && item.album.images && item.album.images[0] ? item.album.images[0].url : "";
                if (img && art) { img.src = art; img.alt = item.name || ""; }
                var set = function(sel, text) { var n = root.querySelector(sel); if (n) { n.textContent = text; } };
                set(".track-name", item.name || "");
                set(".track-artist", (item.artists || []).map(function(a) { return a.name; }).join(", "));
                set(".track-album", item.album ? item.album.name : "");
                var progress = root.querySelector(".progress");
                if (progress) { progress.style.width = pct + "%"; }
                var listen = root.querySelector(".listen-now");
                if (listen && item.external_urls && item.external_urls.spotify) { listen.href = item.external_urls.spotify; }
            }

            function tick() {
                if (!document.getElementById(cfg.id)) { return; }
                var request = new XMLHttpRequest();
                request.open("POST", cfg.ajaxUrl, true);
                request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
                request.onloadend = function() {
                    try {
                        var res = JSON.parse(request.responseText);
                        if (res && res.success) { update(res.data); }
                    } catch (e) {}
                    setTimeout(tick, interval);
                };
                request.send("action=get_now_playing_data&nonce=" + encodeURIComponent(cfg.nonce));
            }

            setTimeout(tick, interval);
        })();</script>';
    }
}
