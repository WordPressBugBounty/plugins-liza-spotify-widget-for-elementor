<?php
namespace LizaSpotify\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class SpotifyEmbed extends Widget_Base {
    public function get_name() {
        return 'spotify-embed';
    }

    public function get_title() {
        return __('Spotify Embed', 'liza-spotify');
    }

    public function get_icon() {
        return 'eicon-code';
    }

    public function get_categories() {
        return ['liza-spotify'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Spotify Content', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'search_type',
            [
                'label' => __('Content Type', 'liza-spotify'),
                'type' => Controls_Manager::SELECT,
                'default' => 'track',
                'options' => [
                    'track' => __('Track', 'liza-spotify'),
                    'album' => __('Album', 'liza-spotify'),
                    'artist' => __('Artist', 'liza-spotify'),
                    'playlist' => __('Playlist', 'liza-spotify'),
                    'episode' => __('Episode', 'liza-spotify'),
                    'show' => __('Show', 'liza-spotify'),
                ],
            ]
        );

        $this->add_control(
            'search_query',
            [
                'label' => __('Search Spotify', 'liza-spotify'),
                'type' => Controls_Manager::RAW_HTML,
                'raw' => '<div class="elementor-control-field">
                    <div class="spotify-search-wrapper">
                        <div class="spotify-search-input">
                            <input type="text" class="spotify-search-field elementor-control-input-text" placeholder="' . esc_attr__('Type to search...', 'liza-spotify') . '">
                            <button type="button" class="spotify-search-button elementor-button elementor-button-default">
                                <i class="eicon-search"></i>
                            </button>
                        </div>
                        <div class="spotify-search-results"></div>
                    </div>
                </div>',
                'content_classes' => 'elementor-control-field',
            ]
        );

        $this->add_control(
            'spotify_url',
            [
                'label' => __('Selected Content', 'liza-spotify'),
                'type' => Controls_Manager::HIDDEN,
                'default' => '',
            ]
        );

        $this->add_control(
            'theme',
            [
                'label' => __('Theme', 'liza-spotify'),
                'type' => Controls_Manager::SELECT,
                'default' => 'dark',
                'options' => [
                    'dark' => __('Dark', 'liza-spotify'),
                    'light' => __('Light', 'liza-spotify'),
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Layout', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_STYLE,
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
                        'step' => 1,
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
                    '{{WRAPPER}} .spotify-embed-wrapper' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'height',
            [
                'label' => __('Height', 'liza-spotify'),
                'type' => Controls_Manager::NUMBER,
                'default' => 380,
                'min' => 80,
                'max' => 1000,
                'step' => 1,
            ]
        );

        $this->add_control(
            'alignment',
            [
                'label' => __('Alignment', 'liza-spotify'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'liza-spotify'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'liza-spotify'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'liza-spotify'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .spotify-embed-wrapper' => 'margin: 0 auto; text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $spotify_url = $settings['spotify_url'] ?? '';

        if (empty($spotify_url)) {
            echo '<div class="spotify-embed-error">' . __('Please enter a valid Spotify URL.', 'liza-spotify') . '</div>';
            return;
        }

        // Extract Spotify ID from URL
        preg_match('/spotify\.com\/(track|album|artist|playlist|episode|show)\/([a-zA-Z0-9]+)/', $spotify_url, $matches);
        if (empty($matches[2])) {
            echo '<div class="spotify-embed-error">' . __('Invalid Spotify URL format.', 'liza-spotify') . '</div>';
            return;
        }

        $spotify_id = $matches[2];
        $spotify_type = $matches[1];

        // Generate embed URL
        $embed_url = "https://open.spotify.com/embed/{$spotify_type}/{$spotify_id}";
        if ($settings['theme'] === 'dark') {
            $embed_url .= '?theme=0';
        } else {
            $embed_url .= '?theme=1';
        }

        // Get width from settings
        $width = '';
        if (isset($settings['width']['size']) && isset($settings['width']['unit'])) {
            $width = $settings['width']['size'] . $settings['width']['unit'];
        }

        // Render the embed iframe
        echo '<div class="spotify-embed-wrapper"' . ($width ? ' style="width: ' . esc_attr($width) . ';"' : '') . '>';
        echo '<iframe src="' . esc_url($embed_url) . '" 
                      width="100%" 
                      height="' . esc_attr($settings['height']) . '" 
                      frameborder="0" 
                      allowtransparency="true" 
                      allow="encrypted-media"></iframe>';
        echo '</div>';
    }

    public function get_script_depends() {
        return ['spotify-embed'];
    }

    public function get_style_depends() {
        return ['spotify-embed'];
    }
} 