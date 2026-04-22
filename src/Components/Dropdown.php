<?php
/**
 * AyeCode UI Dropdown Component
 *
 * Renders a Bootstrap dropdown component.
 *
 * @package AyeCode\UI\Components
 */

namespace AyeCode\UI\Components;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dropdown class.
 *
 * Renders a Bootstrap 5 dropdown with a trigger button and menu items.
 */
class Dropdown {

	/**
	 * Build and return the dropdown HTML.
	 *
	 * @param array $args Component arguments.
	 * @return string
	 */
	public static function get( array $args = [] ): string {
		$defaults = [
			'type'                  => 'button',
			'href'                  => '#',
			'class'                 => 'btn btn-primary dropdown-toggle',
			'wrapper_class'         => '',
			'dropdown_menu_class'   => '',
			'id'                    => '',
			'title'                 => '',
			'value'                 => '',
			'content'               => '',
			'icon'                  => '',
			'hover_content'         => '',
			'hover_icon'            => '',
			'data-toggle'           => 'dropdown',
			'aria-haspopup'         => 'true',
			'aria-expanded'         => 'false',
			'dropdown_menu'         => '',
			'dropdown_items'        => [],
		];

		$args   = wp_parse_args( $args, $defaults );
		$output = '';

		if ( ! empty( $args['type'] ) ) {
			$output .= '<div class="dropdown ' . Helper::esc_classes( $args['wrapper_class'] ) . '">';
			$output .= aui()->button( $args );

			if ( ! empty( $args['dropdown_menu'] ) ) {
				$output .= $args['dropdown_menu'];
			} elseif ( ! empty( $args['dropdown_items'] ) ) {
				$output .= '<div class="dropdown-menu ' . Helper::esc_classes( $args['dropdown_menu_class'] ) . '" aria-labelledby="' . sanitize_html_class( $args['id'] ) . '">';
				$output .= aui()->render( $args['dropdown_items'] );
				$output .= '</div>';
			}

			$output .= '</div>';
		}

		return $output;
	}
}
