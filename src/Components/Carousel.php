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
			'slides'       => array(),
			'thumbnails'   => array(),
			'id'           => '',
			'class'        => '',
			'slide_class'  => '',
			'aria_label'   => '',
			'arrows'       => true,
			'arrow_offset' => '',
			'dots'         => true,
			'autoplay'     => false,
			'fade'         => false,
			'auto_height'  => false,
			'loop'         => false,
			'align'        => 'start',
			'per_view'     => 1,
			'scroll'       => 1,
			'gap'          => 3,
			'lazy'         => true,
			'raw'          => false,
			'peek'         => '',
			'edge_fade'    => false,
			'auto_scroll'  => 0,
			'options'      => array(),
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

		// Part of the next slide shows at the end: the row is narrower than the viewport by the peek
		// percent, so the row-cols maths keeps the chosen columns inside the rest and the next slide
		// fills the strip. Embla takes its view size from the container it is handed, so the narrow row
		// sits inside a full width container and the script points Embla at the slides within it.
		// The gutter's negative margin moves from the row to that container: Embla takes the distance
		// to a slide as always positive, so a first slide that starts before its container lands one
		// gutter off its snap. On the container the margin is clipped by the viewport as it is on a
		// plain row, and the first slide starts exactly at the container's edge.
		$peek       = '';
		$track_open = '';
		if ( ! $args['fade'] && $args['peek'] ) {
			$aliases  = array(
				'small' => 12,
				'large' => 25,
			);
			$peek_pct = isset( $aliases[ $args['peek'] ] ) ? $aliases[ $args['peek'] ] : min( 50, absint( $args['peek'] ) );

			if ( $peek_pct ) {
				$peek       = ' style="width:' . ( 100 - $peek_pct ) . '%"';
				$row_class .= ' mx-0';
				$track_open = '<div class="gx-' . self::gap( $args['gap'] ) . '" data-aui-carousel-peek="' . (int) $peek_pct . '" style="margin:0 calc(var(--bs-gutter-x) * -.5)">';
			}
		}
		$track_close = $peek ? '</div>' : '';

		// The slides fade into the background at the edges through a mask on the viewport. With a peek
		// it is the edge the next or previous slide peeks at, both when the current slide is centered,
		// and the fade covers the peek strip so the current slide stays sharp. Without a peek the
		// slides pass under both edges, so both fade at the class's own width. A percent overrides.
		$viewport_class = 'aui-carousel-viewport overflow-hidden';
		$viewport_style = '';
		if ( $args['edge_fade'] && ! $args['fade'] ) {
			$sides           = array(
				'center' => 'x',
				'end'    => 'start',
			);
			$viewport_class .= ' mask-fade-' . ( $peek && isset( $sides[ $args['align'] ] ) ? $sides[ $args['align'] ] : ( $peek ? 'end' : 'x' ) );

			if ( true !== $args['edge_fade'] ) {
				$viewport_style = ' style="--bs-mask-fade:' . min( 50, max( 1, absint( $args['edge_fade'] ) ) ) . '%"';
			} elseif ( $peek ) {
				$viewport_style = ' style="--bs-mask-fade:' . (float) ( 'center' === $args['align'] ? $peek_pct / 2 : $peek_pct ) . '%"';
			}
		}

		// Images in the slides that show first load as normal; the script loads the rest as they come into view.
		$eager = $args['fade'] ? 1 : self::max_per_view( $args['per_view'] ) + ( $peek ? 1 : 0 );

		$slides_html = '';
		foreach ( $slides as $i => $slide ) {
			$deferred = $defer_slides && $i > 0 ? ' d-none" data-aui-carousel-deferred="' : '';
			$slide    = $args['raw'] ? $slide : Helper::sanitize_html_field( $slide );

			if ( $args['lazy'] && $count > 1 && $i >= $eager ) {
				$slide = self::lazy_images( $slide );
			}

			$slides_html .= '<div class="aui-carousel-slide' . $slide_class . $deferred . '">' . $slide . '</div>';
		}

		$root_class = 'aui-carousel position-relative';
		if ( ! empty( $args['class'] ) ) {
			$root_class .= ' ' . Helper::esc_classes( $args['class'] );
		}

		// Arrow placement: on the slide edges, on the edges only while hovered, in a row above on the
		// end side, or 'external' for buttons the caller prints elsewhere with self::arrows().
		$arrows = true === $args['arrows'] ? 'edge' : $args['arrows'];
		if ( ! in_array( $arrows, array( 'edge', 'hover', 'end', 'external' ), true ) ) {
			$arrows = '';
		}

		// Edge arrows sit a spacer step in from the slide edges: a Bootstrap spacer 0 to 12, or n1 to n12
		// to push them out past the edges, over the page. Two is the default.
		$arrow_offset = preg_match( '/^n?(1[0-2]|[0-9])$/', (string) $args['arrow_offset'] ) ? (string) $args['arrow_offset'] : '2';

		$aria_label = $args['aria_label'] ? $args['aria_label'] : __( 'Carousel', 'ayecode-connect' );

		// A single slide needs no script, so it is printed as static markup and nothing is enqueued.
		if ( 1 === $count ) {
			return '<div' . Helper::id( $args['id'] ) . ' class="' . esc_attr( $root_class ) . '">'
				. '<div class="' . esc_attr( $viewport_class ) . '"' . $viewport_style . '>' . $track_open . '<div class="' . esc_attr( $row_class ) . '"' . $peek . '>' . $slides_html . '</div>' . $track_close . '</div>'
				. '</div>';
		}

		AssetManager::instance()->enqueue_carousel();

		$output  = '<div' . Helper::id( $args['id'] ) . ' class="' . esc_attr( $root_class ) . '"';
		$output .= " data-aui-carousel='" . esc_attr( wp_json_encode( self::config( $args ) ) ) . "'>";

		if ( 'end' === $arrows ) {
			$output .= '<div class="aui-carousel-arrows d-flex justify-content-end gap-2 mb-3">' . self::arrow( 'prev', $arrows ) . self::arrow( 'next', $arrows ) . '</div>';
		}

		$output .= '<div class="aui-carousel-main position-relative' . ( 'hover' === $arrows ? ' hover-effect-opacity' : '' ) . '">';
		// The accessibility plugin makes the viewport the labelled region, so the label lives there.
		$output .= '<div class="' . esc_attr( $viewport_class ) . '"' . $viewport_style . ' role="region" aria-roledescription="' . esc_attr__( 'carousel', 'ayecode-connect' ) . '" aria-label="' . esc_attr( $aria_label ) . '">';
		$output .= $track_open . '<div class="' . esc_attr( $row_class ) . '"' . $peek . '>' . $slides_html . '</div>' . $track_close . '</div>';

		if ( 'edge' === $arrows || 'hover' === $arrows ) {
			$output .= self::arrow( 'prev', $arrows, $arrow_offset ) . self::arrow( 'next', $arrows, $arrow_offset );
		}

		$output .= '</div>';

		if ( $args['dots'] && empty( $args['thumbnails'] ) ) {
			$output .= self::dots( $count );
		}

		if ( ! empty( $args['thumbnails'] ) && is_array( $args['thumbnails'] ) ) {
			$output .= self::thumbnails( array_slice( array_values( $args['thumbnails'] ), 0, $count ), (bool) $args['raw'] );
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
		} elseif ( $args['auto_scroll'] ) {
			// Pixels per frame for the auto scroll plugin: 1 is slow, 2 is its default, 4 is fast.
			// A negative speed runs it backward.
			$speed                = true === $args['auto_scroll'] ? 2 : (float) $args['auto_scroll'];
			$config['autoScroll'] = ( $speed < 0 ? -1 : 1 ) * min( 10, max( 0.1, round( abs( $speed ), 1 ) ) );
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
	 * Previous and next buttons for a carousel printed elsewhere, such as a block's title row.
	 *
	 * The carousel must have the same id and `'arrows' => 'external'`. The script finds the
	 * buttons by the id, so they can sit anywhere on the page.
	 *
	 * @param string $carousel_id The carousel's id.
	 * @return string
	 */
	public static function arrows( string $carousel_id ): string {
		if ( '' === $carousel_id ) {
			return '';
		}

		return '<div class="aui-carousel-arrows d-flex gap-2" data-aui-carousel-for="' . esc_attr( sanitize_html_class( $carousel_id ) ) . '">' . self::arrow( 'prev', 'end' ) . self::arrow( 'next', 'end' ) . '</div>';
	}

	/**
	 * Build a previous or next arrow button.
	 *
	 * @param string $direction 'prev' or 'next'.
	 * @param string $placement 'edge', 'hover' or 'end'.
	 * @param string $offset    Spacer step between an edge arrow and the slide edge, n1 to n12 for outside.
	 * @return string
	 */
	private static function arrow( string $direction, string $placement = 'edge', string $offset = '2' ): string {
		$is_prev = 'prev' === $direction;
		$label   = $is_prev ? __( 'Previous slide', 'ayecode-connect' ) : __( 'Next slide', 'ayecode-connect' );

		if ( 'end' === $placement ) {
			$side = '';
		} else {
			$side = 'position-absolute top-50 translate-middle-y z-1 ' . ( $is_prev ? 'start-0 ms-' . $offset : 'end-0 me-' . $offset );
			if ( 'hover' === $placement ) {
				$side .= ' hover-effect-target opacity-0';
			}
		}

		// Point the chevrons the other way in RTL, where the previous arrow sits on the right.
		$points_left = is_rtl() ? ! $is_prev : $is_prev;
		$icon        = Helper::icon( $points_left ? 'fas fa-chevron-left fs-lg animate-target' : 'fas fa-chevron-right fs-lg animate-target', false, array( 'aria-hidden' => 'true' ) );

		// Round icon button on a body coloured disc with a light border, which reads on both light and dark slides.
		// On hover the chevron slides out the way it points and back in from the other side.
		$side .= $points_left ? ' animate-slide-start' : ' animate-slide-end';

		return '<button type="button" class="aui-carousel-' . esc_attr( $direction ) . ' btn btn-icon btn-outline-secondary bg-body border-secondary-subtle rounded-circle ' . esc_attr( trim( $side ) ) . '" aria-label="' . esc_attr( $label ) . '">' . $icon . '</button>';
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
	 * @param bool  $raw        Print the thumbnail HTML as given instead of running it through kses.
	 * @return string
	 */
	private static function thumbnails( array $thumbnails, bool $raw = false ): string {
		$output = '<div class="aui-carousel-thumbs overflow-hidden mt-2"><div class="row flex-nowrap gx-2 gy-0 row-cols-4 row-cols-sm-5 row-cols-md-6">';

		foreach ( $thumbnails as $i => $thumbnail ) {
			$state   = 0 === $i ? ' aria-current="true"' : '';
			$output .= '<div><button type="button" class="btn p-0 border-0 w-100' . ( 0 === $i ? '' : ' opacity-50' ) . '"' . $state . ' aria-label="' . esc_attr( sprintf( /* translators: %d: slide number. */ __( 'Go to slide %d', 'ayecode-connect' ), $i + 1 ) ) . '">'
				. preg_replace( '/<img\b(?![^>]*\sloading=)/i', '<img loading="lazy" decoding="async"', $raw ? $thumbnail : Helper::sanitize_html_field( $thumbnail ) ) . '</button></div>';
		}

		return $output . '</div></div>';
	}
}
