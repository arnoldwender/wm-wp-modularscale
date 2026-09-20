# Changelog — WM Modular Scale

All notable changes to **WM Modular Scale** are documented in this file in adherence to [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) and [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.2.2] - 2026-09-20

- **118 of the 123 translatable strings had no entry in the `.pot`.** A string the code wraps in `__()` but that the catalog does not carry cannot be translated by anyone, to any language, ever — the translator never sees it and nothing reports an error. The catalog was regenerated from the code with `wp i18n make-pot`, every `.po` merged with `msgmerge --no-fuzzy-matching` and every `.mo` rebuilt.
- **The coverage figure in the README was measuring dead work.** It reported `ar` at 70 of 70 translated; 69 of those 70 translate strings of the *Spanish* settings page this plugin no longer has (`-- Seleccionar proporción o escribir valor --`), so they had already stopped appearing. Verified independently against the strings the code extracts, not inferred from the merge. The honest count is now 4 translated entries across 28 locales.
- **Nothing was deleted.** The 2,007 entries that no longer match a string are kept as obsolete `#~` entries where a translator can reuse them; all 28 `.mo` files read back with `msgunfmt`.
- `tests/test-pot-freshness.php` and two CI steps stop it drifting again. It takes its definition of "fresh" from `wp i18n make-pot` rather than a regex of its own, reads the text domain from the plugin header instead of holding a second copy, treats 0 extracted strings as a failure, ignores the plugin header (nobody translates an author name or a URL), requires wp-cli instead of skipping itself, and runs its own self-test first.
- **The README badge still said the opposite of the paragraph below it.** While Known issues spelled out "4 translated entries across the 28 locales", the badge row carried **`Translations — 24 EU Languages` in green**, which a reader takes as the plugin being translated into 24 languages. A badge is read before the prose, so an honest paragraph does not cancel a misleading badge. It now reads `Translated READMEs — 24 (headings only)` in grey — the label three other plugins of the fleet already use — and links to the same section, which now states plainly that the translated READMEs are a translated table of contents over an English body, not 24 translated manuals. The 24 translated READMEs were regenerated from this change with `wp-plugin-fleet-translations-sync.py`.
- The fleet carries a check for the same defect (self-test 11/11). It only fires when a badge claims the *plugin* is translated, is dressed in a success colour, and the catalogs hold under 50 % real translations — counting a `msgstr` that repeats its `msgid` as untranslated, which is what it is.

## [Unreleased]

