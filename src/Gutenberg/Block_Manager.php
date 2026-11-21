<?php
declare(strict_types=1);

namespace ArnoldWender\ModularScale\Gutenberg;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArnoldWender\ModularScale\Plugin;

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
	 * Shared editor script handle for all 7 WM Modular Scale blocks.
	 */
	public const EDITOR_HANDLE = 'wm-modularscale-blocks-editor';

	/**
	 * Initialize block manager hooks
	 */
	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register_blocks' ] );
		// The block editor canvas is an iframe that WordPress fills from enqueue_block_assets. On
		// enqueue_block_editor_assets the token sheet landed in the admin page around it, so inside the
		// canvas --wm-step-* was empty and a Fluid Heading showed the theme's size (measured 2026-11-21).
		add_action( 'enqueue_block_assets', [ __CLASS__, 'enqueue_editor_tokens' ] );
		add_filter( 'block_categories_all', [ __CLASS__, 'register_block_category' ], 10, 2 );
	}

	/**
	 * Register compiled Gutenberg editor script and JED translations.
	 */
	public static function register_editor_assets(): void {
		if ( ! function_exists( 'wp_register_script' ) ) {
			return;
		}

		$plugin_dir  = dirname( __DIR__, 2 );
		$plugin_file = $plugin_dir . '/wm-modularscale.php';
		$asset_file  = $plugin_dir . '/build/index.asset.php';

		$asset = file_exists( $asset_file )
			? require $asset_file
			: [
				'dependencies' => [ 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-server-side-render' ],
				'version'      => Plugin::VERSION,
			];

		wp_register_script(
			self::EDITOR_HANDLE,
			plugins_url( 'build/index.js', $plugin_file ),
			$asset['dependencies'],
			(string) $asset['version'],
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( self::EDITOR_HANDLE, 'wm-modularscale', $plugin_dir . '/languages' );
		}

		if ( function_exists( 'wp_add_inline_script' ) ) {
			wp_add_inline_script(
				self::EDITOR_HANDLE,
				'window.wmModularScaleEditorConfig = ' . wp_json_encode( Plugin::scale_options() ) . ';',
				'before'
			);
		}
	}

	/**
	 * Enqueue compiled fluid CSS tokens inside the Gutenberg editor canvas.
	 *
	 * enqueue_block_assets also fires on the front end, where Plugin prints the tokens only while
	 * "apply to the site" is on; without the admin check this sheet would print them regardless.
	 */
	public static function enqueue_editor_tokens(): void {
		if ( ! function_exists( 'wp_register_style' ) || ! is_admin() ) {
			return;
		}

		wp_register_style( 'wm-modularscale-editor-tokens', false, [], Plugin::VERSION );
		wp_enqueue_style( 'wm-modularscale-editor-tokens' );
		wp_add_inline_style( 'wm-modularscale-editor-tokens', Plugin::instance()->get_compiled_css() );
	}

	/**
	 * Register all 7 Gutenberg blocks from block.json metadata
	 */
	public static function register_blocks(): void {
		self::register_editor_assets();

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
