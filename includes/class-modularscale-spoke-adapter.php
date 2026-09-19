<?php
/**
 * Wender Media Modular Scale — Suite Hub Spoke Adapter
 *
 * Implements the Wender Media Suite Hub contract (WM_Plugin_Module_Interface)
 * for centralized telemetry, typography design tokens, and modular scaling ratios.
 *
 * @package WMModularScale
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'WenderMedia\SuiteHub\Contracts\WM_Plugin_Module_Interface' ) ) {
	return;
}

use WenderMedia\SuiteHub\Contracts\WM_Plugin_Module_Interface;

class ModularScale_Spoke_Adapter implements WM_Plugin_Module_Interface {

	public function get_module_slug(): string {
		return 'wm-modularscale';
	}

	public function get_name(): string {
		return 'WM Modular Scale';
	}

	public function get_version(): string {
		return '1.2.0';
	}

	public function get_description(): string {
		return 'Harmonious, responsive typography modular scaling ratios & CSS design tokens.';
	}

	public function get_health_status(): string {
		return 'HEALTHY';
	}

	public function get_sbom_data(): array {
		return [
			'bom-ref'     => 'pkg:wordpress/wm-modularscale@' . $this->get_version(),
			'type'        => 'application',
			'name'        => $this->get_name(),
			'version'     => $this->get_version(),
			'description' => $this->get_description(),
			'scope'       => 'required',
			'hashes'      => [
				[
					'alg'     => 'SHA-256',
					'content' => hash_file( 'sha256', dirname( __DIR__ ) . '/wm-modularscale.php' ),
				],
			],
			'licenses'    => [
				[
					'license' => [
						'id'   => 'Proprietary',
						'name' => 'Proprietary Wender Media Commercial License',
					],
				],
			],
			'properties'  => [
				[
					'name'  => 'wm:category',
					'value' => 'Typography & Design System',
				],
				[
					'name'  => 'wm:tokens:CSS',
					'value' => 'Modular Scale Design Tokens (--wmmsp-*)',
				],
			],
		];
	}

	public function get_dependencies(): array {
		return [];
	}

	public function get_settings_url(): ?string {
		return admin_url( 'admin.php?page=wm-modular-scale' );
	}

	public function get_telemetry_metrics(): array {
		$base      = (float) get_option( 'wmmsp_base_size', 16 );
		$ratio     = (float) get_option( 'wmmsp_ratio', 1.25 );
		$unit      = (string) get_option( 'wmmsp_unit', 'px' );
		$precision = (int) get_option( 'wmmsp_precision', 2 );
		$applied   = (bool) get_option( 'wmmsp_apply_typography', 1 ); // The default the token output uses.

		return [
			'base_size'          => sprintf( '%.2f%s', $base, $unit ),
			'scale_ratio'        => sprintf( '%.4f', $ratio ),
			'typography_applied' => $applied ? 'YES' : 'NO',
			'token_h1'           => sprintf( '%.2f%s', round( $base * pow( $ratio, 4 ), $precision ), $unit ),
			'token_h2'           => sprintf( '%.2f%s', round( $base * pow( $ratio, 3 ), $precision ), $unit ),
			'token_h3'           => sprintf( '%.2f%s', round( $base * pow( $ratio, 2 ), $precision ), $unit ),
		];
	}

	public function execute_action( string $action, array $params = [] ): array {
		switch ( $action ) {
			case 'self_test':
			case 'calculate_scale':
				$base      = (float) ( $params['base'] ?? get_option( 'wmmsp_base_size', 16 ) );
				$ratio     = (float) ( $params['ratio'] ?? get_option( 'wmmsp_ratio', 1.25 ) );
				$unit      = (string) ( $params['unit'] ?? get_option( 'wmmsp_unit', 'px' ) );
				$precision = (int) ( $params['precision'] ?? get_option( 'wmmsp_precision', 2 ) );

				$h1 = round( $base * pow( $ratio, 4 ), $precision );
				$h2 = round( $base * pow( $ratio, 3 ), $precision );
				$h3 = round( $base * pow( $ratio, 2 ), $precision );

				return [
					'success' => true,
					'message' => sprintf( 'Modular Scale berechnet: Base %.2f%s, Ratio %.3f -> H1: %.2f%s, H2: %.2f%s, H3: %.2f%s', $base, $unit, $ratio, $h1, $unit, $h2, $unit, $h3, $unit ),
				];

			default:
				return [
					'success' => false,
					'message' => sprintf( 'Unbekannte Spoke-Aktion "%s".', esc_html( $action ) ),
				];
		}
	}
}
