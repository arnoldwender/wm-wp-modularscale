# WM Modular Scale (Deutsche Dokumentation)

<p align="center">
  <a href="README.md"><img src="https://img.shields.io/badge/Language-English-lightgrey.svg" alt="English Documentation" /></a>
  <a href="README.de.md"><img src="https://img.shields.io/badge/Sprache-Deutsch%20(Aktiv)-blue.svg" alt="Deutsche Dokumentation" /></a>
  <a href="README.es.md"><img src="https://img.shields.io/badge/Idioma-Espa%C3%B1ol-lightgrey.svg" alt="Documentación en Español" /></a>
</p>

> **Typografischer Skalen-Rechner, Generator fluider `clamp()`-Tokens und Typografie-Brücke zu Gutenberg, GeneratePress und GenerateBlocks**  
> **Autor & Chefarchitekt:** Arnold Wender ([ORCID: 0009-0005-1750-818X](https://orcid.org/0009-0005-1750-818X)) · Inhaber von Wender Media und SEO Halle · Halle (Saale) · 2026  
> **Unternehmensportal:** [https://www.wendermedia.com](https://www.wendermedia.com) · **Persönliche Website:** [https://www.arnoldwender.com](https://www.arnoldwender.com) · **SEO Portal:** [https://www.seo-halle.de](https://www.seo-halle.de) · **Kognitives Substrat:** [https://neurozoa.ai](https://neurozoa.ai)

> Diese Datei beschreibt, was das Plugin tatsächlich ausliefert. Bis zum 2026-09-14 nannte sie WP-CLI-Befehle (`wp modular …`), REST-Endpunkte, einen Shortcode `[wm_modular_scale_grid]`, GoBD-Exporte und eine HMAC-Hash-Kette; nichts davon existiert in diesem Repository (gemessen: kein `WP_CLI::add_command`, `register_rest_route`, `add_shortcode`, `hash_hmac`, `dbDelta`). Das Plugin speichert zehn Optionen und gibt CSS aus.

---

## Was das Plugin leistet

1. **Modulare Skala berechnen:** eine Basisgröße, ein Verhältnis (Major Second 1,125 … Goldener Schnitt 1,618 oder frei), neun Stufen von −2 (Mikro-Beschriftung) bis +6 (Hero).
2. **Fluide CSS-Tokens im `<head>`**, wenn *auf die Website anwenden* aktiv ist: `@property`-Deklarationen und ein `:root`-Block mit `--wm-step-neg-2 … --wm-step-6` als `clamp()` zwischen den konfigurierten Viewports (Standard 360 px → 1440 px), passenden Zeilenhöhen `--wm-lh-*`, semantischen Aliassen (`--wm-font-size-h1 … body`), GeneratePress-Aliassen (`--gp-font-size-*`), den Hilfsklassen `.has-fluid-h1-font-size … .has-fluid-body-font-size` und `.wm-fluid-container`. Jede Stufe wird auf WCAG 1.4.4 Zoom-Sicherheit geprüft (max ≤ 2,5 × min). Optionaler Anti-CLS-`@font-face`-Fallback (Inter → Arial u. a.).
3. **Optionales Legacy-Stylesheet:** der Schalter *anwenden* lädt zusätzlich `wm-modularscale.css`, das `body`, `h1`–`h6` und Absätze aus `--wmmsp-*`-Variablen (Basis, Verhältnis, Einheit, Präzision) dimensioniert. Keine Viewport-Regel verkleinert die eingestellte Basisgröße (Standard 16 px); ein Minimum erzwingt das Plugin nicht, das Feld nimmt jeden Wert ab 1.
4. **Sieben Gutenberg-Blöcke** in der Kategorie „WM Modular Scale“ (`wm-scale/*`): Fluid Heading, Fluid Lead Text, Fluid Container (`@container`), Typographic Grid (CSS-Raster mit Abstand aus der Skala), Ratio Visualizer (die neun Stufen in ihrer Größe), APCA Contrast Badge (Lc eines Farbpaars nach apca-w3 0.1.9 mit dem Nutzungshinweis des APCA-Lesbarkeitskriteriums; APCA ist nicht Teil der WCAG), Theme.json Exporter.
5. **theme.json-Brücke:** Filter `wp_theme_json_data_theme` — `settings.typography.fontSizes` werden die neun fluiden Stufen, `defaultFontSizes: false`, `fluid: true`.
6. **GeneratePress-Brücke:** leert die Google-Fonts-Liste und blendet sie im Font Manager aus, entfernt `generate-fonts`, hängt `--gp-font-size-*` und `h1`–`h6`-Regeln an das dynamische Typografie-CSS an und listet Inter, Outfit, Cinzel, Cabinet Grotesk und einen System-Stack in den Schriftauswahlen (das Plugin liefert keine Schriftdateien; die Namen greifen nur, wenn die Website sie bereitstellt).
7. **GenerateBlocks-Brücke:** Global Styles `wm-fluid-h1/h2/h3/body`, Typografie-Presets `wm-step-0 … 6`, `container-type: inline-size` auf Container-Blöcken mit dem Attribut `useContainerQueries`.
8. **Backend:** Einstellungsseite (Menü *Modular Scale*, mit aktivem Hub unter *Wender Media*; zusätzlich *Einstellungen › Modular Scale*) mit Live-Sandbox und Ein-Klick-Presets; Dashboard-Widget mit Basisgröße, Verhältnis und Anwendungsstatus. Beides auf den Admin-Stylesheets der Wender-Media-Familie.
9. **WM Suite Hub Spoke:** `includes/class-modularscale-spoke-adapter.php` meldet sich über `wm_register_suite_module` (SBOM-Eintrag mit SHA-256 der Hauptdatei, Telemetrie, Aktionen `self_test` / `calculate_scale`).

### Nicht enthalten

Keine WP-CLI-Befehle, keine REST-Endpunkte, keine Shortcodes, keine eigenen Datenbanktabellen, kein Audit-Protokoll oder Export, keine externen Anfragen.

---

## Schnellstart

1. Plugin installieren und aktivieren (WordPress 6.4+, PHP 8.1+).
2. **Modular Scale** im Admin-Menü öffnen (**Wender Media › Modular Scale** mit Hub).
3. Verhältnis-Preset wählen oder Wert eintippen, Basisgröße, Einheit und Präzision setzen, Sandbox prüfen, *Skala auf der Website anwenden* aktivieren, speichern.
4. Im Editor die Block-Kategorie *WM Modular Scale* oder die `.has-fluid-*`-Klassen nutzen; mit GeneratePress / GenerateBlocks erscheinen die Tokens in deren eigenen Bedienelementen.

Optionen, Hooks, Klassen und bekannte Abweichungen: **[README.md](README.md)** (Referenz) und **[DEVELOPERS.md](DEVELOPERS.md)**.

```bash
php tests/test-suite.php        # 98 Standalone-Prüfungen, ohne WordPress
bash tests/run-mutations.sh     # jede rückgängig gemachte Korrektur muss die Suite rot machen
php tests/test-ui-family.php    # Familien-Stylesheets byte-identisch mit dem Hub
npm test                        # vitest: Skript der Einstellungsseite in jsdom
```

---

## Datenschutz und Barrierefreiheit

- **Keine externen Anfragen:** kein CDN, keine Google Fonts (die GeneratePress-Brücke entfernt sie), keine Telemetrie an Dritte.
- **Barrierefreiheit:** die eingestellte Basisgröße (Standard 16 px) gilt auf jedem Viewport, jede fluide Stufe wird gegen WCAG 1.4.4 (Zoom, max ≤ 2,5 × min) geprüft, der APCA-Block zeigt den Lc-Wert eines Farbpaars. Eine Konformität mit WCAG oder BFSG wird nicht behauptet.
- **Datenschutz:** es werden keine personenbezogenen Daten verarbeitet; das Plugin liest und schreibt nur eigene Optionen.

---

## Lizenz

Vertrieben unter der **GNU General Public License v2 oder neuer**.  
Copyright (C) 2026 Arnold Wender / Wender Media.  
Website: [https://www.wendermedia.com](https://www.wendermedia.com) · Kognitives Substrat: [https://neurozoa.ai](https://neurozoa.ai)
