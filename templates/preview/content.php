<?php
/**
 * AyeCode UI test page: toolbar and sections.
 *
 * @package AyeCode\UI
 *
 * @var \AyeCode\UI\Preview $aui_preview
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aui_preview_all      = $aui_preview->get_sections();
$aui_preview_sections = $aui_preview->get_shown_sections();
$aui_preview_static   = '1' === $aui_preview->get_param( 'aui-static', [ '1' ] );
$aui_preview_settings = \AyeCode\UI\Settings::instance()->get_settings();
?>
<main id="aui-preview" class="bsui">
	<div class="aui-preview-toolbar sticky-top bg-body border-bottom shadow-sm">
		<div class="container py-2 d-flex flex-wrap align-items-center gap-2">
			<strong class="me-2"><?php esc_html_e( 'AyeCode UI test page', 'ayecode-connect' ); ?></strong>

			<div class="btn-group btn-group-sm" role="group" aria-label="<?php esc_attr_e( 'Color mode', 'ayecode-connect' ); ?>">
				<button type="button" class="btn btn-outline-secondary" data-aui-preview-theme="light" aria-pressed="false"><?php esc_html_e( 'Light', 'ayecode-connect' ); ?></button>
				<button type="button" class="btn btn-outline-secondary" data-aui-preview-theme="dark" aria-pressed="false"><?php esc_html_e( 'Dark', 'ayecode-connect' ); ?></button>
				<button type="button" class="btn btn-outline-secondary" data-aui-preview-theme="auto" aria-pressed="false"><?php esc_html_e( 'Auto', 'ayecode-connect' ); ?></button>
			</div>

			<div class="btn-group btn-group-sm" role="group" aria-label="<?php esc_attr_e( 'CSS mode', 'ayecode-connect' ); ?>">
				<a class="btn btn-outline-secondary<?php echo 'core' !== $aui_preview_settings['css'] ? ' active' : ''; ?>" href="<?php echo esc_url( $aui_preview->url( [ 'aui-css' => 'compatibility' ] ) ); ?>"><?php esc_html_e( 'Compatibility', 'ayecode-connect' ); ?></a>
				<a class="btn btn-outline-secondary<?php echo 'core' === $aui_preview_settings['css'] ? ' active' : ''; ?>" href="<?php echo esc_url( $aui_preview->url( [ 'aui-css' => 'core' ] ) ); ?>"><?php esc_html_e( 'Full', 'ayecode-connect' ); ?></a>
			</div>

			<a class="btn btn-sm btn-outline-secondary<?php echo $aui_preview_static ? ' active' : ''; ?>" href="<?php echo esc_url( $aui_preview->url( [ 'aui-static' => $aui_preview_static ? null : '1' ] ) ); ?>">
				<?php $aui_preview_static ? esc_html_e( 'Animations off', 'ayecode-connect' ) : esc_html_e( 'Animations on', 'ayecode-connect' ); ?>
			</a>

			<div class="dropdown">
				<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><?php esc_html_e( 'Sections', 'ayecode-connect' ); ?></button>
				<ul class="dropdown-menu">
					<li><a class="dropdown-item" href="<?php echo esc_url( $aui_preview->url( [ 'aui-section' => null ] ) ); ?>"><?php esc_html_e( 'All sections', 'ayecode-connect' ); ?></a></li>
					<li><hr class="dropdown-divider"></li>
					<?php foreach ( $aui_preview_all as $aui_preview_id => $aui_preview_section ) : ?>
						<li>
							<a class="dropdown-item" href="<?php echo esc_url( count( $aui_preview_sections ) > 1 ? '#aui-preview-' . $aui_preview_id : $aui_preview->url( [ 'aui-section' => $aui_preview_id ] ) ); ?>"><?php echo esc_html( $aui_preview_section['title'] ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="ms-lg-auto small text-body-secondary d-flex flex-wrap gap-3">
				<?php foreach ( $aui_preview->get_info() as $aui_preview_label => $aui_preview_value ) : ?>
					<span><?php echo esc_html( $aui_preview_label ); ?>: <strong class="text-body"><?php echo esc_html( $aui_preview_value ); ?></strong></span>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div class="container py-5">
		<div class="row g-4">
			<nav class="col-lg-3 col-xl-2 d-none d-lg-block" aria-label="<?php esc_attr_e( 'Test page sections', 'ayecode-connect' ); ?>">
				<div id="aui-preview-nav" class="aui-preview-nav sticky-top overflow-y-auto">
					<div class="list-group list-group-flush small">
						<?php if ( count( $aui_preview_sections ) < 2 ) : ?>
							<a class="list-group-item list-group-item-action border-0 rounded py-1" href="<?php echo esc_url( $aui_preview->url( [ 'aui-section' => null ] ) ); ?>"><?php esc_html_e( 'All sections', 'ayecode-connect' ); ?></a>
							<hr class="my-2">
						<?php endif; ?>
						<?php foreach ( $aui_preview_all as $aui_preview_id => $aui_preview_section ) : ?>
							<?php
							$aui_preview_shown = isset( $aui_preview_sections[ $aui_preview_id ] );
							$aui_preview_href  = count( $aui_preview_sections ) > 1 ? '#aui-preview-' . $aui_preview_id : $aui_preview->url( [ 'aui-section' => $aui_preview_id ] );
							?>
							<a class="list-group-item list-group-item-action border-0 rounded py-1<?php echo ! $aui_preview_shown || count( $aui_preview_sections ) > 1 ? '' : ' active'; ?>" href="<?php echo esc_url( $aui_preview_href ); ?>">
								<?php echo esc_html( ! empty( $aui_preview_section['nav_title'] ) ? $aui_preview_section['nav_title'] : $aui_preview_section['title'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</nav>

			<div class="col-lg-9 col-xl-10">
				<?php if ( $aui_preview->site_css_disabled() ) : ?>
					<div class="alert alert-warning"><?php esc_html_e( 'AyeCode UI CSS is disabled on this site; this page loads it anyway.', 'ayecode-connect' ); ?></div>
				<?php endif; ?>

				<?php foreach ( $aui_preview_sections as $aui_preview_id => $aui_preview_section ) : ?>
					<section id="aui-preview-<?php echo esc_attr( $aui_preview_id ); ?>" class="aui-preview-section mb-5 pb-4">
						<h2 class="h3 border-bottom pb-2 mb-4 d-flex align-items-center gap-2">
							<?php echo esc_html( $aui_preview_section['title'] ); ?>
							<a class="fs-sm fw-normal ms-auto" href="<?php echo esc_url( $aui_preview->url( [ 'aui-section' => $aui_preview_id ] ) ); ?>"><?php esc_html_e( 'Only this section', 'ayecode-connect' ); ?></a>
						</h2>
						<?php call_user_func( $aui_preview_section['callback'] ); ?>
					</section>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</main>
