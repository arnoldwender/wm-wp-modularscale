<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Server-Side Render for wm-scale/contrast-matrix-badge
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

use WenderMedia\ModularScale\Core\Scale_Engine;

$text_color = sanitize_hex_color( $attributes['textColor'] ?? '#f8fafc' ) ?: '#f8fafc';
$bg_color   = sanitize_hex_color( $attributes['backgroundColor'] ?? '#0f172a' ) ?: '#0f172a';
$target_ctx = sanitize_text_field( $attributes['targetContext'] ?? 'body' );

$lc = Scale_Engine::calculate_apca_lc( $text_color, $bg_color );
$abs_lc = abs( $lc );

// Usage hint from APCA's readability criterion (Lc 75 body text, 60 other text, 45 large text). One colour
// pair says nothing about a page's accessibility: until 2026-09-15 the badge printed a BFSG conformity label
// from Lc 75 on.
$status_label = 'Lc ' . $abs_lc;
$badge_color  = '#10b981'; // green
$usage_hint   = 'Reicht für Fließtext';

if ( $abs_lc < 45 ) {
	$badge_color = '#ef4444'; // red
	$usage_hint  = 'Zu wenig Kontrast für Text';
} elseif ( $abs_lc < 60 ) {
	$badge_color = '#f59e0b'; // amber
	$usage_hint  = 'Nur große Überschriften (ab 36 px)';
} elseif ( $abs_lc < 75 ) {
	$badge_color = '#38bdf8'; // blue
	$usage_hint  = 'Text außer Fließtext';
}

$class_name = 'wm-contrast-badge-pill';
if ( ! empty( $attributes['className'] ) ) {
	$class_name .= ' ' . esc_attr( $attributes['className'] );
}

?>
<div class="<?php echo esc_attr( $class_name ); ?>" style="display:inline-flex;align-items:center;gap:8px;background:rgba(15,23,42,0.85);border:1px solid <?php echo esc_attr( $badge_color ); ?>;border-radius:24px;padding:6px 14px;font-family:-apple-system,sans-serif;font-size:0.85rem;color:#f8fafc;box-shadow:0 4px 12px rgba(0,0,0,0.15);">
	<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?php echo esc_attr( $badge_color ); ?>;"></span>
	<span style="font-weight:700;color:<?php echo esc_attr( $badge_color ); ?>;"><?php echo esc_html( $status_label ); ?></span>
	<span style="color:#94a3b8;">|</span>
	<span><?php echo esc_html( $usage_hint ); ?></span>
	<span style="font-size:0.75rem;color:#64748b;background:rgba(255,255,255,0.05);padding:2px 6px;border-radius:4px;">APCA SAPC</span>
</div>
