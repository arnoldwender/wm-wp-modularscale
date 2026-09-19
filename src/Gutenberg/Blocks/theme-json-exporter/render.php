<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Server-Side Render for wm-scale/theme-json-exporter
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

use WenderMedia\ModularScale\Integration\Theme_Json_Bridge;
use WenderMedia\ModularScale\Tokens\CSS_Token_Generator;

$scale    = \WenderMedia\ModularScale\Plugin::scale_options();
$base_min = $scale['base_min'];
$base_max = $scale['base_max'];
$ratio    = $scale['ratio'];
$format   = $attributes['format'] ?? 'json';

$schema = [
	'version'  => 3,
	'settings' => [
		'typography' => [
			'defaultFontSizes' => false,
			'fluid'            => true,
			'fontSizes'        => Theme_Json_Bridge::build_font_sizes_schema( $base_min, $base_max, $ratio ),
		],
	],
];

$json_output = wp_json_encode( $schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
$tailwind_output = CSS_Token_Generator::export_tailwind_v4_theme( $base_min, $base_max, $ratio );

$class_name = 'wm-theme-json-exporter-box';
if ( ! empty( $attributes['className'] ) ) {
	$class_name .= ' ' . esc_attr( $attributes['className'] );
}

?>
<div class="<?php echo esc_attr( $class_name ); ?>" style="background:#0f172a;border:1px solid #334155;border-radius:12px;padding:20px;color:#f8fafc;font-family:monospace;font-size:0.85rem;">
	<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;border-bottom:1px solid #1e293b;padding-bottom:8px;font-family:sans-serif;">
		<span style="font-weight:700;color:#38bdf8;">theme.json v3 &amp; Tailwind CSS v4 token export</span>
	</div>

	<div style="margin-bottom:16px;">
		<div style="font-size:0.75rem;color:#94a3b8;margin-bottom:4px;font-family:sans-serif;font-weight:600;">theme.json (settings.typography.fontSizes):</div>
		<pre style="background:#040813;padding:12px;border-radius:8px;overflow-x:auto;max-height:220px;color:#38bdf8;margin:0;"><?php echo esc_html( $json_output ); ?></pre>
	</div>

	<div>
		<div style="font-size:0.75rem;color:#94a3b8;margin-bottom:4px;font-family:sans-serif;font-weight:600;">Tailwind CSS v4 (@theme):</div>
		<pre style="background:#040813;padding:12px;border-radius:8px;overflow-x:auto;color:#a7f3d0;margin:0;"><?php echo esc_html( $tailwind_output ); ?></pre>
	</div>
</div>
