=== WM Modular Scale ===
Contributors: arnoldwender
Tags: typography, modular scale, design tokens, css clamp, generateblocks
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.4.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Typographic scale calculator, fluid clamp() token generator and Gutenberg / GeneratePress / GenerateBlocks typography bridge for WordPress.

== Description ==

**WM Modular Scale** calculates a modular type scale from one base size and one ratio and prints it as fluid CSS `clamp()` tokens between two viewport widths (default 360 px to 1440 px), with bridges to `theme.json`, GeneratePress, GenerateBlocks 1.x / 2.x and WordPress Block Bindings.

### Features

* **Ratios:** Major Second (1.125), Minor Third (1.200), Major Third (1.250), Perfect Fourth (1.333), Augmented Fourth (1.414), Perfect Fifth (1.500), Golden Ratio (1.618), or any custom value from 1 (extensible via `wm_modularscale_ratios`).
* **Fluid tokens:** `--wm-step-neg-2` to `--wm-step-6` and `--ms-neg-2` to `--ms-6` as `clamp(min, preferred, max)` between the two viewport widths, each step checked against WCAG 1.4.4 zoom (max no more than 2.5 × min).
* **theme.json bridge:** the nine steps become the theme's `settings.typography.fontSizes`, and with them the `--wp--preset--font-size--wm-step-*` properties.
* **Seven Gutenberg blocks with compiled sidebar controls (`build/index.js`):** Fluid Heading, Fluid Lead Text, Fluid Container (`@container`), Typographic Grid, Ratio Visualizer (with light/dark theme and step-card toggle), APCA Contrast Badge (with target role selector), and Theme.json & Tailwind v4 Exporter.
* **GenerateBlocks 2.x & Block Bindings:** registers `{{wm_scale_token}}` via `GenerateBlocks_Register_Dynamic_Tag`, the `wm-modularscale/tokens` Block Bindings source, two ready-to-insert block patterns, and the `wm-modularscale/compute-scale` WordPress 6.9 Ability.
* **No external requests:** no CDN, no Google Fonts (the GeneratePress bridge removes them), no tracking.
* **Opt-in clean uninstall:** preserves your typographic configuration on plugin removal by default, with an explicit settings checkbox (`wmmsp_delete_data_on_uninstall`) to remove all stored options on uninstall.
* **28 full UI & Editor locales:** ships complete `.po`, `.mo`, and Gutenberg JED `.json` translation catalogs across all 24 official EU languages plus `en_US`, `fi`, `tr_TR` and `ar`.

**No Support & Zero Liability ("AS IS"):** This plugin is provided free of charge as personal open-source software with **zero support** and **no private support**. Do not contact the author via email, `arnoldwender.com`, or direct messages. Report reproducible defects exclusively at `https://github.com/arnoldwender/wm-wp-modularscale/issues` (no SLA or response guarantee). Under GPLv2+ Sections 11-12 it is distributed **"AS IS"** without warranty, legal advice, or author liability.

== Installation ==

1. Upload the `wm-modularscale` folder to the `/wp-content/plugins/` directory, or upload the ZIP file via **Plugins > Add New > Upload Plugin**.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Open **Modular Scale** in the admin sidebar (**WM Suite > Modular Scale** when the WM Suite Hub is active, also **Settings > Modular Scale**).
4. Pick a ratio preset or type one, set the base font size, unit and decimals, and check the live sandbox.
5. Enable the site-wide apply checkbox and save to print the fluid typography tokens.

== Frequently Asked Questions ==

= Do I need programming skills to use this plugin? =
No. The plugin includes a visual live preview sandbox where you can preview headings and body text in real-time before applying them to your theme, plus visual sidebar controls on all seven Gutenberg blocks.

= Is it compatible with Block Themes, GeneratePress and GenerateBlocks 2.x? =
Yes. The `theme.json` bridge registers the nine fluid steps as font size presets, the GeneratePress bridge maps `--gp-font-size-*` tokens and removes external Google Fonts, and `Ecosystem_Bridge` registers `{{wm_scale_token}}` for GenerateBlocks 2.x alongside `--ms-*` and `--wm-step-*` CSS variables.

