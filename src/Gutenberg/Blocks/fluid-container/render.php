<?php
/**
 * Server-Side Render for wm-scale/fluid-container
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$container_name = sanitize_text_field( $attributes['containerName'] ?? 'wm-typographic-context' );
$container_type = sanitize_text_field( $attributes['containerType'] ?? 'inline-size' );

$class_name = 'wm-fluid-container';
if ( ! empty( $attributes['className'] ) ) {
	$class_name .= ' ' . esc_attr( $attributes['className'] );
}

$style = "container-type: " . esc_attr( $container_type ) . "; container-name: " . esc_attr( $container_name ) . ";";

?>
<div class="<?php echo esc_attr( $class_name ); ?>" style="<?php echo esc_attr( $style ); ?>">
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inner blocks, already rendered by WordPress. ?>
</div>
