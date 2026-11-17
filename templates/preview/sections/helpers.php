<?php
/**
 * Test page section: the classes the block settings output.
 *
 * @package AyeCode\UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="row g-4">
	<div class="col-12">
		<h3 class="h5"><?php esc_html_e( 'Entrance animations (scroll down to this point to see them play)', 'ayecode-connect' ); ?></h3>
		<div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
			<?php foreach ( [ 'fade', 'fade-up', 'fade-down', 'fade-start', 'fade-end', 'zoom-in' ] as $aui_preview_i => $aui_preview_effect ) : ?>
				<div class="col">
					<div class="aui-preview-box aui-reveal aui-reveal-<?php echo esc_attr( $aui_preview_effect ); ?> aui-reveal-delay-<?php echo esc_attr( (string) [ 100, 200, 300, 500, 750, 1000 ][ $aui_preview_i ] ); ?> d-flex align-items-center justify-content-center rounded bg-primary-subtle text-primary-emphasis small p-2 text-center">
						<?php echo esc_html( $aui_preview_effect ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Hover animations', 'ayecode-connect' ); ?></h3>
		<div class="d-flex flex-wrap gap-2 mb-3">
			<?php foreach ( [ 'hover-zoom', 'hover-shadow', 'hover-move-up', 'hover-move-down', 'hover-move-left', 'hover-move-right' ] as $aui_preview_class ) : ?>
				<div class="aui-preview-box <?php echo esc_attr( $aui_preview_class ); ?> d-flex align-items-center justify-content-center border rounded bg-body px-3 small"><?php echo esc_html( $aui_preview_class ); ?></div>
			<?php endforeach; ?>
		</div>
		<div class="d-flex flex-wrap gap-2">
			<?php foreach ( [ 'animate-shake' => 'fa-solid fa-bell', 'animate-pulse' => 'fa-solid fa-heart', 'animate-rotate' => 'fa-solid fa-rotate', 'animate-scale' => 'fa-solid fa-star', 'animate-slide-end' => 'fa-solid fa-arrow-right', 'animate-slide-up' => 'fa-solid fa-arrow-up' ] as $aui_preview_class => $aui_preview_icon ) : ?>
				<button type="button" class="btn btn-outline-secondary btn-sm <?php echo esc_attr( $aui_preview_class ); ?>">
					<?php echo \AyeCode\UI\Preview::icon( $aui_preview_icon, 'animate-target me-1' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup. ?>
					<?php echo esc_html( $aui_preview_class ); ?>
				</button>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Show on hover (hover-effect-opacity)', 'ayecode-connect' ); ?></h3>
		<div class="row g-3">
			<div class="col-6">
				<div class="card hover-effect-opacity hover-effect-scale overflow-hidden">
					<div class="position-relative">
						<img class="card-img-top hover-effect-target" src="<?php echo esc_attr( \AyeCode\UI\Preview::placeholder_image( 400, 260, '', 160 ) ); ?>" alt="">
						<div class="position-absolute top-0 end-0 z-1 hover-effect-target opacity-0 m-2">
							<button type="button" class="btn btn-sm btn-icon btn-light rounded-circle animate-pulse" aria-label="<?php esc_attr_e( 'Favorite', 'ayecode-connect' ); ?>">
								<?php echo \AyeCode\UI\Preview::icon( 'fa-regular fa-heart', 'animate-target' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup. ?>
							</button>
						</div>
					</div>
					<div class="card-body py-2 small"><?php esc_html_e( 'Heart shows on hover', 'ayecode-connect' ); ?></div>
				</div>
			</div>
			<div class="col-6">
				<div class="card hover-effect-opacity">
					<div class="card-body small">
						<span class="hover-effect-target opacity-100 d-block"><?php esc_html_e( 'Hides on hover', 'ayecode-connect' ); ?></span>
						<a href="#aui-preview-helpers" class="hover-effect-underline"><?php esc_html_e( 'hover-effect-underline', 'ayecode-connect' ); ?></a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Background opacity', 'ayecode-connect' ); ?></h3>
		<?php foreach ( [ 'bg-primary', 'bg-primary-subtle', 'bg-dark' ] as $aui_preview_bg ) : ?>
			<div class="d-flex gap-1 mb-2 p-1 rounded bg-dotted">
				<?php foreach ( [ 100, 80, 60, 40, 20, 10 ] as $aui_preview_opacity ) : ?>
					<div class="flex-fill rounded p-2 small text-center <?php echo esc_attr( $aui_preview_bg ); ?> bg-opacity-<?php echo esc_attr( (string) $aui_preview_opacity ); ?> <?php echo 'bg-primary-subtle' === $aui_preview_bg ? 'text-primary-emphasis' : 'text-white'; ?>"><?php echo esc_html( $aui_preview_opacity . '%' ); ?></div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
		<p class="small text-body-secondary"><?php esc_html_e( 'Each row should fade from solid to almost clear, in light and dark mode.', 'ayecode-connect' ); ?></p>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Backdrop blur', 'ayecode-connect' ); ?></h3>
		<div class="rounded overflow-hidden p-3 d-flex gap-2" style="background: repeating-linear-gradient(45deg, var(--bs-primary) 0 8px, var(--bs-warning) 8px 16px);">
			<?php foreach ( [ '', 'backdrop-blur-sm', 'backdrop-blur-md', 'backdrop-blur-lg' ] as $aui_preview_blur ) : ?>
				<div class="flex-fill rounded p-3 small text-center bg-light-subtle bg-opacity-50 <?php echo esc_attr( $aui_preview_blur ); ?>"><?php echo esc_html( $aui_preview_blur ? $aui_preview_blur : __( 'No blur', 'ayecode-connect' ) ); ?></div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="col-12">
		<h3 class="h5"><?php esc_html_e( 'Border radius', 'ayecode-connect' ); ?></h3>
		<div class="d-flex flex-wrap gap-2 mb-2">
			<?php foreach ( [ 'rounded-0', 'rounded-1', 'rounded-2', 'rounded-3', 'rounded-4', 'rounded-5', 'rounded-6', 'rounded-7', 'rounded-pill', 'rounded-circle' ] as $aui_preview_class ) : ?>
				<div class="aui-preview-box d-flex align-items-center justify-content-center bg-secondary-subtle text-secondary-emphasis small px-3 <?php echo esc_attr( $aui_preview_class ); ?>" style="min-width:6rem"><?php echo esc_html( $aui_preview_class ); ?></div>
			<?php endforeach; ?>
		</div>
		<div class="d-flex flex-wrap gap-2">
			<?php foreach ( [ 'rounded rounded-0 rounded-md-pill' => 'rounded-0 rounded-md-pill', 'rounded rounded-0 rounded-lg-5' => 'rounded-0 rounded-lg-5', 'rounded-sm' => 'rounded-sm (BS4)', 'rounded-lg' => 'rounded-lg (BS4)', 'rounded-right' => 'rounded-right (BS4)', 'rounded-left' => 'rounded-left (BS4)' ] as $aui_preview_class => $aui_preview_label ) : ?>
				<div class="aui-preview-box d-flex align-items-center justify-content-center bg-info-subtle text-info-emphasis small px-3 <?php echo esc_attr( $aui_preview_class ); ?>" style="min-width:10rem"><?php echo esc_html( $aui_preview_label ); ?></div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Spacing 6 to 12', 'ayecode-connect' ); ?></h3>
		<div class="d-flex align-items-start gap-1 bg-body-tertiary rounded">
			<?php foreach ( [ 6, 7, 8, 9, 10, 11, 12 ] as $aui_preview_space ) : ?>
				<div class="bg-success-subtle rounded">
					<div class="pt-<?php echo (int) $aui_preview_space; ?> bg-success rounded-top" style="width:2.5rem"></div>
					<div class="small text-center"><?php echo (int) $aui_preview_space; ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Icon boxes', 'ayecode-connect' ); ?></h3>
		<div class="d-flex flex-wrap align-items-center gap-3">
			<?php foreach ( [ 'extrasmall', 'small', 'smallmedium', 'medium', 'large' ] as $aui_preview_size ) : ?>
				<span class="iconbox icon<?php echo esc_attr( $aui_preview_size ); ?> rounded-circle border-0 iconbox-translucent text-primary">
					<?php echo \AyeCode\UI\Preview::icon( 'fa-solid fa-star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup. ?>
				</span>
			<?php endforeach; ?>
			<span class="iconbox iconmedium rounded-3 border-0 bg-success text-white">
				<?php echo \AyeCode\UI\Preview::icon( 'fa-solid fa-check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup. ?>
			</span>
		</div>
	</div>
</div>
