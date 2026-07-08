<?php
namespace LizaSpotify\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gutenberg version of the Apple Music Embed widget (premium).
 */
class AppleMusicEmbedBlock extends AbstractBlock {

    public function get_name() {
        return 'apple-music-embed';
    }

    public function get_title() {
        return __( 'Apple Music Embed', 'liza-spotify-widget-for-elementor' );
    }

    public function is_premium() {
        return true;
    }

    public function get_style_handles() {
        // Registered only in the pro plugin; enqueue silently skips unknown handles.
        return [ 'liza-spotify-apple-music' ];
    }

    public function get_attributes() {
        return [
            'appleMusicUrl'     => [ 'type' => 'string', 'default' => '' ],
            'embedHeight'       => [ 'type' => 'number', 'default' => 450 ],
            'showTitle'         => [ 'type' => 'boolean', 'default' => true ],
            'customTitle'       => [ 'type' => 'string', 'default' => '' ],
            'showDescription'   => [ 'type' => 'boolean', 'default' => false ],
            'customDescription' => [ 'type' => 'string', 'default' => '' ],
            'showPreview'       => [ 'type' => 'boolean', 'default' => true ],
            // Style attributes (defaults match the Elementor controls)
            'backgroundColor'   => [ 'type' => 'string', 'default' => '#ffffff' ],
            'borderRadius'      => [ 'type' => 'number', 'default' => 8 ],
            'padding'           => [ 'type' => 'number', 'default' => 20 ],
            'titleColor'        => [ 'type' => 'string', 'default' => '#333333' ],
            'descriptionColor'  => [ 'type' => 'string', 'default' => '#666666' ],
            'embedBorderRadius' => [ 'type' => 'number', 'default' => 8 ],
        ];
    }

