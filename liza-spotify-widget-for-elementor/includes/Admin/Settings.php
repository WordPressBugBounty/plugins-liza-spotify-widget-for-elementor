<?php
namespace LizaSpotify\Admin;

class Settings {
    private $spotify_client;

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'init_settings']);
        
        // Handle Spotify OAuth callback and disconnection
        add_action('admin_init', [$this, 'handle_spotify_callback']);
        add_action('admin_init', [$this, 'handle_spotify_disconnect']);
        
        // Add dashboard widget
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
        
        $this->spotify_client = new \LizaSpotify\SpotifyAPI\Client();
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Liza Spotify Settings', 'liza-spotify'),
            __('Liza Spotify', 'liza-spotify'),
            'manage_options',
            'liza-spotify-settings',
            [$this, 'render_settings_page'],
            'dashicons-spotify'
        );
    }

    public function init_settings() {
        register_setting('liza_spotify_settings', 'liza_spotify_client_id');
        register_setting('liza_spotify_settings', 'liza_spotify_client_secret');

        add_settings_section(
            'liza_spotify_settings_section',
            __('Spotify API Settings', 'liza-spotify'),
            [$this, 'render_section_info'],
            'liza_spotify_settings'
        );

        add_settings_field(
            'liza_spotify_client_id',
            __('Client ID', 'liza-spotify'),
            [$this, 'render_client_id_field'],
            'liza_spotify_settings',
            'liza_spotify_settings_section'
        );

        add_settings_field(
            'liza_spotify_client_secret',
            __('Client Secret', 'liza-spotify'),
            [$this, 'render_client_secret_field'],
            'liza_spotify_settings',
            'liza_spotify_settings_section'
        );
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Show admin notices
        settings_errors('liza_spotify_messages');

        $profile = null;
        if (get_option('liza_spotify_access_token')) {
            $profile = $this->spotify_client->get_user_profile();
        }
        ?>
        <div class="wrap">
            <div class="title-section" style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                <h1 style="margin: 0; padding: 0;"><?= esc_html(get_admin_page_title()); ?></h1>
                <a href="#" class="page-title-action pro-btn" style="margin: 0; background: #1DB954; color: #fff; border-color: #1aa549; font-weight: 500;">
                    <?php _e('Go Pro', 'liza-spotify'); ?> 
                    <span class="dashicons dashicons-star-filled" style="font-size: 14px; width: 14px; height: 14px; margin-left: 4px; vertical-align: text-bottom;"></span>
                </a>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('liza_spotify_settings');
                do_settings_sections('liza_spotify_settings');
                submit_button(__('Save Settings', 'liza-spotify'));
                ?>
            </form>

            <div class="spotify-auth-section">
                <h2><?php _e('Spotify Authentication', 'liza-spotify'); ?></h2>
                <?php if ($profile): ?>
                    <div class="spotify-profile">
                        <?php if (!empty($profile['images'][0]['url'])): ?>
                            <img src="<?php echo esc_url($profile['images'][0]['url']); ?>" 
                                 alt="<?php echo esc_attr($profile['display_name']); ?>"
                                 style="width: 100px; height: 100px; border-radius: 50%; margin-bottom: 10px;">
                        <?php endif; ?>
                        <p><?php printf(__('Connected as: %s', 'liza-spotify'), esc_html($profile['display_name'])); ?></p>
                        <p><?php printf(__('Email: %s', 'liza-spotify'), esc_html($profile['email'])); ?></p>
                        <p><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=liza-spotify-settings&disconnect=1'), 'spotify_disconnect')); ?>" 
                              class="button" 
                              onclick="return confirm('<?php esc_attr_e('Are you sure you want to disconnect your Spotify account?', 'liza-spotify'); ?>');">
                            <?php _e('Disconnect', 'liza-spotify'); ?>
                        </a></p>
                    </div>
                <?php else: ?>
                    <div class="spotify-profile not-connected">
                        <p><?php _e('No Spotify account connected.', 'liza-spotify'); ?></p>
                        <p><a href="<?php echo esc_url($this->spotify_client->get_auth_url()); ?>" class="button button-primary"><?php _e('Connect with Spotify', 'liza-spotify'); ?></a></p>
                    </div>
                <?php endif; ?>
            </div>

            <style>
                .spotify-profile {
                    background: #f9f9f9;
                    padding: 20px;
                    border-radius: 5px;
                    margin-top: 15px;
                    text-align: center;
                }
                .spotify-profile.not-connected {
                    border-left: 4px solid #dc3232;
                }
                .spotify-profile img {
                    display: block;
                    margin: 0 auto 15px;
                }
                .pro-btn:hover {
                    background: #1ed760 !important;
                    border-color: #1aa549 !important;
                    color: #fff !important;
                }
                .pro-btn:focus {
                    box-shadow: 0 0 0 1px #fff, 0 0 0 3px #1DB954 !important;
                    color: #fff !important;
                }
            </style>
        </div>
        <?php
    }

    public function render_section_info() {
        echo '<p>' . esc_html__('Enter your Spotify API credentials below. You can get these by creating an application in the Spotify Developer Dashboard.', 'liza-spotify') . '</p>';
    }

    public function render_client_id_field() {
        $client_id = get_option('liza_spotify_client_id');
        echo '<input type="text" name="liza_spotify_client_id" value="' . esc_attr($client_id) . '" class="regular-text">';
    }

    public function render_client_secret_field() {
        $client_secret = get_option('liza_spotify_client_secret');
        echo '<input type="password" name="liza_spotify_client_secret" value="' . esc_attr($client_secret) . '" class="regular-text">';
    }

    public function handle_spotify_callback() {
        if (!isset($_GET['code']) || !isset($_GET['state'])) {
            return;
        }

        if (!wp_verify_nonce($_GET['state'], 'spotify_auth')) {
            wp_die(__('Invalid authentication request', 'liza-spotify'));
        }

        $success = $this->spotify_client->handle_auth_callback($_GET['code']);

        if ($success) {
            add_settings_error(
                'liza_spotify_messages',
                'spotify_connected',
                __('Successfully connected to Spotify!', 'liza-spotify'),
                'success'
            );
        } else {
            add_settings_error(
                'liza_spotify_messages',
                'spotify_error',
                __('Failed to connect to Spotify. Please try again.', 'liza-spotify'),
                'error'
            );
        }
    }

    public function handle_spotify_disconnect() {
        if (!isset($_GET['disconnect']) || !isset($_GET['_wpnonce'])) {
            return;
        }

        if (!wp_verify_nonce($_GET['_wpnonce'], 'spotify_disconnect')) {
            wp_die(__('Invalid disconnect request', 'liza-spotify'));
        }

        // Clear all Spotify-related tokens and data
        delete_option('liza_spotify_access_token');
        delete_option('liza_spotify_refresh_token');
        delete_option('liza_spotify_token_expiry');

        // Add success message
        add_settings_error(
            'liza_spotify_messages',
            'spotify_disconnected',
            __('Successfully disconnected from Spotify.', 'liza-spotify'),
            'success'
        );

        // Redirect to remove the disconnect parameters from URL
        wp_redirect(admin_url('admin.php?page=liza-spotify-settings'));
        exit;
    }

    public function add_dashboard_widget() {
        // Add custom HTML to widget title
        $widget_title = sprintf(
            '%s <a href="#" class="page-title-action pro-btn" style="margin-left: 10px; background: #1DB954; color: #fff; border-color: #1aa549; font-weight: 500; text-decoration: none; font-size: 12px; padding: 3px 8px; border-radius: 2px;">%s <span class="dashicons dashicons-star-filled" style="font-size: 12px; width: 12px; height: 12px; margin-left: 4px; vertical-align: text-bottom;"></span></a>',
            __('Spotify Connection Status', 'liza-spotify'),
            __('Go Pro', 'liza-spotify')
        );

        wp_add_dashboard_widget(
            'liza_spotify_dashboard_widget',
            $widget_title,
            [$this, 'render_dashboard_widget']
        );
    }

    public function render_dashboard_widget() {
        $profile = null;
        if (get_option('liza_spotify_access_token')) {
            $profile = $this->spotify_client->get_user_profile();
        }

        if ($profile) {
            echo '<div class="spotify-dashboard-status connected">';
            if (!empty($profile['images'][0]['url'])) {
                echo '<img src="' . esc_url($profile['images'][0]['url']) . '" 
                           alt="' . esc_attr($profile['display_name']) . '"
                           style="width: 50px; height: 50px; border-radius: 50%; margin-right: 10px; vertical-align: middle;">';
            }
            echo '<strong>' . sprintf(__('Connected as: %s', 'liza-spotify'), esc_html($profile['display_name'])) . '</strong>';
            echo '<p><a href="' . esc_url(admin_url('admin.php?page=liza-spotify-settings')) . '" class="button button-secondary">' . 
                 __('Manage Settings', 'liza-spotify') . '</a></p>';
            echo '</div>';
        } else {
            echo '<div class="spotify-dashboard-status not-connected">';
            echo '<p>' . __('Not connected to Spotify', 'liza-spotify') . '</p>';
            echo '<p><a href="' . esc_url(admin_url('admin.php?page=liza-spotify-settings')) . '" class="button button-primary">' . 
                 __('Connect Spotify Account', 'liza-spotify') . '</a></p>';
            echo '</div>';
        }

        ?>
        <style>
            .spotify-dashboard-status {
                padding: 15px;
                background: #fff;
                border-left: 4px solid #ccc;
                margin-bottom: 10px;
            }
            .spotify-dashboard-status.connected {
                border-left-color: #46b450;
            }
            .spotify-dashboard-status.not-connected {
                border-left-color: #dc3232;
            }
            .spotify-dashboard-status img {
                display: inline-block;
            }
            .spotify-dashboard-status strong {
                display: inline-block;
                margin-bottom: 10px;
            }
            #liza_spotify_dashboard_widget .pro-btn:hover {
                background: #1ed760 !important;
                border-color: #1aa549 !important;
                color: #fff !important;
            }
            #liza_spotify_dashboard_widget .pro-btn:focus {
                box-shadow: 0 0 0 1px #fff, 0 0 0 3px #1DB954 !important;
                color: #fff !important;
            }
        </style>
        <?php
    }
} 