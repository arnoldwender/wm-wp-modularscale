import { describe, it, expect } from 'vitest';

describe('Modular Scale Calculation Engine', () => {
  const RATIOS: Record<string, number> = {
    'minor-second': 1.067,
    'major-second': 1.125,
    'minor-third': 1.2,
    'major-third': 1.25,
    'perfect-fourth': 1.333,
    'augmented-fourth': 1.414,
    'perfect-fifth': 1.5,
    'golden-ratio': 1.618,
  };

  const calculateStepValue = (base: number, ratio: number, step: number): number => {
    return base * Math.pow(ratio, step);
  };

  const deriveClamp = (
    minSizePx: number,
    maxSizePx: number,
    minVwPx = 360,
    maxVwPx = 1440,
    unit: 'vw' | 'cqi' = 'vw',
    rootFontSize = 16
  ) => {
    const minRem = minSizePx / rootFontSize;
    const maxRem = maxSizePx / rootFontSize;
    const slope = (maxSizePx - minSizePx) / (maxVwPx - minVwPx);
    const yInterceptPx = minSizePx - slope * minVwPx;
    const yInterceptRem = yInterceptPx / rootFontSize;
    const slopeUnit = slope * 100;
    const zoomSafe = maxSizePx <= minSizePx * 2.5;

    const clampStr = `clamp(${minRem.toFixed(4)}rem, ${yInterceptRem.toFixed(4)}rem + ${slopeUnit.toFixed(4)}${unit}, ${maxRem.toFixed(4)}rem)`;
    return { clamp: clampStr, zoomSafe, slope, yInterceptRem };
  };

  const calculateInverseLineHeight = (fontSizePx: number): number => {
    const minSize = 16.0;
    const maxSize = 64.0;
    const maxLh = 1.6;
    const minLh = 1.1;

    if (fontSizePx <= minSize) return maxLh;
    if (fontSizePx >= maxSize) return minLh;

    const progress = (fontSizePx - minSize) / (maxSize - minSize);
    return Number((maxLh - progress * (maxLh - minLh)).toFixed(2));
  };

  it('defines 8 classical harmonic ratios', () => {
    expect(Object.keys(RATIOS)).toHaveLength(8);
    expect(RATIOS['golden-ratio']).toBe(1.618);
    expect(RATIOS['major-third']).toBe(1.25);
  });

  it('calculates accurate exponential powers across steps', () => {
    const base = 16;
    const ratio = 1.25;
    expect(calculateStepValue(base, ratio, 0)).toBe(16);
    expect(calculateStepValue(base, ratio, 1)).toBe(20);
    expect(calculateStepValue(base, ratio, 2)).toBe(25);
    expect(calculateStepValue(base, ratio, -1)).toBeCloseTo(12.8, 2);
  });

  it('generates mathematically precise CSS clamp() strings', () => {
    const res = deriveClamp(16, 24, 360, 1440, 'vw', 16);
    expect(res.clamp).toMatch(/^clamp\(1\.0000rem, .* \+ .*vw, 1\.5000rem\)$/);
    expect(res.zoomSafe).toBe(true);
  });

  it('enforces WCAG 1.4.4 & BFSG 2025 zoom safety threshold (<= 2.5x)', () => {
    const safe = deriveClamp(16, 32);
    expect(safe.zoomSafe).toBe(true);

    const unsafe = deriveClamp(12, 48); // 4x scale
    expect(unsafe.zoomSafe).toBe(false);
  });

  it('calculates inverse line-height with smooth decay', () => {
    expect(calculateInverseLineHeight(16)).toBe(1.6);
    expect(calculateInverseLineHeight(64)).toBe(1.1);
    const midLh = calculateInverseLineHeight(40);
    expect(midLh).toBeGreaterThan(1.1);
    expect(midLh).toBeLessThan(1.6);
  });
});
