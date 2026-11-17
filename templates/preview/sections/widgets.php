<?php
/**
 * Test page section: carousel, lightbox, date picker, Choices select and icon picker.
 *
 * @package AyeCode\UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aui_preview_slides = [];
foreach ( [ 200, 140, 20, 330, 260, 90 ] as $aui_preview_i => $aui_preview_hue ) {
	/* translators: %d: slide number */
	$aui_preview_label    = sprintf( __( 'Slide %d', 'ayecode-connect' ), $aui_preview_i + 1 );
	$aui_preview_slides[] = '<img class="img-fluid rounded w-100" src="' . esc_attr( \AyeCode\UI\Preview::placeholder_image( 800, 450, $aui_preview_label, $aui_preview_hue ) ) . '" alt="' . esc_attr( $aui_preview_label ) . '">';
}

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- aui() components escape their own output.
?>
<div class="row g-4">
	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Carousel, one per view', 'ayecode-connect' ); ?></h3>
		<?php
		echo aui()->carousel(
			[
				'id'     => 'aui-preview-carousel-1',
				'slides' => $aui_preview_slides,
				'lazy'   => false,
				// The slides are built above from escaped values; sanitizing would strip the data URI images.
				'raw'    => true,
			]
		);
		?>
	</div>
	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Carousel, three per view with peek', 'ayecode-connect' ); ?></h3>
		<?php
		echo aui()->carousel(
			[
				'id'       => 'aui-preview-carousel-3',
				'slides'   => $aui_preview_slides,
				'per_view' => [
					'xs' => 1,
					'md' => 3,
				],
				'peek'     => 10,
				'dots'     => false,
				'lazy'     => false,
				'raw'      => true,
			]
		);
		?>
	</div>

	<div class="col-12">
		<h3 class="h5"><?php esc_html_e( 'Lightbox gallery', 'ayecode-connect' ); ?></h3>
		<div class="aui-gallery row row-cols-3 row-cols-md-6 g-2">
			<?php foreach ( [ 200, 140, 20, 330, 260, 90 ] as $aui_preview_i => $aui_preview_hue ) : ?>
				<?php
				/* translators: %d: image number */
				$aui_preview_label = sprintf( __( 'Image %d', 'ayecode-connect' ), $aui_preview_i + 1 );
				?>
				<div class="col">
					<a class="aui-lightbox-image d-block" href="<?php echo esc_attr( \AyeCode\UI\Preview::placeholder_image( 1200, 800, $aui_preview_label, $aui_preview_hue ) ); ?>">
						<img class="img-fluid rounded" src="<?php echo esc_attr( \AyeCode\UI\Preview::placeholder_image( 300, 200, $aui_preview_label, $aui_preview_hue ) ); ?>" alt="<?php echo esc_attr( $aui_preview_label ); ?>">
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Date and time pickers (flatpickr)', 'ayecode-connect' ); ?></h3>
		<?php
		echo aui()->input(
			[
				'type'       => 'datepicker',
				'id'         => 'aui-preview-date',
				'name'       => 'aui_preview_date',
				'label'      => __( 'Date', 'ayecode-connect' ),
				'label_type' => 'top',
				'value'      => '2026-01-15',
			]
		);
		echo aui()->input(
			[
				'type'             => 'datepicker',
				'id'               => 'aui-preview-range-date',
				'name'             => 'aui_preview_range_date',
				'label'            => __( 'Date range', 'ayecode-connect' ),
				'label_type'       => 'top',
				'extra_attributes' => [ 'data-mode' => 'range' ],
			]
		);
		echo aui()->input(
			[
				'type'             => 'timepicker',
				'id'               => 'aui-preview-time',
				'name'             => 'aui_preview_time',
				'label'            => __( 'Time', 'ayecode-connect' ),
				'label_type'       => 'top',
				'extra_attributes' => [
					'data-enable-time' => 'true',
					'data-no-calendar' => 'true',
				],
			]
		);
		?>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Select with search (Choices)', 'ayecode-connect' ); ?></h3>
		<?php
		echo aui()->select(
			[
				'id'         => 'aui-preview-choices',
				'name'       => 'aui_preview_choices',
				'label'      => __( 'Single', 'ayecode-connect' ),
				'label_type' => 'top',
				'select2'    => true,
				'options'    => [
					''      => __( 'Choose a fruit', 'ayecode-connect' ),
					'apple' => __( 'Apple', 'ayecode-connect' ),
					'pear'  => __( 'Pear', 'ayecode-connect' ),
					'plum'  => __( 'Plum', 'ayecode-connect' ),
				],
			]
		);
		echo aui()->select(
			[
				'id'         => 'aui-preview-choices-multiple',
				'name'       => 'aui_preview_choices_multiple',
				'label'      => __( 'Multiple', 'ayecode-connect' ),
				'label_type' => 'top',
				'select2'    => true,
				'multiple'   => true,
				'value'      => [ 'apple', 'plum' ],
				'options'    => [
					'apple' => __( 'Apple', 'ayecode-connect' ),
					'pear'  => __( 'Pear', 'ayecode-connect' ),
					'plum'  => __( 'Plum', 'ayecode-connect' ),
				],
			]
		);
		?>

		<h3 class="h5"><?php esc_html_e( 'Icon picker', 'ayecode-connect' ); ?></h3>
		<?php
		echo aui()->input(
			[
				'type'       => 'iconpicker',
				'id'         => 'aui-preview-iconpicker',
				'name'       => 'aui_preview_iconpicker',
				'label'      => __( 'Icon', 'ayecode-connect' ),
				'label_type' => 'top',
				'value'      => 'fas fa-star',
			]
		);
		?>
	</div>
</div>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
