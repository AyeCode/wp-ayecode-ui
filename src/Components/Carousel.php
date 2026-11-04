<?php
/**
 * AyeCode UI Carousel Component
 *
 * Renders an Embla Carousel slider.
 *
 * @package AyeCode\UI\Components
 */

namespace AyeCode\UI\Components;

use AyeCode\UI\AssetManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Carousel class.
 *
 * Builds the markup for the Embla based carousel and enqueues its script. The
 * markup lays out correctly before the script runs: the slides sit in a
 * non-wrapping Bootstrap row inside an overflow-hidden viewport, so the first
 * slide shows at its final size with no layout shift.
 */
class Carousel {

	/**
	 * Breakpoints accepted by the per_view argument, in Bootstrap order.
	 *
	 * @var string[]
	 */
	private const BREAKPOINTS = array( 'xs', 'sm', 'md', 'lg', 'xl', 'xxl' );

	/**
	 * Transparent 1x1 GIF that holds the place of a lazy slide image until the script loads it.
	 *
	 * @var string
	 */
	public const PLACEHOLDER = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';

	/**
	 * Build and return the carousel HTML.
	 *
	 * @param array $args Component arguments.
	 * @return string
	 */
	public static function get( array $args = array() ): string {
		$defaults = array(
			'slides'      => array(),
			'thumbnails'  => array(),
			'id'          => '',
			'class'       => '',
			'slide_class' => '',
			'aria_label'  => '',
			'arrows'      => true,
			'dots'        => true,
			'autoplay'    => false,
			'fade'        => false,
			'auto_height' => false,
			'loop'        => false,
			'align'       => 'start',
			'per_view'    => 1,
			'scroll'      => 1,
			'gap'         => 3,
			'lazy'        => true,
			'options'     => array(),
		);

		$args   = wp_parse_args( $args, $defaults );
		$slides = array_values( array_filter( (array) $args['slides'], 'strlen' ) );
		$count  = count( $slides );

		if ( ! $count ) {
			return '';
		}

		$slide_class = ! empty( $args['slide_class'] ) ? ' ' . Helper::esc_classes( $args['slide_class'] ) : '';
		$row_class   = 'row flex-nowrap gy-0 gx-' . self::gap( $args['gap'] ) . ' ' . self::per_view_classes( $args['fade'] ? 1 : $args['per_view'] );

		if ( $args['auto_height'] ) {
			$row_class .= ' align-items-start';
		}

		// With auto height and one slide per view, the row would be as tall as the tallest slide
		// until the script runs. Hiding the later slides keeps it at the first slide's height,
		// and the script shows them again just before it starts Embla.
		$defer_slides = $args['auto_height'] && ( $args['fade'] || 1 === self::max_per_view( $args['per_view'] ) );

		// Images in the slides that show first load as normal; the script loads the rest as they come into view.
		$eager = $args['fade'] ? 1 : self::max_per_view( $args['per_view'] );

		$slides_html = '';
		foreach ( $slides as $i => $slide ) {
			$deferred = $defer_slides && $i > 0 ? ' d-none" data-aui-carousel-deferred="' : '';
			$slide    = Helper::sanitize_html_field( $slide );

			if ( $args['lazy'] && $count > 1 && $i >= $eager ) {
				$slide = self::lazy_images( $slide );
			}

			$slides_html .= '<div class="aui-carousel-slide' . $slide_class . $deferred . '">' . $slide . '</div>';
		}

		$root_class = 'aui-carousel position-relative';
		if ( ! empty( $args['class'] ) ) {
			$root_class .= ' ' . Helper::esc_classes( $args['class'] );
		}

		$aria_label = $args['aria_label'] ? $args['aria_label'] : __( 'Carousel', 'ayecode-connect' );

		// A single slide needs no script, so it is printed as static markup and nothing is enqueued.
		if ( 1 === $count ) {
			return '<div' . Helper::id( $args['id'] ) . ' class="' . esc_attr( $root_class ) . '">'
				. '<div class="aui-carousel-viewport overflow-hidden"><div class="' . esc_attr( $row_class ) . '">' . $slides_html . '</div></div>'
				. '</div>';
		}

		AssetManager::instance()->enqueue_carousel();

		$output  = '<div' . Helper::id( $args['id'] ) . ' class="' . esc_attr( $root_class ) . '"';
		$output .= " data-aui-carousel='" . esc_attr( wp_json_encode( self::config( $args ) ) ) . "'>";

		$output .= '<div class="aui-carousel-main position-relative">';
		// The accessibility plugin makes the viewport the labelled region, so the label lives there.
		$output .= '<div class="aui-carousel-viewport overflow-hidden" role="region" aria-roledescription="' . esc_attr__( 'carousel', 'ayecode-connect' ) . '" aria-label="' . esc_attr( $aria_label ) . '">';
		$output .= '<div class="' . esc_attr( $row_class ) . '">' . $slides_html . '</div></div>';

		if ( $args['arrows'] ) {
			$output .= self::arrow( 'prev' ) . self::arrow( 'next' );
		}

		$output .= '</div>';

		if ( $args['dots'] && empty( $args['thumbnails'] ) ) {
			$output .= self::dots( $count );
		}

		if ( ! empty( $args['thumbnails'] ) && is_array( $args['thumbnails'] ) ) {
			$output .= self::thumbnails( array_slice( array_values( $args['thumbnails'] ), 0, $count ) );
		}

		$output .= '<div class="aui-carousel-live visually-hidden"></div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Build the data-aui-carousel config: Embla options plus our feature switches.
	 *
	 * @param array $args Parsed component arguments.
	 * @return array
	 */
	private static function config( array $args ): array {
		$config = array(
			'loop'  => (bool) $args['loop'],
			'align' => in_array( $args['align'], array( 'start', 'center', 'end' ), true ) ? $args['align'] : 'start',
		);

		// Embla moves one slide at a time unless told otherwise; 'auto' groups the slides in view into a page.
		if ( 'page' === $args['scroll'] ) {
			$config['slidesToScroll'] = 'auto';
		} elseif ( absint( $args['scroll'] ) > 1 ) {
			$config['slidesToScroll'] = absint( $args['scroll'] );
		}

		if ( $args['autoplay'] ) {
			$config['autoplay'] = true === $args['autoplay'] ? 5000 : absint( $args['autoplay'] );
		}

		if ( $args['fade'] ) {
			$config['fade'] = true;
		}

		if ( $args['auto_height'] ) {
			$config['autoHeight'] = true;
		}

		if ( is_array( $args['options'] ) ) {
			$config = array_merge( $config, $args['options'] );
		}

		return $config;
	}

	/**
	 * Convert a per_view value into Bootstrap row-cols classes.
	 *
	 * Accepts an int for every screen size, or an array keyed by breakpoint,
	 * e.g. array( 'xs' => 1, 'md' => 2, 'lg' => 3 ).
	 *
	 * @param int|array $per_view Slides per view.
	 * @return string
	 */
	public static function per_view_classes( $per_view ): string {
		if ( ! is_array( $per_view ) ) {
			$per_view = array( 'xs' => $per_view );
		}

		$classes = array();
		foreach ( self::BREAKPOINTS as $breakpoint ) {
			if ( ! isset( $per_view[ $breakpoint ] ) ) {
				continue;
			}

			$cols      = max( 1, min( 6, absint( $per_view[ $breakpoint ] ) ) );
			$classes[] = 'xs' === $breakpoint ? 'row-cols-' . $cols : 'row-cols-' . $breakpoint . '-' . $cols;
		}

		return $classes ? implode( ' ', $classes ) : 'row-cols-1';
	}

	/**
	 * Get the largest number of slides per view across all breakpoints.
	 *
	 * @param int|array $per_view Slides per view.
	 * @return int
	 */
	private static function max_per_view( $per_view ): int {
		$values = is_array( $per_view ) ? array_map( 'absint', $per_view ) : array( absint( $per_view ) );

		return $values ? max( 1, max( $values ) ) : 1;
	}

	/**
	 * Hand the image sources of a slide to the script, which sets them back as the slide
	 * comes into view. A tiny GIF keeps the place so the box and alt text behave as before.
	 *
	 * @param string $html Slide HTML.
	 * @return string
	 */
	private static function lazy_images( string $html ): string {
		return preg_replace_callback(
			'/<img\b[^>]*\ssrc=[^>]*>/i',
			function ( $m ) {
				$tag = preg_replace( '/\s(src|srcset)=/i', ' data-$1=', $m[0] );
				$tag = preg_replace( '/\s(loading|fetchpriority)=(["\'])[^"\']*\2/i', '', $tag );

				return str_replace( '<img', '<img src="' . self::PLACEHOLDER . '"', $tag );
			},
			$html
		);
	}

	/**
	 * Clamp a gap value to a Bootstrap gutter step.
	 *
	 * @param mixed $gap Gap value.
	 * @return int
	 */
	private static function gap( $gap ): int {
		return max( 0, min( 5, absint( $gap ) ) );
	}

	/**
	 * Build a previous or next arrow button.
	 *
	 * @param string $direction 'prev' or 'next'.
	 * @return string
	 */
	private static function arrow( string $direction ): string {
		$is_prev = 'prev' === $direction;
		$label   = $is_prev ? __( 'Previous slide', 'ayecode-connect' ) : __( 'Next slide', 'ayecode-connect' );
		$side    = $is_prev ? 'start-0 ms-2' : 'end-0 me-2';

		// Point the chevrons the other way in RTL, where the previous arrow sits on the right.
		$points_left = is_rtl() ? ! $is_prev : $is_prev;
		$icon        = Helper::icon( $points_left ? 'fas fa-chevron-left' : 'fas fa-chevron-right', false, array( 'aria-hidden' => 'true' ) );

		return '<button type="button" class="aui-carousel-' . esc_attr( $direction ) . ' btn btn-light shadow-sm rounded-circle position-absolute top-50 translate-middle-y z-1 ' . esc_attr( $side ) . '" aria-label="' . esc_attr( $label ) . '">' . $icon . '</button>';
	}

	/**
	 * Build the dot buttons. One per slide is printed so the row has its final
	 * height before the script runs; the script trims them to the number of snaps.
	 *
	 * @param int $count Number of slides.
	 * @return string
	 */
	private static function dots( int $count ): string {
		$output = '<div class="aui-carousel-dots d-flex flex-wrap justify-content-center mt-2">';

		for ( $i = 0; $i < $count; $i++ ) {
			$dot_class = 0 === $i ? 'bg-primary' : 'bg-secondary bg-opacity-25';
			$output   .= '<button type="button" class="btn p-1 border-0 lh-1" aria-label="' . esc_attr( sprintf( /* translators: %d: slide number. */ __( 'Go to slide %d', 'ayecode-connect' ), $i + 1 ) ) . '">'
				. '<span class="d-block p-1 rounded-circle ' . $dot_class . '"></span></button>';
		}

		return $output . '</div>';
	}

	/**
	 * Build the thumbnail strip, which runs as a second synced Embla instance.
	 *
	 * @param array $thumbnails Thumbnail HTML, one per slide.
	 * @return string
	 */
	private static function thumbnails( array $thumbnails ): string {
		$output = '<div class="aui-carousel-thumbs overflow-hidden mt-2"><div class="row flex-nowrap gx-2 gy-0 row-cols-4 row-cols-sm-5 row-cols-md-6">';

		foreach ( $thumbnails as $i => $thumbnail ) {
			$state   = 0 === $i ? ' aria-current="true"' : '';
			$output .= '<div><button type="button" class="btn p-0 border-0 w-100' . ( 0 === $i ? '' : ' opacity-50' ) . '"' . $state . ' aria-label="' . esc_attr( sprintf( /* translators: %d: slide number. */ __( 'Go to slide %d', 'ayecode-connect' ), $i + 1 ) ) . '">'
				. preg_replace( '/<img\b(?![^>]*\sloading=)/i', '<img loading="lazy" decoding="async"', Helper::sanitize_html_field( $thumbnail ) ) . '</button></div>';
		}

		return $output . '</div></div>';
	}
}
