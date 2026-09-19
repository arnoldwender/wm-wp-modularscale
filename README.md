# WM Modular Scale

<p align="center">
  <a href="README.md"><img src="https://img.shields.io/badge/Language-English%20(Default)-blue.svg" alt="English Documentation" /></a>
  <a href="README.de.md"><img src="https://img.shields.io/badge/Sprache-Deutsch-lightgrey.svg" alt="Deutsche Dokumentation" /></a>
  <a href="README.es.md"><img src="https://img.shields.io/badge/Idioma-Espa%C3%B1ol-lightgrey.svg" alt="Documentación en Español" /></a>
  <a href="#translated-documentation"><img src="https://img.shields.io/badge/Translations-24%20EU%20Languages-success.svg" alt="24 EU Translations" /></a>
</p>

<p align="center">
  <a href="https://wordpress.org"><img src="https://img.shields.io/badge/WordPress-6.4%20--%207.1+-21759b.svg?logo=wordpress&logoColor=white" alt="WordPress Version" /></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.1%20%7C%208.2%20%7C%208.3+-777bb4.svg?logo=php&logoColor=white" alt="PHP Version" /></a>
  <a href="https://www.wendermedia.com"><img src="https://img.shields.io/badge/Zero--CDN-no%20external%20fonts-purple.svg" alt="Zero-CDN" /></a>
  <a href="https://www.wendermedia.com"><img src="https://img.shields.io/badge/License-GPLv2%20Commercial-brightgreen.svg" alt="License" /></a>
</p>

