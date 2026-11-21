import { describe, expect, it } from 'vitest';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

describe('Gutenberg Block Editor Bundle (build/index.js)', () => {
  it('registers all 7 wm-scale/* blocks with edit and save functions', () => {
    const registered: Record<string, { edit?: (props: unknown) => unknown; save?: () => unknown }> = {};

    const createEl = (type: unknown, props: Record<string, unknown> | null, ...children: unknown[]) => ({
      type,
      props: props ?? {},
      children,
    });

    const InnerBlocksMock = Object.assign(() => createEl('InnerBlocks', null), {
      Content: () => createEl('InnerBlocks.Content', null),
    });

    (window as unknown as Record<string, unknown>).wp = {
      blocks: {
        registerBlockType: (name: string, settings: { edit?: (props: unknown) => unknown; save?: () => unknown }) => {
          registered[name] = settings;
        },
      },
      element: {
        createElement: createEl,
        Fragment: 'Fragment',
      },
      blockEditor: {
        InspectorControls: 'InspectorControls',
        RichText: 'RichText',
        InnerBlocks: InnerBlocksMock,
        useBlockProps: (extra: Record<string, unknown> = {}) => extra,
      },
      components: {
        PanelBody: 'PanelBody',
        RangeControl: 'RangeControl',
        SelectControl: 'SelectControl',
        TextControl: 'TextControl',
        ToggleControl: 'ToggleControl',
      },
      i18n: {
        __: (text: string) => text,
      },
      serverSideRender: 'ServerSideRender',
    };

    const bundlePath = resolve(__dirname, '../build/index.js');
    const bundleCode = readFileSync(bundlePath, 'utf8');
    const runBundle = new Function(bundleCode);
    runBundle();

    const expectedSlugs = [
      'wm-scale/fluid-heading',
      'wm-scale/fluid-lead',
      'wm-scale/fluid-container',
      'wm-scale/typographic-grid',
      'wm-scale/ratio-visualizer',
      'wm-scale/contrast-matrix-badge',
      'wm-scale/theme-json-exporter',
    ];

    expect(Object.keys(registered).sort()).toEqual([...expectedSlugs].sort());

    for (const slug of expectedSlugs) {
      const def = registered[slug];
      expect(typeof def.edit).toBe('function');
      expect(typeof def.save).toBe('function');
      const rendered = def.edit!({
        attributes: {},
        setAttributes: () => undefined,
      });
      expect(rendered).toBeTruthy();
    }

    expect(registered['wm-scale/fluid-heading'].save!()).toBeNull();
    expect(registered['wm-scale/fluid-container'].save!()).toBeTruthy();
    expect(registered['wm-scale/typographic-grid'].save!()).toBeTruthy();
  });
});
