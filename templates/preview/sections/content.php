<?php
/**
 * Test page section: alerts, badges, cards, list groups and tables.
 *
 * @package AyeCode\UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aui_preview_colors = [ 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark' ];
?>
<div class="row g-4">
	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Alerts', 'ayecode-connect' ); ?></h3>
		<?php foreach ( $aui_preview_colors as $aui_preview_color ) : ?>
			<div class="alert alert-<?php echo esc_attr( $aui_preview_color ); ?> py-2" role="alert">
				alert-<?php echo esc_html( $aui_preview_color ); ?> <?php esc_html_e( 'with', 'ayecode-connect' ); ?> <a href="#aui-preview-content" class="alert-link"><?php esc_html_e( 'a link', 'ayecode-connect' ); ?></a>
			</div>
		<?php endforeach; ?>
		<div class="alert alert-warning alert-dismissible fade show" role="alert">
			<?php esc_html_e( 'Dismissible alert.', 'ayecode-connect' ); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?php esc_attr_e( 'Close', 'ayecode-connect' ); ?>"></button>
		</div>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Badges', 'ayecode-connect' ); ?></h3>
		<div class="d-flex flex-wrap gap-2 mb-2">
			<?php foreach ( $aui_preview_colors as $aui_preview_color ) : ?>
				<span class="badge text-bg-<?php echo esc_attr( $aui_preview_color ); ?>"><?php echo esc_html( $aui_preview_color ); ?></span>
			<?php endforeach; ?>
		</div>
		<div class="d-flex flex-wrap align-items-start gap-2 mb-3">
			<?php foreach ( $aui_preview_colors as $aui_preview_color ) : ?>
				<span class="badge rounded-pill bg-<?php echo esc_attr( $aui_preview_color ); ?>-subtle text-<?php echo esc_attr( $aui_preview_color ); ?>-emphasis border border-<?php echo esc_attr( $aui_preview_color ); ?>-subtle"><?php echo esc_html( $aui_preview_color ); ?></span>
			<?php endforeach; ?>
		</div>
		<div class="mb-4">
			<button type="button" class="btn btn-primary position-relative">
				<?php esc_html_e( 'Inbox', 'ayecode-connect' ); ?>
				<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">9</span>
			</button>
		</div>

		<h3 class="h5"><?php esc_html_e( 'List group', 'ayecode-connect' ); ?></h3>
		<div class="list-group mb-4">
			<a href="#aui-preview-content" class="list-group-item list-group-item-action active" aria-current="true"><?php esc_html_e( 'Active item', 'ayecode-connect' ); ?></a>
			<a href="#aui-preview-content" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"><?php esc_html_e( 'Item with badge', 'ayecode-connect' ); ?> <span class="badge text-bg-primary rounded-pill">14</span></a>
			<a href="#aui-preview-content" class="list-group-item list-group-item-action list-group-item-success"><?php esc_html_e( 'Success item', 'ayecode-connect' ); ?></a>
			<span class="list-group-item disabled" aria-disabled="true"><?php esc_html_e( 'Disabled item', 'ayecode-connect' ); ?></span>
		</div>
	</div>

	<div class="col-12">
		<h3 class="h5"><?php esc_html_e( 'Cards', 'ayecode-connect' ); ?></h3>
		<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
			<div class="col">
				<div class="card h-100">
					<img src="<?php echo esc_attr( \AyeCode\UI\Preview::placeholder_image( 600, 360, '600 x 360', 210 ) ); ?>" class="card-img-top" alt="">
					<div class="card-body">
						<h4 class="card-title h5"><?php esc_html_e( 'Card title', 'ayecode-connect' ); ?></h4>
						<p class="card-text"><?php esc_html_e( 'Some quick text to build on the card title.', 'ayecode-connect' ); ?></p>
						<a href="#aui-preview-content" class="btn btn-primary btn-sm stretched-link"><?php esc_html_e( 'Stretched link', 'ayecode-connect' ); ?></a>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card h-100">
					<div class="card-header"><?php esc_html_e( 'Header', 'ayecode-connect' ); ?></div>
					<div class="card-body">
						<p class="card-text"><?php esc_html_e( 'Card with a header and a footer.', 'ayecode-connect' ); ?></p>
					</div>
					<div class="card-footer text-body-secondary"><?php esc_html_e( 'Footer', 'ayecode-connect' ); ?></div>
				</div>
			</div>
			<div class="col">
				<div class="card h-100 text-bg-dark">
					<img src="<?php echo esc_attr( \AyeCode\UI\Preview::placeholder_image( 600, 400, '', 260 ) ); ?>" class="card-img" alt="">
					<div class="card-img-overlay">
						<h4 class="card-title h5"><?php esc_html_e( 'Image overlay', 'ayecode-connect' ); ?></h4>
						<p class="card-text text-shadow"><?php esc_html_e( 'Text over an image, with text-shadow.', 'ayecode-connect' ); ?></p>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card h-100 border-primary">
					<div class="card-body">
						<h4 class="card-title h5 text-primary"><?php esc_html_e( 'Bordered', 'ayecode-connect' ); ?></h4>
						<h5 class="card-subtitle h6 mb-2 text-body-secondary"><?php esc_html_e( 'Subtitle', 'ayecode-connect' ); ?></h5>
						<a href="#aui-preview-content" class="card-link"><?php esc_html_e( 'Card link', 'ayecode-connect' ); ?></a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-12">
		<h3 class="h5"><?php esc_html_e( 'Tables', 'ayecode-connect' ); ?></h3>
		<div class="table-responsive">
			<table class="table table-striped table-hover align-middle">
				<thead>
					<tr><th scope="col">#</th><th scope="col"><?php esc_html_e( 'Name', 'ayecode-connect' ); ?></th><th scope="col"><?php esc_html_e( 'Status', 'ayecode-connect' ); ?></th><th scope="col"><?php esc_html_e( 'Amount', 'ayecode-connect' ); ?></th></tr>
				</thead>
				<tbody>
					<tr><th scope="row">1</th><td><?php esc_html_e( 'First row', 'ayecode-connect' ); ?></td><td><span class="badge text-bg-success"><?php esc_html_e( 'Paid', 'ayecode-connect' ); ?></span></td><td>$120.00</td></tr>
					<tr class="table-active"><th scope="row">2</th><td><?php esc_html_e( 'Active row', 'ayecode-connect' ); ?></td><td><span class="badge text-bg-warning"><?php esc_html_e( 'Pending', 'ayecode-connect' ); ?></span></td><td>$80.00</td></tr>
					<tr class="table-danger"><th scope="row">3</th><td><?php esc_html_e( 'Danger row', 'ayecode-connect' ); ?></td><td><span class="badge text-bg-danger"><?php esc_html_e( 'Failed', 'ayecode-connect' ); ?></span></td><td>$45.50</td></tr>
				</tbody>
			</table>
		</div>
		<table class="table table-sm table-bordered">
			<tbody>
				<tr><td>table-sm</td><td>table-bordered</td></tr>
			</tbody>
		</table>
	</div>
</div>
