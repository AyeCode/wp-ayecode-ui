<?php
/**
 * AyeCode UI Admin Interface
 *
 * Handles admin settings page using the AyeCode Settings Framework.
 *
 * @package AyeCode\UI
 */

namespace AyeCode\UI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class.
 *
 * Manages the admin interface and settings page. Hooks are registered by
 * Loader, not in this constructor.
 */
class Admin extends \AyeCode\SettingsFramework\Settings_Framework {

	/**
	 * Singleton instance.
	 *
	 * @var Admin|null
	 */
	private static ?Admin $instance = null;

	/**
	 * Package name.
	 *
	 * @var string
	 */
	protected $plugin_name = 'AyeCode UI';

	/**
	 * Option name for settings.
	 *
	 * @var string
	 */
	protected $option_name = 'ayecode-ui-settings';

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	protected $page_slug = 'ayecode-ui-settings';

	/**
	 * Menu title.
	 *
	 * @var string
	 */
	protected $menu_title = 'AyeCode UI';

	/**
	 * Page title.
	 *
	 * @var string
	 */
	protected $page_title = 'Settings';

	/**
	 * Menu icon.
	 *
	 * @var string
	 */
	protected $menu_icon = 'dashicons-admin-settings';

	/**
	 * Menu position.
	 *
	 * @var int
	 */
	protected $menu_position = 80;

	/**
	 * Parent page slug.
	 *
	 * @var string
	 */
	protected $parent_slug = 'options-general.php';

