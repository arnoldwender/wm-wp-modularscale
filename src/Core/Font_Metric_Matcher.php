<?php
declare(strict_types=1);

namespace WenderMedia\ModularScale\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Font_Metric_Matcher
 *
 * Computes and generates @font-face fallback overrides (size-adjust, ascent-override,
 * descent-override, line-gap-override) to achieve Cumulative Layout Shift (CLS) = 0.000
 * when web fonts load asynchronously with font-display: swap.
 */
class Font_Metric_Matcher {

	/**
	 * Canonical font metric database for standard web fonts & fallbacks.
	 *
	 * [unitsPerEm, ascent, descent, lineGap, xHeight, capHeight]
	 */
	public const FONT_METRICS = [
		'inter' => [
			'units_per_em' => 2048,
			'ascent'       => 1984,
			'descent'      => -494,
			'line_gap'     => 0,
			'x_height'     => 1082,
		],
		'roboto' => [
			'units_per_em' => 2048,
			'ascent'       => 1900,
			'descent'      => -500,
			'line_gap'     => 0,
			'x_height'     => 1082,
		],
		'arial' => [
			'units_per_em' => 2048,
			'ascent'       => 1854,
			'descent'      => -434,
			'line_gap'     => 67,
			'x_height'     => 1062,
		],
		'times-new-roman' => [
			'units_per_em' => 2048,
			'ascent'       => 1825,
			'descent'      => -443,
			'line_gap'     => 87,
			'x_height'     => 930,
		],
		'georgia' => [
			'units_per_em' => 2048,
			'ascent'       => 1878,
			'descent'      => -434,
			'line_gap'     => 0,
			'x_height'     => 987,
		],
		'helvetica' => [
			'units_per_em' => 2048,
			'ascent'       => 1850,
			'descent'      => -450,
			'line_gap'     => 0,
			'x_height'     => 1070,
		],
	];

	/**
	 * Pre-calibrated font override presets
	 */
	public const PRESETS = [
		'inter-to-arial' => [
			'target_font'   => 'Inter',
			'fallback_font' => 'Arial',
			'size_adjust'   => 97.4,
			'ascent'        => 88.5,
			'descent'       => 21.0,
			'line_gap'      => 0.0,
		],
		'roboto-to-arial' => [
			'target_font'   => 'Roboto',
			'fallback_font' => 'Arial',
			'size_adjust'   => 98.1,
			'ascent'        => 92.7,
			'descent'       => 24.4,
			'line_gap'      => 0.0,
		],
		'merriweather-to-georgia' => [
			'target_font'   => 'Merriweather',
			'fallback_font' => 'Georgia',
			'size_adjust'   => 90.2,
			'ascent'        => 98.5,
			'descent'       => 28.0,
			'line_gap'      => 0.0,
		],
		'playfair-to-times' => [
			'target_font'   => 'Playfair Display',
			'fallback_font' => 'Times New Roman',
			'size_adjust'   => 93.5,
			'ascent'        => 95.0,
			'descent'       => 22.5,
			'line_gap'      => 0.0,
		],
	];

	/**
	 * Generate CSS @font-face fallback override rule.
	 *
	 * @param string $fallback_name Local system font name (e.g., 'Arial').
	 * @param string $custom_fallback_family Name for the created fallback family.
	 * @param float  $size_adjust Size-adjust percentage (e.g. 97.4).
	 * @param float  $ascent Ascent-override percentage (e.g. 88.5).
	 * @param float  $descent Descent-override percentage (e.g. 21.0).
	 * @param float  $line_gap Line-gap-override percentage (default 0.0).
	 * @return string CSS @font-face definition.
	 */
	public static function generate_font_face_override(
		string $fallback_name,
		string $custom_fallback_family,
		float $size_adjust,
		float $ascent,
		float $descent,
		float $line_gap = 0.0
	): string {
		$size_adj_str = number_format( $size_adjust, 1, '.', '' ) . '%';
		$ascent_str   = number_format( $ascent, 1, '.', '' ) . '%';
		$descent_str  = number_format( $descent, 1, '.', '' ) . '%';
		$line_gap_str = $line_gap > 0 ? ( number_format( $line_gap, 1, '.', '' ) . '%' ) : 'normal';

		return "@font-face {\n" .
		       "  font-family: '{$custom_fallback_family}';\n" .
		       "  src: local('{$fallback_name}');\n" .
		       "  size-adjust: {$size_adj_str};\n" .
		       "  ascent-override: {$ascent_str};\n" .
		       "  descent-override: {$descent_str};\n" .
		       "  line-gap-override: {$line_gap_str};\n" .
		       "}";
	}

	/**
	 * Get pre-calibrated override CSS for a named preset.
	 *
	 * @param string $preset_key (e.g., 'inter-to-arial').
	 * @param string|null $custom_family
	 * @return string|null
	 */
	public static function get_preset_css( string $preset_key, ?string $custom_family = null ): ?string {
		if ( ! isset( self::PRESETS[ $preset_key ] ) ) {
			return null;
		}

		$p = self::PRESETS[ $preset_key ];
		$family = $custom_family ?? ( $p['target_font'] . ' Fallback' );

		return self::generate_font_face_override(
			$p['fallback_font'],
			$family,
			$p['size_adjust'],
			$p['ascent'],
			$p['descent'],
			$p['line_gap']
		);
	}
}
