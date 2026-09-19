<?php
declare(strict_types=1);

namespace WenderMedia\ModularScale\Gutenberg;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Block_Manager
 *
 * Registers the 7 enterprise Gutenberg typography blocks with Block API v3 and block.json metadata:
 * 1. wm-scale/fluid-heading
 * 2. wm-scale/fluid-lead
 * 3. wm-scale/fluid-container
 * 4. wm-scale/typographic-grid
 * 5. wm-scale/ratio-visualizer
 * 6. wm-scale/contrast-matrix-badge
 * 7. wm-scale/theme-json-exporter
 */
class Block_Manager {

	/**
	 * List of all 7 modular scale block slugs
	 */
	public const BLOCKS = [
		'fluid-heading',
		'fluid-lead',
		'fluid-container',
		'typographic-grid',
		'ratio-visualizer',
		'contrast-matrix-badge',
		'theme-json-exporter',
	];

	/**
	 * Initialize block manager hooks
	 */
	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register_blocks' ] );
		add_filter( 'block_categories_all', [ __CLASS__, 'register_block_category' ], 10, 2 );
	}

	/**
	 * Register all 7 Gutenberg blocks from block.json metadata
	 */
	public static function register_blocks(): void {
		$base_dir = dirname( __DIR__ ) . '/Gutenberg/Blocks';

		foreach ( self::BLOCKS as $slug ) {
			$block_path = $base_dir . '/' . $slug;
			if ( file_exists( $block_path . '/block.json' ) ) {
				register_block_type_from_metadata( $block_path );
			}
		}
	}

	/**
	 * Register wm-scale block category in Gutenberg
	 *
	 * @param array<int, array{slug: string, title: string, icon?: string}> $categories
	 * @param \WP_Block_Editor_Context $context
	 * @return array<int, array{slug: string, title: string, icon?: string}>
	 */
	public static function register_block_category( array $categories, $context ): array {
		array_unshift(
			$categories,
			[
				'slug'  => 'wm-scale',
				'title' => __( 'WM Modular Scale — Typografie Enterprise', 'wm-modularscale' ),
				'icon'  => 'editor-customchar',
			]
		);

		return $categories;
	}
}
