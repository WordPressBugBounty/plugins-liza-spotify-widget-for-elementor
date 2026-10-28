<?php
namespace LizaSpotify\Admin;

/**
 * A dismissible MailMonk promo banner, styled after mailmonk.co.
 *
 * Shown once after a fresh install and once again after every plugin update:
 * the dismissal is stored against the plugin version that was current when the
 * user closed it, so bumping LIZA_SPOTIFY_VERSION brings the banner back.
 */
class MailMonkNotice {
    /** Plugin version the banner was last dismissed at, or '' if never. */
    const OPTION_DISMISSED_AT = 'liza_spotify_mailmonk_dismissed_at';

    const URL = 'https://mailmonk.co';

    public function __construct() {
        add_action('admin_notices', [$this, 'maybe_show_notice']);
        add_action('wp_ajax_liza_spotify_dismiss_mailmonk', [$this, 'ajax_dismiss']);
    }

    /**
     * Dashboard, the Plugins list and this plugin's own screens — the places a
     * user actually lands on right after installing or updating.
     */
    private function is_relevant_screen() {
        $screen = get_current_screen();
        if (!$screen) {
            return false;
        }
        if (in_array($screen->id, ['dashboard', 'plugins', 'plugins-network'], true)) {
            return true;
        }
        return strpos($screen->id, 'liza-spotify') !== false;
    }

    public function maybe_show_notice() {
        if (!current_user_can('manage_options') || !$this->is_relevant_screen()) {
            return;
        }
        // Never dismissed => false, which never matches the version string, so
        // a fresh install sees the banner on its first admin page load.
        if (get_option(self::OPTION_DISMISSED_AT) === LIZA_SPOTIFY_VERSION) {
            return;
        }

        ?>
        <div class="notice liza-mm-notice" data-liza-mm>
            <?php $this->render_styles(); ?>

            <button type="button" class="liza-mm-close" data-liza-mm-close aria-label="<?php esc_attr_e('Dismiss this notice', 'liza-spotify-widget-for-elementor'); ?>">&times;</button>

            <div class="liza-mm-body">
                <span class="liza-mm-badge"><?php esc_html_e('From the makers of Liza', 'liza-spotify-widget-for-elementor'); ?></span>

                <h2 class="liza-mm-title">
                    <?php esc_html_e('Your site looks pro.', 'liza-spotify-widget-for-elementor'); ?>
                    <span class="liza-mm-mark"><?php esc_html_e('Does your inbox?', 'liza-spotify-widget-for-elementor'); ?></span>
                </h2>

                <p class="liza-mm-sub">
                    <?php esc_html_e('Clients notice when hello@yourstudio.com replies from yourstudio@gmail.com. Mailmonk puts real email on the domain you already own. It works in Gmail, Outlook and Apple Mail, and it’s live in five minutes.', 'liza-spotify-widget-for-elementor'); ?>
                </p>

                <ul class="liza-mm-perks">
                    <li><?php esc_html_e('2 mailboxes free, for good', 'liza-spotify-widget-for-elementor'); ?></li>
                    <li><?php esc_html_e('3 DNS records, not 15', 'liza-spotify-widget-for-elementor'); ?></li>
                    <li><?php esc_html_e('No Google Workspace bill', 'liza-spotify-widget-for-elementor'); ?></li>
                </ul>
            </div>

            <div class="liza-mm-action">
                <a class="liza-mm-cta" href="<?php echo esc_url(self::URL); ?>" target="_blank" rel="noopener">
                    <span class="liza-mm-cta-icon" aria-hidden="true">
                        <span class="liza-mm-cta-rail">
                            <span class="liza-mm-cta-arrow">&rarr;</span>
                            <span class="liza-mm-cta-arrow">&rarr;</span>
                        </span>
                    </span>
                    <span class="liza-mm-cta-label"><?php esc_html_e('Get my free address', 'liza-spotify-widget-for-elementor'); ?></span>
                </a>

                <p class="liza-mm-promo">
                    <?php
                    printf(
                        /* translators: 1: promo code, 2: what the promo code unlocks */
                        esc_html__('Liza users: code %1$s for %2$s', 'liza-spotify-widget-for-elementor'),
                        '<code class="liza-mm-code">LIZA</code>',
                        '<strong>' . esc_html__('3 months of Pro free', 'liza-spotify-widget-for-elementor') . '</strong>'
                    );
                    ?>
                </p>
            </div>
        </div>

        <script>
        (function () {
            var notice = document.querySelector('[data-liza-mm]');
            if (!notice) { return; }
            notice.querySelector('[data-liza-mm-close]').addEventListener('click', function () {
                notice.style.display = 'none';
                var body = new FormData();
                body.append('action', 'liza_spotify_dismiss_mailmonk');
                body.append('nonce', '<?php echo esc_js(wp_create_nonce('liza_spotify_dismiss_mailmonk')); ?>');
                window.fetch(ajaxurl, { method: 'POST', credentials: 'same-origin', body: body });
            });
        }());
        </script>
        <?php
    }

    public function ajax_dismiss() {
        check_ajax_referer('liza_spotify_dismiss_mailmonk', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error();
        }
        update_option(self::OPTION_DISMISSED_AT, LIZA_SPOTIFY_VERSION);
        wp_send_json_success();
    }

