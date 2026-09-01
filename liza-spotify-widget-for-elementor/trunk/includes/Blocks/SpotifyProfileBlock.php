<?php
namespace LizaSpotify\Blocks;

use LizaSpotify\SpotifyAPI\Client;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gutenberg version of the Spotify Profile widget.
 */
class SpotifyProfileBlock extends AbstractBlock {

    public function get_name() {
        return 'spotify-profile';
    }

    public function get_title() {
        return __( 'Spotify Profile', 'liza-spotify-widget-for-elementor' );
    }

    public function get_attributes() {
        return [
            'showImage'            => [ 'type' => 'boolean', 'default' => true ],
            'showFollowers'        => [ 'type' => 'boolean', 'default' => true ],
            'showSpotifyLink'      => [ 'type' => 'boolean', 'default' => true ],
            'showButtonIcon'       => [ 'type' => 'boolean', 'default' => true ],
            'buttonIcon'           => [ 'type' => 'string', 'default' => 'dashicons-spotify' ],
            'buttonText'           => [ 'type' => 'string', 'default' => '' ],
            'iconPosition'         => [ 'type' => 'string', 'default' => 'after' ],
            'contentAlignment'     => [ 'type' => 'string', 'default' => 'center' ],
            // Style attributes (defaults match the Elementor controls)
            'backgroundColor'      => [ 'type' => 'string', 'default' => '#f9f9f9' ],
            'imageSize'            => [ 'type' => 'number', 'default' => 150 ],
            'imageBorderRadius'    => [ 'type' => 'number', 'default' => 50 ],
            'nameColor'            => [ 'type' => 'string', 'default' => '' ],
            'followersColor'       => [ 'type' => 'string', 'default' => '' ],
            'buttonBgColor'        => [ 'type' => 'string', 'default' => '#1DB954' ],
            'buttonTextColor'      => [ 'type' => 'string', 'default' => '#FFFFFF' ],
            'buttonHoverBgColor'   => [ 'type' => 'string', 'default' => '#1ed760' ],
            'buttonHoverTextColor' => [ 'type' => 'string', 'default' => '#FFFFFF' ],
            'buttonBorderRadius'   => [ 'type' => 'number', 'default' => 3 ],
        ];
    }

    public function render( $attributes ) {
        $client  = new Client();
        $profile = $client->get_user_profile();

        if ( ! $profile ) {
            return '<p>' . esc_html__( 'Please connect your Spotify account in the plugin settings.', 'liza-spotify-widget-for-elementor' ) . '</p>';
        }

        $uid   = wp_unique_id( 'liza-spotify-profile-' );
        $align = in_array( $attributes['contentAlignment'], [ 'left', 'center', 'right' ], true ) ? $attributes['contentAlignment'] : 'center';

        $allowed_icons = [
            'dashicons-spotify',
            'dashicons-external',
            'dashicons-arrow-right-alt',
            'dashicons-arrow-right',
            'dashicons-plus',
            'dashicons-controls-play',
        ];
        $icon = in_array( $attributes['buttonIcon'], $allowed_icons, true ) ? $attributes['buttonIcon'] : 'dashicons-spotify';

        $button_text = '' !== trim( (string) $attributes['buttonText'] )
            ? $attributes['buttonText']
            : __( 'Follow on Spotify', 'liza-spotify-widget-for-elementor' );

        $show_icon     = $this->truthy( $attributes['showButtonIcon'] );
        $icon_position = 'before' === $attributes['iconPosition'] ? 'before' : 'after';

        // Dashicons are needed on the frontend for the button icon.
        if ( $show_icon && $this->truthy( $attributes['showSpotifyLink'] ) ) {
            wp_enqueue_style( 'dashicons' );
        }

        $css = $this->build_scoped_css( '#' . $uid, [
            '&' => [
                'text-align'       => $align,
                'background-color' => $this->css_color( $attributes['backgroundColor'] ),
                'padding'          => '20px',
            ],
            '.profile-image'      => [ 'margin-bottom' => '15px' ],
            '.profile-image img'  => [
                'width'         => $this->css_length( $attributes['imageSize'], '150px' ),
                'height'        => $this->css_length( $attributes['imageSize'], '150px' ),
                'border-radius' => max( 0, (int) $attributes['imageBorderRadius'] ) . '%',
                'object-fit'    => 'cover',
            ],
            '.profile-name'       => [ 'color' => $this->css_color( $attributes['nameColor'] ) ],
            '.profile-followers'  => [ 'color' => $this->css_color( $attributes['followersColor'] ) ],
            '.profile-link a'     => [
                'background-color' => $this->css_color( $attributes['buttonBgColor'] ),
                'color'            => $this->css_color( $attributes['buttonTextColor'] ),
                'border-radius'    => max( 0, (int) $attributes['buttonBorderRadius'] ) . 'px',
                'padding'          => '10px 20px',
                'text-decoration'  => 'none',
                'display'          => 'inline-flex',
                'align-items'      => 'center',
                'justify-content'  => 'center',
                'gap'              => '5px',
                'transition'       => 'all 0.3s ease',
            ],
            '.profile-link a:hover' => [
                'background-color' => $this->css_color( $attributes['buttonHoverBgColor'] ),
                'color'            => $this->css_color( $attributes['buttonHoverTextColor'] ),
            ],
            '.profile-link .dashicons' => [
                'font-size' => '16px',
                'width'     => '16px',
                'height'    => '16px',
            ],
        ] );

        ob_start();
        ?>
        <div class="wp-block-liza-spotify-profile">
            <div class="spotify-profile-widget" id="<?php echo esc_attr( $uid ); ?>">
                <?php if ( $this->truthy( $attributes['showImage'] ) && ! empty( $profile['images'][0]['url'] ) ) : ?>
                    <div class="profile-image">
                        <img src="<?php echo esc_url( $profile['images'][0]['url'] ); ?>"
                             alt="<?php echo esc_attr( $profile['display_name'] ); ?>">
                    </div>
                <?php endif; ?>

                <h3 class="profile-name"><?php echo esc_html( $profile['display_name'] ); ?></h3>

                <?php if ( $this->truthy( $attributes['showFollowers'] ) && isset( $profile['followers']['total'] ) ) : ?>
                    <p class="profile-followers">
                        <?php
                        printf(
                            /* translators: %s: number of followers */
                            esc_html( _n( '%s Follower', '%s Followers', (int) $profile['followers']['total'], 'liza-spotify-widget-for-elementor' ) ),
                            esc_html( number_format_i18n( (int) $profile['followers']['total'] ) )
                        );
                        ?>
                    </p>
                <?php endif; ?>

                <?php if ( $this->truthy( $attributes['showSpotifyLink'] ) && ! empty( $profile['external_urls']['spotify'] ) ) : ?>
                    <p class="profile-link">
                        <a href="<?php echo esc_url( $profile['external_urls']['spotify'] ); ?>"
                           target="_blank"
                           rel="noopener noreferrer">
                            <?php if ( $show_icon && 'before' === $icon_position ) : ?>
                                <span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
                            <?php endif; ?>
                            <?php echo esc_html( $button_text ); ?>
                            <?php if ( $show_icon && 'after' === $icon_position ) : ?>
                                <span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
                            <?php endif; ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean() . $css;
    }
}
