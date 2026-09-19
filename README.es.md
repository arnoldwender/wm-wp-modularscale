# WM Modular Scale (Documentación en Español)

<p align="center">
  <a href="README.md"><img src="https://img.shields.io/badge/Language-English-lightgrey.svg" alt="English Documentation" /></a>
  <a href="README.de.md"><img src="https://img.shields.io/badge/Sprache-Deutsch-lightgrey.svg" alt="Deutsche Dokumentation" /></a>
  <a href="README.es.md"><img src="https://img.shields.io/badge/Idioma-Espa%C3%B1ol%20(Activo)-blue.svg" alt="Documentación en Español" /></a>
</p>

> **Calculadora de escala tipográfica, generador de tokens `clamp()` fluidos y puente tipográfico para Gutenberg, GeneratePress y GenerateBlocks**  
> **Autor y desarrollador web:** Arnold Wender ([ORCID: 0009-0005-1750-818X](https://orcid.org/0009-0005-1750-818X)) · Inhaber, Wender Media (Einzelunternehmen) · Halle (Saale), Alemania · 2026  
> **Portal corporativo:** [https://www.wendermedia.com](https://www.wendermedia.com) · **Sitio personal:** [https://www.arnoldwender.com](https://www.arnoldwender.com) · **Portal SEO:** [https://www.seo-halle.de](https://www.seo-halle.de) · **Sustrato cognitivo:** [https://neurozoa.ai](https://neurozoa.ai)

> Este archivo describe lo que el plugin entrega de verdad. Hasta el 2026-09-14 mencionaba comandos WP-CLI (`wp modular …`), endpoints REST, un shortcode `[wm_modular_scale_grid]`, exportaciones GoBD y una cadena de hashes HMAC; nada de eso existe en este repositorio (medido: ningún `WP_CLI::add_command`, `register_rest_route`, `add_shortcode`, `hash_hmac` ni `dbDelta`). El plugin guarda diez opciones e imprime CSS.

---

## Qué hace el plugin

1. **Calcula una escala modular:** un tamaño base, una proporción (Major Second 1.125 … Proporción Áurea 1.618 o un valor libre), nueve pasos de −2 (micro leyenda) a +6 (hero).
2. **Imprime tokens CSS fluidos en `<head>`** cuando *aplicar al sitio* está activo: declaraciones `@property` y un bloque `:root` con `--wm-step-neg-2 … --wm-step-6` como `clamp()` entre los viewports configurados (por defecto 360 px → 1440 px), alturas de línea `--wm-lh-*`, alias semánticos (`--wm-font-size-h1 … body`), alias de GeneratePress (`--gp-font-size-*`), las clases `.has-fluid-h1-font-size … .has-fluid-body-font-size` y `.wm-fluid-container`. Cada paso se comprueba contra la seguridad de zoom WCAG 1.4.4 (máx ≤ 2.5 × mín). Fallback `@font-face` anti-CLS opcional (Inter → Arial, entre otros).
3. **Hoja heredada opcional:** el interruptor *aplicar* también carga `wm-modularscale.css`, que dimensiona `body`, `h1`–`h6` y párrafos a partir de las variables `--wmmsp-*` (base, proporción, unidad, precisión). Ninguna regla de viewport reduce el tamaño base configurado (16 px por defecto); el plugin no impone un mínimo, el campo acepta cualquier valor desde 1.
4. **Siete bloques de Gutenberg** en la categoría «WM Modular Scale» (`wm-scale/*`): Fluid Heading, Fluid Lead Text, Fluid Container (`@container`), Typographic Grid (rejilla CSS con separación de la escala), Ratio Visualizer (los nueve pasos a su tamaño), APCA Contrast Badge (Lc de un par de colores según apca-w3 0.1.9 con la indicación de uso del criterio de legibilidad de APCA; APCA no forma parte de las WCAG), Theme.json Exporter.
5. **Puente theme.json:** filtro `wp_theme_json_data_theme`; `settings.typography.fontSizes` pasan a ser los nueve pasos fluidos, `defaultFontSizes: false`, `fluid: true`.
6. **Puente GeneratePress:** vacía la lista de Google Fonts y la oculta en el Font Manager, retira `generate-fonts`, añade `--gp-font-size-*` y reglas `h1`–`h6` al CSS tipográfico dinámico y lista Inter, Outfit, Cinzel, Cabinet Grotesk y una pila del sistema en los selectores (el plugin no incluye archivos de fuentes; esos nombres solo resuelven si el sitio los provee).
7. **Puente GenerateBlocks:** estilos globales `wm-fluid-h1/h2/h3/body`, presets tipográficos `wm-step-0 … 6`, `container-type: inline-size` en los bloques Container con el atributo `useContainerQueries`.
8. **Administración:** página de ajustes (menú *Modular Scale*, o bajo *Wender Media* con el hub activo; también *Ajustes › Modular Scale*) con sandbox en vivo y presets de un clic; widget del escritorio con base, proporción y estado de aplicación. Ambos sobre las hojas admin de la familia Wender Media.
9. **Spoke del WM Suite Hub:** `includes/class-modularscale-spoke-adapter.php` se registra por `wm_register_suite_module` (entrada SBOM con el SHA-256 del archivo principal, telemetría, acciones `self_test` / `calculate_scale`).

### No incluido

Sin comandos WP-CLI, sin endpoints REST, sin shortcodes, sin tablas propias en la base de datos, sin registro de auditoría ni exportaciones, sin peticiones externas.

---

## Inicio rápido

1. Instala y activa el plugin (WordPress 6.4+, PHP 8.1+).
2. Abre **Modular Scale** en el menú de administración (**Wender Media › Modular Scale** con el hub).
3. Elige un preset de proporción o escribe una, fija tamaño base, unidad y precisión, revisa el sandbox, marca *Skala auf der Website anwenden* (las pantallas de administración están en alemán) y guarda.
4. En el editor usa la categoría de bloques *WM Modular Scale* o las clases `.has-fluid-*`; con GeneratePress / GenerateBlocks los tokens aparecen en sus propios controles.

Opciones, hooks, clases y desviaciones conocidas: **[README.md](README.md)** (referencia) y **[DEVELOPERS.md](DEVELOPERS.md)**.

```bash
php tests/test-suite.php        # 98 comprobaciones standalone, sin WordPress
bash tests/run-mutations.sh     # cada corrección deshecha tiene que poner la suite en rojo
php tests/test-ui-family.php    # hojas de la familia byte-idénticas al hub
npm test                        # vitest: script de la página de ajustes en jsdom
```

---

## Privacidad y accesibilidad

- **Sin peticiones externas:** sin CDN, sin Google Fonts (el puente GeneratePress las retira), sin telemetría a terceros.
- **Accesibilidad:** el tamaño base configurado (16 px por defecto) se mantiene en cualquier viewport, cada paso fluido se comprueba contra WCAG 1.4.4 (zoom, máx ≤ 2.5 × mín), el bloque APCA muestra el Lc de un par de colores. No se afirma conformidad con las WCAG ni con la BFSG.
- **Privacidad:** no se procesan datos personales; el plugin solo lee y escribe sus propias opciones.

---

## Licencia

Distribuido bajo la **GNU General Public License v2 o posterior**.  
Copyright (C) 2026 Arnold Wender / Wender Media.  
Sitio web: [https://www.wendermedia.com](https://www.wendermedia.com) · Sustrato cognitivo: [https://neurozoa.ai](https://neurozoa.ai)
