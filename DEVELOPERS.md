# WM Modular Scale — Developer Manual

> Everything below is measured against the code in this repository (v1.4.1, 2026-11-21). Earlier editions before 2026-09-14 documented REST routes and custom tables that never existed here.

---

## 1. Layout

| Path | Role |
| --- | --- |
| `wm-modularscale.php` | Bootstrap: PSR-4 autoloader for `ArnoldWender\ModularScale\`, `Plugin::instance()`, text domain, hub spoke registration, legacy frontend sheet, admin menu, dashboard widget, settings page, family header helper. |
| `src/Plugin.php` | Singleton (`VERSION = '1.4.1'`). Boots `Block_Manager`, the four integration bridges and `Settings_Controller` (admin only); prints the fluid tokens on `wp_head` (priority 5) and registers them as an inline style on `wp_enqueue_scripts`. `Plugin::scale_options()` reads the scale options with fallbacks and passes them through `wm_modularscale_scale_options`. |
| `src/Core/Scale_Engine.php` | Ratios (`Scale_Engine::get_ratios()` filtered by `wm_modularscale_ratios`), `calculate_step_value()`, `derive_clamp()` (slope / intercept, `vw` or `cqi`, zoom-safety flag), `calculate_inverse_line_height()`, `generate_multi_strand_matrix()` (display / body / caption strands), `calculate_apca_lc()` (apca-w3 0.1.9). |
| `src/Core/Font_Metric_Matcher.php` | Font metric table and four `@font-face` fallback presets (`size-adjust`, `ascent-override`, `descent-override`, `line-gap-override`). |
| `src/Tokens/CSS_Token_Generator.php` | `generate_stylesheet()` (`--wm-step-*`, `--ms-*`, `--wm-font-size-*`, `--gp-font-size-*`, filtered by `wm_modularscale_tokens_generated`), `compute_hash()` (SHA-256 of the CSS, printed as a comment), `export_tailwind_v4_theme()`, variable name helpers. |
| `src/Gutenberg/Block_Manager.php` + `Blocks/*/block.json` + `render.php` | Seven server-rendered blocks in category `wm-scale`, sharing compiled editor handle `wm-modularscale-blocks-editor` (`build/index.js` + `build/index.asset.php`) and `wp_set_script_translations`. |
| `blocks-src/index.js` + `scripts/build-blocks.mjs` | Gutenberg editor registration and `InspectorControls` for all seven blocks; compiled to `build/index.js` and guarded `build/index.asset.php` via `npm run build`. |
| `src/Integration/Theme_Json_Bridge.php` | `wp_theme_json_data_theme` filter. |
| `src/Integration/GeneratePress_Bridge.php` | GeneratePress font and typography filters. |
| `src/Integration/GenerateBlocks_Bridge.php` | GenerateBlocks global styles, presets, container queries. |
| `src/Integration/Ecosystem_Bridge.php` | WordPress 6.5+ Block Bindings (`wm-modularscale/tokens`), GenerateBlocks 2.x Dynamic Tag (`{{wm_scale_token}}`), Core / GenerateBlocks 2.x block patterns, WordPress 6.9 Ability (`wm-modularscale/compute-scale`), and `wm_modularscale_ready` action. |
| `src/Admin/Settings_Controller.php` | `register_setting()` for all eleven options, admin enqueue (family sheets + `assets/css/modularscale-admin.css` + `assets/js/modularscale-admin.js`), AJAX `wmmsp_calculate_scale`. |
| `includes/class-modularscale-spoke-adapter.php` | WM Suite Hub contract (`WM_Plugin_Module_Interface`). |
| `assets/css/` | `wm-admin-tokens.css`, `wm-admin-ui.css` (byte-identical copies of the hub's canonical sheets, pinned by `tests/test-ui-family.php`) and the plugin layer `modularscale-admin.css`. |
| `wm-modularscale.css` | Legacy frontend sheet (`--wmmsp-*`), enqueued by `wmmsp_force_styles()` when `wmmsp_apply_typography` is on. |
| `uninstall.php` | Preserves stored options by default; deletes all eleven `wmmsp_*` options (single-site and multisite) only when `wmmsp_delete_data_on_uninstall` is truthy. |

---

## 2. Options

Registered once, in `Settings_Controller::register_settings()` on `admin_init`. `wmmsp_settings_group` holds the six fields present on the settings form; fieldless advanced options sit in `wmmsp_scale_advanced_group`, which no form posts.

| Option | Group | Type | Default | Read by |
| --- | --- | --- | --- | --- |
| `wmmsp_base_size` | settings | number | 16.0 | `Plugin::scale_options()`, legacy sheet, settings page, widget, adapter |
| `wmmsp_ratio` | settings | number | 1.25 | `Plugin::scale_options()`, legacy sheet, settings page, widget, adapter |
| `wmmsp_unit` | settings | string | px | legacy sheet, settings page, widget, adapter |
| `wmmsp_precision` | settings | integer | 2 | legacy sheet, settings page, adapter |
| `wmmsp_apply_typography` | settings | boolean | 1 | tokens, settings page, widget, adapter (`get_option( …, 1 )`); legacy sheet (`get_option( …, 0 )`) |
| `wmmsp_delete_data_on_uninstall` | settings | boolean | 0 | settings page, `uninstall.php` |
| `wmmsp_base_size_max` | advanced | number | 18.0 | `Plugin::scale_options()` |
| `wmmsp_min_viewport` / `wmmsp_max_viewport` | advanced | number | 360.0, 1440.0 | `Plugin::scale_options()` |
| `wmmsp_font_override_preset` | advanced | string | inter-to-arial | `Plugin::scale_options()` |
| `wmmsp_ratio_name` | advanced | string | major-third | nobody |

`Plugin::scale_options()` feeds the token sheet, the editor inline config, `Ecosystem_Bridge`, `Theme_Json_Bridge`, and the `ratio-visualizer` and `theme-json-exporter` blocks.

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
  --ms-neg-2: clamp(0.64rem, 0.5867rem + 0.2369vw, 0.72rem);
  --wm-lh-neg-2: 1.6;
  … --wm-step-6: clamp(…); --ms-6: clamp(…); --wm-lh-6: …;
  --wm-font-size-body: var(--wm-step-0); … --wm-font-size-h1: var(--wm-step-6);
  --gp-font-size-body: var(--wm-step-0); … --gp-font-size-h1: var(--wm-step-6);
}
.has-fluid-body-font-size { … } … .has-fluid-h1-font-size { … }
.wm-fluid-container { container-type: inline-size; container-name: wm-typographic-context; }
```

The same CSS is registered on `wm-modularscale-tokens` (frontend) and `wm-modularscale-editor-tokens` (block editor canvas: `enqueue_block_assets`, admin only, whatever the apply toggle says).

---

## 4. Blocks

| Block | Title | Editor | Server Render |
| --- | --- | --- | --- |
| `wm-scale/fluid-heading` | Fluid Heading (WM Scale) | `RichText` + `InspectorControls` (`build/index.js`) | `render.php` |
| `wm-scale/fluid-lead` | Fluid Lead Text (WM Scale) | `RichText` + `InspectorControls` (`build/index.js`) | `render.php` |
| `wm-scale/fluid-container` | Fluid Container (@container) | `InnerBlocks` + `InspectorControls` (`build/index.js`) | `render.php` |
| `wm-scale/typographic-grid` | Typographic Grid (WM Scale) | `InnerBlocks` + `InspectorControls` (`build/index.js`) | `render.php` |
| `wm-scale/ratio-visualizer` | Ratio Visualizer (SVG Curve) | `ServerSideRender` + `InspectorControls` (`build/index.js`) | `render.php` |
| `wm-scale/contrast-matrix-badge` | APCA Contrast Badge (WM Scale) | `ServerSideRender` + `InspectorControls` (`build/index.js`) | `render.php` |
| `wm-scale/theme-json-exporter` | Theme.json Exporter (WM Scale) | `ServerSideRender` + `InspectorControls` (`build/index.js`) | `render.php` |

Full attribute reference: **[docs/BLOCKS.md](docs/BLOCKS.md)**.

---

## 5. Hooks & Extension Surface

### Hooks the plugin attaches to

| Hook | Where | Effect |
| --- | --- | --- |
| `init` | `Plugin`, `Block_Manager`, `Ecosystem_Bridge`, main file | blocks, editor script + JED translations, Block Bindings, GenerateBlocks 2.x Dynamic Tag, block patterns, text domain |
| `enqueue_block_assets` | `Block_Manager` | fluid tokens inside the iframed block editor canvas (admin only; on the front end `Plugin` prints them) |
| `wp_abilities_api_init` | `Ecosystem_Bridge` | registers `wm-modularscale/compute-scale` |
| `wp_head` (5) / `wp_enqueue_scripts` (20, 100) | `Plugin`, main file | fluid tokens; legacy sheet |
| `wp_theme_json_data_theme` (20) | `Theme_Json_Bridge` | fluid `fontSizes`, `defaultFontSizes: false`, `fluid: true` |
| `generate_google_fonts_array` (999), `generate_font_manager_show_google_fonts` (999) | `GeneratePress_Bridge` | no Google Fonts |
| `generate_typography_css` (20) | `GeneratePress_Bridge` | `--gp-font-size-*` + `h1`–`h6` rules appended |
| `generate_typography_default_fonts`, `generate_font_manager_system_fonts` | `GeneratePress_Bridge` | local font names in the pickers |
| `generateblocks_global_styles` (20), `generateblocks_typography_presets` (20) | `GenerateBlocks_Bridge` | `wm-fluid-*` styles, `wm-step-*` presets |
| `render_block_generateblocks/container` | `GenerateBlocks_Bridge` | `container-type: inline-size` when `useContainerQueries` |
| `admin_menu`, `admin_init`, `wp_dashboard_setup`, `admin_enqueue_scripts`, `wp_ajax_wmmsp_calculate_scale` | main file, `Settings_Controller` | admin surface |
| `wm_register_suite_module` | main file | hub spoke |
| `plugin_action_links_*` | main file | settings link |

### Filters & actions the plugin exposes to third-party developers

| Hook | Type | Signature | Purpose |
| --- | --- | --- | --- |
| `wm_modularscale_ratios` | Filter | `apply_filters( 'wm_modularscale_ratios', array $ratios )` | Add or adjust named ratios in `Scale_Engine::get_ratios()`. |
| `wm_modularscale_scale_options` | Filter | `apply_filters( 'wm_modularscale_scale_options', array $options )` | Override resolved `base_min`, `base_max`, `ratio`, `min_vp`, `max_vp`, `font_preset` per context/theme. |
| `wm_modularscale_tokens_generated` | Filter | `apply_filters( 'wm_modularscale_tokens_generated', string $css, array $context )` | Append or transform custom CSS properties emitted by `CSS_Token_Generator`. |
| `wm_modularscale_ready` | Action | `do_action( 'wm_modularscale_ready', string $version )` | Fires on `init` (priority 25) once blocks, Block Bindings, Dynamic Tags and patterns are registered. |

---

## 6. Known nuances

1. `wmmsp_apply_typography` defaults to 1 everywhere except the legacy sheet (0). A fresh install that was never saved prints the fluid tokens (`--wm-step-*`, `--ms-*`) without `wm-modularscale.css`, so activating the plugin does not resize an existing theme's headings until saved.
2. `wmmsp_ratio_name`, `wmmsp_base_size_max`, viewports and the font preset have no UI field (`wp option update`); `wmmsp_ratio_name` is unused.
3. `ratio-visualizer`, `theme-json-exporter` and `contrast-matrix-badge` use self-contained inline styles so specimen contrast stays independent of the active frontend theme.

---

## 7. WM Suite Hub spoke

`ModularScale_Spoke_Adapter` (loaded when `WM_Plugin_Module_Interface` exists): slug `wm-modularscale`, version read dynamically from the `wm-modularscale.php` header (`1.4.1`), license `GPL-2.0-or-later`, health `HEALTHY`, SBOM component with the SHA-256 of `wm-modularscale.php`, settings URL `admin.php?page=wm-modular-scale`, telemetry `base_size`, `scale_ratio`, `typography_applied`, `token_h1..h3`, actions `self_test` / `calculate_scale`.

---

## 8. Tests

```bash
php tests/test-suite.php         # standalone assertions: engine, clamp, APCA, 7 blocks + editorScript, bridges, uninstall, i18n
php tests/test-pot-freshness.php # every translatable string in code has an entry in languages/wm-modularscale.pot
bash tests/run-mutations.sh      # undoes each correction in tests/mutations.php on a copy; the suite must fail for every one
npm run build && npm test        # builds build/index.js + runs Vitest (sandbox, engine formulas, editor bundle)
npm run lint                     # ESLint flat config with --max-warnings 0
```

---

## 9. Security disclosure

See [SECURITY.md](SECURITY.md).
