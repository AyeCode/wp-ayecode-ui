<?php
/**
 * Front end performance switches for the BlockStrap setup.
 *
 * @package AyeCode\UI
 */

namespace AyeCode\UI;

defined( 'ABSPATH' ) || exit;

/**
 * Skips WordPress work a BlockStrap site never uses on a visitor's page.
 *
 * Two opt-in switches, both off by default, both front end only. The editor,
 * the REST API, AJAX, WP-CLI and Customizer previews always register every
 * widget and every block, so nothing an admin sees changes.
 *
 * Widgets: WordPress registers its 18 built-in widgets on every request and
 * builds them all, although a block theme renders no widget area. About 120 KB
 * a page.
 *
 * Core blocks: WordPress registers 116 core block types on every request, with a
 * style handle each and theme.json metadata for each, although a BlockStrap page
 * is built from BlockStrap and GeoDirectory blocks and uses a few dozen core
 * ones. Registering only the enabled types is about 560 KB a page. A block that
 * is not enabled still saves in the editor; on the page it renders its saved
 * HTML without its block styles, and a dynamic block renders nothing. Such
 * blocks are recorded so the settings page can list them with a one click fix.
 *
 * Available only when the BlockStrap theme and the BlockStrap Blocks plugin are
 * active: the default block list is drawn from the templates, parts, patterns
 * and kits they ship.
 *
 * @since 3.0.3
 */
final class Performance {

	/**
	 * Option holding the core block names seen on the front end while not enabled.
	 */
	const SEEN_OPTION = 'ayecode-ui-blocks-seen';

	/**
	 * Core features registered as block types. Always registered, never a choice.
	 *
	 * The skip link loses its stylesheet and becomes visible without its type.
	 */
	const ALWAYS = [ 'template-skip-link' ];

	/**
	 * Core blocks the BlockStrap theme, the BlockStrap Blocks plugin, the kits and
	 * GeoDirectory's templates use, found by scanning every template, part,
	 * pattern and kit file they ship plus a site's saved content (2026-11-08);
	 * tools/block-audit/audit.php scan reports a shipped file that uses one outside it.
	 * Keep in step with those files.
	 */
	const DEFAULT_BLOCKS = [
		// Structure.
		'template-part',
		'pattern',
		'group',
		'spacer',
		'paragraph',
		'heading',
		'post-content',
		'query',
		'post-template',
		'query-no-results',
		// Post data.
		'post-terms',
		'post-date',
		'post-author',
		'post-navigation-link',
		'term-description',
		'avatar',
		'search',
		// Comments.
		'comments',
		'comments-title',
		'comment-template',
		'comment-author-name',
		'comment-date',
		'comment-content',
		'comment-edit-link',
		'comment-reply-link',
		'comments-pagination',
		'comments-pagination-previous',
		'comments-pagination-numbers',
		'comments-pagination-next',
		'post-comments-form',
		// Theme fallback parts, used when a site has no saved header or footer.
		'social-links',
		'social-link',
		'buttons',
		'button',
		'separator',
		'latest-posts',
		'latest-comments',
		'categories',
		// Common in saved content and in the theme's fallback pattern markup.
		'shortcode',
		'quote',
		'html',
		'post-title',
		'query-title',
		'query-pagination',
		'query-pagination-previous',
		'query-pagination-numbers',
		'query-pagination-next',
		'columns',
		'column',
		'site-title',
		'site-logo',
	];

	/**
	 * @var Performance|null
	 */
	private static $instance = null;

	/**
	 * Core block names met on this request whose type was not registered.
	 *
	 * @var array<string, true>
	 */
	private $seen = [];

