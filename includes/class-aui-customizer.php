<?php
/**
 * AyeCode UI Customizer Integration
 *
 * Handles WordPress Customizer settings for AyeCode UI colors and options.
 *
 * @since 2.0.0
 * @package AyeCode_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AUI_Customizer class.
 *
 * Manages WordPress Customizer integration.
 */
class AUI_Customizer {

	/**
	 * Singleton instance.
	 *
	 * @var AUI_Customizer|null
	 */
	private static ?AUI_Customizer $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return AUI_Customizer
	 */
	public static function instance(): AUI_Customizer {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'customize_register', [ $this, 'register_customizer_settings' ] );

		// Add custom colors to BlockStrap if active
		if ( defined( 'BLOCKSTRAP_VERSION' ) ) {
			add_filter( 'sd_aui_colors', [ $this, 'add_blockstrap_colors' ], 10, 3 );
		}
	}

	/**
	 * Register customizer settings.
	 *
	 * @param WP_Customize_Manager $wp_customize WordPress Customizer object.
	 * @return void
	 */
	public function register_customizer_settings( $wp_customize ): void {
		// Add AUI section
		$wp_customize->add_section( 'aui_settings', [
			'title'    => __( 'AyeCode UI', 'ayecode-connect' ),
			'priority' => 120,
		] );

		// Primary Color
		$wp_customize->add_setting( 'aui_options[color_primary]', [
			'default'           => AUI_PRIMARY_COLOR,
			'sanitize_callback' => 'sanitize_hex_color',
			'capability'        => 'edit_theme_options',
			'type'              => 'option',
			'transport'         => 'refresh',
		] );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'color_primary', [
			'label'    => __( 'Primary Color', 'ayecode-connect' ),
			'section'  => 'aui_settings',
			'settings' => 'aui_options[color_primary]',
		] ) );

		// Secondary Color
		$wp_customize->add_setting( 'aui_options[color_secondary]', [
			'default'           => AUI_SECONDARY_COLOR,
			'sanitize_callback' => 'sanitize_hex_color',
			'capability'        => 'edit_theme_options',
			'type'              => 'option',
			'transport'         => 'refresh',
		] );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'color_secondary', [
			'label'    => __( 'Secondary Color', 'ayecode-connect' ),
			'section'  => 'aui_settings',
			'settings' => 'aui_options[color_secondary]',
		] ) );
	}

	/**
	 * Add custom colors to BlockStrap color selector.
	 *
	 * @param array $theme_colors Existing theme colors.
	 * @param bool  $include_outlines Whether to include outline variants.
	 * @param bool  $include_branding Whether to include branding colors.
	 * @return array Modified theme colors.
	 */
	public function add_blockstrap_colors( array $theme_colors, bool $include_outlines, bool $include_branding ): array {
		$setting = wp_get_global_settings();

		if ( ! empty( $setting['color']['palette']['custom'] ) ) {
			foreach ( $setting['color']['palette']['custom'] as $color ) {
				if ( isset( $color['slug'], $color['name'] ) ) {
					$theme_colors[ $color['slug'] ] = esc_attr( $color['name'] );
				}
			}
		}

		return $theme_colors;
	}
}
