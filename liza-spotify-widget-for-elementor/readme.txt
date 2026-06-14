=== Liza Widget For Spotify and Elementor ===
Contributors: fallentroj, freemius
Tags: spotify, elementor, music player, spotify embed, spotify widget
Stable tag: 4.0.1
Requires at least: 5.2
Tested up to: 7.0
Requires PHP: 7.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Add a Spotify widget to Elementor in seconds — embed tracks, artists, albums, playlists, and your live Now Playing status with zero code.

== Description ==

**Liza Widget For Spotify and Elementor** is the easiest way to embed Spotify content directly inside the [Elementor Page Builder](https://wordpress.org/plugins/elementor/). No shortcodes, no coding — just drag, drop, and connect your Spotify account.

### Why Liza Spotify Widget?

* **Works instantly** — authorize once via the settings page and search Spotify content from inside Elementor.
* **Multiple widget types** — embed tracks, albums, playlists, artist profiles, and a live Now Playing ticker.
* **Real-time Spotify Profile widget** — show your followers, top tracks, and currently playing song with live API data.
* **Apple Music Embed** — embed Apple Music content alongside Spotify.
* **Clean, lightweight output** — no bloat, just a fast responsive embed your visitors will love.

### Features

* Spotify Track / Album / Playlist Embed widget
* Spotify Artist Profile widget with real-time stats
* Spotify Now Playing widget (live polling via Spotify API)
* Apple Music Embed widget
* One-click Spotify account authorization inside WordPress admin
* Built-in Spotify search inside the Elementor editor — find and insert content without leaving the page
* Works with any Elementor theme

### How to get started

1. Install and activate the plugin.
2. Go to **Settings → Liza Spotify** and authorize your Spotify account.
3. Open any page in Elementor, search for "Spotify" in the widget panel, and drag a widget onto your page.

📹 [Watch the setup tutorial](https://youtu.be/HbL8ERGBquk?si=8ErHDMorbyG8iAPK)

Built with love by [RuthlessWP / Nikusha Sirbiladze](https://ruthlesswp.com).

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install it directly from the WordPress plugin repository.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Navigate to **Settings → Liza Spotify** and enter your Spotify API credentials (Client ID and Client Secret) obtained from the [Spotify Developer Dashboard](https://developer.spotify.com/dashboard).
4. Authorize your Spotify account.
5. Open any page or post in Elementor and use the Liza Spotify widgets from the widget panel.

== Frequently Asked Questions ==

= Do I need a Spotify account? =
Yes. A free Spotify account is enough to embed public tracks, albums, and playlists. A Spotify Developer app (free) is required to use the Now Playing and Profile widgets.

= Is Elementor required? =
Yes, the widgets are built for the Elementor Page Builder (free version supported).

= How do I get Spotify API credentials? =
Go to [developer.spotify.com/dashboard](https://developer.spotify.com/dashboard), create a free app, and copy the Client ID and Client Secret into **Settings → Liza Spotify**.

= Does the Now Playing widget update automatically? =
Yes, it polls the Spotify API periodically and updates the display without a page reload.

= Is this plugin free? =
The core plugin is free. Premium features are available via an optional upgrade.

= Where can I get support? =
Post in the [plugin support forum](https://wordpress.org/support/plugin/liza-spotify-widget-for-elementor/) on WordPress.org.

== Screenshots ==

1. Spotify Embed widget inside the Elementor editor.
2. Spotify Now Playing widget on the front end.
3. Spotify Artist Profile widget with real-time stats.
4. Plugin settings page — Spotify API authorization.

== Changelog ==

= 3.0 =
* Added Spotify Profile widget with real-time follower and track stats.
* Added built-in Spotify search inside the Elementor editor.
* New settings page with one-click Spotify account authorization.
* Improved embed widget — easier track and artist sharing.
* Added Apple Music Embed widget.
* Performance and stability improvements.

== Upgrade Notice ==

= 3.0 =
Major update: new Spotify Profile widget, in-editor search, and redesigned settings page. Reauthorize your Spotify account after upgrading.

== External Services ==

This plugin connects to the following external services:

### Spotify API

Used to fetch and display music, playlists, albums, and artist information.

- **Data sent**: Search queries, artist/playlist/track IDs, and (when authorized) your Spotify account token.
- **When**: Whenever a widget requests or refreshes Spotify content.
- [Spotify Developer Terms of Service](https://developer.spotify.com/terms/)
- [Spotify Privacy Policy](https://www.spotify.com/legal/privacy-policy/)

### Freemius

Used for license management, plugin updates, and optional usage analytics.

- **Data sent**: Site URL, WordPress version, plugin version, PHP version, and (only if opted in) email address.
- **When**: On plugin activation, updates, or deactivation.
- [Freemius Terms of Service](https://freemius.com/terms/)
- [Freemius Privacy Policy](https://freemius.com/privacy/)
