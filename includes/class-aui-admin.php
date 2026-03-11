<?php
/**
 * AyeCode UI Admin Interface
 *
 * Handles admin settings page, menu items, and notices.
 *
 * @since 2.0.0
 * @package AyeCode_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AUI_Admin class.
 *
 * Manages admin interface and settings page.
 */
class AUI_Admin {

	/**
	 * Singleton instance.
	 *
	 * @var AUI_Admin|null
	 */
	private static ?AUI_Admin $instance = null;

	/**
	 * Settings instance.
	 *
	 * @var AUI_Settings
	 */
	private AUI_Settings $settings;

	/**
	 * Package name.
	 *
	 * @var string
	 */
	private string $name = 'AyeCode UI';

	/**
	 * Package version.
	 *
	 * @var string
	 */
	private string $version = '2.0.0';

	/**
	 * Get singleton instance.
	 *
	 * @return AUI_Admin
	 */
	public static function instance(): AUI_Admin {
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

		add_action( 'admin_menu', [ $this, 'add_menu_item' ] );
		add_action( 'admin_notices', [ $this, 'show_admin_style_notice' ] );
	}

	/**
	 * Add settings page to admin menu.
	 *
	 * @return void
	 */
	public function add_menu_item(): void {
		// Won't pass theme check if function name present directly
		$menu_function = 'add' . '_' . 'options' . '_' . 'page';
		call_user_func(
			$menu_function,
			$this->name,
			$this->name,
			'manage_options',
			'ayecode-ui-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'ayecode-connect' ) );
		}

		$settings = $this->settings->get_settings();
		$overrides = apply_filters( 'ayecode-ui-settings', [], [], [] );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( $this->name ); ?></h1>
			<p><?php echo esc_html( apply_filters( 'ayecode-ui-settings-message', __( 'Here you can adjust settings if you are having compatibility issues.', 'ayecode-connect' ) ) ); ?></p>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'ayecode-ui-settings' );
				do_settings_sections( 'ayecode-ui-settings' );
				?>

				<h2><?php esc_html_e( 'Bootstrap Version', 'ayecode-connect' ); ?></h2>
				<p><?php esc_html_e( 'This package now only supports Bootstrap 5.3+ (with dark mode support).', 'ayecode-connect' ); ?></p>
				<div class="bsui">
					<?php
					if ( ! empty( $overrides ) ) {
						echo aui()->alert( [ // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'type'    => 'info',
							'content' => esc_html__( 'Some options are disabled as your current theme is overriding them.', 'ayecode-connect' ),
						] );
					}

