<?php
/**
 * AyeCode UI Admin Interface
 *
 * Handles the admin settings page using the AyeCode Settings Framework.
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
		$overrides        = apply_filters( 'ayecode_ui_settings', array(), array(), array() );
		$current_settings = get_option( $this->option_name, array() );
		$version          = AYECODE_UI_VERSION;

		return array(
			// array_filter: the Performance section is empty on sites where it does not apply.
			'sections' => array_values( array_filter( array(
				array(
					'id'          => 'bootstrap',
					'name'        => __( 'Bootstrap Version', 'ayecode-connect' ),
					'icon'        => 'fa-solid fa-code',
					'description' => __( 'This package now only supports Bootstrap 5.3+ (with dark mode support).', 'ayecode-connect' ),
					'fields'      => array(
						array(
							'id'               => 'bs_ver',
							'type'             => 'select',
							'label'            => __( 'Bootstrap Version', 'ayecode-connect' ),
							'desc'             => __( 'Select the Bootstrap version to use.', 'ayecode-connect' ),
							'options'          => apply_filters(
								'ayecode_ui_versions',
								array(
									'5dm' => __( 'Bootstrap 5.3+ (Dark Mode) - v3.0', 'ayecode-connect' ),
								),
								$current_settings,
								$overrides
							),
							'default'          => '5dm',
							'extra_attributes' => ! empty( $overrides['bs_ver'] ) ? array( 'disabled' => true ) : array(),
						),
					),
				),

				array(
					'id'     => 'loading',
					'name'   => __( 'Asset Loading', 'ayecode-connect' ),
					'icon'   => 'fa-solid fa-bolt',
					'fields' => array(
						array(
							'id'      => 'load_mode',
							'type'    => 'select',
							'label'   => __( 'Load Mode', 'ayecode-connect' ),
							'desc'    => __( 'Auto mode only loads assets when AyeCode blocks are detected, improving performance.', 'ayecode-connect' ),
							'options' => array(
								'auto'   => __( 'Auto (recommended) - Only when blocks detected', 'ayecode-connect' ),
								'always' => __( 'Always - Load on all pages', 'ayecode-connect' ),
								'manual' => __( 'Manual - Developer controlled', 'ayecode-connect' ),
							),
							'default' => 'auto',
						),
					),
				),

				$this->performance_section( $current_settings ),

				array(
					'id'     => 'frontend',
					'name'   => __( 'Frontend', 'ayecode-connect' ),
					'icon'   => 'fa-solid fa-display',
					'fields' => array(
						array(
							'id'               => 'css',
							'type'             => 'select',
							'label'            => __( 'Load CSS', 'ayecode-connect' ),
							'options'          => array(
								'compatibility' => __( 'Compatibility Mode (default)', 'ayecode-connect' ),
								'core'          => __( 'Full Mode', 'ayecode-connect' ),
								''              => __( 'Disabled', 'ayecode-connect' ),
							),
							'default'          => 'compatibility',
							'extra_attributes' => ! empty( $overrides['css'] ) ? array( 'disabled' => true ) : array(),
						),
						array(
							'id'               => 'js',
							'type'             => 'select',
							'label'            => __( 'Load JS', 'ayecode-connect' ),
							'options'          => array(
								'core-popper' => __( 'Core + Popper (default)', 'ayecode-connect' ),
								'popper'      => __( 'Popper', 'ayecode-connect' ),
								'required'    => __( 'Required functions only', 'ayecode-connect' ),
								''            => __( 'Disabled (not recommended)', 'ayecode-connect' ),
							),
							'default'          => 'core-popper',
							'extra_attributes' => ! empty( $overrides['js'] ) ? array( 'disabled' => true ) : array(),
						),
						array(
							'id'               => 'html_font_size',
							'type'             => 'number',
							'label'            => __( 'HTML Font Size (px)', 'ayecode-connect' ),
							'desc'             => __( 'Our font sizing is rem (responsive based) here you can set the html font size in-case your theme is setting it too low.', 'ayecode-connect' ),
							'default'          => 16,
							'min'              => 10,
							'max'              => 24,
							'placeholder'      => '16',
							'extra_attributes' => ! empty( $overrides['html_font_size'] ) ? array( 'disabled' => true ) : array(),
						),
					),
				),

				array(
					'id'     => 'backend',
					'name'   => __( 'Backend (wp-admin)', 'ayecode-connect' ),
					'icon'   => 'fa-solid fa-gauge',
					'fields' => array(
						array(
							'id'               => 'css_backend',
							'type'             => 'select',
							'label'            => __( 'Load CSS', 'ayecode-connect' ),
							'options'          => array(
								'compatibility' => __( 'Compatibility Mode (default)', 'ayecode-connect' ),
								'core'          => __( 'Full Mode (will cause style issues)', 'ayecode-connect' ),
								''              => __( 'Disabled', 'ayecode-connect' ),
							),
							'default'          => 'compatibility',
							'extra_attributes' => ! empty( $overrides['css_backend'] ) ? array( 'disabled' => true ) : array(),
						),
						array(
							'id'               => 'js_backend',
							'type'             => 'select',
							'label'            => __( 'Load JS', 'ayecode-connect' ),
							'options'          => array(
								'core-popper' => __( 'Core + Popper (default)', 'ayecode-connect' ),
								'popper'      => __( 'Popper', 'ayecode-connect' ),
								'required'    => __( 'Required functions only', 'ayecode-connect' ),
								''            => __( 'Disabled (not recommended)', 'ayecode-connect' ),
							),
							'default'          => 'core-popper',
							'extra_attributes' => ! empty( $overrides['js_backend'] ) ? array( 'disabled' => true ) : array(),
						),
						array(
							'id'          => 'disable_admin',
							'type'        => 'textarea',
							'label'       => __( 'Disable load on URL', 'ayecode-connect' ),
							'desc'        => __( 'If you have backend conflict you can enter a partial URL argument that will disable the loading of AUI on those pages. Add each argument on a new line.', 'ayecode-connect' ),
							'rows'        => 10,
							'placeholder' => "myplugin.php\naction=go",
							'class'       => 'large-text code',
						),
					),
				),

				array(
					'id'     => 'tools',
					'name'   => __( 'Tools', 'ayecode-connect' ),
					'icon'   => 'fa-solid fa-wrench',
					'fields' => array(
						array(
							'type'        => 'alert',
							'label'       => __( 'Test page', 'ayecode-connect' ),
							'description' => sprintf(
								/* translators: %s: test page URL */
								__( 'Every Bootstrap component and AyeCode UI extra on one page, inside your theme, to check how they look in light and dark mode. Only administrators can open it. <a href="%s" target="_blank">Open the test page</a>', 'ayecode-connect' ),
								esc_url( add_query_arg( Preview::QUERY_VAR, '1', home_url( '/' ) ) )
							),
							'alert_type'  => 'info',
						),
						array(
							'id'              => 'reset_settings',
							'type'            => 'action_button',
							'label'           => __( 'Reset Settings', 'ayecode-connect' ),
							'description'     => __( 'Reset all settings to their default values.', 'ayecode-connect' ),
							'button_text'     => __( 'Reset to Defaults', 'ayecode-connect' ),
							'button_class'    => 'btn-danger',
							'ajax_action'     => 'reset_settings',
							'confirm'         => true,
							'confirm_message' => __( 'Are you sure you want to reset all settings to their default values? This cannot be undone.', 'ayecode-connect' ),
						),
					),
				),

				array(
					'id'     => 'info',
					'name'   => __( 'Info', 'ayecode-connect' ),
					'icon'   => 'fa-solid fa-circle-info',
					'fields' => array(
						array(
							'type'        => 'alert',
							'label'       => __( 'Version Information', 'ayecode-connect' ),
							'description' => sprintf(
								/* translators: 1: version number, 2: source plugin/theme name */
								__( 'Version: %1$s<br>Loaded from: %2$s', 'ayecode-connect' ),
								$version,
								$this->get_load_source()
							),
							'alert_type'  => 'info',
						),
					),
				),
			) ) ),
		);
	}

	/**
	 * Get default settings.
	 *
	 * @return array Default settings values.
	 */
	protected function get_default_settings(): array {
		return array(
			'bs_ver'         => '5dm',
			'load_mode'      => 'auto',
			'css'            => 'compatibility',
			'js'             => 'core-popper',
			'html_font_size' => 16,
			'css_backend'    => 'compatibility',
			'js_backend'     => 'core-popper',
			'disable_admin'  => '',
		);
	}

	/**
	 * The Performance section: two front end switches for the BlockStrap setup.
	 *
	 * Empty on any other setup, so the section does not appear there.
	 *
	 * @param array $current_settings Saved settings.
	 * @return array Section config, or an empty array.
	 */
	private function performance_section( array $current_settings ): array {
		if ( ! Performance::is_available() ) {
			return array();
		}

		$seen   = Performance::seen_blocks();
		$fields = array(
			array(
				'id'      => 'frontend_widgets',
				'type'    => 'select',
				'label'   => __( 'Front end widgets', 'ayecode-connect' ),
				'desc'    => __( 'Skip loading WordPress\'s 18 built-in widgets on the front end. A block theme renders no widget area, so they are built on every page for nothing (about 120 KB). Leave off if this site uses a widget area or the Legacy Widget block. The admin, the Customizer and the REST API always load them.', 'ayecode-connect' ),
				'options' => array(
					'off' => __( 'Off (WordPress default)', 'ayecode-connect' ),
					'on'  => __( 'On - skip the built-in widgets', 'ayecode-connect' ),
				),
				'default' => 'off',
			),
			array(
				'id'      => 'frontend_blocks',
				'type'    => 'select',
				'label'   => __( 'Front end core blocks', 'ayecode-connect' ),
				'desc'    => __( 'Register only the core blocks ticked below on the front end. WordPress registers all 116 on every page, with a stylesheet handle and theme.json data each, while a BlockStrap page uses a few dozen (about 560 KB a page). A block that is not ticked still saves in the editor; on the page it renders its saved HTML without its block styles, and a dynamic block such as Latest Posts renders nothing. Blocks met on the front end that are not ticked are listed below. The editor and the REST API always register every block.', 'ayecode-connect' ),
				'options' => array(
					'off' => __( 'Off (WordPress default)', 'ayecode-connect' ),
					'on'  => __( 'On - only the ticked blocks', 'ayecode-connect' ),
				),
				'default' => 'off',
			),
			array(
				'id'      => 'frontend_blocks_enabled',
				'type'    => 'checkbox_group',
				'label'   => __( 'Core blocks to register', 'ayecode-connect' ),
				'desc'    => __( 'The ticked blocks are the ones the BlockStrap theme, the BlockStrap Blocks plugin, the design kits and GeoDirectory use. The skip link is always registered: it is a core feature, not content.', 'ayecode-connect' ),
				'options' => Performance::core_block_choices(),
				'default' => Performance::DEFAULT_BLOCKS,
			),
		);

		if ( ! empty( $seen ) ) {
			$enable_url = wp_nonce_url( admin_url( 'options-general.php?page=' . $this->page_slug . '&aui-enable-seen-blocks=1' ), 'aui-enable-seen-blocks', 'nonce' );
			$fields[]   = array(
				'type'        => 'alert',
				'label'       => __( 'Blocks seen on the front end that are not registered', 'ayecode-connect' ),
				'description' => sprintf(
					/* translators: 1: comma separated block names, 2: URL that enables them. */
					__( '%1$s. <a href="%2$s">Enable these blocks</a>.', 'ayecode-connect' ),
					esc_html( implode( ', ', $seen ) ),
					esc_url( $enable_url )
				),
				'alert_type'  => 'warning',
			);
		}

		return array(
			'id'     => 'performance',
			'name'   => __( 'Performance', 'ayecode-connect' ),
			'icon'   => 'fa-solid fa-gauge-high',
			'fields' => $fields,
		);
	}

	/**
	 * Handle the "enable these blocks" link on the settings page.
	 *
	 * @return void
	 */
	public function maybe_enable_seen_blocks(): void {
		if ( ! isset( $_GET['aui-enable-seen-blocks'], $_GET['nonce'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'aui-enable-seen-blocks' ) ) {
			return;
		}

		Performance::enable_seen_blocks();

		wp_safe_redirect( admin_url( 'options-general.php?page=' . $this->page_slug ) );
		exit;
	}

	/**
	 * Tell the admin when the front end met core blocks that are not registered.
	 *
	 * @return void
	 */
	public function show_seen_blocks_notice(): void {
		if ( ! Performance::is_available() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$seen = Performance::seen_blocks();
		if ( empty( $seen ) ) {
			return;
		}

		$settings_url = admin_url( 'options-general.php?page=' . $this->page_slug );
		$enable_url   = wp_nonce_url( $settings_url . '&aui-enable-seen-blocks=1', 'aui-enable-seen-blocks', 'nonce' );

		printf(
			'<div class="notice notice-warning"><p>%s <a href="%s">%s</a> | <a href="%s">%s</a></p></div>',
			sprintf(
				/* translators: %s: comma separated block names. */
				esc_html__( 'AyeCode UI: these core blocks rendered on the front end without being registered, so they show without their block styles: %s.', 'ayecode-connect' ),
				esc_html( implode( ', ', $seen ) )
			),
			esc_url( $enable_url ),
			esc_html__( 'Enable them', 'ayecode-connect' ),
			esc_url( $settings_url ),
			esc_html__( 'Settings', 'ayecode-connect' )
		);
	}

	/**
	 * Show admin notice if backend scripts are not loaded correctly.
	 *
	 * @return void
	 */
	public function show_admin_style_notice(): void {
		// The notice carries a fix-admin nonce, so only show it to users who may act on it.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'settings_page_' . $this->page_slug === $screen->id ) {
			return;
		}

		$settings = $this->get_settings();

		if ( 'compatibility' === $settings['css_backend'] && 'core-popper' === $settings['js_backend'] ) {
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

		$settings                = $this->get_settings();
		$settings['css_backend'] = 'compatibility';
		$settings['js_backend']  = 'core-popper';
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
		$file        = str_replace( array( '/', '\\' ), '/', realpath( AYECODE_UI_PLUGIN_FILE ) );
		$plugins_dir = str_replace( array( '/', '\\' ), '/', realpath( WP_PLUGIN_DIR ) );

		$source = array();
		if ( strpos( $file, $plugins_dir ) !== false ) {
			$source = explode( '/', plugin_basename( $file ) );
		} elseif ( function_exists( 'get_theme_root' ) ) {
			$themes_dir = str_replace( array( '/', '\\' ), '/', realpath( get_theme_root() ) );

			if ( strpos( $file, $themes_dir ) !== false ) {
				$source = explode( '/', ltrim( str_replace( $themes_dir, '', $file ), '/' ) );
			}
		}

		return isset( $source[0] ) ? esc_attr( $source[0] ) : '';
	}
}
