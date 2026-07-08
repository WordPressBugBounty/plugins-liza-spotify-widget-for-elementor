/**
 * Liza Spotify Widgets — Gutenberg editor integration.
 *
 * No build step: plain wp.element JS. Every block is server-rendered
 * (ServerSideRender) so the editor preview matches the frontend exactly.
 */
(function (wp) {
    'use strict';

    if (!wp || !wp.blocks || !wp.element || !wp.blockEditor) {
        return;
    }

    var el = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var __ = wp.i18n.__;
    var registerBlockType = wp.blocks.registerBlockType;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var PanelColorSettings = wp.blockEditor.PanelColorSettings;
    var MediaUpload = wp.blockEditor.MediaUpload;
    var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
    var ServerSideRender = wp.serverSideRender;
    var components = wp.components;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var TextareaControl = components.TextareaControl;
    var ToggleControl = components.ToggleControl;
    var RangeControl = components.RangeControl;
    var SelectControl = components.SelectControl;
    var Button = components.Button;
    var Placeholder = components.Placeholder;

    var data = window.lizaSpotifyBlocksData || { isPremium: false, upgradeUrl: '' };

    /* ── Generic helpers ─────────────────────────────────────── */

    function setter(props, attr) {
        return function (value) {
            var update = {};
            update[attr] = value;
            props.setAttributes(update);
        };
    }

    function renderControl(props, c) {
        var value = props.attributes[c.attr];
        var common = { key: c.attr, label: c.label, help: c.help, __nextHasNoMarginBottom: true };

        switch (c.type) {
            case 'text':
                return el(TextControl, Object.assign(common, {
                    value: value || '',
                    placeholder: c.placeholder,
                    onChange: setter(props, c.attr)
                }));
            case 'textarea':
                return el(TextareaControl, Object.assign(common, {
                    value: value || '',
                    placeholder: c.placeholder,
                    rows: 3,
                    onChange: setter(props, c.attr)
                }));
            case 'toggle':
                return el(ToggleControl, Object.assign(common, {
                    checked: !!value,
                    onChange: setter(props, c.attr)
                }));
            case 'range':
                return el(RangeControl, Object.assign(common, {
                    value: typeof value === 'number' ? value : c.min,
                    min: c.min,
                    max: c.max,
                    step: c.step || 1,
                    onChange: setter(props, c.attr)
                }));
            case 'select':
                return el(SelectControl, Object.assign(common, {
                    value: value,
                    options: c.options,
                    onChange: setter(props, c.attr)
                }));
            default:
                return null;
        }
    }

    function renderPanel(props, panel, index) {
        // Panel-level conditional visibility
        if (panel.showIf && !panel.showIf(props.attributes)) {
            return null;
        }

        if (panel.colors) {
            return el(PanelColorSettings, {
                key: 'panel-' + index,
                title: panel.title,
                initialOpen: !!panel.initialOpen,
                enableAlpha: true,
                colorSettings: panel.colors.map(function (c) {
                    return {
                        label: c.label,
                        value: props.attributes[c.attr],
                        onChange: setter(props, c.attr)
                    };
                })
            });
        }

        return el(
            PanelBody,
            { key: 'panel-' + index, title: panel.title, initialOpen: !!panel.initialOpen },
            panel.controls.map(function (c) {
                if (c.showIf && !c.showIf(props.attributes)) {
                    return null;
                }
                if (c.render) {
                    return c.render(props);
                }
                return renderControl(props, c);
            })
        );
    }

    function premiumPlaceholder(title) {
        return el(
            Placeholder,
            {
                icon: 'lock',
                label: title,
                instructions: __('This block is only available in the premium version.', 'liza-spotify-widget-for-elementor')
            },
            data.upgradeUrl
                ? el(Button, { variant: 'primary', href: data.upgradeUrl, target: '_blank' },
                    __('Upgrade to Premium', 'liza-spotify-widget-for-elementor'))
                : null
        );
    }

    function registerLizaBlock(name, config) {
        if (wp.blocks.getBlockType('liza-spotify/' + name)) {
            return; // already registered (free + pro both active)
        }

        registerBlockType('liza-spotify/' + name, {
            apiVersion: 2,
            title: config.title,
            description: config.description,
            icon: config.icon,
            category: 'liza-spotify',
            keywords: config.keywords || ['spotify', 'music'],
            supports: { html: false },
            edit: function (props) {
                var blockProps = useBlockProps();

                if (config.premium && !data.isPremium) {
                    return el('div', blockProps, premiumPlaceholder(config.title));
                }

                return el(
                    'div',
                    blockProps,
                    el(InspectorControls, {}, config.panels.map(function (panel, i) {
                        return renderPanel(props, panel, i);
                    })),
                    el(ServerSideRender, {
                        block: 'liza-spotify/' + name,
                        attributes: props.attributes
                    })
                );
            },
            save: function () {
                return null; // dynamic block, rendered in PHP
            }
        });
    }

    /* ── Spotify Embed ───────────────────────────────────────── */

    registerLizaBlock('spotify-embed', {
        title: __('Spotify Embed', 'liza-spotify-widget-for-elementor'),
        description: __('Embed a Spotify track, album, artist, playlist, episode, or show.', 'liza-spotify-widget-for-elementor'),
        icon: 'format-audio',
        keywords: ['spotify', 'embed', 'music'],
        panels: [
            {
                title: __('Spotify Content', 'liza-spotify-widget-for-elementor'),
                initialOpen: true,
                controls: [
                    {
                        type: 'text', attr: 'spotifyUrl', label: __('Spotify URL', 'liza-spotify-widget-for-elementor'),
                        placeholder: 'https://open.spotify.com/track/...',
                        help: __('Paste your Spotify track, album, artist, playlist, episode, or show URL here.', 'liza-spotify-widget-for-elementor')
                    }
                ]
            },
            {
                title: __('Appearance', 'liza-spotify-widget-for-elementor'),
                controls: [
                    {
                        type: 'select', attr: 'theme', label: __('Theme', 'liza-spotify-widget-for-elementor'),
                        options: [
                            { value: 'dark', label: __('Dark', 'liza-spotify-widget-for-elementor') },
                            { value: 'light', label: __('Light', 'liza-spotify-widget-for-elementor') }
                        ]
                    },
                    { type: 'range', attr: 'height', label: __('Height (px)', 'liza-spotify-widget-for-elementor'), min: 80, max: 1000 },
                    { type: 'range', attr: 'width', label: __('Width', 'liza-spotify-widget-for-elementor'), min: 10, max: 1000 },
                    {
                        type: 'select', attr: 'widthUnit', label: __('Width Unit', 'liza-spotify-widget-for-elementor'),
                        options: [
                            { value: '%', label: '%' },
                            { value: 'px', label: 'px' }
                        ]
                    },
                    {
                        type: 'select', attr: 'alignment', label: __('Alignment', 'liza-spotify-widget-for-elementor'),
                        options: [
                            { value: 'left', label: __('Left', 'liza-spotify-widget-for-elementor') },
                            { value: 'center', label: __('Center', 'liza-spotify-widget-for-elementor') },
                            { value: 'right', label: __('Right', 'liza-spotify-widget-for-elementor') }
                        ]
                    }
                ]
            }
        ]
    });

    /* ── Spotify Profile ─────────────────────────────────────── */

    registerLizaBlock('spotify-profile', {
        title: __('Spotify Profile', 'liza-spotify-widget-for-elementor'),
        description: __('Show your connected Spotify profile with a follow button.', 'liza-spotify-widget-for-elementor'),
        icon: 'admin-users',
        keywords: ['spotify', 'profile', 'follow'],
        panels: [
            {
                title: __('Content', 'liza-spotify-widget-for-elementor'),
                initialOpen: true,
                controls: [
                    { type: 'toggle', attr: 'showImage', label: __('Show Profile Image', 'liza-spotify-widget-for-elementor') },
                    { type: 'toggle', attr: 'showFollowers', label: __('Show Followers Count', 'liza-spotify-widget-for-elementor') },
                    { type: 'toggle', attr: 'showSpotifyLink', label: __('Show Follow Button', 'liza-spotify-widget-for-elementor') },
                    {
                        type: 'text', attr: 'buttonText', label: __('Button Text', 'liza-spotify-widget-for-elementor'),
                        placeholder: __('Follow on Spotify', 'liza-spotify-widget-for-elementor'),
                        showIf: function (a) { return a.showSpotifyLink; }
                    },
                    {
                        type: 'toggle', attr: 'showButtonIcon', label: __('Show Button Icon', 'liza-spotify-widget-for-elementor'),
                        showIf: function (a) { return a.showSpotifyLink; }
                    },
                    {
                        type: 'select', attr: 'buttonIcon', label: __('Button Icon', 'liza-spotify-widget-for-elementor'),
                        showIf: function (a) { return a.showSpotifyLink && a.showButtonIcon; },
                        options: [
                            { value: 'dashicons-spotify', label: __('Spotify', 'liza-spotify-widget-for-elementor') },
                            { value: 'dashicons-external', label: __('External Link', 'liza-spotify-widget-for-elementor') },
                            { value: 'dashicons-arrow-right-alt', label: __('Arrow Right', 'liza-spotify-widget-for-elementor') },
                            { value: 'dashicons-arrow-right', label: __('Arrow', 'liza-spotify-widget-for-elementor') },
                            { value: 'dashicons-plus', label: __('Plus', 'liza-spotify-widget-for-elementor') },
                            { value: 'dashicons-controls-play', label: __('Play', 'liza-spotify-widget-for-elementor') }
                        ]
                    },
                    {
                        type: 'select', attr: 'iconPosition', label: __('Icon Position', 'liza-spotify-widget-for-elementor'),
                        showIf: function (a) { return a.showSpotifyLink && a.showButtonIcon; },
                        options: [
                            { value: 'before', label: __('Before', 'liza-spotify-widget-for-elementor') },
                            { value: 'after', label: __('After', 'liza-spotify-widget-for-elementor') }
                        ]
                    },
                    {
                        type: 'select', attr: 'contentAlignment', label: __('Content Alignment', 'liza-spotify-widget-for-elementor'),
                        options: [
                            { value: 'left', label: __('Left', 'liza-spotify-widget-for-elementor') },
                            { value: 'center', label: __('Center', 'liza-spotify-widget-for-elementor') },
                            { value: 'right', label: __('Right', 'liza-spotify-widget-for-elementor') }
                        ]
                    }
                ]
            },
            {
                title: __('Image Style', 'liza-spotify-widget-for-elementor'),
                showIf: function (a) { return a.showImage; },
                controls: [
                    { type: 'range', attr: 'imageSize', label: __('Image Size (px)', 'liza-spotify-widget-for-elementor'), min: 50, max: 300 },
                    { type: 'range', attr: 'imageBorderRadius', label: __('Border Radius (%)', 'liza-spotify-widget-for-elementor'), min: 0, max: 50 }
                ]
            },
            {
                title: __('Button Style', 'liza-spotify-widget-for-elementor'),
                showIf: function (a) { return a.showSpotifyLink; },
                controls: [
                    { type: 'range', attr: 'buttonBorderRadius', label: __('Border Radius (px)', 'liza-spotify-widget-for-elementor'), min: 0, max: 50 }
                ]
            },
            {
                title: __('Colors', 'liza-spotify-widget-for-elementor'),
                colors: [
                    { attr: 'backgroundColor', label: __('Background', 'liza-spotify-widget-for-elementor') },
                    { attr: 'nameColor', label: __('Name', 'liza-spotify-widget-for-elementor') },
                    { attr: 'followersColor', label: __('Followers', 'liza-spotify-widget-for-elementor') },
                    { attr: 'buttonBgColor', label: __('Button Background', 'liza-spotify-widget-for-elementor') },
                    { attr: 'buttonTextColor', label: __('Button Text', 'liza-spotify-widget-for-elementor') },
                    { attr: 'buttonHoverBgColor', label: __('Button Hover Background', 'liza-spotify-widget-for-elementor') },
                    { attr: 'buttonHoverTextColor', label: __('Button Hover Text', 'liza-spotify-widget-for-elementor') }
                ]
            }
        ]
    });

    /* ── Spotify Now Playing (premium) ───────────────────────── */

    registerLizaBlock('spotify-now-playing', {
        title: __('Spotify Now Playing', 'liza-spotify-widget-for-elementor'),
        description: __('Live view of the track currently playing on your Spotify account.', 'liza-spotify-widget-for-elementor'),
        icon: 'controls-play',
        premium: true,
        keywords: ['spotify', 'now playing', 'live'],
        panels: [
            {
                title: __('Display Settings', 'liza-spotify-widget-for-elementor'),
                initialOpen: true,
                controls: [
                    {
                        type: 'select', attr: 'layout', label: __('Layout', 'liza-spotify-widget-for-elementor'),
                        options: [
                            { value: 'card', label: __('Card (Full Widget)', 'liza-spotify-widget-for-elementor') },
                            { value: 'compact', label: __('Compact', 'liza-spotify-widget-for-elementor') },
                            { value: 'inline', label: __('Inline Text', 'liza-spotify-widget-for-elementor') }
                        ]
                    },
                    {
                        type: 'toggle', attr: 'showArtwork', label: __('Show Album Artwork', 'liza-spotify-widget-for-elementor'),
                        showIf: function (a) { return a.layout !== 'inline'; }
                    },
                    { type: 'toggle', attr: 'showListenNow', label: __('Show Listen Now Button', 'liza-spotify-widget-for-elementor') },
                    { type: 'range', attr: 'refreshInterval', label: __('Refresh Interval (seconds)', 'liza-spotify-widget-for-elementor'), min: 1, max: 60 },
                    {
                        type: 'text', attr: 'placeholderText', label: __('Placeholder Text', 'liza-spotify-widget-for-elementor'),
                        help: __('Text to display when no track is currently playing.', 'liza-spotify-widget-for-elementor')
                    }
                ]
            },
            {
                title: __('Card Style', 'liza-spotify-widget-for-elementor'),
                showIf: function (a) { return a.layout !== 'compact' && a.layout !== 'inline'; },
                controls: [
                    { type: 'range', attr: 'padding', label: __('Padding (px)', 'liza-spotify-widget-for-elementor'), min: 0, max: 100 },
                    { type: 'range', attr: 'borderRadius', label: __('Border Radius (px)', 'liza-spotify-widget-for-elementor'), min: 0, max: 50 },
                    {
                        type: 'range', attr: 'artworkSize', label: __('Artwork Size (px)', 'liza-spotify-widget-for-elementor'), min: 40, max: 200,
                        showIf: function (a) { return a.showArtwork; }
                    },
                    { type: 'range', attr: 'progressHeight', label: __('Progress Bar Height (px)', 'liza-spotify-widget-for-elementor'), min: 1, max: 20 }
                ]
            },
            {
                title: __('Colors', 'liza-spotify-widget-for-elementor'),
                showIf: function (a) { return a.layout !== 'inline'; },
                colors: [
                    { attr: 'backgroundColor', label: __('Background', 'liza-spotify-widget-for-elementor') },
                    { attr: 'trackNameColor', label: __('Track Name', 'liza-spotify-widget-for-elementor') },
                    { attr: 'artistColor', label: __('Artist', 'liza-spotify-widget-for-elementor') },
                    { attr: 'albumColor', label: __('Album', 'liza-spotify-widget-for-elementor') },
                    { attr: 'placeholderColor', label: __('Placeholder', 'liza-spotify-widget-for-elementor') },
                    { attr: 'progressBgColor', label: __('Progress Background', 'liza-spotify-widget-for-elementor') },
                    { attr: 'progressColor', label: __('Progress', 'liza-spotify-widget-for-elementor') },
                    { attr: 'buttonBgColor', label: __('Button Background', 'liza-spotify-widget-for-elementor') },
                    { attr: 'buttonTextColor', label: __('Button Text', 'liza-spotify-widget-for-elementor') },
                    { attr: 'buttonHoverBgColor', label: __('Button Hover Background', 'liza-spotify-widget-for-elementor') }
                ]
            }
        ]
    });

    /* ── Spotify Artist (premium) ────────────────────────────── */

    registerLizaBlock('spotify-artist', {
        title: __('Spotify Artist', 'liza-spotify-widget-for-elementor'),
        description: __('Show a Spotify artist profile with stats, genres, and top tracks.', 'liza-spotify-widget-for-elementor'),
        icon: 'microphone',
        premium: true,
        keywords: ['spotify', 'artist', 'music'],
        panels: [
            {
                title: __('Artist Settings', 'liza-spotify-widget-for-elementor'),
                initialOpen: true,
                controls: [
                    {
                        type: 'text', attr: 'artistId', label: __('Artist ID/URL', 'liza-spotify-widget-for-elementor'),
                        help: __('Enter Spotify Artist ID or URL', 'liza-spotify-widget-for-elementor')
                    },
                    {
                        type: 'select', attr: 'layout', label: __('Layout', 'liza-spotify-widget-for-elementor'),
                        options: [
                            { value: 'horizontal', label: __('Horizontal', 'liza-spotify-widget-for-elementor') },
                            { value: 'vertical', label: __('Vertical', 'liza-spotify-widget-for-elementor') },
                            { value: 'compact', label: __('Compact', 'liza-spotify-widget-for-elementor') }
                        ]
                    },
                    { type: 'toggle', attr: 'showImage', label: __('Show Artist Image', 'liza-spotify-widget-for-elementor') }
                ]
            },
            {
                title: __('Layout Style', 'liza-spotify-widget-for-elementor'),
                controls: [
                    { type: 'range', attr: 'padding', label: __('Padding (px)', 'liza-spotify-widget-for-elementor'), min: 0, max: 100 },
                    { type: 'range', attr: 'borderRadius', label: __('Border Radius (px)', 'liza-spotify-widget-for-elementor'), min: 0, max: 50 },
                    {
                        type: 'range', attr: 'imageSize', label: __('Image Size (px)', 'liza-spotify-widget-for-elementor'), min: 50, max: 300,
                        showIf: function (a) { return a.showImage; }
                    },
                    {
                        type: 'range', attr: 'imageBorderRadius', label: __('Image Border Radius (%)', 'liza-spotify-widget-for-elementor'), min: 0, max: 50,
                        showIf: function (a) { return a.showImage; }
                    },
                    { type: 'range', attr: 'buttonBorderRadius', label: __('Button Border Radius (px)', 'liza-spotify-widget-for-elementor'), min: 0, max: 50 }
                ]
            },
            {
                title: __('Colors', 'liza-spotify-widget-for-elementor'),
                colors: [
                    { attr: 'backgroundColor', label: __('Background', 'liza-spotify-widget-for-elementor') },
                    { attr: 'textColor', label: __('Text', 'liza-spotify-widget-for-elementor') },
                    { attr: 'buttonBgColor', label: __('Button Background', 'liza-spotify-widget-for-elementor') },
                    { attr: 'buttonTextColor', label: __('Button Text', 'liza-spotify-widget-for-elementor') },
                    { attr: 'buttonHoverBgColor', label: __('Button Hover Background', 'liza-spotify-widget-for-elementor') },
                    { attr: 'buttonHoverTextColor', label: __('Button Hover Text', 'liza-spotify-widget-for-elementor') }
                ]
            }
        ]
    });

    /* ── Spotify Podcast Episodes ────────────────────────────── */

    registerLizaBlock('spotify-podcast', {
        title: __('Spotify Podcast Episodes', 'liza-spotify-widget-for-elementor'),
        description: __('List the latest episodes of a Spotify podcast show.', 'liza-spotify-widget-for-elementor'),
        icon: 'microphone',
        keywords: ['spotify', 'podcast', 'episodes', 'show'],
        panels: [
            {
                title: __('Podcast Settings', 'liza-spotify-widget-for-elementor'),
                initialOpen: true,
                controls: [
                    {
                        type: 'text', attr: 'showId', label: __('Show ID/URL', 'liza-spotify-widget-for-elementor'),
                        placeholder: 'https://open.spotify.com/show/...',
                        help: __('Enter Spotify Show (podcast) ID or URL', 'liza-spotify-widget-for-elementor')
                    },
                    {
                        type: 'text', attr: 'market', label: __('Market', 'liza-spotify-widget-for-elementor'),
                        help: __('Two-letter country code used for episode availability (e.g. US, DE, GB).', 'liza-spotify-widget-for-elementor')
                    },
                    {
                        type: 'select', attr: 'layout', label: __('Layout', 'liza-spotify-widget-for-elementor'),
                        options: [
                            { value: 'list', label: __('List', 'liza-spotify-widget-for-elementor') },
                            { value: 'grid', label: __('Grid', 'liza-spotify-widget-for-elementor') }
                        ]
                    },
                    { type: 'range', attr: 'episodesCount', label: __('Number of Episodes', 'liza-spotify-widget-for-elementor'), min: 1, max: 50 },
                    { type: 'toggle', attr: 'showHeader', label: __('Show Podcast Header', 'liza-spotify-widget-for-elementor') },
                    {
                        type: 'toggle', attr: 'showCover', label: __('Show Cover Art', 'liza-spotify-widget-for-elementor'),
                        showIf: function (a) { return a.showHeader; }
                    },
                    {
                        type: 'toggle', attr: 'showPublisher', label: __('Show Publisher', 'liza-spotify-widget-for-elementor'),
                        showIf: function (a) { return a.showHeader; }
                    },
                    {
                        type: 'toggle', attr: 'showFollow', label: __('Show Follow Button', 'liza-spotify-widget-for-elementor'),
                        showIf: function (a) { return a.showHeader; }
                    },
                    { type: 'toggle', attr: 'showArtwork', label: __('Show Episode Artwork', 'liza-spotify-widget-for-elementor') },
                    { type: 'toggle', attr: 'showDescription', label: __('Show Episode Description', 'liza-spotify-widget-for-elementor') },
                    { type: 'toggle', attr: 'showDate', label: __('Show Release Date', 'liza-spotify-widget-for-elementor') },
                    { type: 'toggle', attr: 'showDuration', label: __('Show Duration', 'liza-spotify-widget-for-elementor') }
                ]
            },
            {
                title: __('Layout Style', 'liza-spotify-widget-for-elementor'),
                controls: [
                    { type: 'range', attr: 'padding', label: __('Padding (px)', 'liza-spotify-widget-for-elementor'), min: 0, max: 100 },
                    { type: 'range', attr: 'borderRadius', label: __('Border Radius (px)', 'liza-spotify-widget-for-elementor'), min: 0, max: 50 },
                    {
                        type: 'range', attr: 'artworkSize', label: __('Artwork Size (px)', 'liza-spotify-widget-for-elementor'), min: 40, max: 120,
                        showIf: function (a) { return a.showArtwork && a.layout === 'list'; }
                    }
                ]
            },
            {
                title: __('Colors', 'liza-spotify-widget-for-elementor'),
                colors: [
                    { attr: 'accentColor', label: __('Accent', 'liza-spotify-widget-for-elementor') },
                    { attr: 'backgroundColor', label: __('Background', 'liza-spotify-widget-for-elementor') },
                    { attr: 'textColor', label: __('Text', 'liza-spotify-widget-for-elementor') }
                ]
            }
        ]
    });

    /* ── Apple Music Embed (premium) ─────────────────────────── */

    registerLizaBlock('apple-music-embed', {
        title: __('Apple Music Embed', 'liza-spotify-widget-for-elementor'),
        description: __('Embed an Apple Music song, album, or playlist.', 'liza-spotify-widget-for-elementor'),
        icon: 'format-audio',
        premium: true,
        keywords: ['apple', 'music', 'embed'],
        panels: [
            {
                title: __('Apple Music Settings', 'liza-spotify-widget-for-elementor'),
                initialOpen: true,
                controls: [
                    {
                        type: 'text', attr: 'appleMusicUrl', label: __('Apple Music URL', 'liza-spotify-widget-for-elementor'),
                        placeholder: 'https://music.apple.com/...',
                        help: __('Enter the Apple Music song, album, or playlist URL', 'liza-spotify-widget-for-elementor')
                    },
                    { type: 'range', attr: 'embedHeight', label: __('Embed Height (px)', 'liza-spotify-widget-for-elementor'), min: 100, max: 800, step: 10 },
                    { type: 'toggle', attr: 'showTitle', label: __('Show Title', 'liza-spotify-widget-for-elementor') },
                    {
                        type: 'text', attr: 'customTitle', label: __('Custom Title', 'liza-spotify-widget-for-elementor'),
                        placeholder: __('Leave empty to use Apple Music title', 'liza-spotify-widget-for-elementor'),
                        showIf: function (a) { return a.showTitle; }
                    },
                    { type: 'toggle', attr: 'showDescription', label: __('Show Description', 'liza-spotify-widget-for-elementor') },
                    {
                        type: 'textarea', attr: 'customDescription', label: __('Custom Description', 'liza-spotify-widget-for-elementor'),
                        showIf: function (a) { return a.showDescription; }
                    },
                    { type: 'toggle', attr: 'showPreview', label: __('Show Preview Info', 'liza-spotify-widget-for-elementor') }
                ]
            },
            {
                title: __('Layout Style', 'liza-spotify-widget-for-elementor'),
                controls: [
                    { type: 'range', attr: 'padding', label: __('Padding (px)', 'liza-spotify-widget-for-elementor'), min: 0, max: 100 },
                    { type: 'range', attr: 'borderRadius', label: __('Border Radius (px)', 'liza-spotify-widget-for-elementor'), min: 0, max: 50 },
                    { type: 'range', attr: 'embedBorderRadius', label: __('Embed Border Radius (px)', 'liza-spotify-widget-for-elementor'), min: 0, max: 50 }
                ]
            },
            {
                title: __('Colors', 'liza-spotify-widget-for-elementor'),
                colors: [
                    { attr: 'backgroundColor', label: __('Background', 'liza-spotify-widget-for-elementor') },
                    { attr: 'titleColor', label: __('Title', 'liza-spotify-widget-for-elementor') },
                    { attr: 'descriptionColor', label: __('Description', 'liza-spotify-widget-for-elementor') }
                ]
            }
        ]
    });

})(window.wp);
