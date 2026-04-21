<?php
namespace LizaSpotify\Admin;

class Onboarding {
    const OPTION_COMPLETE  = 'liza_spotify_onboarding_complete';
    const OPTION_DISMISSED = 'liza_spotify_onboarding_dismissed';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_page']);
        add_action('admin_notices', [$this, 'maybe_show_notice']);
        add_action('wp_ajax_liza_spotify_dismiss_onboarding', [$this, 'ajax_dismiss']);
    }

    public function add_page() {
        add_submenu_page(
            'liza-spotify-settings',
            __('Getting Started', 'liza-spotify-widget-for-elementor'),
            __('Getting Started', 'liza-spotify-widget-for-elementor'),
            'manage_options',
            'liza-spotify-onboarding',
            [$this, 'render_page']
        );
    }

    public function maybe_show_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }
        // Silently complete onboarding for existing installs that are already configured
        if (!get_option(self::OPTION_COMPLETE) && !get_option(self::OPTION_DISMISSED)) {
            if (get_option('liza_spotify_client_id') && get_option('liza_spotify_access_token')) {
                update_option(self::OPTION_COMPLETE, 1);
                return;
            }
        }
        if (get_option(self::OPTION_COMPLETE) || get_option(self::OPTION_DISMISSED)) {
            return;
        }
        // Don't show on the onboarding page itself
        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'liza-spotify-onboarding') !== false) {
            return;
        }
        $url = admin_url('admin.php?page=liza-spotify-onboarding');
        ?>
        <div class="notice notice-info is-dismissible liza-spotify-onboarding-notice">
            <p>
                <strong><?php esc_html_e('Welcome to Liza Spotify Widgets!', 'liza-spotify-widget-for-elementor'); ?></strong>
                <?php esc_html_e('Follow the quick setup guide to connect your Spotify account and start using the widgets.', 'liza-spotify-widget-for-elementor'); ?>
                &nbsp;
                <a href="<?php echo esc_url($url); ?>" class="button button-primary">
                    <?php esc_html_e('Get Started', 'liza-spotify-widget-for-elementor'); ?>
                </a>
            </p>
        </div>
        <script>
        jQuery(document).on('click', '.liza-spotify-onboarding-notice .notice-dismiss', function() {
            jQuery.post(ajaxurl, {
                action: 'liza_spotify_dismiss_onboarding',
                nonce: '<?php echo esc_js(wp_create_nonce('liza_spotify_dismiss_onboarding')); ?>'
            });
        });
        </script>
        <?php
    }

    public function ajax_dismiss() {
        check_ajax_referer('liza_spotify_dismiss_onboarding', 'nonce');
        update_option(self::OPTION_DISMISSED, 1);
        wp_send_json_success();
    }

    public function render_page() {
        $td           = 'liza-spotify-widget-for-elementor';
        $step         = isset($_GET['step']) ? max(1, min(4, (int) $_GET['step'])) : 1;
        $has_creds    = get_option('liza_spotify_client_id') && get_option('liza_spotify_client_secret');
        $has_token    = (bool) get_option('liza_spotify_access_token');
        $settings_url = admin_url('admin.php?page=liza-spotify-settings');
        $redirect_uri = admin_url('admin.php?page=liza-spotify-settings');

        $step_labels = [
            1 => __('Create App', $td),
            2 => __('Add Credentials', $td),
            3 => __('Connect Account', $td),
            4 => __('Done', $td),
        ];
        ?>
        <div class="wrap lsob-wrap">

            <?php $this->render_page_styles(); ?>

            <!-- Header bar -->
            <div class="lsob-header">
                <span class="lsob-logo">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z" fill="currentColor"/></svg>
                </span>
                <span class="lsob-header-title"><?php esc_html_e('Liza Spotify — Setup', $td); ?></span>
            </div>

            <!-- Step tracker -->
            <div class="lsob-steps">
                <?php foreach ($step_labels as $n => $label) :
                    $cls = $n < $step ? 'done' : ($n === $step ? 'active' : 'pending');
                    ?>
                    <div class="lsob-step <?php echo esc_attr($cls); ?>">
                        <span class="lsob-step-num">
                            <?php if ($n < $step) : ?>&#10003;<?php else : echo (int) $n; endif; ?>
                        </span>
                        <span class="lsob-step-label"><?php echo esc_html($label); ?></span>
                    </div>
                    <?php if ($n < 4) : ?><div class="lsob-step-line <?php echo $n < $step ? 'done' : ''; ?>"></div><?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Card -->
            <div class="lsob-card">

                <?php if ($step === 1) : ?>

                    <h2><?php esc_html_e('Create a Spotify Developer App', $td); ?></h2>
                    <p class="lsob-intro"><?php esc_html_e('This takes about 2 minutes. You only need to do it once.', $td); ?></p>

                    <ol class="lsob-list">
                        <li><?php esc_html_e('Open the Spotify Developer Dashboard at', $td); ?> <strong>developer.spotify.com/dashboard</strong>.</li>
                        <li><?php esc_html_e('Click', $td); ?> <strong><?php esc_html_e('"Create App"', $td); ?></strong> <?php esc_html_e('and fill in any name and description.', $td); ?></li>
                        <li>
                            <?php esc_html_e('Under "Redirect URIs" paste this URL exactly:', $td); ?>
                            <div class="lsob-copy-row">
                                <code class="lsob-uri"><?php echo esc_html($redirect_uri); ?></code>
                                <button type="button" class="lsob-copy-btn" data-clipboard="<?php echo esc_attr($redirect_uri); ?>"><?php esc_html_e('Copy', $td); ?></button>
                            </div>
                        </li>
                        <li><?php esc_html_e('Save the app, then go to its settings to find your', $td); ?> <strong>Client ID</strong> <?php esc_html_e('and', $td); ?> <strong>Client Secret</strong>.</li>
                    </ol>

                    <div class="lsob-actions">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=liza-spotify-onboarding&step=2')); ?>" class="lsob-btn-primary">
                            <?php esc_html_e('Next: Add Credentials', $td); ?> &rarr;
                        </a>
                    </div>

                <?php elseif ($step === 2) : ?>

                    <h2><?php esc_html_e('Add Your API Credentials', $td); ?></h2>
                    <p class="lsob-intro"><?php esc_html_e('Paste your Client ID and Client Secret from the Spotify Developer Dashboard into the plugin settings.', $td); ?></p>

                    <?php if ($has_creds) : ?>
                        <div class="lsob-status lsob-status--ok">
                            <span>&#10003;</span> <?php esc_html_e('Credentials are already saved.', $td); ?>
                        </div>
                    <?php else : ?>
                        <div class="lsob-status lsob-status--warn">
                            <?php esc_html_e('No credentials found yet. Open Settings, paste them in, then come back here.', $td); ?>
                        </div>
                        <a href="<?php echo esc_url($settings_url); ?>" class="lsob-btn-secondary" target="_blank">
                            <?php esc_html_e('Open Settings', $td); ?> &#8599;
                        </a>
                    <?php endif; ?>

                    <div class="lsob-actions">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=liza-spotify-onboarding&step=1')); ?>" class="lsob-btn-ghost">&larr; <?php esc_html_e('Back', $td); ?></a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=liza-spotify-onboarding&step=3')); ?>" class="lsob-btn-primary">
                            <?php esc_html_e('Next: Connect Account', $td); ?> &rarr;
                        </a>
                    </div>

                <?php elseif ($step === 3) : ?>

                    <h2><?php esc_html_e('Connect Your Spotify Account', $td); ?></h2>
                    <p class="lsob-intro"><?php esc_html_e('Authorise the plugin to read your Spotify data. You can disconnect at any time.', $td); ?></p>

                    <?php if ($has_token) : ?>
                        <div class="lsob-status lsob-status--ok">
                            <span>&#10003;</span> <?php esc_html_e('Spotify account is connected!', $td); ?>
                        </div>
                    <?php else : ?>
                        <div class="lsob-status lsob-status--warn">
                            <?php esc_html_e('Not connected yet. Go to Settings and click "Connect with Spotify".', $td); ?>
                        </div>
                        <a href="<?php echo esc_url($settings_url); ?>" class="lsob-btn-primary" target="_blank">
                            <?php esc_html_e('Go to Settings & Connect', $td); ?> &#8599;
                        </a>
                        <p class="lsob-hint"><?php esc_html_e('After connecting, come back here and click Next.', $td); ?></p>
                    <?php endif; ?>

                    <div class="lsob-actions">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=liza-spotify-onboarding&step=2')); ?>" class="lsob-btn-ghost">&larr; <?php esc_html_e('Back', $td); ?></a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=liza-spotify-onboarding&step=4')); ?>" class="lsob-btn-primary">
                            <?php echo $has_token ? esc_html__('Finish Setup', $td) : esc_html__('Next', $td); ?> &rarr;
                        </a>
                    </div>

                <?php elseif ($step === 4) : ?>
                    <?php update_option(self::OPTION_COMPLETE, 1); ?>

                    <div class="lsob-done">
                        <div class="lsob-done-icon">&#9679;</div>
                        <h2><?php esc_html_e("You're all set!", $td); ?></h2>
                        <p><?php esc_html_e('Open Elementor on any page and search for "Spotify" in the widget panel to start building.', $td); ?></p>
                    </div>

                    <div class="lsob-actions lsob-actions--center">
                        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=page')); ?>" class="lsob-btn-primary">
                            <?php esc_html_e('Open Elementor', $td); ?>
                        </a>
                        <a href="<?php echo esc_url($settings_url); ?>" class="lsob-btn-ghost">
                            <?php esc_html_e('View Settings', $td); ?>
                        </a>
                    </div>

                <?php endif; ?>

            </div><!-- .lsob-card -->

        </div><!-- .lsob-wrap -->

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.lsob-copy-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var text = this.dataset.clipboard;
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(text);
                    } else {
                        var ta = document.createElement('textarea');
                        ta.value = text; document.body.appendChild(ta);
                        ta.select(); document.execCommand('copy');
                        document.body.removeChild(ta);
                    }
                    this.textContent = '<?php echo esc_js(__('Copied!', $td)); ?>';
                    var self = this;
                    setTimeout(function() { self.textContent = '<?php echo esc_js(__('Copy', $td)); ?>'; }, 1500);
                });
            });
        });
        </script>
        <?php
    }

    private function render_page_styles() {
        ?>
        <style>
        /* -- Onboarding page shell -- */
        .lsob-wrap { max-width: 680px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }

        /* Header bar */
        .lsob-header {
            display: flex; align-items: center; gap: 10px;
            background: #121212; color: #fff;
            padding: 14px 20px; border-radius: 8px;
            margin-bottom: 24px;
        }
        .lsob-logo { color: #1DB954; display: flex; align-items: center; }
        .lsob-header-title { font-size: 15px; font-weight: 700; letter-spacing: -0.01em; }

        /* Step tracker */
        .lsob-steps {
            display: flex; align-items: center; gap: 0;
            margin-bottom: 20px; overflow: hidden;
        }
        .lsob-step {
            display: flex; align-items: center; gap: 7px;
            white-space: nowrap;
        }
        .lsob-step-num {
            width: 26px; height: 26px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; flex-shrink: 0;
            background: #e5e5e5; color: #777;
        }
        .lsob-step.active .lsob-step-num  { background: #1DB954; color: #000; }
        .lsob-step.done   .lsob-step-num  { background: #1DB954; color: #000; }
        .lsob-step-label { font-size: 13px; color: #555; }
        .lsob-step.active .lsob-step-label { color: #1DB954; font-weight: 700; }
        .lsob-step.done   .lsob-step-label { color: #1a7c3e; }

        .lsob-step-line {
            flex: 1; height: 2px; background: #e5e5e5; min-width: 20px; max-width: 48px; margin: 0 8px;
        }
        .lsob-step-line.done { background: #1DB954; }

        /* Card */
        .lsob-card {
            background: #fff; border: 1px solid #e2e2e2;
            border-radius: 10px; padding: 28px 32px; margin-bottom: 12px;
        }
        .lsob-card h2 { margin: 0 0 6px; font-size: 20px; font-weight: 800; color: #111; }
        .lsob-intro { margin: 0 0 20px; color: #555; font-size: 14px; line-height: 1.6; }

        /* Numbered list */
        .lsob-list { margin: 0 0 24px 0; padding-left: 22px; color: #333; font-size: 14px; line-height: 1.7; }
        .lsob-list li { margin-bottom: 6px; }
        .lsob-list li strong { color: #111; }

        /* Copy row */
        .lsob-copy-row {
            display: flex; align-items: center; gap: 8px;
            background: #f5f5f5; border: 1px solid #ddd;
            border-radius: 6px; padding: 6px 10px;
            margin-top: 6px; flex-wrap: wrap;
        }
        .lsob-uri { font-size: 12px; word-break: break-all; flex: 1; background: none; border: none; padding: 0; }
        .lsob-copy-btn {
            background: #111; color: #fff; border: none; border-radius: 4px;
            padding: 4px 10px; font-size: 12px; cursor: pointer;
            flex-shrink: 0; transition: background 0.15s;
        }
        .lsob-copy-btn:hover { background: #1DB954; }

        /* Status banners */
        .lsob-status {
            padding: 10px 14px; border-radius: 6px; font-size: 13px;
            margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
        }
        .lsob-status--ok   { background: #f0faf4; border: 1px solid #b2dfca; color: #1a7c3e; font-weight: 600; }
        .lsob-status--warn { background: #fffbe6; border: 1px solid #f5e17a; color: #7a6000; }
        .lsob-hint { margin: 10px 0 0; font-size: 12px; color: #888; }

        /* Done state */
        .lsob-done { text-align: center; padding: 8px 0 16px; }
        .lsob-done-icon { font-size: 36px; color: #1DB954; line-height: 1; margin-bottom: 10px; }
        .lsob-done h2 { margin: 0 0 8px; font-size: 22px; }
        .lsob-done p { color: #555; font-size: 14px; margin: 0; }

        /* Buttons */
        .lsob-actions { display: flex; align-items: center; gap: 10px; margin-top: 24px; flex-wrap: wrap; }
        .lsob-actions--center { justify-content: center; }

        .lsob-btn-primary {
            display: inline-flex; align-items: center;
            background: #1DB954; color: #000 !important;
            text-decoration: none; padding: 9px 20px;
            border-radius: 500px; font-size: 13px; font-weight: 700;
            letter-spacing: 0.01em; transition: background 0.15s, transform 0.15s;
        }
        .lsob-btn-primary:hover { background: #1ed760; transform: scale(1.02); }

        .lsob-btn-secondary {
            display: inline-flex; align-items: center;
            background: #111; color: #fff !important;
            text-decoration: none; padding: 9px 18px;
            border-radius: 500px; font-size: 13px; font-weight: 600;
            transition: background 0.15s;
        }
        .lsob-btn-secondary:hover { background: #333; }

        .lsob-btn-ghost {
            display: inline-flex; align-items: center;
            color: #555 !important; text-decoration: none;
            font-size: 13px; padding: 9px 4px;
            transition: color 0.15s;
        }
        .lsob-btn-ghost:hover { color: #111 !important; }

        @media (max-width: 480px) {
            .lsob-card { padding: 20px 18px; }
            .lsob-step-label { display: none; }
            .lsob-step.active .lsob-step-label { display: inline; }
        }
        </style>
        <?php
    }
}
