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
	public static function get( array $args = [] ): string {
		$defaults = [
			'type'        => 'info',
			'class'       => '',
			'icon'        => '',
			'heading'     => '',
			'content'     => '',
			'footer'      => '',
			'dismissible' => false,
			'data'        => '',
		];

		$args   = wp_parse_args( $args, $defaults );
		$output = '';

		if ( ! empty( $args['content'] ) ) {
			$type = sanitize_html_class( $args['type'] );
			if ( $type === 'error' ) {
				$type = 'danger';
			}

			$icon = ! empty( $args['icon'] ) ? "<i class='" . esc_attr( $args['icon'] ) . "'></i>" : '';

			// Default icons per alert type.
			if ( ! $icon && $args['icon'] !== false && $type ) {
				if ( $type === 'danger' ) {
					$icon = '<i class="fas fa-exclamation-circle"></i>';
				} elseif ( $type === 'warning' ) {
					$icon = '<i class="fas fa-exclamation-triangle"></i>';
				} elseif ( $type === 'success' ) {
					$icon = '<i class="fas fa-check-circle"></i>';
				} elseif ( $type === 'info' ) {
					$icon = '<i class="fas fa-info-circle"></i>';
				}
			}

			$class = ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '';
			if ( $args['dismissible'] ) {
				$class .= ' alert-dismissible fade show';
			}

			$output .= '<div class="alert alert-' . $type . ' ' . $class . '" role="alert">';

			if ( ! empty( $args['heading'] ) ) {
				$output .= '<h4 class="alert-heading">' . $args['heading'] . '</h4>';
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
				$output .= '<p class="mb-0">' . $args['footer'] . '</p>';
			}

			$output .= '</div>';
		}

		return $output;
	}
}
