<?php
/**
 * Runtime check in a WordPress install, read-only:
 *
 *     wp eval-file wp-content/plugins/wm-modularscale/tests/test-settings-save-runtime.php --user=<admin id>
 *
 * A save of the settings page goes to wp-admin/options.php, which writes every option registered in the
 * posted group and stores null for an option whose field the form does not carry (WordPress 7.1,
 * `$options = $allowed_options[ $option_page ]`, filled from `$new_allowed_options` by
 * `option_update_filter()`). Until 2026-09-15 that zeroed the viewports and the maximum base size on
 * every save. This file compares the group core would write with the fields the page renders, and checks
 * the token sheet the site prints with the options as they are stored. Nothing is written.
 *
 * Exit 0 when both checks pass, 1 otherwise. `wp eval-file` runs this file inside a method, so the counters
 * live in $GLOBALS.
 *
 * @package WM_ModularScale
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/template.php';

$GLOBALS['wmmsp_runtime'] = [ 'passed' => 0, 'failed' => 0 ];

$wmmsp_check = static function ( bool $ok, string $name, string $detail = '' ): void {
	$GLOBALS['wmmsp_runtime'][ $ok ? 'passed' : 'failed' ]++;
	echo ( $ok ? '[PASS] ' : '[FAIL] ' ) . $name . ( $ok || '' === $detail ? '' : ' — ' . $detail ) . "\n";
};

// Settings as options.php sees them on a save of the page.
\WenderMedia\ModularScale\Admin\Settings_Controller::register_settings();
$wmmsp_group = array_values( array_unique( option_update_filter( [] )['wmmsp_settings_group'] ?? [] ) );

ob_start();
wmmsp_settings_page();
$wmmsp_html = (string) ob_get_clean();
preg_match_all( '/name="(wmmsp_\w+)"/', $wmmsp_html, $wmmsp_fields );
preg_match( "/name='option_page' value='([^']+)'/", $wmmsp_html, $wmmsp_posted_group );

$wmmsp_nulled = array_values( array_diff( $wmmsp_group, $wmmsp_fields[1] ) );
$wmmsp_check(
	'wmmsp_settings_group' === ( $wmmsp_posted_group[1] ?? '' ) && count( $wmmsp_group ) >= 5 && [] === $wmmsp_nulled,
	'A save of the settings page writes only options the form carries',
	wp_json_encode( [ 'posted group' => $wmmsp_posted_group[1] ?? null, 'group' => $wmmsp_group, 'written as null' => $wmmsp_nulled ] )
);

// Token sheet from the options as stored.
$wmmsp_css = \WenderMedia\ModularScale\Plugin::instance()->get_compiled_css();
preg_match_all( '/--wm-step-([a-z0-9-]+):\s*clamp\(\s*([\d.]+)rem,[^,]+,\s*([\d.]+)rem\s*\)/', $wmmsp_css, $wmmsp_steps, PREG_SET_ORDER );
preg_match( '/--wm-min-viewport:\s*([\d.]+)px;\s*--wm-max-viewport:\s*([\d.]+)px;/', $wmmsp_css, $wmmsp_vp );
$wmmsp_inverted = array_values( array_map( static fn( $s ) => $s[1], array_filter( $wmmsp_steps, static fn( $s ) => (float) $s[3] < (float) $s[2] ) ) );
$wmmsp_check(
	9 === count( $wmmsp_steps ) && [] === $wmmsp_inverted && isset( $wmmsp_vp[2] ) && (float) $wmmsp_vp[1] > 0 && (float) $wmmsp_vp[2] > (float) $wmmsp_vp[1],
	'The printed token sheet has nine steps with max >= min between two ordered, non-zero viewports',
	wp_json_encode( [ 'steps' => count( $wmmsp_steps ), 'inverted' => $wmmsp_inverted, 'viewports' => array_slice( $wmmsp_vp, 1 ) ] )
);

printf( "RUNTIME RESULTS: %d PASSED | %d FAILED\n", $GLOBALS['wmmsp_runtime']['passed'], $GLOBALS['wmmsp_runtime']['failed'] );
exit( $GLOBALS['wmmsp_runtime']['failed'] > 0 ? 1 : 0 );
