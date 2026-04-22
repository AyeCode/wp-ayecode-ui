<?php
/**
 * AyeCode UI Package Loader
 *
 * Sole entry point for the package. Only registers WordPress hooks;
 * contains no business logic.
 *
 * @package AyeCode\UI
 */

namespace AyeCode\UI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loader class.
 *
 * Instantiated once by the Package Loader script. Its constructor is the
 * only place where WordPress action and filter hooks are registered for
 * the entire package.
 */
class Loader {

	/**
	 * Register all package hooks.
	 */
	public function __construct() {
		// Core initialization — reads settings, defines constants, adds body classes.
		add_action( 'init', [ SettingsOrchestrator::instance(), 'init' ] );

		// Register settings with WordPress.
		add_action( 'admin_init', [ SettingsOrchestrator::instance(), 'register_settings' ] );

		// Developer preview endpoint.
		add_action( 'template_redirect', [ SettingsOrchestrator::instance(), 'maybe_show_examples' ] );

		// Register all assets early so they can be conditionally enqueued.
		add_action( 'wp_enqueue_scripts',    [ AssetManager::instance(), 'register_assets' ], 1 );
		add_action( 'admin_enqueue_scripts', [ AssetManager::instance(), 'register_assets' ], 1 );

		// Lazy-loading: detect AyeCode blocks during render and enqueue if found.
		add_filter( 'render_block', [ AssetManager::instance(), 'detect_ayecode_blocks' ], 10, 2 );
		add_action( 'wp_head',      [ AssetManager::instance(), 'enqueue_if_detected' ], 7 );

		// WordPress Customizer integration.
		add_action( 'customize_register', [ Customizer::instance(), 'register_customizer_settings' ] );

		if ( defined( 'BLOCKSTRAP_VERSION' ) ) {
			add_filter( 'sd_aui_colors', [ Customizer::instance(), 'add_blockstrap_colors' ], 10, 3 );
		}

		// Admin notices and fix handler.
		add_action( 'admin_notices', [ Admin::instance(), 'show_admin_style_notice' ] );
		add_action( 'admin_init',    [ Admin::instance(), 'maybe_fix_admin_settings' ] );
	}
}
