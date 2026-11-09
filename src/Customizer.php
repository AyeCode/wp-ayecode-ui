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
	 * Add the custom palette colors to the grouped Super Duper color options.
	 *
	 * @param array $options Color options, optgroup label => [ slug => label ].
	 * @param array $types   Color types requested.
	 * @return array Modified color options.
	 */
	public function add_palette_colors( array $options, array $types ): array {
		$custom = $this->get_custom_palette_colors( $types );

		if ( $custom ) {
			$options[ __( 'Custom Colors', 'ayecode-connect' ) ] = $custom;
		}

		return $options;
	}

	/**
	 * Add the custom palette colors to the flat Super Duper color options.
	 *
	 * @param array $options Color options, slug => label.
	 * @param array $types   Color types requested.
	 * @return array Modified color options.
	 */
	public function add_palette_colors_flat( array $options, array $types ): array {
		return $options + $this->get_custom_palette_colors( $types );
	}

	/**
	 * Get the colors added to the palette by the user or a design kit.
	 *
	 * They have the solid and outline classes (see CSS_Generator::custom_color_css()),
	 * so they are offered alongside the core colors and the outlines, and not with
	 * the subtle or emphasis variants.
	 *
	 * @param array $types Color types requested.
	 * @return array<string, string> Color labels keyed by option value.
	 */
	private function get_custom_palette_colors( array $types ): array {
		$colors = [];

		foreach ( SettingsOrchestrator::get_added_palette_colors() as $slug => $color ) {
			if ( in_array( 'core', $types, true ) ) {
				$colors[ $slug ] = $color['name'];
			}

			if ( in_array( 'outline', $types, true ) ) {
				/* translators: %s: color name */
				$colors[ 'outline-' . $slug ] = sprintf( __( '%s outline', 'ayecode-connect' ), $color['name'] );
			}
		}

		return $colors;
	}
}
