<?php
declare(strict_types=1);

namespace WenderMedia\ModularScale\Tokens;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WenderMedia\ModularScale\Core\Scale_Engine;
use WenderMedia\ModularScale\Core\Font_Metric_Matcher;

/**
 * Class CSS_Token_Generator
 *
 * Compiles and generates modern CSS custom properties (:root), type-safe @property definitions,
 * Tailwind v4 @theme export, and cached inline styles with SHA-256 fingerprinting.
 */
class CSS_Token_Generator {

	/**
	 * Generate full CSS token stylesheet.
	 *
	 * @param float $base_min Base font size min in px (default 16.0).
	 * @param float $base_max Base font size max in px (default 18.0).
	 * @param float $ratio Scale ratio multiplier (default 1.25).
	 * @param float $min_vp Min viewport width in px (default 360.0).
	 * @param float $max_vp Max viewport width in px (default 1440.0).
	 * @param bool  $enable_at_property Whether to emit CSS @property rules (default true).
	 * @param string|null $font_override_preset Optional font fallback preset key (e.g. 'inter-to-arial').
	 * @return string Complete CSS string.
	 */
	public static function generate_stylesheet(
		float $base_min = 16.0,
		float $base_max = 18.0,
		float $ratio = 1.25,
		float $min_vp = 360.0,
		float $max_vp = 1440.0,
		bool $enable_at_property = true,
		?string $font_override_preset = null
	): string {
		$css = "/* === WM Modular Scale Enterprise CSS Tokens (2026) === */\n";

		// 1. Optional Fallback Font Metrics Override
		if ( $font_override_preset ) {
			$override_css = Font_Metric_Matcher::get_preset_css( $font_override_preset );
			if ( $override_css ) {
				$css .= "/* Anti-CLS Fallback Overrides */\n" . $override_css . "\n\n";
			}
		}

		// 2. CSS @property Type-Safety Definitions
		if ( $enable_at_property ) {
			$css .= "/* CSS Properties and Values API (Type Safety) */\n";
			for ( $s = -2; $s <= 6; $s++ ) {
				$var_name = self::get_step_var_name( $s );
				$css .= "@property {$var_name} {\n  syntax: '<length>';\n  inherits: true;\n  initial-value: 16px;\n}\n";
			}
			$css .= "\n";
		}

		// 3. :root Custom Properties & Fluid Steps
		$css .= ":root {\n";
		$css .= "  --wm-scale-ratio: {$ratio};\n";
		$css .= "  --wm-min-viewport: {$min_vp}px;\n";
		$css .= "  --wm-max-viewport: {$max_vp}px;\n";

		// Steps from -2 (micro-caption) to +6 (hero display)
		for ( $s = -2; $s <= 6; $s++ ) {
			$min_px = Scale_Engine::calculate_step_value( $base_min, $ratio, $s );
			$max_px = Scale_Engine::calculate_step_value( $base_max, $ratio, $s );

			$clamp_data = Scale_Engine::derive_clamp( $min_px, $max_px, $min_vp, $max_vp, 'vw' );
			$lh = Scale_Engine::calculate_inverse_line_height( $max_px );
			$var_name = self::get_step_var_name( $s );
			$lh_name  = self::get_lh_var_name( $s );

			$css .= "  {$var_name}: {$clamp_data['clamp']};\n";
			$css .= "  {$lh_name}: {$lh};\n";
		}

		// Alias mappings for standard HTML tags & WordPress Core
		$css .= "\n  /* Semantic Tag & Core Mappings */\n";
		$css .= "  --wm-font-size-body: var(--wm-step-0);\n";
		$css .= "  --wm-font-size-h6: var(--wm-step-1);\n";
		$css .= "  --wm-font-size-h5: var(--wm-step-2);\n";
		$css .= "  --wm-font-size-h4: var(--wm-step-3);\n";
		$css .= "  --wm-font-size-h3: var(--wm-step-4);\n";
		$css .= "  --wm-font-size-h2: var(--wm-step-5);\n";
		$css .= "  --wm-font-size-h1: var(--wm-step-6);\n";

		// GeneratePress Compatibility Variables
		$css .= "\n  /* GeneratePress Compatibility Layer */\n";
		$css .= "  --gp-font-size-body: var(--wm-step-0);\n";
		$css .= "  --gp-font-size-h1: var(--wm-step-6);\n";
		$css .= "  --gp-font-size-h2: var(--wm-step-5);\n";
		$css .= "  --gp-font-size-h3: var(--wm-step-4);\n";
		$css .= "  --gp-font-size-h4: var(--wm-step-3);\n";
		$css .= "  --gp-font-size-h5: var(--wm-step-2);\n";
		$css .= "  --gp-font-size-h6: var(--wm-step-1);\n";

		$css .= "}\n\n";

		// 4. Utility Classes
		$css .= "/* Utility & Gutenberg Classes */\n";
		$css .= ".has-fluid-body-font-size { font-size: var(--wm-step-0) !important; line-height: var(--wm-lh-0); }\n";
		$css .= ".has-fluid-h6-font-size { font-size: var(--wm-step-1) !important; line-height: var(--wm-lh-1); }\n";
		$css .= ".has-fluid-h5-font-size { font-size: var(--wm-step-2) !important; line-height: var(--wm-lh-2); }\n";
		$css .= ".has-fluid-h4-font-size { font-size: var(--wm-step-3) !important; line-height: var(--wm-lh-3); }\n";
		$css .= ".has-fluid-h3-font-size { font-size: var(--wm-step-4) !important; line-height: var(--wm-lh-4); }\n";
		$css .= ".has-fluid-h2-font-size { font-size: var(--wm-step-5) !important; line-height: var(--wm-lh-5); text-wrap: balance; }\n";
		$css .= ".has-fluid-h1-font-size { font-size: var(--wm-step-6) !important; line-height: var(--wm-lh-6); text-wrap: balance; }\n";

		// Container Query Utility
		$css .= "\n/* Container Query Typographic Context */\n";
		$css .= ".wm-fluid-container {\n";
		$css .= "  container-type: inline-size;\n";
		$css .= "  container-name: wm-typographic-context;\n";
		$css .= "}\n";

		return $css;
	}

