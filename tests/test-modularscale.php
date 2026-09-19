<?php
declare(strict_types=1);

namespace WenderMedia\SuiteHub\Contracts {
	if ( ! interface_exists( 'WenderMedia\SuiteHub\Contracts\WM_Plugin_Module_Interface' ) ) {
		interface WM_Plugin_Module_Interface {
			public function get_module_slug(): string;
			public function get_name(): string;
			public function get_version(): string;
			public function get_description(): string;
			public function get_health_status(): string;
			public function get_sbom_data(): array;
			public function get_dependencies(): array;
			public function get_settings_url(): ?string;
			public function get_telemetry_metrics(): array;
			public function execute_action( string $action, array $params = [] ): array;
		}
	}
}

namespace {

// Mock WordPress environment functions if not loaded
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		return is_string( $str ) ? trim( strip_tags( $str ) ) : '';
	}
}

if ( ! function_exists( 'sanitize_hex_color' ) ) {
	function sanitize_hex_color( $color ) {
		if ( '' === $color ) {
			return '';
		}
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
			return $color;
		}
		return null;
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $str ) {
		return $str;
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $str ) {
		return htmlspecialchars( (string) $str, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $str ) {
		return htmlspecialchars( (string) $str, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, $options = 0 ) {
		return json_encode( $data, $options );
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $option, $default = false ) {
		// Section 13 stores values the way a settings save left them on a local WordPress install (2026-09-15).
		if ( isset( $GLOBALS['wmmsp_option_overrides'] ) && array_key_exists( $option, $GLOBALS['wmmsp_option_overrides'] ) ) {
			return $GLOBALS['wmmsp_option_overrides'][ $option ];
		}
		$options = [
			'wmmsp_base_size'     => 16.0,
			'wmmsp_base_size_max' => 18.0,
			'wmmsp_ratio'         => 1.25,
			'wmmsp_unit'          => 'px',
			'wmmsp_precision'     => 2,
			'wmmsp_apply_typography' => 1,
			'wmmsp_min_viewport'  => 360.0,
			'wmmsp_max_viewport'  => 1440.0,
			'wmmsp_font_override_preset' => 'inter-to-arial',
		];
		return $options[ $option ] ?? $default;
	}
}

if ( ! function_exists( 'admin_url' ) ) {
	function admin_url( $path = '' ) {
		return 'https://example.com/wp-admin/' . $path;
	}
}

// Register PSR-4 autoloader
spl_autoload_register( function ( string $class_name ): void {
	$prefix = 'WenderMedia\\ModularScale\\';
	if ( strpos( $class_name, $prefix ) !== 0 ) {
		return;
	}
	$relative_class = substr( $class_name, strlen( $prefix ) );
	$file           = dirname( __DIR__ ) . '/src/' . str_replace( '\\', '/', $relative_class ) . '.php';
	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

// Test Runner Infrastructure
class Test_Runner {
	private int $passed = 0;
	private int $failed = 0;
	private array $errors = [];

	public function assert( bool $condition, string $test_name, string $message = '' ): void {
		if ( $condition ) {
			$this->passed++;
			echo "\033[32m[PASS]\033[0m {$test_name}\n";
		} else {
			$this->failed++;
			$err = "\033[31m[FAIL]\033[0m {$test_name}" . ( $message ? " — {$message}" : '' );
			$this->errors[] = $err;
			echo "{$err}\n";
		}
	}

	public function report(): int {
		echo "\n" . str_repeat( '=', 60 ) . "\n";
		echo " WM MODULAR SCALE TEST RESULTS: {$this->passed} PASSED | {$this->failed} FAILED\n";
		echo str_repeat( '=', 60 ) . "\n\n";

		if ( $this->failed > 0 ) {
			echo "Failed Tests Summary:\n";
			foreach ( $this->errors as $e ) {
				echo "  {$e}\n";
			}
			return 1;
		}
		return 0;
	}
}

$t = new Test_Runner();

echo "\n" . str_repeat( '=', 60 ) . "\n";
echo " WENDER MEDIA MODULAR SCALE — UNIT & INTEGRATION SUITE\n";
echo str_repeat( '=', 60 ) . "\n\n";

use WenderMedia\ModularScale\Core\Scale_Engine;
use WenderMedia\ModularScale\Core\Font_Metric_Matcher;
use WenderMedia\ModularScale\Tokens\CSS_Token_Generator;
use WenderMedia\ModularScale\Plugin;
use WenderMedia\ModularScale\Gutenberg\Block_Manager;
use WenderMedia\ModularScale\Integration\GeneratePress_Bridge;
use WenderMedia\ModularScale\Integration\GenerateBlocks_Bridge;
use WenderMedia\ModularScale\Integration\Theme_Json_Bridge;

// 1. Scale_Engine Tests
$t->assert(
	count( Scale_Engine::RATIOS ) === 8,
	'Scale_Engine defines all 8 standard harmonic ratios'
);

$t->assert(
	abs( Scale_Engine::RATIOS['golden-ratio'] - 1.618 ) < 0.001,
	'Scale_Engine golden ratio multiplier is 1.618'
);

$step_0 = Scale_Engine::calculate_step_value( 16.0, 1.25, 0 );
$step_1 = Scale_Engine::calculate_step_value( 16.0, 1.25, 1 );
$step_2 = Scale_Engine::calculate_step_value( 16.0, 1.25, 2 );
$step_neg_1 = Scale_Engine::calculate_step_value( 16.0, 1.25, -1 );

$t->assert(
	abs( $step_0 - 16.0 ) < 0.01 && abs( $step_1 - 20.0 ) < 0.01 && abs( $step_2 - 25.0 ) < 0.01 && abs( $step_neg_1 - 12.8 ) < 0.01,
	'Scale_Engine calculate_step_value computes accurate exponential powers'
);

// 2. Linear Clamp Derivation Tests
$clamp_res = Scale_Engine::derive_clamp( 16.0, 24.0, 360.0, 1440.0, 'vw', 16.0 );
$t->assert(
	strpos( $clamp_res['clamp'], 'clamp(1rem,' ) === 0 && strpos( $clamp_res['clamp'], '1.5rem)' ) !== false,
	'Scale_Engine derive_clamp creates valid CSS clamp() string'
);

$t->assert(
	$clamp_res['zoom_safe'] === true,
	'Scale_Engine derive_clamp validates WCAG 1.4.4 / BFSG 2025 zoom safety (MaxSize <= 2.5 * MinSize)'
);

$unsafe_clamp = Scale_Engine::derive_clamp( 10.0, 35.0, 360.0, 1440.0 );
$t->assert(
	$unsafe_clamp['zoom_safe'] === false,
	'Scale_Engine derive_clamp correctly detects unsafe zoom exceeding 2.5x threshold'
);

// 3. Inverse Line Height
$lh_16 = Scale_Engine::calculate_inverse_line_height( 16.0 );
$lh_64 = Scale_Engine::calculate_inverse_line_height( 64.0 );
$lh_32 = Scale_Engine::calculate_inverse_line_height( 32.0 );
$t->assert(
	$lh_16 === 1.6 && $lh_64 === 1.1 && ( $lh_32 > 1.1 && $lh_32 < 1.6 ),
	'Scale_Engine calculate_inverse_line_height decreases smoothly from 1.6 to 1.1'
);

// 4. Multi-Strand Matrix Generation
$matrix = Scale_Engine::generate_multi_strand_matrix( 360.0, 1440.0 );
$t->assert(
	isset( $matrix['display'], $matrix['body'], $matrix['caption'] ),
	'Scale_Engine generate_multi_strand_matrix generates all 3 independent strands'
);

$t->assert(
	count( $matrix['display'] ) === 7 && count( $matrix['body'] ) === 5 && count( $matrix['caption'] ) === 4,
	'Scale_Engine strands have correct step distributions'
);

// 5. APCA Contrast Scoring
$lc_dark_on_light = Scale_Engine::calculate_apca_lc( '#111111', '#ffffff' );
$lc_light_on_dark = Scale_Engine::calculate_apca_lc( '#ffffff', '#000000' );
$lc_low_contrast  = Scale_Engine::calculate_apca_lc( '#888888', '#999999' );

$t->assert(
	$lc_dark_on_light > 90.0,
	'Scale_Engine calculate_apca_lc scores dark on light text above Lc 90 (Optimal Body)'
);

$t->assert(
	abs( $lc_light_on_dark ) > 90.0,
	'Scale_Engine calculate_apca_lc scores white on black text above Lc 90'
);

$t->assert(
	abs( $lc_low_contrast ) < 30.0,
	'Scale_Engine calculate_apca_lc identifies inaccessible low contrast (< Lc 30)'
);

// 6. Font_Metric_Matcher Tests
$t->assert(
	count( Font_Metric_Matcher::PRESETS ) === 4,
	'Font_Metric_Matcher defines 4 pre-calibrated anti-CLS font presets'
);

$inter_override = Font_Metric_Matcher::get_preset_css( 'inter-to-arial' );
$t->assert(
	$inter_override !== null && strpos( $inter_override, 'size-adjust: 97.4%' ) !== false && strpos( $inter_override, 'ascent-override: 88.5%' ) !== false,
	'Font_Metric_Matcher get_preset_css generates valid @font-face size-adjust and ascent-override'
);

// 7. CSS_Token_Generator Tests
$stylesheet = CSS_Token_Generator::generate_stylesheet( 16.0, 18.0, 1.25, 360.0, 1440.0, true, 'inter-to-arial' );
$t->assert(
	strpos( $stylesheet, '@property --wm-step-0' ) !== false,
	'CSS_Token_Generator generates type-safe @property rules'
);

$t->assert(
	strpos( $stylesheet, '--wm-step-0:' ) !== false && strpos( $stylesheet, '--gp-font-size-h1:' ) !== false,
	'CSS_Token_Generator outputs :root variables and GeneratePress compatibility variables'
);

$t->assert(
	strpos( $stylesheet, '.has-fluid-h1-font-size' ) !== false && strpos( $stylesheet, '.wm-fluid-container' ) !== false,
	'CSS_Token_Generator outputs utility classes and container query context classes'
);

$hash = CSS_Token_Generator::compute_hash( $stylesheet );
$t->assert(
	strlen( $hash ) === 64 && ctype_xdigit( $hash ),
	'CSS_Token_Generator compute_hash returns valid 64-char SHA-256 fingerprint'
);

$tailwind_theme = CSS_Token_Generator::export_tailwind_v4_theme( 16.0, 18.0, 1.25 );
$t->assert(
	strpos( $tailwind_theme, '@theme {' ) === 0 && strpos( $tailwind_theme, '--text-wm-step-0:' ) !== false,
	'CSS_Token_Generator export_tailwind_v4_theme generates valid Tailwind CSS v4 syntax'
);

// 8. Gutenberg Block Manager & 7 Block Definitions
$t->assert(
	count( Block_Manager::BLOCKS ) === 7,
	'Block_Manager maintains exactly 7 enterprise Gutenberg blocks'
);

$blocks_dir = dirname( __DIR__ ) . '/src/Gutenberg/Blocks';

// The README named the blocks by their titles ("Fluid Heading") and never by the slug someone
// would search for, so all seven counted as undocumented surface. docs/BLOCKS.md carries the
// slugs, the attributes and — the part a user cannot discover otherwise — which five attributes
// the render reads and then never uses.
$blocks_doc = (string) @file_get_contents( dirname( __DIR__ ) . '/docs/BLOCKS.md' );
foreach ( Block_Manager::BLOCKS as $b_slug ) {
	$json_file = $blocks_dir . '/' . $b_slug . '/block.json';
	$json_name = is_file( $json_file )
		? (string) ( json_decode( (string) file_get_contents( $json_file ), true )['name'] ?? '' )
		: '';
	$t->assert(
		'' !== $json_name && str_contains( $blocks_doc, $json_name ),
		"Block {$json_name} is documented in docs/BLOCKS.md"
	);
}

foreach ( Block_Manager::BLOCKS as $b_slug ) {
	$json_file   = $blocks_dir . '/' . $b_slug . '/block.json';
	$render_file = $blocks_dir . '/' . $b_slug . '/render.php';

	$t->assert(
		file_exists( $json_file ),
		"Block {$b_slug} has block.json metadata file"
	);

	if ( file_exists( $json_file ) ) {
		$json_data = json_decode( file_get_contents( $json_file ), true );
		$t->assert(
			isset( $json_data['apiVersion'] ) && $json_data['apiVersion'] === 3,
			"Block {$b_slug} block.json specifies apiVersion 3"
		);
		$t->assert(
			isset( $json_data['name'] ) && strpos( $json_data['name'], 'wm-scale/' ) === 0,
			"Block {$b_slug} is under wm-scale namespace"
		);
	}

	$t->assert(
		file_exists( $render_file ),
		"Block {$b_slug} has SSR render.php file"
	);

	// Until 2026-09-14 a guard appended after the closing PHP tag printed three lines of PHP
	// source into every rendered block: whatever follows the last closing tag goes to the
	// browser as-is. (The tag itself is not written in this comment: inside a comment it would
	// end PHP mode, which is exactly what broke this file on the first try.)
	if ( file_exists( $render_file ) ) {
		$render_src = (string) file_get_contents( $render_file );
		$last_close = strrpos( $render_src, '?>' );
		$trailing   = false === $last_close ? '' : substr( $render_src, $last_close + 2 );
		$t->assert(
			false === strpos( $trailing, 'defined(' ) && false === strpos( $trailing, 'exit;' ),
			"Block {$b_slug} render.php prints no PHP source after its last closing tag"
		);
		$t->assert(
			1 === preg_match( '/^<\?php.*?defined\(\s*[\'"]ABSPATH[\'"]\s*\)/s', $render_src ),
			"Block {$b_slug} render.php carries its direct-access guard inside PHP"
		);
	}
}

// 9. Theme_Json_Bridge Tests
$theme_schema = Theme_Json_Bridge::build_font_sizes_schema( 16.0, 18.0, 1.25 );
$t->assert(
	count( $theme_schema ) === 9,
	'Theme_Json_Bridge build_font_sizes_schema builds 9 fluid font step definitions'
);

$t->assert(
	isset( $theme_schema[2]['slug'] ) && $theme_schema[2]['slug'] === 'wm-step-0' && isset( $theme_schema[2]['fluid'] ),
	'Theme_Json_Bridge step 0 has valid slug and fluid min/max bounds'
);

// 10. GeneratePress & GenerateBlocks Bridges Tests
$gp_local_fonts = GeneratePress_Bridge::register_local_fonts( [] );
$t->assert(
	isset( $gp_local_fonts['Inter'], $gp_local_fonts['Outfit'], $gp_local_fonts['Cabinet Grotesk'] ),
	'GeneratePress_Bridge registers local WOFF2 font families'
);

$gb_global_styles = GenerateBlocks_Bridge::register_global_styles( [] );
$t->assert(
	isset( $gb_global_styles['wm-fluid-h1'], $gb_global_styles['wm-fluid-body'] ),
	'GenerateBlocks_Bridge registers fluid classes in GenerateBlocks Global Styles'
);

$gb_presets = GenerateBlocks_Bridge::register_typography_presets( [] );
$t->assert(
	isset( $gb_presets['wm-step-6'], $gb_presets['wm-step-0'] ),
	'GenerateBlocks_Bridge registers scale presets in GenerateBlocks editor dropdowns'
);

// 11. Spoke Adapter Integration with wm-suite-hub
require_once dirname( __DIR__ ) . '/includes/class-modularscale-spoke-adapter.php';
$adapter = new ModularScale_Spoke_Adapter();
$t->assert(
	$adapter->get_module_slug() === 'wm-modularscale' && $adapter->get_name() === 'WM Modular Scale',
	'ModularScale_Spoke_Adapter identifies with slug wm-modularscale'
);

$health = $adapter->get_health_status();
$t->assert(
	$health === 'HEALTHY',
	'ModularScale_Spoke_Adapter health status is HEALTHY'
);

$telemetry = $adapter->get_telemetry_metrics();
$t->assert(
	isset( $telemetry['base_size'], $telemetry['scale_ratio'] ),
	'ModularScale_Spoke_Adapter extracts telemetry metrics for Suite Hub'
);

$action_res = $adapter->execute_action( 'self_test' );
$t->assert(
	isset( $action_res['success'] ) && $action_res['success'] === true,
	'ModularScale_Spoke_Adapter executes self_test action successfully'
);

// 12. Admin surface on the Wender Media family sheets (2026-09-14). Before: the settings page
// carried 270 lines of inline <style> with 49 hex colours, an inline <script> with onclick
// handlers, 22 emoji, and the admin enqueue pointed at src/wm-modularscale.css (a 404).
$plugin_root = dirname( __DIR__ );
$main_src    = (string) file_get_contents( $plugin_root . '/wm-modularscale.php' );
$ctrl_src    = (string) file_get_contents( $plugin_root . '/src/Admin/Settings_Controller.php' );

$t->assert(
	false === strpos( $main_src, '<style' ) && false === strpos( $main_src, '<script' ),
	'Main file renders no inline <style> or <script> block'
);

$t->assert(
	0 === preg_match( '/\son(click|input|change)=/', $main_src ),
	'Settings page carries no inline event handlers (data attributes + assets/js instead)'
);

$t->assert(
	0 === preg_match( '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $main_src ),
	'Admin strings carry no emoji (dashicons instead)'
);

$t->assert(
	0 === preg_match( '/style="[^"]*#[0-9a-fA-F]{3,6}/', $main_src ),
	'Admin markup carries no inline hex colours'
);

$t->assert(
	false !== strpos( $ctrl_src, 'assets/css/wm-admin-tokens.css' )
		&& false !== strpos( $ctrl_src, 'assets/css/wm-admin-ui.css' )
		&& false !== strpos( $ctrl_src, 'assets/css/modularscale-admin.css' )
		&& false !== strpos( $ctrl_src, 'assets/js/modularscale-admin.js' )
		&& false === strpos( $ctrl_src, "'wm-modularscale.css'" ),
	'Settings_Controller enqueues the family sheets, the admin layer and the sandbox script, not the frontend sheet'
);

foreach ( [ 'assets/css/wm-admin-tokens.css', 'assets/css/wm-admin-ui.css', 'assets/css/modularscale-admin.css', 'assets/js/modularscale-admin.js' ] as $asset ) {
	$t->assert(
		file_exists( $plugin_root . '/' . $asset ),
		"Admin asset {$asset} ships with the plugin"
	);
}

$layer_src = (string) file_get_contents( $plugin_root . '/assets/css/modularscale-admin.css' );
$t->assert(
	0 === preg_match( '/#[0-9a-fA-F]{3,6}\b/', $layer_src ) && 0 === preg_match( '/outline\s*:\s*(none|0)\b/', $layer_src ),
	'Admin layer uses family tokens only and suppresses no focus outline'
);

$front_src = (string) file_get_contents( $plugin_root . '/wm-modularscale.css' );
$t->assert(
	0 === preg_match( '/outline\s*:\s*none/', $front_src ) && false === strpos( $front_src, '!important' ),
	'Frontend sheet carries no admin UI block (no !important rules, no outline: none)'
);

$t->assert(
	0 === preg_match( '/--wmmsp-base-size:\s*1[0-5](\.\d+)?px/', $front_src ),
	'Frontend sheet never drops the base size below 16px on small viewports (BFSG 2025)'
);

// 13. Stored options the tokens can use (2026-09-15). The settings group carried options the page
// has no field for; options.php stores every option of the posted group and writes null for a missing
// field, so one save turned viewports and maximum base size into 0 and the preset names into ''.
// Measured on a local WordPress install: --wm-step-0: clamp(1rem, 1rem - 1600vw, 0rem), nothing fluid.

/** Min and max rem of every --wm-step-* clamp() in a stylesheet. */
function wmmsp_step_bounds( string $css ): array {
	preg_match_all( '/--wm-step-([a-z0-9-]+):\s*clamp\(\s*([\d.]+)rem,[^,]+,\s*([\d.]+)rem\s*\)/', $css, $m, PREG_SET_ORDER );
	$out = [];
	foreach ( $m as $row ) {
		$out[ $row[1] ] = [ (float) $row[2], (float) $row[3] ];
	}
	return $out;
}

$GLOBALS['wmmsp_option_overrides'] = [
	'wmmsp_base_size_max'        => '0',
	'wmmsp_min_viewport'         => '0',
	'wmmsp_max_viewport'         => '0',
	'wmmsp_ratio_name'           => '',
	'wmmsp_font_override_preset' => '',
];
$wiped      = Plugin::scale_options();
$wiped_css  = CSS_Token_Generator::generate_stylesheet( $wiped['base_min'], $wiped['base_max'], $wiped['ratio'], $wiped['min_vp'], $wiped['max_vp'], true, $wiped['font_preset'] );
$wiped_bad  = array_keys( array_filter( wmmsp_step_bounds( $wiped_css ), static fn( $b ) => $b[1] < $b[0] ) );
$t->assert(
	360.0 === $wiped['min_vp'] && 1440.0 === $wiped['max_vp'] && 18.0 === $wiped['base_max'] && 'inter-to-arial' === $wiped['font_preset']
		&& 9 === count( wmmsp_step_bounds( $wiped_css ) ) && [] === $wiped_bad,
	'Options zeroed by a settings save fall back to usable values and every step clamp() keeps max >= min',
	json_encode( [ 'options' => $wiped, 'inverted' => $wiped_bad ] )
);

$GLOBALS['wmmsp_option_overrides'] = [ 'wmmsp_base_size' => '24' ];
$large     = Plugin::scale_options();
$large_css = CSS_Token_Generator::generate_stylesheet( $large['base_min'], $large['base_max'], $large['ratio'], $large['min_vp'], $large['max_vp'], true, $large['font_preset'] );
$large_bad = array_keys( array_filter( wmmsp_step_bounds( $large_css ), static fn( $b ) => $b[1] < $b[0] ) );
$t->assert(
	$large['base_max'] >= $large['base_min'] && [] === $large_bad,
	'A base size above the stored maximum (which has no field) does not invert the clamp()',
	json_encode( [ 'options' => $large, 'inverted' => $large_bad ] )
);
// The page's fields post '' for an emptied base size (floatval: 0) and accept a ratio below their
// min="1" from a crafted request; viewports can only be set with `wp option update`, in any order.
$GLOBALS['wmmsp_option_overrides'] = [ 'wmmsp_base_size' => '', 'wmmsp_ratio' => '0.5', 'wmmsp_min_viewport' => '1440', 'wmmsp_max_viewport' => '360' ];
$odd = Plugin::scale_options();
$t->assert(
	16.0 === $odd['base_min'] && 1.0 === $odd['ratio'] && 360.0 === $odd['min_vp'] && 1440.0 === $odd['max_vp'],
	'An empty base size, a ratio below 1 and viewports in the wrong order fall back to usable values',
	json_encode( $odd )
);

// The token output itself (the private constructor bootstraps WordPress hooks, so no instance()).
$GLOBALS['wmmsp_option_overrides'] = [ 'wmmsp_base_size_max' => '0', 'wmmsp_min_viewport' => '0', 'wmmsp_max_viewport' => '0', 'wmmsp_font_override_preset' => '' ];
$served     = ( new ReflectionClass( Plugin::class ) )->newInstanceWithoutConstructor()->get_compiled_css();
$served_bad = array_keys( array_filter( wmmsp_step_bounds( $served ), static fn( $b ) => $b[1] < $b[0] ) );
$t->assert(
	9 === count( wmmsp_step_bounds( $served ) ) && [] === $served_bad && false !== strpos( $served, '--wm-min-viewport: 360px' ),
	'The token sheet printed in wp_head is built from the usable values, not the zeroed options',
	json_encode( [ 'inverted' => $served_bad ] )
);
unset( $GLOBALS['wmmsp_option_overrides'] );

$main_code = (string) file_get_contents( $plugin_root . '/wm-modularscale.php' );
preg_match_all( "/register_setting\(\s*'wmmsp_settings_group',\s*'(\w+)'/", $main_code . $ctrl_src, $group_opts );
preg_match_all( '/name="(wmmsp_\w+)"/', $main_code, $page_fields );
$fieldless = array_values( array_diff( array_unique( $group_opts[1] ), $page_fields[1] ) );
$t->assert(
	count( $group_opts[1] ) >= 5 && [] === $fieldless && 0 === preg_match( '/register_setting\(/', $main_code ),
	'The settings group posted by the page carries only options the page has a field for, registered once',
	json_encode( [ 'fieldless in group' => $fieldless, 'group' => $group_opts[1] ] )
);

$adapter_src = (string) file_get_contents( $plugin_root . '/includes/class-modularscale-spoke-adapter.php' );
$readers     = [
	'settings page'  => (string) substr( $main_code, (int) strpos( $main_code, 'function wmmsp_settings_page' ), 600 ),
	'widget'         => (string) substr( $main_code, (int) strpos( $main_code, 'function wmmsp_render_dashboard_widget' ), 500 ),
	'adapter'        => $adapter_src,
];
$wrong_default = array_keys( array_filter( $readers, static fn( $src ) => 1 !== preg_match( "/get_option\(\s*'wmmsp_apply_typography',\s*(1|true)\s*\)/", $src ) ) );
$t->assert(
	[] === $wrong_default,
	'Settings page, dashboard widget and hub telemetry read the apply toggle with the default the token output uses (they showed "not applied" while the tokens printed)',
	json_encode( $wrong_default )
);

$direct_readers = [];
foreach ( [ 'src/Integration/Theme_Json_Bridge.php', 'src/Gutenberg/Blocks/ratio-visualizer/render.php', 'src/Gutenberg/Blocks/theme-json-exporter/render.php', 'src/Plugin.php' ] as $reader ) {
	if ( preg_match( "/get_option\(\s*'wmmsp_(base_size_max|min_viewport|max_viewport)'/", (string) file_get_contents( $plugin_root . '/' . $reader ), $dm ) && ! str_ends_with( $reader, 'Plugin.php' ) ) {
		$direct_readers[] = $reader;
	}
}
$t->assert(
	[] === $direct_readers && false !== strpos( (string) file_get_contents( $plugin_root . '/src/Integration/Theme_Json_Bridge.php' ), 'Plugin::scale_options()' ),
	'theme.json and the scale blocks read the scale through Plugin::scale_options(), not the raw options',
	json_encode( $direct_readers )
);

// 14. Settings page language and what it names (2026-09-15). The page was Spanish on German installs
// (the catalogs copy the Spanish msgid into msgstr), its sandbox listed --wm-ms-* variables nothing emits,
// the widget called the off state "CSS Only", and a root wm-modularscale.js was never enqueued.
$spanish_hits = [];
$language_files = array_merge( [ 'wm-modularscale.php', 'src/Integration/Theme_Json_Bridge.php', 'assets/js/modularscale-admin.js' ], array_map( static fn( $f ) => substr( $f, strlen( $plugin_root ) + 1 ), glob( $plugin_root . '/src/Gutenberg/Blocks/*/block.json' ) ) );
foreach ( $language_files as $rel ) {
	if ( preg_match_all( '/[ñáíóú¿¡]/u', (string) file_get_contents( $plugin_root . '/' . $rel ), $sm ) ) {
		$spanish_hits[ $rel ] = count( $sm[0] );
	}
}
// Characters alone miss "Unidad de Medida": the translatable strings and block descriptions are also checked for
// Spanish function words, none of which is a German or English word.
$prose = [];
preg_match_all( "/(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\\(\\s*'((?:[^'\\\\]|\\\\.)*)'\\s*,\\s*'wm-modularscale'/", $main_src, $gettext );
foreach ( $gettext[1] as $s ) {
	$prose[] = $s;
}
foreach ( glob( $plugin_root . '/src/Gutenberg/Blocks/*/block.json' ) as $bj ) {
	$prose[] = (string) ( json_decode( (string) file_get_contents( $bj ), true )['description'] ?? '' );
}
$spanish_words = array_values( array_filter( $prose, static fn( $s ) => 1 === preg_match( '/\\b(de|la|el|los|las|del|para|una|con|tu|y)\\b/iu', $s ) ) );
$t->assert(
	[] === $spanish_hits && count( $gettext[1] ) > 40 && [] === $spanish_words,
	'Settings page, block descriptions, theme.json bridge and sandbox script carry no Spanish (characters ñ á í ó ú ¿ ¡, or Spanish function words in translatable strings)',
	json_encode( [ 'characters' => $spanish_hits, 'strings' => $spanish_words, 'gettext strings' => count( $gettext[1] ) ], JSON_UNESCAPED_UNICODE )
);

$sandbox_src  = (string) file_get_contents( $plugin_root . '/assets/js/modularscale-admin.js' );
$force_styles = (string) substr( $main_src, (int) strpos( $main_src, 'function wmmsp_force_styles' ), 1400 );
preg_match_all( "/'\s*(--[a-z0-9-]+):/", $sandbox_src, $sandbox_vars );
preg_match_all( '/(--wmmsp-[a-z0-9-]+):/', $force_styles, $sheet_vars );
$sandbox_set = array_values( array_unique( $sandbox_vars[1] ) );
$sheet_set   = array_values( array_unique( $sheet_vars[1] ) );
sort( $sandbox_set );
sort( $sheet_set );
$t->assert(
	count( $sheet_set ) >= 8 && $sandbox_set === $sheet_set,
	'The sandbox shows exactly the variables wmmsp_force_styles() prints',
	json_encode( [ 'sandbox' => $sandbox_set, 'stylesheet' => $sheet_set ] )
);

$package = json_decode( (string) file_get_contents( $plugin_root . '/package.json' ), true );
$t->assert(
	! file_exists( $plugin_root . '/wm-modularscale.js' ) && ( ! isset( $package['main'] ) || file_exists( $plugin_root . '/' . $package['main'] ) ),
	'No unenqueued root wm-modularscale.js, and package.json "main" names no missing file',
	json_encode( [ 'main' => $package['main'] ?? null ] )
);

$widget_src = (string) substr( $main_src, (int) strpos( $main_src, 'function wmmsp_render_dashboard_widget' ), 3000 );
$t->assert(
	false === strpos( $widget_src, "'CSS Only'" ) && false === strpos( $widget_src, "'Global Auto'" ),
	'Dashboard widget does not call the off state "CSS Only" (nothing is printed then)'
);

// 15. Blocks print measured values, not claims (2026-09-15). The APCA badge said "BFSG 2025 Konform" from
// one colour pair and computed Lc without the reference soft clamp; the visualizer drew the same straight
// line for every ratio and labelled step +1 as H1; two renders printed emoji and marketing badges.
$apca_ref = [
	[ '#000000', '#ffffff', 106.0 ],
	[ '#ffffff', '#000000', -107.9 ],
	[ '#f8fafc', '#0f172a', -103.3 ],
	[ '#94a3b8', '#0f172a', -50.6 ],
];
$apca_off = [];
foreach ( $apca_ref as [ $txt, $bg, $expected ] ) {
	$got = Scale_Engine::calculate_apca_lc( $txt, $bg );
	if ( abs( $got - $expected ) > 0.15 ) {
		$apca_off[] = "{$txt} on {$bg}: {$got}, apca-w3 {$expected}";
	}
}
$t->assert(
	[] === $apca_off,
	'calculate_apca_lc matches apca-w3 0.1.9 (black soft clamp) on black/white and the badge defaults',
	json_encode( $apca_off )
);

$render_block = static function ( string $slug, array $attributes ): string {
	$content = '';
	ob_start();
	include dirname( __DIR__ ) . '/src/Gutenberg/Blocks/' . $slug . '/render.php';
	return (string) ob_get_clean();
};

$badge_high = $render_block( 'contrast-matrix-badge', [ 'textColor' => '#ffffff', 'backgroundColor' => '#000000' ] );
$badge_low  = $render_block( 'contrast-matrix-badge', [ 'textColor' => '#777777', 'backgroundColor' => '#222222' ] );
$claim_src  = '';
foreach ( Block_Manager::BLOCKS as $b_slug ) {
	$claim_src .= (string) file_get_contents( $blocks_dir . '/' . $b_slug . '/render.php' ) . (string) file_get_contents( $blocks_dir . '/' . $b_slug . '/block.json' );
}
$t->assert(
	false !== strpos( $badge_high, 'Reicht für Fließtext' ) && false !== strpos( $badge_low, 'Zu wenig Kontrast für Text' )
		&& 0 === preg_match( '/konform|compliant|WCAG 3/i', $claim_src ),
	'The APCA badge gives the usage hint of its Lc value and no block claims conformity or WCAG 3'
);

$GLOBALS['wmmsp_option_overrides'] = [ 'wmmsp_base_size' => '16', 'wmmsp_base_size_max' => '18', 'wmmsp_ratio' => '1.5' ];
$visual = $render_block( 'ratio-visualizer', [ 'showMath' => false ] );
unset( $GLOBALS['wmmsp_option_overrides'] );
preg_match_all( '/<circle cx="(\d+)" cy="(\d+)"/', $visual, $nodes );
preg_match_all( '/<text x="\d+" y="145"[^>]*>([^<]+)<\/text>/', $visual, $labels );
$cy       = array_map( 'intval', $nodes[2] );
$gaps_low = count( $cy ) === 9 ? $cy[0] - $cy[1] : 0;
$gaps_top = count( $cy ) === 9 ? $cy[7] - $cy[8] : 0;
$t->assert(
	9 === count( $cy ) && $gaps_top > $gaps_low * 3 && 'H6 (+1)' === ( $labels[1][3] ?? '' ) && 'H1 (+6)' === ( $labels[1][8] ?? '' ),
	'Ratio visualizer places the nodes at the step sizes (gaps grow with the ratio) and labels +1 as H6, +6 as H1',
	json_encode( [ 'cy' => $cy, 'labels' => $labels[1] ] )
);

$emoji_renders = [];
foreach ( Block_Manager::BLOCKS as $b_slug ) {
	if ( preg_match( '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', (string) file_get_contents( $blocks_dir . '/' . $b_slug . '/render.php' ) ) ) {
		$emoji_renders[] = $b_slug;
	}
}
$t->assert(
	[] === $emoji_renders && false === strpos( $claim_src, 'Living Standard' ) && false === strpos( $claim_src, 'Multi-Strand Engine' ),
	'Block renders print no emoji and no marketing badges',
	json_encode( $emoji_renders )
);

// Summary & Exit
exit( $t->report() );
}
