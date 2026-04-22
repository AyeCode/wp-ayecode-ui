<?php
/**
 * AyeCode UI Alert Component
 *
 * Renders a Bootstrap alert component.
 *
 * @package AyeCode\UI\Components
 */

namespace AyeCode\UI\Components;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Alert class.
 *
 * Renders a Bootstrap 5 alert with optional icon, heading, content, and dismiss button.
 */
class Alert {

	/**
	 * Build and return the alert HTML.
	 *
	 * @param array $args Component arguments.
	 * @return string
	 */
	public static function get( array $args = array() ): string {
		$defaults = array(
			'type'        => 'info',
			'class'       => '',
			'icon'        => '',
			'heading'     => '',
			'content'     => '',
			'footer'      => '',
			'dismissible' => false,
			'data'        => '',
		);

		$args   = wp_parse_args( $args, $defaults );
		$output = '';

		if ( ! empty( $args['content'] ) ) {
			$type = sanitize_html_class( $args['type'] );
			if ( 'error' === $type ) {
				$type = 'danger';
			}

			$icon = '';
			if ( ! empty( $args['icon'] ) ) {
				$icon = function_exists( 'ayecode_get_icon' )
					? \ayecode_get_icon( $args['icon'] )
					: '<i class="' . esc_attr( $args['icon'] ) . '"></i>';
			}

			// Default icons per alert type.
			if ( ! $icon && false !== $args['icon'] && $type ) {
				$default_icons = array(
					'danger'  => 'fas fa-exclamation-circle',
					'warning' => 'fas fa-exclamation-triangle',
					'success' => 'fas fa-check-circle',
					'info'    => 'fas fa-info-circle',
				);
				if ( isset( $default_icons[ $type ] ) ) {
					$icon_name = $default_icons[ $type ];
					$icon      = function_exists( 'ayecode_get_icon' )
						? \ayecode_get_icon( $icon_name )
						: '<i class="' . esc_attr( $icon_name ) . '"></i>';
				}
			}

			$class = ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '';
			if ( $args['dismissible'] ) {
				$class .= ' alert-dismissible fade show';
			}

			$output .= '<div class="alert alert-' . esc_attr( $type ) . ' ' . esc_attr( $class ) . '" role="alert">';

			if ( ! empty( $args['heading'] ) ) {
				$output .= '<h4 class="alert-heading">' . wp_kses_post( $args['heading'] ) . '</h4>';
			}

			if ( ! empty( $icon ) ) {
				$output .= $icon . ' ';
			}

			$output .= $args['content'];

			if ( $args['dismissible'] ) {
				$output .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="' . esc_attr__( 'Close', 'ayecode-connect' ) . '"></button>';
			}

			if ( ! empty( $args['footer'] ) ) {
				$output .= '<hr>';
				$output .= '<p class="mb-0">' . wp_kses_post( $args['footer'] ) . '</p>';
			}

			$output .= '</div>';
		}

		return $output;
	}
}