	/**
	 * Compute SHA-256 hash for stylesheet fingerprinting and cache validation.
	 *
	 * @param string $css
	 * @return string 64-character SHA-256 hash.
	 */
	public static function compute_hash( string $css ): string {
		return hash( 'sha256', $css );
	}

	/**
	 * Export tokens formatted for Tailwind CSS v4 @theme directive.
	 *
	 * @param float $base_min
	 * @param float $base_max
	 * @param float $ratio
	 * @return string
	 */
	public static function export_tailwind_v4_theme( float $base_min = 16.0, float $base_max = 18.0, float $ratio = 1.25 ): string {
		$out = "@theme {\n";
		for ( $s = -2; $s <= 6; $s++ ) {
			$min_px = Scale_Engine::calculate_step_value( $base_min, $ratio, $s );
			$max_px = Scale_Engine::calculate_step_value( $base_max, $ratio, $s );
			$clamp_data = Scale_Engine::derive_clamp( $min_px, $max_px );
			$step_suffix = $s < 0 ? ( 'neg-' . abs( $s ) ) : (string) $s;
			$out .= "  --text-wm-step-{$step_suffix}: {$clamp_data['clamp']};\n";
		}
		$out .= "}\n";
		return $out;
	}

	/**
	 * Helper: format step variable name.
	 *
	 * @param int $step
	 * @return string (e.g., '--wm-step-neg-1', '--wm-step-0', '--wm-step-5')
	 */
	public static function get_step_var_name( int $step ): string {
		if ( $step < 0 ) {
			return '--wm-step-neg-' . abs( $step );
		}
		return '--wm-step-' . $step;
	}

	/**
	 * Helper: format line-height variable name.
	 *
	 * @param int $step
	 * @return string (e.g., '--wm-lh-neg-1', '--wm-lh-0', '--wm-lh-5')
	 */
	public static function get_lh_var_name( int $step ): string {
		if ( $step < 0 ) {
			return '--wm-lh-neg-' . abs( $step );
		}
		return '--wm-lh-' . $step;
	}
}
