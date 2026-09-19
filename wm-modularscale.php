<?php
/**
 * Plugin Name:       WM Modular Scale
 * Plugin URI:        https://www.wendermedia.com/plugins/wm-modularscale/
 * Description:       Generates harmonious, responsive typography ratios and modular scales with a visual live preview sandbox for non-technical users.
 * Version:           1.2.0
 * Requires at least: 6.4
 * Tested up to:      7.1
 * Requires PHP:      8.1
 * Author:            Arnold Wender
 * Author URI:        https://www.arnoldwender.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wm-modularscale
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// PSR-4 Autoloader for WenderMedia\ModularScale namespace
spl_autoload_register( function ( string $class_name ): void {
	$prefix = 'WenderMedia\\ModularScale\\';
	if ( strpos( $class_name, $prefix ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class_name, strlen( $prefix ) );
	$file           = plugin_dir_path( __FILE__ ) . 'src/' . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

// Bootstrap Enterprise Typography Engine & Gutenberg Blocks
if ( class_exists( 'WenderMedia\\ModularScale\\Plugin' ) ) {
	\WenderMedia\ModularScale\Plugin::instance();
}

/**
 * Load plugin textdomain for i18n
 */
function wmmsp_load_textdomain() {
	load_plugin_textdomain( 'wm-modularscale', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'wmmsp_load_textdomain' );

// Register as Spoke in Wender Media Suite Hub
add_filter( 'wm_register_suite_module', function ( array $modules ): array {
	if ( ! class_exists( 'ModularScale_Spoke_Adapter' ) && file_exists( plugin_dir_path( __FILE__ ) . 'includes/class-modularscale-spoke-adapter.php' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-modularscale-spoke-adapter.php';
	}
	if ( class_exists( 'ModularScale_Spoke_Adapter' ) ) {
		$modules[] = new ModularScale_Spoke_Adapter();
	}
	return $modules;
} );

/**
 * Enqueue frontend typography styles with CSS custom properties
 */
function wmmsp_force_styles() {
	// Legacy sheet: stays off until the toggle is saved, as it always did (it resizes body and headings).
	if ( ! (bool) get_option( 'wmmsp_apply_typography', 0 ) ) {
		return;
	}

	wp_enqueue_style( 'wm-modularscale-css', plugins_url( 'wm-modularscale.css', __FILE__ ), [], '1.2.0' );

	$base      = (float) get_option( 'wmmsp_base_size', 16 );
	$ratio     = (float) get_option( 'wmmsp_ratio', 1.25 );
	$unit      = sanitize_text_field( get_option( 'wmmsp_unit', 'px' ) );
	$precision = (int) get_option( 'wmmsp_precision', 2 );

	$custom_css = "
		:root {
			--wmmsp-base-size: {$base}{$unit};
			--wmmsp-h1: " . round( $base * pow( $ratio, 4 ), $precision ) . "{$unit};
			--wmmsp-h2: " . round( $base * pow( $ratio, 3 ), $precision ) . "{$unit};
			--wmmsp-h3: " . round( $base * pow( $ratio, 2 ), $precision ) . "{$unit};
			--wmmsp-h4: " . round( $base * pow( $ratio, 1 ), $precision ) . "{$unit};
			--wmmsp-h5: " . round( $base * pow( $ratio, 0 ), $precision ) . "{$unit};
			--wmmsp-h6: " . round( $base / pow( $ratio, 1 ), $precision ) . "{$unit};
			--wmmsp-paragraph: {$base}{$unit};
		}
	";

	wp_add_inline_style( 'wm-modularscale-css', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'wmmsp_force_styles', 100 );

/**
 * Family menu icon: a monochrome hub-and-spoke mark, filled shapes only so the WordPress
 * svg-painter can recolour it with the admin colour scheme. Placeholder until the family
 * icon is decided (hackaton plan D12).
 */
function wmmsp_family_menu_icon(): string {
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><circle cx="10" cy="10" r="2.6" fill="black"/><circle cx="10" cy="3" r="1.8" fill="black"/><circle cx="16.1" cy="13.5" r="1.8" fill="black"/><circle cx="3.9" cy="13.5" r="1.8" fill="black"/><rect x="9.3" y="4.5" width="1.4" height="3.4" fill="black"/><rect x="9.3" y="12.1" width="1.4" height="3.4" fill="black" transform="rotate(-60 10 10)"/><rect x="9.3" y="12.1" width="1.4" height="3.4" fill="black" transform="rotate(60 10 10)"/></svg>';
	return 'data:image/svg+xml;base64,' . base64_encode( $svg );
}

/**
 * Register Top-Level Admin Menu & Submenus in WP Admin
 */
function wmmsp_add_admin_menu() {
	// Top-level menu in the WordPress sidebar
	add_menu_page(
		__( 'WM Modular Scale', 'wm-modularscale' ),
		__( 'Modular Scale', 'wm-modularscale' ),
		'manage_options',
		'wm-modular-scale',
		'wmmsp_settings_page',
		wmmsp_family_menu_icon(),
		31
	);

	// Submenu: Einstellungen & Typografie-Vorschau
	add_submenu_page(
		'wm-modular-scale',
		__( 'WM Modular Scale — Einstellungen & Vorschau', 'wm-modularscale' ),
		__( 'Typografie & Skalen', 'wm-modularscale' ),
		'manage_options',
		'wm-modular-scale',
		'wmmsp_settings_page'
	);

	// Also maintain Settings menu entry for backwards compatibility
	add_options_page(
		__( 'WM Modular Scale', 'wm-modularscale' ),
		__( 'Modular Scale (Typografie)', 'wm-modularscale' ),
		'manage_options',
		'wm-modular-scale-options',
		'wmmsp_settings_page'
	);
}
add_action( 'admin_menu', 'wmmsp_add_admin_menu' );

/**
 * Register Admin Dashboard Widget
 */
function wmmsp_register_dashboard_widget(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_add_dashboard_widget(
		'wm_modularscale_widget',
		__( 'WM Modular Scale — Harmonische Typografie & CSS Variablen', 'wm-modularscale' ),
		'wmmsp_render_dashboard_widget'
	);
}
add_action( 'wp_dashboard_setup', 'wmmsp_register_dashboard_widget' );

/**
 * Render Admin Dashboard Widget
 *
 * The banner reports the real state: until 2026-09-14 it read "Skala aktiv" whatever the
 * apply-to-site option said.
 */
function wmmsp_render_dashboard_widget(): void {
	$base    = (float) get_option( 'wmmsp_base_size', 16 );
	$ratio   = (float) get_option( 'wmmsp_ratio', 1.25 );
	$unit    = sanitize_text_field( get_option( 'wmmsp_unit', 'px' ) );
	$applied = (bool) get_option( 'wmmsp_apply_typography', 1 ); // The default the token output uses (Plugin).
	?>
	<div class="wm-dash-widget-box wmmsp-dash-widget" data-wm-theme="<?php echo esc_attr( function_exists( 'wm_get_suite_theme' ) ? wm_get_suite_theme() : 'cyber-events' ); ?>">
		<div class="wm-dash-banner <?php echo $applied ? 'is-valid' : 'is-warning'; ?>">
			<span class="wm-dash-banner-icon"><span class="dashicons dashicons-editor-textcolor" aria-hidden="true"></span></span>
			<div>
				<div class="wm-dash-banner-title">
					<?php
					echo $applied
						? esc_html__( 'Skala auf der Website angewendet', 'wm-modularscale' )
						: esc_html__( 'Skala berechnet, noch nicht auf die Website angewendet', 'wm-modularscale' );
					?>
				</div>
				<div class="wm-dash-banner-sub"><?php esc_html_e( 'Fluid-Tokens (--wm-step-*) und Stylesheet (--wmmsp-*)', 'wm-modularscale' ); ?></div>
			</div>
		</div>

		<div class="wm-dash-kpi-grid">
			<div class="wm-dash-kpi-card">
				<div class="wm-dash-kpi-val"><?php echo esc_html( (string) $base . $unit ); ?></div>
				<div class="wm-dash-kpi-lbl"><?php esc_html_e( 'Basisgröße', 'wm-modularscale' ); ?></div>
			</div>
			<div class="wm-dash-kpi-card">
				<div class="wm-dash-kpi-val is-on"><?php echo esc_html( (string) $ratio ); ?></div>
				<div class="wm-dash-kpi-lbl"><?php esc_html_e( 'Verhältnis', 'wm-modularscale' ); ?></div>
			</div>
			<div class="wm-dash-kpi-card">
				<div class="wm-dash-kpi-val <?php echo $applied ? 'is-on' : 'is-off'; ?>"><?php echo $applied ? esc_html__( 'Aktiv', 'wm-modularscale' ) : esc_html__( 'Aus', 'wm-modularscale' ); ?></div>
				<div class="wm-dash-kpi-lbl"><?php esc_html_e( 'Auf der Website', 'wm-modularscale' ); ?></div>
			</div>
		</div>

		<div class="wm-dash-actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wm-modular-scale' ) ); ?>" class="wm-dash-btn">
				<span><?php esc_html_e( 'Typografie-Cockpit öffnen', 'wm-modularscale' ); ?></span>
				<span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
			</a>
		</div>
	</div>
	<?php
}

/**
 * Add Settings shortcut link to Plugins list
 */
function wmmsp_add_plugin_action_links( array $links ): array {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'admin.php?page=wm-modular-scale' ) ),
		__( 'Einstellungen', 'wm-modularscale' )
	);
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wmmsp_add_plugin_action_links' );

// Settings are registered once, in WenderMedia\ModularScale\Admin\Settings_Controller::register_settings().

/**
 * Family header shared with the other Wender Media plugins: eyebrow badge with a dashicon,
 * title, subtitle, and the Wender Media badge carrying the hub/standalone state.
 */
function wmmsp_render_page_header( string $icon, string $eyebrow, string $title, string $subtitle ): void {
	?>
	<header class="wm-admin-header wmmsp-admin-header">
		<div class="wm-header-title">
			<span class="wm-badge wm-badge-cyan">
				<span class="dashicons <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
				<?php echo esc_html( $eyebrow ); ?>
			</span>
			<h1><?php echo esc_html( $title ); ?></h1>
			<p class="wm-subtitle"><?php echo esc_html( $subtitle ); ?></p>
		</div>
		<div class="wm-header-badges">
			<span class="wm-badge-family" title="<?php esc_attr_e( 'Part of the Wender Media plugin family', 'wm-modularscale' ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="3"/><circle cx="12" cy="4" r="2"/><circle cx="19" cy="16" r="2"/><circle cx="5" cy="16" r="2"/><path d="M12 7v2M13.9 13.2l3.4 2M10.1 13.2l-3.4 2"/></svg>
				<?php esc_html_e( 'Wender Media', 'wm-modularscale' ); ?>
				<span class="wm-badge-family-state"><?php echo defined( 'WM_SUITE_HUB_VERSION' ) ? esc_html__( 'Hub', 'wm-modularscale' ) : esc_html__( 'Standalone', 'wm-modularscale' ); ?></span>
			</span>
		</div>
	</header>
	<?php
}

/**
 * One row of the live sandbox: a badge that the script fills with the computed size and the
 * sample text whose font-size follows the fields.
 */
function wmmsp_render_preview_row( string $level, string $prefix, string $sample ): void {
	?>
	<div class="wmmsp-preview-item">
		<span class="wmmsp-preview-badge" id="badge-<?php echo esc_attr( $level ); ?>" data-prefix="<?php echo esc_attr( $prefix ); ?>"><?php echo esc_html( $prefix ); ?>: <?php esc_html_e( 'wird berechnet …', 'wm-modularscale' ); ?></span>
		<p class="wmmsp-preview-text wmmsp-preview-text--<?php echo esc_attr( $level ); ?>" id="prev-<?php echo esc_attr( $level ); ?>"><?php echo esc_html( $sample ); ?></p>
	</div>
	<?php
}

/**
 * Settings Page with Plain-Language Usability, Quick Presets & Live Sandbox Preview
 *
 * Styles come from the family sheets plus assets/css/modularscale-admin.css, the sandbox
 * script from assets/js/modularscale-admin.js (both enqueued by Settings_Controller).
 */
function wmmsp_settings_page() {
	$base      = (float) get_option( 'wmmsp_base_size', 16 );
	$ratio     = (float) get_option( 'wmmsp_ratio', 1.25 );
	$unit      = sanitize_text_field( get_option( 'wmmsp_unit', 'px' ) );
	$precision = (int) get_option( 'wmmsp_precision', 2 );
	$applied   = (bool) get_option( 'wmmsp_apply_typography', 1 ); // Unticked on a fresh install although the tokens printed, until 2026-09-15.

	$presets = [
		[ 'ratio' => '1.20', 'icon' => 'dashicons-book-alt', 'label' => __( 'Kleine Terz 1.200 – Blog', 'wm-modularscale' ) ],
		[ 'ratio' => '1.25', 'icon' => 'dashicons-star-filled', 'label' => __( 'Große Terz 1.250 – Voreinstellung', 'wm-modularscale' ) ],
		[ 'ratio' => '1.333', 'icon' => 'dashicons-art', 'label' => __( 'Quarte 1.333 – markante Überschriften', 'wm-modularscale' ) ],
		[ 'ratio' => '1.618', 'icon' => 'dashicons-performance', 'label' => __( 'Goldener Schnitt 1.618 – Magazin, große Titel', 'wm-modularscale' ) ],
	];

	$ratio_options = [
		'1.125' => __( '1.125 — Große Sekunde (kompakt, Anwendungen)', 'wm-modularscale' ),
		'1.200' => __( '1.200 — Kleine Terz (Blog)', 'wm-modularscale' ),
		'1.250' => __( '1.250 — Große Terz (Voreinstellung)', 'wm-modularscale' ),
		'1.333' => __( '1.333 — Quarte (markante Überschriften)', 'wm-modularscale' ),
		'1.414' => __( '1.414 — Übermäßige Quarte (Editorial)', 'wm-modularscale' ),
		'1.500' => __( '1.500 — Quinte (kreative Layouts)', 'wm-modularscale' ),
		'1.618' => __( '1.618 — Goldener Schnitt (sehr große Titel)', 'wm-modularscale' ),
	];

	$glossary = [
		[ 'icon' => 'dashicons-editor-expand', 'term' => __( 'Verhältnis (Ratio)', 'wm-modularscale' ), 'def' => __( 'Der feste Faktor zwischen zwei Stufen der Skala. Bei 1.25 ist jede Stufe 25 % größer als die vorherige.', 'wm-modularscale' ) ],
		[ 'icon' => 'dashicons-marker', 'term' => __( 'Basisgröße', 'wm-modularscale' ), 'def' => __( 'Die Schriftgröße des Fließtexts (<p>). Aus ihr werden alle Überschriften berechnet. Browser verwenden ohne eigene Einstellung 16 px.', 'wm-modularscale' ) ],
		[ 'icon' => 'dashicons-universal-access', 'term' => __( 'rem-Einheiten', 'wm-modularscale' ), 'def' => __( 'Relativ zur Schriftgröße, die im Browser eingestellt ist. Wer dort eine größere Schrift wählt, bekommt alle Stufen entsprechend größer.', 'wm-modularscale' ) ],
		[ 'icon' => 'dashicons-star-filled', 'term' => __( 'Goldener Schnitt (1.618)', 'wm-modularscale' ), 'def' => __( 'Das Verhältnis (1 + √5) / 2. Als Skala ergibt es einen starken Größenunterschied zwischen Überschriften und Fließtext.', 'wm-modularscale' ) ],
	];
	?>
	<div class="wrap wm-admin wmmsp-admin-wrap">
		<?php
		wmmsp_render_page_header(
			'dashicons-editor-textcolor',
			__( 'Modulare Typografie', 'wm-modularscale' ),
			__( 'WM Modular Scale — Typografische Skala', 'wm-modularscale' ),
			__( 'Berechnet die Schriftgrößen für Überschriften (H1–H6) und Fließtext aus einer Basisgröße und einem Verhältnis und gibt sie als CSS-Variablen aus.', 'wm-modularscale' )
		);
		?>

		<nav class="wm-jump-nav" aria-label="<?php esc_attr_e( 'Seitennavigation', 'wm-modularscale' ); ?>">
			<span class="wm-jump-nav-label"><?php esc_html_e( 'Springen zu:', 'wm-modularscale' ); ?></span>
			<a href="#wm-sec-guide" class="wm-jump-link"><span class="dashicons dashicons-lightbulb" aria-hidden="true"></span><?php esc_html_e( 'Anleitung & Vorlagen', 'wm-modularscale' ); ?></a>
			<a href="#wm-sec-config" class="wm-jump-link"><span class="dashicons dashicons-admin-generic" aria-hidden="true"></span><?php esc_html_e( 'Einstellungen', 'wm-modularscale' ); ?></a>
			<a href="#wm-sec-preview" class="wm-jump-link"><span class="dashicons dashicons-visibility" aria-hidden="true"></span><?php esc_html_e( 'Live-Vorschau', 'wm-modularscale' ); ?></a>
			<a href="#wm-sec-developer" class="wm-jump-link"><span class="dashicons dashicons-editor-code" aria-hidden="true"></span><?php esc_html_e( 'CSS-Variablen', 'wm-modularscale' ); ?></a>
			<a href="#wm-sec-glossary" class="wm-jump-link"><span class="dashicons dashicons-book" aria-hidden="true"></span><?php esc_html_e( 'Glossar', 'wm-modularscale' ); ?></a>
		</nav>

		<section id="wm-sec-guide" class="wm-quick-guide">
			<div class="wm-quick-guide-title">
				<span class="dashicons dashicons-lightbulb" aria-hidden="true"></span>
				<?php esc_html_e( 'Was ist eine modulare Skala?', 'wm-modularscale' ); ?>
			</div>
			<p class="wm-quick-guide-desc">
				<?php esc_html_e( 'Eine modulare Skala nimmt eine Basisgröße (zum Beispiel 16 px) und multipliziert sie Stufe für Stufe mit einem festen Verhältnis, wie die Intervalle in der Musik. So stehen Überschriften (H1 bis H6) und Fließtext in einem gleichbleibenden Größenverhältnis, statt einzeln festgelegt zu werden.', 'wm-modularscale' ); ?>
			</p>
			<div class="wm-quick-guide-actions">
				<strong><?php esc_html_e( 'Vorlage übernehmen (setzt auch Basisgröße 16 und Einheit px):', 'wm-modularscale' ); ?></strong>
				<?php foreach ( $presets as $preset ) : ?>
					<button type="button" class="wm-preset-pill-btn" data-wmmsp-preset data-ratio="<?php echo esc_attr( $preset['ratio'] ); ?>" data-base="16" data-unit="px">
						<span class="dashicons <?php echo esc_attr( $preset['icon'] ); ?>" aria-hidden="true"></span>
						<?php echo esc_html( $preset['label'] ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		</section>

		<div class="wm-grid-2 wmmsp-grid">
			<!-- Configuration Form -->
			<section id="wm-sec-config" class="wm-card">
				<h2 class="wmmsp-card-title"><span class="dashicons dashicons-admin-generic" aria-hidden="true"></span><?php esc_html_e( 'Einstellungen', 'wm-modularscale' ); ?></h2>
				<form method="post" action="options.php" id="wmmsp-form">
					<?php
						settings_fields( 'wmmsp_settings_group' );
						do_settings_sections( 'wmmsp_settings_group' );
					?>

					<div class="wm-form-group">
						<label class="wm-label" for="wmmsp_base_size"><?php esc_html_e( 'Basisgröße (Fließtext)', 'wm-modularscale' ); ?></label>
						<input type="number" step="0.5" min="1" id="wmmsp_base_size" name="wmmsp_base_size" class="wm-input" value="<?php echo esc_attr( (string) $base ); ?>" aria-describedby="wmmsp_base_size_desc" />
						<p class="wm-field-desc" id="wmmsp_base_size_desc"><?php esc_html_e( 'Die Größe des Fließtexts, aus der die Überschriften berechnet werden. Browser verwenden ohne eigene Einstellung 16 px.', 'wm-modularscale' ); ?></p>
					</div>

					<div class="wm-form-group">
						<label class="wm-label" for="wmmsp_ratio_select"><?php esc_html_e( 'Verhältnis (Ratio)', 'wm-modularscale' ); ?></label>
						<select id="wmmsp_ratio_select" class="wm-select wmmsp-ratio-select">
							<option value="custom"><?php esc_html_e( 'Vorlage wählen oder Wert eingeben', 'wm-modularscale' ); ?></option>
							<?php foreach ( $ratio_options as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $ratio, (float) $value ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<label class="screen-reader-text" for="wmmsp_ratio"><?php esc_html_e( 'Verhältnis als Zahl', 'wm-modularscale' ); ?></label>
						<input type="number" step="0.001" min="1" id="wmmsp_ratio" name="wmmsp_ratio" class="wm-input" value="<?php echo esc_attr( (string) $ratio ); ?>" aria-describedby="wmmsp_ratio_desc" />
						<p class="wm-field-desc" id="wmmsp_ratio_desc"><?php esc_html_e( 'Je größer der Wert, desto größer der Abstand zwischen H1 und H3.', 'wm-modularscale' ); ?></p>
					</div>

					<div class="wm-form-group">
						<label class="wm-label" for="wmmsp_unit"><?php esc_html_e( 'Einheit', 'wm-modularscale' ); ?></label>
						<select id="wmmsp_unit" name="wmmsp_unit" class="wm-select" aria-describedby="wmmsp_unit_desc">
							<option value="px" <?php selected( $unit, 'px' ); ?>><?php esc_html_e( 'px (Pixel, feste Größe)', 'wm-modularscale' ); ?></option>
							<option value="rem" <?php selected( $unit, 'rem' ); ?>><?php esc_html_e( 'rem (relativ zur Schriftgröße des Browsers)', 'wm-modularscale' ); ?></option>
							<option value="em" <?php selected( $unit, 'em' ); ?>><?php esc_html_e( 'em (relativ zum übergeordneten Element)', 'wm-modularscale' ); ?></option>
						</select>
						<p class="wm-field-desc" id="wmmsp_unit_desc"><?php esc_html_e( 'Einheit der Variablen --wmmsp-* im Stylesheet. Die Fluid-Tokens --wm-step-* verwenden immer rem.', 'wm-modularscale' ); ?></p>
					</div>

					<div class="wm-form-group">
						<label class="wm-label" for="wmmsp_precision"><?php esc_html_e( 'Nachkommastellen', 'wm-modularscale' ); ?></label>
						<input type="number" step="1" min="0" max="4" id="wmmsp_precision" name="wmmsp_precision" class="wm-input" value="<?php echo esc_attr( (string) $precision ); ?>" aria-describedby="wmmsp_precision_desc" />
						<p class="wm-field-desc" id="wmmsp_precision_desc"><?php esc_html_e( 'Auf so viele Nachkommastellen rundet das Stylesheet die Werte (Voreinstellung 2).', 'wm-modularscale' ); ?></p>
					</div>

					<div class="wmmsp-toggle-box">
						<label class="wm-toggle-label" for="wmmsp_apply_typography">
							<input type="checkbox" id="wmmsp_apply_typography" name="wmmsp_apply_typography" value="1" <?php checked( $applied, true ); ?> />
							<span>
								<span class="wmmsp-toggle-title"><?php esc_html_e( 'Skala auf der Website anwenden', 'wm-modularscale' ); ?></span>
								<span class="wm-field-desc wmmsp-toggle-desc"><?php esc_html_e( 'Gibt die Fluid-Tokens --wm-step-* im Seitenkopf aus und lädt das Stylesheet, das body, h1–h6, Absätze, Listen, Zitate und Code aus diesen Werten setzt.', 'wm-modularscale' ); ?></span>
							</span>
						</label>
					</div>

					<div class="wmmsp-submit-row">
						<?php submit_button( __( 'Typografie speichern', 'wm-modularscale' ), 'primary large' ); ?>
					</div>
				</form>
			</section>

			<!-- Live Typography Sandbox Preview -->
			<section id="wm-sec-preview" class="wm-card">
				<h2 class="wmmsp-card-title"><span class="dashicons dashicons-visibility" aria-hidden="true"></span><?php esc_html_e( 'Live-Vorschau', 'wm-modularscale' ); ?></h2>
				<p class="wm-field-desc wmmsp-card-intro">
					<?php esc_html_e( 'Zeigt die Größen des Stylesheets (--wmmsp-*) für die aktuellen Felder, bevor Sie speichern.', 'wm-modularscale' ); ?>
				</p>

				<div class="wmmsp-preview-box">
					<?php
					wmmsp_render_preview_row( 'h1', 'H1', __( 'Hauptüberschrift H1', 'wm-modularscale' ) );
					wmmsp_render_preview_row( 'h2', 'H2', __( 'Abschnittsüberschrift H2', 'wm-modularscale' ) );
					wmmsp_render_preview_row( 'h3', 'H3', __( 'Überschrift eines Inhaltsblocks H3', 'wm-modularscale' ) );
					wmmsp_render_preview_row( 'h4', 'H4', __( 'Titel einer Karte H4', 'wm-modularscale' ) );
					wmmsp_render_preview_row( 'h5', 'H5', __( 'Kleine Überschrift H5', 'wm-modularscale' ) );
					wmmsp_render_preview_row( 'h6', 'H6', __( 'Fußnote H6', 'wm-modularscale' ) );
					wmmsp_render_preview_row( 'p', __( 'Fließtext', 'wm-modularscale' ), __( 'Das ist ein Absatz im Fließtext. Seine Größe ist die Basisgröße; die Überschriften darüber sind daraus berechnet.', 'wm-modularscale' ) );
					?>
				</div>
			</section>
		</div>

		<!-- Developer Generated CSS Variables Card -->
		<section id="wm-sec-developer" class="wm-card wmmsp-section">
			<h2 class="wmmsp-card-title"><span class="dashicons dashicons-editor-code" aria-hidden="true"></span><?php esc_html_e( 'CSS-Variablen', 'wm-modularscale' ); ?></h2>
			<p class="wm-field-desc wmmsp-card-intro">
				<?php esc_html_e( 'Diese Variablen setzt das Stylesheet, wenn die Skala angewendet wird. Die fließenden Stufen --wm-step-neg-2 bis --wm-step-6 berechnet das Plugin zusätzlich aus Basisgröße, maximaler Basisgröße und den Viewport-Breiten.', 'wm-modularscale' ); ?>
			</p>
			<pre class="wm-code-block wmmsp-code-block" id="wmmsp-generated-css"><code>/* <?php esc_html_e( 'Variablen werden berechnet …', 'wm-modularscale' ); ?> */</code></pre>
		</section>

		<!-- Typography Glossary Card -->
		<section id="wm-sec-glossary" class="wm-card">
			<h2 class="wmmsp-card-title"><span class="dashicons dashicons-book" aria-hidden="true"></span><?php esc_html_e( 'Glossar', 'wm-modularscale' ); ?></h2>
			<p class="wm-field-desc wmmsp-card-intro">
				<?php esc_html_e( 'Die Begriffe dieser Seite kurz erklärt.', 'wm-modularscale' ); ?>
			</p>
			<div class="wm-glossary-grid">
				<?php foreach ( $glossary as $entry ) : ?>
					<div class="wm-glossary-card">
						<div class="wm-glossary-term"><span class="dashicons <?php echo esc_attr( $entry['icon'] ); ?>" aria-hidden="true"></span><?php echo esc_html( $entry['term'] ); ?></div>
						<p class="wm-glossary-def"><?php echo esc_html( $entry['def'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	</div>
	<?php
}