	/**
	 * Get singleton instance.
	 *
	 * @return Admin
	 */
	public static function instance(): Admin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get the settings configuration.
	 *
	 * @return array Configuration array with sections and fields.
	 */
	public function get_config(): array {
		$overrides        = apply_filters( 'ayecode-ui-settings', [], [], [] );
		$current_settings = get_option( $this->option_name, [] );
		$version          = defined( 'AYECODE_UI_VERSION' ) ? AYECODE_UI_VERSION : '3.0.0-beta';

		return [
			'sections' => [
				[
					'id'          => 'bootstrap',
					'name'        => __( 'Bootstrap Version', 'ayecode-connect' ),
					'icon'        => 'fa-solid fa-code',
					'description' => __( 'This package now only supports Bootstrap 5.3+ (with dark mode support).', 'ayecode-connect' ),
					'fields'      => [
						[
							'id'               => 'bs_ver',
							'type'             => 'select',
							'label'            => __( 'Bootstrap Version', 'ayecode-connect' ),
							'desc'             => __( 'Select the Bootstrap version to use.', 'ayecode-connect' ),
							'options'          => apply_filters( 'ayecode_ui_versions', [
								'5dm' => __( 'Bootstrap 5.3+ (Dark Mode) - v3.0', 'ayecode-connect' ),
							], $current_settings, $overrides ),
							'default'          => '5dm',
							'extra_attributes' => ! empty( $overrides['bs_ver'] ) ? [ 'disabled' => true ] : [],
						],
					],
				],

				[
					'id'     => 'loading',
					'name'   => __( 'Asset Loading', 'ayecode-connect' ),
					'icon'   => 'fa-solid fa-bolt',
					'fields' => [
						[
							'id'      => 'load_mode',
							'type'    => 'select',
							'label'   => __( 'Load Mode', 'ayecode-connect' ),
							'desc'    => __( 'Auto mode only loads assets when AyeCode blocks are detected, improving performance.', 'ayecode-connect' ),
							'options' => [
								'auto'   => __( 'Auto (recommended) - Only when blocks detected', 'ayecode-connect' ),
								'always' => __( 'Always - Load on all pages', 'ayecode-connect' ),
								'manual' => __( 'Manual - Developer controlled', 'ayecode-connect' ),
							],
							'default' => 'auto',
						],
					],
				],

				[
					'id'     => 'frontend',
					'name'   => __( 'Frontend', 'ayecode-connect' ),
					'icon'   => 'fa-solid fa-display',
					'fields' => [
						[
							'id'               => 'css',
							'type'             => 'select',
							'label'            => __( 'Load CSS', 'ayecode-connect' ),
							'options'          => [
								'compatibility' => __( 'Compatibility Mode (default)', 'ayecode-connect' ),
								'core'          => __( 'Full Mode', 'ayecode-connect' ),
								''              => __( 'Disabled', 'ayecode-connect' ),
							],
							'default'          => 'compatibility',
							'extra_attributes' => ! empty( $overrides['css'] ) ? [ 'disabled' => true ] : [],
						],
						[
							'id'               => 'js',
							'type'             => 'select',
							'label'            => __( 'Load JS', 'ayecode-connect' ),
							'options'          => [
								'core-popper' => __( 'Core + Popper (default)', 'ayecode-connect' ),
								'popper'      => __( 'Popper', 'ayecode-connect' ),
								'required'    => __( 'Required functions only', 'ayecode-connect' ),
								''            => __( 'Disabled (not recommended)', 'ayecode-connect' ),
							],
							'default'          => 'core-popper',
							'extra_attributes' => ! empty( $overrides['js'] ) ? [ 'disabled' => true ] : [],
						],
						[
							'id'               => 'html_font_size',
							'type'             => 'number',
							'label'            => __( 'HTML Font Size (px)', 'ayecode-connect' ),
							'desc'             => __( 'Our font sizing is rem (responsive based) here you can set the html font size in-case your theme is setting it too low.', 'ayecode-connect' ),
							'default'          => 16,
							'min'              => 10,
							'max'              => 24,
							'placeholder'      => '16',
							'extra_attributes' => ! empty( $overrides['html_font_size'] ) ? [ 'disabled' => true ] : [],
						],
					],
				],

				[
					'id'     => 'backend',
					'name'   => __( 'Backend (wp-admin)', 'ayecode-connect' ),
					'icon'   => 'fa-solid fa-gauge',
					'fields' => [
						[
							'id'               => 'css_backend',
							'type'             => 'select',
							'label'            => __( 'Load CSS', 'ayecode-connect' ),
							'options'          => [
								'compatibility' => __( 'Compatibility Mode (default)', 'ayecode-connect' ),
								'core'          => __( 'Full Mode (will cause style issues)', 'ayecode-connect' ),
								''              => __( 'Disabled', 'ayecode-connect' ),
							],
							'default'          => 'compatibility',
							'extra_attributes' => ! empty( $overrides['css_backend'] ) ? [ 'disabled' => true ] : [],
						],
						[
							'id'               => 'js_backend',
							'type'             => 'select',
							'label'            => __( 'Load JS', 'ayecode-connect' ),
							'options'          => [
								'core-popper' => __( 'Core + Popper (default)', 'ayecode-connect' ),
								'popper'      => __( 'Popper', 'ayecode-connect' ),
								'required'    => __( 'Required functions only', 'ayecode-connect' ),
								''            => __( 'Disabled (not recommended)', 'ayecode-connect' ),
							],
							'default'          => 'core-popper',
							'extra_attributes' => ! empty( $overrides['js_backend'] ) ? [ 'disabled' => true ] : [],
						],
						[
							'id'          => 'disable_admin',
							'type'        => 'textarea',
							'label'       => __( 'Disable load on URL', 'ayecode-connect' ),
							'desc'        => __( 'If you have backend conflict you can enter a partial URL argument that will disable the loading of AUI on those pages. Add each argument on a new line.', 'ayecode-connect' ),
							'rows'        => 10,
							'placeholder' => "myplugin.php\naction=go",
							'class'       => 'large-text code',
						],
					],
				],

				[
					'id'     => 'tools',
					'name'   => __( 'Tools', 'ayecode-connect' ),
					'icon'   => 'fa-solid fa-wrench',
					'fields' => [
						[
							'id'             => 'reset_settings',
							'type'           => 'action_button',
							'label'          => __( 'Reset Settings', 'ayecode-connect' ),
							'description'    => __( 'Reset all settings to their default values.', 'ayecode-connect' ),
							'button_text'    => __( 'Reset to Defaults', 'ayecode-connect' ),
							'button_class'   => 'btn-danger',
							'ajax_action'    => 'reset_settings',
							'confirm'        => true,
							'confirm_message' => __( 'Are you sure you want to reset all settings to their default values? This cannot be undone.', 'ayecode-connect' ),
						],
					],
				],

				[
					'id'     => 'info',
					'name'   => __( 'Info', 'ayecode-connect' ),
					'icon'   => 'fa-solid fa-circle-info',
					'fields' => [
						[
							'type'        => 'alert',
							'label'       => __( 'Version Information', 'ayecode-connect' ),
							'description' => sprintf(
								/* translators: 1: version number, 2: source plugin/theme name */
								__( 'Version: %1$s<br>Loaded from: %2$s', 'ayecode-connect' ),
								$version,
								$this->get_load_source()
							),
							'alert_type' => 'info',
						],
					],
				],
			],
		];
	}

