=== Liza Widget For Spotify and Elementor ===
Contributors: fallentroj, freemius
Tags: spotify, elementor, gutenberg, podcast, music player
Stable tag: 4.2.0
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Add Spotify widgets to Elementor or Gutenberg in seconds — embed tracks, albums, playlists, your Spotify profile, and podcast episode lists with zero code.

== Description ==

**Liza Widget For Spotify and Elementor** is the easiest way to embed Spotify content directly inside the [Elementor Page Builder](https://wordpress.org/plugins/elementor/) **or the Gutenberg block editor**. No shortcodes, no coding — just drag, drop, and connect your Spotify account.

### Why Liza Spotify Widget?

* **Works instantly** — authorize once via the settings page and use the widgets in Elementor or Gutenberg.
* **Elementor AND Gutenberg** — every widget is also available as a native block with live preview, so Elementor is no longer required.
* **Podcast Episodes widget** — show the latest episodes of any Spotify podcast, automatically kept up to date.
* **Real-time Spotify Profile widget** — show your Spotify profile with live API data.
* **Beautiful by default** — a fully redesigned widget look with a consistent design system, and full color/typography controls when you want to make it your own.
* **Clean, lightweight output** — no build tools, no bloat, just fast responsive widgets your visitors will love.

### Features

* **Spotify Embed widget** — embed any track, album, playlist, artist, episode, or show
* **Spotify Podcast Episodes widget** — list the latest episodes of any podcast with cover art, artwork, descriptions, dates, and durations in a list or grid layout
* **Spotify Profile widget** — display your connected Spotify profile with real-time data
* Native Gutenberg blocks for every widget with live preview in the editor — no Elementor required
* Redesigned widget styling with accent color, background, text color, radius, and typography controls
* One-click Spotify account authorization inside WordPress admin, with a guided setup wizard
* Smart caching so your pages stay fast and API limits are never a problem
* Works with any theme

### How to get started

1. Install and activate the plugin.
2. Go to **Liza Spotify → Settings** and authorize your Spotify account.
3. Add a widget in Elementor (search "Spotify" in the widget panel) or insert a block in Gutenberg (look for the "Spotify Widgets" category).

📹 [Watch the setup tutorial](https://youtu.be/HbL8ERGBquk?si=8ErHDMorbyG8iAPK)

Built with love by [RuthlessWP / Nikusha Sirbiladze](https://ruthlesswp.com).

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install it directly from the WordPress plugin repository.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Navigate to **Liza Spotify → Settings** and enter your Spotify API credentials (Client ID and Client Secret) obtained from the [Spotify Developer Dashboard](https://developer.spotify.com/dashboard).
4. Authorize your Spotify account.
5. Add the widgets from the Elementor widget panel or the Gutenberg block inserter.

== Frequently Asked Questions ==

= Do I need a Spotify account? =
Yes. A free Spotify account is enough. A Spotify Developer app (free to create) is required so the plugin can fetch data like your profile and podcast episodes.

= Is Elementor required? =
No. Since version 4.1 every widget is also available as a native Gutenberg block. If Elementor is installed, the Elementor widgets are available as well.

= How do I get Spotify API credentials? =
Go to [developer.spotify.com/dashboard](https://developer.spotify.com/dashboard), create a free app, and copy the Client ID and Client Secret into **Liza Spotify → Settings**.

= How does the Podcast Episodes widget work? =
Paste a Spotify show URL or ID and the widget lists the newest episodes — with cover art, descriptions, release dates, and durations. Episode data is cached and refreshed automatically, so new episodes appear on your site without you touching anything.

= Can I style the widgets to match my site? =
Yes. Every widget ships with a polished default design and exposes accent, background, and text colors, border radius, padding, and typography controls in both Elementor and Gutenberg.

= Is this plugin free? =
The core plugin is free. Additional widgets are available via an optional upgrade.

= Where can I get support? =
Post in the [plugin support forum](https://wordpress.org/support/plugin/liza-spotify-widget-for-elementor/) on WordPress.org.

== Screenshots ==

1. Spotify Embed widget inside the Elementor editor.
2. Spotify Podcast Episodes widget on the front end.
3. Spotify Profile widget with real-time data.
4. Plugin settings page — Spotify API authorization.

== Changelog ==

= 4.2.0 =
* NEW: Spotify Podcast Episodes widget + block (free) — list the latest episodes of any Spotify show with cover art, episode artwork, descriptions, release dates, and durations, in a list or grid layout.
* NEW: Complete visual redesign of all widgets — a consistent, modern design system with accent color support, refined typography, and polished hover/focus states.
* NEW: Redesigned settings page and dashboard connection widget.
* Improved: Podcast feeds that Spotify lists oldest-first are now detected automatically so the newest episodes always show first.
* Improved: Smarter API caching — podcast and show data is cached for an hour, and the header + episode list is usually served from a single API call.
* Improved: Episodes unavailable in the selected market are filtered out instead of rendering empty rows.
* Fixed: Translation loading — all plugin strings now use the correct text domain.

= 4.1.0 =
* NEW: Full Gutenberg (block editor) support — every widget is now also a native block with live preview: Spotify Embed, Spotify Profile, Spotify Now Playing, Spotify Artist, and Apple Music Embed.
* Elementor is no longer required — the plugin now works with Gutenberg alone. Elementor widgets keep working exactly as before when Elementor is active.
* Improved Apple Music URL handling (modern album URLs and playlist embeds).
* Now Playing refresh script rewritten in dependency-free JavaScript for block usage.

= 3.0 =
* Added Spotify Profile widget with real-time follower and track stats.
* Added built-in Spotify search inside the Elementor editor.
* New settings page with one-click Spotify account authorization.
* Improved embed widget — easier track and artist sharing.
* Added Apple Music Embed widget.
* Performance and stability improvements.

== Upgrade Notice ==

= 4.2.0 =
New free Spotify Podcast Episodes widget + block, plus a complete visual redesign of all widgets.

= 4.1.0 =
Adds full Gutenberg block support — all widgets now work without Elementor.

= 3.0 =
Major update: new Spotify Profile widget, in-editor search, and redesigned settings page. Reauthorize your Spotify account after upgrading.

== External Services ==

This plugin connects to the following external services:

### Spotify API

Used to fetch and display music, playlists, albums, artist information, and podcast episodes.

- **Data sent**: Search queries, artist/playlist/track/show IDs, and (when authorized) your Spotify account token.
- **When**: Whenever a widget requests or refreshes Spotify content.
- [Spotify Developer Terms of Service](https://developer.spotify.com/terms/)
- [Spotify Privacy Policy](https://www.spotify.com/legal/privacy-policy/)

### Freemius

Used for license management, plugin updates, and optional usage analytics.

- **Data sent**: Site URL, WordPress version, plugin version, PHP version, and (only if opted in) email address.
- **When**: On plugin activation, updates, or deactivation.
- [Freemius Terms of Service](https://freemius.com/terms/)
- [Freemius Privacy Policy](https://freemius.com/privacy/)
