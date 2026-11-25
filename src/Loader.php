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
		// Test page (/?preview-aui): its settings override must be in place before
		// anything reads the settings, as they are cached on the first read.
		add_filter( 'ayecode-ui-settings', [ Preview::instance(), 'filter_settings' ], 20 );

		// Core initialization — reads settings, defines constants, adds body classes.
		add_action( 'init', array( SettingsOrchestrator::instance(), 'init' ) );

		// Front end performance switches for the BlockStrap setup; hooks before init.
		Performance::instance()->register_hooks();

		// Register settings with WordPress.
		add_action( 'admin_init', array( SettingsOrchestrator::instance(), 'register_settings' ) );

		// Test page, admins only.
		add_action( 'template_redirect', [ Preview::instance(), 'send_headers' ] );
		add_filter( 'template_include', [ Preview::instance(), 'template' ], 99 );
		add_filter( 'wp_robots', [ Preview::instance(), 'robots' ] );
		add_filter( 'pre_get_document_title', [ Preview::instance(), 'document_title' ] );
		add_filter( 'body_class', [ Preview::instance(), 'body_class' ] );
		add_action( 'wp_enqueue_scripts', [ Preview::instance(), 'enqueue' ], 20 );

		// Register all assets early so they can be conditionally enqueued.
		add_action( 'wp_enqueue_scripts', array( AssetManager::instance(), 'register_assets' ), 1 );
		add_action( 'admin_enqueue_scripts', array( AssetManager::instance(), 'register_assets' ), 1 );

		// Lazy-loading: detect AyeCode blocks during render and enqueue if found.
		add_filter( 'render_block', array( AssetManager::instance(), 'detect_ayecode_blocks' ), 10, 2 );
		add_action( 'wp_super_duper_rendered', array( AssetManager::instance(), 'detect_super_duper_output' ) );
		add_action( 'wp_head', array( AssetManager::instance(), 'enqueue_if_detected' ), 7 );

		// render_block only fires once the loop is rendering, which is after wp_head has
		// been sent, so the head pass can never see a block. Ask again in the footer, where
		// WordPress still prints a late-enqueued stylesheet.
		add_action( 'wp_footer', array( AssetManager::instance(), 'enqueue_if_detected' ), 7 );

		// Hooks that depend on the active theme; BLOCKSTRAP_VERSION is defined by the
		// theme's functions.php, which loads after plugins_loaded.
		add_action( 'after_setup_theme', [ $this, 'register_theme_hooks' ], 20 );

		// Admin notices and fix handler.
		add_action( 'admin_notices', array( Admin::instance(), 'show_admin_style_notice' ) );
		add_action( 'admin_init', array( Admin::instance(), 'maybe_fix_admin_settings' ) );
		add_action( 'admin_init', array( Admin::instance(), 'maybe_enable_seen_blocks' ) );
		add_action( 'admin_notices', array( Admin::instance(), 'show_seen_blocks_notice' ) );
	}

	/**
	 * Register the hooks that depend on the active theme.
	 *
	 * With the BlockStrap theme the colors come from the theme.json palette, so the
	 * custom palette colors are offered in the block color pickers. Any other theme
	 * gets the Customizer section instead. Hooking customize_register is also what
	 * makes WordPress show the Customize menu for a block theme, so BlockStrap must
	 * not get it.
	 *
	 * @return void
	 */
	public function register_theme_hooks(): void {
		if ( defined( 'BLOCKSTRAP_VERSION' ) ) {
			add_filter( 'sd_get_aui_colors', [ Customizer::instance(), 'add_palette_colors' ], 10, 2 );
			add_filter( 'sd_get_aui_colors_flat', [ Customizer::instance(), 'add_palette_colors_flat' ], 10, 2 );
		} else {
			add_action( 'customize_register', [ Customizer::instance(), 'register_customizer_settings' ] );
		}
	}
}
