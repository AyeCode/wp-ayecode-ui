<?php
/**
 * AyeCode UI Customizer Integration
 *
 * Handles WordPress Customizer settings for AyeCode UI colors and options.
 *
 * @package AyeCode\UI
 */

namespace AyeCode\UI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customizer class.
 *
 * Manages WordPress Customizer integration. Hooks are registered by Loader,
 * not in this constructor.
 */
class Customizer {

	/**
	 * Singleton instance.
	 *
	 * @var Customizer|null
	 */
	private static ?Customizer $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Customizer
	 */
	public static function instance(): Customizer {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {}

	/**
	 * Register customizer settings.
	 *
	 * @param \WP_Customize_Manager $wp_customize WordPress Customizer object.
	 * @return void
	 */
	public function register_customizer_settings( $wp_customize ): void {
		$wp_customize->add_section( 'aui_settings', [
			'title'    => __( 'AyeCode UI', 'ayecode-connect' ),
			'priority' => 120,
		] );

		$wp_customize->add_setting( 'aui_options[color_primary]', [
			'default'           => AUI_PRIMARY_COLOR,
			'sanitize_callback' => 'sanitize_hex_color',
			'capability'        => 'edit_theme_options',
			'type'              => 'option',
			'transport'         => 'refresh',
		] );

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'color_primary', [
			'label'    => __( 'Primary Color', 'ayecode-connect' ),
			'section'  => 'aui_settings',
			'settings' => 'aui_options[color_primary]',
		] ) );

		$wp_customize->add_setting( 'aui_options[color_secondary]', [
			'default'           => AUI_SECONDARY_COLOR,
			'sanitize_callback' => 'sanitize_hex_color',
			'capability'        => 'edit_theme_options',
			'type'              => 'option',
			'transport'         => 'refresh',
		] );

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'color_secondary', [
			'label'    => __( 'Secondary Color', 'ayecode-connect' ),
			'section'  => 'aui_settings',
			'settings' => 'aui_options[color_secondary]',
		] ) );
	}

	/**
	 * Add custom colors to the BlockStrap color selector.
	 *
	 * @param array $theme_colors      Existing theme colors.
	 * @param bool  $include_outlines  Whether to include outline variants.
	 * @param bool  $include_branding  Whether to include branding colors.
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
