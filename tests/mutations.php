<?php
/**
 * Mutations for tests/run-mutations.sh: each entry undoes one correction with literal replacements
 * [ file, search, replace ]; the standalone suite must turn red for every one of them.
 *
 * @package WM_ModularScale
 */

return [
	[
		'scale_options keeps a zero or empty stored number',
		[
			[ 'src/Plugin.php', 'return $value > 0 ? $value : $default;', 'return $value;' ],
		],
	],
	[
		'scale_options accepts a ratio below 1',
		[
			[ 'src/Plugin.php', 'max( 1.0, $number( \'wmmsp_ratio\', 1.25 ) )', '$number( \'wmmsp_ratio\', 1.25 )' ],
		],
	],
	[
		'scale_options keeps a maximum base size below the minimum',
		[
			[ 'src/Plugin.php', 'if ( $base_max < $base_min ) {', 'if ( false ) {' ],
		],
	],
	[
		'scale_options keeps viewports in the wrong order',
		[
			[ 'src/Plugin.php', 'if ( $max_vp <= $min_vp ) {', 'if ( false ) {' ],
		],
	],
	[
		'scale_options returns an empty font preset',
		[
			[ 'src/Plugin.php', '\'\' !== $font_preset ? $font_preset : \'inter-to-arial\'', '$font_preset' ],
		],
	],
	[
		'get_compiled_css reads the raw options again',
		[
			[ 'src/Plugin.php', '			$scale[\'base_min\'],
			$scale[\'base_max\'],
			$scale[\'ratio\'],
			$scale[\'min_vp\'],
			$scale[\'max_vp\'],', '			(float) get_option( \'wmmsp_base_size\', 16.0 ),
			(float) get_option( \'wmmsp_base_size_max\', 18.0 ),
			(float) get_option( \'wmmsp_ratio\', 1.25 ),
			(float) get_option( \'wmmsp_min_viewport\', 360.0 ),
			(float) get_option( \'wmmsp_max_viewport\', 1440.0 ),' ],
		],
	],
	[
		'a fieldless option back in the group the page posts',
		[
			[ 'src/Admin/Settings_Controller.php', 'register_setting( \'wmmsp_scale_advanced_group\', \'wmmsp_min_viewport\', [', 'register_setting( \'wmmsp_settings_group\', \'wmmsp_min_viewport\', [' ],
		],
	],
	[
		'a page field no longer registered in the group the page posts',
		[
			[ 'src/Admin/Settings_Controller.php', 'register_setting( \'wmmsp_settings_group\', \'wmmsp_unit\', [', 'register_setting( \'wmmsp_scale_advanced_group\', \'wmmsp_unit\', [' ],
		],
	],
	[
		'the main file registers settings a second time',
		[
			[ 'wm-modularscale.php', '// Settings are registered once, in', 'register_setting( \'wmmsp_settings_group\', \'wmmsp_base_size\' ); // Settings are registered once, in' ],
		],
	],
	[
		'settings page reads the apply toggle with default 0',
		[
			[ 'wm-modularscale.php', 'get_option( \'wmmsp_apply_typography\', 1 ); // Unticked', 'get_option( \'wmmsp_apply_typography\', 0 ); // Unticked' ],
		],
	],
	[
		'dashboard widget reads the apply toggle with default false',
		[
			[ 'wm-modularscale.php', 'get_option( \'wmmsp_apply_typography\', 1 ); // The default the token output uses (Plugin).', 'get_option( \'wmmsp_apply_typography\', false ); // The default the token output uses (Plugin).' ],
		],
	],
	[
		'hub telemetry reads the apply toggle with default 0',
		[
			[ 'includes/class-modularscale-spoke-adapter.php', 'get_option( \'wmmsp_apply_typography\', 1 );', 'get_option( \'wmmsp_apply_typography\', 0 );' ],
		],
	],
	[
		'the spoke adapter carries its own copy of the version again',
		[
			[ 'includes/class-modularscale-spoke-adapter.php', '$header = get_file_data( dirname( __DIR__ ) . \'/wm-modularscale.php\', [ \'version\' => \'Version\' ], \'plugin\' );

		return \'\' !== $header[\'version\'] ? $header[\'version\'] : \'0.0.0\';', 'return \'1.2.0\';' ],
		],
	],
	[
		'Plugin::VERSION drifts from the plugin header again',
		[
			[ 'src/Plugin.php', 'public const VERSION = \'1.2.2\';', 'public const VERSION = \'1.2.0\';' ],
		],
	],
	[
		'the SBOM declares a Proprietary licence again',
		[
			[ 'includes/class-modularscale-spoke-adapter.php', '\'id\'   => \'GPL-2.0-or-later\',
						\'name\' => \'GNU General Public License v2.0 or later\',', '\'id\'   => \'Proprietary\',
						\'name\' => \'Proprietary Wender Media Commercial License\',' ],
		],
	],
	[
		'theme.json bridge reads the raw options again',
		[
			[ 'src/Integration/Theme_Json_Bridge.php', '$scale      = Plugin::scale_options();
		$font_sizes = self::build_font_sizes_schema( $scale[\'base_min\'], $scale[\'base_max\'], $scale[\'ratio\'] );', '$font_sizes = self::build_font_sizes_schema( (float) get_option( \'wmmsp_base_size\', 16.0 ), (float) get_option( \'wmmsp_base_size_max\', 18.0 ), (float) get_option( \'wmmsp_ratio\', 1.25 ) );' ],
		],
	],
	[
		'ratio visualizer block reads the raw maximum base size',
		[
			[ 'src/Gutenberg/Blocks/ratio-visualizer/render.php', '$base_max = $scale[\'base_max\'];', '$base_max = (float) get_option( \'wmmsp_base_size_max\', 18.0 );' ],
		],
	],
	[
		'theme.json exporter block reads the raw maximum base size',
		[
			[ 'src/Gutenberg/Blocks/theme-json-exporter/render.php', '$base_max = $scale[\'base_max\'];', '$base_max = (float) get_option( \'wmmsp_base_size_max\', 18.0 );' ],
		],
	],
	[
		'a Spanish label back on the settings page',
		[
			[ 'wm-modularscale.php', "esc_html_e( 'Einheit', 'wm-modularscale' )", "esc_html_e( 'Unidad de Medida', 'wm-modularscale' )" ],
		],
	],
	[
		'a Spanish block description back',
		[
			[ 'src/Gutenberg/Blocks/fluid-lead/block.json', '"description": "Paragraph with the fluid body font size', '"description": "Párrafo con el tamaño fluido' ],
		],
	],
	[
		'the sandbox names a variable the stylesheet does not print',
		[
			[ 'assets/js/modularscale-admin.js', "'  --wmmsp-h1: '", "'  --wm-ms-h1: '" ],
		],
	],
	[
		'package.json points "main" at the removed root script',
		[
			[ 'package.json', '"private": true,', '"private": true,' . "\n" . '  "main": "wm-modularscale.js",' ],
		],
	],
	[
		'the widget calls the off state "CSS Only" again',
		[
			[ 'wm-modularscale.php', "esc_html__( 'Aus', 'wm-modularscale' )", "esc_html__( 'CSS Only', 'wm-modularscale' )" ],
		],
	],
	[
		'APCA back to the hard clamp at Y 0.0005',
		[
			[ 'src/Core/Scale_Engine.php', '$txt_y = $txt_y > 0.022 ? $txt_y : $txt_y + pow( 0.022 - $txt_y, 1.414 );', '$txt_y = $txt_y > 0.0005 ? $txt_y : 0.0005;' ],
			[ 'src/Core/Scale_Engine.php', '$bg_y  = $bg_y > 0.022 ? $bg_y : $bg_y + pow( 0.022 - $bg_y, 1.414 );', '$bg_y  = $bg_y > 0.0005 ? $bg_y : 0.0005;' ],
		],
	],
	[
		'the APCA badge claims BFSG conformity again',
		[
			[ 'src/Gutenberg/Blocks/contrast-matrix-badge/render.php', "\$usage_hint   = 'Reicht für Fließtext';", "\$usage_hint   = 'BFSG 2025 Konform';" ],
		],
	],
	[
		'visualizer nodes back on a straight line by index',
		[
			[ 'src/Gutenberg/Blocks/ratio-visualizer/render.php', "\$steps[ \$idx ]['cy'] = 120 - ( ( \$st['max_px'] - \$lowest ) / \$span ) * 95;", "\$steps[ \$idx ]['cy'] = 120 - ( ( \$idx / 8.0 ) * 95 );" ],
		],
	],
	[
		'visualizer labels step +1 as H1 again',
		[
			[ 'src/Gutenberg/Blocks/ratio-visualizer/render.php', "'H' . ( 7 - \$s ) . \" (+{\$s})\"", "\"H{\$s} (+{\$s})\"" ],
		],
	],
	[
		'an emoji back in the exporter header',
		[
			[ 'src/Gutenberg/Blocks/theme-json-exporter/render.php', '>theme.json v3 &amp; Tailwind CSS v4 token export<', '>' . "\u{1F4E6}" . ' theme.json v3 &amp; Tailwind CSS v4 token export<' ],
		],
	],
	[
		'a registered block stops being documented, so its slug and its inert attributes go dark again',
		[
			[ 'docs/BLOCKS.md', '### `wm-scale/fluid-container` — Fluid Container', '### Fluid Container' ],
		],
	],
];
