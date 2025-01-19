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
                'label' => __('Spotify URL', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'spotify_url',
            [
                'label' => __('Spotify URL', 'liza-spotify'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => 'https://open.spotify.com/track/...',
                'description' => __('Enter the Spotify URL for track, album, artist, playlist, or podcast.', 'liza-spotify'),
            ]
        );

        $this->add_control(
            'theme',
            [
                'label' => __('Theme', 'liza-spotify'),
                'type' => Controls_Manager::SELECT,
                'default' => '0',
                'options' => [
                    '0' => __('Black', 'liza-spotify'),
                    '1' => __('White', 'liza-spotify'),
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
        
        if (empty($settings['spotify_url'])) {
            echo '<div class="spotify-embed-error">' . __('Please enter a Spotify URL.', 'liza-spotify') . '</div>';
            return;
        }

        // Extract the Spotify URI from the URL
        preg_match('/spotify\.com\/(track|album|artist|playlist|episode|show)\/([a-zA-Z0-9]+)/', $settings['spotify_url'], $matches);
        
        if (empty($matches[1]) || empty($matches[2])) {
            echo '<div class="spotify-embed-error">' . __('Invalid Spotify URL.', 'liza-spotify') . '</div>';
            return;
        }

        $type = $matches[1];
        $id = $matches[2];
        $theme = $settings['theme'];

        $embed_url = "https://open.spotify.com/embed/{$type}/{$id}?theme={$theme}";
        
        ?>
        <div class="spotify-embed-wrapper">
            <iframe 
                src="<?php echo esc_url($embed_url); ?>"
                width="100%" 
                height="352" 
                frameborder="0" 
                allowtransparency="true" 
                allow="encrypted-media"
            ></iframe>
        </div>
        <?php
    }
} 