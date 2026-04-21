<?php
namespace LizaSpotify\Admin;

class Settings {
    private $spotify_client;
    private $profile_cache = null;

    public function __construct() {
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_init', [$this, 'page_init']);
        add_action('admin_init', [$this, 'handle_spotify_callback']);
        add_action('admin_init', [$this, 'handle_spotify_disconnect']);
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
        $this->spotify_client = new \LizaSpotify\SpotifyAPI\Client();
    }

    public function add_plugin_page() {
        add_menu_page(
            __('Liza Spotify', 'liza-spotify-widget-for-elementor'),
            __('Liza Spotify', 'liza-spotify-widget-for-elementor'),
            'manage_options',
            'liza-spotify-settings',
            [$this, 'create_admin_page'],
            'dashicons-spotify'
        );

        add_submenu_page(
            'liza-spotify-settings',
            __('Settings', 'liza-spotify-widget-for-elementor'),
            __('Settings', 'liza-spotify-widget-for-elementor'),
            'manage_options',
            'liza-spotify-settings',
            [$this, 'create_admin_page']
        );
    }

    private function get_profile() {
        if ($this->profile_cache === null) {
            $this->profile_cache = get_option('liza_spotify_access_token')
                ? $this->spotify_client->get_user_profile()
                : false;
        }
        return $this->profile_cache ?: null;
    }

