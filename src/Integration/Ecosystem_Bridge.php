<?php
declare(strict_types=1);

namespace ArnoldWender\ModularScale\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArnoldWender\ModularScale\Core\Scale_Engine;
use ArnoldWender\ModularScale\Plugin;

/**
 * Class Ecosystem_Bridge
 *
 * Bridges WM Modular Scale with:
 * 1. WordPress 6.5+ Block Bindings API (`wm-modularscale/tokens`).
 * 2. GenerateBlocks 2.x Dynamic Tags (`{{wm_scale_token}}` via `GenerateBlocks_Register_Dynamic_Tag`).
 * 3. Core & GenerateBlocks 2.x Block Patterns (`wm-modularscale/typographic-specimen`, `wm-modularscale/gb-fluid-hero`).
 * 4. WordPress 6.9+ Declarative Abilities API (`wm-modularscale/compute-scale`).
 * 5. Lifecycle action `wm_modularscale_ready` for third-party plugins.
 */
final class Ecosystem_Bridge {

	/**
	 * Initialize ecosystem hooks.
	 */
	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register_block_bindings' ], 20 );
		add_action( 'init', [ __CLASS__, 'register_generateblocks_dynamic_tags' ], 20 );
		add_action( 'init', [ __CLASS__, 'register_patterns' ], 25 );
		add_action( 'init', [ __CLASS__, 'signal_ready' ], 30 );
		add_action( 'wp_abilities_api_init', [ __CLASS__, 'register_abilities' ] );
	}

	/**
	 * Fire lifecycle action so companion plugins know scale bridges are active.
	 */
	public static function signal_ready(): void {
		if ( function_exists( 'do_action' ) ) {
			do_action( 'wm_modularscale_ready', Plugin::VERSION );
		}
	}

	/**
	 * Register WordPress 6.5+ Block Bindings source (`wm-modularscale/tokens`).
	 */
	public static function register_block_bindings(): void {
		if ( ! function_exists( 'register_block_bindings_source' ) ) {
			return;
		}

		register_block_bindings_source(
			'wm-modularscale/tokens',
			[
				'label'              => __( 'WM Modular Scale Tokens', 'wm-modularscale' ),
				'get_value_callback' => [ __CLASS__, 'resolve_token_value' ],
				'uses_context'       => [ 'postId', 'postType' ],
			]
		);
	}

	/**
	 * Resolve a computed scale token for Block Bindings and GenerateBlocks 2.x Dynamic Tags.
	 *
	 * @param array<string, mixed> $source_args Arguments (`key`, `step`, `field`).
	 * @param mixed                $block       Optional WP_Block instance.
	 * @return string
	 */
	public static function resolve_token_value( array $source_args, $block = null ): string {
		$scale = Plugin::scale_options();
		$key   = isset( $source_args['key'] ) ? sanitize_key( (string) $source_args['key'] ) : '';

		if ( 'ratio' === $key ) {
			return (string) round( $scale['ratio'], 3 );
		}
		if ( 'base_min' === $key || 'base' === $key ) {
			return round( $scale['base_min'], 2 ) . 'px';
		}
		if ( 'base_max' === $key ) {
			return round( $scale['base_max'], 2 ) . 'px';
		}

		$heading_map = [
			'h1'   => 6,
			'h2'   => 5,
			'h3'   => 4,
			'h4'   => 3,
			'h5'   => 2,
			'h6'   => 1,
			'body' => 0,
		];

		$step = 0;
		if ( isset( $heading_map[ $key ] ) ) {
			$step = $heading_map[ $key ];
		} elseif ( preg_match( '/^step_neg_([12])$/', $key, $m ) ) {
			$step = -(int) $m[1];
		} elseif ( preg_match( '/^step_([0-6])$/', $key, $m ) ) {
			$step = (int) $m[1];
		} elseif ( isset( $source_args['step'] ) ) {
			$step = max( -2, min( 6, (int) $source_args['step'] ) );
		}

		$min_px     = Scale_Engine::calculate_step_value( $scale['base_min'], $scale['ratio'], $step );
		$max_px     = Scale_Engine::calculate_step_value( $scale['base_max'], $scale['ratio'], $step );
		$clamp_data = Scale_Engine::derive_clamp( $min_px, $max_px, $scale['min_vp'], $scale['max_vp'] );
		$field      = isset( $source_args['field'] ) ? sanitize_key( (string) $source_args['field'] ) : 'clamp';

		if ( 'min_px' === $field ) {
			return round( $min_px, 1 ) . 'px';
		}
		if ( 'max_px' === $field ) {
			return round( $max_px, 1 ) . 'px';
		}
		if ( 'range' === $field ) {
			return round( $min_px, 1 ) . 'px – ' . round( $max_px, 1 ) . 'px';
		}

		return esc_html( $clamp_data['clamp'] );
	}

	/**
	 * Register GenerateBlocks 2.x Dynamic Tag (`{{wm_scale_token}}`) when GenerateBlocks 2.0+ is active.
	 */
	public static function register_generateblocks_dynamic_tags(): void {
		if ( ! class_exists( 'GenerateBlocks_Register_Dynamic_Tag' ) ) {
			return;
		}

		new \GenerateBlocks_Register_Dynamic_Tag(
			[
				'title'       => __( 'WM Modular Scale Token', 'wm-modularscale' ),
				'tag'         => 'wm_scale_token',
				'type'        => 'wm-suite',
				'supports'    => [ 'source' ],
				'description' => __( 'Outputs a computed fluid clamp() token or px range from WM Modular Scale.', 'wm-modularscale' ),
				'options'     => [
					'step'  => [
						'type'    => 'number',
						'label'   => __( 'Scale step (-2 to 6)', 'wm-modularscale' ),
						'default' => 0,
					],
					'field' => [
						'type'    => 'select',
						'label'   => __( 'Output format', 'wm-modularscale' ),
						'default' => 'clamp',
						'options' => [
							[
								'value' => 'clamp',
								'label' => __( 'CSS clamp() expression', 'wm-modularscale' ),
							],
							[
								'value' => 'range',
								'label' => __( 'Min–Max px range', 'wm-modularscale' ),
							],
							[
								'value' => 'max_px',
								'label' => __( 'Maximum px size', 'wm-modularscale' ),
							],
						],
					],
				],
				'return'      => [ __CLASS__, 'render_generateblocks_tag' ],
			]
		);
	}

	/**
	 * Callback for GenerateBlocks 2.x Dynamic Tag rendering.
	 *
	 * @param array<string, mixed> $options  Tag options passed by GenerateBlocks.
	 * @param array<string, mixed> $block    Parsed block array.
	 * @param mixed                $instance WP_Block instance.
	 * @return string
	 */
	public static function render_generateblocks_tag( array $options, array $block = [], $instance = null ): string {
		return self::resolve_token_value(
			[
				'step'  => isset( $options['step'] ) ? (int) $options['step'] : 0,
				'field' => isset( $options['field'] ) ? (string) $options['field'] : 'clamp',
			],
			$instance
		);
	}

	/**
	 * Register Block Patterns for Core Gutenberg and GenerateBlocks 2.x (`gbp-section`).
	 */
	public static function register_patterns(): void {
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		if ( function_exists( 'register_block_pattern_category' ) ) {
			register_block_pattern_category(
				'wm-modularscale',
				[
					'label' => __( 'WM Modular Scale', 'wm-modularscale' ),
				]
			);
		}

		register_block_pattern(
			'wm-modularscale/typographic-specimen',
			[
				'title'       => __( 'Fluid Typographic Hero & Lead', 'wm-modularscale' ),
				'description' => __( 'Container-query section pairing a Fluid Heading with an ergonomic Fluid Lead paragraph.', 'wm-modularscale' ),
				'categories'  => [ 'wm-modularscale' ],
				'content'     => '<!-- wp:wm-scale/fluid-container {"align":"wide"} -->'
					. '<!-- wp:wm-scale/fluid-heading {"level":1,"step":6,"content":"' . esc_attr__( 'Harmonische Typografie im Browser', 'wm-modularscale' ) . '"} /-->'
					. '<!-- wp:wm-scale/fluid-lead {"measureLimit":65,"content":"' . esc_attr__( 'Fließende Schriftgrößen zwischen Mobilgerät und Desktop, berechnet aus Basisgröße und musikalischem Intervall.', 'wm-modularscale' ) . '"} /-->'
					. '<!-- /wp:wm-scale/fluid-container -->',
			]
		);

		if ( defined( 'GENERATEBLOCKS_VERSION' ) ) {
			register_block_pattern(
				'wm-modularscale/gb-fluid-hero',
				[
					'title'       => __( 'GenerateBlocks Fluid Scale Section', 'wm-modularscale' ),
					'description' => __( 'Two-layer GenerateBlocks section (gbp-section + gbp-section__inner) consuming --ms-* and --wm-step-* fluid tokens.', 'wm-modularscale' ),
					'categories'  => [ 'wm-modularscale' ],
					'content'     => '<!-- wp:generateblocks/element {"tagName":"section","className":"gbp-section","styles":{"paddingTop":"var(--ms-4, 2rem)","paddingBottom":"var(--ms-4, 2rem)"}} -->'
						. '<section class="gbp-section">'
						. '<!-- wp:generateblocks/element {"tagName":"div","className":"gbp-section__inner","styles":{"maxWidth":"80rem","marginLeft":"auto","marginRight":"auto"}} -->'
						. '<div class="gbp-section__inner">'
						. '<!-- wp:generateblocks/text {"tagName":"h1","styles":{"fontSize":"var(--ms-6, var(--wm-step-6))","lineHeight":"var(--wm-lh-6, 1.15)"}} -->'
						. '<h1>' . esc_html__( 'Modulare Skala mit GenerateBlocks', 'wm-modularscale' ) . '</h1>'
						. '<!-- /wp:generateblocks/text -->'
						. '<!-- wp:generateblocks/text {"tagName":"p","styles":{"fontSize":"var(--ms-0, var(--wm-step-0))","maxWidth":"65ch"}} -->'
						. '<p>' . esc_html__( 'Direkte Nutzung der berechneten CSS-Variablen ohne Media-Query-Stufen.', 'wm-modularscale' ) . '</p>'
						. '<!-- /wp:generateblocks/text -->'
						. '</div>'
						. '<!-- /wp:generateblocks/element -->'
						. '</section>'
						. '<!-- /wp:generateblocks/element -->',
				]
			);
		}
	}

	/**
	 * Register declarative WordPress 6.9+ Ability (`wm-modularscale/compute-scale`).
	 */
	public static function register_abilities(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wm-modularscale/compute-scale',
			[
				'label'               => __( 'Compute Modular Typography Scale', 'wm-modularscale' ),
				'description'         => __( 'Calculates the nine fluid clamp() steps (-2 to +6), line heights and WCAG 1.4.4 zoom-safety flags for a base size and ratio.', 'wm-modularscale' ),
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'base_min' => [ 'type' => 'number', 'minimum' => 8, 'maximum' => 64 ],
						'base_max' => [ 'type' => 'number', 'minimum' => 8, 'maximum' => 96 ],
						'ratio'    => [ 'type' => 'number', 'minimum' => 1.0, 'maximum' => 3.0 ],
						'min_vp'   => [ 'type' => 'number', 'minimum' => 240, 'maximum' => 1920 ],
						'max_vp'   => [ 'type' => 'number', 'minimum' => 320, 'maximum' => 3840 ],
					],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'ratio' => [ 'type' => 'number' ],
						'steps' => [ 'type' => 'array' ],
					],
				],
				'execute_callback'    => [ __CLASS__, 'execute_compute_scale_ability' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Execute the `wm-modularscale/compute-scale` ability.
	 *
	 * @param mixed $input Optional input parameters.
	 * @return array{ratio: float, base_min: float, base_max: float, steps: array<int, array<string, mixed>>}
	 */
	public static function execute_compute_scale_ability( $input = [] ): array {
		$defaults = Plugin::scale_options();
		$args     = is_array( $input ) ? $input : [];

		$base_min = isset( $args['base_min'] ) && (float) $args['base_min'] > 0 ? (float) $args['base_min'] : $defaults['base_min'];
		$base_max = isset( $args['base_max'] ) && (float) $args['base_max'] >= $base_min ? (float) $args['base_max'] : max( $base_min, $defaults['base_max'] );
		$ratio    = isset( $args['ratio'] ) && (float) $args['ratio'] >= 1.0 ? (float) $args['ratio'] : $defaults['ratio'];
		$min_vp   = isset( $args['min_vp'] ) && (float) $args['min_vp'] > 0 ? (float) $args['min_vp'] : $defaults['min_vp'];
		$max_vp   = isset( $args['max_vp'] ) && (float) $args['max_vp'] > $min_vp ? (float) $args['max_vp'] : max( $min_vp + 1.0, $defaults['max_vp'] );

		$steps = [];
		for ( $s = -2; $s <= 6; $s++ ) {
			$min_px     = Scale_Engine::calculate_step_value( $base_min, $ratio, $s );
			$max_px     = Scale_Engine::calculate_step_value( $base_max, $ratio, $s );
			$clamp_data = Scale_Engine::derive_clamp( $min_px, $max_px, $min_vp, $max_vp );
			$steps[]    = [
				'step'        => $s,
				'token'       => $s < 0 ? ( '--wm-step-neg-' . abs( $s ) ) : ( '--wm-step-' . $s ),
				'ms_alias'    => $s < 0 ? ( '--ms-neg-' . abs( $s ) ) : ( '--ms-' . $s ),
				'min_px'      => round( $min_px, 2 ),
				'max_px'      => round( $max_px, 2 ),
				'clamp'       => $clamp_data['clamp'],
				'line_height' => Scale_Engine::calculate_inverse_line_height( $max_px ),
				'zoom_safe'   => $clamp_data['zoom_safe'],
			];
		}

		return [
			'ratio'    => $ratio,
			'base_min' => $base_min,
			'base_max' => $base_max,
			'steps'    => $steps,
		];
	}
}
