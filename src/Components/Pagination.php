<?php
/**
 * AyeCode UI Pagination Component
 *
 * Renders a Bootstrap pagination component.
 *
 * @package AyeCode\UI\Components
 */

namespace AyeCode\UI\Components;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pagination class.
 *
 * Renders Bootstrap 5 pagination from WP_Query or a custom links array.
 */
class Pagination {

	/**
	 * Build and return the pagination HTML.
	 *
	 * @param array $args Component arguments.
	 * @return string
	 */
	public static function get( array $args = array() ): string {
		global $wp_query;

		$defaults = array(
			'class'              => '',
			'mid_size'           => 2,
			'prev_text'          => '',
			'next_text'          => '',
			'screen_reader_text' => __( 'Posts navigation', 'ayecode-connect' ),
			'before_paging'      => '',
			'after_paging'       => '',
			'type'               => 'array',
			'total'              => isset( $wp_query->max_num_pages ) ? $wp_query->max_num_pages : 1,
			'links'              => array(),
			'rounded_style'      => false,
			'custom_next_text'   => '',
			'custom_prev_text'   => '',
		);

		$args = wp_parse_args( $args, $defaults );

		// Apply default prev/next icons only when the caller did not supply their own text.
		if ( '' === $args['prev_text'] ) {
			$args['prev_text'] = function_exists( 'ayecode_get_icon' )
				? \ayecode_get_icon( 'fas fa-chevron-left' )
				: '<i class="fas fa-chevron-left"></i>';
		}
		if ( '' === $args['next_text'] ) {
			$args['next_text'] = function_exists( 'ayecode_get_icon' )
				? \ayecode_get_icon( 'fas fa-chevron-right' )
				: '<i class="fas fa-chevron-right"></i>';
		}
		$output = '';

		if ( $args['total'] > 1 ) {
			$links = ! empty( $args['links'] ) ? $args['links'] : paginate_links( $args );
			$class = ! empty( $args['class'] ) ? $args['class'] : '';

			$custom_prev_link = '';
			$custom_next_link = '';

			$links_html = "<ul class='pagination m-0 p-0 $class'>";
			if ( ! empty( $links ) ) {
				foreach ( $links as $link ) {
					$_link       = $link;
					$link_class  = $args['rounded_style'] ? 'page-link badge rounded-pill border-0 mx-1 fs-base text-dark link-primary' : 'page-link';
					$link_active = $args['rounded_style'] ? ' current active fw-bold badge rounded-pill' : ' current active';

					$links_html .= "<li class='page-item mx-0'>";
					$link        = str_replace( array( 'page-numbers', ' current' ), array( $link_class, $link_active ), $link );
					$link        = str_replace( 'text-dark link-primary current', 'current', $link );
					$links_html .= $link;
					$links_html .= '</li>';

					if ( strpos( $_link, 'next page-numbers' ) || strpos( $_link, 'prev page-numbers' ) ) {
						$btn_link = str_replace(
							array( 'page-numbers', ' current' ),
							array( 'btn btn-outline-primary rounded' . ( $args['rounded_style'] ? '-pill' : '' ) . ' mx-1 fs-base text-dark link-primary', ' current active fw-bold badge rounded-pill' ),
							$_link
						);
						$btn_link = str_replace( 'text-dark link-primary current', 'current', $btn_link );

						if ( strpos( $_link, 'next page-numbers' ) && ! empty( $args['custom_next_text'] ) ) {
							$custom_next_link = str_replace( $args['next_text'], $args['custom_next_text'], $btn_link );
						} elseif ( strpos( $_link, 'prev page-numbers' ) && ! empty( $args['custom_prev_text'] ) ) {
							$custom_prev_link = str_replace( $args['prev_text'], $args['custom_prev_text'], $btn_link );
						}
					}
				}
			}
			$links_html .= '</ul>';

			if ( $links ) {
				$output .= '<section class="px-0 py-2 w-100">';
				$output .= _navigation_markup( $links_html, 'aui-pagination', $args['screen_reader_text'] );
				$output .= '</section>';
			}

			$output = str_replace( 'screen-reader-text', 'screen-reader-text sr-only', $output );
			$output = str_replace( 'nav-links', 'aui-nav-links', $output );
		}

		if ( $output ) {
			if ( $custom_next_link || $custom_prev_link ) {
				$current = get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1;
				$output  = '<div class="row d-flex align-items-center justify-content-between"><div class="col text-start">' . $custom_prev_link . '</div><div class="col text-center d-none d-md-block">' . $output . '</div><div class="col text-center d-md-none">' . $current . '/' . $args['total'] . '</div><div class="col text-end">' . $custom_next_link . '</div></div>';
			}

			if ( ! empty( $args['before_paging'] ) ) {
				$output = $args['before_paging'] . $output;
			}
			if ( ! empty( $args['after_paging'] ) ) {
				$output = $output . $args['after_paging'];
			}
		}

		return $output;
	}
}
