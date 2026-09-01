<?php
namespace LizaSpotify\Blocks;

use LizaSpotify\SpotifyAPI\Client;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gutenberg version of the Spotify Podcast Episodes widget.
 */
class SpotifyPodcastBlock extends AbstractBlock {

    public function get_name() {
        return 'spotify-podcast';
    }

    public function get_title() {
        return __( 'Spotify Podcast Episodes', 'liza-spotify-widget-for-elementor' );
    }

    public function get_style_handles() {
        return [ 'liza-spotify-podcast' ];
    }

    public function get_attributes() {
        return [
            'showId'          => [ 'type' => 'string', 'default' => '' ],
            'market'          => [ 'type' => 'string', 'default' => 'US' ],
            'layout'          => [ 'type' => 'string', 'default' => 'list' ],
            'episodesCount'   => [ 'type' => 'number', 'default' => 5 ],
            'showHeader'      => [ 'type' => 'boolean', 'default' => true ],
            'showCover'       => [ 'type' => 'boolean', 'default' => true ],
            'showPublisher'   => [ 'type' => 'boolean', 'default' => true ],
            'showFollow'      => [ 'type' => 'boolean', 'default' => true ],
            'showArtwork'     => [ 'type' => 'boolean', 'default' => true ],
            'showDescription' => [ 'type' => 'boolean', 'default' => true ],
            'showDate'        => [ 'type' => 'boolean', 'default' => true ],
            'showDuration'    => [ 'type' => 'boolean', 'default' => true ],
            // Style attributes — empty means "use the stylesheet tokens"
            'accentColor'     => [ 'type' => 'string', 'default' => '' ],
            'backgroundColor' => [ 'type' => 'string', 'default' => '' ],
            'textColor'       => [ 'type' => 'string', 'default' => '' ],
            'borderRadius'    => [ 'type' => 'number', 'default' => 20 ],
            'padding'         => [ 'type' => 'number', 'default' => 24 ],
            'artworkSize'     => [ 'type' => 'number', 'default' => 56 ],
        ];
    }

