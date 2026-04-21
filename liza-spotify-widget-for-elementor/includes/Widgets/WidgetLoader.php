<?php
namespace LizaSpotify\Widgets;

class WidgetLoader {
    public function __construct() {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('elementor/editor/after_enqueue_styles', [$this, 'enqueue_editor_panel_fix']);
    }

    /**
     * WordPress admin injects a blue background on all <button> elements globally.
     * Elementor 3.x renders panel widget items as <button class="elementor-element">,
     * so WP's rule wins over Elementor's own `background-color: transparent`.
     * This inline override restores the correct transparent appearance.
     */
    public function enqueue_editor_panel_fix() {
        wp_add_inline_style('elementor-editor', '
            .elementor-panel button.elementor-element {
                background-color: transparent !important;
                background-image: none !important;
                border-color: var(--e-a-border-color-bold, rgba(255,255,255,.12)) !important;
                box-shadow: none !important;
                text-shadow: none !important;
                color: var(--e-a-color-txt, #ccc) !important;
                outline: none !important;
            }
            .elementor-panel button.elementor-element:hover {
                background-color: var(--e-a-bg-hover, rgba(255,255,255,.06)) !important;
                border-color: var(--e-a-border-color-accent, rgba(255,255,255,.25)) !important;
            }
            .elementor-panel button.elementor-element:active,
            .elementor-panel button.elementor-element:focus {
                background-color: var(--e-a-bg-hover, rgba(255,255,255,.06)) !important;
                outline: none !important;
                box-shadow: none !important;
            }
        ');
    }

    public function register_widgets($widgets_manager) {
        global $liza_spotify_fs;

        // Always register free widgets
        $this->load_widget('SpotifyEmbed');
        $this->load_widget('SpotifyProfile');

        if (class_exists(__NAMESPACE__ . '\\SpotifyEmbed')) {
            $widgets_manager->register(new SpotifyEmbed());
        }
        if (class_exists(__NAMESPACE__ . '\\SpotifyProfile')) {
            $widgets_manager->register(new SpotifyProfile());
        }

        // Register premium widgets when user has access
        $has_premium = $liza_spotify_fs && ($liza_spotify_fs->can_use_premium_code() || $liza_spotify_fs->is_trial());
        if ($has_premium) {
            foreach (['SpotifyNowPlaying', 'SpotifyArtist', 'AppleMusicEmbed'] as $widget) {
                $this->load_widget($widget);
                $class = __NAMESPACE__ . '\\' . $widget;
                if (class_exists($class)) {
                    $widgets_manager->register(new $class());
                }
            }
        }

        // Allow the pro plugin to register its exclusive widgets even when both are active
        do_action('liza_spotify_after_widgets_registered', $widgets_manager);
    }

    private function load_widget($name) {
        $file = LIZA_SPOTIFY_PATH . 'includes/Widgets/' . $name . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
} 