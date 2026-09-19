<?php
/**
 * Server-Side Render for wm-scale/typographic-grid
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$columns = isset( $attributes['columns'] ) ? max( 1, min( 6, (int) $attributes['columns'] ) ) : 3;
$gap     = $attributes['gap'] ?? 'var(--wm-step-2, 1.5rem)';

$class_name = 'wm-typographic-grid';
if ( ! empty( $attributes['className'] ) ) {
	$class_name .= ' ' . esc_attr( $attributes['className'] );
}

$style = "display: grid; grid-template-columns: repeat({$columns}, minmax(0, 1fr)); gap: " . esc_attr( $gap ) . "; align-items: start;";

?>
<div class="<?php echo esc_attr( $class_name ); ?>" style="<?php echo esc_attr( $style ); ?>">
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inner blocks, already rendered by WordPress. ?>
</div>
