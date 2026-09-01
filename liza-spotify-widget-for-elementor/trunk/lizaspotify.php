<?php
/**
 * Liza Widget For Spotify and Elementor
 *
 * @author            NikushaSirbiladze/RuthlessWP
 * @copyright         2025 NikushaSirbiladze/RuthlessWP
 * @license           GPL-3.0-or-later
 *
 * @liza-spotify-widget-for-elementor
 * Plugin Name:       Liza Widget For Spotify and Elementor
 * Plugin URI:        https://ruthlesswp.com/spotify
 * Description:       Spotify widgets for Elementor and Gutenberg (block editor).
 * Version:           4.3.0
 * tested up to:      7.1
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Author:            NikushaSirbiladze/RuthlessWP
 * Author URI:        https://ruthlesswp.com
 * Text Domain:       liza-spotify-widget-for-elementor
 * License:           GPL v3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */

if (!defined('ABSPATH')) {
    exit;
}

if (function_exists('liza_spotify_fs')) {
    liza_spotify_fs()->set_basename(true, __FILE__);
} else {
    /**
     * DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE
     * `function_exists` CALL ABOVE TO PROPERLY WORK.
     */
    if (!function_exists('liza_spotify_fs')) {
        // Create a helper function for easy SDK access.
        function liza_spotify_fs() {
            global $liza_spotify_fs;

            if (!isset($liza_spotify_fs)) {
                // Include Freemius SDK.
                require_once dirname(__FILE__) . '/freemius/start.php';

                $liza_spotify_fs = fs_dynamic_init(array(
                    'id'                  => '17621',
                    'slug'               => 'liza-spotify-widget-for-elementor',
                    'type'               => 'plugin',
                    'public_key'         => 'pk_ab067d7d1f575920e999c45eda465',
                    'is_premium'         => false,
                    'has_premium_version' => true,
                    'has_addons'         => false,
                    'has_paid_plans'     => true,
                    'premium_suffix'      => 'Pro',
                    'is_org_compliant'   => true,
                    'menu' => array(
                        'slug'           => 'liza-spotify-settings',
                        'first-path'     => 'admin.php?page=liza-spotify-settings',
                        'parent'         => array(
                            'slug'       => 'liza-spotify-settings',
                        ),
                        'account'        => true,
                        'contact'        => false,
                        'support'        => true,
                        'network'        => true,
                        'pricing'        => true,
                    ),
                    'is_live'            => true,
                    'trial'              => array(
                        'days'               => 14,
                        'is_require_payment' => false,
                    ),
                    'textdomain'         => 'liza-spotify-widget-for-elementor',
                ));
            }

            return $liza_spotify_fs;
        }

        // Init Freemius.
        liza_spotify_fs();
        // Signal that SDK was initiated.
        do_action('liza_spotify_fs_loaded');

        define('LIZA_SPOTIFY_PATH', plugin_dir_path(__FILE__));
        define('LIZA_SPOTIFY_URL', plugin_dir_url(__FILE__));
        define('LIZA_SPOTIFY_VERSION', '4.3.0');

        // Activation/deactivation hooks must be registered at file-load time (not inside plugins_loaded)
        register_activation_hook(__FILE__, function () {
            add_option('liza_spotify_client_id', '');
            add_option('liza_spotify_client_secret', '');
            add_option('liza_spotify_access_token', '');
            add_option('liza_spotify_refresh_token', '');
            add_option('liza_spotify_token_expiry', '');
            add_option('liza_spotify_onboarding_complete', 0);
            add_option('liza_spotify_onboarding_dismissed', 0);
        });

        // Autoloader
        spl_autoload_register(function ($class) {
            $prefix = 'LizaSpotify\\';
            $base_dir = LIZA_SPOTIFY_PATH . 'includes/';

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            if (file_exists($file)) {
                require $file;
            }
        });

        // Initialize the plugin
        class LizaSpotify {
            private static $instance = null;

            public static function get_instance() {
                if (null === self::$instance) {
                    self::$instance = new self();
                }
                return self::$instance;
            }

            private function __construct() {
                add_action('plugins_loaded', [$this, 'init']);
            }

            public function init() {
                // Load plugin components (works with or without Elementor)
                $this->load_dependencies();
                $this->setup_hooks();

                // Gutenberg blocks are registered regardless of Elementor
                new \LizaSpotify\Blocks\BlockLoader();

                // Elementor widgets only when Elementor is installed and activated
                if (did_action('elementor/loaded') && class_exists('\Elementor\Plugin')) {
                    // Add Elementor widget category
                    add_action('elementor/elements/categories_registered', [$this, 'add_elementor_widget_category']);

                    // Initialize widget loader
                    new \LizaSpotify\Widgets\WidgetLoader();
                }
            }

            public function add_elementor_widget_category($elements_manager) {
                $elements_manager->add_category(
                    'liza-spotify',
                    [
                        'title' => __('Spotify Widgets', 'liza-spotify-widget-for-elementor'),
                        'icon' => 'eicon-spotify',
                    ]
                );
            }

            private function load_dependencies() {
                require_once LIZA_SPOTIFY_PATH . 'includes/Admin/Settings.php';
                require_once LIZA_SPOTIFY_PATH . 'includes/Admin/Onboarding.php';
                require_once LIZA_SPOTIFY_PATH . 'includes/Admin/MailMonkNotice.php';
                require_once LIZA_SPOTIFY_PATH . 'includes/SpotifyAPI/Client.php';
                require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/WidgetLoader.php';
                require_once LIZA_SPOTIFY_PATH . 'includes/Ajax/NowPlaying.php';
            }

            private function setup_hooks() {
                // Initialize admin settings and onboarding
                if (is_admin()) {
                    new \LizaSpotify\Admin\Settings();
                    new \LizaSpotify\Admin\Onboarding();
                    new \LizaSpotify\Admin\MailMonkNotice();
                }

                // Initialize AJAX handlers
                new \LizaSpotify\Ajax\NowPlaying();

                // Enqueue admin styles only on plugin pages
                add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
            }

            public function enqueue_admin_styles() {
                $screen = get_current_screen();
                if ( ! $screen || strpos( $screen->id, 'liza-spotify' ) === false ) {
                    return;
                }
                wp_enqueue_style(
                    'liza-spotify-admin',
                    LIZA_SPOTIFY_URL . 'assets/css/admin.css',
                    [],
                    LIZA_SPOTIFY_VERSION
                );
            }

            public function activate() {
                add_option('liza_spotify_client_id', '');
                add_option('liza_spotify_client_secret', '');
                add_option('liza_spotify_access_token', '');
                add_option('liza_spotify_refresh_token', '');
                add_option('liza_spotify_token_expiry', '');
                // Show onboarding notice to new installs (add_option is a no-op if already set)
                add_option('liza_spotify_onboarding_complete', 0);
                add_option('liza_spotify_onboarding_dismissed', 0);
            }

            public function deactivate() {
                // Cleanup if necessary
            }
        }

        // Initialize the plugin
        LizaSpotify::get_instance();
    }
} 