	/**
	 * @return Performance
	 */
	public static function instance(): Performance {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Whether the switches exist on this site: BlockStrap theme and BlockStrap Blocks.
	 *
	 * @return bool
	 */
	public static function is_available(): bool {
		return 'blockstrap' === get_template() && defined( 'BLOCKSTRAP_BLOCKS_VERSION' );
	}

	/**
	 * The core block names registered on the front end: the enabled list plus ALWAYS.
	 *
	 * @param array $settings AyeCode UI settings.
	 * @return string[] Names without the core/ prefix.
	 */
	public static function enabled_blocks( array $settings ): array {
		$enabled = isset( $settings['frontend_blocks_enabled'] ) && is_array( $settings['frontend_blocks_enabled'] )
			? $settings['frontend_blocks_enabled']
			: self::DEFAULT_BLOCKS;

		return array_values( array_unique( array_merge( array_map( 'sanitize_key', $enabled ), self::ALWAYS ) ) );
	}

	/**
	 * Hook the switches. Runs before init so the init 1 widget registration can be caught.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		if ( ! self::is_available() || ! $this->is_front_end() ) {
			return;
		}

		$settings = Settings::instance()->get_settings();

		if ( 'on' === ( $settings['frontend_widgets'] ?? 'off' ) ) {
			// Core registers its widgets at init 1 and the factory builds them at
			// widgets_init 100. Fire the hook ourselves so everything hooked to it,
			// Super Duper's block registry included, still runs; only the 18 core
			// widget classes stay out.
			add_action( 'init', [ $this, 'unhook_core_widgets' ], 0 );
			add_filter( 'load_default_widgets', '__return_false' );
			add_action( 'init', [ $this, 'fire_widgets_init' ], 1 );
		}

		if ( 'on' === ( $settings['frontend_blocks'] ?? 'off' ) ) {
			add_action( 'init', [ $this, 'register_enabled_core_blocks' ], 9 );
			add_action( 'init', [ $this, 'deregister_unused_block_styles' ], 10 );
			add_filter( 'render_block_data', [ $this, 'record_unregistered_block' ], -PHP_INT_MAX );
			add_action( 'shutdown', [ $this, 'save_seen' ], 0 );
		}
	}

	/**
	 * A visitor's request on a block theme: not admin, AJAX, REST, JSON, WP-CLI or a
	 * Customizer preview.
	 *
	 * @return bool
	 */
	private function is_front_end(): bool {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || wp_is_json_request() ) {
			return false;
		}

		if ( ( defined( 'WP_CLI' ) && WP_CLI ) || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) ) {
			return false;
		}