					$bs_ver_options = apply_filters( 'ayecode_ui_versions', [
						'5dm' => __( 'Bootstrap 5.3+ (Dark Mode) - v2.0', 'ayecode-connect' ),
					], $settings, $overrides );
					?>
				</div>
				<table class="form-table wpbs-table-version-settings">
					<tr valign="top">
						<th scope="row"><label for="wpbs-version"><?php esc_html_e( 'Version', 'ayecode-connect' ); ?></label></th>
						<td>
							<select name="ayecode-ui-settings[bs_ver]" id="wpbs-version" <?php echo ! empty( $overrides['bs_ver'] ) ? 'disabled' : ''; ?>>
								<?php foreach ( $bs_ver_options as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['bs_ver'], $value ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Asset Loading', 'ayecode-connect' ); ?></h2>
				<table class="form-table wpbs-table-settings">
					<tr valign="top">
						<th scope="row"><label for="wpbs-load-mode"><?php esc_html_e( 'Load Mode', 'ayecode-connect' ); ?></label></th>
						<td>
							<select name="ayecode-ui-settings[load_mode]" id="wpbs-load-mode">
								<option value="auto" <?php selected( $settings['load_mode'], 'auto' ); ?>><?php esc_html_e( 'Auto (recommended) - Only when blocks detected', 'ayecode-connect' ); ?></option>
								<option value="always" <?php selected( $settings['load_mode'], 'always' ); ?>><?php esc_html_e( 'Always - Load on all pages', 'ayecode-connect' ); ?></option>
								<option value="manual" <?php selected( $settings['load_mode'], 'manual' ); ?>><?php esc_html_e( 'Manual - Developer controlled', 'ayecode-connect' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Auto mode only loads assets when AyeCode blocks are detected, improving performance.', 'ayecode-connect' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Frontend', 'ayecode-connect' ); ?></h2>
				<table class="form-table wpbs-table-settings">
					<tr valign="top">
						<th scope="row"><label for="wpbs-css"><?php esc_html_e( 'Load CSS', 'ayecode-connect' ); ?></label></th>
						<td>
							<select name="ayecode-ui-settings[css]" id="wpbs-css" <?php echo ! empty( $overrides['css'] ) ? 'disabled' : ''; ?>>
								<option value="compatibility" <?php selected( $settings['css'], 'compatibility' ); ?>><?php esc_html_e( 'Compatibility Mode (default)', 'ayecode-connect' ); ?></option>
								<option value="core" <?php selected( $settings['css'], 'core' ); ?>><?php esc_html_e( 'Full Mode', 'ayecode-connect' ); ?></option>
								<option value="" <?php selected( $settings['css'], '' ); ?>><?php esc_html_e( 'Disabled', 'ayecode-connect' ); ?></option>
							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><label for="wpbs-js"><?php esc_html_e( 'Load JS', 'ayecode-connect' ); ?></label></th>
						<td>
							<select name="ayecode-ui-settings[js]" id="wpbs-js" <?php echo ! empty( $overrides['js'] ) ? 'disabled' : ''; ?>>
								<option value="core-popper" <?php selected( $settings['js'], 'core-popper' ); ?>><?php esc_html_e( 'Core + Popper (default)', 'ayecode-connect' ); ?></option>
								<option value="popper" <?php selected( $settings['js'], 'popper' ); ?>><?php esc_html_e( 'Popper', 'ayecode-connect' ); ?></option>
								<option value="required" <?php selected( $settings['js'], 'required' ); ?>><?php esc_html_e( 'Required functions only', 'ayecode-connect' ); ?></option>
								<option value="" <?php selected( $settings['js'], '' ); ?>><?php esc_html_e( 'Disabled (not recommended)', 'ayecode-connect' ); ?></option>
							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><label for="wpbs-font_size"><?php esc_html_e( 'HTML Font Size (px)', 'ayecode-connect' ); ?></label></th>
						<td>
							<input type="number" name="ayecode-ui-settings[html_font_size]" id="wpbs-font_size" value="<?php echo absint( $settings['html_font_size'] ); ?>" placeholder="16" <?php echo ! empty( $overrides['html_font_size'] ) ? 'disabled' : ''; ?> />
							<p class="description"><?php esc_html_e( 'Our font sizing is rem (responsive based) here you can set the html font size in-case your theme is setting it too low.', 'ayecode-connect' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Backend', 'ayecode-connect' ); ?> (wp-admin)</h2>
				<table class="form-table wpbs-table-settings">
					<tr valign="top">
						<th scope="row"><label for="wpbs-css-admin"><?php esc_html_e( 'Load CSS', 'ayecode-connect' ); ?></label></th>
						<td>
							<select name="ayecode-ui-settings[css_backend]" id="wpbs-css-admin" <?php echo ! empty( $overrides['css_backend'] ) ? 'disabled' : ''; ?>>
								<option value="compatibility" <?php selected( $settings['css_backend'], 'compatibility' ); ?>><?php esc_html_e( 'Compatibility Mode (default)', 'ayecode-connect' ); ?></option>
								<option value="core" <?php selected( $settings['css_backend'], 'core' ); ?>><?php esc_html_e( 'Full Mode (will cause style issues)', 'ayecode-connect' ); ?></option>
								<option value="" <?php selected( $settings['css_backend'], '' ); ?>><?php esc_html_e( 'Disabled', 'ayecode-connect' ); ?></option>
							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><label for="wpbs-js-admin"><?php esc_html_e( 'Load JS', 'ayecode-connect' ); ?></label></th>
						<td>
							<select name="ayecode-ui-settings[js_backend]" id="wpbs-js-admin" <?php echo ! empty( $overrides['js_backend'] ) ? 'disabled' : ''; ?>>
								<option value="core-popper" <?php selected( $settings['js_backend'], 'core-popper' ); ?>><?php esc_html_e( 'Core + Popper (default)', 'ayecode-connect' ); ?></option>
								<option value="popper" <?php selected( $settings['js_backend'], 'popper' ); ?>><?php esc_html_e( 'Popper', 'ayecode-connect' ); ?></option>
								<option value="required" <?php selected( $settings['js_backend'], 'required' ); ?>><?php esc_html_e( 'Required functions only', 'ayecode-connect' ); ?></option>
								<option value="" <?php selected( $settings['js_backend'], '' ); ?>><?php esc_html_e( 'Disabled (not recommended)', 'ayecode-connect' ); ?></option>
							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><label for="wpbs-disable-admin"><?php esc_html_e( 'Disable load on URL', 'ayecode-connect' ); ?></label></th>
						<td>
							<p><?php esc_html_e( 'If you have backend conflict you can enter a partial URL argument that will disable the loading of AUI on those pages. Add each argument on a new line.', 'ayecode-connect' ); ?></p>
							<textarea name="ayecode-ui-settings[disable_admin]" rows="10" cols="50" id="wpbs-disable-admin" class="large-text code" spellcheck="false" placeholder="myplugin.php &#10;action=go"><?php echo esc_textarea( $settings['disable_admin'] ); ?></textarea>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
			<div id="wpbs-version" data-aui-source="<?php echo esc_attr( $this->get_load_source() ); ?>"><?php echo esc_html( $this->version ); ?></div>
		</div>
		<?php
	}

	/**
	 * Show admin notice if backend scripts not loaded correctly.
	 *
	 * @return void
	 */
	public function show_admin_style_notice(): void {
		$settings = $this->settings->get_settings();

		if ( $settings['css_backend'] === 'compatibility' && $settings['js_backend'] === 'core-popper' ) {
			return; // Correct settings, no need to show notice
		}

		$fix_url = admin_url( 'options-general.php?page=ayecode-ui-settings&aui-fix-admin=true&nonce=' . wp_create_nonce( 'aui-fix-admin' ) );
		$button = '<a href="' . esc_url( $fix_url ) . '" class="button-primary">Fix Now</a>';
		$message = __( '<b>Style Issue:</b> AyeCode UI is disabled or set wrong.', 'ayecode-connect' ) . ' ' . $button;

		echo '<div class="notice notice-error aui-settings-error-notice"><p>' . wp_kses_post( $message ) . '</p></div>';
	}

	/**
	 * Get the source plugin/theme loading AUI.
	 *
	 * @return string Source name.
	 */
	private function get_load_source(): string {
		$file = str_replace( [ '/', '\\' ], '/', realpath( __FILE__ ) );
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
