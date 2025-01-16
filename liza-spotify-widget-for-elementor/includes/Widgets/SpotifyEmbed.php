<?php
namespace LizaSpotify\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class SpotifyEmbed extends Widget_Base {
    public function get_name() {
        return 'liza_spotify_embed';
    }

    public function get_title() {
        return __('Spotify Embed', 'liza-spotify');
    }

    public function get_icon() {
        return 'eicon-spotify';
    }

    public function get_categories() {
        return ['liza-spotify'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'liza-spotify'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'spotify_url',
            [
                'label' => __('Spotify URL', 'liza-spotify'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => __('Enter Spotify URL', 'liza-spotify'),
                'description' => __('Enter the URL of a Spotify track, album, playlist, or artist. Example: https://open.spotify.com/track/123...', 'liza-spotify'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'theme',
            [
                'label' => __('Theme', 'liza-spotify'),
                'type' => Controls_Manager::SELECT,
                'default' => '0',
                'options' => [
                    '0' => __('Dark', 'liza-spotify'),
                    '1' => __('Light', 'liza-spotify'),
                ],
            ]
        );

        $this->add_control(
            'height',
            [
                'label' => __('Height', 'liza-spotify'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 80,
                        'max' => 500,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 380,
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        if (empty($settings['spotify_url'])) {
            echo '<p>' . __('Please enter a Spotify URL', 'liza-spotify') . '</p>';
            return;
        }

        // Clean and validate the URL
        $url = trim($settings['spotify_url']);
        $embed_url = '';

        // Handle both URI and URL formats
        if (preg_match('/^spotify:([a-z]+):([a-zA-Z0-9]+)/', $url, $matches)) {
            // Convert Spotify URI to embed URL
            $type = $matches[1];
            $id = $matches[2];
            $embed_url = "https://open.spotify.com/embed/{$type}/{$id}";
        } elseif (preg_match('#https?://open\.spotify\.com/([a-z]+)/([a-zA-Z0-9]+)(?:\?.*)?#', $url, $matches)) {
            // Convert Spotify URL to embed URL
            $type = $matches[1];
            $id = $matches[2];
            $embed_url = "https://open.spotify.com/embed/{$type}/{$id}";
        }

        if (empty($embed_url)) {
            echo '<p>' . __('Invalid Spotify URL. Please enter a valid Spotify URL or URI.', 'liza-spotify') . '</p>';
            return;
        }

        $theme = !empty($settings['theme']) ? $settings['theme'] : '0';
        $height = !empty($settings['height']['size']) ? $settings['height']['size'] : 380;

        ?>
        <div class="spotify-embed-widget">
            <iframe
                style="border-radius:12px"
                src="<?php echo esc_url($embed_url); ?>?theme=<?php echo esc_attr($theme); ?>"
                width="100%"
                height="<?php echo esc_attr($height); ?>"
                frameborder="0"
                allowfullscreen=""
                allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                loading="lazy">
            </iframe>
        </div>
        <style>
            .spotify-embed-widget {
                margin: 10px 0;
            }
            .spotify-embed-widget iframe {
                display: block;
                max-width: 100%;
            }
        </style>
        <?php
    }

    protected function content_template() {
        ?>
        <# if ( settings.spotify_url ) {
            var embedUrl = '';
            var url = settings.spotify_url;
            var uriMatch = url.match(/^spotify:([a-z]+):([a-zA-Z0-9]+)/);
            var urlMatch = url.match(/https?:\/\/open\.spotify\.com\/([a-z]+)\/([a-zA-Z0-9]+)(?:\?.*)?/);
            
            if (uriMatch) {
                embedUrl = 'https://open.spotify.com/embed/' + uriMatch[1] + '/' + uriMatch[2];
            } else if (urlMatch) {
                embedUrl = 'https://open.spotify.com/embed/' + urlMatch[1] + '/' + urlMatch[2];
            }

            if (embedUrl) {
                #>
                <div class="spotify-embed-widget">
                    <iframe
                        style="border-radius:12px"
                        src="{{ embedUrl }}?theme={{ settings.theme }}"
                        width="100%"
                        height="{{ settings.height.size }}"
                        frameborder="0"
                        allowfullscreen=""
                        allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                        loading="lazy">
                    </iframe>
                </div>
                <#
            } else {
                #>
                <p><?php echo __('Invalid Spotify URL. Please enter a valid Spotify URL or URI.', 'liza-spotify'); ?></p>
                <#
            }
        } else {
            #>
            <p><?php echo __('Please enter a Spotify URL', 'liza-spotify'); ?></p>
            <#
        } #>
        <?php
    }
} 