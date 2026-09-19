<?php
declare(strict_types=1);

namespace WenderMedia\ModularScale\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Scale_Engine
 *
 * Mathematical engine for calculating harmonic modular scales, multi-strand progressions,
 * linear interpolation slopes for CSS clamp() (viewport and container queries),
 * inverse vertical rhythm, and APCA perceptual contrast scoring.
 */
class Scale_Engine {

	/**
	 * Standard harmonic ratios
	 */
	public const RATIOS = [
		'minor-second'     => 1.067, // Captions / Metadata / Data tables
		'major-second'     => 1.125, // Body / UI standard
		'minor-third'      => 1.200, // Body / Editorial
		'major-third'      => 1.250, // Display / Sub-Hero
		'perfect-fourth'   => 1.333, // Display Hero / Impact
		'augmented-fourth' => 1.414, // Augmented Fourth (ISO DIN ratio)
		'perfect-fifth'    => 1.500, // Perfect Fifth
		'golden-ratio'     => 1.618, // Golden Ratio (phi)
	];

	/**
	 * Multi-strand defaults
	 */
	public const STRAND_DEFAULTS = [
		'display' => [
			'ratio'     => 1.333,
			'base_min'  => 16.0,
			'base_max'  => 18.0,
			'min_step'  => 0,
			'max_step'  => 6,
		],
		'body' => [
			'ratio'     => 1.125,
			'base_min'  => 16.0,
			'base_max'  => 18.0,
			'min_step'  => -1,
			'max_step'  => 3,
		],
		'caption' => [
			'ratio'     => 1.067,
			'base_min'  => 14.0,
			'base_max'  => 14.0,
			'min_step'  => -2,
			'max_step'  => 1,
		],
	];

	/**
	 * Calculate a single step value in pixels/rem for a given base and ratio.
	 *
	 * @param float $base Base font size in px.
	 * @param float $ratio Scale ratio multiplier.
	 * @param int   $step Step index (positive for headings, negative for captions).
	 * @return float
	 */
	public static function calculate_step_value( float $base, float $ratio, int $step ): float {
		return $base * pow( $ratio, $step );
	}

	/**
	 * Derive the linear interpolation clamp() formula.
	 *
	 * Formula:
	 * Slope = (MaxSize - MinSize) / (MaxViewport - MinViewport)
	 * Y-Intercept = MinSize - (Slope * MinViewport)
	 * Preferred = Y-Intercept(rem) + (Slope * 100)vw (or cqi)
	 *
	 * @param float  $min_size_px Minimum size in px at minimum viewport.
	 * @param float  $max_size_px Maximum size in px at maximum viewport.
	 * @param float  $min_viewport_px Minimum viewport width in px (e.g. 360).
	 * @param float  $max_viewport_px Maximum viewport width in px (e.g. 1440).
	 * @param string $unit_type 'vw' for viewport queries or 'cqi' for container queries.
	 * @param float  $root_font_size Root rem size in px (default 16.0).
	 * @return array{clamp: string, min_rem: string, max_rem: string, slope_pct: float, y_intercept_rem: float, zoom_safe: bool}
	 */
	public static function derive_clamp(
		float $min_size_px,
		float $max_size_px,
		float $min_viewport_px = 360.0,
		float $max_viewport_px = 1440.0,
		string $unit_type = 'vw',
		float $root_font_size = 16.0
	): array {
		if ( $max_viewport_px <= $min_viewport_px ) {
			$max_viewport_px = $min_viewport_px + 1.0;
		}

		$slope = ( $max_size_px - $min_size_px ) / ( $max_viewport_px - $min_viewport_px );
		$y_intercept_px = $min_size_px - ( $slope * $min_viewport_px );
		$y_intercept_rem = $y_intercept_px / $root_font_size;
		$slope_pct = $slope * 100.0;

		$min_rem = round( $min_size_px / $root_font_size, 4 );
		$max_rem = round( $max_size_px / $root_font_size, 4 );
		$y_intercept_rem_rounded = round( $y_intercept_rem, 4 );
		$slope_pct_rounded = round( $slope_pct, 4 );

		// Sign for preferred expression
		$sign = $slope_pct_rounded >= 0 ? '+' : '-';
		$abs_slope = abs( $slope_pct_rounded );

		$unit = in_array( $unit_type, [ 'vw', 'cqi', 'cqw' ], true ) ? $unit_type : 'vw';
		$preferred = "{$y_intercept_rem_rounded}rem {$sign} {$abs_slope}{$unit}";
		$clamp = "clamp({$min_rem}rem, {$preferred}, {$max_rem}rem)";

		// WCAG 1.4.4 / BFSG 2025 Zoom Safety check (MaxSize <= 2.5 * MinSize)
		$zoom_safe = ( $min_size_px > 0 ) ? ( ( $max_size_px / $min_size_px ) <= 2.501 ) : true;

		return [
			'clamp'           => $clamp,
			'min_rem'         => "{$min_rem}rem",
			'max_rem'         => "{$max_rem}rem",
			'slope_pct'       => $slope_pct_rounded,
			'y_intercept_rem' => $y_intercept_rem_rounded,
			'zoom_safe'       => $zoom_safe,
		];
	}

