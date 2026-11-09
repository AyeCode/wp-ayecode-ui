<?php
/**
 * Functionality to convert BS4 to BS5.
 */

/**
 * Convert BS4 HTML to BS5 HTML from Super Duper output.
 *
 * @param $output
 * @param $instance
 * @param $args
 * @param $sd
 *
 * @return array|mixed|string|string[]
 */
function aui_bs_convert_sd_output( $output, $instance = '', $args = '', $sd = '' ) {
	// A Super Duper block that wraps other blocks runs this filter over its whole
	// output, children included, so a child inside one converts twice or more. Skip
	// the child: the outermost Super Duper ancestor converts the subtree once.
	if ( aui_bs_has_sd_ancestor() ) {
		return $output;
	}

	// Always convert to BS5 in v2.0+
	$convert = array(
		'"ml-'                                    => '"ms-',
		'"mr-'                                    => '"me-',
		'"pl-'                                    => '"ps-',
		'"pr-'                                    => '"pe-',
		"'ml-"                                    => "'ms-",
		"'mr-"                                    => "'me-",
		"'pl-"                                    => "'ps-",
		"'pr-"                                    => "'pe-",
		' ml-'                                    => ' ms-',
		' mr-'                                    => ' me-',
		' pl-'                                    => ' ps-',
		' pr-'                                    => ' pe-',
		'.ml-'                                    => '.ms-',
		'.mr-'                                    => '.me-',
		'.pl-'                                    => '.ps-',
		'.pr-'                                    => '.pe-',
		' form-row'                               => ' row',
		' embed-responsive-item'                  => '',
		' embed-responsive'                       => ' ratio',
		'-1by1'                                   => '-1x1',
		'-4by3'                                   => '-4x3',
		'-16by9'                                  => '-16x9',
		'-21by9'                                  => '-21x9',
		'geodir-lightbox-image'                   => 'aui-lightbox-image',
		'geodir-lightbox-iframe'                  => 'aui-lightbox-iframe',
		' badge-'                                 => ' text-bg-', // not light/dark compliant
		'form-group'                              => 'mb-3',
		'custom-select'                           => 'form-select',
		'float-left'                              => 'float-start',
		'float-right'                             => 'float-end',
		'text-left'                               => 'text-start',
		'text-sm-left'                            => 'text-sm-start',
		'text-md-left'                            => 'text-md-start',
		'text-lg-left'                            => 'text-lg-start',
		'text-right'                              => 'text-end',
		'text-sm-right'                           => 'text-sm-end',
		'text-md-right'                           => 'text-md-end',
		'text-lg-right'                           => 'text-lg-end',
		'border-right'                            => 'border-end',
		'border-left'                             => 'border-start',
		'font-weight-'                            => 'fw-',
		'btn-block'                               => 'w-100',
		'rounded-left'                            => 'rounded-start',
		'rounded-right'                           => 'rounded-end',
		'font-italic'                             => 'fst-italic',

		' data-toggle='                           => ' data-bs-toggle=',
		'data-ride='                              => 'data-bs-ride=',
		'data-controlnav='                        => 'data-bs-controlnav=',
		'data-slide='                             => 'data-bs-slide=',
		'data-slide-to='                          => 'data-bs-slide-to=',
		'data-target='                            => 'data-bs-target=',
		'data-dismiss="modal"'                    => 'data-bs-dismiss="modal"',
		'class="close"'                           => 'class="btn-close"',
		'<span aria-hidden="true">&times;</span>' => '',
	);
	$output  = str_replace(
		array_keys( $convert ),
		array_values( $convert ),
		$output
	);

	return $output;
}

add_filter( 'wp_super_duper_widget_output', 'aui_bs_convert_sd_output', 10, 4 );

/**
 * Whether a Super Duper block is rendering above the block being rendered now.
 *
 * @return bool
 */
function aui_bs_has_sd_ancestor() {
	$stack = $GLOBALS['aui_bs_render_stack'] ?? [];
	array_pop( $stack ); // The block being rendered now.

	return in_array( true, $stack, true );
}

/**
 * Track which blocks are rendering, so nested Super Duper output is converted once.
 *
 * WordPress applies render_block_data right before a block renders, top level and
 * inner blocks alike, and render_block right after, so a push on one and a pop on
 * the other is a stack of the blocks currently rendering. A block saved by Super
 * Duper carries its shortcode in the sd_shortcode attribute.
 */
add_filter(
	'render_block_data',
	function ( $parsed_block ) {
		$GLOBALS['aui_bs_render_stack'][] = ! empty( $parsed_block['attrs']['sd_shortcode'] );

		return $parsed_block;
	},
	-PHP_INT_MAX
);
add_filter(
	'render_block',
	function ( $block_content ) {
		if ( ! empty( $GLOBALS['aui_bs_render_stack'] ) ) {
			array_pop( $GLOBALS['aui_bs_render_stack'] );
		}

		return $block_content;
	},
	PHP_INT_MAX
);