= Does uninstalling delete my typography settings? =
Not unless you ask it to. By default `uninstall.php` keeps your saved scale options so reinstalling restores your typography; tick the delete-on-uninstall option on the settings screen if you want every `wmmsp_*` option removed upon deletion.

= Does it slow down my website? =
No. It loads pure inline/stylesheet CSS without external CDN calls or runtime JavaScript execution on the public frontend.

= Is technical or private support provided? =

No. This plugin comes with zero support (`0` support). Private inquiries sent via email, `arnoldwender.com`, or social/chat channels are not answered. Reproducible bugs belong solely on GitHub Issues (`https://github.com/arnoldwender/wm-wp-modularscale/issues`) with no guarantee of reply or resolution. The software is provided **"AS IS"** at your sole risk with zero warranty or liability (see `SUPPORT.md` in the repository).

== Screenshots ==

1. Modular Scale settings screen with ratio presets, WCAG 1.4.4 zoom bounds and live clamp() sandbox
2. Block editor showing wm-scale/ratio-visualizer (step curve and inspector sidebar)
3. Frontend rendering of Fluid Heading, Ratio Visualizer and APCA Contrast Badge blocks
4. WordPress dashboard widget displaying base font size, active ratio and H1-H3 tokens

== Changelog ==


= 1.4.1 =
* Fixed: the block editor showed the plugin's blocks without the scale (the token sheet reached the admin page around the editor canvas, not the canvas). It is enqueued on `enqueue_block_assets` in the admin now; the front end is unchanged.
* The bundled family stylesheet is identical to WM Suite Hub 1.2.5 again: it starts the admin header below WordPress's help toggle instead of shortening it. This plugin's own screens register no help tab, so they change only where another plugin adds one.

= 1.4.0 =
* The WM Suite Hub contract now lives in the `ArnoldWender\SuiteHub\Contracts` namespace. The spoke adapter registers only with WM Suite Hub 1.2.0 or later, so update the hub together with this plugin; used on its own, the plugin works as before.
* Renamed the plugin's own namespace to `ArnoldWender\ModularScale\…` (same vendor as the hub).
* The `.wordpress-org/` banners show the current version.
* Removed: The archived pre-2026-11-19 marketing visuals in `versionen/`: they claimed features and conformity the plugin does not have. They were never part of the installable package.

= 1.3.3 =
* Removed the agency name, links to unrelated projects and the personal mailbox from code, catalogs and documentation; the admin family badge and the hub menu read "WM Suite". The READMEs say the plugin was built with an agentic workflow (AI coding agents) and should be reviewed like any third-party code.
* Added root `SUPPORT.md`, GitHub Issue templates, explicit zero-support & "AS IS" liability notices in READMEs and `readme.txt`, and normalized canonical GPLv2+ `arnoldwender.com` / `arnoldwender/wm-wp-modularscale` links.
* Updated `.wordpress-org/` visual banners to personal `ARNOLD WENDER · OPEN SOURCE` branding.

= 1.3.2 =
* Add `.wordpress-org/` vector icons, banners (`772x250`, `1544x500`), live WordPress screenshots (`screenshot-1..4.png`), and updated `== Screenshots ==` descriptions.
* Update `README*.md`, `docs/translations/README.*.md`, and `llms.txt` with live visual previews and direct catalog link (`https://arnoldwender.com/wordpress-plugins/wm-modularscale/`).
* Archive superseded `assets/images/modular-scale-banner.jpg` and `docs/screenshots/wm-modular-scale.png` into `versionen/visuals-pre-2026-11-19/` and exclude `versionen/` via `.distignore`.

= 1.3.1 =
* Point Plugin URI to the official plugin page.

