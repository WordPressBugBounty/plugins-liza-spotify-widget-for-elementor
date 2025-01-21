<?php
namespace LizaSpotify\Admin;

class Settings {
    private $spotify_client;

    public function __construct() {
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_init', [$this, 'page_init']);
        
        // Handle Spotify OAuth callback and disconnection
        add_action('admin_init', [$this, 'handle_spotify_callback']);
        add_action('admin_init', [$this, 'handle_spotify_disconnect']);
        
        // Add dashboard widget
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
        
        add_action('wp_ajax_dismiss_ruthless_promo', [$this, 'dismiss_promo']);
        
        $this->spotify_client = new \LizaSpotify\SpotifyAPI\Client();
    }

    public function add_plugin_page() {
        add_menu_page(
            __('Liza Spotify', 'liza-spotify'),
            __('Liza Spotify', 'liza-spotify'),
            'manage_options',
            'liza-spotify-settings',
            [$this, 'create_admin_page'],
            'dashicons-spotify'
        );

        add_submenu_page(
            'liza-spotify-settings',
            __('Settings', 'liza-spotify'),
            __('Settings', 'liza-spotify'),
            'manage_options',
            'liza-spotify-settings',
            [$this, 'create_admin_page']
        );
    }

    public function create_admin_page() {
        global $liza_spotify_fs;
        // Show admin notices
        settings_errors('liza_spotify_messages');

        $profile = null;
        if (get_option('liza_spotify_access_token')) {
            $profile = $this->spotify_client->get_user_profile();
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php
            // Show upgrade notice for free users
            if (!$liza_spotify_fs->can_use_premium_code() && !$liza_spotify_fs->is_trial()) {
                ?>
                <div class="notice notice-info is-dismissible" style="padding: 20px; border-left-color: #2271b1;">
                    <h3 style="margin-top: 0;"><?php _e('Upgrade to Pro Version', 'liza-spotify'); ?></h3>
                    <p><?php _e('Get access to premium features:', 'liza-spotify'); ?></p>
                    <ul style="list-style-type: disc; margin-left: 20px;">
                        <li><?php _e('Now Playing Widget - Display currently playing track', 'liza-spotify'); ?></li>
                        <li><?php _e('Artist Widget - Show artist profiles with stats', 'liza-spotify'); ?></li>
                        <li><?php _e('Apple Music Integration - Embed Apple Music content', 'liza-spotify'); ?></li>
                        <li><?php _e('Priority Support', 'liza-spotify'); ?></li>
                    </ul>
                    <p>
                        <a href="<?php echo esc_url($liza_spotify_fs->get_upgrade_url()); ?>" class="button button-primary">
                            <?php _e('Upgrade Now', 'liza-spotify'); ?>
                        </a>
                    </p>
                </div>
                <?php
            }
            ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('liza_spotify_options');
                do_settings_sections('liza-spotify-settings');
                submit_button();
                ?>
            </form>

            <div class="spotify-auth-section" style="margin-top: 30px;">
                <h2><?php _e('Spotify Authentication', 'liza-spotify'); ?></h2>
                <?php if ($profile): ?>
                    <div class="spotify-profile" style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin-top: 15px; text-align: center;">
                        <?php if (!empty($profile['images'][0]['url'])): ?>
                            <img src="<?php echo esc_url($profile['images'][0]['url']); ?>" 
                                 alt="<?php echo esc_attr($profile['display_name']); ?>"
                                 style="width: 100px; height: 100px; border-radius: 50%; margin-bottom: 10px;">
                        <?php endif; ?>
                        <p><?php printf(__('Connected as: %s', 'liza-spotify'), esc_html($profile['display_name'])); ?></p>
                        <p><?php printf(__('Email: %s', 'liza-spotify'), esc_html($profile['email'])); ?></p>
                        <p>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=liza-spotify-settings&disconnect=1'), 'spotify_disconnect')); ?>" 
                               class="button" 
                               onclick="return confirm('<?php esc_attr_e('Are you sure you want to disconnect your Spotify account?', 'liza-spotify'); ?>');">
                                <?php _e('Disconnect', 'liza-spotify'); ?>
                            </a>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="spotify-profile not-connected" style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin-top: 15px; text-align: center; border-left: 4px solid #dc3232;">
                        <p><?php _e('No Spotify account connected.', 'liza-spotify'); ?></p>
                        <p>
                            <a href="<?php echo esc_url($this->spotify_client->get_auth_url()); ?>" 
                               class="button button-primary">
                                <?php _e('Connect with Spotify', 'liza-spotify'); ?>
                            </a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function page_init() {
        // Spotify Settings
        register_setting(
            'liza_spotify_options',
            'liza_spotify_client_id'
        );

        register_setting(
            'liza_spotify_options',
            'liza_spotify_client_secret'
        );

        add_settings_section(
            'liza_spotify_setting_section',
            __('Spotify API Settings', 'liza-spotify'),
            [$this, 'section_info'],
            'liza-spotify-settings'
        );

        add_settings_field(
            'client_id',
            __('Client ID', 'liza-spotify'),
            [$this, 'client_id_callback'],
            'liza-spotify-settings',
            'liza_spotify_setting_section'
        );

        add_settings_field(
            'client_secret',
            __('Client Secret', 'liza-spotify'),
            [$this, 'client_secret_callback'],
            'liza-spotify-settings',
            'liza_spotify_setting_section'
        );
    }

    public function section_info() {
        echo '<p>' . esc_html__('Enter your Spotify API credentials below. You can get these by creating an application in the Spotify Developer Dashboard.', 'liza-spotify') . '</p>';
    }

    public function client_id_callback() {
        printf(
            '<input type="text" id="client_id" name="liza_spotify_client_id" value="%s" class="regular-text" />',
            esc_attr(get_option('liza_spotify_client_id'))
        );
    }

    public function client_secret_callback() {
        printf(
            '<input type="password" id="client_secret" name="liza_spotify_client_secret" value="%s" class="regular-text" />',
            esc_attr(get_option('liza_spotify_client_secret'))
        );
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

    public function dismiss_promo() {
        check_ajax_referer('dismiss_ruthless_promo', 'nonce');
        update_user_meta(get_current_user_id(), 'ruthless_promo_dismissed', time());
        wp_send_json_success();
    }
} 