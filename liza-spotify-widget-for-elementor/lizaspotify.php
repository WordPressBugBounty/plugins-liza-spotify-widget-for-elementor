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
 * Requires Plugins:  elementor
 * Plugin URI:        https://ruthlesswp.com/spotify
 * Description:       Spotify Widget For Elementor
 * Version:           2.7 
 * tested up to:      6.8
 * Requires at least: 5.2
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

// Enable error reporting for debugging
if (!function_exists('lizaspotifywidget_write_log')) {
    function lizaspotifywidget_write_log($log) {
        // Only log if both WP_DEBUG and WP_DEBUG_LOG are enabled
        if (!defined('WP_DEBUG') || !WP_DEBUG || !defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
            return;
        }

        // Format the log message
        $message = is_array($log) || is_object($log) ? wp_json_encode($log) : $log;
        
        // Add timestamp and context using WordPress time functions
        $message = '[' . current_time('mysql') . '] Liza Spotify Widget: ' . $message;
        
        // Use WordPress debug log function instead of error_log
        if (function_exists('wp_debug_log')) {
            wp_debug_log($message, 'lizaspotifywidget');
        }
    }
}

// Define plugin constants
define('LIZASPOTIFYWIDGET_PATH', plugin_dir_path(__FILE__));
define('LIZASPOTIFYWIDGET_URL', plugin_dir_url(__FILE__));
define('LIZASPOTIFYWIDGET_VERSION', '2.4');

// Autoloader with error handling
spl_autoload_register(function ($class) {
    try {
        $prefix = 'LizaSpotifyWidget\\';
        $base_dir = LIZASPOTIFYWIDGET_PATH . 'includes/';
        $len = strlen($prefix);
        
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        } else {
            lizaspotifywidget_write_log('File not found: ' . $file);
        }
    } catch (Exception $e) {
        lizaspotifywidget_write_log('Autoloader Error: ' . $e->getMessage());
    }
});

// Handle Spotify OAuth callback
add_action('admin_init', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'liza-spotify-settings' && isset($_GET['code'])) {
        // Verify nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'spotify_auth')) {
            wp_die(esc_html__('Invalid authentication request', 'liza-spotify-widget-for-elementor'));
        }

        try {
            $client = new \LizaSpotifyWidget\SpotifyAPI\Client();
            $code = sanitize_text_field(wp_unslash($_GET['code']));
            $success = $client->handle_auth_callback($code);
            
            if ($success) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . 
                         esc_html__('Successfully connected to Spotify!', 'liza-spotify-widget-for-elementor') . 
                         '</p></div>';
                });
            } else {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error is-dismissible"><p>' . 
                         esc_html__('Failed to connect to Spotify. Please try again.', 'liza-spotify-widget-for-elementor') . 
                         '</p></div>';
                });
            }
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG === true) {
                lizaspotifywidget_write_log('Spotify OAuth Error: ' . $e->getMessage());
            }
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . 
                     /* translators: %s: Error message from Spotify API */
                     esc_html(sprintf(__('Spotify connection error: %s', 'liza-spotify-widget-for-elementor'), $e->getMessage())) . 
                     '</p></div>';
            });
        }
    }
});

// Initialize the plugin with error handling
add_action('plugins_loaded', function () {
    try {
        // Check if Elementor is installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', function() {
                // Verify nonce for admin requests
                if (isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'activate-plugin')) {
                    if (isset($_GET['activate'])) {
                        unset($_GET['activate']);
                    }
                    
                    $message = sprintf(
                        /* translators: 1: Plugin name, 2: Required plugin name */
                        esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'liza-spotify-widget-for-elementor'),
                        '<strong>' . esc_html__('Liza Spotify Widgets', 'liza-spotify-widget-for-elementor') . '</strong>',
                        '<strong>' . esc_html__('Elementor', 'liza-spotify-widget-for-elementor') . '</strong>'
                    );
                    
                    printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', wp_kses_post($message));
                }
            });
            return;
        }

        // Initialize the plugin
        LizaSpotifyWidget::get_instance();
    } catch (Exception $e) {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            lizaspotifywidget_write_log('Liza Spotify Initialization Error: ' . $e->getMessage());
        }
        add_action('admin_notices', function() use ($e) {
            printf(
                '<div class="notice notice-error is-dismissible"><p>%s</p></div>',
                /* translators: %s: Error message from plugin initialization */
                esc_html(sprintf(__('Liza Spotify Widgets Error: %s', 'liza-spotify-widget-for-elementor'), $e->getMessage()))
            );
        });
    }
}, 0);

/**
 * Main plugin class
 */
