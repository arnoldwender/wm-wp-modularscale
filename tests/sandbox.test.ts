import { describe, it, expect } from 'vitest';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { runInThisContext } from 'node:vm';

// Runs the shipped settings-page script (assets/js/modularscale-admin.js) against the fields it reads and
// checks the generated :root block. The expected values are what wmmsp_force_styles() prints for the same
// fields: round( base * ratio^n, precision ), trailing zeros dropped, base size and paragraph unrounded.
// Until 2026-09-15 the block named --wm-ms-* variables that nothing emits.

const scriptPath = resolve(__dirname, '../assets/js/modularscale-admin.js');
const script = readFileSync(scriptPath, 'utf8');
const mainPhp = readFileSync(resolve(__dirname, '../wm-modularscale.php'), 'utf8');

function add(tag: string, id: string, attrs: Record<string, string> = {}): HTMLElement {
  const el = document.createElement(tag);
  el.id = id;
  for (const [name, value] of Object.entries(attrs)) {
    el.setAttribute(name, value);
  }
  document.body.appendChild(el);
  return el;
}

function runSandbox(base: string, ratio: string, unit: string, precision: string): string {
  document.body.replaceChildren();
  (add('input', 'wmmsp_base_size') as HTMLInputElement).value = base;
  (add('input', 'wmmsp_ratio') as HTMLInputElement).value = ratio;
  const unitSelect = add('select', 'wmmsp_unit') as HTMLSelectElement;
  for (const u of ['px', 'rem', 'em']) {
    unitSelect.appendChild(new Option(u, u));
  }
  unitSelect.value = unit;
  (add('input', 'wmmsp_precision') as HTMLInputElement).value = precision;
  (add('select', 'wmmsp_ratio_select') as HTMLSelectElement).appendChild(new Option('custom', 'custom'));
  add('span', 'badge-h1', { 'data-prefix': 'H1' });
  add('p', 'prev-h1');
  add('pre', 'wmmsp-generated-css');

  // The plugin's own file from disk, run the way the browser runs it: it registers a DOMContentLoaded listener.
  runInThisContext(script, { filename: scriptPath });
  document.dispatchEvent(new Event('DOMContentLoaded'));
  return document.getElementById('wmmsp-generated-css')?.textContent ?? '';
}

describe('settings page sandbox', () => {
  it('prints the stylesheet variables with the values the PHP side prints', () => {
    expect(runSandbox('16', '1.25', 'px', '2')).toBe(
      [
        ':root {',
        '  --wmmsp-base-size: 16px;',
        '  --wmmsp-h1: 39.06px;',
        '  --wmmsp-h2: 31.25px;',
        '  --wmmsp-h3: 25px;',
        '  --wmmsp-h4: 20px;',
        '  --wmmsp-h5: 16px;',
        '  --wmmsp-h6: 12.8px;',
        '  --wmmsp-paragraph: 16px;',
        '}',
      ].join('\n'),
    );
  });

  it('names exactly the variables wmmsp_force_styles() declares', () => {
    const fn = mainPhp.slice(
      mainPhp.indexOf('function wmmsp_force_styles'),
      mainPhp.indexOf("add_action( 'wp_enqueue_scripts', 'wmmsp_force_styles'"),
    );
    const phpVars = [...fn.matchAll(/(--wmmsp-[a-z0-9-]+):/g)].map((m) => m[1]).sort();
    const jsVars = [...runSandbox('18', '1.333', 'rem', '3').matchAll(/(--[a-z0-9-]+):/g)].map((m) => m[1]).sort();
    expect(phpVars.length).toBeGreaterThanOrEqual(8);
    expect(jsVars).toEqual(phpVars);
  });

  it('writes the size into the preview badge', () => {
    runSandbox('16', '1.25', 'px', '2');
    expect(document.getElementById('badge-h1')?.textContent).toBe('H1: 39.06px');
  });
});