> **Typographic scale calculator, fluid `clamp()` token generator and Gutenberg / GeneratePress / GenerateBlocks typography bridge for WordPress**  
> **Author & Lead Architect:** Arnold Wender ([ORCID: 0009-0005-1750-818X](https://orcid.org/0009-0005-1750-818X)) · Inhaber, Wender Media (Einzelunternehmen) · Halle (Saale), Germany · 2026  
> **Corporate Portal:** [https://www.wendermedia.com](https://www.wendermedia.com) · **Personal Website:** [https://www.arnoldwender.com](https://www.arnoldwender.com) · **SEO Portal:** [https://www.seo-halle.de](https://www.seo-halle.de) · **Cognitive Substrate:** [https://neurozoa.ai](https://neurozoa.ai)

> This README describes what the plugin ships. Until 2026-09-14 it also described WP-CLI commands (`wp modular …`), a REST namespace, a `[wm_modular_scale_grid]` shortcode, GoBD audit exports and an HMAC hash chain; none of those exist in this repository (measured: no `WP_CLI::add_command`, `register_rest_route`, `add_shortcode`, `hash_hmac` or `dbDelta` call). The plugin stores ten options and prints CSS.

---

## Showcase

![WM Modular Scale banner](assets/images/modular-scale-banner.jpg)

Settings screen and dashboard widget on the Wender Media admin family: `docs/screenshots/design-family/`.

---

## What the plugin does

1. **Calculates a modular scale.** One base size, one ratio (Major Second 1.125 … Golden Ratio 1.618, or a custom value), nine steps from −2 (micro caption) to +6 (hero) — `WenderMedia\ModularScale\Core\Scale_Engine`.
2. **Prints fluid CSS tokens into `<head>`** when *apply to the site* is on: `@property` declarations and a `:root` block with `--wm-step-neg-2 … --wm-step-6` as `clamp(min, preferred, max)` between the configured viewports (default 360 px → 1440 px), matching `--wm-lh-*` line heights, semantic aliases (`--wm-font-size-h1 … body`) and GeneratePress aliases (`--gp-font-size-*`), plus the utility classes `.has-fluid-h1-font-size … .has-fluid-body-font-size` and `.wm-fluid-container` (container queries). Each step is checked against WCAG 1.4.4 zoom safety (max ≤ 2.5 × min). Optional anti-CLS `@font-face` fallback override (Inter → Arial, Roboto → Arial, Merriweather → Georgia, Playfair → Times).
3. **Optional legacy sheet.** The settings page's *apply* toggle also enqueues `wm-modularscale.css`, which sizes `body`, `h1`–`h6`, `p`/lists from `--wmmsp-*` variables computed from base, ratio, unit and precision. No viewport rule shrinks the configured base size (default 16 px); the plugin does not enforce a minimum, the field accepts any value from 1.
4. **Seven Gutenberg blocks** in the "WM Modular Scale" category — each one with its slug, attributes and defaults in **[docs/BLOCKS.md](docs/BLOCKS.md)** (`wm-scale/*`): Fluid Heading, Fluid Lead Text, Fluid Container (`@container`), Typographic Grid (CSS grid with a scale gap), Ratio Visualizer (the nine steps drawn at their sizes), APCA Contrast Badge (Lc of a colour pair per apca-w3 0.1.9 with the usage hint of APCA's readability criterion; APCA is not part of WCAG), Theme.json Exporter.
5. **theme.json bridge.** Filters `wp_theme_json_data_theme`: `settings.typography.fontSizes` become the nine fluid steps (`wm-step-neg-2 … wm-step-6`), `defaultFontSizes: false`, `fluid: true`.
6. **GeneratePress bridge.** Empties the Google Fonts list and hides it in the Font Manager, dequeues `generate-fonts`, appends `--gp-font-size-*` plus `h1`–`h6` rules to GeneratePress' dynamic typography CSS, and lists Inter, Outfit, Cinzel, Cabinet Grotesk and a system stack in the font pickers (the plugin ships no font files; those names resolve only if the site provides them).
7. **GenerateBlocks bridge.** Global styles `wm-fluid-h1/h2/h3/body`, typography presets `wm-step-0 … 6`, and `container-type: inline-size` on a Container block that carries the `useContainerQueries` attribute.
8. **Admin.** Settings page (menu *Modular Scale*, or under *Wender Media* when the hub is active; also *Settings › Modular Scale*) with a live sandbox preview and one-click ratio presets; a dashboard widget with the configured base, ratio and apply state. Both render from the Wender Media admin family sheets.
9. **WM Suite Hub spoke.** `includes/class-modularscale-spoke-adapter.php` registers through `wm_register_suite_module`: SBOM entry with the SHA-256 of the main file, telemetry (base size, ratio, applied, H1–H3 tokens) and the `self_test` / `calculate_scale` actions.

### Not shipped

No WP-CLI commands, no REST endpoints, no shortcodes, no custom database tables, no audit log or export, no external requests.

---

## Quick start

1. Install and activate the plugin (WordPress 6.4+, PHP 8.1+).
2. Open **Modular Scale** in the admin menu (**Wender Media › Modular Scale** with the hub).
3. Pick a ratio preset or type one, set base size, unit and precision, watch the sandbox, tick *Skala auf der Website anwenden* (the admin screens are German), save.
4. In the editor, use the *WM Modular Scale* block category or the `.has-fluid-*` classes; with GeneratePress / GenerateBlocks the tokens appear in their own controls.

### Options

| Option | Default | Written by |
| --- | --- | --- |
| `wmmsp_base_size` | 16 | settings page |
| `wmmsp_ratio` | 1.25 | settings page |
| `wmmsp_unit` | px | settings page |
| `wmmsp_precision` | 2 | settings page |
| `wmmsp_apply_typography` | 1 for the token output, 0 for the legacy sheet — see Known issues | settings page |
| `wmmsp_base_size_max` | 18 | not exposed in the UI (`wp option update`) |
| `wmmsp_min_viewport` / `wmmsp_max_viewport` | 360 / 1440 | not exposed in the UI (`wp option update`) |
| `wmmsp_ratio_name` | major-third | not exposed in the UI; read by nothing |
| `wmmsp_font_override_preset` | inter-to-arial | not exposed in the UI (`wp option update`) |

A stored value the scale cannot use (0, empty, viewports in the wrong order, a maximum below the base size) falls back to the default above; until 2026-09-15 every save of the settings page wrote 0 into the options without a field.

---

## Developers

Hooks, classes, tokens and the hub adapter: **[DEVELOPERS.md](DEVELOPERS.md)**. Architecture: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md). Deployment: docs/DEPLOYMENT.md.

```bash
php tests/test-suite.php        # 98 standalone assertions, no WordPress needed
bash tests/run-mutations.sh     # each correction in tests/mutations.php undone must turn the suite red
php tests/test-ui-family.php    # admin family sheets byte-identical to the hub
npm test                        # vitest: the settings-page script in jsdom, plus a TypeScript copy of the engine formulas
```

### Known issues

- `wmmsp_apply_typography` has two defaults: 1 for the fluid tokens, the settings page, the widget and the hub telemetry, 0 for the legacy sheet. A fresh install that was never saved prints the tokens but not the legacy sheet, although the toggle shows ticked.
- The `.po` / `.mo` catalogs in `languages/` do not translate: their entries repeat the source string (de_DE and 20 other locales 0 of 132 entries translated, bg_BG, cs_CZ and da_DK 21, `ar` 70 of 70, measured 2026-09-15), and they were written for the earlier Spanish settings page. Every locale shows the German source strings of the admin screens.
- The blocks ship no editor script (no `editorScript` in any `block.json`), so their attributes have no controls; `showSteps` and `theme` (Ratio Visualizer), `format` (Theme.json Exporter), `targetContext` (APCA badge) and `step` (Fluid Heading) change nothing in the render.
- Ratio Visualizer, Theme.json Exporter and APCA badge style themselves with inline colours of a fixed dark theme, not with tokens.

---

## Translated documentation

`languages/` carries `.po` / `.mo` files for the 24 official EU languages (28 locale files); they are not translated yet, see Known issues. Translated READMEs live in `docs/translations/README.<lang>.md` and share this file's structure; the English README is the reference.

| Language (Native) | Locale Code | Language (Native) | Locale Code |
|---|---|---|---|
| [Български](docs/translations/README.bg.md) | `bg_BG` | [Magyar](docs/translations/README.hu.md) | `hu_HU` |
| [Čeština](docs/translations/README.cs.md) | `cs_CZ` | [Italiano](docs/translations/README.it.md) | `it_IT` |
| [Dansk](docs/translations/README.da.md) | `da_DK` | [Lietuvių](docs/translations/README.lt.md) | `lt_LT` |
| [Deutsch](README.de.md) | `de_DE` | [Latviešu](docs/translations/README.lv.md) | `lv_LV` |
| [Ελληνικά](docs/translations/README.el.md) | `el_GR` | [Malti](docs/translations/README.mt.md) | `mt_MT` |
| [English](README.md) | `en_GB` | [Nederlands](docs/translations/README.nl.md) | `nl_NL` |
| [Español](README.es.md) | `es_ES` | [Polski](docs/translations/README.pl.md) | `pl_PL` |
| [Eesti](docs/translations/README.et.md) | `et_EE` | [Português](docs/translations/README.pt.md) | `pt_PT` |
| [Suomi](docs/translations/README.fi.md) | `fi_FI` | [Română](docs/translations/README.ro.md) | `ro_RO` |
| [Français](docs/translations/README.fr.md) | `fr_FR` | [Slovenčina](docs/translations/README.sk.md) | `sk_SK` |
| [Gaeilge](docs/translations/README.ga.md) | `ga_IE` | [Slovenščina](docs/translations/README.sl.md) | `sl_SI` |
| [Hrvatski](docs/translations/README.hr.md) | `hr_HR` | [Svenska](docs/translations/README.sv.md) | `sv_SE` |

---

## Compliance

- **Zero external requests:** no CDN, no Google Fonts (the GeneratePress bridge removes them), no telemetry to third parties.
- **Accessibility:** the configured base size (default 16 px) holds on every viewport, every fluid step is checked against WCAG 1.4.4 zoom (max ≤ 2.5 × min), the APCA badge shows the Lc of a colour pair. No conformity with WCAG or the BFSG is claimed.
- **Privacy:** no personal data is processed; the plugin reads and writes its own options only.

---

## Contribution & Security

- [CONTRIBUTING.md](CONTRIBUTING.md) · [SECURITY.md](SECURITY.md) · [CHANGELOG.md](CHANGELOG.md)

---

## License

Distributed under the **GNU General Public License v2 or later**.  
Copyright (C) 2026 Arnold Wender.  
Official website: [https://www.wendermedia.com](https://www.wendermedia.com) · Cognitive Substrate: [https://neurozoa.ai](https://neurozoa.ai)
