<?php
namespace LizaSpotify\Blocks;

use LizaSpotify\SpotifyAPI\Client;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gutenberg version of the Spotify Artist widget (premium).
 */
class SpotifyArtistBlock extends AbstractBlock {

    public function get_name() {
        return 'spotify-artist';
    }

    public function get_title() {
        return __( 'Spotify Artist', 'liza-spotify-widget-for-elementor' );
    }

    public function is_premium() {
        return true;
    }

    public function get_style_handles() {
        return [ 'liza-spotify-artist' ];
    }

    public function get_attributes() {
        return [
            'artistId'          => [ 'type' => 'string', 'default' => '' ],
            'layout'            => [ 'type' => 'string', 'default' => 'horizontal' ],
            'showImage'         => [ 'type' => 'boolean', 'default' => true ],
            // Style attributes (defaults match the Elementor controls)
            'backgroundColor'   => [ 'type' => 'string', 'default' => '#282828' ],
            'textColor'         => [ 'type' => 'string', 'default' => '#FFFFFF' ],
            'borderRadius'      => [ 'type' => 'number', 'default' => 8 ],
            'padding'           => [ 'type' => 'number', 'default' => 20 ],
            'imageSize'         => [ 'type' => 'number', 'default' => 150 ],
            'imageBorderRadius' => [ 'type' => 'number', 'default' => 50 ],
            'buttonBgColor'     => [ 'type' => 'string', 'default' => '#1DB954' ],
            'buttonTextColor'   => [ 'type' => 'string', 'default' => '#FFFFFF' ],
            'buttonHoverBgColor' => [ 'type' => 'string', 'default' => '#1ed760' ],
            'buttonHoverTextColor' => [ 'type' => 'string', 'default' => '#FFFFFF' ],
            'buttonBorderRadius' => [ 'type' => 'number', 'default' => 20 ],
        ];
    }

    public function render( $attributes ) {
        // Extract artist ID from URL, URI, or use as-is (same logic as the widget).
        $artist_id = sanitize_text_field( (string) $attributes['artistId'] );
        if ( false !== strpos( $artist_id, 'spotify.com/artist/' ) ) {
            if ( preg_match( '/artist\/([a-zA-Z0-9]+)/', $artist_id, $matches ) ) {
                $artist_id = $matches[1];
            } else {
                $artist_id = '';
            }
        }
        if ( 0 === strpos( $artist_id, 'spotify:artist:' ) ) {
            $artist_id = substr( $artist_id, strlen( 'spotify:artist:' ) );
        }

        if ( '' === $artist_id ) {
            return '<div class="spotify-artist-widget-error">' . esc_html__( 'Please enter a valid Spotify Artist ID or URL.', 'liza-spotify-widget-for-elementor' ) . '</div>';
        }

        $client = new Client();
        $artist = $client->get_artist( $artist_id );
        if ( ! $artist || ! is_array( $artist ) ) {
            return '<div class="spotify-artist-widget-error">' . esc_html__( 'Unable to fetch artist data.', 'liza-spotify-widget-for-elementor' ) . '</div>';
        }

        $uid    = wp_unique_id( 'liza-spotify-artist-' );
        $layout = in_array( $attributes['layout'], [ 'horizontal', 'vertical', 'compact' ], true ) ? $attributes['layout'] : 'horizontal';

        $css = $this->build_scoped_css( '#' . $uid, [
            '&' => [
                'background-color' => $this->css_color( $attributes['backgroundColor'] ),
                'color'            => $this->css_color( $attributes['textColor'] ),
                'border-radius'    => max( 0, (int) $attributes['borderRadius'] ) . 'px',
                'padding'          => max( 0, (int) $attributes['padding'] ) . 'px',
            ],
            '.artist-image img' => [
                'width'         => max( 50, (int) $attributes['imageSize'] ) . 'px',
                'height'        => max( 50, (int) $attributes['imageSize'] ) . 'px',
                'border-radius' => max( 0, (int) $attributes['imageBorderRadius'] ) . '%',
            ],
            '.follow-button' => [
                'background-color' => $this->css_color( $attributes['buttonBgColor'] ),
                'color'            => $this->css_color( $attributes['buttonTextColor'] ),
                'border-radius'    => max( 0, (int) $attributes['buttonBorderRadius'] ) . 'px',
            ],
            '.follow-button:hover' => [
                'background-color' => $this->css_color( $attributes['buttonHoverBgColor'] ),
                'color'            => $this->css_color( $attributes['buttonHoverTextColor'] ),
            ],
        ] );

        ob_start();
        ?>
        <div class="wp-block-liza-spotify-artist">
            <div class="spotify-artist-widget layout-<?php echo esc_attr( $layout ); ?>" id="<?php echo esc_attr( $uid ); ?>">
                <?php if ( $this->truthy( $attributes['showImage'] ) && ! empty( $artist['images'][0]['url'] ) ) : ?>
                <div class="artist-image">
                    <img src="<?php echo esc_url( $artist['images'][0]['url'] ); ?>"
                         alt="<?php echo esc_attr( $artist['name'] ); ?>">
                </div>
                <?php endif; ?>

                <div class="artist-info">
                    <h3 class="artist-name"><?php echo esc_html( $artist['name'] ); ?></h3>

                    <?php if ( ! empty( $artist['external_urls']['spotify'] ) ) : ?>
                    <a href="<?php echo esc_url( $artist['external_urls']['spotify'] ); ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="follow-button">
                        <svg viewBox="0 0 24 24" class="spotify-icon">
                            <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                        </svg>
                        <?php esc_html_e( 'Follow on Spotify', 'liza-spotify-widget-for-elementor' ); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean() . $css;
    }
}
