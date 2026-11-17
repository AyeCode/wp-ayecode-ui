<?php
/**
 * Test page section: buttons.
 *
 * @package AyeCode\UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aui_preview_colors = [ 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark' ];
?>
<h3 class="h5"><?php esc_html_e( 'Solid', 'ayecode-connect' ); ?></h3>
<div class="d-flex flex-wrap gap-2 mb-3">
	<?php foreach ( $aui_preview_colors as $aui_preview_color ) : ?>
		<button type="button" class="btn btn-<?php echo esc_attr( $aui_preview_color ); ?>"><?php echo esc_html( ucfirst( $aui_preview_color ) ); ?></button>
	<?php endforeach; ?>
	<button type="button" class="btn btn-link"><?php esc_html_e( 'Link', 'ayecode-connect' ); ?></button>
</div>

<h3 class="h5"><?php esc_html_e( 'Outline', 'ayecode-connect' ); ?></h3>
<div class="d-flex flex-wrap gap-2 mb-3">
	<?php foreach ( $aui_preview_colors as $aui_preview_color ) : ?>
		<button type="button" class="btn btn-outline-<?php echo esc_attr( $aui_preview_color ); ?>"><?php echo esc_html( ucfirst( $aui_preview_color ) ); ?></button>
	<?php endforeach; ?>
</div>

<h3 class="h5"><?php esc_html_e( 'Subtle', 'ayecode-connect' ); ?></h3>
<div class="d-flex flex-wrap gap-2 mb-3">
	<?php foreach ( $aui_preview_colors as $aui_preview_color ) : ?>
		<button type="button" class="btn btn-<?php echo esc_attr( $aui_preview_color ); ?>-subtle"><?php echo esc_html( ucfirst( $aui_preview_color ) ); ?></button>
	<?php endforeach; ?>
</div>

<h3 class="h5"><?php esc_html_e( 'Sizes, states, icons and groups', 'ayecode-connect' ); ?></h3>
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
	<button type="button" class="btn btn-primary btn-lg"><?php esc_html_e( 'Large', 'ayecode-connect' ); ?></button>
	<button type="button" class="btn btn-primary"><?php esc_html_e( 'Default', 'ayecode-connect' ); ?></button>
	<button type="button" class="btn btn-primary btn-sm"><?php esc_html_e( 'Small', 'ayecode-connect' ); ?></button>
	<button type="button" class="btn btn-primary" disabled><?php esc_html_e( 'Disabled', 'ayecode-connect' ); ?></button>
	<button type="button" class="btn btn-outline-primary active" aria-pressed="true"><?php esc_html_e( 'Active', 'ayecode-connect' ); ?></button>
	<button type="button" class="btn btn-primary">
		<?php echo \AyeCode\UI\Preview::icon( 'fa-solid fa-plus', 'me-2' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup. ?>
		<?php esc_html_e( 'With icon', 'ayecode-connect' ); ?>
	</button>
	<button type="button" class="btn btn-icon btn-outline-secondary rounded-circle" aria-label="<?php esc_attr_e( 'Favorite', 'ayecode-connect' ); ?>">
		<?php echo \AyeCode\UI\Preview::icon( 'fa-regular fa-heart' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup. ?>
	</button>
	<button type="button" class="btn btn-primary" disabled>
		<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span><?php esc_html_e( 'Loading', 'ayecode-connect' ); ?>
	</button>
</div>
<div class="d-flex flex-wrap gap-3">
	<div class="btn-group" role="group" aria-label="<?php esc_attr_e( 'Button group', 'ayecode-connect' ); ?>">
		<button type="button" class="btn btn-outline-primary"><?php esc_html_e( 'Left', 'ayecode-connect' ); ?></button>
		<button type="button" class="btn btn-outline-primary active"><?php esc_html_e( 'Middle', 'ayecode-connect' ); ?></button>
		<button type="button" class="btn btn-outline-primary"><?php esc_html_e( 'Right', 'ayecode-connect' ); ?></button>
	</div>
	<div class="btn-group" role="group" aria-label="<?php esc_attr_e( 'Radio toggle buttons', 'ayecode-connect' ); ?>">
		<input type="radio" class="btn-check" name="aui-preview-btnradio" id="aui-preview-btnradio1" autocomplete="off" checked>
		<label class="btn btn-outline-secondary" for="aui-preview-btnradio1"><?php esc_html_e( 'Day', 'ayecode-connect' ); ?></label>
		<input type="radio" class="btn-check" name="aui-preview-btnradio" id="aui-preview-btnradio2" autocomplete="off">
		<label class="btn btn-outline-secondary" for="aui-preview-btnradio2"><?php esc_html_e( 'Week', 'ayecode-connect' ); ?></label>
		<input type="radio" class="btn-check" name="aui-preview-btnradio" id="aui-preview-btnradio3" autocomplete="off">
		<label class="btn btn-outline-secondary" for="aui-preview-btnradio3"><?php esc_html_e( 'Month', 'ayecode-connect' ); ?></label>
	</div>
	<input type="checkbox" class="btn-check" id="aui-preview-btncheck" autocomplete="off" checked>
	<label class="btn btn-outline-success" for="aui-preview-btncheck"><?php esc_html_e( 'Checkbox toggle', 'ayecode-connect' ); ?></label>
	<button type="button" class="btn-close" aria-label="<?php esc_attr_e( 'Close', 'ayecode-connect' ); ?>"></button>
</div>