    public function create_admin_page() {
        global $liza_spotify_fs;
        $td         = 'liza-spotify-widget-for-elementor';
        $profile    = $this->get_profile();
        $has_creds  = get_option('liza_spotify_client_id') && get_option('liza_spotify_client_secret');
        $is_premium = $liza_spotify_fs && ($liza_spotify_fs->can_use_premium_code() || $liza_spotify_fs->is_trial());

        settings_errors('liza_spotify_messages');
        ?>
        <div class="wrap lssp-wrap">
            <?php $this->render_page_styles(); ?>

            <!-- Header -->
            <div class="lssp-header">
                <span class="lssp-logo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z" fill="currentColor"/></svg>
                </span>
                <span class="lssp-header-title"><?php esc_html_e('Liza Spotify — Settings', $td); ?></span>
                <?php if (!$is_premium && $liza_spotify_fs) : ?>
                    <a href="<?php echo esc_url($liza_spotify_fs->get_upgrade_url()); ?>" class="lssp-pro-badge"><?php esc_html_e('Go Pro ✦', $td); ?></a>
                <?php else : ?>
                    <span class="lssp-pro-badge lssp-pro-badge--active"><?php esc_html_e('Pro ✓', $td); ?></span>
                <?php endif; ?>
            </div>

            <div class="lssp-layout">

                <!-- Spotify Connection Card -->
                <div class="lssp-card lssp-card--connection <?php echo $profile ? 'is-connected' : 'is-disconnected'; ?>">
                    <div class="lssp-card-title"><?php esc_html_e('Spotify Account', $td); ?></div>
                    <?php if ($profile) : ?>
                        <div class="lssp-profile">
                            <?php if (!empty($profile['images'][0]['url'])) : ?>
                                <img class="lssp-avatar" src="<?php echo esc_url($profile['images'][0]['url']); ?>" alt="<?php echo esc_attr($profile['display_name']); ?>">
                            <?php else : ?>
                                <div class="lssp-avatar lssp-avatar--placeholder">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v2h20v-2c0-3.3-6.7-5-10-5z" fill="currentColor"/></svg>
                                </div>
                            <?php endif; ?>
                            <div class="lssp-profile-info">
                                <div class="lssp-profile-name"><?php echo esc_html($profile['display_name']); ?></div>
                                <?php if (!empty($profile['email'])) : ?>
                                    <div class="lssp-profile-email"><?php echo esc_html($profile['email']); ?></div>
                                <?php endif; ?>
                                <span class="lssp-status-dot lssp-status-dot--on"></span>
                                <span class="lssp-status-text"><?php esc_html_e('Connected', $td); ?></span>
                            </div>
                        </div>
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=liza-spotify-settings&disconnect=1'), 'spotify_disconnect')); ?>"
                           class="lssp-btn-ghost lssp-btn-disconnect"
                           onclick="return confirm('<?php esc_attr_e('Disconnect your Spotify account?', $td); ?>');">
                            <?php esc_html_e('Disconnect', $td); ?>
                        </a>
                    <?php else : ?>
                        <p class="lssp-connection-hint">
                            <?php esc_html_e('Connect your Spotify account to display real-time data in your widgets.', $td); ?>
                        </p>
                        <?php if ($has_creds) : ?>
                            <a href="<?php echo esc_url($this->spotify_client->get_auth_url()); ?>" class="lssp-btn-primary">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z" fill="currentColor"/></svg>
                                <?php esc_html_e('Connect with Spotify', $td); ?>
                            </a>
                        <?php else : ?>
                            <div class="lssp-status-warn">
                                <?php esc_html_e('Save your API credentials first, then connect your account.', $td); ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- API Credentials Card -->
                <div class="lssp-card">
                    <div class="lssp-card-title"><?php esc_html_e('API Credentials', $td); ?></div>
                    <p class="lssp-card-hint">
                        <?php esc_html_e('Get your credentials from the Spotify Developer Dashboard. Need help?', $td); ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=liza-spotify-onboarding')); ?>"><?php esc_html_e('Setup guide', $td); ?> &rarr;</a>
                    </p>
                    <form method="post" action="options.php">
                        <?php settings_fields('liza_spotify_options'); ?>
                        <div class="lssp-field">
                            <label class="lssp-label" for="liza_spotify_client_id"><?php esc_html_e('Client ID', $td); ?></label>
                            <input class="lssp-input" type="text" id="liza_spotify_client_id" name="liza_spotify_client_id"
                                   value="<?php echo esc_attr(get_option('liza_spotify_client_id', '')); ?>"
                                   placeholder="<?php esc_attr_e('Paste your Spotify Client ID', $td); ?>">
                        </div>
                        <div class="lssp-field">
                            <label class="lssp-label" for="liza_spotify_client_secret">
                                <?php esc_html_e('Client Secret', $td); ?>
                            </label>
                            <div class="lssp-input-group">
                                <input class="lssp-input" type="password" id="liza_spotify_client_secret" name="liza_spotify_client_secret"
                                       value="<?php echo esc_attr(get_option('liza_spotify_client_secret', '')); ?>"
                                       placeholder="<?php esc_attr_e('Paste your Spotify Client Secret', $td); ?>">
                                <button type="button" class="lssp-toggle-secret" aria-label="<?php esc_attr_e('Show/hide secret', $td); ?>">
                                    <svg class="eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/></svg>
                                    <svg class="eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" style="display:none"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z" fill="currentColor"/></svg>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="lssp-btn-primary"><?php esc_html_e('Save Credentials', $td); ?></button>
                    </form>
                </div>

            </div><!-- .lssp-layout -->

            <?php if (!$is_premium && $liza_spotify_fs) : ?>
            <!-- Upgrade Card -->
            <div class="lssp-upgrade-card">
                <div class="lssp-upgrade-inner">
                    <div class="lssp-upgrade-text">
                        <div class="lssp-upgrade-title"><?php esc_html_e('Unlock Pro Widgets', $td); ?></div>
                        <ul class="lssp-upgrade-list">
                            <li><?php esc_html_e('Now Playing — live track with progress bar', $td); ?></li>
                            <li><?php esc_html_e('Artist Profile — stats, genres, top tracks', $td); ?></li>
                            <li><?php esc_html_e('Custom Audio Playlist — your own MP3/WAV files', $td); ?></li>
                            <li><?php esc_html_e('Apple Music Embed', $td); ?></li>
                        </ul>
                    </div>
                    <a href="<?php echo esc_url($liza_spotify_fs->get_upgrade_url()); ?>" class="lssp-btn-upgrade"><?php esc_html_e('Upgrade to Pro', $td); ?></a>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- .lssp-wrap -->

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.querySelector('.lssp-toggle-secret');
            if (!btn) return;
            var input = document.getElementById('liza_spotify_client_secret');
            var eyeShow = btn.querySelector('.eye-show');
            var eyeHide = btn.querySelector('.eye-hide');
            btn.addEventListener('click', function() {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                eyeShow.style.display = show ? 'none' : '';
                eyeHide.style.display = show ? '' : 'none';
            });
        });
        </script>
        <?php
    }

    private function render_page_styles() {
        ?>
        <style>
        .lssp-wrap { max-width: 820px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }

        /* Header */
        .lssp-header {
            display: flex; align-items: center; gap: 10px;
            background: #121212; color: #fff;
            padding: 14px 20px; border-radius: 8px;
            margin-bottom: 24px;
        }
        .lssp-logo { color: #1DB954; display: flex; align-items: center; flex-shrink: 0; }
        .lssp-header-title { font-size: 15px; font-weight: 700; letter-spacing: -0.01em; flex: 1; }
        .lssp-pro-badge {
            font-size: 11px; font-weight: 700; letter-spacing: 0.04em;
            padding: 3px 10px; border-radius: 500px;
            background: #1DB954; color: #000 !important;
            text-decoration: none; white-space: nowrap;
            transition: background 0.15s;
        }
        .lssp-pro-badge:hover { background: #1ed760; }
        .lssp-pro-badge--active { background: rgba(29,185,84,.18); color: #1DB954 !important; cursor: default; }

        /* Two-column layout */
        .lssp-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        @media (max-width: 700px) { .lssp-layout { grid-template-columns: 1fr; } }

        /* Card */
        .lssp-card {
            background: #fff; border: 1px solid #e2e2e2;
            border-radius: 10px; padding: 24px;
        }
        .lssp-card-title { font-size: 13px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #555; margin-bottom: 14px; }
        .lssp-card-hint { font-size: 13px; color: #666; margin: 0 0 16px; line-height: 1.5; }
        .lssp-card-hint a { color: #1DB954; text-decoration: none; }
        .lssp-card-hint a:hover { text-decoration: underline; }

        /* Connection card states */
        .lssp-card--connection.is-connected { border-color: #b2dfca; }
        .lssp-card--connection.is-disconnected { border-color: #e2e2e2; }

        /* Profile */
        .lssp-profile { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
        .lssp-avatar { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
        .lssp-avatar--placeholder {
            width: 56px; height: 56px; border-radius: 50%; background: #f0f0f0;
            display: flex; align-items: center; justify-content: center; color: #aaa;
        }
        .lssp-profile-name { font-size: 15px; font-weight: 700; color: #111; margin-bottom: 2px; }
        .lssp-profile-email { font-size: 12px; color: #666; margin-bottom: 6px; }
        .lssp-status-dot {
            display: inline-block; width: 7px; height: 7px; border-radius: 50%;
            vertical-align: middle; margin-right: 4px;
        }
        .lssp-status-dot--on { background: #1DB954; }
        .lssp-status-text { font-size: 12px; color: #1a7c3e; font-weight: 600; }

        /* Connection hint / warn */
        .lssp-connection-hint { font-size: 13px; color: #555; line-height: 1.5; margin: 0 0 16px; }
        .lssp-status-warn {
            background: #fffbe6; border: 1px solid #f5e17a; color: #7a6000;
            padding: 9px 12px; border-radius: 6px; font-size: 13px; margin-bottom: 16px;
        }

        /* Form */
        .lssp-field { margin-bottom: 16px; }
        .lssp-label { display: block; font-size: 13px; font-weight: 600; color: #333; margin-bottom: 6px; }
        .lssp-input {
            width: 100%; box-sizing: border-box;
            padding: 8px 12px; font-size: 13px;
            border: 1px solid #ddd; border-radius: 6px;
            outline: none; color: #111;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .lssp-input:focus { border-color: #1DB954; box-shadow: 0 0 0 2px rgba(29,185,84,.15); }
        .lssp-input-group { position: relative; display: flex; align-items: stretch; }
        .lssp-input-group .lssp-input { padding-right: 40px; }
        .lssp-toggle-secret {
            position: absolute; right: 0; top: 0; bottom: 0;
            width: 38px; background: none; border: none;
            color: #888; cursor: pointer; display: flex; align-items: center; justify-content: center;
            border-radius: 0 6px 6px 0;
        }
        .lssp-toggle-secret:hover { color: #333; }

        /* Buttons */
        .lssp-btn-primary {
            display: inline-flex; align-items: center; gap: 6px;
            background: #1DB954; color: #000 !important; text-decoration: none;
            padding: 9px 20px; border-radius: 500px; border: none;
            font-size: 13px; font-weight: 700; letter-spacing: 0.01em; cursor: pointer;
            transition: background 0.15s, transform 0.15s;
        }
        .lssp-btn-primary:hover { background: #1ed760; transform: scale(1.02); }
        .lssp-btn-ghost {
            display: inline-flex; align-items: center;
            background: none; border: 1px solid #ddd; color: #555 !important;
            text-decoration: none; padding: 7px 16px;
            border-radius: 500px; font-size: 12px; cursor: pointer;
            transition: border-color 0.15s, color 0.15s;
        }
        .lssp-btn-ghost:hover { border-color: #999; color: #111 !important; }
        .lssp-btn-disconnect { margin-top: 4px; }

        /* Upgrade card */
        .lssp-upgrade-card {
            background: #181818; border-radius: 10px; padding: 24px; margin-top: 0;
        }
        .lssp-upgrade-inner { display: flex; align-items: center; gap: 24px; flex-wrap: wrap; }
        .lssp-upgrade-text { flex: 1; min-width: 200px; }
        .lssp-upgrade-title { font-size: 15px; font-weight: 800; color: #fff; margin-bottom: 10px; }
        .lssp-upgrade-list { margin: 0; padding: 0; list-style: none; }
        .lssp-upgrade-list li { font-size: 13px; color: rgba(255,255,255,.65); line-height: 1.8; padding-left: 18px; position: relative; }
        .lssp-upgrade-list li::before { content: '✓'; position: absolute; left: 0; color: #1DB954; font-weight: 700; }
        .lssp-btn-upgrade {
            display: inline-flex; align-items: center; flex-shrink: 0;
            background: #1DB954; color: #000 !important; text-decoration: none;
            padding: 10px 24px; border-radius: 500px;
            font-size: 13px; font-weight: 700; white-space: nowrap;
            transition: background 0.15s;
        }
        .lssp-btn-upgrade:hover { background: #1ed760; }
        </style>
        <?php
    }

    public function page_init() {
        register_setting('liza_spotify_options', 'liza_spotify_client_id');
        register_setting('liza_spotify_options', 'liza_spotify_client_secret');
    }

    public function handle_spotify_callback() {
        if (!isset($_GET['code']) || !isset($_GET['state'])) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['state'])), 'spotify_auth')) {
            wp_die(esc_html__('Invalid authentication request', 'liza-spotify-widget-for-elementor'));
        }

        $code    = sanitize_text_field(wp_unslash($_GET['code']));
        $success = $this->spotify_client->handle_auth_callback($code);

        if ($success) {
            add_settings_error('liza_spotify_messages', 'spotify_connected', __('Successfully connected to Spotify!', 'liza-spotify-widget-for-elementor'), 'success');
        } else {
            add_settings_error('liza_spotify_messages', 'spotify_error', __('Failed to connect to Spotify. Please try again.', 'liza-spotify-widget-for-elementor'), 'error');
        }
    }

    public function handle_spotify_disconnect() {
        if (!isset($_GET['disconnect']) || !isset($_GET['_wpnonce'])) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'spotify_disconnect')) {
            wp_die(esc_html__('Invalid disconnect request', 'liza-spotify-widget-for-elementor'));
        }

        delete_option('liza_spotify_access_token');
        delete_option('liza_spotify_refresh_token');
        delete_option('liza_spotify_token_expiry');

        add_settings_error('liza_spotify_messages', 'spotify_disconnected', __('Spotify account disconnected.', 'liza-spotify-widget-for-elementor'), 'success');
        wp_redirect(admin_url('admin.php?page=liza-spotify-settings'));
        exit;
    }

    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'liza_spotify_dashboard_widget',
            __('Spotify Connection', 'liza-spotify-widget-for-elementor'),
            [$this, 'render_dashboard_widget']
        );
    }

    public function render_dashboard_widget() {
        $profile = $this->get_profile();
        ?>
        <style>
        #liza_spotify_dashboard_widget .lsdw { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        #liza_spotify_dashboard_widget .lsdw-row { display: flex; align-items: center; gap: 12px; padding: 4px 0 12px; }
        #liza_spotify_dashboard_widget .lsdw-avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
        #liza_spotify_dashboard_widget .lsdw-name { font-size: 14px; font-weight: 700; color: #111; }
        #liza_spotify_dashboard_widget .lsdw-meta { font-size: 12px; color: #666; margin-top: 1px; }
        #liza_spotify_dashboard_widget .lsdw-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; vertical-align: middle; margin-right: 3px; }
        #liza_spotify_dashboard_widget .lsdw-dot--on { background: #1DB954; }
        #liza_spotify_dashboard_widget .lsdw-dot--off { background: #ccc; }
        #liza_spotify_dashboard_widget .lsdw-btn {
            display: inline-flex; align-items: center;
            background: #1DB954; color: #000 !important; text-decoration: none;
            padding: 6px 16px; border-radius: 500px; font-size: 12px; font-weight: 700;
            transition: background 0.15s;
        }
        #liza_spotify_dashboard_widget .lsdw-btn:hover { background: #1ed760; }
        #liza_spotify_dashboard_widget .lsdw-btn-ghost {
            display: inline-flex; align-items: center;
            color: #555 !important; text-decoration: none;
            font-size: 12px; padding: 6px 0; margin-left: 8px;
        }
        </style>
        <div class="lsdw">
            <?php if ($profile) : ?>
                <div class="lsdw-row">
                    <?php if (!empty($profile['images'][0]['url'])) : ?>
                        <img class="lsdw-avatar" src="<?php echo esc_url($profile['images'][0]['url']); ?>" alt="">
                    <?php endif; ?>
                    <div>
                        <div class="lsdw-name"><?php echo esc_html($profile['display_name']); ?></div>
                        <div class="lsdw-meta">
                            <span class="lsdw-dot lsdw-dot--on"></span>
                            <?php esc_html_e('Connected', 'liza-spotify-widget-for-elementor'); ?>
                        </div>
                    </div>
                </div>
                <a href="<?php echo esc_url(admin_url('admin.php?page=liza-spotify-settings')); ?>" class="lsdw-btn-ghost">
                    <?php esc_html_e('Manage Settings', 'liza-spotify-widget-for-elementor'); ?> &rarr;
                </a>
            <?php else : ?>
                <div class="lsdw-meta" style="margin-bottom:12px;">
                    <span class="lsdw-dot lsdw-dot--off"></span>
                    <?php esc_html_e('No Spotify account connected', 'liza-spotify-widget-for-elementor'); ?>
                </div>
                <a href="<?php echo esc_url(admin_url('admin.php?page=liza-spotify-settings')); ?>" class="lsdw-btn">
                    <?php esc_html_e('Connect Spotify', 'liza-spotify-widget-for-elementor'); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
}
