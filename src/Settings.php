<?php
/**
 * AyeCode UI Settings Manager
 *
 * Handles settings registration, retrieval, and defaults for AyeCode UI.
 *
 * @package AyeCode\UI
 */

namespace AyeCode\UI;

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
	private array $settings = [];

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
		register_setting( 'ayecode-ui-settings', 'ayecode-ui-settings' );
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

		$db_settings = get_option( 'ayecode-ui-settings', [] );
		$js_default  = 'core-popper';

		/**
		 * Filter the default settings.
		 *
		 * @param array $defaults     Default settings.
		 * @param array $db_settings  Database settings.
		 */
		$defaults = apply_filters( 'ayecode-ui-default-settings', [
			'css'            => 'compatibility',
			'js'             => $js_default,
			'html_font_size' => '16',
			'css_backend'    => 'compatibility',
			'js_backend'     => $js_default,
			'disable_admin'  => '',
			'bs_ver'         => '5dm',
			'load_mode'      => 'auto',
		], $db_settings );

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

		$url_parts = explode( "\n", $disable_admin );
		foreach ( $url_parts as $part ) {
			if ( ! empty( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], trim( $part ) ) !== false ) {
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
