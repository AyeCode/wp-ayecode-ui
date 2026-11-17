<?php
/**
 * Test page section: theme colors, subtle colors and added palette colors.
 *
 * @package AyeCode\UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aui_preview_colors = [ 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark' ];
$aui_preview_added  = \AyeCode\UI\SettingsOrchestrator::get_added_palette_colors();
?>
<h3 class="h5"><?php esc_html_e( 'Theme colors', 'ayecode-connect' ); ?></h3>
<div class="row row-cols-2 row-cols-md-4 row-cols-lg-8 g-3 mb-4">
	<?php foreach ( $aui_preview_colors as $aui_preview_color ) : ?>
		<div class="col">
			<div class="aui-preview-swatch rounded bg-<?php echo esc_attr( $aui_preview_color ); ?> border"></div>
			<div class="aui-preview-swatch rounded bg-<?php echo esc_attr( $aui_preview_color ); ?>-subtle border border-<?php echo esc_attr( $aui_preview_color ); ?>-subtle mt-1"></div>
			<div class="small mt-1 text-<?php echo esc_attr( $aui_preview_color ); ?>-emphasis"><?php echo esc_html( $aui_preview_color ); ?></div>
		</div>
	<?php endforeach; ?>
</div>

<h3 class="h5"><?php esc_html_e( 'Text and background helpers', 'ayecode-connect' ); ?></h3>
<div class="d-flex flex-wrap gap-2 mb-3">
	<?php foreach ( $aui_preview_colors as $aui_preview_color ) : ?>
		<span class="p-2 rounded text-bg-<?php echo esc_attr( $aui_preview_color ); ?>">text-bg-<?php echo esc_html( $aui_preview_color ); ?></span>
	<?php endforeach; ?>
</div>
<div class="d-flex flex-wrap gap-3 mb-3">
	<?php foreach ( $aui_preview_colors as $aui_preview_color ) : ?>
		<span class="text-<?php echo esc_attr( $aui_preview_color ); ?>">text-<?php echo esc_html( $aui_preview_color ); ?></span>
		<a class="link-<?php echo esc_attr( $aui_preview_color ); ?>" href="#aui-preview-colors">link-<?php echo esc_html( $aui_preview_color ); ?></a>
	<?php endforeach; ?>
</div>
<div class="d-flex flex-wrap gap-2 mb-4">
	<span class="p-2 bg-body border rounded">bg-body</span>
	<span class="p-2 bg-body-secondary rounded">bg-body-secondary</span>
	<span class="p-2 bg-body-tertiary rounded">bg-body-tertiary</span>
	<span class="p-2 bg-dotted rounded">bg-dotted</span>
	<span class="p-2 border border-dashed rounded">border-dashed</span>
	<span class="p-2 border border-dotted rounded">border-dotted</span>
</div>

<h3 class="h5"><?php esc_html_e( 'Added palette colors', 'ayecode-connect' ); ?></h3>
<?php if ( $aui_preview_added ) : ?>
	<div class="d-flex flex-wrap gap-2">
		<?php foreach ( $aui_preview_added as $aui_preview_slug => $aui_preview_color ) : ?>
			<span class="p-2 rounded bg-<?php echo esc_attr( $aui_preview_slug ); ?> text-white"><?php echo esc_html( $aui_preview_color['name'] ); ?></span>
			<span class="p-2 rounded text-<?php echo esc_attr( $aui_preview_slug ); ?> border border-<?php echo esc_attr( $aui_preview_slug ); ?>"><?php echo esc_html( $aui_preview_slug ); ?></span>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<p class="text-body-secondary"><?php esc_html_e( 'This site has no palette colors beyond the theme colors.', 'ayecode-connect' ); ?></p>
<?php endif; ?>
