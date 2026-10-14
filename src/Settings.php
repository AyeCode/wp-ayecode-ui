<?php
/**
 * AyeCode UI Settings Manager
 *
 * Handles settings registration, retrieval, and defaults for AyeCode UI.
 *
 * @package AyeCode\UI
 */

namespace AyeCode\UI;

use AyeCode\UI\Components\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings class.
 *
 * Manages all settings-related functionality for AyeCode UI.
 */
class Settings {

	/**
	 * Singleton instance.
	 *
	 * @var Settings|null
	 */
	private static ?Settings $instance = null;

	/**
	 * Settings array.
	 *
	 * @var array
	 */
	private array $settings = array();

	/**
	 * Get singleton instance.
	 *
	 * @return Settings
	 */
	public static function instance(): Settings {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Settings are loaded on-demand.
	}

	/**
	 * Register settings with WordPress.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'ayecode-ui-settings',
			'ayecode-ui-settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanitize the settings array before it is stored.
	 *
	 * Known keys are sanitized according to the value they hold. Unknown keys are
	 * kept but run through a generic text sanitizer, so that settings added via the
	 * `ayecode-ui-default-settings` filter survive a save instead of being dropped.
	 *
	 * @param mixed $settings Raw settings submitted from the settings page.
	 * @return array
	 */
	public function sanitize_settings( $settings ): array {
		if ( ! is_array( $settings ) ) {
			return array();
		}

		$clean = array();

		foreach ( $settings as $key => $value ) {
			$key = sanitize_key( $key );

			switch ( $key ) {
				case 'css':
				case 'js':
				case 'css_backend':
				case 'js_backend':
					$clean[ $key ] = is_scalar( $value ) ? sanitize_key( $value ) : '';
					break;

				case 'html_font_size':
					$clean[ $key ] = absint( $value );
					break;

				case 'disable_admin':
					$clean[ $key ] = is_scalar( $value ) ? Helper::sanitize_textarea_field( (string) $value ) : '';
					break;

				default:
					$clean[ $key ] = $this->sanitize_unknown( $value );
					break;
			}
		}

		return $clean;
	}

	/**
	 * Sanitize a setting we have no specific rule for.
	 *
	 * Arrays are walked so that settings added by other plugins through the
	 * `ayecode-ui-default-settings` filter keep their shape instead of collapsing.
	 *
	 * @param mixed $value Value to sanitize.
	 * @return array|string
	 */
	private function sanitize_unknown( $value ) {
		if ( is_array( $value ) ) {
			$clean = array();

			foreach ( $value as $key => $item ) {
				$clean[ sanitize_key( $key ) ] = $this->sanitize_unknown( $item );
			}

			return $clean;
		}

		return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
	}

	/**
	 * Get all settings.
	 *
	 * @return array
	 */
	public function get_settings(): array {
		if ( ! empty( $this->settings ) ) {
			return $this->settings;
		}

		$db_settings = get_option( 'ayecode-ui-settings', array() );
		$js_default  = 'core-popper';

		/**
		 * Filter the default settings.
		 *
		 * @param array $defaults     Default settings.
		 * @param array $db_settings  Database settings.
		 */
		$defaults = apply_filters(
			'ayecode-ui-default-settings',
			array(
				'css'            => 'compatibility',
				'js'             => $js_default,
				'html_font_size' => '16',
				'css_backend'    => 'compatibility',
				'js_backend'     => $js_default,
				'disable_admin'  => '',
				'bs_ver'         => '5dm',
				'load_mode'      => 'auto',
			),
			$db_settings
		);

		$settings = wp_parse_args( $db_settings, $defaults );

		/**
		 * Filter the final settings.
		 *
		 * @param array $settings     Merged settings.
		 * @param array $db_settings  Database settings.
		 * @param array $defaults     Default settings.
		 */
		$this->settings = apply_filters( 'ayecode-ui-settings', $settings, $db_settings, $defaults );

		return $this->settings;
	}

	/**
	 * Get a single setting value.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value if not set.
	 * @return mixed
	 */
	public function get( string $key, $default = '' ) {
		$settings = $this->get_settings();
		return $settings[ $key ] ?? $default;
	}

	/**
	 * Check if admin scripts should be loaded on the current page.
	 *
	 * @return bool
	 */
	public function should_load_admin_scripts(): bool {
		$disable_admin = $this->get( 'disable_admin' );

		if ( empty( $disable_admin ) ) {
			return true;
		}

		$request_uri = ! empty( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		if ( '' === $request_uri ) {
			return true;
		}

		$url_parts = explode( "\n", $disable_admin );
		foreach ( $url_parts as $part ) {
			if ( strpos( $request_uri, trim( $part ) ) !== false ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the load mode setting.
	 *
	 * @return string auto, always, or manual
	 */
	public function get_load_mode(): string {
		if ( defined( 'AUI_ALWAYS_LOAD' ) && AUI_ALWAYS_LOAD ) {
			return 'always';
		}

		return $this->get( 'load_mode', 'auto' );
	}
}
