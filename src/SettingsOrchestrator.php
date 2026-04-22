<?php
/**
 * AyeCode UI Settings Orchestrator
 *
 * Coordinates all AyeCode UI functionality by delegating to specialised sub-classes.
 *
 * @package AyeCode\UI
 */

namespace AyeCode\UI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SettingsOrchestrator class.
 *
 * Initialises settings, defines color constants, registers body classes,
 * delegates to AssetManager/Customizer/Admin, and exposes builder-detection
 * utilities used by other packages.
 *
 * All WordPress hook registrations live in {@see Loader}; this class contains
 * only business logic.
 */
class SettingsOrchestrator {

	/**
	 * Bootstrap version shipped with this package.
	 *
	 * @var string
	 */
	public string $latest = '5.3.6';

	/**
	 * Choices.js version shipped with this package.
	 *
	 * @var string
	 */
	public string $choices_version = '11.1.0';

	/**
	 * Human-readable package name.
	 *
	 * @var string
	 */
	public string $name = 'AyeCode UI';

	/**
	 * Settings sub-class instance.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * AssetManager sub-class instance.
	 *
	 * @var AssetManager
	 */
	private AssetManager $asset_manager;

	/**
	 * Singleton instance.
	 *
	 * @var SettingsOrchestrator|null
	 */
	private static ?SettingsOrchestrator $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return SettingsOrchestrator
	 */
	public static function instance(): SettingsOrchestrator {
		if ( null === self::$instance ) {
			self::$instance = new self();
			do_action( 'ayecode_ui_settings_loaded' );
		}

		return self::$instance;
	}

	/**
	 * Constructor — initialises sub-class instances; no hook registrations.
	 */
	private function __construct() {
		$this->settings      = Settings::instance();
		$this->asset_manager = AssetManager::instance();
	}

