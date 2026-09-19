<?php
/**
 * Server-Side Render for wm-scale/fluid-lead
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$measure = isset( $attributes['measureLimit'] ) ? max( 35, min( 85, (int) $attributes['measureLimit'] ) ) : 65;
$content = $attributes['content'] ?? ( $content ?: '' );
$align   = $attributes['align'] ?? 'left';

$class_name = 'has-fluid-body-font-size wm-fluid-lead';
if ( ! empty( $attributes['className'] ) ) {
	$class_name .= ' ' . esc_attr( $attributes['className'] );
}

$style = "max-width: {$measure}ch; text-align: " . esc_attr( $align ) . "; text-wrap: pretty; margin-left: auto; margin-right: auto;";
if ( $align === 'left' ) {
	$style = "max-width: {$measure}ch; text-align: left; text-wrap: pretty;";
}

?>
<p class="<?php echo esc_attr( $class_name ); ?>" style="<?php echo esc_attr( $style ); ?>">
	<?php echo wp_kses_post( $content ); ?>
</p>
