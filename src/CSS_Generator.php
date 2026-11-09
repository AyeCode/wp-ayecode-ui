<?php
/**
 * AyeCode UI CSS Generator
 *
 * Handles CSS generation, color manipulation, and minification for AyeCode UI.
 *
 * @package AyeCode\UI
 */

namespace AyeCode\UI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSS_Generator class.
 *
 * Manages all CSS generation and color manipulation functionality.
 */
class CSS_Generator {

	/**
	 * Theme colors whose classes the stylesheet builds from the color variable at
	 * runtime (assets/scss/_color-runtime.scss and the brand loops in the partials),
	 * so only the variable and a readable text color need writing out.
	 */
	private const RUNTIME_COLORS = [ 'primary', 'success', 'danger', 'warning', 'info' ];


	/**
	 * Singleton instance.
	 *
	 * @var CSS_Generator|null
	 */
	private static ?CSS_Generator $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return CSS_Generator
	 */
	public static function instance(): CSS_Generator {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {}

	/**
	 * Generate custom CSS for color customizations.
	 *
	 * @param bool|string $compatibility Compatibility mode setting.
	 * @param bool        $is_fse        Whether this is for the FSE editor.
	 * @return string Minified custom CSS.
	 */
	public static function custom_css( $compatibility = true, bool $is_fse = false ): string {
		$colors        = [];
		$custom_colors = [];

		if ( defined( 'BLOCKSTRAP_VERSION' ) ) {
			$setting = wp_get_global_settings();

			if ( ! empty( $setting['color']['palette']['theme'] ) ) {
				foreach ( $setting['color']['palette']['theme'] as $color ) {
					$slug = sanitize_key( $color['slug'] );

					if ( in_array( $slug, SettingsOrchestrator::THEME_COLORS, true ) ) {
						$colors[ $slug ] = self::sanitize_css_color( (string) $color['color'] );
					}
				}
			}

			// Colors added by the user or a design kit are offered in the block color
			// pickers, so they get the same classes as a theme color.
			foreach ( SettingsOrchestrator::get_added_palette_colors() as $slug => $color ) {
				$colors[ $slug ]        = self::sanitize_css_color( $color['color'] );
				$custom_colors[ $slug ] = true;
			}

			$colors = array_filter( $colors );
		} else {
			$settings = get_option( 'aui_options', [] );

			// The option is trusted by the customizer on write, but not on read.
			$primary   = self::sanitize_css_color( (string) ( $settings['color_primary'] ?? '' ) );
			$secondary = self::sanitize_css_color( (string) ( $settings['color_secondary'] ?? '' ) );

			$colors = [
				'primary'   => '' !== $primary ? $primary : AUI_PRIMARY_COLOR,
				'secondary' => '' !== $secondary ? $secondary : AUI_SECONDARY_COLOR,
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
		$custom_admin = $is_fse || SettingsOrchestrator::is_preview() ? true : apply_filters( 'ayecode_ui_custom_admin', false );
		$bs_custom_css = apply_filters( 'ayecode_ui_bs_custom_css', $custom_admin || $custom_front );

		$colors_css = '';
		if ( ! empty( $colors ) && $bs_custom_css ) {
			$d_colors = SettingsOrchestrator::get_colors( true );

			foreach ( $colors as $key => $color ) {
				if ( $is_fse || empty( $d_colors[ $key ] ) || strtolower( $d_colors[ $key ] ) !== strtolower( $color ) ) {
					$var   = $is_fse ? 'var(--wp--preset--color--' . sanitize_key( $key ) . ')' : $color;
					$compat = $is_fse ? '.editor-styles-wrapper' : $compatibility;

					$colors_css .= isset( $custom_colors[ $key ] )
						? self::custom_color_css( $key, $var, $compat, $color )
						: self::css_overwrite( $key, $var, $compat, $color );
				}
			}
		}

		if ( $colors_css ) {
			echo $colors_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo ' body.modal-open #wpadminbar{z-index:999}.embed-responsive-16by9 .fluid-width-video-wrapper{padding:0 !important;position:initial}';

		if ( is_admin() ) {
			echo ' body.modal-open #adminmenuwrap{z-index:999} body.modal-open #wpadminbar{z-index:1025}';
		}

		$custom_css = '';

		if ( defined( 'BLOCKSTRAP_VERSION' ) && $bs_custom_css ) {
			$css           = '';
			$theme_settings = wp_get_global_styles();

			if ( ! empty( $theme_settings['typography']['fontFamily'] ) ) {
				$t_fontface = str_replace(
					[ 'var:preset|', 'font-family|' ],
					[ '--wp--preset--', 'font-family--' ],
					$theme_settings['typography']['fontFamily']
				);
				$css .= '--bs-body-font-family: ' . esc_attr( $t_fontface ) . ';';
			}

			if ( ! empty( $theme_settings['typography']['fontSize'] ) ) {
				$css .= '--bs-body-font-size: ' . esc_attr( $theme_settings['typography']['fontSize'] ) . ' ;';
			}

			if ( ! empty( $theme_settings['typography']['lineHeight'] ) ) {
				$css .= '--bs-body-line-height: ' . esc_attr( $theme_settings['typography']['lineHeight'] ) . ';';
			}

			if ( ! empty( $theme_settings['typography']['fontWeight'] ) ) {
				$css .= '--bs-body-font-weight: ' . esc_attr( $theme_settings['typography']['fontWeight'] ) . ';';
			}

			if ( ! empty( $theme_settings['color']['background'] ) ) {
				$css .= '--bs-body-bg: ' . esc_attr( $theme_settings['color']['background'] ) . ';';
			}

			if ( ! empty( $theme_settings['color']['gradient'] ) ) {
				$css .= 'background: ' . esc_attr( $theme_settings['color']['gradient'] ) . ';';
			}

			if ( ! empty( $theme_settings['color']['text'] ) ) {
				$css .= '--bs-body-color: ' . esc_attr( $theme_settings['color']['text'] ) . ';';
			}

			if ( ! empty( $theme_settings['elements']['link']['color']['text'] ) && self::is_defined_preset_color( $theme_settings['elements']['link']['color']['text'] ) ) {
				$css .= '--bs-link-color: ' . esc_attr( $theme_settings['elements']['link']['color']['text'] ) . ';';
			}
			if ( ! empty( $theme_settings['elements']['link'][':hover']['color']['text'] ) && self::is_defined_preset_color( $theme_settings['elements']['link'][':hover']['color']['text'] ) ) {
				$css .= '--bs-link-hover-color: ' . esc_attr( $theme_settings['elements']['link'][':hover']['color']['text'] ) . ';';
			}

			if ( $css ) {
				$custom_css .= $is_fse ? 'body.editor-styles-wrapper{' . esc_attr( $css ) . '}' : 'body{' . esc_attr( $css ) . '}';
			}

			$bep = $is_fse ? 'body.editor-styles-wrapper ' : '';

			$headings_css = '';
			if ( ! empty( $theme_settings['elements']['heading']['color']['text'] ) ) {
				$headings_css .= 'color: ' . esc_attr( $theme_settings['elements']['heading']['color']['text'] ) . ';';
			}
			if ( ! empty( $theme_settings['elements']['heading']['color']['background'] ) ) {
				$headings_css .= 'background: ' . esc_attr( $theme_settings['elements']['heading']['color']['background'] ) . ';';
			}
			if ( ! empty( $theme_settings['elements']['heading']['typography']['fontFamily'] ) ) {
				$headings_css .= 'font-family: ' . esc_attr( $theme_settings['elements']['heading']['typography']['fontFamily'] ) . ';';
			}

			if ( $headings_css ) {
				$custom_css .= "$bep h1,$bep h2,$bep h3, $bep h4,$bep h5,$bep h6{ " . esc_attr( $headings_css ) . '}';
			}

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

		if ( function_exists( 'hello_elementor_setup' ) ) {
			echo '.aui-nav-links .pagination{justify-content:inherit}';
		}

		if ( defined( 'ASTRA_THEME_VERSION' ) ) {
			echo '.woocommerce-js.modal-open .select2-container .select2-dropdown, .woocommerce-js.modal-open .select2-container .select2-search__field, .woocommerce-page.modal-open .select2-container .select2-dropdown, .woocommerce-page.modal-open .select2-container .select2-search__field{z-index: 1056;}';
		}

		?></style><?php
		$custom_css = ob_get_clean();

		return str_replace( [ '<style>', '</style>' ], '', self::minify_css( $custom_css ) );
	}

	/**
	 * Check that a color pointing at a palette preset names one that exists.
	 *
	 * Links read --bs-link-color directly, so a stale reference, such as a preset
	 * dropped from the palette, would make every link inherit the text color.
	 *
	 * @param string $color Color value from Global Styles.
	 * @return bool False only for a reference to a palette color that is not defined.
	 */
	private static function is_defined_preset_color( string $color ): bool {
		if ( ! preg_match( '/^var(?::preset\|color\||\(--wp--preset--color--)([a-z0-9-]+)\)?$/', $color, $matches ) ) {
			return true;
		}

		$palette = wp_get_global_settings( [ 'color', 'palette' ] );
		$slugs   = [];

		foreach ( (array) $palette as $origin ) {
			$slugs = array_merge( $slugs, wp_list_pluck( (array) $origin, 'slug' ) );
		}

		return in_array( $matches[1], $slugs, true );
	}

	/**
	 * Build the CSS to overwrite a Bootstrap color variable.
	 *
	 * The brand colors only need the variable, as the stylesheet mixes every shade
	 * from it. Any other color still gets its classes written out here.
	 *
	 * @param string      $type         Color type (primary, secondary, etc.).
	 * @param string      $color_code   Color code or CSS variable.
	 * @param bool|string $compatibility Compatibility mode.
	 * @param string      $hex          Hex color value.
	 * @return string Generated CSS.
	 */
	public static function css_overwrite( string $type, string $color_code, $compatibility, string $hex = '' ): string {
		if ( empty( $color_code ) ) {
			return '';
		}

		// Reject anything that is not a recognised color before it reaches the stylesheet.
		$color_code = self::sanitize_css_color( $color_code );

		if ( '' === $color_code ) {
			return '';
		}

		$is_var    = strpos( $color_code, 'var' ) !== false;
		$is_custom = strpos( $type, 'custom-' ) !== false;

		// Callers may pass only a hex color, with no separate value to derive from.
		$hex = '' !== $hex ? $hex : $color_code;
		$rgb = self::hex_to_rgb( $hex );

		$compatibility = self::selector_scope( $compatibility );
		$prefix        = $compatibility ? $compatibility . ' ' : '';
		$type          = sanitize_html_class( $type );

		// Set on the root, where the stylesheet mixes the subtle and emphasis shades
		// from it, and on every color mode scope, which repeats the compiled value.
		$output = ':root,[data-bs-theme] {--bs-' . esc_attr( $type ) . ': ' . $color_code . '; --bs-' . esc_attr( $type ) . '-rgb: ' . $rgb . '; }';

		if ( in_array( $type, self::RUNTIME_COLORS, true ) ) {
			return $output . self::contrast_text_css( $type, $hex, $prefix );
		}

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

		$output .= $prefix . ' .link-' . esc_attr( $type ) . ' {color: var(--bs-' . esc_attr( $type ) . ') !important;}';
		$output .= $prefix . ' .link-' . esc_attr( $type ) . ':hover {color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .8) !important;}';

		$output .= $prefix . ' .btn-' . esc_attr( $type ) . '{';
		$output .= '
		--bs-btn-bg: ' . $color_code . ';
		--bs-btn-border-color: ' . $color_code . ';
		--bs-btn-hover-bg: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-hover-border-color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-focus-shadow-rgb: var(--bs-' . esc_attr( $type ) . '-rgb);
		--bs-btn-active-bg: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-active-border-color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-active-shadow: unset;
		--bs-btn-disabled-bg: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .5);
		--bs-btn-disabled-border-color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .1);
		';
		$output .= '}';

		$output .= $prefix . ' .btn-outline-' . esc_attr( $type ) . '{';
		$output .= '
		--bs-btn-color: ' . $color_code . ';
		--bs-btn-border-color: ' . $color_code . ';
		--bs-btn-hover-bg: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-hover-border-color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-focus-shadow-rgb: var(--bs-' . esc_attr( $type ) . '-rgb);
		--bs-btn-active-bg: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-active-border-color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .9);
		--bs-btn-active-shadow: unset;
		--bs-btn-disabled-bg: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .5);
		--bs-btn-disabled-border-color: rgba(var(--bs-' . esc_attr( $type ) . '-rgb), .1);
		';
		$output .= '}';

		// The compiled text color was picked for the default; keep the text readable on
		// the new color.
		$output .= self::contrast_text_css( $type, $hex, $prefix );

		$output .= $prefix . ' .btn-' . esc_attr( $type ) . ':hover{';
		$output .= ' box-shadow: 0 0.25rem 0.25rem 0.125rem rgb(var(--bs-' . esc_attr( $type ) . '-rgb), .1), 0 0.375rem 0.75rem -0.125rem rgb(var(--bs-' . esc_attr( $type ) . '-rgb) , .4);}';

		if ( $is_custom ) {
			$color      = [];
			$background = [];
			$border     = [];
			$fill       = [];

			foreach ( $selectors as $selector => $types ) {
				$selector = $compatibility ? $compatibility . ' ' . $selector : $selector;
				$types    = array_combine( $types, $types );
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

		$transition  = $is_var ? 'transition: color 0.15s ease-in-out,background-color 0.15s ease-in-out,border-color 0.15s ease-in-out,box-shadow 0.15s ease-in-out,filter 0.15s ease-in-out;' : '';
		$darker_075  = $is_var ? $color_code . ';filter:brightness(0.925)' : self::css_hex_lighten_darken( $color_code, '-0.075' );
		$darker_10   = $is_var ? $color_code . ';filter:brightness(0.9)' : self::css_hex_lighten_darken( $color_code, '-0.10' );
		$darker_125  = $is_var ? $color_code . ';filter:brightness(0.875)' : self::css_hex_lighten_darken( $color_code, '-0.125' );
		$op_25       = $color_code . '40';

		$output .= $is_var ? $prefix . " .btn-{$type}{{$transition }} " : '';
		$output .= $prefix . " .btn-{$type}:hover, $prefix .btn-{$type}:focus, $prefix .btn-{$type}.focus{background-color: " . $darker_075 . ';    border-color: ' . $darker_10 . ';} ';
		$output .= $prefix . " .btn-outline-{$type}:not(:disabled):not(.disabled):active:focus, $prefix .btn-outline-{$type}:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-outline-{$type}.dropdown-toggle:focus{box-shadow: 0 0 0 0.2rem $op_25;} ";
		$output .= $prefix . " .btn-{$type}:not(:disabled):not(.disabled):active, $prefix .btn-{$type}:not(:disabled):not(.disabled).active, .show>$prefix .btn-{$type}.dropdown-toggle{background-color: " . $darker_10 . ';    border-color: ' . $darker_125 . ';} ';
		$output .= $prefix . " .btn-{$type}:not(:disabled):not(.disabled):active:focus, $prefix .btn-{$type}:not(:disabled):not(.disabled).active:focus, .show>$prefix .btn-{$type}.dropdown-toggle:focus {box-shadow: 0 0 0 0.2rem $op_25;} ";
		$output .= $prefix . " .btn-{$type}:not(:disabled):not(.disabled):active:focus, $prefix .btn-{$type}:not(:disabled):not(.disabled):focus {box-shadow: 0 0.25rem 0.25rem 0.125rem rgba(var(--bs-{$type}-rgb), 0.1), 0 0.375rem 0.75rem -0.125rem rgba(var(--bs-{$type}-rgb), 0.4);} ";
		$output .= $prefix . " .alert-{$type} {--bs-alert-bg: rgba(var(--bs-{$type}-rgb), .1 ) !important;--bs-alert-border-color: rgba(var(--bs-{$type}-rgb), .25 ) !important;--bs-alert-color: rgba(var(--bs-{$type}-rgb), 1 ) !important;} ";

		return $output;
	}

	/**
	 * Build the CSS for a color the user added to the palette.
	 *
	 * The same rules the stylesheet compiles for a brand color (see _color-runtime.scss,
	 * partials/_colors.scss and the brand loop in components/_buttons.scss), written
	 * against this color's variable, so its shades follow it live in the editor too.
	 *
	 * @param string      $type          Color slug.
	 * @param string      $color_code    Color value or CSS variable.
	 * @param bool|string $compatibility Compatibility mode.
	 * @param string      $hex           Saved color value.
	 * @return string Generated CSS.
	 */
	public static function custom_color_css( string $type, string $color_code, $compatibility, string $hex = '' ): string {
		$color_code = self::sanitize_css_color( $color_code );

		if ( '' === $color_code ) {
			return '';
		}

		$hex    = '' !== $hex ? $hex : $color_code;
		$scope  = self::selector_scope( $compatibility );
		$p      = $scope ? $scope . ' ' : '';
		$type   = sanitize_html_class( $type );
		$var    = 'var(--bs-' . $type . ')';
		$hover  = 'color-mix(in srgb,' . $var . ',#000 20%)';
		$tinted = 'color-mix(in srgb,' . $var . ',#fff var(--bs-text-tint,0%))';
		$text   = sanitize_hex_color( $hex ) ? self::contrast_text_color( $hex ) : '#fff';
		$focus  = 'var(--bs-' . $type . '-rgb)';

		return ':root,[data-bs-theme]{--bs-' . $type . ':' . $color_code . ';--bs-' . $type . '-rgb:' . self::hex_to_rgb( $hex ) . '}'
			. $p . '.btn-' . $type . '{--bs-btn-color:' . $text . ';--bs-btn-bg:' . $var . ';--bs-btn-border-color:' . $var . ';--bs-btn-hover-color:' . $text . ';--bs-btn-hover-bg:' . $hover . ';--bs-btn-hover-border-color:' . $hover . ';--bs-btn-focus-shadow-rgb:' . $focus . ';--bs-btn-active-color:' . $text . ';--bs-btn-active-bg:' . $hover . ';--bs-btn-active-border-color:' . $hover . ';--bs-btn-disabled-color:' . $text . ';--bs-btn-disabled-bg:' . $var . ';--bs-btn-disabled-border-color:' . $var . '}'
			. $p . '.btn-outline-' . $type . '{--bs-btn-color:' . $tinted . ';--bs-btn-border-color:' . $tinted . ';--bs-btn-hover-color:' . $text . ';--bs-btn-hover-bg:' . $var . ';--bs-btn-hover-border-color:' . $var . ';--bs-btn-focus-shadow-rgb:' . $focus . ';--bs-btn-active-color:' . $text . ';--bs-btn-active-bg:' . $var . ';--bs-btn-active-border-color:' . $var . ';--bs-btn-disabled-color:' . $tinted . ';--bs-btn-disabled-border-color:' . $tinted . '}'
			. $p . '.text-' . $type . '{color:color-mix(in srgb,' . $tinted . ' calc(var(--bs-text-opacity,1)*100%),transparent)!important}'
			. $p . '.link-' . $type . '{color:' . $tinted . '!important;text-decoration-color:' . $tinted . '!important}'
			. $p . '.bg-' . $type . '{background-color:color-mix(in srgb,' . $var . ' calc(var(--bs-bg-opacity,1)*100%),transparent)!important;fill:' . $var . '}'
			. $p . '.text-bg-' . $type . '{color:' . $text . '!important;background-color:color-mix(in srgb,' . $var . ' calc(var(--bs-bg-opacity,1)*100%),transparent)!important}'
			. $p . '.border-' . $type . '{border-color:color-mix(in srgb,' . $var . ' calc(var(--bs-border-opacity,1)*100%),transparent)!important}';
	}

	/**
	 * Resolve the compatibility setting to the selector generated rules are scoped to.
	 *
	 * @param bool|string|int $compatibility True or 1 for the .bsui wrapper, false for
	 *                                       none, or a selector.
	 * @return string Selector, or an empty string for none.
	 */
	private static function selector_scope( $compatibility ): string {
		if ( true === $compatibility || 1 === $compatibility ) {
			return '.bsui';
		}

		return $compatibility ? esc_attr( (string) $compatibility ) : '';
	}

	/**
	 * Text color rules for a brand color that is not the compiled default.
	 *
	 * The stylesheet picks white or dark text for its own colors at compile time;
	 * CSS cannot yet pick it for a color only known at runtime, so it is worked out
	 * here from the saved value. The compiled defaults are left to the stylesheet.
	 *
	 * @param string $type   Color slug.
	 * @param string $hex    Saved color value.
	 * @param string $prefix Selector prefix, with a trailing space when set.
	 * @return string Generated CSS.
	 */
	private static function contrast_text_css( string $type, string $hex, string $prefix ): string {
		$hex      = (string) sanitize_hex_color( $hex );
		$compiled = SettingsOrchestrator::get_colors( true );

		if ( '' === $hex || ( isset( $compiled[ $type ] ) && strtolower( $compiled[ $type ] ) === strtolower( $hex ) ) ) {
			return '';
		}

		$text = self::contrast_text_color( $hex );

		return $prefix . '.btn-' . $type . '{--bs-btn-color:' . $text . ';--bs-btn-hover-color:' . $text . ';--bs-btn-active-color:' . $text . ';--bs-btn-disabled-color:' . $text . '}'
			. $prefix . '.btn-outline-' . $type . '{--bs-btn-hover-color:' . $text . ';--bs-btn-active-color:' . $text . '}'
			. $prefix . '.text-bg-' . $type . '{color:' . $text . ' !important}';
	}

	/**
	 * Pick white or black text for a background color, the way Bootstrap's
	 * color-contrast() does: white when it reaches WCAG AA (4.5:1), else black when
	 * that does, else whichever contrasts more.
	 *
	 * @param string $hex Background color as a hex value.
	 * @return string '#fff' or '#000'.
	 */
	public static function contrast_text_color( string $hex ): string {
		$channels = array_map(
			static function ( $value ) {
				$value /= 255;
				return $value <= 0.03928 ? $value / 12.92 : ( ( $value + 0.055 ) / 1.055 ) ** 2.4;
			},
			array_map( 'intval', explode( ',', self::hex_to_rgb( $hex ) ) )
		);

		$luminance = 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
		$on_white  = 1.05 / ( $luminance + 0.05 );
		$on_black  = ( $luminance + 0.05 ) / 0.05;

		if ( $on_white > 4.5 ) {
			return '#fff';
		}

		if ( $on_black > 4.5 ) {
			return '#000';
		}

		return $on_white >= $on_black ? '#fff' : '#000';
	}

	/**
	 * Validate a value that will be written into a CSS declaration.
	 *
	 * Output escapers such as esc_attr() are not sufficient inside a <style>
	 * element: they leave `;`, `{`, `}` and `(` untouched, so a crafted value can
	 * still close the declaration and inject new rules. Colors are therefore
	 * validated against the forms we actually support and rejected outright when
	 * they do not match.
	 *
	 * @param string $color Color value from settings or theme.json.
	 * @return string The color if it is valid, an empty string otherwise.
	 */
	public static function sanitize_css_color( string $color ): string {
		$color = trim( $color );

		if ( '' === $color ) {
			return '';
		}

		// Hex colors, validated by WordPress.
		if ( 0 === strpos( $color, '#' ) ) {
			return (string) sanitize_hex_color( $color );
		}

		// Custom property references, e.g. var(--wp--preset--color--primary).
		if ( preg_match( '/^var\(\s*--[A-Za-z0-9_-]+\s*\)$/', $color ) ) {
			return $color;
		}

		// Functional notations, e.g. rgb(0 0 0 / 50%) or hsla(120, 50%, 50%, .5).
		if ( preg_match( '/^(?:rgb|rgba|hsl|hsla)\([0-9%.,\s\/+-]+\)$/', $color ) ) {
			return $color;
		}

		// Named colors such as "red" or "transparent".
		if ( preg_match( '/^[A-Za-z]+$/', $color ) ) {
			return $color;
		}

		return '';
	}

	/**
	 * Convert hex color to RGB values.
	 *
	 * @param string $hex Hex color code.
	 * @return string RGB values as comma-separated string.
	 */
	public static function hex_to_rgb( string $hex ): string {
		$hex = str_replace( '#', '', $hex );

		if ( strpos( $hex, 'rgba(' ) === 0 || strpos( $hex, 'rgb(' ) === 0 ) {
			$_rgb = explode( ',', str_replace( [ 'rgba(', 'rgb(', ')' ], '', $hex ) );

			$rgb  = ( isset( $_rgb[0] ) ? (int) trim( $_rgb[0] ) : '0' ) . ',';
			$rgb .= ( isset( $_rgb[1] ) ? (int) trim( $_rgb[1] ) : '0' ) . ',';
			$rgb .= ( isset( $_rgb[2] ) ? (int) trim( $_rgb[2] ) : '0' );

			return $rgb;
		}

		if ( strlen( $hex ) === 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
		}

		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		return $r . ',' . $g . ',' . $b;
	}

	/**
	 * Lighten or darken a hex color by a percentage.
	 *
	 * @param string $hexCode       Hex color code (with or without #).
	 * @param float  $adjustPercent Adjustment percentage (-1 to 1). Positive = lighter, negative = darker.
	 * @return string Modified hex color.
	 */
	public static function css_hex_lighten_darken( string $hexCode, $adjustPercent ): string {
		$hexCode = ltrim( $hexCode, '#' );

		if ( strpos( $hexCode, 'rgba(' ) !== false || strpos( $hexCode, 'rgb(' ) !== false ) {
			return $hexCode;
		}

		if ( strlen( $hexCode ) === 3 ) {
			$hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
		}

		$hexCode = array_map( 'hexdec', str_split( $hexCode, 2 ) );

		foreach ( $hexCode as &$color ) {
			$adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
			$adjustAmount    = ceil( $adjustableLimit * $adjustPercent );
			$color           = str_pad( dechex( $color + $adjustAmount ), 2, '0', STR_PAD_LEFT );
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
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
				'#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
				'#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
				'#(background-position):0(?=[;\}])#si',
				'#(?<=[\s:,\-])0+\.(\d+)#s',
				'#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
				'#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
				'#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
				'#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
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
				'#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
				'#;+\}#',
				'#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
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
