<?php
namespace LizaSpotify\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Base class for Gutenberg blocks.
 *
 * Blocks are server-rendered so the frontend output is identical to the
 * matching Elementor widget (same markup, same CSS classes, same JS).
 */
abstract class AbstractBlock {

    /**
     * Block name without namespace, e.g. 'spotify-embed'.
     */
    abstract public function get_name();

    /**
     * Block attribute definitions (register_block_type format).
     */
    abstract public function get_attributes();

    /**
     * Render the block on the frontend / in ServerSideRender previews.
     *
     * @param array $attributes Attribute values merged with defaults by WP.
     * @return string HTML.
     */
    abstract public function render( $attributes );

    /**
     * Style handles to enqueue when the block renders.
     */
    public function get_style_handles() {
        return [];
    }

    /**
     * Script handles to enqueue when the block renders.
     */
    public function get_script_handles() {
        return [];
    }

    /**
     * Whether this block requires a premium license to render.
     */
    public function is_premium() {
        return false;
    }

    public function get_full_name() {
        return 'liza-spotify/' . $this->get_name();
    }

    /**
     * register_block_type render callback. Handles asset enqueueing and
     * premium gating so subclasses only implement render().
     */
    public function render_callback( $attributes ) {
        foreach ( $this->get_style_handles() as $handle ) {
            wp_enqueue_style( $handle );
        }
        foreach ( $this->get_script_handles() as $handle ) {
            wp_enqueue_script( $handle );
        }

        if ( $this->is_premium() && ! $this->premium_active() ) {
            return $this->premium_notice();
        }

        return $this->render( is_array( $attributes ) ? $attributes : [] );
    }

    /**
     * Mirrors the premium check used by the Elementor widgets.
     */
    protected function premium_active() {
        global $liza_spotify_fs;
        return $liza_spotify_fs && ( $liza_spotify_fs->can_use_premium_code() || $liza_spotify_fs->is_trial() );
    }

    /**
     * Same upsell box the Elementor widgets output for non-premium users.
     */
    protected function premium_notice() {
        global $liza_spotify_fs;

        $html  = '<div class="spotify-widget-premium-notice">';
        $html .= '<h3>' . esc_html__( 'Premium Feature', 'liza-spotify-widget-for-elementor' ) . '</h3>';
        $html .= '<p>' . sprintf(
            /* translators: %s: widget title */
            esc_html__( 'The %s block is only available in the premium version.', 'liza-spotify-widget-for-elementor' ),
            esc_html( $this->get_title() )
        ) . '</p>';
        if ( $liza_spotify_fs ) {
            $html .= '<a href="' . esc_url( $liza_spotify_fs->get_upgrade_url() ) . '" class="button button-primary" target="_blank">'
                . esc_html__( 'Upgrade to Premium', 'liza-spotify-widget-for-elementor' ) . '</a>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Human readable title used in the premium notice.
     */
    public function get_title() {
        return ucwords( str_replace( '-', ' ', $this->get_name() ) );
    }

    /**
     * Build a scoped <style> tag from selector => declarations pairs.
     * Selectors are prefixed with the instance scope (e.g. '#my-id').
     * Rules with empty declarations are skipped; returns '' when nothing remains.
     *
     * @param string $scope CSS selector that scopes the rules (e.g. '#my-id').
     * @param array  $rules selector => array of property => value pairs.
     */
    protected function build_scoped_css( $scope, array $rules ) {
        $css = '';
        foreach ( $rules as $selector => $props ) {
            $decls = [];
            foreach ( (array) $props as $prop => $value ) {
                if ( '' === $value || null === $value ) {
                    continue;
                }
                $decls[] = $prop . ':' . $value;
            }
            if ( empty( $decls ) ) {
                continue;
            }
            // '&' refers to the scope element itself.
            if ( '&' === $selector ) {
                $full_selector = $scope;
            } else {
                $full_selector = $scope . ' ' . $selector;
            }
            $css .= $full_selector . '{' . implode( ';', $decls ) . '}';
        }
        if ( '' === $css ) {
            return '';
        }
        return '<style>' . $css . '</style>';
    }

    /**
     * Helper: 'yes'/true/1 style truthy check for boolean attributes.
     */
    protected function truthy( $value ) {
        return true === $value || 'yes' === $value || 1 === $value || '1' === $value || 'true' === $value;
    }

    /**
     * Sanitize a CSS color value (hex, rgb(a), hsl(a), or CSS keyword).
     * Returns '' when the value is unsafe/empty.
     */
    protected function css_color( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }
        if ( preg_match( '/^#([0-9a-fA-F]{3,8})$/', $value ) ) {
            return $value;
        }
        if ( preg_match( '/^(rgb|rgba|hsl|hsla)\(\s*[\d.,%\s\/]+\)$/i', $value ) ) {
            return $value;
        }
        if ( preg_match( '/^[a-zA-Z]+$/', $value ) ) {
            return $value;
        }
        return '';
    }

    /**
     * Sanitize a CSS length; accepts number (treated as px) or number+unit.
     */
    protected function css_length( $value, $default = '' ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return $default;
        }
        if ( is_numeric( $value ) ) {
            return $value . 'px';
        }
        if ( preg_match( '/^-?[\d.]+(px|em|rem|%|vh|vw)$/', $value ) ) {
            return $value;
        }
        return $default;
    }

    /**
     * Compose a style="" attribute from property => value pairs, skipping empties.
     */
    protected function inline_style( array $props ) {
        $parts = [];
        foreach ( $props as $prop => $value ) {
            if ( '' === $value || null === $value ) {
                continue;
            }
            $parts[] = $prop . ':' . $value;
        }
        return empty( $parts ) ? '' : ' style="' . esc_attr( implode( ';', $parts ) ) . '"';
    }
}
