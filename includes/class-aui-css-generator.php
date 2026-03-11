<?php
/**
 * AyeCode UI CSS Generator
 *
 * Handles CSS generation, color manipulation, and minification for AyeCode UI.
 *
 * @since 2.0.0
 * @package AyeCode_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AUI_CSS_Generator class.
 *
 * Manages all CSS generation and color manipulation functionality.
 */
class AUI_CSS_Generator {

	/**
	 * Singleton instance.
	 *
	 * @var AUI_CSS_Generator|null
	 */
	private static ?AUI_CSS_Generator $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return AUI_CSS_Generator
	 */
	public static function instance(): AUI_CSS_Generator {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Nothing to initialize
	}

	/**
	 * Generate custom CSS for color customizations.
	 *
	 * @param bool|string $compatibility Compatibility mode setting.
	 * @param bool        $is_fse Whether this is for FSE editor.
	 * @return string Minified custom CSS.
	 */
	public static function custom_css( $compatibility = true, bool $is_fse = false ): string {
		$colors = [];

		// Get colors from BlockStrap or settings
		if ( defined( 'BLOCKSTRAP_VERSION' ) ) {
			$setting = wp_get_global_settings();

			if ( ! empty( $setting['color']['palette']['theme'] ) ) {
				foreach ( $setting['color']['palette']['theme'] as $color ) {
					$colors[ $color['slug'] ] = esc_attr( $color['color'] );
				}
			}

			if ( ! empty( $setting['color']['palette']['custom'] ) ) {
				foreach ( $setting['color']['palette']['custom'] as $color ) {
					$colors[ $color['slug'] ] = esc_attr( $color['color'] );
				}
			}
		} else {
			$settings = get_option( 'aui_options', [] );

			$colors = [
				'primary'   => $settings['color_primary'] ?? AUI_PRIMARY_COLOR,
				'secondary' => $settings['color_secondary'] ?? AUI_SECONDARY_COLOR,
			];
		}

		ob_start();
		?>
		<style>
        html[data-bs-theme="dark"] .aui-dark-mode-hide {
            display: none
        }

        html[data-bs-theme="light"] .aui-light-mode-hide {
            display: none
        }

        <?php

		$custom_front = ! is_admin() ? true : apply_filters( 'ayecode_ui_custom_front', false );
		$custom_admin = $is_fse || AyeCode_UI_Settings::is_preview() ? true : apply_filters( 'ayecode_ui_custom_admin', false );
		$bs_custom_css = apply_filters( 'ayecode_ui_bs_custom_css', $custom_admin || $custom_front );

		$colors_css = '';
		if ( ! empty( $colors ) && $bs_custom_css ) {
			$d_colors = AyeCode_UI_Settings::get_colors( true );

			foreach ( $colors as $key => $color ) {
				if ( ( empty( $d_colors[ $key ] ) || $d_colors[ $key ] != $color ) || $is_fse ) {
					$var = $is_fse ? "var(--wp--preset--color--$key)" : $color;
					$compat = $is_fse ? '.editor-styles-wrapper' : $compatibility;

					$colors_css .= self::css_overwrite( $key, $var, $compat, $color );
				}
			}
		}

		if ( $colors_css ) {
			echo $colors_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		// Set admin bar z-index lower when modal is open
		echo ' body.modal-open #wpadminbar{z-index:999}.embed-responsive-16by9 .fluid-width-video-wrapper{padding:0 !important;position:initial}';

		if ( is_admin() ) {
			echo ' body.modal-open #adminmenuwrap{z-index:999} body.modal-open #wpadminbar{z-index:1025}';
		}

		$custom_css = '';

		// BlockStrap theme integration
		if ( defined( 'BLOCKSTRAP_VERSION' ) && $bs_custom_css ) {
			$css = '';
			$theme_settings = wp_get_global_styles();

			// Font face
			if ( ! empty( $theme_settings['typography']['fontFamily'] ) ) {
				$t_fontface = str_replace(
					[ 'var:preset|', 'font-family|' ],
					[ '--wp--preset--', 'font-family--' ],
					$theme_settings['typography']['fontFamily']
				);
				$css .= '--bs-body-font-family: ' . esc_attr( $t_fontface ) . ';';
			}

			// Font size
			if ( ! empty( $theme_settings['typography']['fontSize'] ) ) {
				$css .= '--bs-body-font-size: ' . esc_attr( $theme_settings['typography']['fontSize'] ) . ' ;';
			}

			// Line height
			if ( ! empty( $theme_settings['typography']['lineHeight'] ) ) {
				$css .= '--bs-body-line-height: ' . esc_attr( $theme_settings['typography']['lineHeight'] ) . ';';
			}

			// Font weight
			if ( ! empty( $theme_settings['typography']['fontWeight'] ) ) {
				$css .= '--bs-body-font-weight: ' . esc_attr( $theme_settings['typography']['fontWeight'] ) . ';';
			}

			// Background
			if ( ! empty( $theme_settings['color']['background'] ) ) {
				$css .= '--bs-body-bg: ' . esc_attr( $theme_settings['color']['background'] ) . ';';
			}

			// Background Gradient
			if ( ! empty( $theme_settings['color']['gradient'] ) ) {
				$css .= 'background: ' . esc_attr( $theme_settings['color']['gradient'] ) . ';';
			}

			// Text color
			if ( ! empty( $theme_settings['color']['text'] ) ) {
				$css .= '--bs-body-color: ' . esc_attr( $theme_settings['color']['text'] ) . ';';
			}

			// Link colors
			if ( ! empty( $theme_settings['elements']['link']['color']['text'] ) ) {
				$css .= '--bs-link-color: ' . esc_attr( $theme_settings['elements']['link']['color']['text'] ) . ';';
			}
			if ( ! empty( $theme_settings['elements']['link'][':hover']['color']['text'] ) ) {
				$css .= '--bs-link-hover-color: ' . esc_attr( $theme_settings['elements']['link'][':hover']['color']['text'] ) . ';';
			}

			if ( $css ) {
				$custom_css .= $is_fse ? 'body.editor-styles-wrapper{' . esc_attr( $css ) . '}' : 'body{' . esc_attr( $css ) . '}';
			}

			$bep = $is_fse ? 'body.editor-styles-wrapper ' : '';

			// Headings
			$headings_css = '';
			if ( ! empty( $theme_settings['elements']['heading']['color']['text'] ) ) {
				$headings_css .= 'color: ' . esc_attr( $theme_settings['elements']['heading']['color']['text'] ) . ';';
			}

			// Heading background
			if ( ! empty( $theme_settings['elements']['heading']['color']['background'] ) ) {
				$headings_css .= 'background: ' . esc_attr( $theme_settings['elements']['heading']['color']['background'] ) . ';';
			}

			// Heading font family
			if ( ! empty( $theme_settings['elements']['heading']['typography']['fontFamily'] ) ) {
				$headings_css .= 'font-family: ' . esc_attr( $theme_settings['elements']['heading']['typography']['fontFamily'] ) . ';';
			}

			if ( $headings_css ) {
				$custom_css .= "$bep h1,$bep h2,$bep h3, $bep h4,$bep h5,$bep h6{ " . esc_attr( $headings_css ) . '}';
			}

			// Individual heading styles
			$hs = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
			foreach ( $hs as $hn ) {
				$h_css = '';
				if ( ! empty( $theme_settings['elements'][ $hn ]['color']['text'] ) ) {
					$h_css .= 'color: ' . esc_attr( $theme_settings['elements'][ $hn ]['color']['text'] ) . ';';
				}

				if ( ! empty( $theme_settings['elements'][ $hn ]['typography']['fontSize'] ) ) {
					$h_css .= 'font-size: ' . esc_attr( $theme_settings['elements'][ $hn ]['typography']['fontSize'] ) . ';';
				}

				if ( ! empty( $theme_settings['elements'][ $hn ]['typography']['fontFamily'] ) ) {
					$h_css .= 'font-family: ' . esc_attr( $theme_settings['elements'][ $hn ]['typography']['fontFamily'] ) . ';';
				}

				if ( $h_css ) {
					$custom_css .= esc_attr( $bep . $hn ) . '{' . esc_attr( $h_css ) . '}';
				}
			}
		}

		if ( $custom_css ) {
			echo $custom_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		// Pagination on Hello Elementor theme
		if ( function_exists( 'hello_elementor_setup' ) ) {
			echo '.aui-nav-links .pagination{justify-content:inherit}';
		}

		// Astra theme - WooCommerce select2 modal fix
		if ( defined( 'ASTRA_THEME_VERSION' ) ) {
			echo '.woocommerce-js.modal-open .select2-container .select2-dropdown, .woocommerce-js.modal-open .select2-container .select2-search__field, .woocommerce-page.modal-open .select2-container .select2-dropdown, .woocommerce-page.modal-open .select2-container .select2-search__field{z-index: 1056;}';
		}

		?></style><?php
		$custom_css = ob_get_clean();

		// Strip <style> tags and minify
		return str_replace( [ '<style>', '</style>' ], '', self::minify_css( $custom_css ) );
	}

	/**
	 * Build the CSS to overwrite a bootstrap color variable.
	 *
	 * @param string      $type Color type (primary, secondary, etc.).
	 * @param string      $color_code Color code or CSS variable.
	 * @param bool|string $compatibility Compatibility mode.
	 * @param string      $hex Hex color value.
	 * @return string Generated CSS.
	 */
	public static function css_overwrite( string $type, string $color_code, $compatibility, string $hex = '' ): string {
		if ( empty( $color_code ) ) {
			return '';
		}

		$is_var = strpos( $color_code, 'var' ) !== false;
		$is_custom = strpos( $type, 'custom-' ) !== false;

		if ( $is_var ) {
			$color_code = esc_attr( $color_code );
		}

		$rgb = self::hex_to_rgb( $hex );

		// Handle compatibility prefix
		if ( $compatibility === true || $compatibility === 1 ) {
			$compatibility = '.bsui';
		} elseif ( ! $compatibility ) {
			$compatibility = '';
		} else {
			$compatibility = esc_attr( $compatibility );
		}

		$prefix = $compatibility ? $compatibility . ' ' : '';
		$type = sanitize_html_class( $type );

		$output = '';

		/**
		 * c = color, b = background color, o = border-color, f = fill
		 */
		$selectors = [
			".btn-{$type}"                                              => [ 'b', 'o' ],
			".btn-{$type}.disabled"                                     => [ 'b', 'o' ],
			".btn-{$type}:disabled"                                     => [ 'b', 'o' ],
			".btn-outline-{$type}"                                      => [ 'c', 'o' ],
			".btn-outline-{$type}:hover"                                => [ 'b', 'o' ],
			".btn-outline-{$type}:not(:disabled):not(.disabled).active" => [ 'b', 'o' ],
			".btn-outline-{$type}:not(:disabled):not(.disabled):active" => [ 'b', 'o' ],
			".show>.btn-outline-{$type}.dropdown-toggle"                => [ 'b', 'o' ],
			".badge-{$type}"                                            => [ 'b' ],
			".bg-{$type}"                                               => [ 'b', 'f' ],
			".btn-link.btn-{$type}"                                     => [ 'c' ],
			".text-{$type}"                                             => [ 'c' ],
		];

		if ( $type === 'primary' ) {
			$selectors += [
				'a'                            => [ 'c' ],
				'.btn-link'                    => [ 'c' ],
				'.dropdown-item.active'        => [ 'b' ],
				'.nav-pills .nav-link.active'  => [ 'b' ],
				'.nav-pills .show>.nav-link'   => [ 'b' ],
				'.page-link'                   => [ 'c' ],
				'.page-item.active .page-link' => [ 'b', 'o' ],
				'.progress-bar'                => [ 'b' ],
				'.list-group-item.active'      => [ 'b', 'o' ],
			];
		}

		// Link colors
		if ( $type === 'primary' ) {
			$output .= 'html body {--bs-link-hover-color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .75); --bs-link-color: var(--bs-' . esc_attr( $type ) . '); }';
			$output .= $prefix . ' .breadcrumb{--bs-breadcrumb-item-active-color: ' . esc_attr( $color_code ) . ';  }';
			$output .= $prefix . ' .navbar { --bs-nav-link-hover-color: ' . esc_attr( $color_code ) . '; --bs-navbar-hover-color: ' . esc_attr( $color_code ) . '; --bs-navbar-active-color: ' . esc_attr( $color_code ) . '; }';
			$output .= $prefix . ' a{color: var(--bs-' . esc_attr( $type ) . ');}';
			$output .= $prefix . ' .text-primary{color: var(--bs-' . esc_attr( $type ) . ') !important;}';
			$output .= $prefix . ' .dropdown-menu{--bs-dropdown-link-hover-color: var(--bs-' . esc_attr( $type ) . '); --bs-dropdown-link-active-color: var(--bs-' . esc_attr( $type ) . ');}';
//			$output .= $prefix . ' .pagination{--bs-pagination-hover-color: var(--bs-' . esc_attr( $type ) . '); --bs-pagination-active-bg: var(--bs-' . esc_attr( $type ) . ');}'; // @todo this seems not needed in new ver
		}

		$output .= $prefix . ' .link-' . esc_attr( $type ) . ' {color: var(--bs-' . esc_attr( $type ) . '-rgb) !important;}';
		$output .= $prefix . ' .link-' . esc_attr( $type ) . ':hover {color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .8) !important;}';

		// Button styles
		$output .= $prefix . ' .btn-' . esc_attr( $type ) . '{';
		$output .= '
		--bs-btn-bg: ' . esc_attr( $color_code ) . ';
		--bs-btn-border-color: ' . esc_attr( $color_code ) . ';
		--bs-btn-hover-bg: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-hover-border-color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-focus-shadow-rgb: --bs-' . esc_attr( $type ) . '-rgb;
		--bs-btn-active-bg: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-active-border-color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-active-shadow: unset;
		--bs-btn-disabled-bg: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .5);
		--bs-btn-disabled-border-color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .1);
		';
		$output .= '}';

		// Button outline styles
		$output .= $prefix . ' .btn-outline-' . esc_attr( $type ) . '{';
		$output .= '
		--bs-btn-color: ' . esc_attr( $color_code ) . ';
		--bs-btn-border-color: ' . esc_attr( $color_code ) . ';
		--bs-btn-hover-bg: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-hover-border-color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-focus-shadow-rgb: --bs-' . esc_attr( $type ) . '-rgb;
		--bs-btn-active-bg: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-active-border-color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-active-shadow: unset;
		--bs-btn-disabled-bg: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .5);
		--bs-btn-disabled-border-color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .1);
		';
		$output .= '}';

		// Button hover shadow
		$output .= $prefix . ' .btn-' . esc_attr( $type ) . ':hover{';
		$output .= ' box-shadow: 0 0.25rem 0.25rem 0.125rem rgb(var(--bs-' . esc_attr( $type ) . '-rgb), .1), 0 0.375rem 0.75rem -0.125rem rgb(var(--bs-' . esc_attr( $type ) . '-rgb) , .4);}';

		// Set CSS variables
		$output .= 'html body {--bs-' . esc_attr( $type ) . ': ' . esc_attr( $color_code ) . '; }';
		$output .= 'html body {--bs-' . esc_attr( $type ) . '-rgb: ' . $rgb . '; }';

		// Custom color selectors
		if ( $is_custom ) {
			$color = [];
			$background = [];
			$border = [];
			$fill = [];

			foreach ( $selectors as $selector => $types ) {
				$selector = $compatibility ? $compatibility . ' ' . $selector : $selector;
				$types = array_combine( $types, $types );
				if ( isset( $types['c'] ) ) {
					$color[] = $selector;
				}
				if ( isset( $types['b'] ) ) {
					$background[] = $selector;
				}
				if ( isset( $types['o'] ) ) {
					$border[] = $selector;
				}
				if ( isset( $types['f'] ) ) {
					$fill[] = $selector;
				}
			}

			if ( ! empty( $color ) ) {
				$output .= implode( ',', $color ) . "{color: $color_code;} ";
			}
			if ( ! empty( $background ) ) {
				$output .= implode( ',', $background ) . "{background-color: $color_code;} ";
			}
			if ( ! empty( $border ) ) {
				$output .= implode( ',', $border ) . "{border-color: $color_code;} ";
			}
			if ( ! empty( $fill ) ) {
				$output .= implode( ',', $fill ) . "{fill: $color_code;} ";
			}
		}

		// Button states
		$transition = $is_var ? 'transition: color 0.15s ease-in-out,background-color 0.15s ease-in-out,border-color 0.15s ease-in-out,box-shadow 0.15s ease-in-out,filter 0.15s ease-in-out;' : '';
		$darker_075 = $is_var ? $color_code . ';filter:brightness(0.925)' : self::css_hex_lighten_darken( $color_code, '-0.075' );
		$darker_10 = $is_var ? $color_code . ';filter:brightness(0.9)' : self::css_hex_lighten_darken( $color_code, '-0.10' );
		$darker_125 = $is_var ? $color_code . ';filter:brightness(0.875)' : self::css_hex_lighten_darken( $color_code, '-0.125' );
		$op_25 = $color_code . '40'; // 25% opacity

		$output .= $is_var ? $prefix . " .btn-{$type}{{$transition }} " : '';
		$output .= $prefix . " .btn-{$type}:hover, $prefix .btn-{$type}:focus, $prefix .btn-{$type}.focus{background-color: " . $darker_075 . ';    border-color: ' . $darker_10 . ';} ';
		$output .= $prefix . " .btn-outline-{$type}:not(:disabled):not(.disabled):active:focus, $prefix .btn-outline-{$type}:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-outline-{$type}.dropdown-toggle:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";
		$output .= $prefix . " .btn-{$type}:not(:disabled):not(.disabled):active, $prefix .btn-{$type}:not(:disabled):not(.disabled).active, .show>$prefix .btn-{$type}.dropdown-toggle{background-color: " . $darker_10 . ';    border-color: ' . $darker_125 . ';} ';
		$output .= $prefix . " .btn-{$type}:not(:disabled):not(.disabled):active:focus, $prefix .btn-{$type}:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-{$type}.dropdown-toggle:focus {box-shadow: 0 0 0 0.2rem $op_25;} ";
		$output .= $prefix . " .btn-{$type}:not(:disabled):not(.disabled):active:focus, $prefix .btn-{$type}:not(:disabled):not(.disabled):focus {box-shadow: 0 0.25rem 0.25rem 0.125rem rgba(var(--bs-{$type}-rgb), 0.1), 0 0.375rem 0.75rem -0.125rem rgba(var(--bs-{$type}-rgb), 0.4);} ";

		// Alerts
		$output .= $prefix . " .alert-{$type} {--bs-alert-bg: rgba(var(--bs-{$type}-rgb), .1 ) !important;--bs-alert-border-color: rgba(var(--bs-{$type}-rgb), .25 ) !important;--bs-alert-color: rgba(var(--bs-{$type}-rgb), 1 ) !important;} ";

		return $output;
	}

	/**
	 * Convert hex color to RGB values.
	 *
	 * @param string $hex Hex color code.
	 * @return string RGB values as comma-separated string.
	 */
	public static function hex_to_rgb( string $hex ): string {
		// Remove '#' if present
		$hex = str_replace( '#', '', $hex );

		// Check if input is already RGB
		if ( strpos( $hex, 'rgba(' ) === 0 || strpos( $hex, 'rgb(' ) === 0 ) {
			$_rgb = explode( ',', str_replace( [ 'rgba(', 'rgb(', ')' ], '', $hex ) );

			$rgb = ( isset( $_rgb[0] ) ? (int) trim( $_rgb[0] ) : '0' ) . ',';
			$rgb .= ( isset( $_rgb[1] ) ? (int) trim( $_rgb[1] ) : '0' ) . ',';
			$rgb .= ( isset( $_rgb[2] ) ? (int) trim( $_rgb[2] ) : '0' );

			return $rgb;
		}

		// Convert 3-digit hex to 6-digit hex
		if ( strlen( $hex ) === 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
		}

		// Convert hex to RGB
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		return $r . ',' . $g . ',' . $b;
	}

	/**
	 * Lighten or darken a hex color by a percentage.
	 *
	 * @param string $hexCode Hex color code (with or without #).
	 * @param float  $adjustPercent Adjustment percentage (-1 to 1). Positive = lighter, negative = darker.
	 * @return string Modified hex color.
	 */
	public static function css_hex_lighten_darken( string $hexCode, $adjustPercent ): string {
		$hexCode = ltrim( $hexCode, '#' );

		// Return unchanged if already RGB
		if ( strpos( $hexCode, 'rgba(' ) !== false || strpos( $hexCode, 'rgb(' ) !== false ) {
			return $hexCode;
		}

		// Convert 3-digit to 6-digit hex
		if ( strlen( $hexCode ) === 3 ) {
			$hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
		}

		$hexCode = array_map( 'hexdec', str_split( $hexCode, 2 ) );

		foreach ( $hexCode as &$color ) {
			$adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
			$adjustAmount = ceil( $adjustableLimit * $adjustPercent );

			$color = str_pad( dechex( $color + $adjustAmount ), 2, '0', STR_PAD_LEFT );
		}

		return '#' . implode( $hexCode );
	}

	/**
	 * Minify CSS code.
	 *
	 * @param string $input CSS code to minify.
	 * @return string Minified CSS.
	 */
	public static function minify_css( string $input ): string {
		if ( trim( $input ) === '' ) {
			return $input;
		}

		return preg_replace(
			[
				// Remove comment(s)
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
				// Remove unused white-space(s)
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
				// Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
				'#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
				// Replace `:0 0 0 0` with `:0`
				'#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
				// Replace `background-position:0` with `background-position:0 0`
				'#(background-position):0(?=[;\}])#si',
				// Replace `0.6` with `.6`
				'#(?<=[\s:,\-])0+\.(\d+)#s',
				// Minify string value
				'#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
				'#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
				// Minify HEX color code
				'#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
				// Replace `(border|outline):none` with `(border|outline):0`
				'#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
				// Remove empty selector(s)
				'#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s',
			],
			[
				'$1',
				'$1$2$3$4$5$6$7',
				'$1',
				':0',
				'$1:0 0',
				'.$1',
				'$1$3',
				'$1$2$4$5',
				'$1$2$3',
				'$1:0',
				'$1$2',
			],
			$input
		);
	}

	/**
	 * Minify JavaScript code.
	 *
	 * @param string $input JavaScript code to minify.
	 * @return string Minified JavaScript.
	 */
	public static function minify_js( string $input ): string {
		if ( trim( $input ) === '' ) {
			return $input;
		}

		return preg_replace(
			[
				// Remove comment(s)
				'#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
				// Remove white-space(s) outside the string and regex
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
				// Remove the last semicolon
				'#;+\}#',
				// Minify object attribute(s)
				'#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
				// From `foo['bar']` to `foo.bar`
				'#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i',
			],
			[
				'$1',
				'$1$2',
				'}',
				'$1$3',
				'$1.$3',
			],
			$input
		);
	}
}
