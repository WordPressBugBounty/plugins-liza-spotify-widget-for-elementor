<?php
namespace LizaSpotify\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gutenberg version of the Spotify Embed widget.
 */
class SpotifyEmbedBlock extends AbstractBlock {

    public function get_name() {
        return 'spotify-embed';
    }

    public function get_title() {
        return __( 'Spotify Embed', 'liza-spotify-widget-for-elementor' );
    }

    public function get_style_handles() {
        return [ 'liza-spotify-embed' ];
    }

    public function get_attributes() {
        return [
            'spotifyUrl' => [ 'type' => 'string', 'default' => '' ],
            'theme'      => [ 'type' => 'string', 'default' => 'dark' ],
            'height'     => [ 'type' => 'number', 'default' => 380 ],
            'width'      => [ 'type' => 'number', 'default' => 100 ],
            'widthUnit'  => [ 'type' => 'string', 'default' => '%' ],
            'alignment'  => [ 'type' => 'string', 'default' => 'center' ],
        ];
    }

    public function render( $attributes ) {
        $spotify_url = isset( $attributes['spotifyUrl'] ) ? trim( $attributes['spotifyUrl'] ) : '';

        if ( '' === $spotify_url ) {
            $html  = '<div class="spotify-embed-placeholder">';
            $html .= '<div style="text-align: center; padding: 40px 20px;">';
            $html .= '<span style="font-size: 48px; display: block; margin-bottom: 15px;">🎵</span>';
            $html .= '<h3 style="margin: 0 0 10px 0; color: #333; font-size: 18px;">' . esc_html__( 'Spotify Content', 'liza-spotify-widget-for-elementor' ) . '</h3>';
            $html .= '<p style="margin: 0; color: #6c757d; font-size: 14px;">' . esc_html__( 'Enter a Spotify URL in the block settings to embed content.', 'liza-spotify-widget-for-elementor' ) . '</p>';
            $html .= '</div></div>';
            return $html;
        }

        if ( ! preg_match( '/^https:\/\/open\.spotify\.com\/(?:[a-z\-]+\/)?(track|album|artist|playlist|episode|show)\/([a-zA-Z0-9]+)/', $spotify_url, $matches ) ) {
            return '<div class="spotify-embed-error">' . esc_html__( 'Invalid Spotify URL format. Please enter a valid Spotify URL.', 'liza-spotify-widget-for-elementor' ) . '</div>';
        }

        $spotify_type = $matches[1];
        $spotify_id   = $matches[2];

        $embed_url = 'https://open.spotify.com/embed/' . $spotify_type . '/' . $spotify_id;
        $embed_url .= ( isset( $attributes['theme'] ) && 'light' === $attributes['theme'] ) ? '?theme=1' : '?theme=0';

        $height = isset( $attributes['height'] ) ? max( 80, (int) $attributes['height'] ) : 380;
        $width  = isset( $attributes['width'] ) ? (float) $attributes['width'] : 100;
        $unit   = ( isset( $attributes['widthUnit'] ) && 'px' === $attributes['widthUnit'] ) ? 'px' : '%';
        $align  = isset( $attributes['alignment'] ) && in_array( $attributes['alignment'], [ 'left', 'center', 'right' ], true )
            ? $attributes['alignment']
            : 'center';

        $wrapper_style = $this->inline_style( [
            'width'      => $width . $unit,
            'text-align' => $align,
            'margin'     => 'center' === $align ? '0 auto' : '',
        ] );

        $html  = '<div class="wp-block-liza-spotify-embed">';
        $html .= '<div class="spotify-embed-wrapper"' . $wrapper_style . '>';
        $html .= '<iframe src="' . esc_url( $embed_url ) . '" width="100%" height="' . esc_attr( $height ) . '" frameborder="0" allowtransparency="true" allow="encrypted-media" loading="lazy" title="' . esc_attr__( 'Spotify player', 'liza-spotify-widget-for-elementor' ) . '"></iframe>';
        $html .= '</div></div>';

        return $html;
    }
}
