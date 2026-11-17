<?php
/**
 * Test page section: progress, spinners and placeholders.
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
		<h3 class="h5"><?php esc_html_e( 'Progress', 'ayecode-connect' ); ?></h3>
		<div class="progress mb-2" role="progressbar" aria-label="<?php esc_attr_e( 'Progress', 'ayecode-connect' ); ?>" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: 25%">25%</div></div>
		<div class="progress mb-2" role="progressbar" aria-label="<?php esc_attr_e( 'Striped progress', 'ayecode-connect' ); ?>" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped bg-success" style="width: 50%"></div></div>
		<div class="progress mb-2" role="progressbar" aria-label="<?php esc_attr_e( 'Animated progress', 'ayecode-connect' ); ?>" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: 75%"></div></div>
		<div class="progress-stacked mb-2">
			<div class="progress" role="progressbar" aria-label="<?php esc_attr_e( 'Segment one', 'ayecode-connect' ); ?>" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" style="width: 15%"><div class="progress-bar"></div></div>
			<div class="progress" role="progressbar" aria-label="<?php esc_attr_e( 'Segment two', 'ayecode-connect' ); ?>" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" style="width: 30%"><div class="progress-bar bg-success"></div></div>
			<div class="progress" role="progressbar" aria-label="<?php esc_attr_e( 'Segment three', 'ayecode-connect' ); ?>" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%"><div class="progress-bar bg-info"></div></div>
		</div>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Spinners', 'ayecode-connect' ); ?></h3>
		<div class="d-flex flex-wrap gap-3 mb-2">
			<?php foreach ( $aui_preview_colors as $aui_preview_color ) : ?>
				<div class="spinner-border text-<?php echo esc_attr( $aui_preview_color ); ?>" role="status"><span class="visually-hidden"><?php esc_html_e( 'Loading', 'ayecode-connect' ); ?></span></div>
			<?php endforeach; ?>
		</div>
		<div class="d-flex flex-wrap gap-3 mb-4">
			<div class="spinner-grow text-primary" role="status"><span class="visually-hidden"><?php esc_html_e( 'Loading', 'ayecode-connect' ); ?></span></div>
			<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden"><?php esc_html_e( 'Loading', 'ayecode-connect' ); ?></span></div>
		</div>

		<h3 class="h5"><?php esc_html_e( 'Placeholders', 'ayecode-connect' ); ?></h3>
		<div class="card" aria-hidden="true">
			<div class="card-body">
				<h4 class="card-title h5 placeholder-glow"><span class="placeholder col-6"></span></h4>
				<p class="card-text placeholder-glow">
					<span class="placeholder col-7"></span>
					<span class="placeholder col-4"></span>
					<span class="placeholder col-4"></span>
					<span class="placeholder col-6"></span>
				</p>
				<span class="btn btn-primary disabled placeholder col-4"></span>
			</div>
		</div>
	</div>
</div>
