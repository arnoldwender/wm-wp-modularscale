<?php
/**
 * Server-Side Render for wm-scale/ratio-visualizer
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WenderMedia\ModularScale\Core\Scale_Engine;

$scale    = \WenderMedia\ModularScale\Plugin::scale_options();
$base_min = $scale['base_min'];
$base_max = $scale['base_max'];
$ratio    = $scale['ratio'];
$show_math = ! empty( $attributes['showMath'] );

// Compute steps for visualizer
$steps = [];
for ( $s = -2; $s <= 6; $s++ ) {
	$min_px = Scale_Engine::calculate_step_value( $base_min, $ratio, $s );
	$max_px = Scale_Engine::calculate_step_value( $base_max, $ratio, $s );
	$steps[] = [
		'step'   => $s,
		'min_px' => round( $min_px, 1 ),
		'max_px' => round( $max_px, 1 ),
		// Step +1 is H6 and +6 is H1 (--wm-font-size-h6: var(--wm-step-1) … h1: step 6); until 2026-09-15 the label said H1 for +1.
		'label'  => $s === 0 ? 'Body (0)' : ( $s > 0 ? 'H' . ( 7 - $s ) . " (+{$s})" : "Cap ({$s})" ),
	];
}

// Node heights follow the maximum sizes (y 120 for the smallest step, 25 for the largest). Until 2026-09-15
// the nodes sat on a straight line by index under a fixed Bezier curve, the same picture for every ratio.
$max_values = array_column( $steps, 'max_px' );
$lowest     = min( $max_values );
$span       = max( max( $max_values ) - $lowest, 0.001 );
$points     = [];
foreach ( $steps as $idx => $st ) {
	$steps[ $idx ]['cx'] = 60 + ( $idx * 72.5 );
	$steps[ $idx ]['cy'] = 120 - ( ( $st['max_px'] - $lowest ) / $span ) * 95;
	$points[]            = round( $steps[ $idx ]['cx'], 1 ) . ' ' . round( $steps[ $idx ]['cy'], 1 );
}

$class_name = 'wm-ratio-visualizer-box';
if ( ! empty( $attributes['className'] ) ) {
	$class_name .= ' ' . esc_attr( $attributes['className'] );
}

?>
<div class="<?php echo esc_attr( $class_name ); ?>" style="background:#090e1d;border:1px solid rgba(56,189,248,0.2);border-radius:12px;padding:24px;color:#f8fafc;font-family:-apple-system,BlinkMacSystemFont,sans-serif;">
	<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:12px;">
		<div>
			<div style="font-size:1.1rem;font-weight:700;color:#38bdf8;">WM Modular Scale — scale steps</div>
			<div style="font-size:0.85rem;color:#94a3b8;">Base: <?php echo esc_html( (string) $base_min ); ?>px – <?php echo esc_html( (string) $base_max ); ?>px | Ratio: <?php echo esc_html( (string) $ratio ); ?></div>
		</div>
	</div>

	<!-- SVG Curve Visualization -->
	<div style="margin:16px 0;background:#040813;border-radius:8px;padding:16px;overflow-x:auto;">
		<svg viewBox="0 0 700 160" style="width:100%;height:auto;display:block;" role="img" aria-label="<?php echo esc_attr( sprintf( 'Scale steps from %s px to %s px', $steps[0]['max_px'], $steps[8]['max_px'] ) ); ?>">
			<defs>
				<linearGradient id="wmScaleGrad" x1="0%" y1="0%" x2="100%" y2="0%">
					<stop offset="0%" stop-color="#38bdf8" />
					<stop offset="100%" stop-color="#00f2fe" />
				</linearGradient>
			</defs>
			<!-- Grid Lines -->
			<line x1="40" y1="130" x2="660" y2="130" stroke="#1e293b" stroke-width="1" />
			<line x1="40" y1="80" x2="660" y2="80" stroke="#1e293b" stroke-width="1" stroke-dasharray="4" />
			<line x1="40" y1="30" x2="660" y2="30" stroke="#1e293b" stroke-width="1" stroke-dasharray="4" />

			<!-- Line through the steps -->
			<path d="M <?php echo esc_attr( implode( ' L ', $points ) ); ?>" fill="none" stroke="url(#wmScaleGrad)" stroke-width="3" />

			<!-- Node Points -->
			<?php
			foreach ( $steps as $st ) :
				$cx = $st['cx'];
				$cy = $st['cy'];
			?>
				<circle cx="<?php echo (int) $cx; ?>" cy="<?php echo (int) $cy; ?>" r="5" fill="#00f2fe" stroke="#040813" stroke-width="2" />
				<text x="<?php echo (int) $cx; ?>" y="145" fill="#94a3b8" font-size="10" text-anchor="middle"><?php echo esc_html( $st['label'] ); ?></text>
				<text x="<?php echo (int) $cx; ?>" y="<?php echo (int) ( $cy - 8 ); ?>" fill="#38bdf8" font-size="10" font-weight="bold" text-anchor="middle"><?php echo esc_html( (string) $st['max_px'] ); ?>px</text>
			<?php endforeach; ?>
		</svg>
	</div>

	<?php if ( $show_math ) : ?>
		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-top:16px;">
			<?php foreach ( $steps as $st ) : ?>
				<div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:6px;padding:8px 12px;">
					<div style="font-size:0.75rem;color:#94a3b8;"><?php echo esc_html( $st['label'] ); ?></div>
					<div style="font-size:0.95rem;font-weight:700;color:#f8fafc;margin-top:2px;">
						<?php echo esc_html( (string) $st['min_px'] ); ?>px → <?php echo esc_html( (string) $st['max_px'] ); ?>px
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
