<?php
/**
 * Server-Side Render for wm-scale/fluid-heading
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$level   = isset( $attributes['level'] ) ? max( 1, min( 6, (int) $attributes['level'] ) ) : 2;
$step    = isset( $attributes['step'] ) ? (int) $attributes['step'] : ( 7 - $level );
$content = $attributes['content'] ?? ( $content ?: '' );
$align   = $attributes['align'] ?? 'left';
$custom_clamp = $attributes['customClamp'] ?? '';

$tag = 'h' . $level;
$class_name = 'has-fluid-h' . $level . '-font-size wm-fluid-heading';
if ( ! empty( $attributes['className'] ) ) {
	$class_name .= ' ' . esc_attr( $attributes['className'] );
}

$style = "text-align: " . esc_attr( $align ) . "; text-wrap: balance;";
if ( ! empty( $custom_clamp ) ) {
	$style .= " font-size: " . esc_attr( $custom_clamp ) . " !important;";
}

?>
<<?php echo esc_attr( $tag ); ?> class="<?php echo esc_attr( $class_name ); ?>" style="<?php echo esc_attr( $style ); ?>">
	<?php echo wp_kses_post( $content ); ?>
</<?php echo esc_attr( $tag ); ?>>
