<?php

/**
 * liza Spotify Widget For Elementor
 *
 * @author            Nick Sirbiladze
 * @copyright         2022 Nick Sirbiladze
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Liza Spotify Widget For Elementor
 * Plugin URI:        https://stagg.live/spotify
 * Description:       Spotify Widget For Elementor
 * Version:           1.2.1
 * tested up to:      6.2.0
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * Author:            Nick Sirbiladze
 * Author URI:        https://nicolo.wtf
 * Text Domain:       liza-spotify
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

 if( ! defined( 'ABSPATH' ) ) exit();

 /**
 * Elementor Extension main CLass
 * @since 1.0.0
 * 
 * 
 * 
 */
final class liza_spotify {

    
    // Plugin version
    const VERSION = '1.1.0';

    // Minimum Elementor Version
    const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

    // Minimum PHP Version
    const MINIMUM_PHP_VERSION = '5.0';

    // Instance
    private static $_instance = null;

    /**
    * SIngletone Instance Method
    * @since 1.1.0
    */
    public static function instance() {
        if( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
    * Construct Method
    * @since 1.1.0
    */
    public function __construct() {
        // Call Constants Method
        $this->define_constants();
        // add_action( 'wp_enqueue_scripts', [ $this, 'scripts_styles' ] );
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    /**
    * Define Plugin Constants
    * @since 1.1.0
    */
    public function define_constants() {
        define( 'LIZA_SPOTIFY_PLUGIN_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );
        define( 'LIZA_SPOTIFY_PLUGIN_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
    }


    // /**
    // * Load Scripts & Styles
    // * @since 1.0.0
    // */
    // public function scripts_styles() {
       

    //     wp_register_style( 'liza-style', LIZA_SPOTIFY_PLUGIN_URL . 'assets/style.css', [], rand(), 'all' );
    //     wp_register_script( 'liza-script', LIZA_SPOTIFY_PLUGIN_URL . 'assets/public.js', [ 'jquery' ], rand(), true );


    // }
   
      
    /**
    * Initialize the plugin
    * @since 1.0.0
    */
    public function init() {
        // Check if the ELementor installed and activated
        if( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
            return;
        }

        if( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
            return;
        }

        if( ! version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
            return;
        }
        // add_action( 'admin_notices', [ $this, 'admin_notice_pay' ] );
        // add_action( 'elementor/init', [ $this, 'init_category' ] );
        add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
    }

    /**
    * Init Widgets
    * @since 1.0.0
    */
    public function init_widgets() {
        require_once LIZA_SPOTIFY_PLUGIN_PATH . '/widgets/spotify-widget.php';
    }
    
    // /**
    // * Init Category Section
    // * @since 1.0.0
    // */
    // public function init_category() {
    //     \Elementor\Plugin::instance()->elements_manager->add_category(
    //         'liza-spotify-category',
    //         [
    //             'title' => 'Liza Spotify'
    //         ],
    //         1
    //     );
    // }
    /**
    * Admin Notice
    * Warning when the site doesn't have Elementor installed or activated
    * @since 1.0.0
    */
    public function admin_notice_missing_main_plugin() {

        $plugin = 'elementor/elementor.php';
        $elementorPath = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
        if( isset( $_GET[ 'activate' ] ) ) unset( $_GET[ 'activate' ] );
        $message = sprintf(
            esc_html__( '%1$s requires %2$s to be installed and activated %3$s', 'liza-spotify' ),
            '<strong>'.esc_html__( 'liza Spotify Widget', 'liza-spotify' ).'</strong>',
            '<strong>'.esc_html__( 'Elementor', 'liza-spotify' ).'</strong>',
            '<strong>'.__(' <br><a href="'. $elementorPath .'" class="button-primary"> install Elementor</a>'). '</strong>',
        );

        printf( '<div class="notice notice-warning is-dimissible"><p>%1$s</p></div>', $message );
    }

    /**
    * Admin Notice
    * Warning when the site doesn't have a minimum required Elementor version.
    * @since 1.0.0
    */
    public function admin_notice_minimum_elementor_version() {
        if( isset( $_GET[ 'activate' ] ) ) unset( $_GET[ 'activate' ] );
        $message = sprintf(
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater', 'liza-spotify' ),
            '<strong>'.esc_html__( 'Liza Spotify Widget', 'liza-spotify' ).'</strong>',
            '<strong>'.esc_html__( 'Elementor', 'liza-spotify' ).'</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );

        printf( '<div class="notice notice-warning is-dimissible"><p>%1$s</p></div>', $message );
    }

    /**
    * Admin Notice
    * Warning when the site doesn't have a minimum required PHP version.
    * @since 1.0.0
    */
    public function admin_notice_minimum_php_version() {
        if( isset( $_GET[ 'activate' ] ) ) unset( $_GET[ 'activate' ] );
        $message = sprintf(
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater', 'liza-spotify' ),
            '<strong>'.esc_html__( 'liza-spotify', 'liza-spotify' ).'</strong>',
            '<strong>'.esc_html__( 'PHP', 'liza-spotify' ).'</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf( '<div class="notice notice-warning is-dimissible"><p>%1$s</p></div>', $message );
    }
    // public function admin_notice_pay() {
    //     if( isset( $_GET[ 'activate' ] ) ) unset( $_GET[ 'activate' ] );
    //     $donateLinkLiza = 'https://paypal.me/nikushasirbiladze?country.x=GE&locale.x=en_US';
    //     $message = sprintf(
    //         esc_html__( '%1$s %2$s %3$s', 'liza-spotify' ),
    //         '<strong>'.esc_html__( 'To keep the plugin developent going please', 'liza-spotify' ).'</strong>',
    //         '<strong>'.esc_html__( 'Donate Now', 'liza-spotify' ).'</strong>',
    //         '<strong>'.__(' <br><a href="'. $donateLinkLiza .'" class="notais">Donate Now</a>'). '</strong>',
    //         self::MINIMUM_PHP_VERSION
    //     );

    //     printf( '<div  data-dismissible="disable-notice-forever" class="notice notice-info is-dismissible"><p>%1$s</p></div>', $message );
    // }


}

    /* Dashboard Widget */
function liza_admin_dashboard_widget(){
    wp_add_dashboard_widget(
        'admin_dashboard_widget',
        'Liza Spotify Widget',
        'liza_dashboard_widget_callback'

    );
}
add_action('wp_dashboard_setup','liza_admin_dashboard_widget');
    
    $stylez = 'style="height: 1cm;"';
    function liza_dashboard_widget_callback(){
      echo '<div class="dash-wrap">';
       echo '<h1> Thank You For Using Liza Spotify Widget For Elementor';
      echo '</div>';
    echo '<b>Please</b> Consider  <a href="https://www.paypal.com/paypalme/nikushasirbiladze">Donation</a> for more Updates, Also Please <a href="https://wordpress.org/plugins/liza-spotify-widget-for-elementor/">Review Us on wordpress.org!</a>';
   
    }


    function get_song_liza() {
        /** These are the lyrics to Hello Dolly */
        $lyrics = "
        https://open.spotify.com/embed/track/3fOc9x06lKJBhz435mInlH
        https://open.spotify.com/embed/track/3tSmXSxaAnU1EPGKa6NytH
        https://open.spotify.com/embed/track/59WN2psjkt1tyaxjspN8fp
        https://open.spotify.com/embed/track/5ghIJDpPoe3CfHMGu71E6T
        https://open.spotify.com/embed/track/3ANDYGKbaqaKjciA0zTzTZ
        https://open.spotify.com/embed/track/3LpHzQU2CZzZJGdUWV79SI
        https://open.spotify.com/embed/track/2O7UGwJPVlix15Wn4sa5vw
        https://open.spotify.com/embed/track/08mG3Y1vljYA6bvDt4Wqkj
        https://open.spotify.com/embed/track/7LRMbd3LEoV5wZJvXT1Lwb
        https://open.spotify.com/embed/track/57bgtoPSgt236HzfBOd8kj
        https://open.spotify.com/embed/track/2zYzyRzz6pRmhPzyfMEC8s
        https://open.spotify.com/embed/track/28WmNsclKsrVmdv3tDmoYU
        https://open.spotify.com/embed/track/2PzU4IB8Dr6mxV3lHuaG34
        https://open.spotify.com/embed/track/7HKez549fwJQDzx3zLjHKC
        https://open.spotify.com/embed/track/2EqlS6tkEnglzr7tkKAAYD
        https://open.spotify.com/embed/track/4fUKE8EULjQdHF4zb0M8FO
        https://open.spotify.com/embed/track/0NWPxcsf5vdjdiFUI8NgkP
        https://open.spotify.com/embed/track/41wWrTueWew7qUU0Ru87Mp
        https://open.spotify.com/embed/track/1YHIX390WgL6fPmhZ1dLGz
        https://open.spotify.com/embed/track/5IyL3XOaRPpTgxVjRIAxXU
        https://open.spotify.com/embed/track/10Nmj3JCNoMeBQ87uw5j8k
        https://open.spotify.com/embed/track/2oaK4JLVnmRGIO9ytBE1bt
        https://open.spotify.com/embed/track/6mib3N4E8PZHAGQ3xy7bho
        https://open.spotify.com/embed/track/48UPSzbZjgc449aqz8bxox
        https://open.spotify.com/embed/track/6LVkntQPrL5GUZKgN6E1fC
        https://open.spotify.com/embed/track/64yrDBpcdwEdNY9loyEGbX
        https://open.spotify.com/embed/track/5XcZRgJv3zMhTqCyESjQrF
        https://open.spotify.com/embed/track/6b2oQwSGFkzsMtQruIWm2p
        https://open.spotify.com/embed/track/63OQupATfueTdZMWTxW03A
        https://open.spotify.com/embed/track/2ov8L95QD25TLpZAZPYWXL
        https://open.spotify.com/embed/track/6guXhXMAHU4QYaEsobnS6v
        https://open.spotify.com/embed/track/63T7DJ1AFDD6Bn8VzG6JE8
        https://open.spotify.com/embed/track/3Z2RsIdWm4BNbT0LsFBuoN
        https://open.spotify.com/embed/track/1ZgMsA55GIY7ICkQh5MILA
        https://open.spotify.com/embed/track/30bqVoKjX479ab90a8Pafp
        https://open.spotify.com/embed/track/6i4Qi1mJxXjqNIL9HfJhRs";
        $lyrics = explode( "\n", $lyrics );
        return wptexturize( $lyrics[ mt_rand( 0, count( $lyrics ) - 1 ) ] );
    }


function liza_admin_bar_link() {
    global $wp_admin_bar;
    if ( !is_super_admin() || !is_admin_bar_showing() )
        return;
    $wp_admin_bar->add_menu( array(
    'id' => 'liza_link',
    'title' => __('Get random Song on Spotify'),
    'href' => __(get_song_liza()),
    'meta'  => array(
            'target' => '_blank',
        ),

    ) );
}
add_action('admin_bar_menu', 'liza_admin_bar_link',10000);


liza_spotify::instance();
