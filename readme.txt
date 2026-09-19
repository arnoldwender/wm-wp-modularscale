=== WM Modular Scale ===
Contributors: arnoldwender
Tags: typography, modular scale, design tokens, css clamp, tailwindcss, gutenberg
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Typographic scale calculator, fluid clamp() token generator and Gutenberg / GeneratePress / GenerateBlocks typography bridge for WordPress.

== Description ==

**WM Modular Scale** calculates a modular type scale from one base size and one ratio and prints it as fluid CSS `clamp()` tokens between two viewport widths (default 360 px to 1440 px), with bridges to theme.json, GeneratePress and GenerateBlocks.

### Features

* **Ratios:** Major Second (1.125), Minor Third (1.200), Major Third (1.250), Perfect Fourth (1.333), Augmented Fourth (1.414), Perfect Fifth (1.500), Golden Ratio (1.618), or any value from 1.
* **Fluid tokens:** `--wm-step-neg-2` to `--wm-step-6` as `clamp(min, preferred, max)` between the two viewport widths, each step checked against WCAG 1.4.4 zoom (max no more than 2.5 × min).
* **theme.json bridge:** the nine steps become the theme's `settings.typography.fontSizes`, and with them the `--wp--preset--font-size--wm-step-*` properties.
* **Seven blocks:** fluid heading, lead text, container, grid, ratio visualizer, APCA contrast badge, theme.json exporter.
* **No external requests:** no CDN, no Google Fonts (the GeneratePress bridge removes them), no tracking.
* **Languages:** the admin screens are in German. `languages/` holds `.po` / `.mo` files for the 24 official EU languages that are not translated yet.

== Installation ==

1. Upload the `wm-modularscale` folder to the `/wp-content/plugins/` directory, or upload the ZIP file via **Plugins > Add New > Upload Plugin**.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Open **Modular Scale** in the admin sidebar (**Wender Media > Modular Scale** when the WM Suite Hub is active, also **Settings > Modular Scale**).
4. Pick a ratio preset or type one, set the base font size, unit and decimals, and check the live sandbox.
5. Tick "Skala auf der Website anwenden" and save to print the fluid typography tokens.

== Frequently Asked Questions ==

= Do I need programming skills to use this plugin? =
No. The plugin includes a visual live preview sandbox where you can preview headings and body text in real-time before applying them to your theme.

= Is it compatible with Block Themes and Full-Site Editing? =
The theme.json bridge replaces the theme's font size presets with the nine fluid steps, so they appear in the block editor's font size control.

= Does it slow down my website? =
No. It loads pure CSS without external CDN calls or runtime JavaScript execution on the frontend.

== Screenshots ==

1. `assets/images/modular-scale-banner.jpg` - Promotional banner.
2. `assets/images/modular-scale-banner.jpg` - Admin settings cockpit.

== Changelog ==

= Unreleased =
* Saving the settings page no longer writes 0 into the viewport and maximum base size options, which turned every fluid step into an inverted clamp().
* Settings page in German; its sandbox shows the CSS variables the plugin really prints.
* APCA badge: Lc as apca-w3 0.1.9 computes it and a usage hint instead of a BFSG conformity label. Ratio visualizer: nodes at the real step sizes, H1 at the top step.
* Settings page and dashboard widget on the Wender Media family sheets (shared tokens and components, visible focus rings, native wrap, family badge); the live sandbox script ships from assets/js instead of inline.
* The admin enqueue pointed at a file that does not exist (404 on every settings page load); fixed.
* The frontend sheet no longer shrinks the base font size to 14px / 12px on small viewports.

= 1.2.0 =
* Scale engine, seven blocks, theme.json, GeneratePress and GenerateBlocks bridges, WM Suite Hub adapter, admin menu and dashboard widget.
* .po / .mo files for 24 EU languages added; their entries were not translated.

= 1.1.0 =
* Initial enterprise release with mathematical ratio calculator and CSS clamp generator.
