<?php
namespace LizaSpotify\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use LizaSpotify\SpotifyAPI\Client;

class SpotifyNowPlaying extends Widget_Base {
    public function get_name() {
        return 'spotify-now-playing';
    }

    public function get_title() {
        return __('Spotify Now Playing', 'liza-spotify');
    }

    public function get_icon() {
        return 'eicon-play';
    }

    public function get_categories() {
        return ['liza-spotify'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Display Settings', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_artwork',
            [
                'label' => __('Show Album Artwork', 'liza-spotify'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_listen_now',
            [
                'label' => __('Show Listen Now Button', 'liza-spotify'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'refresh_interval',
            [
                'label' => __('Refresh Interval (seconds)', 'liza-spotify'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 60,
                'default' => 5,
            ]
        );

        $this->end_controls_section();

        // Container Style
        $this->start_controls_section(
            'section_container_style',
            [
                'label' => __('Container', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'background_color',
            [
                'label' => __('Background Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .spotify-now-playing' => 'background-color: {{VALUE}};',
                ],
                'default' => '#282828',
            ]
        );

        $this->add_control(
            'border_radius',
            [
                'label' => __('Border Radius', 'liza-spotify'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .spotify-now-playing' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                'label' => __('Padding', 'liza-spotify'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .spotify-now-playing' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

        // Artwork Style
        $this->start_controls_section(
            'section_artwork_style',
            [
                'label' => __('Album Artwork', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_artwork' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'artwork_size',
            [
                'label' => __('Size', 'liza-spotify'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 40,
                        'max' => 200,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .track-artwork img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'artwork_border_radius',
            [
                'label' => __('Border Radius', 'liza-spotify'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .track-artwork img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => '4',
                    'right' => '4',
                    'bottom' => '4',
                    'left' => '4',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
            ]
        );

        $this->end_controls_section();

        // Track Info Style
        $this->start_controls_section(
            'section_track_info_style',
            [
                'label' => __('Track Information', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'track_name_typography',
                'label' => __('Track Name Typography', 'liza-spotify'),
                'selector' => '{{WRAPPER}} .track-name',
            ]
        );

        $this->add_control(
            'track_name_color',
            [
                'label' => __('Track Name Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .track-name' => 'color: {{VALUE}};',
                ],
                'default' => '#FFFFFF',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'artist_typography',
                'label' => __('Artist Typography', 'liza-spotify'),
                'selector' => '{{WRAPPER}} .track-artist',
            ]
        );

        $this->add_control(
            'artist_color',
            [
                'label' => __('Artist Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .track-artist' => 'color: {{VALUE}};',
                ],
                'default' => '#FFFFFF',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'album_typography',
                'label' => __('Album Typography', 'liza-spotify'),
                'selector' => '{{WRAPPER}} .track-album',
            ]
        );

        $this->add_control(
            'album_color',
            [
                'label' => __('Album Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .track-album' => 'color: {{VALUE}};',
                ],
                'default' => '#FFFFFF',
            ]
        );

        $this->end_controls_section();

        // Progress Bar Style
        $this->start_controls_section(
            'section_progress_style',
            [
                'label' => __('Progress Bar', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'progress_height',
            [
                'label' => __('Height', 'liza-spotify'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 20,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 4,
                ],
                'selectors' => [
                    '{{WRAPPER}} .progress-bar' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'progress_background_color',
            [
                'label' => __('Background Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .progress-bar' => 'background-color: {{VALUE}};',
                ],
                'default' => 'rgba(255, 255, 255, 0.1)',
            ]
        );

        $this->add_control(
            'progress_color',
            [
                'label' => __('Progress Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .progress-bar .progress' => 'background-color: {{VALUE}};',
                ],
                'default' => '#1DB954',
            ]
        );

        $this->end_controls_section();

        // Listen Now Button Style
        $this->start_controls_section(
            'section_button_style',
            [
                'label' => __('Listen Now Button', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_listen_now' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .listen-now',
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
                'selectors' => [
                    '{{WRAPPER}} .listen-now' => 'background-color: {{VALUE}};',
                ],
                'default' => '#1DB954',
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .listen-now' => 'color: {{VALUE}};',
                ],
                'default' => '#FFFFFF',
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
                'selectors' => [
                    '{{WRAPPER}} .listen-now:hover' => 'background-color: {{VALUE}};',
                ],
                'default' => '#1ed760',
            ]
        );

        $this->add_control(
            'button_text_color_hover',
            [
                'label' => __('Text Color', 'liza-spotify'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .listen-now:hover' => 'color: {{VALUE}};',
                ],
                'default' => '#FFFFFF',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'liza-spotify'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .listen-now' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => '15',
                    'right' => '15',
                    'bottom' => '15',
                    'left' => '15',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
            ]
        );

        $this->add_control(
            'button_padding',
            [
                'label' => __('Padding', 'liza-spotify'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .listen-now' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => '6',
                    'right' => '12',
                    'bottom' => '6',
                    'left' => '12',
                    'unit' => 'px',
                    'isLinked' => false,
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        global $liza_spotify_fs;
        
        if (!$liza_spotify_fs->can_use_premium_code()) {
            ?>
            <div class="spotify-widget-premium-notice">
                <h3><?php _e('Premium Feature', 'liza-spotify'); ?></h3>
                <p><?php _e('The Spotify Now Playing widget is only available in the premium version.', 'liza-spotify'); ?></p>
                <a href="<?php echo esc_url($liza_spotify_fs->get_upgrade_url()); ?>" class="button button-primary" target="_blank">
                    <?php _e('Upgrade to Premium', 'liza-spotify'); ?>
                </a>
            </div>
            <?php
            return;
        }

        $settings = $this->get_settings_for_display();
        $client = new Client();
        $current_track = $client->get_currently_playing();

        echo '<div class="spotify-now-playing" data-refresh="' . esc_attr($settings['refresh_interval']) . '">';
        
        if ($current_track && isset($current_track['item'])) {
            $this->render_track($current_track);
        } else {
            echo '<div class="no-track-playing">';
            echo __('No track currently playing', 'liza-spotify');
            echo '</div>';
        }

        echo '</div>';

        // Add refresh script
        $this->render_script();
    }

    protected function render_track($track_data) {
        $settings = $this->get_settings_for_display();
        $item = $track_data['item'];
        $is_playing = $track_data['is_playing'];
        $progress_ms = $track_data['progress_ms'];
        $duration_ms = $item['duration_ms'];
        $progress_percent = ($progress_ms / $duration_ms) * 100;
        
        ?>
        <div class="track-info">
            <?php if ('yes' === $settings['show_artwork']) : ?>
            <div class="track-artwork">
                <img src="<?php echo esc_url($item['album']['images'][0]['url']); ?>" alt="<?php echo esc_attr($item['name']); ?>">
            </div>
            <?php endif; ?>
            <div class="track-details">
                <div class="track-name"><?php echo esc_html($item['name']); ?></div>
                <div class="track-artist">
                    <?php echo esc_html(implode(', ', array_map(function($artist) { 
                        return $artist['name']; 
                    }, $item['artists']))); ?>
                </div>
                <div class="track-album"><?php echo esc_html($item['album']['name']); ?></div>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo esc_attr($progress_percent); ?>%"></div>
                </div>
                <?php if ('yes' === $settings['show_listen_now']) : ?>
                <a href="<?php echo esc_url($item['external_urls']['spotify']); ?>" target="_blank" class="listen-now">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                    <?php _e('Listen Now', 'liza-spotify'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    protected function render_script() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            function updateTrackInfo(widget, data) {
                if (!data.item) {
                    widget.html('<div class="no-track-playing"><?php echo esc_js(__('No track currently playing', 'liza-spotify')); ?></div>');
                    return;
                }

                const progressPercent = (data.progress_ms / data.item.duration_ms) * 100;
                
                // Update only the dynamic content
                widget.find('.track-artwork img').attr('src', data.item.album.images[0].url);
                widget.find('.track-name').text(data.item.name);
                widget.find('.track-artist').text(data.item.artists.map(artist => artist.name).join(', '));
                widget.find('.track-album').text(data.item.album.name);
                widget.find('.progress').css('width', progressPercent + '%');
                widget.find('.listen-now').attr('href', data.item.external_urls.spotify);
            }

            function refreshNowPlaying() {
                var widget = $('.elementor-widget-spotify-now-playing .spotify-now-playing');
                var refreshInterval = widget.data('refresh') * 1000;

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'get_now_playing_data',
                        nonce: '<?php echo wp_create_nonce('spotify_now_playing'); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            updateTrackInfo(widget, response.data);
                        }
                    },
                    complete: function() {
                        setTimeout(refreshNowPlaying, refreshInterval);
                    }
                });
            }

            refreshNowPlaying();
        });
        </script>
        <?php
    }
} 