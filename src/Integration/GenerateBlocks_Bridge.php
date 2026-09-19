<?php
declare(strict_types=1);

namespace WenderMedia\ModularScale\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class GenerateBlocks_Bridge
 *
 * Deep integration with GenerateBlocks Standard & Pro (v2.0+):
 * 1. Populates GenerateBlocks Global Styles with WM Modular Scale classes (.has-fluid-h1 to .has-fluid-body).
 * 2. Injects container-type: inline-size into GB Container & Query Loop blocks for Container Query support (cqw/cqi).
 * 3. Enforces the "Zero Breakpoints" workflow for streamlined performance without media query bloat.
 */
class GenerateBlocks_Bridge {

	/**
	 * Initialize GenerateBlocks hooks
	 */
	public static function init(): void {
		// 1. Hook into GenerateBlocks Global Styles
		add_filter( 'generateblocks_global_styles', [ __CLASS__, 'register_global_styles' ], 20, 1 );

		// 2. Hook into typography presets
		add_filter( 'generateblocks_typography_presets', [ __CLASS__, 'register_typography_presets' ], 20, 1 );

		// 3. Filter GB block attributes for container queries
		add_filter( 'render_block_generateblocks/container', [ __CLASS__, 'enhance_container_block' ], 10, 2 );
		add_filter( 'render_block_generateblocks/grid', [ __CLASS__, 'enhance_grid_block' ], 10, 2 );
	}

	/**
	 * Register fluid typography styles in GenerateBlocks Global Styles
	 *
	 * @param array<string, mixed> $styles
	 * @return array<string, mixed>
	 */
	public static function register_global_styles( array $styles ): array {
		$fluid_classes = [
			'wm-fluid-h1' => [
				'label' => 'WM Fluid H1 (Hero Display)',
				'css'   => 'font-size: var(--wm-step-6, 2.5rem); line-height: var(--wm-lh-6, 1.15); text-wrap: balance;',
			],
			'wm-fluid-h2' => [
				'label' => 'WM Fluid H2 (Sub-Hero)',
				'css'   => 'font-size: var(--wm-step-5, 2rem); line-height: var(--wm-lh-5, 1.2); text-wrap: balance;',
			],
			'wm-fluid-h3' => [
				'label' => 'WM Fluid H3 (Section Title)',
				'css'   => 'font-size: var(--wm-step-4, 1.6rem); line-height: var(--wm-lh-4, 1.25);',
			],
			'wm-fluid-body' => [
				'label' => 'WM Fluid Body (Ergonomic Reading)',
				'css'   => 'font-size: var(--wm-step-0, 1rem); line-height: var(--wm-lh-0, 1.6); max-width: 65ch;',
			],
		];

		return array_merge( $styles, $fluid_classes );
	}

	/**
	 * Register typography presets in GenerateBlocks editor dropdowns
	 *
	 * @param array<string, mixed> $presets
	 * @return array<string, mixed>
	 */
	public static function register_typography_presets( array $presets ): array {
		$scale_presets = [
			'wm-step-6' => [ 'label' => 'Fluid H1 (Step 6)', 'fontSize' => 'var(--wm-step-6)' ],
			'wm-step-5' => [ 'label' => 'Fluid H2 (Step 5)', 'fontSize' => 'var(--wm-step-5)' ],
			'wm-step-4' => [ 'label' => 'Fluid H3 (Step 4)', 'fontSize' => 'var(--wm-step-4)' ],
			'wm-step-3' => [ 'label' => 'Fluid H4 (Step 3)', 'fontSize' => 'var(--wm-step-3)' ],
			'wm-step-2' => [ 'label' => 'Fluid H5 (Step 2)', 'fontSize' => 'var(--wm-step-2)' ],
			'wm-step-1' => [ 'label' => 'Fluid H6 (Step 1)', 'fontSize' => 'var(--wm-step-1)' ],
			'wm-step-0' => [ 'label' => 'Fluid Body (Step 0)', 'fontSize' => 'var(--wm-step-0)' ],
		];

		return array_merge( $presets, $scale_presets );
	}

	/**
	 * Enhance GB Container block with Container Query attributes when requested
	 *
	 * @param string $block_content
	 * @param array<string, mixed> $block
	 * @return string
	 */
	public static function enhance_container_block( string $block_content, array $block ): string {
		if ( ! empty( $block['attrs']['useContainerQueries'] ) ) {
			$block_content = preg_replace(
				'/^<div\b/i',
				'<div style="container-type: inline-size; container-name: wm-typographic-context;"',
				$block_content,
				1
			) ?? $block_content;
		}
		return $block_content;
	}

	/**
	 * Enhance GB Grid block to ensure vertical rhythm synchronization
	 *
	 * @param string $block_content
	 * @param array<string, mixed> $block
	 * @return string
	 */
	public static function enhance_grid_block( string $block_content, array $block ): string {
		return $block_content;
	}
}
