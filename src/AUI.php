<?php
/**
 * AyeCode UI — Main API Singleton
 *
 * Provides a unified interface for rendering all AyeCode UI / Bootstrap 5 components.
 *
 * @package AyeCode\UI
 */

namespace AyeCode\UI;

use AyeCode\UI\Components\Alert;
use AyeCode\UI\Components\Button;
use AyeCode\UI\Components\Dropdown;
use AyeCode\UI\Components\Input;
use AyeCode\UI\Components\Pagination;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AUI class.
 *
 * Singleton entry point for rendering AyeCode UI components.
 */
class AUI {

	/**
	 * Singleton instance.
	 *
	 * @var AUI|null
	 */
	private static ?AUI $instance = null;

	/**
	 * Cached plugin options.
	 *
	 * @var array|false
	 */
	private static $options = null;

	/**
	 * Get singleton instance.
	 *
	 * @return AUI
	 */
	public static function instance(): AUI {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor — loads options only; no hook registrations.
	 */
	private function __construct() {
		self::$options = get_option( 'aui_options' );
	}

	/**
	 * Get a single plugin option.
	 *
	 * Falls back to the relevant color constant when the stored value is empty.
	 *
	 * @param string $option Option key.
	 * @return string Option value, or empty string.
	 */
	public function get_option( string $option ): string {
		$result = isset( self::$options[ $option ] ) ? esc_attr( self::$options[ $option ] ) : '';

		if ( ! $result && $option ) {
			if ( $option === 'color_primary' && defined( 'AUI_PRIMARY_COLOR' ) ) {
				$result = AUI_PRIMARY_COLOR;
			} elseif ( $option === 'color_secondary' && defined( 'AUI_SECONDARY_COLOR' ) ) {
				$result = AUI_SECONDARY_COLOR;
			}
		}

		return $result;
	}

	/**
	 * Render multiple component items defined by a structured array.
	 *
	 * Each item must have a `render` key matching a public method on this class.
	 *
	 * @param array $items    Array of component argument arrays, each with a `render` key.
	 * @param bool  $echo     Whether to echo the output instead of returning it.
	 * @return string Rendered HTML (empty string when $echo is true).
	 */
	public function render( array $items = [], bool $echo = false ): string {
		$output = '';

		foreach ( $items as $args ) {
			$render = $args['render'] ?? '';
			if ( $render && method_exists( $this, $render ) ) {
				$output .= $this->$render( $args );
			}
		}

		if ( $echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return '';
		}

		return $output;
	}

	/**
	 * Render a Bootstrap alert component.
	 *
	 * @param array $args Component arguments.
	 * @param bool  $echo Whether to echo instead of return.
	 * @return string Rendered HTML.
	 */
	public function alert( array $args = [], bool $echo = false ): string {
		$output = Alert::get( $args );

		if ( $echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return '';
		}

		return $output;
	}

	/**
	 * Render a Bootstrap input component.
	 *
	 * @param array $args Component arguments.
	 * @param bool  $echo Whether to echo instead of return.
	 * @return string Rendered HTML.
	 */
	public function input( array $args = [], bool $echo = false ): string {
		$output = Input::input( $args );

		if ( $echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return '';
		}

		return $output;
	}

	/**
	 * Render a Bootstrap textarea component.
	 *
	 * @param array $args Component arguments.
	 * @param bool  $echo Whether to echo instead of return.
	 * @return string Rendered HTML.
	 */
	public function textarea( array $args = [], bool $echo = false ): string {
		$output = Input::textarea( $args );

		if ( $echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return '';
		}

		return $output;
	}

	/**
	 * Render a Bootstrap button or anchor component.
	 *
	 * @param array $args Component arguments.
	 * @param bool  $echo Whether to echo instead of return.
	 * @return string Rendered HTML.
	 */
	public function button( array $args = [], bool $echo = false ): string {
		$output = Button::get( $args );

		if ( $echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return '';
		}

		return $output;
	}

	/**
	 * Render a Bootstrap badge component.
	 *
	 * @param array $args Component arguments.
	 * @param bool  $echo Whether to echo instead of return.
	 * @return string Rendered HTML.
	 */
	public function badge( array $args = [], bool $echo = false ): string {
		$defaults = [
			'class' => 'badge badge-primary align-middle',
		];

		if ( empty( $args['href'] ) ) {
			$defaults['type'] = 'badge';
		}

		$args   = wp_parse_args( $args, $defaults );
		$output = Button::get( $args );

		if ( $echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return '';
		}

		return $output;
	}

	/**
	 * Render a Bootstrap dropdown component.
	 *
	 * @param array $args Component arguments.
	 * @param bool  $echo Whether to echo instead of return.
	 * @return string Rendered HTML.
	 */
	public function dropdown( array $args = [], bool $echo = false ): string {
		$output = Dropdown::get( $args );

		if ( $echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return '';
		}

		return $output;
	}

	/**
	 * Render a Bootstrap select component.
	 *
	 * @param array $args Component arguments.
	 * @param bool  $echo Whether to echo instead of return.
	 * @return string Rendered HTML.
	 */
	public function select( array $args = [], bool $echo = false ): string {
		$output = Input::select( $args );

		if ( $echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return '';
		}

		return $output;
	}

	/**
	 * Render a Bootstrap radio component.
	 *
	 * @param array $args Component arguments.
	 * @param bool  $echo Whether to echo instead of return.
	 * @return string Rendered HTML.
	 */
	public function radio( array $args = [], bool $echo = false ): string {
		$output = Input::radio( $args );

		if ( $echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return '';
		}

		return $output;
	}

	/**
	 * Render a Bootstrap pagination component.
	 *
	 * @param array $args Component arguments.
	 * @param bool  $echo Whether to echo instead of return.
	 * @return string Rendered HTML.
	 */
	public function pagination( array $args = [], bool $echo = false ): string {
		$output = Pagination::get( $args );

		if ( $echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return '';
		}

		return $output;
	}

	/**
	 * Render a form-group wrapper around arbitrary content.
	 *
	 * @param array $args Component arguments.
	 * @param bool  $echo Whether to echo instead of return.
	 * @return string Rendered HTML.
	 */
	public function wrap( array $args = [], bool $echo = false ): string {
		$output = Input::wrap( $args );

		if ( $echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return '';
		}

		return $output;
	}

	/**
	 * Return the Helper utilities accessor.
	 *
	 * Enables the fluent call pattern: aui()->helpers()->help_text( $text )
	 *
	 * @return Components\Helper
	 */
	public function helpers(): Components\Helper {
		return Components\Helper::instance();
	}
}