    /**
     * MailMonk's palette and CTA, transplanted into a wp-admin notice: cream
     * surface, black ink, lime accent, and the two-arrow rail that slides one
     * icon width on hover so one arrow leaves as the next arrives.
     */
    private function render_styles() {
        ?>
        <style>
        .liza-mm-notice {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
            /* Notices are given a left border by core; MailMonk has none. */
            border: 1px solid #e6e3da;
            border-left-width: 1px;
            border-radius: 16px;
            background: #f5f3ed;
            padding: 22px 26px;
            margin: 16px 0;
            box-shadow: none;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .liza-mm-body { flex: 1 1 320px; min-width: 0; }

        .liza-mm-badge {
            display: inline-block;
            background: #c3fd61;
            color: #111;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.02em;
            line-height: 1;
            padding: 6px 10px;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .liza-mm-title {
            margin: 0 0 6px;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: -0.4px;
            line-height: 1.25;
            color: #111;
        }
        .liza-mm-mark {
            /* The lime works as a highlighter here rather than a solid chip. */
            background: linear-gradient(transparent 62%, #c3fd61 62%);
            padding: 0 2px;
        }

        /* Scoped under the notice so core's `.notice p` margins don't win. */
        .liza-mm-notice .liza-mm-sub {
            margin: 0;
            padding: 0;
            max-width: 60ch;
            font-size: 13.5px;
            font-weight: 400;
            letter-spacing: -0.1px;
            line-height: 1.55;
            color: #4a4a45;
        }

        .liza-mm-perks {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 18px;
            margin: 12px 0 0;
            padding: 0;
            list-style: none;
        }
        .liza-mm-perks li {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.4;
            color: #111;
        }
        /* A small lime chip per tick, echoing the badge. */
        .liza-mm-perks li::before {
            content: '✓';
            display: flex;
            flex: none;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 5px;
            background: #c3fd61;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
            color: #111;
        }

        /* ------------------------------------------------------------- cta -- */

        .liza-mm-action {
            display: flex;
            flex: none;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .liza-mm-cta {
            display: flex;
            flex: none;
            align-items: center;
            justify-content: center;
            gap: 14px;
            height: 52px;
            padding: 4px 20px;
            border-radius: 14px;
            background: #fffefc;
            box-shadow: 0 1px 2px rgba(17, 17, 17, 0.06);
            text-decoration: none;
            transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.25s ease;
        }
        .liza-mm-cta:hover,
        .liza-mm-cta:focus {
            transform: translateY(-4px);
            box-shadow: 0 6px 14px rgba(17, 17, 17, 0.1);
        }
        /* Keyboard users still get a ring; pointer users don't see one. */
        .liza-mm-cta:focus-visible {
            outline: 2px solid #111;
            outline-offset: 3px;
        }
        .liza-mm-cta:active { transform: translateY(-4px) scale(0.97); }

        .liza-mm-cta-icon {
            display: flex;
            flex: none;
            align-items: center;
            /* The rail is two arrows wide and starts flush left, so the seam
               between the two arrows is never visible at rest. */
            justify-content: flex-start;
            width: 28px;
            height: 28px;
            border-radius: 9px;
            background: #111;
            overflow: hidden;
        }
        .liza-mm-cta-rail {
            display: flex;
            flex: none;
            transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .liza-mm-cta:hover .liza-mm-cta-rail,
        .liza-mm-cta:focus .liza-mm-cta-rail { transform: translateX(-28px); }

        .liza-mm-cta-arrow {
            flex: none;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Matches .liza-mm-cta-icon exactly, so one rail step is one arrow. */
            width: 28px;
            height: 28px;
            font-size: 14px;
            line-height: 1;
            color: #fff;
        }

        .liza-mm-cta-label {
            font-size: 15px;
            font-weight: 500;
            letter-spacing: -0.2px;
            line-height: 1.4;
            color: #111;
            white-space: pre;
        }

        .liza-mm-notice .liza-mm-promo {
            margin: 0;
            padding: 0;
            font-size: 12.5px;
            line-height: 1.4;
            color: #4a4a45;
            text-align: center;
        }
        .liza-mm-promo strong { font-weight: 600; color: #111; }
        .liza-mm-code {
            display: inline-block;
            margin: 0 1px;
            padding: 2px 6px;
            border: 1px dashed #111;
            border-radius: 6px;
            background: #fffefc;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.06em;
            color: #111;
            /* One click selects the whole code, ready to copy. */
            user-select: all;
        }

        /* ----------------------------------------------------------- close -- */

        .liza-mm-close {
            position: absolute;
            top: 10px;
            right: 12px;
            border: none;
            background: none;
            padding: 4px 6px;
            font-size: 18px;
            line-height: 1;
            color: #9a978f;
            cursor: pointer;
            transition: color 0.15s;
        }
        .liza-mm-close:hover { color: #111; }

        @media (max-width: 782px) {
            .liza-mm-notice { padding: 20px; gap: 18px; }
            .liza-mm-title { font-size: 17px; }
            .liza-mm-action { width: 100%; align-items: stretch; }
            .liza-mm-cta { justify-content: flex-start; }
            .liza-mm-notice .liza-mm-promo { text-align: left; }
        }

        @media (prefers-reduced-motion: reduce) {
            .liza-mm-cta,
            .liza-mm-cta-rail { transition: none; }
            .liza-mm-cta:hover,
            .liza-mm-cta:focus,
            .liza-mm-cta:active { transform: none; }
            .liza-mm-cta:hover .liza-mm-cta-rail,
            .liza-mm-cta:focus .liza-mm-cta-rail { transform: none; }
        }
        </style>
        <?php
    }
}
