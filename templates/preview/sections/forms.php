<?php
/**
 * Test page section: form controls, with every checkbox, radio and switch state.
 *
 * @package AyeCode\UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aui_preview_checks = [
	'off'           => [ __( 'Off', 'ayecode-connect' ), '' ],
	'on'            => [ __( 'On', 'ayecode-connect' ), ' checked' ],
	'disabled'      => [ __( 'Disabled', 'ayecode-connect' ), ' disabled' ],
	'on-disabled'   => [ __( 'On, disabled', 'ayecode-connect' ), ' checked disabled' ],
	'indeterminate' => [ __( 'Indeterminate', 'ayecode-connect' ), ' data-aui-preview-indeterminate' ],
];
?>
<div class="row g-4">
	<div class="col-lg-4">
		<h3 class="h5"><?php esc_html_e( 'Checkboxes', 'ayecode-connect' ); ?></h3>
		<?php foreach ( $aui_preview_checks as $aui_preview_key => $aui_preview_check ) : ?>
			<div class="form-check">
				<input class="form-check-input" type="checkbox" id="aui-preview-check-<?php echo esc_attr( $aui_preview_key ); ?>"<?php echo $aui_preview_check[1]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed attributes. ?>>
				<label class="form-check-label" for="aui-preview-check-<?php echo esc_attr( $aui_preview_key ); ?>"><?php echo esc_html( $aui_preview_check[0] ); ?></label>
			</div>
		<?php endforeach; ?>
		<div class="form-check form-check-inline mt-2">
			<input class="form-check-input" type="checkbox" id="aui-preview-check-inline1" checked>
			<label class="form-check-label" for="aui-preview-check-inline1"><?php esc_html_e( 'Inline', 'ayecode-connect' ); ?></label>
		</div>
		<div class="form-check form-check-inline">
			<input class="form-check-input" type="checkbox" id="aui-preview-check-inline2">
			<label class="form-check-label" for="aui-preview-check-inline2"><?php esc_html_e( 'Inline', 'ayecode-connect' ); ?></label>
		</div>
	</div>

	<div class="col-lg-4">
		<h3 class="h5"><?php esc_html_e( 'Radios', 'ayecode-connect' ); ?></h3>
		<?php foreach ( $aui_preview_checks as $aui_preview_key => $aui_preview_check ) : ?>
			<?php
			if ( 'indeterminate' === $aui_preview_key ) {
				continue;
			}
			?>
			<div class="form-check">
				<input class="form-check-input" type="radio" name="aui-preview-radio-<?php echo esc_attr( false !== strpos( $aui_preview_key, 'disabled' ) ? 'disabled' : 'enabled' ); ?>" id="aui-preview-radio-<?php echo esc_attr( $aui_preview_key ); ?>"<?php echo $aui_preview_check[1]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed attributes. ?>>
				<label class="form-check-label" for="aui-preview-radio-<?php echo esc_attr( $aui_preview_key ); ?>"><?php echo esc_html( $aui_preview_check[0] ); ?></label>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="col-lg-4">
		<h3 class="h5"><?php esc_html_e( 'Switches', 'ayecode-connect' ); ?></h3>
		<?php foreach ( $aui_preview_checks as $aui_preview_key => $aui_preview_check ) : ?>
			<?php
			if ( 'indeterminate' === $aui_preview_key ) {
				continue;
			}
			?>
			<div class="form-check form-switch">
				<input class="form-check-input" type="checkbox" role="switch" id="aui-preview-switch-<?php echo esc_attr( $aui_preview_key ); ?>"<?php echo $aui_preview_check[1]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed attributes. ?>>
				<label class="form-check-label" for="aui-preview-switch-<?php echo esc_attr( $aui_preview_key ); ?>"><?php echo esc_html( $aui_preview_check[0] ); ?></label>
			</div>
		<?php endforeach; ?>
		<?php foreach ( [ 'sm', 'md', 'lg', 'xl' ] as $aui_preview_size ) : ?>
			<div class="form-check form-switch custom-switch-<?php echo esc_attr( $aui_preview_size ); ?>">
				<input class="form-check-input" type="checkbox" role="switch" id="aui-preview-switch-size-<?php echo esc_attr( $aui_preview_size ); ?>" checked>
				<label class="form-check-label" for="aui-preview-switch-size-<?php echo esc_attr( $aui_preview_size ); ?>">custom-switch-<?php echo esc_html( $aui_preview_size ); ?></label>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Text inputs', 'ayecode-connect' ); ?></h3>
		<div class="mb-3">
			<label class="form-label" for="aui-preview-text"><?php esc_html_e( 'Label', 'ayecode-connect' ); ?></label>
			<input type="text" class="form-control" id="aui-preview-text" placeholder="<?php esc_attr_e( 'Placeholder', 'ayecode-connect' ); ?>">
			<div class="form-text"><?php esc_html_e( 'Help text under the field.', 'ayecode-connect' ); ?></div>
		</div>
		<div class="d-flex flex-column gap-2 mb-3">
			<input type="text" class="form-control form-control-lg" placeholder="form-control-lg" aria-label="form-control-lg">
			<input type="text" class="form-control form-control-sm" placeholder="form-control-sm" aria-label="form-control-sm">
			<input type="text" class="form-control" value="<?php esc_attr_e( 'Disabled', 'ayecode-connect' ); ?>" aria-label="<?php esc_attr_e( 'Disabled', 'ayecode-connect' ); ?>" disabled>
			<input type="text" class="form-control" value="<?php esc_attr_e( 'Read only', 'ayecode-connect' ); ?>" aria-label="<?php esc_attr_e( 'Read only', 'ayecode-connect' ); ?>" readonly>
			<input type="text" class="form-control-plaintext" value="form-control-plaintext" aria-label="form-control-plaintext" readonly>
		</div>
		<div class="form-floating mb-3">
			<input type="email" class="form-control" id="aui-preview-floating" placeholder="name@example.com">
			<label for="aui-preview-floating"><?php esc_html_e( 'Floating label', 'ayecode-connect' ); ?></label>
		</div>
		<div class="form-floating mb-3">
			<select class="form-select" id="aui-preview-floating-select">
				<option selected><?php esc_html_e( 'Choose one', 'ayecode-connect' ); ?></option>
				<option value="1"><?php esc_html_e( 'One', 'ayecode-connect' ); ?></option>
			</select>
			<label for="aui-preview-floating-select"><?php esc_html_e( 'Floating select', 'ayecode-connect' ); ?></label>
		</div>
		<div class="mb-3">
			<textarea class="form-control" rows="3" aria-label="<?php esc_attr_e( 'Textarea', 'ayecode-connect' ); ?>" placeholder="<?php esc_attr_e( 'Textarea', 'ayecode-connect' ); ?>"></textarea>
		</div>
		<div class="row g-2">
			<div class="col"><input type="color" class="form-control form-control-color" value="#2563eb" aria-label="<?php esc_attr_e( 'Color', 'ayecode-connect' ); ?>"></div>
			<div class="col-9"><input class="form-control" type="file" aria-label="<?php esc_attr_e( 'File', 'ayecode-connect' ); ?>"></div>
		</div>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Selects, range and input groups', 'ayecode-connect' ); ?></h3>
		<select class="form-select mb-2" aria-label="<?php esc_attr_e( 'Select', 'ayecode-connect' ); ?>">
			<option selected><?php esc_html_e( 'Select menu', 'ayecode-connect' ); ?></option>
			<option value="1"><?php esc_html_e( 'One', 'ayecode-connect' ); ?></option>
			<option value="2"><?php esc_html_e( 'Two', 'ayecode-connect' ); ?></option>
		</select>
		<select class="form-select form-select-sm mb-2" aria-label="form-select-sm"><option>form-select-sm</option></select>
		<select class="form-select mb-3" multiple aria-label="<?php esc_attr_e( 'Multiple select', 'ayecode-connect' ); ?>">
			<option selected><?php esc_html_e( 'One', 'ayecode-connect' ); ?></option>
			<option><?php esc_html_e( 'Two', 'ayecode-connect' ); ?></option>
			<option><?php esc_html_e( 'Three', 'ayecode-connect' ); ?></option>
		</select>
		<label class="form-label" for="aui-preview-range"><?php esc_html_e( 'Range', 'ayecode-connect' ); ?></label>
		<input type="range" class="form-range mb-3" id="aui-preview-range" value="40">
		<div class="input-group mb-2">
			<span class="input-group-text">@</span>
			<input type="text" class="form-control" placeholder="<?php esc_attr_e( 'Username', 'ayecode-connect' ); ?>" aria-label="<?php esc_attr_e( 'Username', 'ayecode-connect' ); ?>">
		</div>
		<div class="input-group mb-2">
			<input type="text" class="form-control" placeholder="<?php esc_attr_e( 'Search', 'ayecode-connect' ); ?>" aria-label="<?php esc_attr_e( 'Search', 'ayecode-connect' ); ?>">
			<button class="btn btn-primary" type="button"><?php esc_html_e( 'Go', 'ayecode-connect' ); ?></button>
		</div>
		<div class="input-group mb-3">
			<div class="input-group-text"><input class="form-check-input mt-0" type="checkbox" checked aria-label="<?php esc_attr_e( 'Checkbox in an input group', 'ayecode-connect' ); ?>"></div>
			<input type="text" class="form-control" aria-label="<?php esc_attr_e( 'Text with checkbox', 'ayecode-connect' ); ?>">
			<span class="input-group-text">.00</span>
		</div>

		<h3 class="h5"><?php esc_html_e( 'Validation', 'ayecode-connect' ); ?></h3>
		<div class="mb-2">
			<input type="text" class="form-control is-valid" value="<?php esc_attr_e( 'Valid', 'ayecode-connect' ); ?>" aria-label="<?php esc_attr_e( 'Valid', 'ayecode-connect' ); ?>">
			<div class="valid-feedback"><?php esc_html_e( 'Looks good.', 'ayecode-connect' ); ?></div>
		</div>
		<div class="mb-2">
			<input type="text" class="form-control is-invalid" value="<?php esc_attr_e( 'Invalid', 'ayecode-connect' ); ?>" aria-label="<?php esc_attr_e( 'Invalid', 'ayecode-connect' ); ?>">
			<div class="invalid-feedback"><?php esc_html_e( 'Please fix this field.', 'ayecode-connect' ); ?></div>
		</div>
		<div class="form-check">
			<input class="form-check-input is-invalid" type="checkbox" id="aui-preview-check-invalid">
			<label class="form-check-label" for="aui-preview-check-invalid"><?php esc_html_e( 'Invalid checkbox', 'ayecode-connect' ); ?></label>
		</div>
	</div>
</div>
