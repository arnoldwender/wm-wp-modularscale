/**
 * WM Modular Scale — settings page: live sandbox preview, ratio presets, generated CSS.
 *
 * Inline in the page with onclick/oninput handlers until 2026-09-14; the handlers are now
 * data attributes ([data-wmmsp-preset]) and the script ships from assets/js so the browser
 * caches it by file time.
 */
(function () {
	'use strict';

	function byId(id) {
		return document.getElementById(id);
	}

	function setLevel(level, size, unit) {
		const text = byId('prev-' + level);
		const badge = byId('badge-' + level);
		if (text) {
			text.style.fontSize = size + unit;
		}
		if (badge) {
			badge.textContent = (badge.getAttribute('data-prefix') || level.toUpperCase()) + ': ' + size + unit;
		}
	}

	function updateLivePreview() {
		const baseField = byId('wmmsp_base_size');
		const ratioField = byId('wmmsp_ratio');
		const unitField = byId('wmmsp_unit');
		const precField = byId('wmmsp_precision');
		if (!baseField || !ratioField || !unitField || !precField) {
			return;
		}

		const base = parseFloat(baseField.value) || 16;
		const ratio = parseFloat(ratioField.value) || 1.25;
		const unit = unitField.value || 'px';
		let prec = parseInt(precField.value, 10);
		if (isNaN(prec) || prec < 0) {
			prec = 2;
		}

		// H5 is the base step, H1 four steps up, H6 one step down: the same formula as the PHP side.
		const sizes = {
			h1: (base * Math.pow(ratio, 4)).toFixed(prec),
			h2: (base * Math.pow(ratio, 3)).toFixed(prec),
			h3: (base * Math.pow(ratio, 2)).toFixed(prec),
			h4: (base * Math.pow(ratio, 1)).toFixed(prec),
			h5: (base * Math.pow(ratio, 0)).toFixed(prec),
			h6: (base / Math.pow(ratio, 1)).toFixed(prec),
			p: base.toFixed(prec)
		};

		Object.keys(sizes).forEach(function (level) {
			setLevel(level, sizes[level], unit);
		});

		// The names and values wmmsp_force_styles() prints: round() drops trailing zeros, base size and
		// paragraph carry the unrounded field value. Until 2026-09-15 this block showed --wm-ms-* variables
		// that nothing emits.
		function asPhp(value) {
			return String(Number(value));
		}
		const cssCode = ':root {\n' +
			'  --wmmsp-base-size: ' + base + unit + ';\n' +
			'  --wmmsp-h1: ' + asPhp(sizes.h1) + unit + ';\n' +
			'  --wmmsp-h2: ' + asPhp(sizes.h2) + unit + ';\n' +
			'  --wmmsp-h3: ' + asPhp(sizes.h3) + unit + ';\n' +
			'  --wmmsp-h4: ' + asPhp(sizes.h4) + unit + ';\n' +
			'  --wmmsp-h5: ' + asPhp(sizes.h5) + unit + ';\n' +
			'  --wmmsp-h6: ' + asPhp(sizes.h6) + unit + ';\n' +
			'  --wmmsp-paragraph: ' + base + unit + ';\n' +
			'}';
		const codeEl = byId('wmmsp-generated-css');
		if (codeEl) {
			codeEl.textContent = cssCode;
		}
	}

	function setPreset(ratio, base, unit) {
		const ratioField = byId('wmmsp_ratio');
		const baseField = byId('wmmsp_base_size');
		const unitField = byId('wmmsp_unit');
		const select = byId('wmmsp_ratio_select');
		if (ratioField) {
			ratioField.value = ratio;
		}
		if (baseField) {
			baseField.value = base;
		}
		if (unitField) {
			unitField.value = unit;
		}
		if (select) {
			select.value = ratio.toFixed(3);
			if (!select.value) {
				select.value = 'custom';
			}
		}
		updateLivePreview();
	}

	document.addEventListener('DOMContentLoaded', function () {
		['wmmsp_base_size', 'wmmsp_ratio', 'wmmsp_precision'].forEach(function (id) {
			const field = byId(id);
			if (field) {
				field.addEventListener('input', updateLivePreview);
			}
		});

		const unitField = byId('wmmsp_unit');
		if (unitField) {
			unitField.addEventListener('change', updateLivePreview);
		}

		const select = byId('wmmsp_ratio_select');
		if (select) {
			select.addEventListener('change', function () {
				if (select.value !== 'custom') {
					const ratioField = byId('wmmsp_ratio');
					if (ratioField) {
						ratioField.value = select.value;
					}
					updateLivePreview();
				}
			});
		}

		const presets = document.querySelectorAll('[data-wmmsp-preset]');
		Array.prototype.forEach.call(presets, function (btn) {
			btn.addEventListener('click', function () {
				setPreset(
					parseFloat(btn.getAttribute('data-ratio')) || 1.25,
					parseFloat(btn.getAttribute('data-base')) || 16,
					btn.getAttribute('data-unit') || 'px'
				);
			});
		});

		updateLivePreview();
	});
})();
