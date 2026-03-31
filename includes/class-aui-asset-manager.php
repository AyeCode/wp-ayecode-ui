<?php
/**
 * AyeCode UI Asset Manager
 *
 * Handles enqueuing of CSS/JS assets, lazy loading detection, and localization.
 *
 * @since 2.0.0
 * @package AyeCode_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AUI_Asset_Manager class.
 *
 * Manages all asset loading and lazy loading detection.
 */
class AUI_Asset_Manager {

	/**
	 * Singleton instance.
	 *
	 * @var AUI_Asset_Manager|null
	 */
	private static ?AUI_Asset_Manager $instance = null;

	/**
	 * Whether assets have been loaded.
	 *
	 * @var bool
	 */
	private bool $assets_loaded = false;

	/**
	 * Whether AyeCode blocks have been detected.
	 *
	 * @var bool
	 */
	private bool $blocks_detected = false;

	/**
	 * Asset URL base.
	 *
	 * @var string
	 */
	private string $url = '';

	/**
	 * Settings instance.
	 *
	 * @var AUI_Settings
	 */
	private AUI_Settings $settings;

	/**
	 * Package version.
	 *
	 * @var string
	 */
	private string $version = '2.0.0';

	/**
	 * Choices.js version.
	 *
	 * @var string
	 */
	private string $choices_version = '11.1.0';

