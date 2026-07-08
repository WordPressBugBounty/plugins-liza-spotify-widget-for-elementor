<?php
namespace LizaSpotify\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registers the Gutenberg blocks that mirror the Elementor widgets.
 *
 * Works independently of Elementor: blocks are always available in the
 * block editor, while the Elementor widgets keep loading only when
 * Elementor is active. When the pro plugin is also active it registers
 * its two exclusive blocks itself (with duplicate-registration guards).
 */
class BlockLoader {

    /**
     * Script handle for the block editor bundle. The pro plugin uses a
     * different handle ('liza-spotify-blocks-pro') so both can coexist.
     */
    const EDITOR_HANDLE = 'liza-spotify-blocks';

    /**
     * Block classes shipped by this plugin.
     */
    private $block_classes = [
        SpotifyEmbedBlock::class,
        SpotifyProfileBlock::class,
        SpotifyNowPlayingBlock::class,
        SpotifyArtistBlock::class,
        SpotifyPodcastBlock::class,
        AppleMusicEmbedBlock::class,
    ];

    public function __construct() {
        add_action( 'init', [ $this, 'register_blocks' ] );
        add_filter( 'block_categories_all', [ $this, 'register_block_category' ] );
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_assets' ] );
        add_action( 'enqueue_block_assets', [ $this, 'enqueue_editor_preview_styles' ] );
    }

    /**
     * Register asset handles + block types.
     */
    public function register_blocks() {
        $this->register_asset_handles();

        $registry = \WP_Block_Type_Registry::get_instance();

        foreach ( $this->block_classes as $class ) {
            if ( ! class_exists( $class ) ) {
                continue;
            }
            $block = new $class();

            // Guard against double registration when free + pro are both active.
            if ( $registry->is_registered( $block->get_full_name() ) ) {
                continue;
            }

            $styles = $block->get_style_handles();

            register_block_type( $block->get_full_name(), [
                'api_version'     => 2,
                'attributes'      => $block->get_attributes(),
                'render_callback' => [ $block, 'render_callback' ],
                'editor_script'   => self::EDITOR_HANDLE,
                'style'           => ! empty( $styles ) ? $styles[0] : null,
            ] );
        }
    }

    /**
     * Register the CSS handles the blocks rely on. Uses the same files as
     * the Elementor widgets so both builders look identical. Handles already
     * registered elsewhere are kept; missing files are skipped.
     */
    private function register_asset_handles() {
        $styles = [
            'liza-spotify-embed'       => 'assets/css/spotify-embed.css',
            'liza-spotify-now-playing' => 'assets/css/spotify-now-playing.css',
            'liza-spotify-artist'      => 'assets/css/spotify-artist.css',
            'liza-spotify-podcast'     => 'assets/css/spotify-podcast.css',
            'liza-spotify-apple-music' => 'assets/css/apple-music-embed.css',
        ];

        foreach ( $styles as $handle => $path ) {
            if ( ! wp_style_is( $handle, 'registered' ) && file_exists( LIZA_SPOTIFY_PATH . $path ) ) {
                wp_register_style( $handle, LIZA_SPOTIFY_URL . $path, [], LIZA_SPOTIFY_VERSION );
            }
        }
    }

    /**
     * Add the "Spotify Widgets" category to the block inserter.
     */
    public function register_block_category( $categories ) {
        foreach ( $categories as $category ) {
            if ( isset( $category['slug'] ) && 'liza-spotify' === $category['slug'] ) {
                return $categories;
            }
        }

        array_splice( $categories, 1, 0, [
            [
                'slug'  => 'liza-spotify',
                'title' => __( 'Spotify Widgets', 'liza-spotify-widget-for-elementor' ),
                'icon'  => null,
            ],
        ] );

        return $categories;
    }

    /**
     * Block editor bundle (no build step — plain wp.element JS).
     */
    public function enqueue_editor_assets() {
        global $liza_spotify_fs;

        wp_enqueue_script(
            self::EDITOR_HANDLE,
            LIZA_SPOTIFY_URL . 'assets/js/blocks.js',
            [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'wp-i18n' ],
            LIZA_SPOTIFY_VERSION,
            true
        );

        wp_localize_script( self::EDITOR_HANDLE, 'lizaSpotifyBlocksData', [
            'isPremium'  => (bool) ( $liza_spotify_fs && ( $liza_spotify_fs->can_use_premium_code() || $liza_spotify_fs->is_trial() ) ),
            'upgradeUrl' => $liza_spotify_fs ? esc_url_raw( $liza_spotify_fs->get_upgrade_url() ) : '',
        ] );
    }

    /**
     * Make sure the widget styles reach the editor (including the iframed
     * post editor) so ServerSideRender previews look like the frontend.
     */
    public function enqueue_editor_preview_styles() {
        if ( ! is_admin() ) {
            return;
        }
        foreach ( $this->block_classes as $class ) {
            if ( ! class_exists( $class ) ) {
                continue;
            }
            $block = new $class();
            foreach ( $block->get_style_handles() as $handle ) {
                wp_enqueue_style( $handle );
            }
        }
        wp_enqueue_style( 'dashicons' );
    }
}
