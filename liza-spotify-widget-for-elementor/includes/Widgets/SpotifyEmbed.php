<?php
namespace LizaSpotifyWidget\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class SpotifyEmbed extends Widget_Base {
    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        // Register scripts and styles
        wp_register_script(
            'lizaspotifywidget-embed',
            LIZASPOTIFYWIDGET_URL . 'assets/js/spotify-embed.js',
            ['jquery'],
            LIZASPOTIFYWIDGET_VERSION,
            true
        );

        wp_register_style(
            'lizaspotifywidget-embed',
            LIZASPOTIFYWIDGET_URL . 'assets/css/spotify-embed.css',
            [],
            LIZASPOTIFYWIDGET_VERSION
        );

        // Localize script with configuration
        wp_localize_script('lizaspotifywidget-embed', 'spotifyConfig', [
            'clientId' => get_option('lizaspotifywidget_client_id'),
            'clientSecret' => get_option('lizaspotifywidget_client_secret'),
            'i18n' => [
                'searching' => esc_html__('Searching...', 'liza-spotify-widget-for-elementor'),
                'noResults' => esc_html__('No results found.', 'liza-spotify-widget-for-elementor'),
                'error' => esc_html__('An error occurred. Please try again.', 'liza-spotify-widget-for-elementor'),
            ]
        ]);
    }

    public function get_name() {
        return 'lizaspotifywidget-embed';
    }

    public function get_title() {
        return esc_html__('Spotify Embed', 'liza-spotify-widget-for-elementor');
    }

    public function get_icon() {
        return 'eicon-code';
    }

    public function get_categories() {
        return ['lizaspotifywidget'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Spotify Content', 'liza-spotify-widget-for-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Add a wrapper for search functionality
        $this->add_control(
            'search_wrapper',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div class="spotify-search-wrapper"></div>',
                'content_classes' => 'spotify-search-wrapper',
            ]
        );

        $this->add_control(
            'search_type',
            [
                'label' => esc_html__('Content Type', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'track',
                'options' => [
                    'track' => esc_html__('Track', 'liza-spotify-widget-for-elementor'),
                    'album' => esc_html__('Album', 'liza-spotify-widget-for-elementor'),
                    'artist' => esc_html__('Artist', 'liza-spotify-widget-for-elementor'),
                    'playlist' => esc_html__('Playlist', 'liza-spotify-widget-for-elementor'),
                    'episode' => esc_html__('Episode', 'liza-spotify-widget-for-elementor'),
                    'show' => esc_html__('Show', 'liza-spotify-widget-for-elementor'),
                ],
            ]
        );

        $this->add_control(
            'search_field',
            [
                'label' => esc_html__('Search Spotify', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'input_type' => 'search',
                'placeholder' => esc_attr__('Type to search...', 'liza-spotify-widget-for-elementor'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        // Add a container for search results
        $this->add_control(
            'search_results',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div class="spotify-search-results"></div>',
                'content_classes' => 'spotify-search-results-wrapper',
                'separator' => 'none',
            ]
        );

        $this->add_control(
            'spotify_url',
            [
                'label' => esc_html__('Spotify URL', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__('https://open.spotify.com/track/...', 'liza-spotify-widget-for-elementor'),
                'description' => esc_html__('Enter a Spotify track, album, artist, playlist, episode, or show URL.', 'liza-spotify-widget-for-elementor'),
            ]
        );

        $this->add_control(
            'theme',
            [
                'label' => esc_html__('Theme', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'dark',
                'options' => [
                    'dark' => esc_html__('Dark', 'liza-spotify-widget-for-elementor'),
                    'light' => esc_html__('Light', 'liza-spotify-widget-for-elementor'),
                ],
            ]
        );

        $this->add_control(
            'layout',
            [
                'label' => esc_html__('Layout', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'compact',
                'options' => [
                    'compact' => esc_html__('Compact', 'liza-spotify-widget-for-elementor'),
                    'full' => esc_html__('Full', 'liza-spotify-widget-for-elementor'),
                ],
            ]
        );

        $this->add_control(
            'width',
            [
                'label' => esc_html__('Width', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vw'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'vw' => [
                        'min' => 10,
                        'max' => 100,
                        'step' => 1,
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
                'label' => esc_html__('Height', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 80,
                        'max' => 1000,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 380,
                ],
            ]
        );

        $this->add_control(
            'align',
            [
                'label' => esc_html__('Alignment', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'liza-spotify-widget-for-elementor'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'liza-spotify-widget-for-elementor'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'liza-spotify-widget-for-elementor'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Layout', 'liza-spotify-widget-for-elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'width',
            [
                'label' => __('Width', 'liza-spotify-widget-for-elementor'),
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
                'label' => __('Height', 'liza-spotify-widget-for-elementor'),
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
                'label' => __('Alignment', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'liza-spotify-widget-for-elementor'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'liza-spotify-widget-for-elementor'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'liza-spotify-widget-for-elementor'),
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
            echo '<div class="spotify-embed-error">' . esc_html__('Please enter a valid Spotify URL.', 'liza-spotify-widget-for-elementor') . '</div>';
            return;
        }

        if (!preg_match('/^https:\/\/open\.spotify\.com\/(track|album|artist|playlist|episode|show)\/[a-zA-Z0-9]+/', $spotify_url)) {
            echo '<div class="spotify-embed-error">' . esc_html__('Invalid Spotify URL format.', 'liza-spotify-widget-for-elementor') . '</div>';
            return;
        }

        // Extract Spotify ID from URL
        preg_match('/spotify\.com\/(track|album|artist|playlist|episode|show)\/([a-zA-Z0-9]+)/', $spotify_url, $matches);
        if (empty($matches[2])) {
            echo '<div class="spotify-embed-error">' . esc_html__('Invalid Spotify URL format.', 'liza-spotify-widget-for-elementor') . '</div>';
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
                      height="' . esc_attr($settings['height']['size']) . '" 
                      frameborder="0" 
                      allowtransparency="true" 
                      allow="encrypted-media"></iframe>';
        echo '</div>';
    }

    public function get_script_depends() {
        return ['lizaspotifywidget-embed'];
    }

    public function get_style_depends() {
        return ['lizaspotifywidget-embed'];
    }
} 