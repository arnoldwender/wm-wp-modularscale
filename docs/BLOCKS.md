# The seven blocks

Registered by [`src/Gutenberg/Block_Manager.php`](../src/Gutenberg/Block_Manager.php) with
`register_block_type_from_metadata()`, under the editor category **WM Modular Scale**. All seven
are server-rendered from `src/Gutenberg/Blocks/<dir>/render.php`.

The README names them by their titles; this file names them by the slug you actually search for.

```bash
find src/Gutenberg/Blocks -name block.json | wc -l    # 7
```

---

## Before anything else: no block has editor controls

**None of the seven ships an `editorScript`.** There is no `block.json` with that key, so no
sidebar panel exists for any attribute. In the editor you get the block with its defaults; to
change an attribute you edit the block markup in the code editor
(<kbd>⇧</kbd><kbd>⌥</kbd><kbd>⌘</kbd><kbd>M</kbd>) or set it in a block pattern or template.

And five attributes are read by their `render.php` but never used in its output, so setting them
changes nothing at all. Measured 2026-09-16 by counting the uses of the variable each one is
assigned to:

| Block | Inert attribute | What you would expect it to do |
|---|---|---|
| `wm-scale/fluid-heading` | `step` | pick the scale step independently of the heading level — the render uses the level's own class |
| `wm-scale/ratio-visualizer` | `showSteps` | hide the step list; it is never read |
| `wm-scale/ratio-visualizer` | `theme` | switch the visualiser between dark and light; never read |
| `wm-scale/theme-json-exporter` | `format` | choose `json` or the Tailwind block — both are always printed |
| `wm-scale/contrast-matrix-badge` | `targetContext` | change the usage hint per context; assigned, never used |

The other thirteen attributes work. This is a known gap, already recorded in the README's own
"Known issues".

---

## Typography

### `wm-scale/fluid-heading` — Fluid Heading
Renders `<h1>`–`<h6>` with the fluid size class of its level and `text-wrap: balance`.

| Attribute | Type | Default | Effect |
|---|---|---|---|
| `content` | string | `Überschrift mit harmonischer Skala` | the heading text, through `wp_kses_post` |
| `level` | integer | `2` | clamped to 1-6; picks the tag **and** the class `has-fluid-h<level>-font-size` |
| `align` | string | `left` | `text-align` |
| `customClamp` | string | `''` | when set, overrides the size with `font-size: <value> !important` |
| `step` | integer | `5` | **inert** |

`customClamp` takes a full CSS value, e.g. `clamp(2rem, 1rem + 3vw, 4rem)`. It carries
`!important`, so it also beats the theme.

### `wm-scale/fluid-lead` — Fluid Lead Text
A lead paragraph at the fluid body size with a bounded line length and `text-wrap: pretty`.

| Attribute | Type | Default | Effect |
|---|---|---|---|
| `content` | string | `Einführungstext mit optimierter Zeilenbreite…` | the paragraph text |
| `measureLimit` | integer | `65` | maximum line length in characters, clamped to 35-85 |
| `align` | string | `left` | `text-align` |

65 characters is the readability target the default encodes; the 35-85 clamp is what the render
enforces regardless of what is set.

---

## Layout

### `wm-scale/fluid-container` — Fluid Container (`@container`)
Opens a container-query context so descendants can size themselves against this box rather than
the viewport.

| Attribute | Type | Default |
|---|---|---|
| `containerName` | string | `wm-typographic-context` |
| `containerType` | string | `inline-size` |

Accepts inner blocks. The name is what a `@container wm-typographic-context (…)` rule matches, so
changing it silently detaches any CSS written against the default.

### `wm-scale/typographic-grid` — Typographic Grid
Equal-width columns for inner blocks.

| Attribute | Type | Default | Effect |
|---|---|---|---|
| `columns` | integer | `3` | clamped to 1-6 |
| `gap` | string | `var(--wm-step-2, 1.5rem)` | the grid gap, from the scale with a literal fallback |

---

## Inspecting the scale

These three read the stored scale rather than their own attributes, so what they show changes
when the scale does.

### `wm-scale/ratio-visualizer` — Ratio Visualizer
Draws the nine steps with the minimum and maximum px size each one resolves to, computed from
`wmmsp_base_size`, `wmmsp_ratio` and the configured viewports.

| Attribute | Type | Default | Effect |
|---|---|---|---|
| `showMath` | boolean | `true` | print the formula next to each step |
| `showSteps` | boolean | `true` | **inert** |
| `theme` | string | `dark` | **inert** |

### `wm-scale/theme-json-exporter` — Theme.json Exporter
Prints the `settings.typography.fontSizes` array for `theme.json` **and** the Tailwind CSS v4
`@theme` block, both generated from the stored scale, ready to copy.

| Attribute | Type | Default | Effect |
|---|---|---|---|
| `format` | string | `json` | **inert** — both outputs always render |

### `wm-scale/contrast-matrix-badge` — APCA Contrast Badge
Shows the APCA lightness contrast (Lc) of a colour pair with a usage hint for that value.

| Attribute | Type | Default |
|---|---|---|
| `textColor` | string | `#f8fafc` |
| `backgroundColor` | string | `#0f172a` |
| `targetContext` | string | `body` — **inert** |

**APCA is not part of WCAG.** It is the contrast method of the draft WCAG 3, computed here per
`apca-w3` 0.1.9. A BFSG or WCAG 2.1 AA conformance claim rests on the WCAG 2 contrast ratio
(4.5:1 for body text, 3:1 for large text), which is a different calculation with different
results. Use this badge to design, not to evidence conformance.

---

## Where the values come from

The blocks that read the scale read these six options, all set on the plugin's settings screen:

| Option | Default |
|---|---|
| `wmmsp_base_size` | `16` |
| `wmmsp_ratio` | `1.25` |
| `wmmsp_unit` | `px` |
| `wmmsp_precision` | `2` |
| `wmmsp_apply_typography` | see below |
| `wmmsp_font_override_preset` | `inter-to-arial` |

`wmmsp_apply_typography` gates **two different stylesheets, and they start in opposite states**
on a fresh install where the option has never been saved:

| Read in | Default | Controls |
|---|---|---|
| [`src/Plugin.php:79,94`](../src/Plugin.php#L79) | `1` — on | the `--wm-step-*` custom properties injected into `<head>` and enqueued on the front end |
| [`wm-modularscale.php:67`](../wm-modularscale.php#L67) | `0` — off | the legacy `wm-modularscale.css`, which resizes `body` and the headings directly |

That is deliberate: the legacy sheet changes the look of an existing site, so it stays off until
the toggle is explicitly saved, while the custom properties change nothing until something uses
them. Consequence worth knowing: **saving the settings screen with the toggle on activates the
legacy sheet as well**, and that is the moment an existing site's type sizes visibly move.
