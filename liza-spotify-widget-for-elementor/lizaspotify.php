<?php
/**
 * liza Spotify Widget For Elementor
 *
 * @author            RuthlessWP
 * @copyright         2025 RuthlessWP
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Liza Spotify Widget For Elementor
 * Plugin URI:        https://ruthlesswp.com/spotify
 * Description:       Spotify Widget For Elementor
 * Version:           2.0.2
 * tested up to:      6.7.1
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * Author:            RuthlessWP
 * Author URI:        https://ruthlesswp.com
 * Text Domain:       liza-spotify
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
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
        define('LIZA_SPOTIFY_VERSION', '2.0.0');

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
                // Check if Elementor is installed and activated
                if (!did_action('elementor/loaded')) {
                    add_action('admin_notices', [$this, 'elementor_missing_notice']);
                    return;
                }

                // Load plugin components
                $this->load_dependencies();
                $this->setup_hooks();

                // Initialize widgets
                if (class_exists('\Elementor\Plugin')) {
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
                        'title' => __('Spotify Widgets', 'liza-spotify'),
                        'icon' => 'eicon-spotify',
                    ]
                );
            }

            public function elementor_missing_notice() {
                if (isset($_GET['activate'])) {
                    unset($_GET['activate']);
                }

                $message = sprintf(
                    esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'liza-spotify'),
                    '<strong>' . esc_html__('Liza Spotify Widgets Pro', 'liza-spotify') . '</strong>',
                    '<strong>' . esc_html__('Elementor', 'liza-spotify') . '</strong>'
                );

                printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
            }

            private function load_dependencies() {
                // Load required files
                require_once LIZA_SPOTIFY_PATH . 'includes/Admin/Settings.php';
                require_once LIZA_SPOTIFY_PATH . 'includes/SpotifyAPI/Client.php';
                require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/WidgetLoader.php';
                require_once LIZA_SPOTIFY_PATH . 'includes/Ajax/NowPlaying.php';
            }

            private function setup_hooks() {
                // Register activation and deactivation hooks
                register_activation_hook(__FILE__, [$this, 'activate']);
                register_deactivation_hook(__FILE__, [$this, 'deactivate']);

                // Initialize admin settings
                if (is_admin()) {
                    new \LizaSpotify\Admin\Settings();
                }

                // Initialize AJAX handlers
                new \LizaSpotify\Ajax\NowPlaying();

                // Enqueue styles
                add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
                add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
            }

            public function enqueue_styles() {
                wp_enqueue_style(
                    'liza-spotify-now-playing',
                    LIZA_SPOTIFY_URL . 'assets/css/spotify-now-playing.css',
                    [],
                    LIZA_SPOTIFY_VERSION
                );

                wp_enqueue_style(
                    'liza-spotify-artist',
                    LIZA_SPOTIFY_URL . 'assets/css/spotify-artist.css',
                    [],
                    LIZA_SPOTIFY_VERSION
                );
            }

            public function enqueue_admin_styles() {
                wp_enqueue_style(
                    'liza-spotify-admin',
                    LIZA_SPOTIFY_URL . 'assets/css/admin.css',
                    [],
                    LIZA_SPOTIFY_VERSION
                );
            }

            public function show_promo_banner() {
                // Get the dismissal timestamp
                $dismissed_time = get_user_meta(get_current_user_id(), 'ruthless_promo_dismissed', true);
                
                // If dismissed and 2 days haven't passed yet, don't show
                if ($dismissed_time && (time() - $dismissed_time < 2 * DAY_IN_SECONDS)) {
                    return;
                }

                ?>
                <div class="notice ruthless-promo-notice is-dismissible">
                    <div class="ruthless-promo-content">
                        <span class="ruthless-promo-icon">🎨</span>
                        <div class="ruthless-promo-text">
                            <h3><?php _e('Enhance Your Elementor Website with Custom Fonts!', 'liza-spotify'); ?></h3>
                            <p><?php _e('Take your design to the next level with ', 'liza-spotify'); ?>
                            <a href="https://www.ruthlesswp.com/plugins/ruthless-custom-fonts-for-elementor" target="_blank">
                                <?php _e('Ruthless Custom Fonts for Elementor', 'liza-spotify'); ?>
                            </a>
                            <?php _e(' - Upload and use any custom font in your Elementor designs.', 'liza-spotify'); ?></p>
                        </div>
                        <a href="https://www.ruthlesswp.com/plugins/ruthless-custom-fonts-for-elementor" class="button button-primary" target="_blank">
                            <?php _e('Learn More', 'liza-spotify'); ?>
                        </a>
                    </div>
                </div>
                <script>
                jQuery(document).ready(function($) {
                    $(document).on('click', '.ruthless-promo-notice .notice-dismiss', function() {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'dismiss_ruthless_promo',
                                nonce: '<?php echo wp_create_nonce('dismiss_ruthless_promo'); ?>'
                            }
                        });
                    });
                });
                </script>
                <?php
            }

            public function activate() {
                // Create necessary database tables and options
                add_option('liza_spotify_client_id', '');
                add_option('liza_spotify_client_secret', '');
                add_option('liza_spotify_access_token', '');
                add_option('liza_spotify_refresh_token', '');
                add_option('liza_spotify_token_expiry', '');
            }

            public function deactivate() {
                // Cleanup if necessary
            }
        }

        // Initialize the plugin
        LizaSpotify::get_instance();
    }
} 