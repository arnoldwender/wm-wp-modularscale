<?php
declare(strict_types=1);

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Preserve configured typography scale by default unless explicit opt-in is enabled (D-UNINSTALL).
if ( ! (bool) get_option( 'wmmsp_delete_data_on_uninstall', 0 ) ) {
	return;
}

$wmmsp_options = [
	'wmmsp_base_size',
	'wmmsp_ratio',
	'wmmsp_unit',
	'wmmsp_precision',
	'wmmsp_apply_typography',
	'wmmsp_base_size_max',
	'wmmsp_ratio_name',
	'wmmsp_min_viewport',
	'wmmsp_max_viewport',
	'wmmsp_font_override_preset',
	'wmmsp_delete_data_on_uninstall',
];

foreach ( $wmmsp_options as $wmmsp_opt ) {
	delete_option( $wmmsp_opt );
	delete_site_option( $wmmsp_opt );
}