= 1.3.0 =
* Added compiled Gutenberg editor bundle (`build/index.js` + `build/index.asset.php`) and `editorScript` across all seven `wm-scale/*` blocks so all seven appear in the block inserter with full `InspectorControls` sidebar settings.
* Wired all five previously inert block attributes in server renders and editor previews: `step` (`wm-scale/fluid-heading`), `showSteps` and `theme` (`wm-scale/ratio-visualizer`), `targetContext` (`wm-scale/contrast-matrix-badge`), and `format` (`wm-scale/theme-json-exporter`).
* Added `Ecosystem_Bridge` (`src/Integration/Ecosystem_Bridge.php`): WordPress 6.5+ Block Bindings source `wm-modularscale/tokens`, GenerateBlocks 2.x Dynamic Tag `{{wm_scale_token}}`, two Core / GenerateBlocks 2.x block patterns, WordPress 6.9 Ability `wm-modularscale/compute-scale`, and developer hooks (`wm_modularscale_ratios`, `wm_modularscale_scale_options`, `wm_modularscale_tokens_generated`, `wm_modularscale_ready`).
* Added `--ms-neg-2` through `--ms-6` (`--ms-0` … `--ms-6`) CSS custom property aliases alongside `--wm-step-*` and `--gp-font-size-*`.
* Added opt-in data deletion on uninstall (`wmmsp_delete_data_on_uninstall`, default off) so `uninstall.php` preserves settings unless explicitly enabled.
* Completed all 28 `.po`, `.mo` and Gutenberg JED `.json` translation catalogs (193 source strings, 0 untranslated entries), added WordPress Playground `.wordpress-org/blueprints/blueprint.json` and CycloneDX 1.6 `bom.json`, and raised `declare(strict_types=1)` coverage to every PHP file in the repository.

= 1.2.2 =
* Fix: the SBOM the plugin reports to the WM Suite Hub declared it proprietary and named version 1.2.0. It now declares GPL-2.0-or-later, as an SPDX id alone, and reads the version from the plugin header. The notes of the 1.2.1 release announced the licence fix, but that package did not contain it.
* Fix: Plugin::VERSION, which versions the token stylesheet, said 1.2.0, so browsers kept serving the old sheet after an update.
* 118 of the 123 translatable strings had no entry in the .pot, so no translator could see them in any language. The catalog is regenerated from the code; every .po is merged and every .mo rebuilt, and the 2,007 entries that no longer match a string are kept as obsolete instead of discarded. The `ar` catalog that measured 70 of 70 translated was translating the Spanish settings page this plugin no longer has: 69 of those 70 had already stopped appearing. tests/test-pot-freshness.php and a CI step keep it from drifting again.

= Also in the 1.2.0 and 1.2.1 releases (2026-09-19) =
* Saving the settings page no longer writes 0 into the viewport and maximum base size options, which turned every fluid step into an inverted clamp().
* Settings page in German; its sandbox shows the CSS variables the plugin really prints.
* APCA badge: Lc as apca-w3 0.1.9 computes it and a usage hint instead of a BFSG conformity label. Ratio visualizer: nodes at the real step sizes, H1 at the top step.
* Settings page and dashboard widget on the WM Suite family sheets (shared tokens and components, visible focus rings, native wrap, family badge); the live sandbox script ships from assets/js instead of inline.
* The admin enqueue pointed at a file that does not exist (404 on every settings page load); fixed.
* The frontend sheet no longer shrinks the base font size to 14px / 12px on small viewports.

= 1.2.1 =
* Fix: `package.json` pointed at a repository that is not public. It now names github.com/arnoldwender/wm-wp-modularscale.
* Docs: the author line said "Lead Architect" (and "Chefarchitekt", "capo architetto", "glowny architekt" ... in the translated READMEs). In Germany "Architekt" is a title reserved by the Architektengesetz of each Land; the line reads Web Developer now, in each language.
* Tests: the mutation runner copied the tree with rsync and silenced the error; rsync is absent from ordinary CI images, so the copy came out empty and the runner blamed the suite. It uses tar now, says what failed, and refuses a copy shorter than what git listed.

= 1.2.0 =
* Scale engine, seven blocks, theme.json, GeneratePress and GenerateBlocks bridges, WM Suite Hub adapter, admin menu and dashboard widget.
* .po / .mo files for 24 EU languages added; their entries were not translated.

= 1.1.0 =
* Initial enterprise release with mathematical ratio calculator and CSS clamp generator.

== Upgrade Notice ==

= 1.4.1 =
The blocks show the scale in the block editor again, and the family sheet of WM Suite Hub 1.2.5. No data or settings change.

= 1.4.0 =
Hub users: update WM Suite Hub to 1.2.0 together with this plugin. The hub contract moved to a new PHP namespace; next to an older hub the plugin keeps working, but it is not listed in the cockpit and its admin page has no menu entry until the hub is updated.