		// A Customizer preview or changeset request renders widgets and blocks for the admin.
		if ( isset( $_REQUEST['customize_theme'] ) || isset( $_REQUEST['customize_changeset_uuid'] ) || isset( $_REQUEST['wp_customize'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
			return false;
		}

		return function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	}

	/**
	 * Remove core's widget registration.
	 *
	 * @return void
	 */
	public function unhook_core_widgets(): void {
		remove_action( 'init', 'wp_widgets_init', 1 );
	}

	/**
	 * Fire widgets_init at the point core would have.
	 *
	 * @return void
	 */
	public function fire_widgets_init(): void {
		do_action( 'widgets_init' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core hook, fired in place of wp_widgets_init().
	}

	/**
	 * Register only the enabled core block types.
	 *
	 * Static blocks come from core's list; dynamic blocks each hook init on their own
	 * as register_block_core_<name>, so the ones not enabled are unhooked.
	 *
	 * @return void
	 */
	public function register_enabled_core_blocks(): void {
		global $wp_filter;

		$enabled = self::enabled_blocks( Settings::instance()->get_settings() );

		remove_action( 'init', 'register_core_block_types_from_metadata' );

		if ( defined( 'BLOCKS_PATH' ) && file_exists( BLOCKS_PATH . 'require-static-blocks.php' ) ) {
			$folders = require BLOCKS_PATH . 'require-static-blocks.php';
			foreach ( (array) $folders as $folder ) {
				if ( in_array( $folder, $enabled, true ) ) {
					register_block_type_from_metadata( BLOCKS_PATH . $folder );
				}
			}
		}

		if ( empty( $wp_filter['init'] ) ) {
			return;
		}

		foreach ( $wp_filter['init']->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				$function = $callback['function'];
				if ( ! is_string( $function ) || 0 !== strpos( $function, 'register_block_core_' ) ) {
					continue;
				}
				$name = str_replace( '_', '-', substr( $function, strlen( 'register_block_core_' ) ) );
				if ( ! in_array( $name, $enabled, true ) ) {
					remove_action( 'init', $function, $priority );
				}
			}
		}
	}

	/**
	 * Drop the style handles core registered for block types that are not enabled.
	 *
	 * @return void
	 */
	public function deregister_unused_block_styles(): void {
		global $wp_styles;

		if ( ! $wp_styles instanceof \WP_Styles ) {
			return;
		}

		$enabled = self::enabled_blocks( Settings::instance()->get_settings() );

		foreach ( array_keys( $wp_styles->registered ) as $handle ) {
			if ( 0 !== strpos( $handle, 'wp-block-' ) ) {
				continue;
			}
			$name = preg_replace( '/-(theme|editor)$/', '', substr( $handle, strlen( 'wp-block-' ) ) );
			if ( 'library' !== $name && ! in_array( $name, $enabled, true ) ) {
				wp_deregister_style( $handle );
			}
		}
	}

	/**
	 * Remember a core block that rendered without its type, so the admin can enable it.
	 *
	 * @param array $parsed_block The block about to render.
	 * @return array
	 */
	public function record_unregistered_block( $parsed_block ) {
		$name = isset( $parsed_block['blockName'] ) ? (string) $parsed_block['blockName'] : '';

		if ( 0 === strpos( $name, 'core/' ) && ! \WP_Block_Type_Registry::get_instance()->is_registered( $name ) ) {
			$this->seen[ substr( $name, 5 ) ] = true;
		}

		return $parsed_block;
	}

	/**
	 * Add newly seen names to the option, once per request and only when there are new ones.
	 *
	 * @return void
	 */
	public function save_seen(): void {
		if ( empty( $this->seen ) ) {
			return;
		}

		$stored = (array) get_option( self::SEEN_OPTION, [] );
		$merged = array_values( array_unique( array_merge( $stored, array_keys( $this->seen ) ) ) );

		if ( count( $merged ) !== count( $stored ) ) {
			sort( $merged );
			update_option( self::SEEN_OPTION, $merged, false );
		}
	}

	/**
	 * Core blocks recorded on the front end that are not enabled.
	 *
	 * @return string[]
	 */
	public static function seen_blocks(): array {
		return array_values( array_filter( array_map( 'sanitize_key', (array) get_option( self::SEEN_OPTION, [] ) ) ) );
	}

	/**
	 * Enable the recorded blocks and clear the record.
	 *
	 * @return int How many were added.
	 */
	public static function enable_seen_blocks(): int {
		$seen = self::seen_blocks();
		if ( empty( $seen ) ) {
			return 0;
		}

		$option   = 'ayecode-ui-settings';
		$settings = (array) get_option( $option, [] );
		$enabled  = self::enabled_blocks( $settings );
		$added    = array_values( array_diff( $seen, $enabled ) );

		$settings['frontend_blocks_enabled'] = array_values( array_unique( array_merge( $enabled, $added ) ) );
		update_option( $option, $settings );
		delete_option( self::SEEN_OPTION );

		return count( $added );
	}

	/**
	 * The core block types for the settings checklist, as `name => "Category: Title"`,
	 * sorted by category then title. Admin only: every type is registered there.
	 *
	 * @return array<string, string>
	 */
	public static function core_block_choices(): array {
		$categories = [];
		foreach ( get_default_block_categories() as $category ) {
			$categories[ $category['slug'] ] = $category['title'];
		}

		$rows = [];
		foreach ( \WP_Block_Type_Registry::get_instance()->get_all_registered() as $name => $type ) {
			if ( 0 !== strpos( $name, 'core/' ) ) {
				continue;
			}
			$short = substr( $name, 5 );
			if ( in_array( $short, self::ALWAYS, true ) ) {
				continue;
			}
			$category       = $categories[ $type->category ?? '' ] ?? __( 'Other', 'ayecode-connect' );
			$title          = ! empty( $type->title ) ? $type->title : $short;
			$rows[ $short ] = [ $category, $title ];
		}

		uasort(
			$rows,
			static function ( $a, $b ) {
				return strcmp( $a[0] . ' ' . $a[1], $b[0] . ' ' . $b[1] );
			}
		);

		$choices = [];
		foreach ( $rows as $short => list( $category, $title ) ) {
			$choices[ $short ] = $category . ': ' . $title . ' (' . $short . ')';
		}

		return $choices;
	}
}
