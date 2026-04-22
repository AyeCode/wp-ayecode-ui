<?php
/**
 * AyeCode UI Component Helper
 *
 * Helper methods for rendering common component attributes.
 *
 * @package AyeCode\UI\Components
 */

namespace AyeCode\UI\Components;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class.
 *
 * Provides static utility methods shared across all component classes.
 * Can be used statically (Helper::method()) or via the aui()->helpers() accessor.
 */
class Helper {

	/**
	 * Singleton instance — used when accessed via aui()->helpers().
	 *
	 * @var Helper|null
	 */
	private static ?Helper $instance = null;

	/**
	 * Return the singleton instance.
	 *
	 * @return Helper
	 */
	public static function instance(): Helper {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — use instance() or call methods statically.
	 */
	private function __construct() {}

	/**
	 * Generate a name attribute.
	 *
	 * @param string $text     The name value.
	 * @param bool   $multiple Whether the field is multiple; adds [] if no brackets found.
	 * @return string
	 */
	public static function name( $text, $multiple = false ): string {
		$output = '';

		if ( $text ) {
			$is_multiple = strpos( $text, '[' ) === false && $multiple ? '[]' : '';
			$output      = ' name="' . esc_attr( $text ) . $is_multiple . '" ';
		}

		return $output;
	}

	/**
	 * Generate an id attribute.
	 *
	 * @param string $text The id value.
	 * @return string
	 */
	public static function id( $text ): string {
		$output = '';

		if ( $text ) {
			$output = ' id="' . sanitize_html_class( $text ) . '" ';
		}

		return $output;
	}

	/**
	 * Generate a title attribute.
	 *
	 * @param string $text The title value.
	 * @return string
	 */
	public static function title( $text ): string {
		$output = '';

		if ( $text ) {
			$output = ' title="' . esc_attr( $text ) . '" ';
		}

		return $output;
	}

	/**
	 * Generate a value attribute.
	 *
	 * @param string $text The value.
	 * @return string
	 */
	public static function value( $text ): string {
		$output = '';

		if ( null !== $text && false !== $text ) {
			$output = ' value="' . esc_attr( wp_unslash( $text ) ) . '" ';
		}

		return $output;
	}

	/**
	 * Generate a class attribute.
	 *
	 * @param string $text The class string.
	 * @return string
	 */
	public static function class_attr( $text ): string {
		$output = '';

		if ( $text ) {
			$classes = self::esc_classes( $text );
			if ( ! empty( $classes ) ) {
				$output = ' class="' . $classes . '" ';
			}
		}

		return $output;
	}

	/**
	 * Sanitize a space-separated class string.
	 *
	 * @param string $text The class string.
	 * @return string
	 */
	public static function esc_classes( $text ): string {
		$output = '';

		if ( $text ) {
			$classes = explode( ' ', $text );
			$classes = array_map( 'trim', $classes );
			$classes = array_map( 'sanitize_html_class', $classes );
			if ( ! empty( $classes ) ) {
				$output = implode( ' ', $classes );
			}
		}

		return $output;
	}

	/**
	 * Generate data-* attributes from an args array.
	 *
	 * @param array $args Component args array.
	 * @return string
	 */
	public static function data_attributes( $args ): string {
		$output = '';

		if ( ! empty( $args ) ) {
			foreach ( $args as $key => $val ) {
				if ( substr( $key, 0, 5 ) === 'data-' ) {
					$output .= ' ' . sanitize_html_class( $key ) . '="' . esc_attr( $val ) . '" ';
				}
			}
		}

		return $output;
	}

	/**
	 * Generate aria-* attributes from an args array.
	 *
	 * @param array $args Component args array.
	 * @return string
	 */
	public static function aria_attributes( $args ): string {
		$output = '';

		if ( ! empty( $args ) ) {
			foreach ( $args as $key => $val ) {
				if ( substr( $key, 0, 5 ) === 'aria-' ) {
					$output .= ' ' . sanitize_html_class( $key ) . '="' . esc_attr( $val ) . '" ';
				}
			}
		}

		return $output;
	}

	/**
	 * Build a Font Awesome icon tag.
	 *
	 * Delegates to ayecode_get_icon() for JIT/SVG rendering on the frontend when the
	 * Font Awesome settings package is present. Falls back to a plain <i> tag otherwise
	 * (e.g. in wp-admin, where webfont CSS is loaded directly).
	 *
	 * @param string $class            Icon identifier / class string (e.g. 'fas fa-user').
	 * @param bool   $space_after      Whether to add a right margin (me-2) class.
	 * @param array  $extra_attributes Extra HTML attributes keyed by attribute name.
	 * @return string
	 */
	public static function icon( $class, $space_after = false, $extra_attributes = array() ): string {
		$output = '';

		if ( $class ) {
			$identifier = self::esc_classes( $class );
			if ( ! empty( $identifier ) ) {
				if ( function_exists( 'ayecode_get_icon' ) ) {
					$options = array();
					if ( $space_after ) {
						$options['class'] = 'me-2';
					}
					if ( ! empty( $extra_attributes ) && is_array( $extra_attributes ) ) {
						$options['attributes'] = $extra_attributes;
					}
					$output = \ayecode_get_icon( $identifier, $options );
				} else {
					// Fallback: plain <i> tag (admin or Font Awesome settings not loaded).
					if ( $space_after ) {
						$identifier .= ' me-2';
					}
					$output = '<i class="' . $identifier . '" ';
					if ( ! empty( $extra_attributes ) ) {
						$output .= self::extra_attributes( $extra_attributes );
					}
					$output .= '></i>';
				}
			}
		}

		return $output;
	}

	/**
	 * Generate extra HTML attributes from an array or string.
	 *
	 * @param array|string $args Attribute key/value pairs.
	 * @return string
	 */
	public static function extra_attributes( $args ): string {
		$output = '';

		if ( ! empty( $args ) ) {
			if ( is_array( $args ) ) {
				foreach ( $args as $key => $val ) {
					$output .= ' ' . sanitize_html_class( $key ) . '="' . esc_attr( $val ) . '" ';
				}
			} else {
				$output .= ' ' . $args . ' ';
			}
		}

		return $output;
	}

	/**
	 * Generate a help text element.
	 *
	 * @param string $text Help text content.
	 * @return string
	 */
	public static function help_text( $text ): string {
		$output = '';

		if ( $text ) {
			$output .= '<small class="form-text text-muted d-block">' . wp_kses_post( $text ) . '</small>';
		}

		return $output;
	}

	/**
	 * Replace element require context with a JavaScript data attribute.
	 *
	 * @param string $input Element require expression.
	 * @return string
	 */
	public static function element_require( $input ): string {
		$input = str_replace( "'", '"', $input );

		$output = esc_attr(
			str_replace(
				array( '[%', '%]', '%:checked]' ),
				array(
					'jQuery(form).find(\'[data-argument="',
					'\"]\').find(\'input,select,textarea\').val()',
					'\"]\').find(\'input:checked\').val()',
				),
				$input
			)
		);

		if ( $output ) {
			$output = ' data-element-require="' . $output . '" ';
		}

		return $output;
	}

	/**
	 * Sanitize a value while preserving allowable HTML elements.
	 *
	 * @param mixed $value The value to sanitize.
	 * @param array $input Input field definition.
	 * @return mixed
	 */
	public static function sanitize_html_field( $value, $input = array() ) {
		$original = $value;

		if ( is_array( $value ) ) {
			foreach ( $value as $index => $item ) {
				$value[ $index ] = self::_sanitize_html_field( $item, $input );
			}
		} elseif ( is_object( $value ) ) {
			$object_vars = get_object_vars( $value );
			foreach ( $object_vars as $property_name => $property_value ) {
				$value->$property_name = self::_sanitize_html_field( $property_value, $input );
			}
		} else {
			$value = self::_sanitize_html_field( $value, $input );
		}

		return apply_filters( 'ayecode_ui_sanitize_html_field', $value, $original, $input );
	}

	/**
	 * Internal kses-based sanitizer.
	 *
	 * @param string|array $value Content to filter.
	 * @param array        $input Input field definition.
	 * @return string
	 */
	public static function _sanitize_html_field( $value, $input = array() ): string {
		if ( '' === $value ) {
			return $value;
		}

		$allowed_html = self::kses_allowed_html( 'post', $input );

		if ( ! is_array( $allowed_html ) ) {
			$allowed_html = wp_kses_allowed_html( 'post' );
		}

		$filtered = trim( wp_unslash( $value ) );
		$filtered = wp_kses( $filtered, $allowed_html );
		$filtered = balanceTags( $filtered );

		return $filtered;
	}

	/**
	 * Returns an array of allowed HTML tags for a given context, with iframe support added.
	 *
	 * @param string|array $context Context string.
	 * @param array        $input   Input field definition.
	 * @return array
	 */
	public static function kses_allowed_html( $context = 'post', $input = array() ): array {
		$allowed_html = wp_kses_allowed_html( $context );

		if ( is_array( $allowed_html ) && ! isset( $allowed_html['iframe'] ) && 'post' === $context ) {
			$allowed_html['iframe'] = array(
				'class'           => true,
				'id'              => true,
				'src'             => true,
				'width'           => true,
				'height'          => true,
				'frameborder'     => true,
				'marginwidth'     => true,
				'marginheight'    => true,
				'scrolling'       => true,
				'style'           => true,
				'title'           => true,
				'allow'           => true,
				'allowfullscreen' => true,
				'data-*'          => true,
			);
		}

		return apply_filters( 'ayecode_ui_kses_allowed_html', $allowed_html, $context, $input );
	}

	/**
	 * Get a Bootstrap column class for a label or input.
	 *
	 * @param int|string $label_number Label column width (1–11).
	 * @param string     $type         'label' or 'input'.
	 * @return string
	 */
	public static function get_column_class( $label_number = 2, string $type = 'label' ): string {
		$class = '';

		if ( '' === $label_number ) {
			$label_number = 2;
		}

		if ( $label_number && $label_number < 12 && $label_number > 0 ) {
			if ( 'label' === $type ) {
				$class = 'col-sm-' . absint( $label_number );
			} elseif ( 'input' === $type ) {
				$class = 'col-sm-' . ( 12 - absint( $label_number ) );
			}
		}

		return $class;
	}

	/**
	 * Sanitize a multiline string from user input.
	 *
	 * @param string $str String to sanitize.
	 * @return string
	 */
	public static function sanitize_textarea_field( $str ): string {
		$filtered = self::_sanitize_text_fields( $str, true );

		return apply_filters( 'sanitize_textarea_field', $filtered, $str );
	}

	/**
	 * Internal text field sanitizer.
	 *
	 * @param string $str            String to sanitize.
	 * @param bool   $keep_newlines  Whether to preserve newlines.
	 * @return string
	 */
	public static function _sanitize_text_fields( $str, bool $keep_newlines = false ): string {
		if ( is_object( $str ) || is_array( $str ) ) {
			return '';
		}

		$str      = (string) $str;
		$filtered = wp_check_invalid_utf8( $str );

		if ( strpos( $filtered, '<' ) !== false ) {
			$filtered = wp_pre_kses_less_than( $filtered );
			$filtered = wp_strip_all_tags( $filtered, false );
			$filtered = str_replace( "<\n", "&lt;\n", $filtered );
		}

		if ( ! $keep_newlines ) {
			$filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );
		}
		$filtered = trim( $filtered );

		$found = false;
		while ( preg_match( '`[^%](%[a-f0-9]{2})`i', $filtered, $match ) ) {
			$filtered = str_replace( $match[1], '', $filtered );
			$found    = true;
		}
		unset( $match );

		if ( $found ) {
			$filtered = trim( preg_replace( '` +`', ' ', $filtered ) );
		}

		return $filtered;
	}
}