class LizaSpotifyWidget {
    private static $instance = null;
    private $settings = null;
    private $widget_loader = null;

    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        try {
            $this->init();
        } catch (Exception $e) {
            lizaspotifywidget_write_log('Constructor Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Initialize plugin
     */
    private function init() {
        try {
            // Load plugin components
            $this->load_dependencies();
            $this->setup_hooks();

            // Initialize widgets
            if (class_exists('\\Elementor\\Plugin')) {
                add_action('elementor/elements/categories_registered', [$this, 'add_elementor_widget_category']);
                $this->widget_loader = new \LizaSpotifyWidget\Widgets\WidgetLoader();
            }
        } catch (Exception $e) {
            lizaspotifywidget_write_log('Init Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add Elementor widget category
     */
    public function add_elementor_widget_category($elements_manager) {
        try {
            $elements_manager->add_category('lizaspotifywidget', [
                'title' => esc_html__('Spotify Widgets', 'liza-spotify-widget-for-elementor'),
                'icon'  => 'eicon-spotify',
            ]);
        } catch (Exception $e) {
            lizaspotifywidget_write_log('Category Error: ' . $e->getMessage());
        }
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        try {
            $required_files = [
                'includes/Admin/Settings.php',
                'includes/SpotifyAPI/Client.php',
                'includes/Widgets/WidgetLoader.php'
            ];

            foreach ($required_files as $file) {
                $file_path = LIZASPOTIFYWIDGET_PATH . $file;
                if (!file_exists($file_path)) {
                    throw new Exception(sprintf(
                        /* translators: %s: Path to the missing file */
                        __('Required file not found: %s', 'liza-spotify-widget-for-elementor'),
                        $file
                    ));
                }
                require_once $file_path;
            }

            if (is_admin()) {
                $this->settings = new \LizaSpotifyWidget\Admin\Settings();
            }
        } catch (Exception $e) {
            lizaspotifywidget_write_log('Dependencies Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Setup plugin hooks
     */
    private function setup_hooks() {
        try {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
            add_action('elementor/editor/before_enqueue_scripts', [$this, 'enqueue_editor_scripts']);
        } catch (Exception $e) {
            lizaspotifywidget_write_log('Hooks Error: ' . $e->getMessage());
        }
    }

    /**
     * Enqueue frontend styles
     */
    public function enqueue_styles() {
        try {
            wp_enqueue_style(
                'liza-spotify-embed',
                LIZASPOTIFYWIDGET_URL . 'assets/css/spotify-embed.css',
                [],
                LIZASPOTIFYWIDGET_VERSION
            );
        } catch (Exception $e) {
            lizaspotifywidget_write_log('Styles Error: ' . $e->getMessage());
        }
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles() {
        try {
            wp_enqueue_style(
                'liza-spotify-admin',
                LIZASPOTIFYWIDGET_URL . 'assets/css/admin.css',
                [],
                LIZASPOTIFYWIDGET_VERSION
            );
        } catch (Exception $e) {
            lizaspotifywidget_write_log('Admin Styles Error: ' . $e->getMessage());
        }
    }

    /**
     * Enqueue editor scripts
     */
    public function enqueue_editor_scripts() {
        try {
            wp_enqueue_script(
                'spotify-embed',
                LIZASPOTIFYWIDGET_URL . 'assets/js/spotify-embed.js',
                ['jquery'],
                LIZASPOTIFYWIDGET_VERSION,
                true
            );

            wp_localize_script('spotify-embed', 'spotifyConfig', [
                'clientId'     => get_option('lizaspotifywidget_client_id'),
                'clientSecret' => get_option('lizaspotifywidget_client_secret'),
                'i18n'         => [
                    'searching' => esc_html__('Searching...', 'liza-spotify-widget-for-elementor'),
                    'error'     => esc_html__('Error searching Spotify. Please try again.', 'liza-spotify-widget-for-elementor'),
                    'noResults' => esc_html__('No results found.', 'liza-spotify-widget-for-elementor'),
                ],
            ]);
        } catch (Exception $e) {
            lizaspotifywidget_write_log('Editor Scripts Error: ' . $e->getMessage());
        }
    }
}

// Register activation hook with error handling
register_activation_hook(__FILE__, function() {
    try {
        // Create necessary database tables and options
        add_option('lizaspotifywidget_client_id', '');
        add_option('lizaspotifywidget_client_secret', '');
        add_option('lizaspotifywidget_access_token', '');
        add_option('lizaspotifywidget_refresh_token', '');
        add_option('lizaspotifywidget_token_expiry', '');
    } catch (Exception $e) {
        lizaspotifywidget_write_log('Activation Error: ' . $e->getMessage());
    }
});

// Register deactivation hook with error handling
register_deactivation_hook(__FILE__, function() {
    try {
        // Clean up any temporary data
        delete_option('lizaspotifywidget_access_token');
        delete_option('lizaspotifywidget_refresh_token');
        delete_option('lizaspotifywidget_token_expiry');
    } catch (Exception $e) {
        lizaspotifywidget_write_log('Deactivation Error: ' . $e->getMessage());
    }
});