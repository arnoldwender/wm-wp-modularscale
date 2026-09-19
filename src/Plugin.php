<?php
declare(strict_types=1);

namespace WenderMedia\ModularScale;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WenderMedia\ModularScale\Admin\Settings_Controller;
use WenderMedia\ModularScale\Gutenberg\Block_Manager;
use WenderMedia\ModularScale\Integration\GeneratePress_Bridge;
use WenderMedia\ModularScale\Integration\GenerateBlocks_Bridge;
use WenderMedia\ModularScale\Integration\Theme_Json_Bridge;
use WenderMedia\ModularScale\Tokens\CSS_Token_Generator;

/**
 * Class Plugin
 *
 * Master Singleton orchestrator for WM Modular Scale.
 */
final class Plugin {

	/**
	 * Plugin version
	 */
	public const VERSION = '1.2.0';

	/**
	 * Singleton instance
	 */
	private static ?Plugin $instance = null;

	/**
	 * Get Singleton instance
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor
	 */
	private function __construct() {
		$this->bootstrap();
	}

	/**
	 * Bootstrap all subsystem bridges and hooks
	 */
	private function bootstrap(): void {
		// 1. Initialize Gutenberg Blocks
		Block_Manager::init();

		// 2. Initialize Integration Bridges
		GeneratePress_Bridge::init();
		GenerateBlocks_Bridge::init();
		Theme_Json_Bridge::init();

		// 3. Initialize Admin Settings & Ajax
		if ( is_admin() ) {
			Settings_Controller::init();
		}

		// 4. Frontend CSS Injection
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_tokens' ], 20 );
		add_action( 'wp_head', [ $this, 'inject_header_tokens' ], 5 );
	}

	/**
	 * Enqueue front-end token stylesheet
	 */
	public function enqueue_frontend_tokens(): void {
		if ( ! (bool) get_option( 'wmmsp_apply_typography', 1 ) ) {
			return;
		}

		wp_register_style( 'wm-modularscale-tokens', false, [], self::VERSION );
		wp_enqueue_style( 'wm-modularscale-tokens' );

		$css = $this->get_compiled_css();
		wp_add_inline_style( 'wm-modularscale-tokens', $css );
	}

	/**
	 * Inject CSS tokens in wp_head for early critical rendering and zero CLS
	 */
	public function inject_header_tokens(): void {
		if ( ! (bool) get_option( 'wmmsp_apply_typography', 1 ) ) {
			return;
		}

		$css = $this->get_compiled_css();
		$hash = CSS_Token_Generator::compute_hash( $css );

		echo "\n<!-- WM Modular Scale Critical Tokens [hash: " . esc_attr( substr( $hash, 0, 12 ) ) . "] -->\n";
		// A CSS token sheet built from numeric options and a preset name: HTML escaping would break
		// it, stripping tags is what keeps a stray "</style>" out of the head.
		echo "<style id=\"wm-modularscale-critical\">\n" . wp_strip_all_tags( $css ) . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * The scale as stored, with usable values where the stored ones are not.
	 *
	 * Until 2026-09-15 the settings group also carried the options the page has no field for, and
	 * options.php stores null for every missing field: one save turned both viewports and the maximum
	 * base size into 0 and the font preset into ''. The tokens came out as
	 * `clamp(1rem, 1rem - 1600vw, 0rem)` (measured on a local WordPress install), a maximum below the minimum, so no
	 * step was fluid. Stored installs keep those zeros; this is where they stop mattering.
	 *
	 * @return array{base_min: float, base_max: float, ratio: float, min_vp: float, max_vp: float, font_preset: string}
	 */
	public static function scale_options(): array {
		$number = static function ( string $option, float $default ): float {
			$value = (float) get_option( $option, $default );
			return $value > 0 ? $value : $default;
		};

		$base_min = $number( 'wmmsp_base_size', 16.0 );
		$base_max = $number( 'wmmsp_base_size_max', 18.0 );
		$ratio    = max( 1.0, $number( 'wmmsp_ratio', 1.25 ) );
		$min_vp   = $number( 'wmmsp_min_viewport', 360.0 );
		$max_vp   = $number( 'wmmsp_max_viewport', 1440.0 );

		// The maximum has no field: a base size above it would invert every clamp(). Keep the
		// default proportion (18 / 16) instead.
		if ( $base_max < $base_min ) {
			$base_max = $base_min * 1.125;
		}
		if ( $max_vp <= $min_vp ) {
			$min_vp = 360.0;
			$max_vp = 1440.0;
		}

		$font_preset = sanitize_text_field( (string) get_option( 'wmmsp_font_override_preset', 'inter-to-arial' ) );

		return [
			'base_min'    => $base_min,
			'base_max'    => $base_max,
			'ratio'       => $ratio,
			'min_vp'      => $min_vp,
			'max_vp'      => $max_vp,
			'font_preset' => '' !== $font_preset ? $font_preset : 'inter-to-arial',
		];
	}

	/**
	 * Helper: get compiled CSS from options
	 *
	 * @return string
	 */
	public function get_compiled_css(): string {
		$scale = self::scale_options();

		return CSS_Token_Generator::generate_stylesheet(
			$scale['base_min'],
			$scale['base_max'],
			$scale['ratio'],
			$scale['min_vp'],
			$scale['max_vp'],
			true,
			$scale['font_preset']
		);
	}
}
