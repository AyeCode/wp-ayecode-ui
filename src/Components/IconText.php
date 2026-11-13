<?php
/**
 * AyeCode UI Icon Text Component
 *
 * Prints an icon beside text, the one way every block does it.
 *
 * @package AyeCode\UI\Components
 */

namespace AyeCode\UI\Components;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * IconText class.
 *
 * The icon stays an inline element that sits on the text by its own vertical
 * alignment, and the gap is one margin class on the icon. There is no wrapper
 * unless the caller asks for a class on one.
 */
class IconText {

	/**
	 * Build and return the icon and text HTML.
	 *
	 * @param array $args {
	 *     Component arguments.
	 *
	 *     @type string $icon       Icon identifier, e.g. `fas fa-heart`.
	 *     @type string $text       Plain text shown beside the icon. Escaped here.
	 *     @type string $content    Markup shown beside the icon in place of `text`. The caller escapes it.
	 *     @type string $position   `start` (default) or `end`.
	 *     @type string $size       A font size class such as `fs-lg`. Empty for the size of the text.
	 *     @type string $gap        0 to 5. Empty for the default of 1.
	 *     @type string $color      A theme color such as `primary` or `primary-emphasis`. Empty for the text color.
	 *     @type string $icon_class Extra classes for the icon.
	 *     @type array  $icon_attributes Extra attributes for the icon.
	 *     @type string $label      Text for screen readers when the icon has no text beside it.
	 *     @type string $class      Classes for a wrapping span. No span is printed without one.
	 * }
	 * @return string
	 */
	public static function get( array $args = [] ): string {
		$defaults = [
			'icon'            => '',
			'text'            => '',
			'content'         => '',
			'position'        => 'start',
			'size'            => '',
			'gap'             => '',
			'color'           => '',
			'icon_class'      => '',
			'icon_attributes' => [],
			'label'           => '',
			'class'           => '',
		];

		$args = wp_parse_args( $args, $defaults );
		$text = '' !== (string) $args['content'] ? (string) $args['content'] : esc_html( (string) $args['text'] );
		$end  = 'end' === $args['position'];

		$classes = [];

		// The gap only exists between an icon and its text.
		if ( '' !== $text ) {
			$gap = '' === (string) $args['gap'] ? 1 : min( 5, absint( $args['gap'] ) );
			if ( $gap > 0 ) {
				$classes[] = ( $end ? 'ms-' : 'me-' ) . $gap;
			}
		}

		if ( '' !== (string) $args['size'] ) {
			$classes[] = sanitize_html_class( $args['size'] );
		}

		if ( '' !== (string) $args['color'] ) {
			$classes[] = 'text-' . sanitize_html_class( $args['color'] );
		}

		foreach ( preg_split( '/\s+/', trim( (string) $args['icon_class'] ) ) as $extra ) {
			if ( '' !== $extra ) {
				$classes[] = sanitize_html_class( $extra );
			}
		}

		$icon = '';
		if ( '' !== (string) $args['icon'] ) {
			if ( function_exists( 'ayecode_get_icon' ) ) {
				$options = [ 'attributes' => (array) $args['icon_attributes'] ];
				if ( $classes ) {
					$options['class'] = implode( ' ', $classes );
				}
				$icon = \ayecode_get_icon( (string) $args['icon'], $options );
			} else {
				$icon = '<i class="' . esc_attr( trim( $args['icon'] . ' ' . implode( ' ', $classes ) ) ) . '" aria-hidden="true"></i>';
			}

			// An icon on its own says nothing to a screen reader.
			if ( '' !== $icon && '' === $text && '' !== (string) $args['label'] ) {
				$icon .= '<span class="visually-hidden">' . esc_html( (string) $args['label'] ) . '</span>';
			}
		}

		$output = $end ? $text . $icon : $icon . $text;

		if ( '' !== $output && '' !== trim( (string) $args['class'] ) ) {
			$wrap_classes = array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', trim( (string) $args['class'] ) ) ) );
			$output       = '<span class="' . esc_attr( implode( ' ', $wrap_classes ) ) . '">' . $output . '</span>';
		}

		return $output;
	}
}
