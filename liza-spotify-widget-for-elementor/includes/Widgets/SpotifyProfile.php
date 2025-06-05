<?php
namespace LizaSpotifyWidget\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class SpotifyProfile extends Widget_Base {
    private $spotify_client;

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        $this->spotify_client = new \LizaSpotifyWidget\SpotifyAPI\Client();
    }

    public function get_name() {
        return 'lizaspotifywidget-profile';
    }

    public function get_title() {
        /* translators: Widget title in Elementor editor */
        return esc_html__('Spotify Profile', 'liza-spotify-widget-for-elementor');
    }

    public function get_icon() {
        return 'eicon-person';
    }

    public function get_categories() {
        return ['lizaspotifywidget'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Profile Settings', 'liza-spotify-widget-for-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_image',
            [
                'label' => esc_html__('Show Profile Image', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'liza-spotify-widget-for-elementor'),
                'label_off' => esc_html__('Hide', 'liza-spotify-widget-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'image_size',
            [
                'label' => esc_html__('Image Size', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
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
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_name',
            [
                'label' => esc_html__('Show Display Name', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'liza-spotify-widget-for-elementor'),
                'label_off' => esc_html__('Hide', 'liza-spotify-widget-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_email',
            [
                'label' => esc_html__('Show Email', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'liza-spotify-widget-for-elementor'),
                'label_off' => esc_html__('Hide', 'liza-spotify-widget-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_followers',
            [
                'label' => esc_html__('Show Followers', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'liza-spotify-widget-for-elementor'),
                'label_off' => esc_html__('Hide', 'liza-spotify-widget-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Style', 'liza-spotify-widget-for-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'name_typography',
                'label' => esc_html__('Name Typography', 'liza-spotify-widget-for-elementor'),
                'selector' => '{{WRAPPER}} .spotify-profile-name',
                'condition' => [
                    'show_name' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'email_typography',
                'label' => esc_html__('Email Typography', 'liza-spotify-widget-for-elementor'),
                'selector' => '{{WRAPPER}} .spotify-profile-email',
                'condition' => [
                    'show_email' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'followers_typography',
                'label' => esc_html__('Followers Typography', 'liza-spotify-widget-for-elementor'),
                'selector' => '{{WRAPPER}} .spotify-profile-followers',
                'condition' => [
                    'show_followers' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => esc_html__('Text Color', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'background_color',
            [
                'label' => esc_html__('Background Color', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'border',
                'label' => esc_html__('Border', 'liza-spotify-widget-for-elementor'),
                'selector' => '{{WRAPPER}} .spotify-profile',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow',
                'label' => esc_html__('Box Shadow', 'liza-spotify-widget-for-elementor'),
                'selector' => '{{WRAPPER}} .spotify-profile',
            ]
        );

        $this->add_control(
            'border_radius',
            [
                'label' => esc_html__('Border Radius', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'padding',
            [
                'label' => esc_html__('Padding', 'liza-spotify-widget-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .spotify-profile' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        try {
            $profile = $this->spotify_client->get_user_profile();
            if (!$profile) {
                echo '<div class="spotify-profile-error">' . esc_html__('Failed to load Spotify profile.', 'liza-spotify-widget-for-elementor') . '</div>';
                return;
            }

            ?>
            <div class="spotify-profile">
                <?php if ($settings['show_image'] === 'yes' && !empty($profile['images'][0]['url'])): ?>
                    <div class="spotify-profile-image">
                        <img src="<?php echo esc_url($profile['images'][0]['url']); ?>" 
                             alt="<?php echo esc_attr($profile['display_name']); ?>"
                             width="<?php echo esc_attr($settings['image_size']['size']); ?>"
                             height="<?php echo esc_attr($settings['image_size']['size']); ?>">
                    </div>
                <?php endif; ?>

                <?php if ($settings['show_name'] === 'yes'): ?>
                    <h3 class="spotify-profile-name"><?php echo esc_html($profile['display_name']); ?></h3>
                <?php endif; ?>

                <?php if ($settings['show_email'] === 'yes' && !empty($profile['email'])): ?>
                    <p class="spotify-profile-email"><?php echo esc_html($profile['email']); ?></p>
                <?php endif; ?>

                <?php if ($settings['show_followers'] === 'yes' && isset($profile['followers']['total'])): ?>
                    <p class="spotify-profile-followers">
                        <?php
                        printf(
                            /* translators: %d: Number of followers */
                            esc_html(_n('%d Follower', '%d Followers', $profile['followers']['total'], 'liza-spotify-widget-for-elementor')),
                            number_format_i18n($profile['followers']['total'])
                        );
                        ?>
                    </p>
                <?php endif; ?>
            </div>
            <?php
        } catch (\Exception $e) {
            echo '<div class="spotify-profile-error">' . esc_html__('Error loading Spotify profile.', 'liza-spotify-widget-for-elementor') . '</div>';
        }
    }
} 