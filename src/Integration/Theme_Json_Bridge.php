<?php
declare(strict_types=1);

namespace WenderMedia\ModularScale\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WenderMedia\ModularScale\Core\Scale_Engine;
use WenderMedia\ModularScale\Plugin;

/**
 * Class Theme_Json_Bridge
 *
 * Filters wp_theme_json_data_theme (one way: theme.json on disk is never written):
 * - replaces settings.typography.fontSizes with the nine fluid steps of the stored scale;
 * - sets defaultFontSizes to false so the core presets are not offered next to them.
 */
class Theme_Json_Bridge {

	/**
	 * Initialize theme.json bridge filter
	 */
	public static function init(): void {
		add_filter( 'wp_theme_json_data_theme', [ __CLASS__, 'filter_theme_json' ], 20, 1 );
	}

	/**
	 * Filter theme.json data object dynamically
	 *
	 * @param \WP_Theme_JSON_Data $theme_json
	 * @return \WP_Theme_JSON_Data
	 */
	public static function filter_theme_json( $theme_json ) {
		if ( ! is_object( $theme_json ) || ! method_exists( $theme_json, 'get_data' ) || ! method_exists( $theme_json, 'update_with' ) ) {
			return $theme_json;
		}

		$scale      = Plugin::scale_options();
		$font_sizes = self::build_font_sizes_schema( $scale['base_min'], $scale['base_max'], $scale['ratio'] );

		$custom_data = [
			'version'  => 3,
			'settings' => [
				'typography' => [
					'defaultFontSizes' => false,
					'fluid'            => true,
					'fontSizes'        => $font_sizes,
				],
			],
		];

		$theme_json->update_with( $custom_data );

		return $theme_json;
	}

	/**
	 * Build fontSizes array schema for theme.json v3
	 *
	 * @param float $base_min
	 * @param float $base_max
	 * @param float $ratio
	 * @return array<int, array{name: string, slug: string, size: string, fluid: array{min: string, max: string}}>
	 */
	public static function build_font_sizes_schema( float $base_min = 16.0, float $base_max = 18.0, float $ratio = 1.25 ): array {
		$step_labels = [
			-2 => 'Micro Caption (Step -2)',
			-1 => 'Caption (Step -1)',
			0  => 'Body Base (Step 0)',
			1  => 'Heading 6 (Step 1)',
			2  => 'Heading 5 (Step 2)',
			3  => 'Heading 4 (Step 3)',
			4  => 'Heading 3 (Step 4)',
			5  => 'Heading 2 (Step 5)',
			6  => 'Heading 1 (Step 6)',
		];

		$schema = [];

		foreach ( $step_labels as $step => $label ) {
			$min_px = Scale_Engine::calculate_step_value( $base_min, $ratio, $step );
			$max_px = Scale_Engine::calculate_step_value( $base_max, $ratio, $step );

			$clamp_data = Scale_Engine::derive_clamp( $min_px, $max_px );
			$slug = $step < 0 ? ( 'wm-step-neg-' . abs( $step ) ) : ( 'wm-step-' . $step );

			$schema[] = [
				'name'  => $label,
				'slug'  => $slug,
				'size'  => $clamp_data['clamp'],
				'fluid' => [
					'min' => $clamp_data['min_rem'],
					'max' => $clamp_data['max_rem'],
				],
			];
		}

		return $schema;
	}
}
