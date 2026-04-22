<?php
/**
 * AyeCode UI Button Component
 *
 * Renders a Bootstrap button, link, or badge component.
 *
 * @package AyeCode\UI\Components
 */

namespace AyeCode\UI\Components;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Button class.
 *
 * Renders a Bootstrap 5 button, anchor, or badge.
 */
class Button {

	/**
	 * Build and return the button HTML.
	 *
	 * @param array $args Component arguments.
	 * @return string
	 */
	public static function get( array $args = [] ): string {
		$defaults = [
			'type'                   => 'a',
			'href'                   => '#',
			'new_window'             => false,
			'class'                  => 'btn btn-primary',
			'id'                     => '',
			'title'                  => '',
			'value'                  => '',
			'content'                => '',
			'icon'                   => '',
			'hover_content'          => '',
			'hover_icon'             => '',
			'new_line_after'         => true,
			'no_wrap'                => true,
			'onclick'                => '',
			'style'                  => '',
			'extra_attributes'       => [],
			'icon_extra_attributes'  => [],
		];

		$args   = wp_parse_args( $args, $defaults );
		$output = '';

		if ( ! empty( $args['type'] ) ) {
			$type = $args['type'] !== 'a' ? esc_attr( $args['type'] ) : 'a';

			if ( $type === 'a' ) {
				$new_window = ! empty( $args['new_window'] ) ? ' target="_blank" ' : '';
				$output    .= '<a href="' . $args['href'] . '"' . $new_window;
			} elseif ( $type === 'badge' ) {
				$output .= '<span ';
			} else {
				$output .= '<button type="' . $type . '" ';
			}

			if ( ! empty( $args['name'] ) ) {
				$output .= Helper::name( $args['name'] );
			}
			if ( ! empty( $args['id'] ) ) {
				$output .= Helper::id( $args['id'] );
			}
			if ( ! empty( $args['title'] ) ) {
				$output .= Helper::title( $args['title'] );
			}
			if ( ! empty( $args['value'] ) ) {
				$output .= Helper::value( $args['value'] );
			}

			$class   = ! empty( $args['class'] ) ? $args['class'] : '';
			$output .= Helper::class_attr( $class );
			$output .= Helper::data_attributes( $args );
			$output .= Helper::aria_attributes( $args );

			if ( ! empty( $args['extra_attributes'] ) ) {
				$output .= Helper::extra_attributes( $args['extra_attributes'] );
			}

			if ( ! empty( $args['onclick'] ) ) {
				$output .= ' onclick="' . $args['onclick'] . '" ';
			}
			if ( ! empty( $args['style'] ) ) {
				$output .= ' style="' . $args['style'] . '" ';
			}

			$output .= ' >';

			$hover_content = false;
			if ( ! empty( $args['hover_content'] ) || ! empty( $args['hover_icon'] ) ) {
				$output       .= "<span class='hover-content'>" . Helper::icon( $args['hover_icon'], $args['hover_content'] ) . $args['hover_content'] . "</span>";
				$hover_content = true;
			}

			if ( $hover_content ) {
				$output .= "<span class='hover-content-original'>";
			}
			if ( ! empty( $args['content'] ) || ! empty( $args['icon'] ) ) {
				$output .= Helper::icon( $args['icon'], $args['content'], $args['icon_extra_attributes'] ) . $args['content'];
			}
			if ( $hover_content ) {
				$output .= "</span>";
			}

			if ( $type === 'a' ) {
				$output .= '</a>';
			} elseif ( $type === 'badge' ) {
				$output .= '</span>';
			} else {
				$output .= '</button>';
			}

			if ( ! empty( $args['new_line_after'] ) ) {
				$output .= PHP_EOL;
			}

			if ( ! $args['no_wrap'] ) {
				$output = Input::wrap( [ 'content' => $output ] );
			}
		}

		return $output;
	}
}