    public function render( $attributes ) {
        // Extract show ID from URL, URI, or use as-is (same logic as the widget).
        $show_id = sanitize_text_field( (string) $attributes['showId'] );
        if ( false !== strpos( $show_id, 'spotify.com/show/' ) ) {
            if ( preg_match( '/show\/([a-zA-Z0-9]+)/', $show_id, $matches ) ) {
                $show_id = $matches[1];
            } else {
                $show_id = '';
            }
        }
        if ( 0 === strpos( $show_id, 'spotify:show:' ) ) {
            $show_id = substr( $show_id, strlen( 'spotify:show:' ) );
        }

        if ( '' === $show_id ) {
            return '<div class="spotify-podcast-widget-error">' . esc_html__( 'Please enter a valid Spotify Show ID or URL.', 'liza-spotify-widget-for-elementor' ) . '</div>';
        }

        $client = new Client();
        $market = (string) $attributes['market'];
        $show   = $client->get_show( $show_id, $market );
        if ( ! $show || ! is_array( $show ) ) {
            return '<div class="spotify-podcast-widget-error">' . esc_html__( 'Unable to fetch podcast data.', 'liza-spotify-widget-for-elementor' ) . '</div>';
        }

        $count    = max( 1, min( 50, (int) $attributes['episodesCount'] ?: 5 ) );
        $episodes = $client->get_latest_episodes( $show_id, $market, $count );

        $uid    = wp_unique_id( 'liza-spotify-podcast-' );
        $layout = in_array( $attributes['layout'], [ 'list', 'grid' ], true ) ? $attributes['layout'] : 'list';

        $root_rules = [
            '--lsw-accent'     => $this->css_color( $attributes['accentColor'] ),
            '--lsw-text'       => $this->css_color( $attributes['textColor'] ),
            'background-color' => $this->css_color( $attributes['backgroundColor'] ),
            'border-radius'    => max( 0, (int) $attributes['borderRadius'] ) . 'px',
            'padding'          => max( 0, (int) $attributes['padding'] ) . 'px',
        ];
        $rules = [ '&' => $root_rules ];
        if ( 'list' === $layout ) {
            $size = max( 40, min( 120, (int) $attributes['artworkSize'] ) );
            $rules['.episode-artwork'] = [
                'width'  => $size . 'px',
                'height' => $size . 'px',
            ];
        }
        $css = $this->build_scoped_css( '#' . $uid, $rules );

        $cover_url   = ! empty( $show['images'][0]['url'] ) ? $show['images'][0]['url'] : '';
        $show_url    = ! empty( $show['external_urls']['spotify'] ) ? $show['external_urls']['spotify'] : '';
        $date_format = get_option( 'date_format' );

        ob_start();
        ?>
        <div class="wp-block-liza-spotify-podcast">
            <div class="spotify-podcast-widget layout-<?php echo esc_attr( $layout ); ?>" id="<?php echo esc_attr( $uid ); ?>">
                <?php if ( $this->truthy( $attributes['showHeader'] ) ) : ?>
                <div class="podcast-header">
                    <?php if ( $this->truthy( $attributes['showCover'] ) && $cover_url ) : ?>
                    <img class="podcast-cover" src="<?php echo esc_url( $cover_url ); ?>"
                         alt="<?php echo esc_attr( $show['name'] ); ?>" loading="lazy" decoding="async">
                    <?php endif; ?>
                    <div class="podcast-meta">
                        <span class="podcast-label"><?php esc_html_e( 'Podcast', 'liza-spotify-widget-for-elementor' ); ?></span>
                        <h3 class="podcast-name"><?php echo esc_html( $show['name'] ); ?></h3>
                        <?php if ( $this->truthy( $attributes['showPublisher'] ) && ! empty( $show['publisher'] ) ) : ?>
                        <span class="podcast-publisher"><?php echo esc_html( $show['publisher'] ); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ( $this->truthy( $attributes['showFollow'] ) && $show_url ) : ?>
                    <a href="<?php echo esc_url( $show_url ); ?>" target="_blank" rel="noopener noreferrer" class="follow-button">
                        <svg viewBox="0 0 24 24" class="spotify-icon">
                            <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                        </svg>
                        <?php esc_html_e( 'Follow', 'liza-spotify-widget-for-elementor' ); ?>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ( empty( $episodes ) ) : ?>
                <div class="spotify-podcast-widget-error"><?php esc_html_e( 'No episodes are currently available for this market.', 'liza-spotify-widget-for-elementor' ); ?></div>
                <?php else : ?>
                <div class="episodes-list">
                    <?php foreach ( $episodes as $episode ) :
                        $episode_url = ! empty( $episode['external_urls']['spotify'] ) ? $episode['external_urls']['spotify'] : $show_url;
                        $artwork_url = ! empty( $episode['images'][0]['url'] ) ? $episode['images'][0]['url'] : $cover_url;
                        ?>
                    <a class="episode-item" href="<?php echo esc_url( $episode_url ); ?>" target="_blank" rel="noopener noreferrer">
                        <?php if ( $this->truthy( $attributes['showArtwork'] ) && $artwork_url ) : ?>
                        <img class="episode-artwork" src="<?php echo esc_url( $artwork_url ); ?>"
                             alt="<?php echo esc_attr( $episode['name'] ); ?>" loading="lazy" decoding="async">
                        <?php endif; ?>
                        <div class="episode-info">
                            <span class="episode-name"><?php echo esc_html( $episode['name'] ); ?></span>
                            <?php if ( $this->truthy( $attributes['showDescription'] ) && ! empty( $episode['description'] ) ) : ?>
                            <p class="episode-description"><?php echo esc_html( wp_trim_words( $episode['description'], 22, '…' ) ); ?></p>
                            <?php endif; ?>
                            <?php
                            $show_date     = $this->truthy( $attributes['showDate'] ) && ! empty( $episode['release_date'] );
                            $show_duration = $this->truthy( $attributes['showDuration'] ) && ! empty( $episode['duration_ms'] );
                            if ( $show_date || $show_duration ) : ?>
                            <div class="episode-meta">
                                <?php if ( $show_date ) : ?>
                                <span class="episode-date"><?php echo esc_html( date_i18n( $date_format, strtotime( $episode['release_date'] ) ) ); ?></span>
                                <?php endif; ?>
                                <?php if ( $show_date && $show_duration ) : ?>
                                <span class="episode-dot" aria-hidden="true">&middot;</span>
                                <?php endif; ?>
                                <?php if ( $show_duration ) : ?>
                                <span class="episode-duration"><?php echo esc_html( $this->format_duration( $episode['duration_ms'] ) ); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <span class="episode-play" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean() . $css;
    }

    private function format_duration( $duration_ms ) {
        $total_min = (int) round( (int) $duration_ms / 60000 );
        if ( $total_min < 1 ) {
            return __( '1 min', 'liza-spotify-widget-for-elementor' );
        }
        $hours   = intdiv( $total_min, 60 );
        $minutes = $total_min % 60;
        if ( $hours > 0 ) {
            /* translators: 1: hours, 2: minutes */
            return sprintf( __( '%1$d hr %2$d min', 'liza-spotify-widget-for-elementor' ), $hours, $minutes );
        }
        /* translators: %d: minutes */
        return sprintf( __( '%d min', 'liza-spotify-widget-for-elementor' ), $minutes );
    }
}
