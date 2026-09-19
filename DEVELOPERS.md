# WM Modular Scale — Developer Manual

> **Author & Web Developer:** Arnold Wender ([ORCID: 0009-0005-1750-818X](https://orcid.org/0009-0005-1750-818X)) · Wender Media · Halle (Saale) · 2026  
> Everything below is measured against the code in this repository (2026-09-14). The previous edition of this file documented a REST namespace, a `wp_wm_modularscale_presets` table and an HMAC chain that never existed here.

---

## 1. Layout

| Path | Role |
| --- | --- |
| `wm-modularscale.php` | Bootstrap: PSR-4 autoloader for `WenderMedia\ModularScale\`, `Plugin::instance()`, text domain, hub spoke registration, legacy frontend sheet, admin menu, dashboard widget, settings page, family header helper. |
| `src/Plugin.php` | Singleton. Boots `Block_Manager`, the three bridges and `Settings_Controller` (admin only); prints the fluid tokens on `wp_head` (priority 5) and registers them as an inline style on `wp_enqueue_scripts`. `Plugin::scale_options()` reads the scale options with fallbacks for unusable values. |
| `src/Core/Scale_Engine.php` | Ratios, `calculate_step_value()`, `derive_clamp()` (slope / intercept, `vw` or `cqi`, zoom-safety flag), `calculate_inverse_line_height()`, `generate_multi_strand_matrix()` (display / body / caption strands), `calculate_apca_lc()` (apca-w3 0.1.9). |
| `src/Core/Font_Metric_Matcher.php` | Font metric table and four `@font-face` fallback presets (`size-adjust`, `ascent-override`, `descent-override`, `line-gap-override`). |
| `src/Tokens/CSS_Token_Generator.php` | `generate_stylesheet()`, `compute_hash()` (SHA-256 of the CSS, printed as a comment), `export_tailwind_v4_theme()`, variable name helpers. |
| `src/Gutenberg/Block_Manager.php` + `Blocks/*/block.json` + `render.php` | Seven server-rendered blocks, category `wm-scale`. |
| `src/Integration/Theme_Json_Bridge.php` | `wp_theme_json_data_theme` filter. |
| `src/Integration/GeneratePress_Bridge.php` | GeneratePress font and typography filters. |
| `src/Integration/GenerateBlocks_Bridge.php` | GenerateBlocks global styles, presets, container queries. |
| `src/Admin/Settings_Controller.php` | `register_setting()` for all options, admin enqueue (family sheets + `assets/css/modularscale-admin.css` + `assets/js/modularscale-admin.js`), AJAX `wmmsp_calculate_scale`. |
| `includes/class-modularscale-spoke-adapter.php` | WM Suite Hub contract (`WM_Plugin_Module_Interface`). |
| `assets/css/` | `wm-admin-tokens.css`, `wm-admin-ui.css` (byte-identical copies of the hub's canonical sheets, pinned by `tests/test-ui-family.php`) and the plugin layer `modularscale-admin.css`. |
| `wm-modularscale.css` | Legacy frontend sheet (`--wmmsp-*`), enqueued by `wmmsp_force_styles()` when `wmmsp_apply_typography` is on. |
| `uninstall.php` | Deletes the plugin options. |

---

## 2. Options

Registered once, in `Settings_Controller::register_settings()` on `admin_init`. The group decides what a save of the settings page writes: `options.php` stores every option of the posted group and null for a field the form does not carry. `wmmsp_settings_group` therefore holds exactly the page's five fields; the options without a field sit in `wmmsp_scale_advanced_group`, which no page posts. (Until 2026-09-15 both sets shared one group and every save zeroed the viewports and the maximum base size.)

| Option | Group | Type | Default | Read by |
| --- | --- | --- | --- | --- |
| `wmmsp_base_size` | settings | number | 16.0 | `Plugin::scale_options()`, legacy sheet, settings page, widget, adapter |
| `wmmsp_ratio` | settings | number | 1.25 | `Plugin::scale_options()`, legacy sheet, settings page, widget, adapter |
| `wmmsp_unit` | settings | string | px | legacy sheet, settings page, widget, adapter |
| `wmmsp_precision` | settings | integer | 2 | legacy sheet, settings page, adapter |
| `wmmsp_apply_typography` | settings | boolean | 1 | tokens, settings page, widget, adapter (`get_option( …, 1 )`); legacy sheet (`get_option( …, 0 )`) |
| `wmmsp_base_size_max` | advanced | number | 18.0 | `Plugin::scale_options()` |
| `wmmsp_min_viewport` / `wmmsp_max_viewport` | advanced | number | 360.0, 1440.0 | `Plugin::scale_options()` |
| `wmmsp_font_override_preset` | advanced | string | inter-to-arial | `Plugin::scale_options()` |
| `wmmsp_ratio_name` | advanced | string | major-third | nobody |

`Plugin::scale_options()` feeds the token sheet, the theme.json bridge and the Ratio Visualizer and Theme.json Exporter blocks. A number of 0 or less (or empty) falls back to its default, a ratio below 1 becomes 1, viewports in the wrong order become 360/1440, and a maximum base size below the base size becomes base × 1.125. The options without a field can be set with `wp option update`.

---

## 3. Output on the frontend

When `wmmsp_apply_typography` is truthy (default 1 on the token path), `Plugin::inject_header_tokens()` prints on `wp_head`:

```css
/* === WM Modular Scale Enterprise CSS Tokens (2026) === */
@font-face { font-family: 'Inter Fallback'; src: local('Arial'); size-adjust: 97.4%; … }
@property --wm-step-neg-2 { syntax: '<length>'; inherits: true; initial-value: 16px; }
… (one per step, −2 … 6)
:root {
  --wm-scale-ratio: 1.25;
  --wm-min-viewport: 360px;
  --wm-max-viewport: 1440px;
  --wm-step-neg-2: clamp(0.64rem, 0.5867rem + 0.2369vw, 0.72rem);
  --wm-lh-neg-2: 1.6;
  … --wm-step-6: clamp(…); --wm-lh-6: …;
  --wm-font-size-body: var(--wm-step-0); … --wm-font-size-h1: var(--wm-step-6);
  --gp-font-size-body: var(--wm-step-0); … --gp-font-size-h1: var(--wm-step-6);
}
.has-fluid-body-font-size { … } … .has-fluid-h1-font-size { … }
.wm-fluid-container { container-type: inline-size; container-name: wm-typographic-context; }
```

The same CSS is registered as an inline style on the `wm-modularscale-tokens` handle. `CSS_Token_Generator::export_tailwind_v4_theme()` produces an `@theme { --text-wm-step-*: clamp(…) }` block; nothing in the plugin calls it besides the tests.

The legacy path (`wmmsp_force_styles()`, priority 100) enqueues `wm-modularscale.css` and an inline `:root { --wmmsp-base-size; --wmmsp-h1 … --wmmsp-h6; --wmmsp-paragraph }` computed from base × ratio^n.

---

## 4. Blocks

| Block | Title | Render |
| --- | --- | --- |
| `wm-scale/fluid-heading` | Fluid Heading (WM Scale) | `render.php` |
| `wm-scale/fluid-lead` | Fluid Lead Text (WM Scale) | `render.php` |
| `wm-scale/fluid-container` | Fluid Container (@container) | `render.php` |
| `wm-scale/typographic-grid` | Typographic Grid (WM Scale) | `render.php` |
| `wm-scale/ratio-visualizer` | Ratio Visualizer (SVG Curve) | `render.php` |
| `wm-scale/contrast-matrix-badge` | APCA Contrast Badge (WM Scale) | `render.php` |
| `wm-scale/theme-json-exporter` | Theme.json Exporter (WM Scale) | `render.php` |

Registered with `register_block_type_from_metadata()` on `init`; the category `wm-scale` is prepended through `block_categories_all`. Every `render.php` carries its `ABSPATH` guard inside PHP (a guard after the closing tag printed source into the page until 2026-09-14; the test suite checks each file).

---

## 5. Hooks the plugin attaches to

| Hook | Where | Effect |
| --- | --- | --- |
| `init` | `Plugin`, `Block_Manager`, main file | blocks, text domain |
| `wp_head` (5) / `wp_enqueue_scripts` (20, 100) | `Plugin`, main file | fluid tokens; legacy sheet |
| `wp_theme_json_data_theme` (20) | `Theme_Json_Bridge` | fluid `fontSizes`, `defaultFontSizes: false`, `fluid: true` |
| `generate_google_fonts_array` (999), `generate_font_manager_show_google_fonts` (999) | `GeneratePress_Bridge` | no Google Fonts |
| `generate_typography_css` (20) | `GeneratePress_Bridge` | `--gp-font-size-*` + `h1`–`h6` rules appended |
| `generate_typography_default_fonts`, `generate_font_manager_system_fonts` | `GeneratePress_Bridge` | local font names in the pickers |
| `generateblocks_global_styles` (20), `generateblocks_typography_presets` (20) | `GenerateBlocks_Bridge` | `wm-fluid-*` styles, `wm-step-*` presets |
| `render_block_generateblocks/container` | `GenerateBlocks_Bridge` | `container-type: inline-size` when `useContainerQueries` |
| `admin_menu`, `admin_init`, `wp_dashboard_setup`, `admin_enqueue_scripts`, `wp_ajax_wmmsp_calculate_scale` | main file, `Settings_Controller` | admin surface |
| `wm_register_suite_module` | main file | hub spoke |
| `plugin_action_links_*` | main file | "Einstellungen" link |

**Filters the plugin offers:** none. (The previous manual listed `wm_modularscale_ratios`; no `apply_filters()` call exists in the code.)

---

## 6. Admin

- **Menu:** `add_menu_page( 'wm-modular-scale', …, wmmsp_family_menu_icon(), 31 )` plus a submenu of the same slug and `add_options_page( 'wm-modular-scale-options' )`. All three render `wmmsp_settings_page()`. Under the hub the screen is dispatched as `wender-media_page_wm-modular-scale`.
- **Assets:** `Settings_Controller::enqueue_admin_assets()` enqueues `wm-admin-tokens`, `wm-admin-ui`, `wmmsp-admin` (sheets, versioned by file time) on every hook containing `wm-modular-scale` and on `index.php`; `wmmsp-admin` script on the plugin screens only.
- **Sandbox:** `assets/js/modularscale-admin.js` recomputes the seven preview rows and the `:root` block of the legacy sheet (`--wmmsp-base-size`, `--wmmsp-h1` … `--wmmsp-h6`, `--wmmsp-paragraph`, rounded like `wmmsp_force_styles()`) from the fields; preset buttons carry `data-ratio` / `data-base` / `data-unit` and set base 16 and unit px.
- **Language:** the admin screens use German source strings (English for the family header badge); the `.po` catalogs do not translate them (README, Known issues).
- **AJAX:** `wp_ajax_wmmsp_calculate_scale` (nonce `wmmsp_calc_nonce`, `manage_options`) returns `Scale_Engine::generate_multi_strand_matrix()` and the generated stylesheet for the posted `base_min`, `base_max`, `ratio`, `min_vp`, `max_vp`. No shipped script calls it.
- **Widget:** `wm_modularscale_widget` shows base size, ratio and whether the scale is applied.

---

## 7. Known drift (documented, not fixed)

1. `wmmsp_apply_typography` defaults to 1 everywhere except the legacy sheet (0). A fresh install that was never saved prints the fluid tokens but not the legacy sheet, although the toggle shows ticked.
2. `wmmsp_ratio_name`, `wmmsp_base_size_max`, viewports and the font preset have no UI; `wmmsp_ratio_name` is read by nothing.
3. The catalogs in `languages/` repeat the source strings instead of translating them and predate the German settings page (measured 2026-09-15: de_DE 0 of 132 entries translated).
4. No block registers an editor script; attributes without effect: `showSteps`, `theme` (ratio-visualizer), `format` (theme-json-exporter), `targetContext` (contrast-matrix-badge), `step` (fluid-heading).
5. ratio-visualizer, theme-json-exporter and contrast-matrix-badge carry inline hex colours of a fixed dark theme.

---

## 8. WM Suite Hub spoke

`ModularScale_Spoke_Adapter` (loaded only when the hub's interface exists): slug `wm-modularscale`, version `1.2.1`, health `HEALTHY`, SBOM component with the SHA-256 of `wm-modularscale.php`, settings URL `admin.php?page=wm-modular-scale`, telemetry `base_size`, `scale_ratio`, `typography_applied`, `token_h1..h3`, actions `self_test` / `calculate_scale`.

---

## 9. Tests

```bash
php tests/test-suite.php        # 98 assertions: engine, clamp, APCA, blocks, bridges, adapter, admin surface (12), stored options (13), page language and sandbox names (14), block renders (15)
php tests/test-ui-family.php    # family sheets byte-identical to the hub, no outline: none, no stray hex in admin sheets
bash tests/run-mutations.sh     # undoes each correction in tests/mutations.php on a copy; the suite must fail for every one
# in a WordPress install, read-only: the group a settings save writes vs the form, and the printed token sheet
wp eval-file wp-content/plugins/wm-modularscale/tests/test-settings-save-runtime.php --user=<admin id>
npm test                        # vitest: tests/sandbox.test.ts runs assets/js/modularscale-admin.js in jsdom against the PHP output;
                                #         tests/scale-engine.test.ts checks a TypeScript copy of the engine formulas, not the PHP
```

The fleet scripts in the workspace (`wp-plugin-fleet-plugin-check.sh`, `wp-plugin-fleet-runtime.sh`) run Plugin Check on the distribution view and activate the plugin on a local WordPress install.

---

## 10. Security disclosure

See [SECURITY.md](SECURITY.md).
