<?php
namespace LizaSpotify\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class SpotifyProfile extends Widget_Base {
    private $spotify_client;

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        $this->spotify_client = new \LizaSpotify\SpotifyAPI\Client();
    }

    public function get_name() {
        return 'liza_spotify_profile';
    }

    public function get_title() {
        return __('Spotify Profile', 'liza-spotify');
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
            'content_section',
            [
                'label' => __('Content', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_image',
            [
                'label' => __('Show Profile Image', 'liza-spotify'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_followers',
            [
                'label' => __('Show Followers Count', 'liza-spotify'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_spotify_link',
            [
                'label' => __('Show Follow Button', 'liza-spotify'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_button_icon',
            [
                'label' => __('Show Button Icon', 'liza-spotify'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'condition' => [
                    'show_spotify_link' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_icon',
            [
                'label' => __('Button Icon', 'liza-spotify'),
                'type' => Controls_Manager::SELECT,
                'default' => 'dashicons-spotify',
                'options' => [
                    'dashicons-spotify' => __('Spotify', 'liza-spotify'),
                    'dashicons-external' => __('External Link', 'liza-spotify'),
                    'dashicons-arrow-right-alt' => __('Arrow Right', 'liza-spotify'),
                    'dashicons-arrow-right' => __('Arrow', 'liza-spotify'),
                    'dashicons-plus' => __('Plus', 'liza-spotify'),
                    'dashicons-controls-play' => __('Play', 'liza-spotify'),
                ],
                'condition' => [
                    'show_spotify_link' => 'yes',
                    'show_button_icon' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_heading',
            [
                'label' => __('Button Settings', 'liza-spotify'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_spotify_link' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'liza-spotify'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Follow on Spotify', 'liza-spotify'),
                'placeholder' => __('Follow on Spotify', 'liza-spotify'),
                'condition' => [
                    'show_spotify_link' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_icon_heading',
            [
                'label' => __('Content Alignment', 'liza-spotify'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // Add alignment controls to content section
        $this->add_control(
            'content_alignment',
            [
                'label' => __('Content Alignment', 'liza-spotify'),
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
                    '{{WRAPPER}} .spotify-profile-widget' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Container Style Section
        $this->start_controls_section(
            'container_style_section',
            [
                'label' => __('Container Style', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'background_color',
            [
                'label' => __('Background Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget' => 'background-color: {{VALUE}};',
                ],
                'default' => '#f9f9f9',
            ]
        );

        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __('Padding', 'liza-spotify'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'container_border',
                'selector' => '{{WRAPPER}} .spotify-profile-widget',
            ]
        );

        $this->add_control(
            'container_border_radius',
            [
                'label' => __('Border Radius', 'liza-spotify'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_box_shadow',
                'selector' => '{{WRAPPER}} .spotify-profile-widget',
            ]
        );

        $this->end_controls_section();

        // Profile Image Style Section
        $this->start_controls_section(
            'image_style_section',
            [
                'label' => __('Profile Image Style', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'image_size',
            [
                'label' => __('Image Size', 'liza-spotify'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 300,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 150,
                ],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-image img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'image_border_radius',
            [
                'label' => __('Border Radius', 'liza-spotify'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 200,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 50,
                ],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-image img' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'image_border',
                'selector' => '{{WRAPPER}} .spotify-profile-widget .profile-image img',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'image_box_shadow',
                'selector' => '{{WRAPPER}} .spotify-profile-widget .profile-image img',
            ]
        );

        $this->end_controls_section();

        // Enhance Typography Style Section
        $this->start_controls_section(
            'typography_style_section',
            [
                'label' => __('Typography', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'name_typography',
                'label' => __('Name Typography', 'liza-spotify'),
                'selector' => '{{WRAPPER}} .spotify-profile-widget .profile-name',
            ]
        );

        $this->add_responsive_control(
            'name_spacing',
            [
                'label' => __('Name Margin', 'liza-spotify'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-name' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'followers_typography',
                'label' => __('Followers Typography', 'liza-spotify'),
                'selector' => '{{WRAPPER}} .spotify-profile-widget .profile-followers',
                'condition' => [
                    'show_followers' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'followers_spacing',
            [
                'label' => __('Followers Margin', 'liza-spotify'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-followers' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'show_followers' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'name_color',
            [
                'label' => __('Name Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-name' => 'color: {{VALUE}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'followers_color',
            [
                'label' => __('Followers Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-followers' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'show_followers' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Button Style Section
        $this->start_controls_section(
            'button_style_section',
            [
                'label' => __('Follow Button Style', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_spotify_link' => 'yes',
                ],
            ]
        );

        // SECTION: Layout & Sizing
        $this->add_control(
            'section_button_layout',
            [
                'label' => __('Layout & Sizing', 'liza-spotify'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_responsive_control(
            'button_width_type',
            [
                'label' => __('Width Type', 'liza-spotify'),
                'type' => Controls_Manager::SELECT,
                'default' => 'auto',
                'options' => [
                    'auto' => __('Auto', 'liza-spotify'),
                    'full' => __('Full Width', 'liza-spotify'),
                    'custom' => __('Custom', 'liza-spotify'),
                ],
                'prefix_class' => 'elementor-button-width-',
            ]
        );

        $this->add_responsive_control(
            'button_width',
            [
                'label' => __('Custom Width', 'liza-spotify'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 500,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-link a' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'button_width_type' => 'custom',
                ],
            ]
        );

        $this->add_control(
            'button_alignment',
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
                    '{{WRAPPER}} .spotify-profile-widget .profile-link' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        // SECTION: Typography
        $this->add_control(
            'section_button_typography',
            [
                'label' => __('Typography', 'liza-spotify'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .spotify-profile-widget .profile-link a',
            ]
        );

        $this->add_control(
            'button_text_transform',
            [
                'label' => __('Text Transform', 'liza-spotify'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => __('Default', 'liza-spotify'),
                    'uppercase' => __('UPPERCASE', 'liza-spotify'),
                    'lowercase' => __('lowercase', 'liza-spotify'),
                    'capitalize' => __('Capitalize', 'liza-spotify'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-link a' => 'text-transform: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_letter_spacing',
            [
                'label' => __('Letter Spacing', 'liza-spotify'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => -5,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-link a' => 'letter-spacing: {{SIZE}}px;',
                ],
            ]
        );

        // SECTION: Colors
        $this->add_control(
            'section_button_colors',
            [
                'label' => __('Colors', 'liza-spotify'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs('button_styles');

        $this->start_controls_tab(
            'button_normal',
            [
                'label' => __('Normal', 'liza-spotify'),
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label' => __('Background Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'default' => '#1DB954',
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-link a' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFFFFF',
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-link a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'button_hover',
            [
                'label' => __('Hover', 'liza-spotify'),
            ]
        );

        $this->add_control(
            'button_background_color_hover',
            [
                'label' => __('Background Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'default' => '#1ed760',
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-link a:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_color_hover',
            [
                'label' => __('Text Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFFFFF',
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-link a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        // SECTION: Spacing & Border
        $this->add_control(
            'section_button_spacing_border',
            [
                'label' => __('Spacing & Border', 'liza-spotify'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'button_margin',
            [
                'label' => __('Margin', 'liza-spotify'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-link' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Padding', 'liza-spotify'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-link a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => '10',
                    'right' => '20',
                    'bottom' => '10',
                    'left' => '20',
                    'unit' => 'px',
                    'isLinked' => false,
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .spotify-profile-widget .profile-link a',
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'liza-spotify'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-link a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => '3',
                    'right' => '3',
                    'bottom' => '3',
                    'left' => '3',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
            ]
        );

        // SECTION: Icon Style
        $this->add_control(
            'section_button_icon',
            [
                'label' => __('Icon Style', 'liza-spotify'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_button_icon' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'icon_size',
            [
                'label' => __('Icon Size', 'liza-spotify'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 8,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-link .dashicons' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_button_icon' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'icon_spacing',
            [
                'label' => __('Icon Spacing', 'liza-spotify'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile-widget .profile-link a' => 'gap: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_button_icon' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'icon_position',
            [
                'label' => __('Icon Position', 'liza-spotify'),
                'type' => Controls_Manager::SELECT,
                'default' => 'after',
                'options' => [
                    'before' => __('Before', 'liza-spotify'),
                    'after' => __('After', 'liza-spotify'),
                ],
                'prefix_class' => 'elementor-button-icon-position-',
                'condition' => [
                    'show_button_icon' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $profile = $this->spotify_client->get_user_profile();
        
        if (!$profile) {
            echo '<p>' . __('Please connect your Spotify account in the plugin settings.', 'liza-spotify') . '</p>';
            return;
        }

        ?>
        <div class="spotify-profile-widget">
            <?php if ('yes' === $settings['show_image'] && !empty($profile['images'][0]['url'])): ?>
                <div class="profile-image">
                    <img src="<?php echo esc_url($profile['images'][0]['url']); ?>" 
                         alt="<?php echo esc_attr($profile['display_name']); ?>"
                         style="object-fit: cover;">
                </div>
            <?php endif; ?>

            <h3 class="profile-name"><?php echo esc_html($profile['display_name']); ?></h3>

            <?php if ('yes' === $settings['show_followers']): ?>
                <p class="profile-followers">
                    <?php printf(
                        _n('%s Follower', '%s Followers', $profile['followers']['total'], 'liza-spotify'),
                        number_format_i18n($profile['followers']['total'])
                    ); ?>
                </p>
            <?php endif; ?>

            <?php if ('yes' === $settings['show_spotify_link'] && !empty($profile['external_urls']['spotify'])): ?>
                <p class="profile-link">
                    <a href="<?php echo esc_url($profile['external_urls']['spotify']); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer">
                        <?php if ('yes' === $settings['show_button_icon'] && $settings['icon_position'] === 'before'): ?>
                            <span class="dashicons <?php echo esc_attr($settings['button_icon']); ?>"></span>
                        <?php endif; ?>
                        <?php echo esc_html($settings['button_text']); ?>
                        <?php if ('yes' === $settings['show_button_icon'] && $settings['icon_position'] === 'after'): ?>
                            <span class="dashicons <?php echo esc_attr($settings['button_icon']); ?>"></span>
                        <?php endif; ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>

        <style>
            .spotify-profile-widget .profile-image {
                margin-bottom: 15px;
            }
            .spotify-profile-widget .profile-link a {
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 5px;
                transition: all 0.3s ease;
            }
            .spotify-profile-widget .profile-link .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
            }
        </style>
        <?php
    }
} 