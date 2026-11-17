<?php
/**
 * Test page section: nav, tabs, pills, navbar, breadcrumb and pagination.
 *
 * @package AyeCode\UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="row g-4">
	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Tabs', 'ayecode-connect' ); ?></h3>
		<ul class="nav nav-tabs" role="tablist">
			<li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#aui-preview-tab1" type="button" role="tab" aria-controls="aui-preview-tab1" aria-selected="true"><?php esc_html_e( 'Home', 'ayecode-connect' ); ?></button></li>
			<li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#aui-preview-tab2" type="button" role="tab" aria-controls="aui-preview-tab2" aria-selected="false"><?php esc_html_e( 'Profile', 'ayecode-connect' ); ?></button></li>
			<li class="nav-item" role="presentation"><button class="nav-link" type="button" disabled><?php esc_html_e( 'Disabled', 'ayecode-connect' ); ?></button></li>
		</ul>
		<div class="tab-content border border-top-0 rounded-bottom p-3 mb-4">
			<div class="tab-pane fade show active" id="aui-preview-tab1" role="tabpanel" tabindex="0"><?php esc_html_e( 'First tab content.', 'ayecode-connect' ); ?></div>
			<div class="tab-pane fade" id="aui-preview-tab2" role="tabpanel" tabindex="0"><?php esc_html_e( 'Second tab content.', 'ayecode-connect' ); ?></div>
		</div>

		<h3 class="h5"><?php esc_html_e( 'Pills and underline', 'ayecode-connect' ); ?></h3>
		<ul class="nav nav-pills mb-3">
			<li class="nav-item"><a class="nav-link active" aria-current="page" href="#aui-preview-navigation"><?php esc_html_e( 'Active', 'ayecode-connect' ); ?></a></li>
			<li class="nav-item"><a class="nav-link" href="#aui-preview-navigation"><?php esc_html_e( 'Link', 'ayecode-connect' ); ?></a></li>
			<li class="nav-item"><a class="nav-link disabled" aria-disabled="true"><?php esc_html_e( 'Disabled', 'ayecode-connect' ); ?></a></li>
		</ul>
		<ul class="nav nav-underline mb-4">
			<li class="nav-item"><a class="nav-link active" aria-current="page" href="#aui-preview-navigation"><?php esc_html_e( 'Active', 'ayecode-connect' ); ?></a></li>
			<li class="nav-item"><a class="nav-link" href="#aui-preview-navigation"><?php esc_html_e( 'Link', 'ayecode-connect' ); ?></a></li>
		</ul>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Breadcrumb', 'ayecode-connect' ); ?></h3>
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="#aui-preview-navigation"><?php esc_html_e( 'Home', 'ayecode-connect' ); ?></a></li>
				<li class="breadcrumb-item"><a href="#aui-preview-navigation"><?php esc_html_e( 'Library', 'ayecode-connect' ); ?></a></li>
				<li class="breadcrumb-item active" aria-current="page"><?php esc_html_e( 'Data', 'ayecode-connect' ); ?></li>
			</ol>
		</nav>

		<h3 class="h5"><?php esc_html_e( 'Pagination', 'ayecode-connect' ); ?></h3>
		<?php foreach ( [ '', 'pagination-sm', 'pagination-lg' ] as $aui_preview_size ) : ?>
			<nav aria-label="<?php esc_attr_e( 'Pagination', 'ayecode-connect' ); ?>">
				<ul class="pagination <?php echo esc_attr( $aui_preview_size ); ?>">
					<li class="page-item disabled"><span class="page-link"><?php esc_html_e( 'Previous', 'ayecode-connect' ); ?></span></li>
					<li class="page-item"><a class="page-link" href="#aui-preview-navigation">1</a></li>
					<li class="page-item active" aria-current="page"><span class="page-link">2</span></li>
					<li class="page-item"><a class="page-link" href="#aui-preview-navigation">3</a></li>
					<li class="page-item"><a class="page-link" href="#aui-preview-navigation"><?php esc_html_e( 'Next', 'ayecode-connect' ); ?></a></li>
				</ul>
			</nav>
		<?php endforeach; ?>
	</div>

	<div class="col-12">
		<h3 class="h5"><?php esc_html_e( 'Navbar', 'ayecode-connect' ); ?></h3>
		<nav class="navbar navbar-expand-lg bg-body-tertiary rounded">
			<div class="container-fluid">
				<a class="navbar-brand" href="#aui-preview-navigation"><?php esc_html_e( 'Brand', 'ayecode-connect' ); ?></a>
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#aui-preview-navbar" aria-controls="aui-preview-navbar" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'ayecode-connect' ); ?>">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="aui-preview-navbar">
					<ul class="navbar-nav me-auto mb-2 mb-lg-0">
						<li class="nav-item"><a class="nav-link active" aria-current="page" href="#aui-preview-navigation"><?php esc_html_e( 'Home', 'ayecode-connect' ); ?></a></li>
						<li class="nav-item"><a class="nav-link" href="#aui-preview-navigation"><?php esc_html_e( 'Link', 'ayecode-connect' ); ?></a></li>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#aui-preview-navigation" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?php esc_html_e( 'Dropdown', 'ayecode-connect' ); ?></a>
							<ul class="dropdown-menu">
								<li><a class="dropdown-item" href="#aui-preview-navigation"><?php esc_html_e( 'Action', 'ayecode-connect' ); ?></a></li>
								<li><a class="dropdown-item" href="#aui-preview-navigation"><?php esc_html_e( 'Another action', 'ayecode-connect' ); ?></a></li>
							</ul>
						</li>
					</ul>
					<form class="d-flex" role="search" onsubmit="return false;">
						<input class="form-control me-2" type="search" placeholder="<?php esc_attr_e( 'Search', 'ayecode-connect' ); ?>" aria-label="<?php esc_attr_e( 'Search', 'ayecode-connect' ); ?>">
						<button class="btn btn-outline-success" type="submit"><?php esc_html_e( 'Search', 'ayecode-connect' ); ?></button>
					</form>
				</div>
			</div>
		</nav>
	</div>
</div>
