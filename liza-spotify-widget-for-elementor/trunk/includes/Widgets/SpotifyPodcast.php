<?php
namespace LizaSpotify\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use LizaSpotify\SpotifyAPI\Client;

class SpotifyPodcast extends Widget_Base {
    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        if ( ! wp_style_is( 'liza-spotify-podcast', 'registered' ) ) {
            wp_register_style( 'liza-spotify-podcast', LIZA_SPOTIFY_URL . 'assets/css/spotify-podcast.css', [], LIZA_SPOTIFY_VERSION );
        }
    }

    public function get_style_depends() {
        return [ 'liza-spotify-podcast' ];
    }

    public function get_name() {
        return 'spotify-podcast';
    }

    public function get_title() {
        return __('Spotify Podcast Episodes', 'liza-spotify-widget-for-elementor');
    }

    public function get_icon() {
        return 'eicon-headphones';
    }

    public function get_categories() {
        return ['liza-spotify'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Podcast Settings', 'liza-spotify-widget-for-elementor'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_id',
            [
                'label' => __('Show ID/URL', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::TEXT,
                'description' => __('Enter Spotify Show (podcast) ID or URL', 'liza-spotify-widget-for-elementor'),
            ]
        );

        $this->add_control(
            'market',
            [
                'label' => __('Market', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => 'US',
                'description' => __('Two-letter country code used for episode availability (e.g. US, DE, GB).', 'liza-spotify-widget-for-elementor'),
            ]
        );

        $this->add_control(
            'layout',
            [
                'label' => __('Layout', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::SELECT,
                'default' => 'list',
                'options' => [
                    'list' => __('List', 'liza-spotify-widget-for-elementor'),
                    'grid' => __('Grid', 'liza-spotify-widget-for-elementor'),
                ],
            ]
        );

        $this->add_control(
            'episodes_count',
            [
                'label' => __('Number of Episodes', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 50,
                'default' => 5,
            ]
        );

        $this->add_control(
            'show_header',
            [
                'label' => __('Show Podcast Header', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_cover',
            [
                'label' => __('Show Cover Art', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'condition' => [
                    'show_header' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_publisher',
            [
                'label' => __('Show Publisher', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'condition' => [
                    'show_header' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_follow',
            [
                'label' => __('Show Follow Button', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'condition' => [
                    'show_header' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_artwork',
            [
                'label' => __('Show Episode Artwork', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_description',
            [
                'label' => __('Show Episode Description', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_date',
            [
                'label' => __('Show Release Date', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_duration',
            [
                'label' => __('Show Duration', 'liza-spotify-widget-for-elementor'),
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
            'accent_color',
            [
                'label' => __('Accent Color', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .spotify-podcast-widget' => '--lsw-accent: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'background_color',
            [
                'label' => __('Background Color', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .spotify-podcast-widget' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .spotify-podcast-widget' => '--lsw-text: {{VALUE}};',
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
                    '{{WRAPPER}} .spotify-podcast-widget' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .spotify-podcast-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Episodes
        $this->start_controls_section(
            'section_style_episodes',
            [
                'label' => __('Episodes', 'liza-spotify-widget-for-elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'artwork_size',
            [
                'label' => __('Artwork Size', 'liza-spotify-widget-for-elementor'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 40,
                        'max' => 120,
                        'step' => 1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .spotify-podcast-widget.layout-list .episode-artwork' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_artwork' => 'yes',
                    'layout' => 'list',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'podcast_name_typography',
                'label' => __('Podcast Name', 'liza-spotify-widget-for-elementor'),
                'selector' => '{{WRAPPER}} .podcast-name',
                'condition' => [
                    'show_header' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'episode_name_typography',
                'label' => __('Episode Title', 'liza-spotify-widget-for-elementor'),
                'selector' => '{{WRAPPER}} .episode-name',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'episode_description_typography',
                'label' => __('Episode Description', 'liza-spotify-widget-for-elementor'),
                'selector' => '{{WRAPPER}} .episode-description',
                'condition' => [
                    'show_description' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Extract show ID from URL, URI, or use as-is
        $show_id = sanitize_text_field($settings['show_id']);
        if (strpos($show_id, 'spotify.com/show/') !== false) {
            if (preg_match('/show\/([a-zA-Z0-9]+)/', $show_id, $matches)) {
                $show_id = $matches[1];
            } else {
                $show_id = '';
            }
        }
        if (strpos($show_id, 'spotify:show:') === 0) {
            $show_id = substr($show_id, strlen('spotify:show:'));
        }

        if (empty($show_id)) {
            echo '<div class="spotify-podcast-widget-error">' . esc_html__('Please enter a valid Spotify Show ID or URL.', 'liza-spotify-widget-for-elementor') . '</div>';
            return;
        }

        $client = new Client();
        $market = $settings['market'];
        $show   = $client->get_show($show_id, $market);
        if (!$show || !is_array($show)) {
            echo '<div class="spotify-podcast-widget-error">' . esc_html__('Unable to fetch podcast data.', 'liza-spotify-widget-for-elementor') . '</div>';
            return;
        }

        $count    = max(1, min(50, (int) ($settings['episodes_count'] ?: 5)));
        $episodes = $client->get_latest_episodes($show_id, $market, $count);

        $this->render_podcast_widget($show, $episodes, $settings);
    }

    private function format_duration($duration_ms) {
        $total_min = (int) round((int) $duration_ms / 60000);
        if ($total_min < 1) {
            return __('1 min', 'liza-spotify-widget-for-elementor');
        }
        $hours   = intdiv($total_min, 60);
        $minutes = $total_min % 60;
        if ($hours > 0) {
            /* translators: 1: hours, 2: minutes */
            return sprintf(__('%1$d hr %2$d min', 'liza-spotify-widget-for-elementor'), $hours, $minutes);
        }
        /* translators: %d: minutes */
        return sprintf(__('%d min', 'liza-spotify-widget-for-elementor'), $minutes);
    }

    protected function render_podcast_widget($show, $episodes, $settings) {
        $layout      = in_array($settings['layout'], ['list', 'grid'], true) ? $settings['layout'] : 'list';
        $cover_url   = !empty($show['images'][0]['url']) ? $show['images'][0]['url'] : '';
        $show_url    = !empty($show['external_urls']['spotify']) ? $show['external_urls']['spotify'] : '';
        $date_format = get_option('date_format');
        ?>
        <div class="spotify-podcast-widget layout-<?php echo esc_attr($layout); ?>">
            <?php if ($settings['show_header'] === 'yes') : ?>
            <div class="podcast-header">
                <?php if ($settings['show_cover'] === 'yes' && $cover_url) : ?>
                <img class="podcast-cover" src="<?php echo esc_url($cover_url); ?>"
                     alt="<?php echo esc_attr($show['name']); ?>" loading="lazy" decoding="async">
                <?php endif; ?>
                <div class="podcast-meta">
                    <span class="podcast-label"><?php _e('Podcast', 'liza-spotify-widget-for-elementor'); ?></span>
                    <h3 class="podcast-name"><?php echo esc_html($show['name']); ?></h3>
                    <?php if ($settings['show_publisher'] === 'yes' && !empty($show['publisher'])) : ?>
                    <span class="podcast-publisher"><?php echo esc_html($show['publisher']); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($settings['show_follow'] === 'yes' && $show_url) : ?>
                <a href="<?php echo esc_url($show_url); ?>" target="_blank" rel="noopener noreferrer" class="follow-button">
                    <svg viewBox="0 0 24 24" class="spotify-icon">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                    <?php _e('Follow', 'liza-spotify-widget-for-elementor'); ?>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (empty($episodes)) : ?>
            <div class="spotify-podcast-widget-error"><?php _e('No episodes are currently available for this market.', 'liza-spotify-widget-for-elementor'); ?></div>
            <?php else : ?>
            <div class="episodes-list">
                <?php foreach ($episodes as $episode) :
                    $episode_url = !empty($episode['external_urls']['spotify']) ? $episode['external_urls']['spotify'] : $show_url;
                    $artwork_url = !empty($episode['images'][0]['url']) ? $episode['images'][0]['url'] : $cover_url;
                    ?>
                <a class="episode-item" href="<?php echo esc_url($episode_url); ?>" target="_blank" rel="noopener noreferrer">
                    <?php if ($settings['show_artwork'] === 'yes' && $artwork_url) : ?>
                    <img class="episode-artwork" src="<?php echo esc_url($artwork_url); ?>"
                         alt="<?php echo esc_attr($episode['name']); ?>" loading="lazy" decoding="async">
                    <?php endif; ?>
                    <div class="episode-info">
                        <span class="episode-name"><?php echo esc_html($episode['name']); ?></span>
                        <?php if ($settings['show_description'] === 'yes' && !empty($episode['description'])) : ?>
                        <p class="episode-description"><?php echo esc_html(wp_trim_words($episode['description'], 22, '…')); ?></p>
                        <?php endif; ?>
                        <?php
                        $show_date     = $settings['show_date'] === 'yes' && !empty($episode['release_date']);
                        $show_duration = $settings['show_duration'] === 'yes' && !empty($episode['duration_ms']);
                        if ($show_date || $show_duration) : ?>
                        <div class="episode-meta">
                            <?php if ($show_date) : ?>
                            <span class="episode-date"><?php echo esc_html(date_i18n($date_format, strtotime($episode['release_date']))); ?></span>
                            <?php endif; ?>
                            <?php if ($show_date && $show_duration) : ?>
                            <span class="episode-dot" aria-hidden="true">&middot;</span>
                            <?php endif; ?>
                            <?php if ($show_duration) : ?>
                            <span class="episode-duration"><?php echo esc_html($this->format_duration($episode['duration_ms'])); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="episode-play" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
