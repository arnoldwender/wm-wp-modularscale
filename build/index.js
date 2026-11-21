/* WM Modular Scale — Compiled Gutenberg Blocks Bundle (generated from blocks-src/index.js) */
(function () {
'use strict';
/**
 * WM Modular Scale — Gutenberg Block Editor Suite (Block API v3)
 *
 * Registers all 7 WM Modular Scale blocks in the Gutenberg Block Inserter:
 * 1. wm-scale/fluid-heading
 * 2. wm-scale/fluid-lead
 * 3. wm-scale/fluid-container
 * 4. wm-scale/typographic-grid
 * 5. wm-scale/ratio-visualizer
 * 6. wm-scale/contrast-matrix-badge
 * 7. wm-scale/theme-json-exporter
 */

const wpGlobal = typeof window !== 'undefined' ? window.wp : undefined;

if ( wpGlobal && wpGlobal.blocks && wpGlobal.element && wpGlobal.blockEditor && wpGlobal.components && wpGlobal.i18n ) {
	const { registerBlockType } = wpGlobal.blocks;
	const { createElement: el, Fragment } = wpGlobal.element;
	const { InspectorControls, RichText, InnerBlocks, useBlockProps } = wpGlobal.blockEditor;
	const { PanelBody, RangeControl, SelectControl, TextControl, ToggleControl } = wpGlobal.components;
	const { __ } = wpGlobal.i18n;
	const ServerSideRender = wpGlobal.serverSideRender || null;

	const renderServerPreview = ( blockName, attributes, fallbackTitle ) => {
		if ( ServerSideRender ) {
			return el( ServerSideRender, {
				block: blockName,
				attributes,
			} );
		}
		return el(
			'div',
			{ className: 'components-placeholder' },
			el( 'strong', null, fallbackTitle )
		);
	};

	// 1. wm-scale/fluid-heading
	registerBlockType( 'wm-scale/fluid-heading', {
		edit( { attributes, setAttributes } ) {
			const { content = '', level = 2, step = 5, align = 'left', customClamp = '' } = attributes;
			const defaultStep = 7 - level;
			const stepVar = step < 0 ? `--wm-step-neg-${ Math.abs( step ) }` : `--wm-step-${ step }`;
			const style = {
				textAlign: align,
				textWrap: 'balance',
			};
			if ( customClamp ) {
				style.fontSize = customClamp;
			} else if ( step !== defaultStep ) {
				style.fontSize = `var(${ stepVar })`;
			}
			const blockProps = useBlockProps( {
				className: `has-fluid-h${ level }-font-size wm-fluid-heading`,
				style,
			} );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Fluid Heading Settings', 'wm-modularscale' ), initialOpen: true },
						el( RangeControl, {
							label: __( 'Heading level (H1–H6)', 'wm-modularscale' ),
							value: level,
							min: 1,
							max: 6,
							onChange: ( nextLevel ) => {
								const safeLevel = nextLevel || 2;
								setAttributes( { level: safeLevel, step: 7 - safeLevel } );
							},
						} ),
						el( RangeControl, {
							label: __( 'Modular scale step (-2 to +6)', 'wm-modularscale' ),
							value: step,
							min: -2,
							max: 6,
							onChange: ( nextStep ) => setAttributes( { step: typeof nextStep === 'number' ? nextStep : defaultStep } ),
						} ),
						el( SelectControl, {
							label: __( 'Text alignment', 'wm-modularscale' ),
							value: align,
							options: [
								{ label: __( 'Left', 'wm-modularscale' ), value: 'left' },
								{ label: __( 'Center', 'wm-modularscale' ), value: 'center' },
								{ label: __( 'Right', 'wm-modularscale' ), value: 'right' },
							],
							onChange: ( nextAlign ) => setAttributes( { align: nextAlign } ),
						} ),
						el( TextControl, {
							label: __( 'Custom CSS clamp() override', 'wm-modularscale' ),
							help: __( 'Optional full CSS clamp() expression overriding the scale step.', 'wm-modularscale' ),
							value: customClamp,
							placeholder: 'clamp(1.75rem, 1.2rem + 2vw, 3rem)',
							onChange: ( nextClamp ) => setAttributes( { customClamp: nextClamp } ),
						} )
					)
				),
				el( RichText, {
					...blockProps,
					tagName: `h${ level }`,
					value: content,
					placeholder: __( 'Write fluid heading…', 'wm-modularscale' ),
					onChange: ( nextContent ) => setAttributes( { content: nextContent } ),
				} )
			);
		},
		save() {
			return null;
		},
	} );

	// 2. wm-scale/fluid-lead
	registerBlockType( 'wm-scale/fluid-lead', {
		edit( { attributes, setAttributes } ) {
			const { content = '', measureLimit = 65, align = 'left' } = attributes;
			const clampedMeasure = Math.max( 35, Math.min( 85, measureLimit || 65 ) );
			const style = {
				maxWidth: `${ clampedMeasure }ch`,
				textAlign: align,
				textWrap: 'pretty',
			};
			if ( align !== 'left' ) {
				style.marginLeft = 'auto';
				style.marginRight = 'auto';
			}
			const blockProps = useBlockProps( {
				className: 'has-fluid-body-font-size wm-fluid-lead',
				style,
			} );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Fluid Lead Settings', 'wm-modularscale' ), initialOpen: true },
						el( RangeControl, {
							label: __( 'Maximum line length (characters)', 'wm-modularscale' ),
							value: clampedMeasure,
							min: 35,
							max: 85,
							onChange: ( nextMeasure ) => setAttributes( { measureLimit: nextMeasure || 65 } ),
						} ),
						el( SelectControl, {
							label: __( 'Text alignment', 'wm-modularscale' ),
							value: align,
							options: [
								{ label: __( 'Left', 'wm-modularscale' ), value: 'left' },
								{ label: __( 'Center', 'wm-modularscale' ), value: 'center' },
								{ label: __( 'Right', 'wm-modularscale' ), value: 'right' },
							],
							onChange: ( nextAlign ) => setAttributes( { align: nextAlign } ),
						} )
					)
				),
				el( RichText, {
					...blockProps,
					tagName: 'p',
					value: content,
					placeholder: __( 'Write lead paragraph…', 'wm-modularscale' ),
					onChange: ( nextContent ) => setAttributes( { content: nextContent } ),
				} )
			);
		},
		save() {
			return null;
		},
	} );

	// 3. wm-scale/fluid-container
	registerBlockType( 'wm-scale/fluid-container', {
		edit( { attributes, setAttributes } ) {
			const { containerName = 'wm-typographic-context', containerType = 'inline-size' } = attributes;
			const blockProps = useBlockProps( {
				className: 'wm-fluid-container',
				style: {
					containerType,
					containerName,
				},
			} );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Container Query Context', 'wm-modularscale' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Container name', 'wm-modularscale' ),
							value: containerName,
							onChange: ( nextName ) => setAttributes( { containerName: nextName || 'wm-typographic-context' } ),
						} ),
						el( SelectControl, {
							label: __( 'Container type', 'wm-modularscale' ),
							value: containerType,
							options: [
								{ label: __( 'Inline size (cqi / cqw)', 'wm-modularscale' ), value: 'inline-size' },
								{ label: __( 'Size (width & height)', 'wm-modularscale' ), value: 'size' },
								{ label: __( 'Normal', 'wm-modularscale' ), value: 'normal' },
							],
							onChange: ( nextType ) => setAttributes( { containerType: nextType } ),
						} )
					)
				),
				el( 'div', blockProps, el( InnerBlocks ) )
			);
		},
		save() {
			return el( InnerBlocks.Content );
		},
	} );

	// 4. wm-scale/typographic-grid
	registerBlockType( 'wm-scale/typographic-grid', {
		edit( { attributes, setAttributes } ) {
			const { columns = 3, gap = 'var(--wm-step-2, 1.5rem)' } = attributes;
			const safeCols = Math.max( 1, Math.min( 6, columns || 3 ) );
			const blockProps = useBlockProps( {
				className: 'wm-typographic-grid',
				style: {
					display: 'grid',
					gridTemplateColumns: `repeat(${ safeCols }, minmax(0, 1fr))`,
					gap,
					alignItems: 'start',
				},
			} );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Typographic Grid Settings', 'wm-modularscale' ), initialOpen: true },
						el( RangeControl, {
							label: __( 'Columns (1–6)', 'wm-modularscale' ),
							value: safeCols,
							min: 1,
							max: 6,
							onChange: ( nextCols ) => setAttributes( { columns: nextCols || 3 } ),
						} ),
						el( SelectControl, {
							label: __( 'Scale gap token', 'wm-modularscale' ),
							value: gap,
							options: [
								{ label: __( 'Step 0 (Body base)', 'wm-modularscale' ), value: 'var(--wm-step-0, 1rem)' },
								{ label: __( 'Step 1 (Compact)', 'wm-modularscale' ), value: 'var(--wm-step-1, 1.25rem)' },
								{ label: __( 'Step 2 (Default)', 'wm-modularscale' ), value: 'var(--wm-step-2, 1.5rem)' },
								{ label: __( 'Step 3 (Spacious)', 'wm-modularscale' ), value: 'var(--wm-step-3, 2rem)' },
								{ label: __( 'Step 4 (Wide)', 'wm-modularscale' ), value: 'var(--wm-step-4, 2.5rem)' },
							],
							onChange: ( nextGap ) => setAttributes( { gap: nextGap } ),
						} )
					)
				),
				el( 'div', blockProps, el( InnerBlocks ) )
			);
		},
		save() {
			return el( InnerBlocks.Content );
		},
	} );

	// 5. wm-scale/ratio-visualizer
	registerBlockType( 'wm-scale/ratio-visualizer', {
		edit( { attributes, setAttributes } ) {
			const { showSteps = true, showMath = true, theme = 'dark' } = attributes;
			const blockProps = useBlockProps();

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Ratio Visualizer Settings', 'wm-modularscale' ), initialOpen: true },
						el( ToggleControl, {
							label: __( 'Show step labels on SVG axis', 'wm-modularscale' ),
							checked: Boolean( showSteps ),
							onChange: ( val ) => setAttributes( { showSteps: Boolean( val ) } ),
						} ),
						el( ToggleControl, {
							label: __( 'Show step min/max cards', 'wm-modularscale' ),
							checked: Boolean( showMath ),
							onChange: ( val ) => setAttributes( { showMath: Boolean( val ) } ),
						} ),
						el( SelectControl, {
							label: __( 'Color theme', 'wm-modularscale' ),
							value: theme,
							options: [
								{ label: __( 'Dark', 'wm-modularscale' ), value: 'dark' },
								{ label: __( 'Light', 'wm-modularscale' ), value: 'light' },
							],
							onChange: ( nextTheme ) => setAttributes( { theme: nextTheme } ),
						} )
					)
				),
				el( 'div', blockProps, renderServerPreview( 'wm-scale/ratio-visualizer', attributes, __( 'Ratio Visualizer (SVG Curve)', 'wm-modularscale' ) ) )
			);
		},
		save() {
			return null;
		},
	} );

	// 6. wm-scale/contrast-matrix-badge
	registerBlockType( 'wm-scale/contrast-matrix-badge', {
		edit( { attributes, setAttributes } ) {
			const { textColor = '#f8fafc', backgroundColor = '#0f172a', targetContext = 'body' } = attributes;
			const blockProps = useBlockProps();

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'APCA Contrast Settings', 'wm-modularscale' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Foreground hex color', 'wm-modularscale' ),
							value: textColor,
							onChange: ( nextHex ) => setAttributes( { textColor: nextHex } ),
						} ),
						el( TextControl, {
							label: __( 'Background hex color', 'wm-modularscale' ),
							value: backgroundColor,
							onChange: ( nextHex ) => setAttributes( { backgroundColor: nextHex } ),
						} ),
						el( SelectControl, {
							label: __( 'Target text role', 'wm-modularscale' ),
							value: targetContext,
							options: [
								{ label: __( 'Body copy (target Lc 75)', 'wm-modularscale' ), value: 'body' },
								{ label: __( 'UI / secondary text (target Lc 60)', 'wm-modularscale' ), value: 'content' },
								{ label: __( 'Large headline (target Lc 45)', 'wm-modularscale' ), value: 'headline' },
							],
							onChange: ( nextCtx ) => setAttributes( { targetContext: nextCtx } ),
						} )
					)
				),
				el( 'div', blockProps, renderServerPreview( 'wm-scale/contrast-matrix-badge', attributes, __( 'APCA Contrast Badge (WM Scale)', 'wm-modularscale' ) ) )
			);
		},
		save() {
			return null;
		},
	} );

	// 7. wm-scale/theme-json-exporter
	registerBlockType( 'wm-scale/theme-json-exporter', {
		edit( { attributes, setAttributes } ) {
			const { format = 'json' } = attributes;
			const blockProps = useBlockProps();

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Exporter Output Settings', 'wm-modularscale' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Displayed export format', 'wm-modularscale' ),
							value: format,
							options: [
								{ label: __( 'Both (theme.json v3 + Tailwind v4)', 'wm-modularscale' ), value: 'json' },
								{ label: __( 'theme.json v3 only', 'wm-modularscale' ), value: 'json_only' },
								{ label: __( 'Tailwind CSS v4 @theme only', 'wm-modularscale' ), value: 'tailwind_only' },
							],
							onChange: ( nextFormat ) => setAttributes( { format: nextFormat } ),
						} )
					)
				),
				el( 'div', blockProps, renderServerPreview( 'wm-scale/theme-json-exporter', attributes, __( 'Theme.json Exporter (WM Scale)', 'wm-modularscale' ) ) )
			);
		},
		save() {
			return null;
		},
	} );
}

})();
