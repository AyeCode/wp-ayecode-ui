<?php
/**
 * Test page section: dropdowns, modal, offcanvas, accordion, collapse, toasts, tooltips, popovers.
 *
 * @package AyeCode\UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="row g-4">
	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Dropdowns', 'ayecode-connect' ); ?></h3>
		<div class="d-flex flex-wrap gap-2 mb-4">
			<div class="dropdown">
				<button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><?php esc_html_e( 'Dropdown', 'ayecode-connect' ); ?></button>
				<ul class="dropdown-menu">
					<li><h6 class="dropdown-header"><?php esc_html_e( 'Header', 'ayecode-connect' ); ?></h6></li>
					<li><a class="dropdown-item active" href="#aui-preview-overlays"><?php esc_html_e( 'Active', 'ayecode-connect' ); ?></a></li>
					<li><a class="dropdown-item" href="#aui-preview-overlays"><?php esc_html_e( 'Action', 'ayecode-connect' ); ?></a></li>
					<li><a class="dropdown-item disabled" aria-disabled="true"><?php esc_html_e( 'Disabled', 'ayecode-connect' ); ?></a></li>
					<li><hr class="dropdown-divider"></li>
					<li><a class="dropdown-item" href="#aui-preview-overlays"><?php esc_html_e( 'Separated', 'ayecode-connect' ); ?></a></li>
				</ul>
			</div>
			<div class="btn-group dropup">
				<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><?php esc_html_e( 'Dropup', 'ayecode-connect' ); ?></button>
				<ul class="dropdown-menu"><li><a class="dropdown-item" href="#aui-preview-overlays"><?php esc_html_e( 'Action', 'ayecode-connect' ); ?></a></li></ul>
			</div>
			<div class="btn-group dropend">
				<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><?php esc_html_e( 'Dropend', 'ayecode-connect' ); ?></button>
				<ul class="dropdown-menu"><li><a class="dropdown-item" href="#aui-preview-overlays"><?php esc_html_e( 'Action', 'ayecode-connect' ); ?></a></li></ul>
			</div>
			<div class="btn-group">
				<button type="button" class="btn btn-danger"><?php esc_html_e( 'Split', 'ayecode-connect' ); ?></button>
				<button type="button" class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false"><span class="visually-hidden"><?php esc_html_e( 'Toggle dropdown', 'ayecode-connect' ); ?></span></button>
				<ul class="dropdown-menu"><li><a class="dropdown-item" href="#aui-preview-overlays"><?php esc_html_e( 'Action', 'ayecode-connect' ); ?></a></li></ul>
			</div>
		</div>

		<h3 class="h5"><?php esc_html_e( 'Modal and offcanvas', 'ayecode-connect' ); ?></h3>
		<div class="d-flex flex-wrap gap-2 mb-4">
			<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#aui-preview-modal"><?php esc_html_e( 'Open modal', 'ayecode-connect' ); ?></button>
			<button type="button" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#aui-preview-offcanvas" aria-controls="aui-preview-offcanvas"><?php esc_html_e( 'Open offcanvas', 'ayecode-connect' ); ?></button>
		</div>
		<div class="modal fade" id="aui-preview-modal" tabindex="-1" aria-labelledby="aui-preview-modal-title" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title h5" id="aui-preview-modal-title"><?php esc_html_e( 'Modal title', 'ayecode-connect' ); ?></h4>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'ayecode-connect' ); ?>"></button>
					</div>
					<div class="modal-body">
						<p><?php esc_html_e( 'Modal body with a form field.', 'ayecode-connect' ); ?></p>
						<input type="text" class="form-control" aria-label="<?php esc_attr_e( 'Field in a modal', 'ayecode-connect' ); ?>">
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php esc_html_e( 'Close', 'ayecode-connect' ); ?></button>
						<button type="button" class="btn btn-primary"><?php esc_html_e( 'Save', 'ayecode-connect' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<div class="offcanvas offcanvas-end" tabindex="-1" id="aui-preview-offcanvas" aria-labelledby="aui-preview-offcanvas-title">
			<div class="offcanvas-header">
				<h4 class="offcanvas-title h5" id="aui-preview-offcanvas-title"><?php esc_html_e( 'Offcanvas', 'ayecode-connect' ); ?></h4>
				<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?php esc_attr_e( 'Close', 'ayecode-connect' ); ?>"></button>
			</div>
			<div class="offcanvas-body"><?php esc_html_e( 'Offcanvas content.', 'ayecode-connect' ); ?></div>
		</div>

		<h3 class="h5"><?php esc_html_e( 'Tooltips and popovers', 'ayecode-connect' ); ?></h3>
		<div class="d-flex flex-wrap gap-2 mb-4">
			<button type="button" class="btn btn-outline-secondary" data-bs-toggle="tooltip" data-bs-title="<?php esc_attr_e( 'Tooltip on top', 'ayecode-connect' ); ?>"><?php esc_html_e( 'Tooltip', 'ayecode-connect' ); ?></button>
			<button type="button" class="btn btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="<?php esc_attr_e( 'Tooltip on bottom', 'ayecode-connect' ); ?>"><?php esc_html_e( 'Tooltip bottom', 'ayecode-connect' ); ?></button>
			<button type="button" class="btn btn-outline-secondary" data-bs-toggle="popover" data-bs-title="<?php esc_attr_e( 'Popover title', 'ayecode-connect' ); ?>" data-bs-content="<?php esc_attr_e( 'Popover content.', 'ayecode-connect' ); ?>"><?php esc_html_e( 'Popover', 'ayecode-connect' ); ?></button>
		</div>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Accordion', 'ayecode-connect' ); ?></h3>
		<div class="accordion mb-4" id="aui-preview-accordion">
			<?php foreach ( [ 1, 2, 3 ] as $aui_preview_i ) : ?>
				<div class="accordion-item">
					<h4 class="accordion-header">
						<button class="accordion-button<?php echo 1 === $aui_preview_i ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#aui-preview-acc<?php echo (int) $aui_preview_i; ?>" aria-expanded="<?php echo 1 === $aui_preview_i ? 'true' : 'false'; ?>" aria-controls="aui-preview-acc<?php echo (int) $aui_preview_i; ?>">
							<?php
							/* translators: %d: item number */
							echo esc_html( sprintf( __( 'Accordion item %d', 'ayecode-connect' ), $aui_preview_i ) );
							?>
						</button>
					</h4>
					<div id="aui-preview-acc<?php echo (int) $aui_preview_i; ?>" class="accordion-collapse collapse<?php echo 1 === $aui_preview_i ? ' show' : ''; ?>" data-bs-parent="#aui-preview-accordion">
						<div class="accordion-body"><?php esc_html_e( 'Accordion body.', 'ayecode-connect' ); ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<h3 class="h5"><?php esc_html_e( 'Collapse', 'ayecode-connect' ); ?></h3>
		<button class="btn btn-outline-primary mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#aui-preview-collapse" aria-expanded="false" aria-controls="aui-preview-collapse"><?php esc_html_e( 'Toggle', 'ayecode-connect' ); ?></button>
		<div class="collapse mb-4" id="aui-preview-collapse"><div class="card card-body"><?php esc_html_e( 'Collapsed content.', 'ayecode-connect' ); ?></div></div>

		<h3 class="h5"><?php esc_html_e( 'Toasts', 'ayecode-connect' ); ?></h3>
		<div class="toast show mb-2" role="status" aria-live="polite" aria-atomic="true">
			<div class="toast-header">
				<strong class="me-auto"><?php esc_html_e( 'Toast', 'ayecode-connect' ); ?></strong>
				<small class="text-body-secondary"><?php esc_html_e( '11 mins ago', 'ayecode-connect' ); ?></small>
				<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="<?php esc_attr_e( 'Close', 'ayecode-connect' ); ?>"></button>
			</div>
			<div class="toast-body"><?php esc_html_e( 'Hello, this is a toast message.', 'ayecode-connect' ); ?></div>
		</div>
		<div class="toast show align-items-center text-bg-primary border-0" role="status" aria-live="polite" aria-atomic="true">
			<div class="d-flex">
				<div class="toast-body"><?php esc_html_e( 'Colored toast.', 'ayecode-connect' ); ?></div>
				<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="<?php esc_attr_e( 'Close', 'ayecode-connect' ); ?>"></button>
			</div>
		</div>
	</div>
</div>
