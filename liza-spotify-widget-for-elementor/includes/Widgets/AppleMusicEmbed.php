<?php
namespace LizaSpotify\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class AppleMusicEmbed extends Widget_Base {
    public function get_name() {
        return 'apple-music-embed';
    }

    public function get_title() {
        return __('Apple Music Embed (Pro)', 'liza-spotify');
    }

    public function get_icon() {
        return 'eicon-code';
    }

    public function get_categories() {
        return ['liza-spotify'];
    }

    public function get_keywords() {
        return ['apple', 'music', 'embed', 'premium', 'pro'];
    }

    protected function register_controls() {
        global $liza_spotify_fs;

        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        if (!$liza_spotify_fs->can_use_premium_code() && !$liza_spotify_fs->is_trial()) {
            $this->add_control(
                'pro_notice',
                [
                    'type' => Controls_Manager::RAW_HTML,
                    'raw' => sprintf(
                        /* translators: %s: Premium version upgrade link */
                        __('This is a premium feature. Please %s to use Apple Music Embed widget.', 'liza-spotify'),
                        '<a href="' . $liza_spotify_fs->get_upgrade_url() . '">' . __('upgrade to premium', 'liza-spotify') . '</a>'
                    ),
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                ]
            );
            return;
        }

        $this->add_control(
            'apple_music_url',
            [
                'label' => __('Apple Music URL', 'liza-spotify'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => 'https://music.apple.com/us/album/...',
                'description' => __('Enter the URL of an Apple Music song, album, playlist, artist, or episode.', 'liza-spotify'),
            ]
        );

        $this->add_control(
            'theme',
            [
                'label' => __('Theme', 'liza-spotify'),
                'type' => Controls_Manager::SELECT,
                'default' => 'light',
                'options' => [
                    'light' => __('Light', 'liza-spotify'),
                    'dark' => __('Dark', 'liza-spotify'),
                ],
            ]
        );

        $this->add_control(
            'height',
            [
                'label' => __('Height', 'liza-spotify'),
                'type' => Controls_Manager::NUMBER,
                'default' => 450,
                'min' => 100,
                'max' => 1000,
                'step' => 10,
            ]
        );

        $this->add_responsive_control(
            'width',
            [
                'label' => __('Width', 'liza-spotify'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 200,
                        'max' => 1000,
                        'step' => 10,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .apple-music-embed' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $url = $settings['apple_music_url'];
        $theme = $settings['theme'];
        $height = $settings['height'];

        // Add debug output
        if (current_user_can('manage_options')) {
            echo '<!-- Apple Music Embed Debug:
            URL: ' . esc_html($url) . '
            Theme: ' . esc_html($theme) . '
            Height: ' . esc_html($height) . '
            -->';
        }

        if (empty($url)) {
            echo '<div class="elementor-alert elementor-alert-warning">';
            echo __('Please enter an Apple Music URL.', 'liza-spotify');
            echo '</div>';
            return;
        }

        // Extract resource type and ID from URL
        if (preg_match('/music\.apple\.com\/([a-z]{2})\/(?:(album|playlist|song|artist)\/[^\/]+\/(\d+)|album\/[^\/]+\/(\d+)\?i=(\d+))/', $url, $matches)) {
            $country_code = $matches[1];
            $resource_type = $matches[2];
            
            // Handle song within album case
            if (isset($matches[5])) {
                $resource_type = 'song';
                $resource_id = $matches[5];
            } else {
                $resource_id = $matches[3];
            }
            
            // Add debug output for resource ID
            if (current_user_can('manage_options')) {
                echo '<!-- 
                Country Code: ' . esc_html($country_code) . '
                Resource Type: ' . esc_html($resource_type) . '
                Resource ID: ' . esc_html($resource_id) . '
                -->';
            }

            // Construct the embed URL using Apple Music's format
            $embed_url = sprintf(
                'https://embed.music.apple.com/%s/%s/%s?theme=%s',
                esc_attr($country_code),
                esc_attr($resource_type),
                esc_attr($resource_id),
                esc_attr($theme)
            );

            echo '<div class="apple-music-embed" style="margin: 0 auto;">';
            printf(
                '<iframe 
                    allow="autoplay *; encrypted-media *; fullscreen *; clipboard-write" 
                    frameborder="0" 
                    height="%s" 
                    style="width:100%%;max-width:660px;overflow:hidden;background:transparent;display:block;margin:0 auto;" 
                    sandbox="allow-forms allow-popups allow-same-origin allow-scripts allow-storage-access-by-user-activation allow-top-navigation-by-user-activation" 
                    src="%s"
                ></iframe>',
                esc_attr($height),
                esc_url($embed_url)
            );
            echo '</div>';
        } else {
            echo '<div class="elementor-alert elementor-alert-warning">';
            echo __('Invalid Apple Music URL. Please enter a valid URL for a song, album, playlist, or artist.', 'liza-spotify');
            echo '</div>';
            
            // Add example URLs for users
            echo '<div class="elementor-alert elementor-alert-info">';
            echo __('Example URLs:', 'liza-spotify');
            echo '<ul>';
            echo '<li>Album: https://music.apple.com/us/album/album-name/1234567890</li>';
            echo '<li>Song: https://music.apple.com/us/album/album-name/1234567890?i=1234567890</li>';
            echo '<li>Playlist: https://music.apple.com/us/playlist/playlist-name/pl.1234567890</li>';
            echo '<li>Artist: https://music.apple.com/us/artist/artist-name/1234567890</li>';
            echo '</ul>';
            echo '</div>';
        }
    }
} 