# The seven blocks

Registered by [`src/Gutenberg/Block_Manager.php`](../src/Gutenberg/Block_Manager.php) with
`register_block_type_from_metadata()`, under the editor category **WM Modular Scale — Typografie Enterprise** (`wm-scale`).
All seven share the compiled Gutenberg editor script `wm-modularscale-blocks-editor` ([`build/index.js`](../build/index.js), built from [`blocks-src/index.js`](../blocks-src/index.js)) with `InspectorControls` sidebar settings, localized via `wp_set_script_translations()`, and render on the server from `src/Gutenberg/Blocks/<dir>/render.php`.

```bash
find src/Gutenberg/Blocks -name block.json | wc -l    # 7
```

---

## Typography

### `wm-scale/fluid-heading` — Fluid Heading
Renders `<h1>`–`<h6>` with the fluid size class of its level and `text-wrap: balance`, edited inline via `RichText` with sidebar controls for level, modular step, alignment and optional `clamp()` override.

| Attribute | Type | Default | Effect |
|---|---|---|---|
| `content` | string | `Überschrift mit harmonischer Skala` | the heading text, sanitized through `wp_kses_post` |
| `level` | integer | `2` | clamped to 1–6; picks the tag **and** default class `has-fluid-h<level>-font-size` |
| `step` | integer | `5` | clamped to −2…+6; when different from the level's default step (`7 - level`), applies `font-size: var(--wm-step-<step>)` |
| `align` | string | `left` | `text-align` (`left`, `center`, `right`) |
| `customClamp` | string | `''` | when set, overrides `step` and level size with `font-size: <value> !important` |

### `wm-scale/fluid-lead` — Fluid Lead Text
A lead paragraph at the fluid body size with a bounded line length and `text-wrap: pretty`, edited inline via `RichText`.

| Attribute | Type | Default | Effect |
|---|---|---|---|
| `content` | string | `Einführungstext mit optimierter Zeilenbreite…` | the paragraph text |
| `measureLimit` | integer | `65` | maximum line length in characters, clamped to 35–85 (`max-width: <n>ch`) |
| `align` | string | `left` | `text-align` |

---

## Layout

### `wm-scale/fluid-container` — Fluid Container (`@container`)
Opens a container-query context so descendants can size themselves against this box rather than the viewport (`InnerBlocks` in the editor, sanitized via `wp_kses_post` on render).

| Attribute | Type | Default | Effect |
|---|---|---|---|
| `containerName` | string | `wm-typographic-context` | `container-name` matched by `@container` rules |
| `containerType` | string | `inline-size` | `container-type` (`inline-size`, `size`, `normal`) |

### `wm-scale/typographic-grid` — Typographic Grid
Equal-width CSS grid columns for inner blocks (`InnerBlocks` in the editor).

| Attribute | Type | Default | Effect |
|---|---|---|---|
| `columns` | integer | `3` | clamped to 1–6 (`repeat(<n>, minmax(0, 1fr))`) |
| `gap` | string | `var(--wm-step-2, 1.5rem)` | grid gap token from `--wm-step-0` through `--wm-step-4` |

---

## Inspecting the scale

These three preview live in the editor via `ServerSideRender` and update whenever you adjust their sidebar controls or save the plugin's scale settings.

### `wm-scale/ratio-visualizer` — Ratio Visualizer
Draws the nine steps on a proportional SVG curve with their resolved minimum and maximum px sizes from `Plugin::scale_options()`.

| Attribute | Type | Default | Effect |
|---|---|---|---|
| `showMath` | boolean | `true` | display step labels along the SVG axis |
| `showSteps` | boolean | `true` | display the nine min/max px cards beneath the curve |
| `theme` | string | `dark` | switches surface, border and text colours between `dark` and `light` |

### `wm-scale/theme-json-exporter` — Theme.json Exporter
Prints the `settings.typography.fontSizes` JSON array for `theme.json` v3 and/or the Tailwind CSS v4 `@theme` block generated from the stored scale.

| Attribute | Type | Default | Effect |
|---|---|---|---|
| `format` | string | `both` | `both` renders both panels; `json` renders `theme.json` v3 only; `tailwind` renders Tailwind CSS v4 `@theme` only |

### `wm-scale/contrast-matrix-badge` — APCA Contrast Badge
Shows the APCA lightness contrast (Lc) of a colour pair (`apca-w3` 0.1.9) and evaluates it against the selected typographic target role.

| Attribute | Type | Default | Effect |
|---|---|---|---|
| `textColor` | string | `#f8fafc` | foreground hex colour |
| `backgroundColor` | string | `#0f172a` | background hex colour |
| `targetContext` | string | `body` | selects target role threshold (`body` Lc 75, `ui` Lc 60, `headline` Lc 45) |

**APCA is not part of WCAG.** It is computed per `apca-w3` 0.1.9 as a design aid; WCAG 2.x / BFSG contrast checks use the WCAG 2 luminance ratio (4.5:1 / 3:1).

---

## Ecosystem bindings & patterns (`Ecosystem_Bridge`)

In addition to the seven blocks above, [`src/Integration/Ecosystem_Bridge.php`](../src/Integration/Ecosystem_Bridge.php) exposes the same scale tokens to Core blocks and GenerateBlocks 2.x:

- **Block Bindings (`wm-modularscale/tokens`):** bind Core Paragraph or Heading attributes to `key` (`h1`…`h6`, `body`, `ratio`, `base_min`, `base_max`) or `step` (`-2 … 6`) and `field` (`clamp`, `range`, `max_px`, `min_px`).
- **GenerateBlocks 2.x Dynamic Tag (`{{wm_scale_token}}`):** outputs the computed `clamp()`, px range, or max px size in GenerateBlocks 2.x text/headline blocks (`step`, `field`).
- **Block Patterns:** `wm-modularscale/typographic-specimen` (Fluid Container + Fluid Heading + Fluid Lead) and `wm-modularscale/gb-fluid-hero` (two-layer `gbp-section` + `gbp-section__inner` consuming `--ms-*` / `--wm-step-*`).

---

## Where the values come from

`Plugin::scale_options()` reads and validates the stored options (`wmmsp_base_size`, `wmmsp_base_size_max`, `wmmsp_ratio`, `wmmsp_min_viewport`, `wmmsp_max_viewport`, `wmmsp_font_override_preset`).

`wmmsp_apply_typography` gates two stylesheets with distinct fresh-install defaults:

| Read in | Default | Controls |
|---|---|---|
| `src/Plugin.php` | `1` — on | `--wm-step-*` and `--ms-*` custom properties injected into `<head>` and enqueued on frontend & editor |
| `wm-modularscale.php` | `0` — off | legacy `wm-modularscale.css`, which resizes `body` and `h1`–`h6` directly once explicitly saved |