	/**
	 * Get default settings.
	 *
	 * @return array Default settings values.
	 */
	protected function get_default_settings(): array {
		return [
			'bs_ver'         => '5dm',
			'load_mode'      => 'auto',
			'css'            => 'compatibility',
			'js'             => 'core-popper',
			'html_font_size' => 16,
			'css_backend'    => 'compatibility',
			'js_backend'     => 'core-popper',
			'disable_admin'  => '',
		];
	}

	/**
	 * Show admin notice if backend scripts are not loaded correctly.
	 *
	 * @return void
	 */
	public function show_admin_style_notice(): void {
		$screen = get_current_screen();
		if ( ! $screen || $screen->id === 'settings_page_' . $this->page_slug ) {
			return;
		}

		$settings = $this->get_settings();

		if ( $settings['css_backend'] === 'compatibility' && $settings['js_backend'] === 'core-popper' ) {
			return;
		}

		$fix_url = admin_url( 'options-general.php?page=' . $this->page_slug . '&aui-fix-admin=true&nonce=' . wp_create_nonce( 'aui-fix-admin' ) );
		$button  = '<a href="' . esc_url( $fix_url ) . '" class="button-primary">' . esc_html__( 'Fix Now', 'ayecode-connect' ) . '</a>';
		$message = __( '<b>Style Issue:</b> AyeCode UI is disabled or set wrong.', 'ayecode-connect' ) . ' ' . $button;

		echo '<div class="notice notice-error aui-settings-error-notice"><p>' . wp_kses_post( $message ) . '</p></div>';
	}

	/**
	 * Maybe fix admin settings when the "Fix Now" link is clicked.
	 *
	 * @return void
	 */
	public function maybe_fix_admin_settings(): void {
		if ( ! isset( $_GET['aui-fix-admin'], $_GET['nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'aui-fix-admin' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings                  = $this->get_settings();
		$settings['css_backend']   = 'compatibility';
		$settings['js_backend']    = 'core-popper';
		update_option( $this->option_name, $settings );

		wp_safe_redirect( admin_url( 'options-general.php?page=' . $this->page_slug ) );
		exit;
	}

	/**
	 * Get the source plugin or theme loading AUI.
	 *
	 * @return string Source name.
	 */
	private function get_load_source(): string {
		$file        = str_replace( [ '/', '\\' ], '/', realpath( AYECODE_UI_PLUGIN_FILE ) );
		$plugins_dir = str_replace( [ '/', '\\' ], '/', realpath( WP_PLUGIN_DIR ) );

		$source = [];
		if ( strpos( $file, $plugins_dir ) !== false ) {
			$source = explode( '/', plugin_basename( $file ) );
		} elseif ( function_exists( 'get_theme_root' ) ) {
			$themes_dir = str_replace( [ '/', '\\' ], '/', realpath( get_theme_root() ) );

			if ( strpos( $file, $themes_dir ) !== false ) {
				$source = explode( '/', ltrim( str_replace( $themes_dir, '', $file ), '/' ) );
			}
		}

		return isset( $source[0] ) ? esc_attr( $source[0] ) : '';
	}
}
