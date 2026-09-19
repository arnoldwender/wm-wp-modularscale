<?php
declare(strict_types=1);

namespace WenderMedia\ModularScale\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class GeneratePress_Bridge
 *
 * Deep integration with GeneratePress (Free & Premium):
 * 1. Suppresses external Google Fonts requests (GDPR / DSGVO compliance & LG München ruling).
 * 2. Dequeues static Google font assets.
 * 3. Injects fluid typography tokens (--gp-font-size-*) directly into GeneratePress CSS output.
 * 4. Exposes local @fontsource / WOFF2 typography presets in the theme Font Manager.
 */
class GeneratePress_Bridge {

	/**
	 * Initialize GeneratePress bridge hooks
	 */
	public static function init(): void {
		// 1. Suppress Google Fonts array in Customizer & Theme Core
		add_action( 'admin_init', [ __CLASS__, 'suppress_google_fonts' ] );
		add_filter( 'generate_google_fonts_array', '__return_empty_array', 999 );
		add_filter( 'generate_font_manager_show_google_fonts', '__return_false', 999 );

		// 2. Dequeue external font stylesheets
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'dequeue_external_fonts' ], 100 );

		// 3. Inject fluid variables into GeneratePress dynamic CSS
		add_filter( 'generate_typography_css', [ __CLASS__, 'inject_fluid_typography_css' ], 20, 1 );

		// 4. Expose system & local fonts to GP Font Manager
		add_filter( 'generate_typography_default_fonts', [ __CLASS__, 'register_local_fonts' ] );
		add_filter( 'generate_font_manager_system_fonts', [ __CLASS__, 'register_local_fonts' ] );
	}

	/**
	 * Suppress Google Fonts in admin context
	 */
	public static function suppress_google_fonts(): void {
		add_filter( 'generate_google_fonts_array', '__return_empty_array', 999 );
	}

	/**
	 * Dequeue external font styles from GP
	 */
	public static function dequeue_external_fonts(): void {
		wp_dequeue_style( 'generate-fonts' );
		wp_deregister_style( 'generate-fonts' );
	}

	/**
	 * Filter GeneratePress dynamic typography CSS to inject fluid tokens
	 *
	 * @param string $css
	 * @return string
	 */
	public static function inject_fluid_typography_css( string $css ): string {
		$fluid_patch = "
			/* Injected by WM Modular Scale GeneratePress Bridge */
			:root {
				--gp-font-size-body: var(--wm-step-0, 1rem);
				--gp-font-size-h1: var(--wm-step-6, 2.5rem);
				--gp-font-size-h2: var(--wm-step-5, 2rem);
				--gp-font-size-h3: var(--wm-step-4, 1.6rem);
				--gp-font-size-h4: var(--wm-step-3, 1.35rem);
				--gp-font-size-h5: var(--wm-step-2, 1.15rem);
				--gp-font-size-h6: var(--wm-step-1, 1.05rem);
			}
			h1 { font-size: var(--gp-font-size-h1) !important; text-wrap: balance; }
			h2 { font-size: var(--gp-font-size-h2) !important; text-wrap: balance; }
			h3 { font-size: var(--gp-font-size-h3) !important; }
			h4 { font-size: var(--gp-font-size-h4) !important; }
			h5 { font-size: var(--gp-font-size-h5) !important; }
			h6 { font-size: var(--gp-font-size-h6) !important; }
		";

		return $css . "\n" . $fluid_patch;
	}

	/**
	 * Register local self-hosted fonts in GeneratePress typography manager
	 *
	 * @param array<int|string, string> $fonts
	 * @return array<int|string, string>
	 */
	public static function register_local_fonts( array $fonts ): array {
		$local_fonts = [
			'Inter'            => 'Inter (Local WOFF2)',
			'Outfit'           => 'Outfit (Local WOFF2)',
			'Cinzel'           => 'Cinzel (Local WOFF2)',
			'Cabinet Grotesk'  => 'Cabinet Grotesk (Local WOFF2)',
			'System Default'   => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif',
		];

		return array_merge( $fonts, $local_fonts );
	}
}
