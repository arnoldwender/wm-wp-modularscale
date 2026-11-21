# Changelog — WM Modular Scale

All notable changes to **WM Modular Scale** are documented in this file in adherence to [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) and [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.4.1] - 2026-11-21

### Fixed

- The block editor showed the plugin's blocks without the scale. The token sheet was enqueued on `enqueue_block_editor_assets`, which reaches the admin page around the editor canvas but not the canvas itself, an iframe: inside it `--wm-step-*` and the `.has-fluid-*-font-size` classes were missing, so a Fluid Heading or Fluid Lead Text took the theme's size and the Typographic Grid its 1.5rem fallback gap. The sheet is now enqueued on `enqueue_block_assets` in the admin only (on the front end the token output and its apply toggle stay in charge). Measured with a Fluid Heading H2 in the iframed editor: `--wm-step-5` empty and 38.99 px from the theme before, 53.35 px from the scale after.

### Changed

- All 28 editor catalogs (`.json`) are rebuilt from their `.po`, so the block titles, descriptions and keywords carry their context as WordPress writes it, and the catalogs carry the plugin page as Plugin URI and the Author URI as the plugin header has it.
- The first screenshot shows the current settings screen.

- The bundled family stylesheet is identical to WM Suite Hub 1.2.5 again: it starts the admin header below WordPress's help toggle instead of shortening it. This plugin's own screens register no help tab, so they change only where another plugin adds one.

## [1.4.0] - 2026-11-20

### Changed
- The WM Suite Hub contract now lives in the `ArnoldWender\SuiteHub\Contracts` namespace. The spoke adapter registers only with WM Suite Hub 1.2.0 or later, so update the hub together with this plugin; used on its own, the plugin works as before.
- Renamed the plugin's own namespace to `ArnoldWender\ModularScale\…` (same vendor as the hub).
- The `.wordpress-org/` banners show the current version.

### Removed
- The archived pre-2026-11-19 marketing visuals in `versionen/`: they claimed features and conformity the plugin does not have. They were never part of the installable package.

## [1.3.3] - 2026-11-19

- Removed the agency name, links to unrelated projects and the personal mailbox from code, catalogs and documentation; the admin family badge and the hub menu read "WM Suite". The READMEs say the plugin was built with an agentic workflow (AI coding agents) and should be reviewed like any third-party code.

- Added root `SUPPORT.md`, `.github/ISSUE_TEMPLATE/{config.yml,bug_report.yml}`, and explicit zero-support / "AS IS" liability notices across `README.md`, `README.de.md`, `README.es.md`, `readme.txt`, `SECURITY.md`, and `llms.txt`.
- Migrated plugin author/catalog/repository metadata to personal open-source distribution (`Arnold Wender`, `https://arnoldwender.com/wordpress-plugins/wm-modularscale/`, `https://github.com/arnoldwender/wm-wp-modularscale`), normalized FSF GPL-2.0 `LICENSE` metadata where applicable, and updated `.wordpress-org/` banner branding to `ARNOLD WENDER · OPEN SOURCE`.

## [Unreleased]

## [1.3.2] - 2026-11-19

### Added
- Official `.wordpress-org/` visual kit (`icon.svg`, `icon-128x128.png`, `icon-256x256.png`, `banner-772x250.png`, `banner-1544x500.png`) and 4 live Playwright WordPress screenshots (`screenshot-1.png` through `screenshot-4.png`) with `== Screenshots ==` in `readme.txt`.
- Direct catalog URL (`https://arnoldwender.com/wordpress-plugins/wm-modularscale/`) and live visual previews across `README.md`, `README.de.md`, `README.es.md`, `docs/translations/README.*.md`, and `llms.txt`.

### Changed
- Archived superseded `assets/images/modular-scale-banner.jpg` and `docs/screenshots/wm-modular-scale.png` non-destructively into `versionen/visuals-pre-2026-11-19/` and excluded `versionen/` in `.distignore` (keeping `docs/screenshots/design-family/` intact).
- Aligned version metadata at `1.3.2` across `wm-modularscale.php`, `src/Plugin.php`, `package.json`, all 7 `block.json` manifests, `readme.txt`, `llms.txt`, `bom.json`, `DEVELOPERS.md`, `docs/ARCHITECTURE.md`, `tests/test-modularscale.php`, and `tests/mutations.php`.

