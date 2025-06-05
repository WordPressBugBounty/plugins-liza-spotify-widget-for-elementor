<?php
namespace LizaSpotifyWidget\Admin;

class Settings {
    private $spotify_client;
    private $menu_slug = 'lizaspotifywidget-settings';

    public function __construct() {
        // Register menu with a lower priority to avoid conflicts
        add_action('admin_menu', [$this, 'add_admin_menu'], 20);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        // Add dashboard widget
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
        
        try {
            $this->spotify_client = new \LizaSpotifyWidget\SpotifyAPI\Client();
        } catch (\Exception $e) {
            add_action('admin_notices', function() use ($e) {
                printf(
                    '<div class="notice notice-error is-dismissible"><p>%s</p></div>',
                    /* translators: %s: Error message from Spotify client */
                    esc_html(sprintf(__('Spotify Client Error: %s', 'liza-spotify-widget-for-elementor'), $e->getMessage()))
                );
            });
        }
    }

    public function add_admin_menu() {
        // Check if menu already exists to prevent duplicates
        global $menu;
        foreach ($menu as $item) {
            if (isset($item[2]) && $item[2] === $this->menu_slug) {
                return;
            }
        }

        add_menu_page(
            esc_html__('Liza Spotify', 'liza-spotify-widget-for-elementor'),
            esc_html__('Liza Spotify', 'liza-spotify-widget-for-elementor'),
            'manage_options',
            $this->menu_slug,
            [$this, 'render_settings_page'],
            'dashicons-spotify',
            30
        );

        add_submenu_page(
            $this->menu_slug,
            esc_html__('Settings', 'liza-spotify-widget-for-elementor'),
            esc_html__('Settings', 'liza-spotify-widget-for-elementor'),
            'manage_options',
            $this->menu_slug,
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'liza-spotify-widget-for-elementor'));
        }
        
        // Show admin notices
        settings_errors('lizaspotifywidget_messages');

        $profile = null;
        if (get_option('lizaspotifywidget_access_token')) {
            try {
                $profile = $this->spotify_client->get_user_profile();
            } catch (\Exception $e) {
                add_settings_error(
                    'lizaspotifywidget_messages',
                    'spotify_error',
                    esc_html__('Error fetching Spotify profile. Please try reconnecting.', 'liza-spotify-widget-for-elementor'),
                    'error'
                );
            }
        }

        // Check if we have valid credentials
        $client_id = get_option('lizaspotifywidget_client_id');
        $client_secret = get_option('lizaspotifywidget_client_secret');
        $access_token = get_option('lizaspotifywidget_access_token');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php if (empty($client_id) || empty($client_secret)): ?>
                <div class="notice notice-warning">
                    <p><?php esc_html_e('Please enter your Spotify API credentials below.', 'liza-spotify-widget-for-elementor'); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('lizaspotifywidget_options');
                do_settings_sections($this->menu_slug);
                submit_button();
                ?>
            </form>

            <?php if (!empty($client_id) && !empty($client_secret)): ?>
                <div class="spotify-connection-section" style="margin-top: 30px;">
                    <h2><?php esc_html_e('Spotify Connection', 'liza-spotify-widget-for-elementor'); ?></h2>
                    
                    <?php if ($access_token): ?>
                        <p><?php esc_html_e('Connected to Spotify.', 'liza-spotify-widget-for-elementor'); ?></p>
                        <form method="post" action="">
                            <?php wp_nonce_field('disconnect_spotify', 'spotify_disconnect_nonce'); ?>
                            <input type="hidden" name="action" value="disconnect_spotify">
                            <?php submit_button(__('Disconnect Spotify', 'liza-spotify-widget-for-elementor'), 'secondary'); ?>
                        </form>
                    <?php else: ?>
                        <p><?php esc_html_e('Not connected to Spotify.', 'liza-spotify-widget-for-elementor'); ?></p>
                        <a href="<?php echo esc_url($this->spotify_client->get_auth_url()); ?>" class="button button-primary">
                            <?php esc_html_e('Connect to Spotify', 'liza-spotify-widget-for-elementor'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="tutorials-section" style="margin-top: 30px;">
                <h2><?php esc_html_e('Video Tutorials', 'liza-spotify-widget-for-elementor'); ?></h2>
                <div class="tutorials-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 15px;">
                    <div class="tutorial-card" style="background: #f9f9f9; padding: 20px; border-radius: 5px; border-left: 4px solid #1DB954;">
                        <h3 style="margin-top: 0;"><?php esc_html_e('Getting Started with Liza Spotify', 'liza-spotify-widget-for-elementor'); ?></h3>
                        <p><?php esc_html_e('Learn how to set up and use the Liza Spotify plugin for WordPress.', 'liza-spotify-widget-for-elementor'); ?></p>
                        <a href="<?php echo esc_url('https://www.youtube.com/watch?v=HbL8ERGBquk'); ?>" target="_blank" class="button button-primary">
                            <span class="dashicons dashicons-video-alt3" style="vertical-align: middle; margin-right: 5px;"></span>
                            <?php esc_html_e('Watch Tutorial', 'liza-spotify-widget-for-elementor'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function register_settings() {
        // Register settings
        register_setting(
            'lizaspotifywidget_options',
            'lizaspotifywidget_client_id',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        register_setting(
            'lizaspotifywidget_options',
            'lizaspotifywidget_client_secret',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Add settings section
        add_settings_section(
            'lizaspotifywidget_setting_section',
            esc_html__('Spotify API Settings', 'liza-spotify-widget-for-elementor'),
            [$this, 'section_info'],
            $this->menu_slug
        );

        // Add settings fields
        add_settings_field(
            'lizaspotifywidget_client_id',
            esc_html__('Client ID', 'liza-spotify-widget-for-elementor'),
            [$this, 'client_id_callback'],
            $this->menu_slug,
            'lizaspotifywidget_setting_section'
        );

        add_settings_field(
            'lizaspotifywidget_client_secret',
            esc_html__('Client Secret', 'liza-spotify-widget-for-elementor'),
            [$this, 'client_secret_callback'],
            $this->menu_slug,
            'lizaspotifywidget_setting_section'
        );

        // Handle disconnect action
        if (isset($_POST['action']) && $_POST['action'] === 'disconnect_spotify' && 
            isset($_POST['spotify_disconnect_nonce'])) {
            
            $nonce = sanitize_text_field(wp_unslash($_POST['spotify_disconnect_nonce']));
            
            if (wp_verify_nonce($nonce, 'disconnect_spotify')) {
                delete_option('lizaspotifywidget_access_token');
                delete_option('lizaspotifywidget_refresh_token');
                delete_option('lizaspotifywidget_token_expiry');
                
                wp_redirect(add_query_arg('disconnected', '1', admin_url('admin.php?page=' . $this->menu_slug)));
                exit;
            }
        }
    }

    public function section_info() {
        echo '<p>' . esc_html__('Enter your Spotify API credentials below. You can get these by creating an application in the Spotify Developer Dashboard.', 'liza-spotify-widget-for-elementor') . '</p>';
        echo '<p><a href="https://developer.spotify.com/dashboard" target="_blank">' . esc_html__('Go to Spotify Developer Dashboard', 'liza-spotify-widget-for-elementor') . '</a></p>';
    }

    public function client_id_callback() {
        $value = get_option('lizaspotifywidget_client_id');
        ?>
        <input type="text" 
               id="lizaspotifywidget_client_id" 
               name="lizaspotifywidget_client_id" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text" />
        <?php
    }

    public function client_secret_callback() {
        $value = get_option('lizaspotifywidget_client_secret');
        ?>
        <input type="password" 
               id="lizaspotifywidget_client_secret" 
               name="lizaspotifywidget_client_secret" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text" />
        <?php
    }

    public function handle_connect() {
        if (!isset($_GET['code']) || !isset($_GET['state'])) {
            wp_die(esc_html__('Invalid authentication request', 'liza-spotify-widget-for-elementor'));
        }

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['state'])), 'spotify_auth')) {
            wp_die(esc_html__('Invalid authentication request', 'liza-spotify-widget-for-elementor'));
        }

        try {
            $success = $this->spotify_client->handle_auth_callback(sanitize_text_field(wp_unslash($_GET['code'])));

            if ($success) {
                add_settings_error(
                    'lizaspotifywidget_messages',
                    'spotify_connected',
                    esc_html__('Successfully connected to Spotify!', 'liza-spotify-widget-for-elementor'),
                    'success'
                );
            } else {
                throw new \Exception(__('Failed to connect to Spotify', 'liza-spotify-widget-for-elementor'));
            }
        } catch (\Exception $e) {
            add_settings_error(
                'lizaspotifywidget_messages',
                'spotify_error',
                esc_html__('Failed to connect to Spotify. Please try again.', 'liza-spotify-widget-for-elementor'),
                'error'
            );
        }
    }

    public function handle_disconnect() {
        if (!isset($_GET['disconnect']) || !isset($_GET['_wpnonce'])) {
            wp_die(esc_html__('Invalid disconnect request', 'liza-spotify-widget-for-elementor'));
        }

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'spotify_disconnect')) {
            wp_die(esc_html__('Invalid disconnect request', 'liza-spotify-widget-for-elementor'));
        }

        // Clear all Spotify-related tokens and data
        delete_option('lizaspotifywidget_access_token');
        delete_option('lizaspotifywidget_refresh_token');
        delete_option('lizaspotifywidget_token_expiry');

        // Add success message
        add_settings_error(
            'lizaspotifywidget_messages',
            'spotify_disconnected',
            esc_html__('Successfully disconnected from Spotify.', 'liza-spotify-widget-for-elementor'),
            'success'
        );

        // Redirect to remove the disconnect parameters from URL
        wp_redirect(admin_url('admin.php?page=' . $this->menu_slug));
        exit;
    }

    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'lizaspotifywidget_dashboard_widget',
            esc_html__('Liza Spotify Status', 'liza-spotify-widget-for-elementor'),
            [$this, 'render_dashboard_widget']
        );
    }

    public function render_dashboard_widget() {
        if (get_option('lizaspotifywidget_access_token')) {
            try {
                $profile = $this->spotify_client->get_user_profile();
                ?>
                <p><?php esc_html_e('Connected to Spotify as:', 'liza-spotify-widget-for-elementor'); ?></p>
                <p><strong><?php echo esc_html($profile['display_name']); ?></strong></p>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->menu_slug)); ?>" class="button button-secondary">
                        <?php esc_html_e('Manage Settings', 'liza-spotify-widget-for-elementor'); ?>
                    </a>
                </p>
                <?php
            } catch (\Exception $e) {
                ?>
                <p><?php esc_html_e('Error connecting to Spotify. Please try reconnecting.', 'liza-spotify-widget-for-elementor'); ?></p>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->menu_slug)); ?>" class="button button-primary">
                        <?php esc_html_e('Reconnect Spotify', 'liza-spotify-widget-for-elementor'); ?>
                    </a>
                </p>
                <?php
            }
        } else {
            ?>
            <p><?php esc_html_e('No Spotify account connected.', 'liza-spotify-widget-for-elementor'); ?></p>
            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->menu_slug)); ?>" class="button button-primary">
                    <?php esc_html_e('Connect Spotify', 'liza-spotify-widget-for-elementor'); ?>
                </a>
            </p>
            <?php
        }
    }

    public function enqueue_scripts($hook) {
        // Only load on our plugin's settings page
        if ($hook !== 'toplevel_page_' . $this->menu_slug) {
            return;
        }

        wp_enqueue_style(
            'lizaspotifywidget-admin',
            plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/admin.css',
            [],
            LIZASPOTIFYWIDGET_VERSION
        );

        wp_enqueue_script(
            'lizaspotifywidget-admin',
            plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/admin.js',
            ['jquery'],
            LIZASPOTIFYWIDGET_VERSION,
            true
        );
    }
} 