# API — WM Modular Scale

Measured against the code on 2026-09-14. (This file was a generic REST-API template with `/api/example` endpoints until then.)

## There is no HTTP API

The plugin registers **no REST routes** (`register_rest_route` is not called), **no WP-CLI commands** and **no shortcodes**. The only request handler is the admin AJAX action below.

## Admin AJAX

`POST admin-ajax.php?action=wmmsp_calculate_scale` — requires a logged-in user with `manage_options` and the nonce `wmmsp_calc_nonce` in the `nonce` field.

| Field | Type | Default |
| --- | --- | --- |
| `base_min` | float (px) | 16 |
| `base_max` | float (px) | 18 |
| `ratio` | float | 1.25 |
| `min_vp` | float (px) | 360 |
| `max_vp` | float (px) | 1440 |

Response (`wp_send_json_success`):

```json
{
  "matrix": { "display": { "0": { "step": 0, "min_px": 16, "max_px": 18, "clamp": "clamp(1rem, …)", "line_height": 1.6, "zoom_safe": true }, "…": {} }, "body": {}, "caption": {} },
  "css": "/* === WM Modular Scale Enterprise CSS Tokens (2026) === */ …"
}
```

No shipped script calls this action; the settings page sandbox computes in the browser.

## PHP API (public static methods)

| Call | Returns |
| --- | --- |
| `Scale_Engine::calculate_step_value( float $base, float $ratio, int $step )` | px value |
| `Scale_Engine::derive_clamp( $min_px, $max_px, $min_vp = 360, $max_vp = 1440, $unit = 'vw', $root = 16 )` | `[ clamp, min_rem, max_rem, slope_pct, y_intercept_rem, zoom_safe ]` |
| `Scale_Engine::calculate_inverse_line_height( float $px )` | 1.6 … 1.1 |
| `Scale_Engine::generate_multi_strand_matrix( $min_vp, $max_vp, array $custom_strands = [] )` | display / body / caption steps |
| `Scale_Engine::calculate_apca_lc( string $text_hex, string $bg_hex )` | signed Lc |
| `Font_Metric_Matcher::get_preset_css( string $preset, ?string $family = null )` | `@font-face` override or `null` |
| `CSS_Token_Generator::generate_stylesheet( … )` | the full token CSS |
| `CSS_Token_Generator::export_tailwind_v4_theme( $base_min, $base_max, $ratio )` | `@theme { --text-wm-step-*: … }` |
| `Theme_Json_Bridge::build_font_sizes_schema( $base_min, $base_max, $ratio )` | theme.json `fontSizes` array |

## Filters offered

None. The plugin attaches to WordPress, GeneratePress and GenerateBlocks filters (DEVELOPERS.md §5) but exposes no filter of its own.

## CSS contract

`--wm-step-neg-2 … --wm-step-6`, `--wm-lh-*`, `--wm-font-size-{body,h1..h6}`, `--gp-font-size-*`, `.has-fluid-{body,h1..h6}-font-size`, `.wm-fluid-container`; legacy `--wmmsp-base-size`, `--wmmsp-h1 … h6`, `--wmmsp-paragraph`.