## [1.3.1] - 2026-11-19

### Changed
- Point `Plugin URI` to `https://arnoldwender.com/wordpress-plugins/wm-modularscale/` and align release metadata at `v1.3.1`.

## [1.3.0] - 2026-11-16

### Added

- **Compiled Gutenberg editor bundle (`build/index.js` + `build/index.asset.php`):** all seven `wm-scale/*` blocks (`fluid-heading`, `fluid-lead`, `fluid-container`, `typographic-grid`, `ratio-visualizer`, `contrast-matrix-badge`, `theme-json-exporter`) now declare `"editorScript": "wm-modularscale-blocks-editor"` in `block.json`, appear in the Gutenberg block inserter, enqueue live fluid CSS tokens inside the editor canvas, and expose `InspectorControls` sidebar panels (`RichText`, `InnerBlocks`, `ServerSideRender`). Built cleanly via `scripts/build-blocks.mjs` (`npm run build`) with Vitest coverage in `tests/blocks-editor.test.ts`.
- **Wired all five previously inert block attributes:** `step` (`wm-scale/fluid-heading`, explicit modular scale step `-2 … +6` when differing from the heading level's default), `showSteps` and `theme` (`wm-scale/ratio-visualizer`, step-card toggle and `dark` / `light` palette), `targetContext` (`wm-scale/contrast-matrix-badge`, context-specific Lc target hints for `body` Lc 75, `ui` Lc 60, `headline` Lc 45), and `format` (`wm-scale/theme-json-exporter`, `both`, `json`, or `tailwind`).
- **WordPress 6.5–6.9 & GenerateBlocks 2.x ecosystem bridge (`src/Integration/Ecosystem_Bridge.php`):**
  - WordPress 6.5+ Block Bindings source `wm-modularscale/tokens` (`step`, `format`: `clamp`, `px_range`, `max_px`, `min_px`, `var`).
  - GenerateBlocks 2.x Dynamic Tag `{{wm_scale_token}}` registered through `GenerateBlocks_Register_Dynamic_Tag` when present.
  - Block pattern category `wm-modularscale` and patterns `wm-modularscale/typographic-specimen` and `wm-modularscale/gb-fluid-hero` (`gbp-section` + `gbp-section__inner`).
  - WordPress 6.9 Ability `wm-modularscale/compute-scale` (`wp_register_ability`).
  - Developer hooks: filters `wm_modularscale_ratios`, `wm_modularscale_scale_options`, `wm_modularscale_tokens_generated`, and action `wm_modularscale_ready`.
- **`--ms-*` CSS custom property aliases:** `CSS_Token_Generator::generate_stylesheet()` now emits `--ms-neg-2 … --ms-6` (`--ms-0 … --ms-6`) alongside `--wm-step-*`, `--wm-font-size-*`, and `--gp-font-size-*`.
- **Opt-in data deletion on uninstall (`wmmsp_delete_data_on_uninstall`):** `uninstall.php` preserves stored scale options by default so reinstalling restores the site's typography, and deletes all eleven `wmmsp_*` options (single-site and multisite) only when the settings-page checkbox is enabled.
- **Complete 28-locale UI & Block Editor translations:** regenerated `languages/wm-modularscale.pot` (193 entries), populated all 28 `.po` catalogs, compiled all 28 `.mo` binaries with `msgfmt --check-format`, and generated all 28 Gutenberg JED 1.x `.json` files (`wm-modularscale-<locale>-dfbff627e6c248bcb3b61d7d06da9ca9.json`).
- **Distribution & supply-chain artifacts:** added `.wordpress-org/blueprints/blueprint.json` for WordPress Playground, CycloneDX 1.6 `bom.json`, and `.license-allowlist.yml`.

### Fixed

- Enforced `declare(strict_types=1);` and `ABSPATH` / `WP_UNINSTALL_PLUGIN` guards across every PHP file in the repository (`wm-modularscale.php`, `uninstall.php`, `build/index.asset.php`, and all seven `render.php` templates).
- Replaced raw inner-block echoes in `fluid-container/render.php` and `typographic-grid/render.php` with `wp_kses_post()` and escaped the ternary status class output in `wm-modularscale.php`.
- Replaced hardcoded hex literals `#ccc` and `#f5f5f5` in `wm-modularscale.css` with `:root` custom properties `--wmmsp-quote-border` and `--wmmsp-code-bg`.

## [1.2.2] - 2026-11-05

The version fields have said 1.2.2 since 2026-09-20 and the public repository has shown that code since the same day; this is the first package and the first tag of it.

- **118 of the 123 translatable strings had no entry in the `.pot`.** A string the code wraps in `__()` but that the catalog does not carry cannot be translated by anyone, to any language, ever — the translator never sees it and nothing reports an error. The catalog was regenerated from the code with `wp i18n make-pot`, every `.po` merged with `msgmerge --no-fuzzy-matching` and every `.mo` rebuilt.
- **The coverage figure in the README was measuring dead work.** It reported `ar` at 70 of 70 translated; 69 of those 70 translate strings of the *Spanish* settings page this plugin no longer has (`-- Seleccionar proporción o escribir valor --`), so they had already stopped appearing. Verified independently against the strings the code extracts, not inferred from the merge. The honest count is now 4 translated entries across 28 locales.
- **Nothing was deleted.** The 2,007 entries that no longer match a string are kept as obsolete `#~` entries where a translator can reuse them; all 28 `.mo` files read back with `msgunfmt`.
- `tests/test-pot-freshness.php` and two CI steps stop it drifting again. It takes its definition of "fresh" from `wp i18n make-pot` rather than a regex of its own, reads the text domain from the plugin header instead of holding a second copy, treats 0 extracted strings as a failure, ignores the plugin header (nobody translates an author name or a URL), requires wp-cli instead of skipping itself, and runs its own self-test first.
- **The README badge still said the opposite of the paragraph below it.** While Known issues spelled out "4 translated entries across the 28 locales", the badge row carried **`Translations — 24 EU Languages` in green**, which a reader takes as the plugin being translated into 24 languages. A badge is read before the prose, so an honest paragraph does not cancel a misleading badge. It now reads `Translated READMEs — 24 (headings only)` in grey — the label three other plugins of the fleet already use — and links to the same section, which now states plainly that the translated READMEs are a translated table of contents over an English body, not 24 translated manuals. The 24 translated READMEs were regenerated from this change with `wp-plugin-fleet-translations-sync.py`.
- The fleet carries a check for the same defect (self-test 11/11). It only fires when a badge claims the *plugin* is translated, is dressed in a success colour, and the catalogs hold under 50 % real translations — counting a `msgstr` that repeats its `msgid` as untranslated, which is what it is.

### Fixed — the SBOM this plugin publishes was wrong about its own licence and version

- **The hub SBOM declared `Proprietary Commercial License`** while the header, `readme.txt`, `package.json` and `LICENSE` all say GPLv2 or later. It is the only machine-readable statement of this plugin's licence, the hub serves it without authentication, and it is `GPL-2.0-or-later` now — a real SPDX identifier, which `Proprietary` never was.
  **The entry for 1.2.1 below claimed this was already fixed. It was not**: `git log` on `includes/class-modularscale-spoke-adapter.php` shows the file untouched since `a3ab49b`, before that release. The claim is corrected in place rather than deleted, because the record is that it was written.
- **The adapter reported version `1.2.0` for a 1.2.2 plugin**, so the SBOM identified the package as `pkg:wordpress/wm-modularscale@1.2.0`. It reads the `Version:` header through `get_file_data()` now — the one source WordPress itself uses — instead of holding a second copy of the number.
- **`Plugin::VERSION` said `1.2.0` too, and it is the cache-buster of the token stylesheet** (`wp_register_style( …, self::VERSION )`). An update therefore shipped new tokens under the old version and browsers kept serving the old sheet. A `const` cannot call `get_file_data()`, so the header stays the single source and a test pins the constant against it.
- **The licence carried an `id` and a `name` at once (corrected 2026-11-05).** CycloneDX 1.6 takes one or the other — its `license` object is `oneOf` an id or a name — so the block did not validate. It carries the SPDX id alone; the assertion, which only looked for the id, now requires it to stand alone, and a mutation that puts the name back proves it.
- Three assertions and three mutations defend all of it: remove any one fix and the suite turns red (29 mutations, 0 survivors, control green at 108).

## Shipped in the v1.2.0 and v1.2.1 Releases (2026-09-19)

Written between 2026-09-14 and 2026-09-16 under `[Unreleased]` and left there when the v1.2.0 and v1.2.1 Releases were cut on 2026-09-19, although both packages contain it (measured on 2026-11-05 against the two tags of the public repository). Kept as written; only the heading changed.

### Family sheets (2026-09-16)

- Re-synced from the hub: the badge variants carry the state tokens on body surfaces, where the command-banner inks measured 1.49:1 to 2.21:1 against the composed background (hub PR #19), and the canonical sheet dropped five classes that belonged to a single plugin (hub PR #21). `tests/test-ui-family.php` re-pins both md5s.

### Design family (hackaton phase 3, 2026-09-14)

- The settings page and the dashboard widget render from the two WM Suite family sheets (`assets/css/wm-admin-tokens.css`, `assets/css/wm-admin-ui.css`, byte-identical copies of the hub's canonical files, pinned by `tests/test-ui-family.php`) plus a 130-line plugin layer in `assets/css/modularscale-admin.css` (family tokens only). Before this the page carried 270 lines of inline `<style>` with 49 hex colours, an inline `<script>` with `onclick`/`oninput` handlers and 22 emoji; the script now ships from `assets/js/modularscale-admin.js` with data attributes, versioned by file time.
- Family header with the hub/standalone badge, family menu icon, native `.wrap.wm-admin`, jump navigation, quick guide and preset pills on the family classes, dashicons instead of emoji, `aria-describedby` on every field, an accessible name for the numeric ratio input. The dashboard widget's banner reports whether the scale is applied to the site; it always read "Skala aktiv" and named `--fs-*` variables the plugin never emits.
- **Broken admin enqueue fixed:** `Settings_Controller` enqueued the frontend typography sheet from `src/wm-modularscale.css`, a 404 on every settings page load (measured on a local WordPress install). It now enqueues the family sheets, the layer and the script on the plugin's own screens (standalone, Settings submenu and hub-dispatched hooks) and the sheets on `index.php` for the widget.
- **Frontend sheet:** the 170-line admin block (quick guide, jump links, preset pills with forced rules and one suppressed focus outline) moved out of `wm-modularscale.css`; it was frontend-only code styling admin classes. The sheet also dropped the base size to 14px under 1024px and 12px under 768px, against the 16px body minimum the readme promises under "Accessibility Compliance" (BFSG 2025); the two overrides are gone. Tests 12.x in `tests/test-modularscale.php` pin all of this (83 assertions, red when the 12px rule is put back).
- `.gitignore` carries the `assets/` negation: the README banner in `assets/images/` was never committed (global ignore rule of the development machine). Captures before/after in `docs/screenshots/design-family/`.

### Documentation (2026-09-14)

- `README.md`, `README.de.md`, `README.es.md`, the 24 translated READMEs, `DEVELOPERS.md`, `docs/ARCHITECTURE.md`, `docs/API.md` and `docs/DEPLOYMENT.md` describe what the plugin ships. Until now they promised WP-CLI commands (`wp modular status|verify|export`), a REST namespace `/wp-json/wm-modular/v1`, a `[wm_modular_scale_grid]` shortcode, a `wp_wm_modularscale_presets` table, GoBD audit exports with an HMAC-SHA256 chain and a `wm_modularscale_ratios` filter; none exist in this repository (measured: no `WP_CLI::add_command`, `register_rest_route`, `add_shortcode`, `hash_hmac`, `dbDelta` or `apply_filters` call). The three `docs/*.md` were Astro/Vite/Vercel project templates. `scripts/strip-unshipped-claims-from-translations.py --check` fails while any translation still carries those claims.
- The entries **[1.0.0]** and **[1.1.0]** below list "WP-CLI management commands under `wp modular`", a "GoBD cryptographic ledger", a "Decoupled REST API Controller" and a "Zero-CDN Frontend Shortcode". Those features never shipped; the entries are kept as the record of what was written at the time.
- Known drift documented in `DEVELOPERS.md` §7: two `register_setting()` groups with different `wmmsp_apply_typography` defaults, options without UI, the unenqueued root `wm-modularscale.js`, Spanish settings-page strings.

### Fixed

- **Blocks print measured values instead of claims (2026-09-15).** The APCA Contrast Badge printed "BFSG 2025 Konform" for every colour pair from Lc 75 on; one colour pair says nothing about a site's accessibility. It now prints the usage hint of APCA's own readability criterion (Lc 75 body text, 60 other text, 45 large headlines, below that too little contrast for text), and its title no longer says "WCAG 3.0": APCA was removed from the WCAG 3 working draft in 2023. `Scale_Engine::calculate_apca_lc()` clamped near-black luminance hard at 0.0005 instead of the reference soft clamp and had no deltaYmin check: black on white scored Lc 109.8 instead of 106.0, the badge's default colours −105.0 instead of −103.3, up to 4 Lc too high near black. It now matches apca-w3 0.1.9 (tests on four reference pairs).
  The Ratio Visualizer drew a fixed Bézier curve with its nodes on a straight line by index, the same picture for every ratio, and labelled step +1 "H1" and +6 "H6" although the tokens map +1 to H6. The nodes now sit at the step sizes and the labels follow the token mapping. Its "Multi-Strand Engine" badge (the block does not use the multi-strand matrix), the Theme.json Exporter's "Living Standard 2026" badge and the two emoji in their headers are gone.
- **Settings page in German, and naming what the plugin prints (2026-09-15).** On a German install the settings page was Spanish: the 28 catalogs in `languages/` repeat the Spanish source string as their translation (de_DE: 0 of 132 entries translated). The page now uses German source strings; the catalogs do not cover them yet, so every locale shows German. The page strings that were really translated fall back to German too: 61 in `ar` (the only complete catalog, Arabic), 14 each in bg_BG, cs_CZ and da_DK, 2 in `el` (measured against the catalogs; the `.po` files are not edited by hand). The copy says what the page does instead of claims: the invented "95 % of modern websites" is gone, the apply toggle names both the fluid tokens and the legacy sheet, the presets say they also set base 16 and px.
  The sandbox's "generated CSS" listed `--wm-ms-base`, `--wm-ms-ratio`, `--wm-ms-h1` …, variables nothing emits; it now shows the `--wmmsp-*` variables `wmmsp_force_styles()` prints, rounded the same way (`tests/sandbox.test.ts` runs the script in jsdom; red on the previous script). The dashboard widget called the off state "CSS Only" although nothing is printed then; it reads "Aus".
  The seven block descriptions were Spanish and partly claims ("BFSG 2025 compliance live", "baseline grid"); they are English and say what each render does. "Typographic Grid (Vertical Rhythm)" is "Typographic Grid (WM Scale)": the block is a CSS grid with a gap.
- **Dead files.** `wm-modularscale.js` at the root registered a `wm-modularscale/block` whose saved content was a `[modularscale]` shortcode that does not exist; no hook enqueued it since `9d39845` (2025-02-12), and no post on a local WordPress install contains the block. Removed, with `package.json` `main` (which pointed at it) and the description that named a `modularscale-js` dependency the plugin never had; `package.json` is `private` and at 1.2.0. `tests/example.test.ts` (a template asserting `true === true`) is gone.
- **Saving the settings page broke the fluid tokens (2026-09-15).** The page posts `wmmsp_settings_group`, and that group also carried `wmmsp_base_size_max`, both viewports, `wmmsp_ratio_name` and `wmmsp_font_override_preset`, which have no field on the page. `options.php` stores every option of the posted group and writes null for a missing field, so one save turned the viewports and the maximum base size into 0 and the preset names into `''`. Measured on a local WordPress install afterwards: `--wm-min-viewport: 0px; --wm-step-0: clamp(1rem, 1rem - 1600vw, 0rem)`, every step with a maximum below its minimum, nothing fluid. The group now holds exactly the page's five fields (registered once, in `Settings_Controller`; the duplicate registration in the main file with other defaults is gone); the fieldless options sit in `wmmsp_scale_advanced_group`, which no page posts. `Plugin::scale_options()` is the one reader for the token sheet, the theme.json bridge and the Ratio Visualizer and Theme.json Exporter blocks: a zero or empty number falls back to its default, a ratio below 1 becomes 1, viewports in the wrong order become 360/1440, a maximum base size below the minimum keeps the default proportion. Installs that were already saved keep the zeros in the database and render correctly through that fallback. `tests/test-settings-save-runtime.php` (`wp eval-file`, read-only) compares the group core writes with the rendered form and checks the printed sheet: on the previous code on a local WordPress install it reports 5 options written as null and 9 inverted steps, on this code 2 of 2 pass; served on <http://localhost:8080/> with the zeros still stored: `--wm-min-viewport: 360px; --wm-step-0: clamp(1rem, 0.9583rem + 0.1852vw, 1.125rem)`.
  The hand-off of 2026-09-14 recorded the symptom as "unticking the apply toggle does not persist". Core `options.php` (WP 7.1) does store an unticked checkbox (null, read as off); the defect was the fieldless options.
- **The apply toggle showed "off" while the tokens printed.** Settings page and dashboard widget read `wmmsp_apply_typography` with default 0 / `false`, the hub telemetry with 0, the token output with 1: on a fresh install the widget reported the scale as not applied while `wp_head` carried it, and saving the page unchanged stored 0 and silently stopped the tokens. All four now read default 1. The legacy sheet (`wmmsp_force_styles()`, sizes `body` and `h1`–`h6`) keeps default 0, so a fresh install that was never saved prints the tokens without the legacy sheet although the toggle shows ticked; saving applies both, as the toggle's description says.
- **CI ran one of the three test files.** `php tests/test-*.php` executes only the first file of the glob and passes the others as arguments; `tests/test-ui-family.php` never ran. The workflow calls each file and runs `tests/run-mutations.sh`, which undoes each correction listed in `tests/mutations.php` (15 for the settings fix) and requires the suite to fail.
- **Five block templates printed a PHP guard as visible text.** The sprint of 2026-08-28 ("Enforce ABSPATH execution guards") appended `if ( ! defined( 'ABSPATH' ) ) { exit; }` *after* the closing `?>` of `fluid-container`, `fluid-heading`, `fluid-lead`, `typographic-grid` and `ratio-visualizer`, so every rendered block carried those three lines of source code as literal text inside its HTML. The guard now sits at the top of each file, inside PHP, where it does its job; the text is gone from the output. A test walks every `render.php` and fails when anything but markup follows the last closing tag; the fleet HTTP falsifier renders the five blocks and asserts the page carries no guard text.
- Plugin Check (dist view, 2026-09-14): 13 → 0 errors. Inner-block output is annotated as already rendered, heading tag names are escaped, the critical token sheet is stripped of tags before it is printed, `Tested up to` is 7.1 in readme and header (runtime pass of today), and `.editorconfig`, `.prettierrc`, `.gitattributes`, `.dockerignore` and any `.env*` stay out of the ZIP.

---

## [1.2.1] - 2026-09-19

- `package.json` named `arnoldwender/wm-modularscale`, which is PRIVATE: a 404 for every reader, and it published the internal repository's name. It names the public repository now.
- The author line read "Author & Lead Architect", and its translations `Chefarchitekt`, `capo architetto`, `główny architekt`, `Príomh-Ailtire`, `Perit Prinċipali` and the rest, in 24 languages. "Architekt" is a title reserved by the Architektengesetz of each Land; the English compound "Software Architect" passes because the domain word removes the confusion, and this line had none while sitting next to a German address and a German legal form. It reads "Author & Web Developer" now, one per language.
- ~~The SBOM the plugin reports to the hub declared `Proprietary Commercial License`, while the header, `readme.txt`, `package.json` and `LICENSE` all say GPLv2 or later — and the endpoint that serves it needs no authentication. It declares `GPL-2.0-or-later`, which is a real SPDX identifier.~~
  **This did not happen in this release.** Measured 2026-09-20: `includes/class-modularscale-spoke-adapter.php` still declared `Proprietary`, and `git log` shows the file untouched since `a3ab49b`, before 1.2.1 was written. The line is struck through rather than removed — a changelog is a record, and the record is that this was claimed. Really fixed under 1.2.2.
- `tests/run-mutations.sh` copied the working tree with `rsync` and discarded the error. `rsync` is not in ordinary CI base images; the copy came out empty and the runner reported "CONTROL FAILED", blaming the tests for a copy that never happened. It uses `tar`, states the cause, and verifies the copy by counting what `git ls-files` listed against what arrived.

## [1.2.0] — 2026-08-27

Reconstructed on 2026-09-15: the plugin header went from 1.1.2 to 1.2.0 in `093fb86` (2026-08-27) and no entry was written; `readme.txt` got its `= 1.2.0 =` notes in `5a248ae` (2026-08-29). The list below is what the commits between them changed, read from their diffs.

### Added

- `093fb86` (2026-08-27): settings page with a live sandbox and ratio presets, frontend sheet `wm-modularscale.css`, `.pot` plus 28 `.po` / `.mo` files in `languages/`. Their entries repeat the source string as translation (measured 2026-09-15: de_DE 0 of 132 translated), so "24 EU language localization packages compiled" in `readme.txt` means files, not translations.
- `fe0140d`, `e9e5b46` (2026-08-27): WM Suite Hub spoke adapter, text domain loaded on `init`, top-level admin menu and plugin action link. `f7e8cfc`, `a7a60c7`: dashboard widget.
- `42a87c2` (2026-08-28): `Scale_Engine` (ratios, `clamp()` derivation, multi-strand matrix, APCA), `Font_Metric_Matcher`, `CSS_Token_Generator`, `Plugin`, `Settings_Controller`, the seven blocks, theme.json / GeneratePress / GenerateBlocks bridges, `tests/test-modularscale.php` and `tests/scale-engine.test.ts`.
- `e49737f` (2026-08-28): `ABSPATH` guards. In five block templates the guard landed after the closing PHP tag and printed as text (fixed 2026-09-14, see «Shipped in the v1.2.0 and v1.2.1 Releases»).
- `5df4e93` … `edb55a0` (2026-08-29): documentation suite in English, German and Spanish, describing WP-CLI commands, REST routes, a shortcode and GoBD exports that do not exist (replaced 2026-09-14).
- `5a248ae` (2026-08-29): `readme.txt`. Its note "Enhanced WordPress 6.7 and PHP 8.3 compatibility" was not measured then; the first recorded Plugin Check and runtime passes are from 2026-09-14.

[1.1.0] below is dated 2026-08-29, after the header already read 1.2.0.

---

## [1.1.0] — 2026-08-29

### Added

- **24 Official European Union Languages:** Full binary `.mo` and source `.po` translation packages in `languages/` (`bg_BG`, `cs_CZ`, `da_DK`, `de_DE`, `el_GR`, `en_GB`, `es_ES`, `et_EE`, `fi_FI`, `fr_FR`, `ga_IE`, `hr_HR`, `hu_HU`, `it_IT`, `lt_LT`, `lv_LV`, `mt_MT`, `nl_NL`, `pl_PL`, `pt_PT`, `ro_RO`, `sk_SK`, `sl_SI`, `sv_SE`).
- **Developer Documentation Suite:** Created `DEVELOPERS.md` with complete architecture diagrams, hook references, database schema, and REST API guides.
- **Decoupled REST API Controller:** Registered namespace `/wp-json/wm-modular/v1` with strict nonce and permission validation.
- **Zero-CDN Frontend Shortcode:** Implemented `[wm_modular_scale_grid]` with inline SVG icons and CSS design tokens.
- **CycloneDX v1.6 SBOM Engine:** Dynamic SHA-256 entry file integrity hashing and spoke adapter integration for **WM Suite Hub**.
- **Enterprise Showcase Assets:** High-fidelity 16:9 marketing banner and cockpit showcase mockups conditioned on real screenshot topology.
- **Clean Uninstallation Lifecycle:** Comprehensive `uninstall.php` handler for clean database and transient cache purging.

### Security

- **CWE-1236 Mitigation:** Active sanitization against formula injection attacks on all CSV exports and dynamic text inputs.
- **Constant-Time Cryptography:** Enforced `hash_equals()` verification on all HMAC-SHA256 signature chains.
- **SQL Parameterization:** Refactored all queries to use strict `$wpdb->prepare()` with parameterized placeholders.

### Quality & Tests

- Expanded standalone deterministic test suite (`tests/test-suite.php`) with 100% green pass rate and zero external dependencies.

---

## [1.0.0] — 2026-08-25

### Initial Enterprise Release

- Core architectural implementation for Design Systems & Typography.
- WP-CLI management commands under `wp modular`.
- Revisionssicheres GoBD cryptographic ledger and telemetry hooks.
- Strict compliance with W3C CSS Values and Units Module Level 4, BFSG 2025 16px Base Typography & Atomic Design.
