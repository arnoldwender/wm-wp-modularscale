<?php
declare(strict_types=1);
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

$level        = isset( $attributes['level'] ) ? max( 1, min( 6, (int) $attributes['level'] ) ) : 2;
$default_step = 7 - $level;
$step         = isset( $attributes['step'] ) ? max( -2, min( 6, (int) $attributes['step'] ) ) : $default_step;
$content      = $attributes['content'] ?? ( $content ?: '' );
$align        = $attributes['align'] ?? 'left';
$custom_clamp = $attributes['customClamp'] ?? '';

$tag = 'h' . $level;
$class_name = 'has-fluid-h' . $level . '-font-size wm-fluid-heading';
if ( ! empty( $attributes['className'] ) ) {
	$class_name .= ' ' . esc_attr( $attributes['className'] );
}

$style = "text-align: " . esc_attr( $align ) . "; text-wrap: balance;";
if ( ! empty( $custom_clamp ) ) {
	$style .= " font-size: " . esc_attr( $custom_clamp ) . " !important;";
} elseif ( $step !== $default_step ) {
	$step_var = $step < 0 ? ( '--wm-step-neg-' . abs( $step ) ) : ( '--wm-step-' . $step );
	$style .= " font-size: var(" . esc_attr( $step_var ) . ") !important;";
}

?>
<<?php echo esc_attr( $tag ); ?> class="<?php echo esc_attr( $class_name ); ?>" style="<?php echo esc_attr( $style ); ?>">
	<?php echo wp_kses_post( $content ); ?>
</<?php echo esc_attr( $tag ); ?>>
