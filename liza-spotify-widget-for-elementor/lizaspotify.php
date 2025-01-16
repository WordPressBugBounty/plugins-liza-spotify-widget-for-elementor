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
 * Version:           2.0.0
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
            '<strong>' . esc_html__('Liza Spotify Widgets', 'liza-spotify') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'liza-spotify') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    private function load_dependencies() {
        // Load required files
        require_once LIZA_SPOTIFY_PATH . 'includes/Admin/Settings.php';
        require_once LIZA_SPOTIFY_PATH . 'includes/SpotifyAPI/Client.php';
        require_once LIZA_SPOTIFY_PATH . 'includes/Widgets/WidgetLoader.php';
    }

    private function setup_hooks() {
        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        // Initialize admin settings
        if (is_admin()) {
            new \LizaSpotify\Admin\Settings();
        }
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