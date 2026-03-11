<?php
/**
 * AyeCode UI Settings - Main Orchestrator
 *
 * This class coordinates all AyeCode UI functionality by delegating to specialized classes.
 * Refactored for v2.0.0 to support Bootstrap 5.3+ only with modern PHP 7.4+ features.
 *
 * @since 1.0.0
 * @since 2.0.0 Refactored into modular architecture
 * @package AyeCode_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AyeCode_UI_Settings' ) ) {

	/**
	 * Main AyeCode UI Settings orchestrator class.
	 *
	 * Coordinates settings, assets, customizer, CSS generation, and admin interface.
	 */
	class AyeCode_UI_Settings {

		/**
		 * Class version.
		 *
		 * @var string
		 */
		public string $version = '2.0.0';

		/**
		 * Textdomain.
		 *
		 * @var string
		 */
		public string $textdomain = 'aui';

		/**
		 * Latest Bootstrap version.
		 *
		 * @var string
		 */
		public string $latest = '5.3.6';

		/**
		 * Choices.js version.
		 *
		 * @var string
		 */
		public string $choices_version = '11.1.0';

		/**
		 * Package name.
		 *
		 * @var string
		 */
		public string $name = 'AyeCode UI';

		/**
		 * Settings instance.
		 *
		 * @var AUI_Settings
		 */
		private AUI_Settings $settings;

		/**
		 * Asset manager instance.
		 *
		 * @var AUI_Asset_Manager
		 */
		private AUI_Asset_Manager $asset_manager;

		/**
		 * CSS generator instance.
		 *
		 * @var AUI_CSS_Generator
		 */
		private AUI_CSS_Generator $css_generator;

		/**
		 * Customizer instance.
		 *
		 * @var AUI_Customizer
		 */
		private AUI_Customizer $customizer;

		/**
		 * Admin interface instance.
		 *
		 * @var AUI_Admin
		 */
		private AUI_Admin $admin;

		/**
		 * Singleton instance.
		 *
		 * @var AyeCode_UI_Settings|null
		 */
		private static ?AyeCode_UI_Settings $instance = null;

		/**
		 * Get singleton instance.
		 *
		 * @return AyeCode_UI_Settings
		 */
		public static function instance(): AyeCode_UI_Settings {
			if ( null === self::$instance ) {
				self::$instance = new self();

				add_action( 'init', [ self::$instance, 'init' ] );

				if ( is_admin() ) {
					add_action( 'admin_init', [ self::$instance, 'register_settings' ] );
					add_action( 'template_redirect', [ self::$instance, 'maybe_show_examples' ] );
				}

				do_action( 'ayecode_ui_settings_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {
			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}
			spl_autoload_register( [ $this, 'autoload' ] );

			// Load specialized classes
			require_once dirname( __FILE__ ) . '/class-aui-settings.php';
			require_once dirname( __FILE__ ) . '/class-aui-css-generator.php';
			require_once dirname( __FILE__ ) . '/class-aui-asset-manager.php';
			require_once dirname( __FILE__ ) . '/class-aui-customizer.php';
			require_once dirname( __FILE__ ) . '/class-aui-admin.php';

			// Initialize instances
			$this->settings = AUI_Settings::instance();
			$this->asset_manager = AUI_Asset_Manager::instance();
			$this->css_generator = AUI_CSS_Generator::instance();
			$this->customizer = AUI_Customizer::instance();
			$this->admin = AUI_Admin::instance();
		}

		/**
		 * Autoload component classes on the fly.
		 *
		 * @param string $classname Class name to load.
		 * @return void
		 */
		private function autoload( string $classname ): void {
			$class = str_replace( '_', '-', strtolower( $classname ) );
			$file_path = trailingslashit( dirname( __FILE__ ) ) . 'components/class-' . $class . '.php';

			if ( $file_path && is_readable( $file_path ) ) {
				include_once $file_path;
			}
		}

		/**
		 * Initialize the settings and hooks.
		 *
		 * @return void
		 */
		public function init(): void {
			global $aui_bs5, $aui_ver;

			// Handle fix-admin request
			if ( ! empty( $_REQUEST['aui-fix-admin'] ) && ! empty( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], 'aui-fix-admin' ) ) {
				$db_settings = get_option( 'ayecode-ui-settings', [] );
				if ( ! empty( $db_settings ) ) {
					$db_settings['css_backend'] = 'compatibility';
					$db_settings['js_backend'] = 'core-popper';
					update_option( 'ayecode-ui-settings', $db_settings );
					wp_safe_redirect( admin_url( 'options-general.php?page=ayecode-ui-settings&updated=true' ) );
					exit;
				}
			}

			$this->constants();

			// Define global variables for backward compatibility
			$aui_bs5 = true; // Always true in v2.0+
			$aui_ver = '5.3.6'; // Always BS 5.3+

			// Add BS5 body classes
			add_filter( 'admin_body_class', [ $this, 'add_bs5_admin_body_class' ], 99, 1 );
			add_filter( 'body_class', [ $this, 'add_bs5_body_class' ] );

			// Load conversion utilities
			include_once dirname( __FILE__ ) . '/inc/bs-conversion.php';

			// Setup asset loading based on mode
			$load_mode = $this->settings->get_load_mode();
			$settings = $this->settings->get_settings();

			if ( $load_mode === 'always' || $load_mode === 'manual' ) {
				// Enqueue immediately or wait for manual call
				if ( $settings['css'] ) {
					add_action( 'wp_enqueue_scripts', [ $this->asset_manager, 'enqueue_style' ], 10 );
				}

				if ( $settings['js'] ) {
					add_action( 'wp_enqueue_scripts', [ $this->asset_manager, 'enqueue_scripts' ], 10 );
				}

			}
			// 'auto' mode is handled by Asset Manager's lazy loading

            // backend loading is not lazy.
            if ( $settings['css_backend'] ) {
                add_action( 'admin_enqueue_scripts', [ $this->asset_manager, 'enqueue_style' ], 1 );
            }

            if ( $settings['js_backend'] ) {
                add_action( 'admin_enqueue_scripts', [ $this->asset_manager, 'enqueue_scripts' ], 1 );
            }

			// HTML font size
			if ( $settings['html_font_size'] ) {
				add_action( 'wp_footer', [ $this, 'html_font_size' ], 10 );
			}
		}

		/**
		 * Define color constants.
		 *
		 * @return void
		 */
		public function constants(): void {
			define( 'AUI_PRIMARY_COLOR_ORIGINAL', '#1e73be' );
			define( 'AUI_SECONDARY_COLOR_ORIGINAL', '#6c757d' );
			define( 'AUI_INFO_COLOR_ORIGINAL', '#17a2b8' );
			define( 'AUI_WARNING_COLOR_ORIGINAL', '#ffc107' );
			define( 'AUI_DANGER_COLOR_ORIGINAL', '#dc3545' );
			define( 'AUI_SUCCESS_COLOR_ORIGINAL', '#44c553' );
			define( 'AUI_LIGHT_COLOR_ORIGINAL', '#f8f9fa' );
			define( 'AUI_DARK_COLOR_ORIGINAL', '#343a40' );
			define( 'AUI_WHITE_COLOR_ORIGINAL', '#fff' );
			define( 'AUI_PURPLE_COLOR_ORIGINAL', '#ad6edd' );
			define( 'AUI_SALMON_COLOR_ORIGINAL', '#ff977a' );
			define( 'AUI_CYAN_COLOR_ORIGINAL', '#35bdff' );
			define( 'AUI_GRAY_COLOR_ORIGINAL', '#ced4da' );
			define( 'AUI_INDIGO_COLOR_ORIGINAL', '#502c6c' );
			define( 'AUI_ORANGE_COLOR_ORIGINAL', '#orange' );
			define( 'AUI_BLACK_COLOR_ORIGINAL', '#000' );

			if ( ! defined( 'AUI_PRIMARY_COLOR' ) ) {
				define( 'AUI_PRIMARY_COLOR', AUI_PRIMARY_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_SECONDARY_COLOR' ) ) {
				define( 'AUI_SECONDARY_COLOR', AUI_SECONDARY_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_INFO_COLOR' ) ) {
				define( 'AUI_INFO_COLOR', AUI_INFO_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_WARNING_COLOR' ) ) {
				define( 'AUI_WARNING_COLOR', AUI_WARNING_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_DANGER_COLOR' ) ) {
				define( 'AUI_DANGER_COLOR', AUI_DANGER_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_SUCCESS_COLOR' ) ) {
				define( 'AUI_SUCCESS_COLOR', AUI_SUCCESS_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_LIGHT_COLOR' ) ) {
				define( 'AUI_LIGHT_COLOR', AUI_LIGHT_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_DARK_COLOR' ) ) {
				define( 'AUI_DARK_COLOR', AUI_DARK_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_WHITE_COLOR' ) ) {
				define( 'AUI_WHITE_COLOR', AUI_WHITE_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_PURPLE_COLOR' ) ) {
				define( 'AUI_PURPLE_COLOR', AUI_PURPLE_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_SALMON_COLOR' ) ) {
				define( 'AUI_SALMON_COLOR', AUI_SALMON_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_CYAN_COLOR' ) ) {
				define( 'AUI_CYAN_COLOR', AUI_CYAN_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_GRAY_COLOR' ) ) {
				define( 'AUI_GRAY_COLOR', AUI_GRAY_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_INDIGO_COLOR' ) ) {
				define( 'AUI_INDIGO_COLOR', AUI_INDIGO_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_ORANGE_COLOR' ) ) {
				define( 'AUI_ORANGE_COLOR', AUI_ORANGE_COLOR_ORIGINAL );
			}
			if ( ! defined( 'AUI_BLACK_COLOR' ) ) {
				define( 'AUI_BLACK_COLOR', AUI_BLACK_COLOR_ORIGINAL );
			}
		}

		/**
		 * Get all color constants as array.
		 *
		 * @param bool $original Whether to return original values.
		 * @return array Color array.
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
		 * Add BS5 admin body class.
		 *
		 * @param string $classes Existing classes.
		 * @return string Modified classes.
		 */
		public function add_bs5_admin_body_class( string $classes = '' ): string {
			$classes .= ' aui_bs5';
			return $classes;
		}

		/**
		 * Add BS5 body class.
		 *
		 * @param array $classes Existing classes.
		 * @return array Modified classes.
		 */
		public function add_bs5_body_class( array $classes ): array {
			$classes[] = 'aui_bs5';
			return $classes;
		}

		/**
		 * Register settings with WordPress.
		 *
		 * @return void
		 */
		public function register_settings(): void {
			$this->settings->register_settings();
		}

		/**
		 * Add HTML font size to footer.
		 *
		 * @return void
		 */
		public function html_font_size(): void {
			$settings = $this->settings->get_settings();
			echo '<style>html{font-size:' . absint( $settings['html_font_size'] ) . 'px;}</style>';
		}

		/**
		 * Maybe show developer examples.
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
			echo '<h1>AyeCode UI v2.0 - Bootstrap 5.3+ Examples</h1>';
			echo '<p>This is a placeholder for component examples. Add your example components here.</p>';
			wp_footer();
			echo '</body></html>';
			exit;
		}

		// ============================================
		// BUILDER & PREVIEW DETECTION UTILITIES
		// ============================================

		/**
		 * Check if the current theme is a block theme.
		 *
		 * @return bool
		 */
		public static function is_block_theme(): bool {
			if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
				return true;
			}
			return false;
		}

		/**
		 * Check if block editor page.
		 *
		 * @return bool
		 */
		public static function is_block_editor(): bool {
			if ( is_admin() ) {
				$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

				if ( ! empty( $current_screen ) && method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Check if current call is AJAX call to get block content.
		 *
		 * @return bool
		 */
		public static function is_block_content_call(): bool {
			$result = false;
			if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'super_duper_output_shortcode' ) {
				$result = true;
			}
			return $result;
		}

		/**
		 * Test if current output is inside Divi preview.
		 *
		 * @return bool
		 */
		public static function is_divi_preview(): bool {
			$result = false;
			if ( isset( $_REQUEST['et_fb'] ) || isset( $_REQUEST['et_pb_preview'] ) || ( is_admin() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'elementor' ) ) {
				$result = true;
			}
			return $result;
		}

		/**
		 * Test if current output is inside Elementor preview.
		 *
		 * @return bool
		 */
		public static function is_elementor_preview(): bool {
			$result = false;
			if ( isset( $_REQUEST['elementor-preview'] ) || ( is_admin() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'elementor' ) || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'elementor_ajax' ) ) {
				$result = true;
			}
			return $result;
		}

		/**
		 * Test if current output is inside Beaver Builder preview.
		 *
		 * @return bool
		 */
		public static function is_beaver_preview(): bool {
			$result = false;
			if ( isset( $_REQUEST['fl_builder'] ) ) {
				$result = true;
			}
			return $result;
		}

		/**
		 * Test if current output is inside SiteOrigin builder preview.
		 *
		 * @return bool
		 */
		public static function is_siteorigin_preview(): bool {
			$result = false;
			if ( ! empty( $_REQUEST['siteorigin_panels_live_editor'] ) ) {
				$result = true;
			}
			return $result;
		}

		/**
		 * Test if current output is inside Cornerstone builder preview.
		 *
		 * @return bool
		 */
		public static function is_cornerstone_preview(): bool {
			$result = false;
			if ( ! empty( $_REQUEST['cornerstone_preview'] ) || ( ! empty( $_SERVER['REQUEST_URI'] ) && basename( $_SERVER['REQUEST_URI'] ) === 'cornerstone-endpoint' ) ) {
				$result = true;
			}
			return $result;
		}

		/**
		 * Test if current output is inside Fusion builder preview.
		 *
		 * @return bool
		 */
		public static function is_fusion_preview(): bool {
			$result = false;
			if ( ! empty( $_REQUEST['fb-edit'] ) || ! empty( $_REQUEST['fusion_load_nonce'] ) ) {
				$result = true;
			}
			return $result;
		}

		/**
		 * Test if current output is inside Oxygen builder preview.
		 *
		 * @return bool
		 */
		public static function is_oxygen_preview(): bool {
			$result = false;
			if ( ! empty( $_REQUEST['ct_builder'] ) || ( ! empty( $_REQUEST['action'] ) && ( substr( $_REQUEST['action'], 0, 11 ) === 'oxy_render_' || substr( $_REQUEST['action'], 0, 10 ) === 'ct_render_' ) ) ) {
				$result = true;
			}
			return $result;
		}

		/**
		 * Check for Kallyas theme Zion builder preview.
		 *
		 * @return bool
		 */
		public static function is_kallyas_zion_preview(): bool {
			$result = false;
			if ( function_exists( 'znhg_kallyas_theme_config' ) && ! empty( $_REQUEST['zn_pb_edit'] ) ) {
				$result = true;
			}
			return $result;
		}

		/**
		 * Check for Bricks theme builder preview.
		 *
		 * @return bool
		 */
		public static function is_bricks_preview(): bool {
			$result = false;
			if ( function_exists( 'bricks_is_builder' ) && ( bricks_is_builder() || bricks_is_builder_call() ) ) {
				$result = true;
			}
			return $result;
		}

		/**
		 * General function to check if we are in a preview situation.
		 *
		 * @return bool
		 */
		public static function is_preview(): bool {
			if ( self::is_block_editor() ) {
				return true;
			}

			if ( self::is_block_content_call() ) {
				return true;
			} elseif ( self::is_divi_preview() ) {
				return true;
			} elseif ( self::is_elementor_preview() ) {
				return true;
			} elseif ( self::is_beaver_preview() ) {
				return true;
			} elseif ( self::is_siteorigin_preview() ) {
				return true;
			} elseif ( self::is_cornerstone_preview() ) {
				return true;
			} elseif ( self::is_fusion_preview() ) {
				return true;
			} elseif ( self::is_oxygen_preview() ) {
				return true;
			} elseif ( self::is_kallyas_zion_preview() ) {
				return true;
			} elseif ( self::is_bricks_preview() ) {
				return true;
			}

			return false;
		}

		/**
		 * Public method to enqueue select2/choices.js.
		 * Delegates to Asset Manager.
		 *
		 * @return void
		 */
		public function enqueue_select2(): void {
			$this->asset_manager->enqueue_select2();
		}

		/**
		 * Public method to enqueue flatpickr.
		 * Delegates to Asset Manager.
		 *
		 * @return void
		 */
		public function enqueue_flatpickr(): void {
			$this->asset_manager->enqueue_flatpickr();
		}

		/**
		 * Public method to enqueue icon picker.
		 * Delegates to Asset Manager.
		 *
		 * @return void
		 */
		public function enqueue_iconpicker(): void {
			$this->asset_manager->enqueue_iconpicker();
		}
	}

	// Initialize
	AyeCode_UI_Settings::instance();
}