	/**
	 * Get singleton instance.
	 *
	 * @return AUI_Asset_Manager
	 */
	public static function instance(): AUI_Asset_Manager {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->settings = AUI_Settings::instance();
		$this->url = $this->get_url();

		// Always register assets early
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ], 1 );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ], 1 );

		// Set up lazy loading hooks
		$this->setup_lazy_loading();
	}

	/**
	 * Setup lazy loading detection.
	 *
	 * @return void
	 */
	private function setup_lazy_loading(): void {
		$load_mode = $this->settings->get_load_mode();

		if ( $load_mode === 'always' ) {
			// Load immediately
			$this->assets_loaded = true;
		} elseif ( $load_mode === 'auto' ) {
			// Detect blocks during rendering
			add_filter( 'render_block', [ $this, 'detect_ayecode_blocks' ], 10, 2 );
//            add_action( 'wp_footer', [ $this, 'enqueue_if_detected' ], 1 );
            add_action( 'wp_head', [ $this, 'enqueue_if_detected' ], 7 ); // @todo not sure why 7 puts in in head and 8>8 puts it in footer.
		}
		// 'manual' mode requires explicit call to load_assets()
	}

	/**
	 * Register all assets early so they can be enqueued conditionally.
	 *
	 * @return void
	 */
	public function register_assets(): void {
		// Choices.js (select2 replacement)
		wp_register_style( 'choices', $this->url . 'assets/css/choices.css', [], $this->choices_version );
		wp_register_script( 'choices', $this->url . 'assets/js/choices.min.js', [], $this->choices_version, true );

		// Flatpickr
		wp_register_style( 'flatpickr', $this->url . 'assets/css/flatpickr.min.css', [], $this->version );
		wp_register_script( 'flatpickr', $this->url . 'assets/js/flatpickr.min.js', [], $this->version, true );

		// Icon picker (React version for block editor)
		wp_register_script( 'iconpicker-react', $this->url . 'assets/libs/universal-icon-picker/js/universal-icon-picker-react.js', [], $this->version, true );
        wp_add_inline_script( 'iconpicker-react', $this->inline_script_iconpicker(), 'before' );

		// Icon picker
		wp_register_script( 'iconpicker', $this->url . 'assets/libs/universal-icon-picker/js/universal-icon-picker.js', [], $this->version, true );
		wp_add_inline_script( 'iconpicker', $this->inline_script_iconpicker(), 'before' );

		// Dynamic data picker
		wp_register_script( 'sd-dynamic-data-button', $this->url . 'assets/libs/universal-icon-picker/js/dynamic-data-picker-react.js', [], $this->version, true );

		// Bootstrap file browser
		wp_register_script( 'aui-custom-file-input', $this->url . 'assets/js/bs-custom-file-input.min.js', [ 'jquery' ], $this->version, true );
		wp_add_inline_script( 'aui-custom-file-input', $this->inline_script_file_browser() );

		// Main AUI stylesheets
		$settings = $this->settings->get_settings();
		$css_setting = current_action() === 'wp_enqueue_scripts' ? 'css' : 'css_backend';
		$compatibility = $settings[ $css_setting ] === 'core' ? false : true;

		$url = $settings[ $css_setting ] === 'core'
			? $this->url . 'assets/css/ayecode-ui.css'
			: $this->url . 'assets/css/ayecode-ui-compatibility.css';

		wp_register_style( 'ayecode-ui', $url, [], $this->version );

		// FSE editor styles
		wp_register_style( 'ayecode-ui-fse', $this->url . 'assets/css/ayecode-ui-fse.css', [], $this->version );

		// Bootstrap JS
		$dependency = $this->force_load_select2() ? [ 'choices' ] : [];

		wp_register_script( 'bootstrap-js-bundle', $this->url . 'assets/js/bootstrap.bundle.min.js', $dependency, $this->version, true );
		wp_register_script( 'bootstrap-js-popper', $this->url . 'assets/js/popper.min.js', $dependency, $this->version, true );
		wp_register_script( 'bootstrap-dummy', '', $dependency );
	}

	/**
	 * Detect AyeCode blocks during render.
	 *
	 * @param string $block_content Block HTML content.
	 * @param array  $block Block data.
	 * @return string Unchanged block content.
	 */
	public function detect_ayecode_blocks( string $block_content, array $block ): string {
		if ( $this->blocks_detected ) {
			return $block_content;
		}

//        print_r($block);
		$ayecode_blocks = [ 'blockstrap','geodirectory', 'userswp', 'invoicing', 'getpaid' ];

		if ( isset( $block['blockName'] ) ) {
			$parts = explode( '/', $block['blockName'] );
			if ( ! empty( $parts[0] ) && in_array( $parts[0], $ayecode_blocks, true ) ) {
				$this->blocks_detected = true;
			}
		}

		return $block_content;
	}

	/**
	 * Enqueue assets if blocks were detected.
	 *
	 * @return void
	 */
	public function enqueue_if_detected(): void {

		if ( $this->blocks_detected && ! $this->assets_loaded ) {
			$this->enqueue_style();
			$this->enqueue_scripts();
			$this->assets_loaded = true;
		}
	}

	/**
	 * Manually load assets (for 'manual' mode or external calls).
	 *
	 * @return void
	 */
	public function load_assets(): void {
		if ( ! $this->assets_loaded ) {
			$this->enqueue_style();
			$this->enqueue_scripts();
			$this->assets_loaded = true;
		}
	}

	/**
	 * Get the URL path to the assets folder.
	 *
	 * @return string Asset URL with trailing slash.
	 */
	private function get_url(): string {
		$content_dir = wp_normalize_path( untrailingslashit( WP_CONTENT_DIR ) );
		$content_url = untrailingslashit( WP_CONTENT_URL );

		// Maybe replace http:// to https://
		if ( strpos( $content_url, 'http://' ) === 0 && strpos( plugins_url(), 'https://' ) === 0 ) {
			$content_url = str_replace( 'http://', 'https://', $content_url );
		}

		$content_basename = basename( $content_dir );
		$file_dir = str_replace( '/includes', '', wp_normalize_path( dirname( __FILE__ ) ) );

		// Find the relative path by matching from content directory name
		$after_content = substr( $file_dir, strpos( $file_dir, '/' . $content_basename . '/' ) + strlen( '/' . $content_basename . '/' ) );

		// Build URL using WP_CONTENT_URL and the relative path
		$url = trailingslashit( $content_url ) . $after_content;

		// Some hosts end up with /wp-content/wp-content/
		$url = str_replace( '/wp-content/wp-content/', '/wp-content/', $url );

		return trailingslashit( $url );
	}

	/**
	 * Enqueue CSS stylesheets.
	 *
	 * @return void
	 */
	public function enqueue_style(): void {
		if ( is_admin() && ! $this->is_aui_screen() ) {
			return;
		}

		$settings = $this->settings->get_settings();
		$css_setting = current_action() === 'wp_enqueue_scripts' ? 'css' : 'css_backend';
		$load_fse = false;

		if ( ! $settings[ $css_setting ] ) {
			return;
		}

		// Choices.js (select2 replacement)
		if ( $this->force_load_select2() ) {
			wp_enqueue_style( 'choices' );
		}

		// Main AUI stylesheet
		$compatibility = $settings[ $css_setting ] === 'core' ? false : true;
		wp_enqueue_style( 'ayecode-ui' );

		// FSE editor styles
		if ( is_admin() && ( ! empty( $_REQUEST['postType'] ) || AyeCode_UI_Settings::is_block_editor() ) && ( defined( 'BLOCKSTRAP_VERSION' ) || defined( 'AUI_FSE' ) ) ) {
			wp_enqueue_style( 'ayecode-ui-fse' );
			$load_fse = true;
		}

		// Admin-specific fixes
		if ( is_admin() ) {
			$custom_css = "
			body:not(.editor-styles-wrapper){
				background-color: #f1f1f1;
				font-family: -apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,Oxygen-Sans,Ubuntu,Cantarell,\"Helvetica Neue\",sans-serif;
				font-size:13px;
			}
			a {
				color: #0073aa;
				text-decoration: underline;
			}
			label {
				display: initial;
				margin-bottom: 0;
			}
			input, select {
				margin: 1px;
				line-height: initial;
			}
			th, td, div, h2 {
				box-sizing: content-box;
			}
			h1, h2, h3, h4, h5, h6 {
				display: block;
				font-weight: 600;
			}
			h2,h3 {
				font-size: 1.3em;
				margin: 1em 0
			}
			.blocks-widgets-container .bsui *{
				box-sizing: border-box;
			}
			.bs-tooltip-top .arrow{
				margin-left:0px;
			}
			.custom-switch input[type=checkbox]{
				display:none;
			}
			.edit-post-sidebar input[type=color].components-text-control__input{
				padding: 0;
			}
			";
			wp_add_inline_style( 'ayecode-ui', $custom_css );
		}

		// Custom CSS for color overrides
		if ( $load_fse ) {
			wp_add_inline_style( 'ayecode-ui-fse', AUI_CSS_Generator::custom_css( $compatibility, true ) );
		} else {
			wp_add_inline_style( 'ayecode-ui', AUI_CSS_Generator::custom_css( $compatibility ) );
		}
	}

	/**
	 * Enqueue JavaScript files.
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		if ( is_admin() && ! $this->is_aui_screen() ) {
			return;
		}

		$settings = $this->settings->get_settings();
		$js_setting = current_action() === 'wp_enqueue_scripts' ? 'js' : 'js_backend';
		$load_inline = false;

		if ( $settings[ $js_setting ] === 'core-popper' ) {
			// Bootstrap bundle - If in admin then add to footer for compatibility
			is_admin() ? wp_enqueue_script( 'bootstrap-js-bundle', '', null, null, true ) : wp_enqueue_script( 'bootstrap-js-bundle' );

			$script = $this->inline_script();
			wp_add_inline_script( 'bootstrap-js-bundle', $script );
		} elseif ( $settings[ $js_setting ] === 'popper' ) {
			wp_enqueue_script( 'bootstrap-js-popper' );
			$load_inline = true;
		} else {
			$load_inline = true;
		}

		// Load inline scripts via dummy script if main script not loaded
		if ( $load_inline ) {
			wp_enqueue_script( 'bootstrap-dummy' );

			$script = $this->inline_script();
			wp_add_inline_script( 'bootstrap-dummy', $script );
		}
	}

	/**
	 * Generate inline JavaScript for Bootstrap functionality.
	 *
	 * @return string Minified JavaScript.
	 */
	private function inline_script(): string {
		// Flatpickr calendar locale
		$flatpickr_locale = self::flatpickr_locale();

		ob_start();
		include dirname( __FILE__ ) . '/inc/bs5-js.php';
		$output = ob_get_clean();

		// Strip <script> tags and minify
		return str_replace( [ '<script>', '</script>' ], '', AUI_CSS_Generator::minify_js( $output ) );
	}

	/**
	 * Generate inline JavaScript for Bootstrap file input.
	 *
	 * @return string JavaScript code.
	 */
	private function inline_script_file_browser(): string {
		ob_start();
		?>
		<script>
		// run on doc ready
		document.addEventListener('DOMContentLoaded', function() {
			bsCustomFileInput.init();
		});
		</script>
		<?php
		$output = ob_get_clean();

		return str_replace( [ '<script>', '</script>' ], '', $output );
	}

	/**
	 * Generate inline JavaScript configuration for icon picker.
	 *
	 * @return string JavaScript code.
	 */
	private function inline_script_iconpicker(): string {
		// Default icon libraries - full URLs, these are added by the wp-font-awesome-settings package
		$icon_libraries = apply_filters( 'aui_iconpicker_libraries', [] );

		$uploads = wp_upload_dir();

		// Allow filtering of the custom icons settings URL
		$custom_icons_url = apply_filters(
			'aui_iconpicker_custom_icons_settings_url',
			admin_url( 'options-general.php?page=wp-font-awesome-settings#section=custom_icons' )
		);

		ob_start();
		?>
		window.ayecodeFASettings = {
			libraries: <?php echo json_encode( $icon_libraries ); ?>,
			uploadsUrl: '<?php echo esc_js( $uploads['baseurl'] ); ?>',
			customIconsSettingsUrl: '<?php echo esc_url( $custom_icons_url ); ?>'
		};
		<?php
		return ob_get_clean();
	}

	/**
	 * Check if select2/choices should be force loaded.
	 *
	 * @return bool
	 */
	private function force_load_select2(): bool {
		global $aui_select2_enqueued;

		$conditional_select2 = apply_filters( 'aui_is_conditional_select2', true );

		if ( $conditional_select2 !== true ) {
			return true;
		}

		$load = is_admin() && ! $aui_select2_enqueued;

		return apply_filters( 'aui_force_load_select2', $load );
	}

	/**
	 * Check if current admin screen should load AUI scripts.
	 *
	 * @return bool
	 */
	private function is_aui_screen(): bool {
		if ( ! is_admin() ) {
			return false;
		}

		// Check disable_admin setting
		if ( ! $this->settings->should_load_admin_scripts() ) {
			return false;
		}

		// Only enable on set pages
		$aui_screens = [
			'page',
			'post',
			'settings_page_ayecode-ui-settings',
			'appearance_page_gutenberg-widgets',
			'widgets',
			'ayecode-ui-settings',
			'site-editor',
		];

		$screen_ids = apply_filters( 'aui_screen_ids', $aui_screens );
		$screen = get_current_screen();

		// Check if we are on an AUI screen
		if ( $screen && in_array( $screen->id, $screen_ids, true ) ) {
			return true;
		}

		// Load for widget previews in WP 5.8+
		if ( ! empty( $_REQUEST['legacy-widget-preview'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Public method to enqueue select2/choices.
	 *
	 * @return void
	 */
	public function enqueue_select2(): void {
		wp_enqueue_style( 'choices' );
		wp_enqueue_script( 'choices' );
	}

	/**
	 * Public method to enqueue flatpickr.
	 *
	 * @return void
	 */
	public function enqueue_flatpickr(): void {
		wp_enqueue_style( 'flatpickr' );
		wp_enqueue_script( 'flatpickr' );
	}

	/**
	 * Public method to enqueue icon picker.
	 *
	 * @return void
	 */
	public function enqueue_iconpicker(): void {
		wp_enqueue_script( 'iconpicker' );
	}

	/**
	 * Generate flatpickr locale object.
	 *
	 * @return string Flatpickr locale JavaScript object.
	 */
	public static function flatpickr_locale(): string {
		$params = self::calendar_params();

		if ( is_string( $params ) ) {
			$params = html_entity_decode( $params, ENT_QUOTES, 'UTF-8' );
		} else {
			foreach ( (array) $params as $key => $value ) {
				if ( ! is_scalar( $value ) ) {
					continue;
				}
				$params[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
			}
		}

		$day_s3 = [];
		$day_s5 = [];

		for ( $i = 1; $i <= 7; $i++ ) {
			$day_s3[] = addslashes( $params[ 'day_s3_' . $i ] );
			$day_s5[] = addslashes( $params[ 'day_s3_' . $i ] );
		}

		$month_s = [];
		$month_long = [];

		for ( $i = 1; $i <= 12; $i++ ) {
			$month_s[] = addslashes( $params[ 'month_s_' . $i ] );
			$month_long[] = addslashes( $params[ 'month_long_' . $i ] );
		}

		ob_start();
		if ( 0 ) { ?><script><?php } ?>
		{
			weekdays: {
				shorthand: ['<?php echo implode( "','", $day_s3 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'],
				longhand: ['<?php echo implode( "','", $day_s5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'],
			},
			months: {
				shorthand: ['<?php echo implode( "','", $month_s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'],
				longhand: ['<?php echo implode( "','", $month_long ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'],
			},
			daysInMonth: [31,28,31,30,31,30,31,31,30,31,30,31],
			firstDayOfWeek: <?php echo (int) $params['firstDayOfWeek']; ?>,
			ordinal: function (nth) {
				var s = nth % 100;
				if (s > 3 && s < 21) return "th";
				switch (s % 10) {
					case 1: return "st";
					case 2: return "nd";
					case 3: return "rd";
					default: return "th";
				}
			},
			rangeSeparator: '<?php echo esc_attr( $params['rangeSeparator'] ); ?>',
			weekAbbreviation: '<?php echo esc_attr( $params['weekAbbreviation'] ); ?>',
			scrollTitle: '<?php echo esc_attr( $params['scrollTitle'] ); ?>',
			toggleTitle: '<?php echo esc_attr( $params['toggleTitle'] ); ?>',
			amPM: ['<?php echo esc_attr( $params['am_upper'] ); ?>','<?php echo esc_attr( $params['pm_upper'] ); ?>'],
			yearAriaLabel: '<?php echo esc_attr( $params['year'] ); ?>',
			hourAriaLabel: '<?php echo esc_attr( $params['hour'] ); ?>',
			minuteAriaLabel: '<?php echo esc_attr( $params['minute'] ); ?>',
			time_24hr: <?php echo ( $params['time_24hr'] ? 'true' : 'false' ); ?>
		}
		<?php if ( 0 ) { ?></script><?php } ?>
		<?php
		$locale = ob_get_clean();

		return apply_filters( 'ayecode_ui_flatpickr_locale', trim( $locale ) );
	}

	/**
	 * Get calendar parameters for localization.
	 *
	 * @return array Calendar parameters.
	 */
	public static function calendar_params(): array {
		global $wp_locale;

		$day_of_week = get_option( 'start_of_week', 0 );

		$params = [
			'day_s3_1'          => $wp_locale->weekday_abbrev[ $wp_locale->weekday[0] ],
			'day_s3_2'          => $wp_locale->weekday_abbrev[ $wp_locale->weekday[1] ],
			'day_s3_3'          => $wp_locale->weekday_abbrev[ $wp_locale->weekday[2] ],
			'day_s3_4'          => $wp_locale->weekday_abbrev[ $wp_locale->weekday[3] ],
			'day_s3_5'          => $wp_locale->weekday_abbrev[ $wp_locale->weekday[4] ],
			'day_s3_6'          => $wp_locale->weekday_abbrev[ $wp_locale->weekday[5] ],
			'day_s3_7'          => $wp_locale->weekday_abbrev[ $wp_locale->weekday[6] ],
			'month_s_1'         => $wp_locale->month_abbrev[ $wp_locale->month['01'] ],
			'month_s_2'         => $wp_locale->month_abbrev[ $wp_locale->month['02'] ],
			'month_s_3'         => $wp_locale->month_abbrev[ $wp_locale->month['03'] ],
			'month_s_4'         => $wp_locale->month_abbrev[ $wp_locale->month['04'] ],
			'month_s_5'         => $wp_locale->month_abbrev[ $wp_locale->month['05'] ],
			'month_s_6'         => $wp_locale->month_abbrev[ $wp_locale->month['06'] ],
			'month_s_7'         => $wp_locale->month_abbrev[ $wp_locale->month['07'] ],
			'month_s_8'         => $wp_locale->month_abbrev[ $wp_locale->month['08'] ],
			'month_s_9'         => $wp_locale->month_abbrev[ $wp_locale->month['09'] ],
			'month_s_10'        => $wp_locale->month_abbrev[ $wp_locale->month['10'] ],
			'month_s_11'        => $wp_locale->month_abbrev[ $wp_locale->month['11'] ],
			'month_s_12'        => $wp_locale->month_abbrev[ $wp_locale->month['12'] ],
			'month_long_1'      => $wp_locale->month[ '01' ],
			'month_long_2'      => $wp_locale->month[ '02' ],
			'month_long_3'      => $wp_locale->month[ '03' ],
			'month_long_4'      => $wp_locale->month[ '04' ],
			'month_long_5'      => $wp_locale->month[ '05' ],
			'month_long_6'      => $wp_locale->month[ '06' ],
			'month_long_7'      => $wp_locale->month[ '07' ],
			'month_long_8'      => $wp_locale->month[ '08' ],
			'month_long_9'      => $wp_locale->month[ '09' ],
			'month_long_10'     => $wp_locale->month[ '10' ],
			'month_long_11'     => $wp_locale->month[ '11' ],
			'month_long_12'     => $wp_locale->month[ '12' ],
			'firstDayOfWeek'    => $day_of_week,
			'rangeSeparator'    => __( ' to ', 'ayecode-connect' ),
			'weekAbbreviation'  => __( 'Wk', 'ayecode-connect' ),
			'scrollTitle'       => __( 'Scroll to increment', 'ayecode-connect' ),
			'toggleTitle'       => __( 'Click to toggle', 'ayecode-connect' ),
			'am_upper'          => $wp_locale->meridiem['AM'],
			'pm_upper'          => $wp_locale->meridiem['PM'],
			'year'              => __( 'Year', 'ayecode-connect' ),
			'hour'              => __( 'Hour', 'ayecode-connect' ),
			'minute'            => __( 'Minute', 'ayecode-connect' ),
			'time_24hr'         => strpos( get_option( 'time_format' ), 'H' ) !== false,
		];

		return apply_filters( 'ayecode_ui_calendar_params', $params );
	}

	/**
	 * Get select2/choices.js parameters for localization.
	 *
	 * @return array Select2 parameters.
	 */
	public static function select2_params(): array {
		$params = [
			'i18n_select_state_text'   => esc_attr__( 'Select an option&hellip;', 'ayecode-connect' ),
			'i18n_no_matches'          => _x( 'No matches found', 'enhanced select', 'ayecode-connect' ),
			'i18n_ajax_error'          => _x( 'Loading failed', 'enhanced select', 'ayecode-connect' ),
			'i18n_input_too_short_1'   => _x( 'Please enter 1 or more characters', 'enhanced select', 'ayecode-connect' ),
			'i18n_input_too_short_n'   => _x( 'Please enter %qty% or more characters', 'enhanced select', 'ayecode-connect' ),
			'i18n_input_too_long_1'    => _x( 'Please delete 1 character', 'enhanced select', 'ayecode-connect' ),
			'i18n_input_too_long_n'    => _x( 'Please delete %qty% characters', 'enhanced select', 'ayecode-connect' ),
			'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'ayecode-connect' ),
			'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'ayecode-connect' ),
			'i18n_load_more'           => _x( 'Loading more results&hellip;', 'enhanced select', 'ayecode-connect' ),
			'i18n_searching'           => _x( 'Searching&hellip;', 'enhanced select', 'ayecode-connect' ),
		];

		return apply_filters( 'ayecode_ui_select2_params', $params );
	}

	/**
	 * Generate select2/choices.js locale object.
	 *
	 * @return string JSON encoded select2 locale.
	 */
	public static function select2_locale(): string {
		$params = self::select2_params();

		if ( is_string( $params ) ) {
			$params = html_entity_decode( $params, ENT_QUOTES, 'UTF-8' );
		} else {
			foreach ( (array) $params as $key => $value ) {
				if ( ! is_scalar( $value ) ) {
					continue;
				}
				$params[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
			}
		}

		return wp_json_encode( $params );
	}

	/**
	 * Generate timeago locale object.
	 *
	 * @return string JSON encoded timeago locale.
	 */
	public static function timeago_locale(): string {
		$params = [
			'prefix_ago'       => '',
			'suffix_ago'       => ' ' . _x( 'ago', 'time ago', 'ayecode-connect' ),
			'prefix_from_now'  => '',
			'suffix_from_now'  => ' ' . _x( 'from now', 'time from now', 'ayecode-connect' ),
			'seconds'          => _x( 'less than a minute', 'time ago', 'ayecode-connect' ),
			'minute'           => _x( 'about a minute', 'time ago', 'ayecode-connect' ),
			'minutes'          => _x( '%d minutes', 'time ago', 'ayecode-connect' ),
			'hour'             => _x( 'about an hour', 'time ago', 'ayecode-connect' ),
			'hours'            => _x( 'about %d hours', 'time ago', 'ayecode-connect' ),
			'day'              => _x( 'a day', 'time ago', 'ayecode-connect' ),
			'days'             => _x( '%d days', 'time ago', 'ayecode-connect' ),
			'month'            => _x( 'about a month', 'time ago', 'ayecode-connect' ),
			'months'           => _x( '%d months', 'time ago', 'ayecode-connect' ),
			'year'             => _x( 'about a year', 'time ago', 'ayecode-connect' ),
			'years'            => _x( '%d years', 'time ago', 'ayecode-connect' ),
			'numbers'          => [],
		];

		if ( is_string( $params ) ) {
			$params = html_entity_decode( $params, ENT_QUOTES, 'UTF-8' );
		} else {
			foreach ( (array) $params as $key => $value ) {
				if ( ! is_scalar( $value ) ) {
					continue;
				}
				$params[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
			}
		}

		return wp_json_encode( $params );
	}
}
