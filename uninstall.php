<?php
// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options from the database
delete_option('wmmsp_base_size');
delete_option('wmmsp_ratio');
delete_option('wmmsp_unit');
delete_option('wmmsp_precision');
delete_option('wmmsp_apply_typography');

// Remove settings group if necessary (for multisite)
delete_site_option('wmmsp_base_size');
delete_site_option('wmmsp_ratio');
delete_site_option('wmmsp_unit');
delete_site_option('wmmsp_precision');
delete_site_option('wmmsp_apply_typography');