	/**
	 * Run initialisation tasks hooked to `init`.
	 *
	 * Defines color constants, registers body-class filters, includes conversion
	 * utilities, and sets up conditional frontend asset hooks based on load mode.
	 *
	 * @return void
	 */
	public function init(): void {
		// Handle fix-admin request (submitted before headers are sent).
		if ( ! empty( $_REQUEST['aui-fix-admin'] ) && ! empty( $_REQUEST['nonce'] )
			&& wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['nonce'] ) ), 'aui-fix-admin' )
		) {
			$db_settings = get_option( 'ayecode-ui-settings', [] );
			if ( ! empty( $db_settings ) ) {
				$db_settings['css_backend'] = 'compatibility';
				$db_settings['js_backend']  = 'core-popper';
				update_option( 'ayecode-ui-settings', $db_settings );
				wp_safe_redirect( admin_url( 'options-general.php?page=ayecode-ui-settings&updated=true' ) );
				exit;
			}
		}

		$this->define_color_constants();

		// Add BS5 body classes.
		add_filter( 'admin_body_class', [ $this, 'add_bs5_admin_body_class' ], 99, 1 );
		add_filter( 'body_class', [ $this, 'add_bs5_body_class' ] );

		// Load conversion utilities (contains helper functions, not classes).
		include_once AYECODE_UI_PLUGIN_DIR . 'includes/inc/bs-conversion.php';

		// Conditional frontend asset hooks based on load mode.
		$load_mode = $this->settings->get_load_mode();
		$settings  = $this->settings->get_settings();

		if ( $load_mode === 'always' || $load_mode === 'manual' ) {
			if ( $settings['css'] ) {
				add_action( 'wp_enqueue_scripts', [ $this->asset_manager, 'enqueue_style' ], 10 );
			}
			if ( $settings['js'] ) {
				add_action( 'wp_enqueue_scripts', [ $this->asset_manager, 'enqueue_scripts' ], 10 );
			}
		}
		// 'auto' mode is handled by AssetManager's lazy-loading via Loader hooks.

		// Backend loading is not lazy.
		if ( $settings['css_backend'] ) {
			add_action( 'admin_enqueue_scripts', [ $this->asset_manager, 'enqueue_style' ], 1 );
		}
		if ( $settings['js_backend'] ) {
			add_action( 'admin_enqueue_scripts', [ $this->asset_manager, 'enqueue_scripts' ], 1 );
		}

		// HTML font size.
		if ( $settings['html_font_size'] ) {
			add_action( 'wp_footer', [ $this, 'html_font_size' ], 10 );
		}
	}

	/**
	 * Define AUI color constants (original values + overridable aliases).
	 *
	 * @return void
	 */
	private function define_color_constants(): void {
		define( 'AUI_PRIMARY_COLOR_ORIGINAL',   '#1e73be' );
		define( 'AUI_SECONDARY_COLOR_ORIGINAL', '#6c757d' );
		define( 'AUI_INFO_COLOR_ORIGINAL',      '#17a2b8' );
		define( 'AUI_WARNING_COLOR_ORIGINAL',   '#ffc107' );
		define( 'AUI_DANGER_COLOR_ORIGINAL',    '#dc3545' );
		define( 'AUI_SUCCESS_COLOR_ORIGINAL',   '#44c553' );
		define( 'AUI_LIGHT_COLOR_ORIGINAL',     '#f8f9fa' );
		define( 'AUI_DARK_COLOR_ORIGINAL',      '#343a40' );
		define( 'AUI_WHITE_COLOR_ORIGINAL',     '#fff' );
		define( 'AUI_PURPLE_COLOR_ORIGINAL',    '#ad6edd' );
		define( 'AUI_SALMON_COLOR_ORIGINAL',    '#ff977a' );
		define( 'AUI_CYAN_COLOR_ORIGINAL',      '#35bdff' );
		define( 'AUI_GRAY_COLOR_ORIGINAL',      '#ced4da' );
		define( 'AUI_INDIGO_COLOR_ORIGINAL',    '#502c6c' );
		define( 'AUI_ORANGE_COLOR_ORIGINAL',    '#orange' );
		define( 'AUI_BLACK_COLOR_ORIGINAL',     '#000' );

		if ( ! defined( 'AUI_PRIMARY_COLOR' ) )   { define( 'AUI_PRIMARY_COLOR',   AUI_PRIMARY_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_SECONDARY_COLOR' ) ) { define( 'AUI_SECONDARY_COLOR', AUI_SECONDARY_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_INFO_COLOR' ) )      { define( 'AUI_INFO_COLOR',      AUI_INFO_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_WARNING_COLOR' ) )   { define( 'AUI_WARNING_COLOR',   AUI_WARNING_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_DANGER_COLOR' ) )    { define( 'AUI_DANGER_COLOR',    AUI_DANGER_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_SUCCESS_COLOR' ) )   { define( 'AUI_SUCCESS_COLOR',   AUI_SUCCESS_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_LIGHT_COLOR' ) )     { define( 'AUI_LIGHT_COLOR',     AUI_LIGHT_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_DARK_COLOR' ) )      { define( 'AUI_DARK_COLOR',      AUI_DARK_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_WHITE_COLOR' ) )     { define( 'AUI_WHITE_COLOR',     AUI_WHITE_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_PURPLE_COLOR' ) )    { define( 'AUI_PURPLE_COLOR',    AUI_PURPLE_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_SALMON_COLOR' ) )    { define( 'AUI_SALMON_COLOR',    AUI_SALMON_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_CYAN_COLOR' ) )      { define( 'AUI_CYAN_COLOR',      AUI_CYAN_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_GRAY_COLOR' ) )      { define( 'AUI_GRAY_COLOR',      AUI_GRAY_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_INDIGO_COLOR' ) )    { define( 'AUI_INDIGO_COLOR',    AUI_INDIGO_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_ORANGE_COLOR' ) )    { define( 'AUI_ORANGE_COLOR',    AUI_ORANGE_COLOR_ORIGINAL ); }
		if ( ! defined( 'AUI_BLACK_COLOR' ) )     { define( 'AUI_BLACK_COLOR',     AUI_BLACK_COLOR_ORIGINAL ); }
	}

	/**
	 * Get all color constants as an associative array.
	 *
	 * @param bool $original Whether to return the original (non-overridden) values.
	 * @return array<string, string> Color map keyed by color name.
	 */
	public static function get_colors( bool $original = false ): array {
		if ( ! defined( 'AUI_PRIMARY_COLOR' ) ) {
			return [];
		}

		if ( $original ) {
			return [
				'primary'   => AUI_PRIMARY_COLOR_ORIGINAL,
				'secondary' => AUI_SECONDARY_COLOR_ORIGINAL,
				'info'      => AUI_INFO_COLOR_ORIGINAL,
				'warning'   => AUI_WARNING_COLOR_ORIGINAL,
				'danger'    => AUI_DANGER_COLOR_ORIGINAL,
				'success'   => AUI_SUCCESS_COLOR_ORIGINAL,
				'light'     => AUI_LIGHT_COLOR_ORIGINAL,
				'dark'      => AUI_DARK_COLOR_ORIGINAL,
				'white'     => AUI_WHITE_COLOR_ORIGINAL,
				'purple'    => AUI_PURPLE_COLOR_ORIGINAL,
				'salmon'    => AUI_SALMON_COLOR_ORIGINAL,
				'cyan'      => AUI_CYAN_COLOR_ORIGINAL,
				'gray'      => AUI_GRAY_COLOR_ORIGINAL,
				'indigo'    => AUI_INDIGO_COLOR_ORIGINAL,
				'orange'    => AUI_ORANGE_COLOR_ORIGINAL,
				'black'     => AUI_BLACK_COLOR_ORIGINAL,
			];
		}

		return [
			'primary'   => AUI_PRIMARY_COLOR,
			'secondary' => AUI_SECONDARY_COLOR,
			'info'      => AUI_INFO_COLOR,
			'warning'   => AUI_WARNING_COLOR,
			'danger'    => AUI_DANGER_COLOR,
			'success'   => AUI_SUCCESS_COLOR,
			'light'     => AUI_LIGHT_COLOR,
			'dark'      => AUI_DARK_COLOR,
			'white'     => AUI_WHITE_COLOR,
			'purple'    => AUI_PURPLE_COLOR,
			'salmon'    => AUI_SALMON_COLOR,
			'cyan'      => AUI_CYAN_COLOR,
			'gray'      => AUI_GRAY_COLOR,
			'indigo'    => AUI_INDIGO_COLOR,
			'orange'    => AUI_ORANGE_COLOR,
			'black'     => AUI_BLACK_COLOR,
		];
	}

	/**
	 * Append the `aui_bs5` class to the admin body class string.
	 *
	 * @param string $classes Space-separated class string.
	 * @return string Modified class string.
	 */
	public function add_bs5_admin_body_class( string $classes = '' ): string {
		$classes .= ' aui_bs5';
		return $classes;
	}

	/**
	 * Append the `aui_bs5` class to the frontend body classes array.
	 *
	 * @param array $classes Existing body classes.
	 * @return array Modified body classes.
	 */
	public function add_bs5_body_class( array $classes ): array {
		$classes[] = 'aui_bs5';
		return $classes;
	}

	/**
	 * Delegate to Settings::register_settings() — called on `admin_init`.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		$this->settings->register_settings();
	}

	/**
	 * Output an inline `<style>` tag setting the base HTML font size.
	 *
	 * Hooked to `wp_footer` when the `html_font_size` setting is non-zero.
	 *
	 * @return void
	 */
	public function html_font_size(): void {
		$settings = $this->settings->get_settings();
		echo '<style>html{font-size:' . absint( $settings['html_font_size'] ) . 'px;}</style>';
	}

	/**
	 * Output developer component examples when the `?preview-aui` query arg is present.
	 *
	 * Requires `manage_options` capability. Terminates execution after output.
	 *
	 * @return void
	 */
	public function maybe_show_examples(): void {
		if ( ! isset( $_GET['preview-aui'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<!DOCTYPE html><html><head><title>AyeCode UI Examples</title>';
		wp_head();
		echo '</head><body class="aui-examples">';
		echo '<h1>AyeCode UI v3 — Bootstrap 5.3+ Examples</h1>';
		echo '<p>Add component examples here.</p>';
		wp_footer();
		echo '</body></html>';
		exit;
	}

	/**
	 * Delegate select2/choices.js enqueueing to AssetManager.
	 *
	 * @return void
	 */
	public function enqueue_select2(): void {
		$this->asset_manager->enqueue_select2();
	}

	/**
	 * Delegate flatpickr enqueueing to AssetManager.
	 *
	 * @return void
	 */
	public function enqueue_flatpickr(): void {
		$this->asset_manager->enqueue_flatpickr();
	}

	/**
	 * Delegate icon-picker enqueueing to AssetManager.
	 *
	 * @return void
	 */
	public function enqueue_iconpicker(): void {
		$this->asset_manager->enqueue_iconpicker();
	}

	// =========================================================================
	// Builder & preview detection utilities
	// =========================================================================

	/**
	 * Check whether the active theme is a block (FSE) theme.
	 *
	 * @return bool
	 */
	public static function is_block_theme(): bool {
		return function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	}

	/**
	 * Check whether the current admin screen is the block editor.
	 *
	 * @return bool
	 */
	public static function is_block_editor(): bool {
		if ( ! is_admin() ) {
			return false;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return ! empty( $screen ) && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor();
	}

	/**
	 * Check whether the current AJAX call is a block-content render request.
	 *
	 * @return bool
	 */
	public static function is_block_content_call(): bool {
		return wp_doing_ajax()
			&& isset( $_REQUEST['action'] )
			&& $_REQUEST['action'] === 'super_duper_output_shortcode';
	}

	/**
	 * Check whether output is inside a Divi builder preview.
	 *
	 * @return bool
	 */
	public static function is_divi_preview(): bool {
		return isset( $_REQUEST['et_fb'] )
			|| isset( $_REQUEST['et_pb_preview'] )
			|| ( is_admin() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'elementor' );
	}

	/**
	 * Check whether output is inside an Elementor preview.
	 *
	 * @return bool
	 */
	public static function is_elementor_preview(): bool {
		return isset( $_REQUEST['elementor-preview'] )
			|| ( is_admin() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'elementor' )
			|| ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'elementor_ajax' );
	}

	/**
	 * Check whether output is inside a Beaver Builder preview.
	 *
	 * @return bool
	 */
	public static function is_beaver_preview(): bool {
		return isset( $_REQUEST['fl_builder'] );
	}

	/**
	 * Check whether output is inside a SiteOrigin page-builder preview.
	 *
	 * @return bool
	 */
	public static function is_siteorigin_preview(): bool {
		return ! empty( $_REQUEST['siteorigin_panels_live_editor'] );
	}

	/**
	 * Check whether output is inside a Cornerstone builder preview.
	 *
	 * @return bool
	 */
	public static function is_cornerstone_preview(): bool {
		return ! empty( $_REQUEST['cornerstone_preview'] )
			|| ( ! empty( $_SERVER['REQUEST_URI'] ) && basename( $_SERVER['REQUEST_URI'] ) === 'cornerstone-endpoint' );
	}

	/**
	 * Check whether output is inside an Avada Fusion builder preview.
	 *
	 * @return bool
	 */
	public static function is_fusion_preview(): bool {
		return ! empty( $_REQUEST['fb-edit'] ) || ! empty( $_REQUEST['fusion_load_nonce'] );
	}

	/**
	 * Check whether output is inside an Oxygen builder preview.
	 *
	 * @return bool
	 */
	public static function is_oxygen_preview(): bool {
		return ! empty( $_REQUEST['ct_builder'] )
			|| ( ! empty( $_REQUEST['action'] )
				&& ( str_starts_with( $_REQUEST['action'], 'oxy_render_' )
					|| str_starts_with( $_REQUEST['action'], 'ct_render_' ) ) );
	}

	/**
	 * Check whether output is inside a Kallyas / Zion builder preview.
	 *
	 * @return bool
	 */
	public static function is_kallyas_zion_preview(): bool {
		return function_exists( 'znhg_kallyas_theme_config' ) && ! empty( $_REQUEST['zn_pb_edit'] );
	}

	/**
	 * Check whether output is inside a Bricks theme builder preview.
	 *
	 * @return bool
	 */
	public static function is_bricks_preview(): bool {
		return function_exists( 'bricks_is_builder' ) && ( bricks_is_builder() || bricks_is_builder_call() );
	}

	/**
	 * General preview detection — returns true if any supported page-builder is active.
	 *
	 * @return bool
	 */
	public static function is_preview(): bool {
		return self::is_block_editor()
			|| self::is_block_content_call()
			|| self::is_divi_preview()
			|| self::is_elementor_preview()
			|| self::is_beaver_preview()
			|| self::is_siteorigin_preview()
			|| self::is_cornerstone_preview()
			|| self::is_fusion_preview()
			|| self::is_oxygen_preview()
			|| self::is_kallyas_zion_preview()
			|| self::is_bricks_preview();
	}
}