### Family sheets (2026-09-16)
- Re-synced from the hub: the badge variants carry the state tokens on body surfaces, where the command-banner inks measured 1.49:1 to 2.21:1 against the composed background (hub PR #19), and the canonical sheet dropped five classes that belonged to a single plugin (hub PR #21). `tests/test-ui-family.php` re-pins both md5s.

### Design family (hackaton phase 3, 2026-09-14)
- The settings page and the dashboard widget render from the two Wender Media family sheets (`assets/css/wm-admin-tokens.css`, `assets/css/wm-admin-ui.css`, byte-identical copies of the hub's canonical files, pinned by `tests/test-ui-family.php`) plus a 130-line plugin layer in `assets/css/modularscale-admin.css` (family tokens only). Before this the page carried 270 lines of inline `<style>` with 49 hex colours, an inline `<script>` with `onclick`/`oninput` handlers and 22 emoji; the script now ships from `assets/js/modularscale-admin.js` with data attributes, versioned by file time.
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
- **Saving the settings page broke the fluid tokens (2026-09-15).** The page posts `wmmsp_settings_group`, and that group also carried `wmmsp_base_size_max`, both viewports, `wmmsp_ratio_name` and `wmmsp_font_override_preset`, which have no field on the page. `options.php` stores every option of the posted group and writes null for a missing field, so one save turned the viewports and the maximum base size into 0 and the preset names into `''`. Measured on a local WordPress install afterwards: `--wm-min-viewport: 0px; --wm-step-0: clamp(1rem, 1rem - 1600vw, 0rem)`, every step with a maximum below its minimum, nothing fluid. The group now holds exactly the page's five fields (registered once, in `Settings_Controller`; the duplicate registration in the main file with other defaults is gone); the fieldless options sit in `wmmsp_scale_advanced_group`, which no page posts. `Plugin::scale_options()` is the one reader for the token sheet, the theme.json bridge and the Ratio Visualizer and Theme.json Exporter blocks: a zero or empty number falls back to its default, a ratio below 1 becomes 1, viewports in the wrong order become 360/1440, a maximum base size below the minimum keeps the default proportion. Installs that were already saved keep the zeros in the database and render correctly through that fallback. `tests/test-settings-save-runtime.php` (`wp eval-file`, read-only) compares the group core writes with the rendered form and checks the printed sheet: on the previous code on a local WordPress install it reports 5 options written as null and 9 inverted steps, on this code 2 of 2 pass; served on http://localhost:8080/ with the zeros still stored: `--wm-min-viewport: 360px; --wm-step-0: clamp(1rem, 0.9583rem + 0.1852vw, 1.125rem)`.
  The hand-off of 2026-09-14 recorded the symptom as "unticking the apply toggle does not persist". Core `options.php` (WP 7.1) does store an unticked checkbox (null, read as off); the defect was the fieldless options.
- **The apply toggle showed "off" while the tokens printed.** Settings page and dashboard widget read `wmmsp_apply_typography` with default 0 / `false`, the hub telemetry with 0, the token output with 1: on a fresh install the widget reported the scale as not applied while `wp_head` carried it, and saving the page unchanged stored 0 and silently stopped the tokens. All four now read default 1. The legacy sheet (`wmmsp_force_styles()`, sizes `body` and `h1`–`h6`) keeps default 0, so a fresh install that was never saved prints the tokens without the legacy sheet although the toggle shows ticked; saving applies both, as the toggle's description says.
- **CI ran one of the three test files.** `php tests/test-*.php` executes only the first file of the glob and passes the others as arguments; `tests/test-ui-family.php` never ran. The workflow calls each file and runs `tests/run-mutations.sh`, which undoes each correction listed in `tests/mutations.php` (15 for the settings fix) and requires the suite to fail.
- **Five block templates printed a PHP guard as visible text.** The sprint of 2026-08-28 ("Enforce ABSPATH execution guards") appended `if ( ! defined( 'ABSPATH' ) ) { exit; }` *after* the closing `?>` of `fluid-container`, `fluid-heading`, `fluid-lead`, `typographic-grid` and `ratio-visualizer`, so every rendered block carried those three lines of source code as literal text inside its HTML. The guard now sits at the top of each file, inside PHP, where it does its job; the text is gone from the output. A test walks every `render.php` and fails when anything but markup follows the last closing tag; the fleet HTTP falsifier renders the five blocks and asserts the page carries no guard text.
- Plugin Check (dist view, 2026-09-14): 13 → 0 errors. Inner-block output is annotated as already rendered, heading tag names are escaped, the critical token sheet is stripped of tags before it is printed, `Tested up to` is 7.1 in readme and header (runtime pass of today), and `.editorconfig`, `.prettierrc`, `.gitattributes`, `.dockerignore` and any `.env*` stay out of the ZIP.

---

## [1.2.1] - 2026-09-19

- `package.json` named `arnoldwender/wm-modularscale`, which is PRIVATE: a 404 for every reader, and it published the internal repository's name. It names the public repository now.
- The author line read "Author & Lead Architect", and its translations `Chefarchitekt`, `capo architetto`, `główny architekt`, `Príomh-Ailtire`, `Perit Prinċipali` and the rest, in 24 languages. "Architekt" is a title reserved by the Architektengesetz of each Land; the English compound "Software Architect" passes because the domain word removes the confusion, and this line had none while sitting next to a German address and a German legal form. It reads "Author & Web Developer" now, one per language.
- The legal role is `Inhaber, Wender Media (Einzelunternehmen)` in every language. The earlier pass matched the English phrasings only and left `Inhaber von … und …` and `Titular de … y …` alive: the title was fixed and the claim of two companies survived.
- The SBOM the plugin reports to the hub declared `Proprietary Wender Media Commercial License`, while the header, `readme.txt`, `package.json` and `LICENSE` all say GPLv2 or later — and the endpoint that serves it needs no authentication. It declares `GPL-2.0-or-later`, which is a real SPDX identifier.
- `tests/run-mutations.sh` copied the working tree with `rsync` and discarded the error. `rsync` is not in ordinary CI base images; the copy came out empty and the runner reported "CONTROL FAILED", blaming the tests for a copy that never happened. It uses `tar`, states the cause, and verifies the copy by counting what `git ls-files` listed against what arrived.

## [1.2.0] — 2026-08-27

Reconstructed on 2026-09-15: the plugin header went from 1.1.2 to 1.2.0 in `093fb86` (2026-08-27) and no entry was written; `readme.txt` got its `= 1.2.0 =` notes in `5a248ae` (2026-08-29). The list below is what the commits between them changed, read from their diffs.

### Added
- `093fb86` (2026-08-27): settings page with a live sandbox and ratio presets, frontend sheet `wm-modularscale.css`, `.pot` plus 28 `.po` / `.mo` files in `languages/`. Their entries repeat the source string as translation (measured 2026-09-15: de_DE 0 of 132 translated), so "24 EU language localization packages compiled" in `readme.txt` means files, not translations.
- `fe0140d`, `e9e5b46` (2026-08-27): WM Suite Hub spoke adapter, text domain loaded on `init`, top-level admin menu and plugin action link. `f7e8cfc`, `a7a60c7`: dashboard widget.
- `42a87c2` (2026-08-28): `Scale_Engine` (ratios, `clamp()` derivation, multi-strand matrix, APCA), `Font_Metric_Matcher`, `CSS_Token_Generator`, `Plugin`, `Settings_Controller`, the seven blocks, theme.json / GeneratePress / GenerateBlocks bridges, `tests/test-modularscale.php` and `tests/scale-engine.test.ts`.
- `e49737f` (2026-08-28): `ABSPATH` guards. In five block templates the guard landed after the closing PHP tag and printed as text (fixed 2026-09-14, see [Unreleased]).
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
