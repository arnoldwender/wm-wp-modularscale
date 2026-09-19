<?php
declare(strict_types=1);

namespace WenderMedia\ModularScale\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WenderMedia\ModularScale\Core\Scale_Engine;
use WenderMedia\ModularScale\Core\Font_Metric_Matcher;
use WenderMedia\ModularScale\Plugin;
use WenderMedia\ModularScale\Tokens\CSS_Token_Generator;

/**
 * Class Settings_Controller
 *
 * Manages admin menu, settings registration, live interactive Multi-Strand sandbox,
 * and token regeneration with SHA-256 caching.
 */
class Settings_Controller {

	/**
	 * Initialize admin hooks
	 */
	public static function init(): void {
		add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
		add_action( 'wp_ajax_wmmsp_calculate_scale', [ __CLASS__, 'ajax_calculate_scale' ] );
	}

	/**
	 * Register all plugin settings.
	 *
	 * `wmmsp_settings_group` is what the settings page posts, so it holds exactly the options the page
	 * has a field for: options.php stores null for every option of the posted group whose field is
	 * missing. The options without a field sit in `wmmsp_scale_advanced_group`, which no page posts.
	 * Until 2026-09-15 the page's group carried both, and the main plugin file registered the page
	 * options a second time with other defaults.
	 */
	public static function register_settings(): void {
		register_setting( 'wmmsp_settings_group', 'wmmsp_base_size', [
			'type'              => 'number',
			'sanitize_callback' => 'floatval',
			'default'           => 16.0,
		] );

		register_setting( 'wmmsp_settings_group', 'wmmsp_ratio', [
			'type'              => 'number',
			'sanitize_callback' => 'floatval',
			'default'           => 1.25,
		] );

		register_setting( 'wmmsp_settings_group', 'wmmsp_unit', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'px',
		] );

		register_setting( 'wmmsp_settings_group', 'wmmsp_precision', [
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 2,
		] );

		register_setting( 'wmmsp_settings_group', 'wmmsp_apply_typography', [
			'type'              => 'boolean',
			'sanitize_callback' => 'boolval',
			'default'           => 1,
		] );

		register_setting( 'wmmsp_scale_advanced_group', 'wmmsp_base_size_max', [
			'type'              => 'number',
			'sanitize_callback' => 'floatval',
			'default'           => 18.0,
		] );

		register_setting( 'wmmsp_scale_advanced_group', 'wmmsp_ratio_name', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'major-third',
		] );

		register_setting( 'wmmsp_scale_advanced_group', 'wmmsp_min_viewport', [
			'type'              => 'number',
			'sanitize_callback' => 'floatval',
			'default'           => 360.0,
		] );

		register_setting( 'wmmsp_scale_advanced_group', 'wmmsp_max_viewport', [
			'type'              => 'number',
			'sanitize_callback' => 'floatval',
			'default'           => 1440.0,
		] );

		register_setting( 'wmmsp_scale_advanced_group', 'wmmsp_font_override_preset', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'inter-to-arial',
		] );
	}

	/**
	 * Enqueue the family sheets, the plugin's admin layer and the sandbox script.
	 *
	 * Standalone the screens are toplevel_page_wm-modular-scale and
	 * settings_page_wm-modular-scale-options; under the hub the page is dispatched as
	 * wender-media_page_wm-modular-scale. index.php carries the dashboard widget. Until
	 * 2026-09-14 this enqueued the frontend typography sheet from src/ (a 404 on every load).
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public static function enqueue_admin_assets( string $hook ): void {
		$is_plugin_screen = false !== strpos( $hook, 'wm-modular-scale' );
		$is_dashboard     = 'index.php' === $hook;

		if ( ! $is_plugin_screen && ! $is_dashboard ) {
			return;
		}

		$plugin_file = dirname( __DIR__, 2 ) . '/wm-modularscale.php';
		$plugin_dir  = dirname( $plugin_file );

		// Versioned by file time: a sheet updated under an unchanged plugin version was served
		// from the browser cache (measured on another spoke, 2026-09-14).
		$version = static function ( string $relative ) use ( $plugin_dir ): string {
			$file = $plugin_dir . '/' . $relative;
			return file_exists( $file ) ? (string) filemtime( $file ) : Plugin::VERSION;
		};

		// Every WM plugin registers the same two handles, so WordPress loads one copy however
		// many plugins are active.
		wp_enqueue_style( 'wm-admin-tokens', plugins_url( 'assets/css/wm-admin-tokens.css', $plugin_file ), [], $version( 'assets/css/wm-admin-tokens.css' ) );
		wp_enqueue_style( 'wm-admin-ui', plugins_url( 'assets/css/wm-admin-ui.css', $plugin_file ), [ 'wm-admin-tokens' ], $version( 'assets/css/wm-admin-ui.css' ) );
		wp_enqueue_style( 'wmmsp-admin', plugins_url( 'assets/css/modularscale-admin.css', $plugin_file ), [ 'wm-admin-ui', 'dashicons' ], $version( 'assets/css/modularscale-admin.css' ) );

		if ( $is_plugin_screen ) {
			wp_enqueue_script( 'wmmsp-admin', plugins_url( 'assets/js/modularscale-admin.js', $plugin_file ), [], $version( 'assets/js/modularscale-admin.js' ), true );
		}
	}

	/**
	 * AJAX endpoint for live scale calculations in admin
	 */
	public static function ajax_calculate_scale(): void {
		check_ajax_referer( 'wmmsp_calc_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Forbidden' ], 403 );
		}

		// Numeric fields: unslash + text sanitize before the float cast (the cast alone was the
		// sanitizer, which phpcs does not recognise).
		$read_float = static function ( string $key, float $default ): float {
			if ( ! isset( $_POST[ $key ] ) ) {
				return $default;
			}
			return (float) sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
		};

		$base_min = $read_float( 'base_min', 16.0 );
		$base_max = $read_float( 'base_max', 18.0 );
		$ratio    = $read_float( 'ratio', 1.25 );
		$min_vp   = $read_float( 'min_vp', 360.0 );
		$max_vp   = $read_float( 'max_vp', 1440.0 );

		$matrix = Scale_Engine::generate_multi_strand_matrix( $min_vp, $max_vp );

		wp_send_json_success( [
			'matrix' => $matrix,
			'css'    => CSS_Token_Generator::generate_stylesheet( $base_min, $base_max, $ratio, $min_vp, $max_vp ),
		] );
	}
}