	/**
	 * Calculate inverse line-height (as font size increases, line-height decreases).
	 *
	 * Body (16px) -> 1.55-1.6
	 * Sub-hero (32px) -> 1.3
	 * Hero (64px+) -> 1.1-1.15
	 *
	 * @param float $font_size_px Computed font size in px.
	 * @return float
	 */
	public static function calculate_inverse_line_height( float $font_size_px ): float {
		if ( $font_size_px <= 16.0 ) {
			return 1.6;
		}
		if ( $font_size_px >= 64.0 ) {
			return 1.1;
		}

		// Smooth exponential / linear decay between 16px (1.6) and 64px (1.1)
		$t = ( $font_size_px - 16.0 ) / ( 64.0 - 16.0 );
		$lh = 1.6 - ( $t * 0.5 );
		return round( $lh, 3 );
	}

	/**
	 * Compute full multi-strand scale matrix.
	 *
	 * @param float $min_vp Minimum viewport in px (default 360).
	 * @param float $max_vp Maximum viewport in px (default 1440).
	 * @param array<string, array{ratio: float, base_min: float, base_max: float}> $custom_strands
	 * @return array<string, array<int, array{step: int, min_px: float, max_px: float, clamp: string, line_height: float, zoom_safe: bool}>>
	 */
	public static function generate_multi_strand_matrix(
		float $min_vp = 360.0,
		float $max_vp = 1440.0,
		array $custom_strands = []
	): array {
		$strands = array_merge( self::STRAND_DEFAULTS, $custom_strands );
		$matrix  = [];

		foreach ( $strands as $strand_name => $config ) {
			$ratio     = (float) ( $config['ratio'] ?? 1.25 );
			$base_min  = (float) ( $config['base_min'] ?? 16.0 );
			$base_max  = (float) ( $config['base_max'] ?? 18.0 );
			$min_step  = (int) ( $config['min_step'] ?? -2 );
			$max_step  = (int) ( $config['max_step'] ?? 6 );

			$matrix[ $strand_name ] = [];

			for ( $s = $min_step; $s <= $max_step; $s++ ) {
				$min_px = self::calculate_step_value( $base_min, $ratio, $s );
				$max_px = self::calculate_step_value( $base_max, $ratio, $s );

				$clamp_data = self::derive_clamp( $min_px, $max_px, $min_vp, $max_vp );
				$lh = self::calculate_inverse_line_height( $max_px );

				$matrix[ $strand_name ][ $s ] = [
					'step'        => $s,
					'min_px'      => round( $min_px, 2 ),
					'max_px'      => round( $max_px, 2 ),
					'clamp'       => $clamp_data['clamp'],
					'line_height' => $lh,
					'zoom_safe'   => $clamp_data['zoom_safe'],
				];
			}
		}

		return $matrix;
	}

