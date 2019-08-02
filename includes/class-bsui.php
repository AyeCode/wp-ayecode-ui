<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class BSUI {

	public static function alert( $args = array() ) {
		$defaults = array(
			'type'       => 'info',
			'class'      => '',
			'icon_class' => '',
			'heading'    => '',
			'content'    => '',
			'footer'     => '',
			'dismissible'=> false,
			'data'       => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args   = wp_parse_args( $args, $defaults );
		$output = '';
		if ( ! empty( $args['content'] ) ) {
			$data = '';
			$class = !empty($args['class']) ? esc_attr($args['class']) : '';
			if($args['dismissible']){$class .= " alert-dismissible fade show";}

			// open
			$output .= '<div class="alert alert-' . sanitize_html_class( $args['type'] ) . ' '.$class.'" role="alert" '.$data.'>';

			// heading
			if ( ! empty( $args['heading'] ) ) {
				$output .= '<h4 class="alert-heading">' . $args['heading'] . '</h4>';
			}

			// icon
			if ( ! empty( $args['icon_class'] ) ) {
				$output .= '<i class="' . $args['icon_class'] . '"></i>';
			}

			// content
			$output .= $args['content'];

			// dismissible
			if($args['dismissible']){
				$output .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
				$output .= '<span aria-hidden="true">&times;</span>';
				$output .= '</button>';
			}

			// footer
			if ( ! empty( $args['footer'] ) ) {
				$output .= '<hr>';
				$output .= '<p class="mb-0">' . $args['footer'] . '</p>';
			}

			// close
			$output .= '</div>';
		}

		return $output;
	}

}