    public function render( $attributes ) {
        $url = trim( (string) $attributes['appleMusicUrl'] );

        if ( '' === $url ) {
            return '<div class="apple-music-error"><p>' . esc_html__( 'Please enter a valid Apple Music URL', 'liza-spotify-widget-for-elementor' ) . '</p></div>';
        }

        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) || ! preg_match( '/music\.apple\.com/', $url ) ) {
            return '<div class="apple-music-error"><p>' . esc_html__( 'Please enter a valid Apple Music URL', 'liza-spotify-widget-for-elementor' ) . '</p></div>';
        }

        $embed_url = $this->get_apple_music_embed_url( $url );
        if ( ! $embed_url ) {
            return '<div class="apple-music-error"><p>' . esc_html__( 'Invalid Apple Music URL format. Please check the URL and try again.', 'liza-spotify-widget-for-elementor' ) . '</p></div>';
        }

        $uid    = wp_unique_id( 'liza-apple-music-' );
        $height = max( 100, (int) $attributes['embedHeight'] );

        $css = $this->build_scoped_css( '#' . $uid, [
            '&' => [
                'background-color' => $this->css_color( $attributes['backgroundColor'] ),
                'border-radius'    => max( 0, (int) $attributes['borderRadius'] ) . 'px',
                'padding'          => max( 0, (int) $attributes['padding'] ) . 'px',
            ],
            '.apple-music-title'       => [ 'color' => $this->css_color( $attributes['titleColor'] ) ],
            '.apple-music-description' => [ 'color' => $this->css_color( $attributes['descriptionColor'] ) ],
            'iframe'                   => [ 'border-radius' => max( 0, (int) $attributes['embedBorderRadius'] ) . 'px' ],
        ] );

        $html  = '<div class="wp-block-liza-apple-music-embed">';
        $html .= '<div class="apple-music-embed" id="' . esc_attr( $uid ) . '">';

        if ( $this->truthy( $attributes['showTitle'] ) ) {
            $title = '' !== trim( (string) $attributes['customTitle'] )
                ? $attributes['customTitle']
                : $this->extract_title_from_url( $url );
            if ( $title ) {
                $html .= '<h3 class="apple-music-title">' . esc_html( $title ) . '</h3>';
            }
        }

        if ( $this->truthy( $attributes['showDescription'] ) && '' !== trim( (string) $attributes['customDescription'] ) ) {
            $html .= '<div class="apple-music-description">' . esc_html( $attributes['customDescription'] ) . '</div>';
        }

        if ( $this->truthy( $attributes['showPreview'] ) ) {
            $html .= $this->render_preview_info( $url );
        }

        $html .= '<iframe src="' . esc_url( $embed_url ) . '" width="100%" height="' . esc_attr( $height ) . 'px" frameborder="0" loading="lazy" title="' . esc_attr__( 'Apple Music player', 'liza-spotify-widget-for-elementor' ) . '" allow="autoplay *; encrypted-media *; fullscreen *; clipboard-write" sandbox="allow-forms allow-popups allow-same-origin allow-scripts allow-storage-access-by-user-activation allow-top-navigation-by-user-activation"></iframe>';
        $html .= '</div></div>';

        return $html . $css;
    }

    /**
     * Convert an Apple Music URL to its embed URL (same logic as the widget).
     */
    private function get_apple_music_embed_url( $url ) {
        // Song: https://music.apple.com/us/album/song-name/123456789?i=987654321
        if ( preg_match( '/music\.apple\.com\/([a-z]{2})\/album\/[^\/]+\/(\d+)\?i=(\d+)/', $url, $matches ) ) {
            return 'https://embed.music.apple.com/' . $matches[1] . '/album/' . $matches[2] . '?i=' . $matches[3];
        }

        // Album, legacy format: https://music.apple.com/us/album/album-name/id123456789
        if ( preg_match( '/music\.apple\.com\/([a-z]{2})\/album\/[^\/]+\/id(\d+)/', $url, $matches ) ) {
            return 'https://embed.music.apple.com/' . $matches[1] . '/album/' . $matches[2];
        }

        // Album, current format: https://music.apple.com/us/album/album-name/123456789
        if ( preg_match( '/music\.apple\.com\/([a-z]{2})\/album\/[^\/]+\/(\d+)(?:[?#]|$)/', $url, $matches ) ) {
            return 'https://embed.music.apple.com/' . $matches[1] . '/album/' . $matches[2];
        }

        // Playlist: https://music.apple.com/us/playlist/playlist-name/pl.u-123456789 (or curated pl.xxxx)
        if ( preg_match( '/music\.apple\.com\/([a-z]{2})\/playlist\/[^\/]+\/(pl\.[a-zA-Z0-9\-\.]+)/', $url, $matches ) ) {
            return 'https://embed.music.apple.com/' . $matches[1] . '/playlist/' . $matches[2];
        }

        return false;
    }

    /**
     * Extract a readable title from the URL (same logic as the widget).
     */
    private function extract_title_from_url( $url ) {
        if ( preg_match( '/\/([^\/]+)\/id\d+$/', $url, $matches )
            || preg_match( '/\/([^\/]+)\/\d+\?i=\d+$/', $url, $matches )
            || preg_match( '/\/([^\/]+)\/pl\.u-[a-zA-Z0-9]+$/', $url, $matches )
            || preg_match( '/\/([^\/]+)\/(?:id|pl\.u-)/', $url, $matches )
        ) {
            return ucwords( str_replace( '-', ' ', $matches[1] ) );
        }

        return __( 'Apple Music', 'liza-spotify-widget-for-elementor' );
    }

    private function render_preview_info( $url ) {
        $content_type = $this->get_content_type( $url );
        $country      = $this->extract_country_code( $url );

        $html  = '<div class="apple-music-preview"><div class="preview-info">';
        $html .= '<span class="content-type">' . esc_html( $content_type ) . '</span>';
        if ( $country ) {
            $html .= '<span class="country-code">' . esc_html( strtoupper( $country ) ) . '</span>';
        }
        $html .= '</div></div>';

        return $html;
    }

    private function get_content_type( $url ) {
        if ( false !== strpos( $url, '/album/' ) ) {
            if ( false !== strpos( $url, '?i=' ) ) {
                return __( 'Song', 'liza-spotify-widget-for-elementor' );
            }
            return __( 'Album', 'liza-spotify-widget-for-elementor' );
        }
        if ( false !== strpos( $url, '/playlist/' ) ) {
            return __( 'Playlist', 'liza-spotify-widget-for-elementor' );
        }
        return __( 'Music', 'liza-spotify-widget-for-elementor' );
    }

    private function extract_country_code( $url ) {
        if ( preg_match( '/music\.apple\.com\/([a-z]{2})\//', $url, $matches ) ) {
            return $matches[1];
        }
        return false;
    }
}
