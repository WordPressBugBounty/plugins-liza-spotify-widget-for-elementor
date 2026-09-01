<?php
namespace LizaSpotify\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use LizaSpotify\SpotifyAPI\Client;

class SpotifyArtist extends Widget_Base {
    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        wp_register_style( 'liza-spotify-artist', LIZA_SPOTIFY_URL . 'assets/css/spotify-artist.css', [], LIZA_SPOTIFY_VERSION );
    }

    public function get_style_depends() {
        return [ 'liza-spotify-artist' ];
    }

    public function get_name() {
        return 'spotify-artist';
    }

    public function get_title() {
        return __('Spotify Artist', 'liza-spotify-widget-for-elementor');
    }

    public function get_icon() {
        return 'eicon-person';
    }

    public function get_categories() {
        return ['liza-spotify'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Artist Settings', 'liza-spotify-widget-for-elementor'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'artist_id',
            [
                'label' => __('Artist ID/URL', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::TEXT,
                'description' => __('Enter Spotify Artist ID or URL', 'liza-spotify-widget-for-elementor'),
            ]
        );

        $this->add_control(
            'layout',
            [
                'label' => __('Layout', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::SELECT,
                'default' => 'horizontal',
                'options' => [
                    'horizontal' => __('Horizontal', 'liza-spotify-widget-for-elementor'),
                    'vertical' => __('Vertical', 'liza-spotify-widget-for-elementor'),
                    'compact' => __('Compact', 'liza-spotify-widget-for-elementor'),
                ],
            ]
        );

        $this->add_control(
            'show_image',
            [
                'label' => __('Show Artist Image', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // Style Section - Container
        $this->start_controls_section(
            'section_style_container',
            [
                'label' => __('Container', 'liza-spotify-widget-for-elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'background_color',
            [
                'label' => __('Background Color', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .spotify-artist-widget' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .spotify-artist-widget' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'border_radius',
            [
                'label' => __('Border Radius', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .spotify-artist-widget' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => '8',
                    'right' => '8',
                    'bottom' => '8',
                    'left' => '8',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
            ]
        );

        $this->add_control(
            'padding',
            [
                'label' => __('Padding', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .spotify-artist-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => '20',
                    'right' => '20',
                    'bottom' => '20',
                    'left' => '20',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Artist Image
        $this->start_controls_section(
            'section_style_image',
            [
                'label' => __('Artist Image', 'liza-spotify-widget-for-elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'image_size',
            [
                'label' => __('Image Size', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 300,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 150,
                ],
                'selectors' => [
                    '{{WRAPPER}} .artist-image img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'image_border_radius',
            [
                'label' => __('Border Radius', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .artist-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => '50',
                    'right' => '50',
                    'bottom' => '50',
                    'left' => '50',
                    'unit' => '%',
                    'isLinked' => true,
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Typography
        $this->start_controls_section(
            'section_style_typography',
            [
                'label' => __('Typography', 'liza-spotify-widget-for-elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'artist_name_typography',
                'label' => __('Artist Name', 'liza-spotify-widget-for-elementor'),
                'selector' => '{{WRAPPER}} .artist-name',
            ]
        );

        $this->end_controls_section();

        // Style Section - Follow Button
        $this->start_controls_section(
            'section_style_button',
            [
                'label' => __('Follow Button', 'liza-spotify-widget-for-elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('button_styles');

        $this->start_controls_tab(
            'button_normal',
            [
                'label' => __('Normal', 'liza-spotify-widget-for-elementor'),
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label' => __('Background Color', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .follow-button' => 'background-color: {{VALUE}};',
                ],
                'default' => '#1DB954',
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .follow-button' => 'color: {{VALUE}};',
                ],
                'default' => '#FFFFFF',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'button_hover',
            [
                'label' => __('Hover', 'liza-spotify-widget-for-elementor'),
            ]
        );

        $this->add_control(
            'button_background_color_hover',
            [
                'label' => __('Background Color', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .follow-button:hover' => 'background-color: {{VALUE}};',
                ],
                'default' => '#1ed760',
            ]
        );

        $this->add_control(
            'button_text_color_hover',
            [
                'label' => __('Text Color', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .follow-button:hover' => 'color: {{VALUE}};',
                ],
                'default' => '#FFFFFF',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .follow-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => '20',
                    'right' => '20',
                    'bottom' => '20',
                    'left' => '20',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        global $liza_spotify_fs;

        if (!$liza_spotify_fs || !$liza_spotify_fs->can_use_premium_code()) {
            ?>
            <div class="spotify-widget-premium-notice">
                <h3><?php _e('Premium Feature', 'liza-spotify-widget-for-elementor'); ?></h3>
                <p><?php _e('The Spotify Artist widget is only available in the premium version.', 'liza-spotify-widget-for-elementor'); ?></p>
                <a href="<?php echo $liza_spotify_fs ? esc_url($liza_spotify_fs->get_upgrade_url()) : '#'; ?>" class="button button-primary" target="_blank">
                    <?php _e('Upgrade to Premium', 'liza-spotify-widget-for-elementor'); ?>
                </a>
            </div>
            <?php
            return;
        }

        $settings = $this->get_settings_for_display();
        $client = new Client();

        // Extract artist ID from URL or use as-is
        $artist_id = sanitize_text_field($settings['artist_id']);
        if (strpos($artist_id, 'spotify.com/artist/') !== false) {
            if (preg_match('/artist\/([a-zA-Z0-9]+)/', $artist_id, $matches)) {
                $artist_id = $matches[1];
            } else {
                $artist_id = '';
            }
        }
        // Also handle spotify:artist:ID URI format
        if (strpos($artist_id, 'spotify:artist:') === 0) {
            $artist_id = substr($artist_id, strlen('spotify:artist:'));
        }

        if (empty($artist_id)) {
            echo '<div class="spotify-artist-widget-error">' . esc_html__('Please enter a valid Spotify Artist ID or URL.', 'liza-spotify-widget-for-elementor') . '</div>';
            return;
        }

        // Get artist data
        $artist_data = $client->get_artist($artist_id);
        if (!$artist_data || !is_array($artist_data)) {
            echo '<div class="spotify-artist-widget-error">' . esc_html__('Unable to fetch artist data.', 'liza-spotify-widget-for-elementor') . '</div>';
            return;
        }

        $this->render_artist_widget($artist_data, $settings);
    }

    protected function render_artist_widget($artist_data, $settings) {
        $classes = [
            'spotify-artist-widget',
            'layout-' . $settings['layout']
        ];
        ?>
        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>">
            <?php if ($settings['show_image'] === 'yes' && !empty($artist_data['images'][0]['url'])) : ?>
            <div class="artist-image">
                <img src="<?php echo esc_url($artist_data['images'][0]['url']); ?>" 
                     alt="<?php echo esc_attr($artist_data['name']); ?>">
            </div>
            <?php endif; ?>

            <div class="artist-info">
                <h3 class="artist-name"><?php echo esc_html($artist_data['name']); ?></h3>

                <a href="<?php echo esc_url($artist_data['external_urls']['spotify']); ?>"
                   target="_blank" 
                   class="follow-button">
                    <svg viewBox="0 0 24 24" class="spotify-icon">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                    <?php _e('Follow on Spotify', 'liza-spotify-widget-for-elementor'); ?>
                </a>
            </div>
        </div>
        <?php
    }
} 