	/**
	 * APCA lightness contrast (Lc), as apca-w3 0.1.9 (`APCAcontrast()` with the SA98G constants) computes it.
	 *
	 * APCA is not part of WCAG: it was removed from the WCAG 3 working draft in 2023. Its own readability
	 * criterion (Bronze simple mode) asks for Lc 75 on body text (90 preferred), Lc 60 on other content text
	 * and Lc 45 on large text such as headlines.
	 *
	 * Until 2026-09-15 near-black colours were clamped hard at Y 0.0005 instead of the reference soft clamp
	 * (threshold 0.022, exponent 1.414), and the deltaYmin check was missing: black on white scored 109.8
	 * instead of 106.0, and dark backgrounds up to 4 Lc too high.
	 *
	 * @param string $text_hex Text color (#ffffff, #111111).
	 * @param string $bg_hex Background color (#000000, #f8fafc).
	 * @return float Signed Lc value (-108.0 to +106.0). Positive = dark text on light bg; Negative = light text on dark bg.
	 */
	public static function calculate_apca_lc( string $text_hex, string $bg_hex ): float {
		$txt_rgb = self::hex_to_rgb( $text_hex );
		$bg_rgb  = self::hex_to_rgb( $bg_hex );

		// Compute sRGB to Y (luminance) using standard coefficients
		$txt_y = ( 0.2126729 * pow( $txt_rgb[0] / 255.0, 2.4 ) ) +
		         ( 0.7151522 * pow( $txt_rgb[1] / 255.0, 2.4 ) ) +
		         ( 0.0721750 * pow( $txt_rgb[2] / 255.0, 2.4 ) );

		$bg_y  = ( 0.2126729 * pow( $bg_rgb[0] / 255.0, 2.4 ) ) +
		         ( 0.7151522 * pow( $bg_rgb[1] / 255.0, 2.4 ) ) +
		         ( 0.0721750 * pow( $bg_rgb[2] / 255.0, 2.4 ) );

		// Black soft clamp (blkThrs 0.022, blkClmp 1.414) and the early zero for a tiny luminance difference.
		$txt_y = $txt_y > 0.022 ? $txt_y : $txt_y + pow( 0.022 - $txt_y, 1.414 );
		$bg_y  = $bg_y > 0.022 ? $bg_y : $bg_y + pow( 0.022 - $bg_y, 1.414 );
		if ( abs( $bg_y - $txt_y ) < 0.0005 ) {
			return 0.0;
		}

		// Contrast exponent
		if ( $bg_y > $txt_y ) {
			// Dark text on light background
			$sapc = ( pow( $bg_y, 0.56 ) - pow( $txt_y, 0.57 ) ) * 1.14;
			$output = $sapc < 0.1 ? 0.0 : ( ( $sapc - 0.027 ) * 100.0 );
		} else {
			// Light text on dark background
			$sapc = ( pow( $bg_y, 0.65 ) - pow( $txt_y, 0.62 ) ) * 1.14;
			$output = $sapc > -0.1 ? 0.0 : ( ( $sapc + 0.027 ) * 100.0 );
		}

		return round( $output, 1 );
	}

	/**
	 * Helper: Convert 3 or 6 hex string to RGB array [r, g, b].
	 *
	 * @param string $hex
	 * @return array{0: int, 1: int, 2: int}
	 */
	public static function hex_to_rgb( string $hex ): array {
		$hex = ltrim( $hex, '#' );
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( strlen( $hex ) !== 6 ) {
			return [ 0, 0, 0 ];
		}
		return [
			(int) hexdec( substr( $hex, 0, 2 ) ),
			(int) hexdec( substr( $hex, 2, 2 ) ),
			(int) hexdec( substr( $hex, 4, 2 ) ),
		];
	}
}
