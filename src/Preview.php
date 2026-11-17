<?php
/**
 * AyeCode UI test page.
 *
 * @package AyeCode\UI
 */

namespace AyeCode\UI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The test page at /?preview-aui: every Bootstrap component and AyeCode UI extra on one
 * page, inside the active theme, to check for regressions by eye (and later by
 * screenshot). Only administrators can open it; for anyone else the parameter is ignored.
 *
 * Per request options, never saved:
 * - aui-css=compatibility|core  CSS mode to preview
 * - aui-theme=light|dark|auto   starting color mode
 * - aui-static=1                no transitions or animations, for screenshots
 * - aui-section=forms           one section only
 *
 * Other plugins add their own sections through the ayecode_ui_preview_sections filter.
 * Hooks are registered by Loader.
 */
class Preview {

	/**
	 * The query parameter that opens the page.
	 */
	public const QUERY_VAR = 'preview-aui';

	/**
	 * Singleton instance.
	 *
	 * @var Preview|null
	 */
	private static ?Preview $instance = null;

	/**
	 * Whether this request is for the page, once known.
	 *
	 * @var bool|null
	 */
	private ?bool $is_request = null;

	/**
	 * The CSS mode setting before the page overrode it.
	 *
	 * @var string
	 */
	private string $site_css = '';

	/**
	 * Get the singleton instance.
	 *
	 * @return Preview
	 */
	public static function instance(): Preview {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Whether this request is for the test page, made by an administrator.
	 *
	 * @return bool
	 */
	public function is_request(): bool {
		if ( null !== $this->is_request ) {
			return $this->is_request;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only page, no action is taken.
		if ( is_admin() || wp_doing_ajax() || ! isset( $_GET[ self::QUERY_VAR ] ) ) {
			$this->is_request = false;
			return false;
		}

		// The settings can be read before the user is known; the answer waits until it is.
		if ( ! function_exists( 'wp_get_current_user' ) || ( ! did_action( 'plugins_loaded' ) && ! doing_action( 'plugins_loaded' ) ) ) {
			return false;
		}

		$this->is_request = current_user_can( 'manage_options' );

		return $this->is_request;
	}

	/**
	 * Read one of the page's own query parameters, limited to the allowed values.
	 *
	 * @param string $key     Parameter name.
	 * @param array  $allowed Allowed values.
	 * @param string $fallback Value when missing or not allowed.
	 * @return string
	 */
	public function get_param( string $key, array $allowed, string $fallback = '' ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only page, no action is taken.
		$value = isset( $_GET[ $key ] ) ? sanitize_key( wp_unslash( $_GET[ $key ] ) ) : '';

		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}

	/**
	 * Load everything on the page: assets always, the chosen CSS mode, and CSS and JS
	 * even when the site has them switched off, as the page is useless without them.
	 *
	 * @param array $settings The AyeCode UI settings.
	 * @return array
	 */
	public function filter_settings( array $settings ): array {
		if ( ! $this->is_request() ) {
			return $settings;
		}

		$this->site_css = (string) ( $settings['css'] ?? '' );

		$settings['load_mode'] = 'always';
		$settings['css']       = $this->get_param( 'aui-css', [ 'compatibility', 'core' ], $this->site_css ? $this->site_css : 'compatibility' );

		if ( empty( $settings['js'] ) ) {
			$settings['js'] = 'core-popper';
		}

		return $settings;
	}

	/**
	 * Whether the site has the AyeCode UI CSS switched off (the page loads it anyway).
	 *
	 * @return bool
	 */
	public function site_css_disabled(): bool {
		return '' === $this->site_css;
	}

	/**
	 * Keep the page out of caches.
	 *
	 * @return void
	 */
	public function send_headers(): void {
		if ( $this->is_request() ) {
			nocache_headers();
		}
	}

	/**
	 * Keep the page out of search engines.
	 *
	 * @param array $robots Robots directives.
	 * @return array
	 */
	public function robots( array $robots ): array {
		return $this->is_request() ? wp_robots_no_robots( $robots ) : $robots;
	}

	/**
	 * Use the page template.
	 *
	 * @param string $template The template WordPress chose.
	 * @return string
	 */
	public function template( string $template ): string {
		return $this->is_request() ? AYECODE_UI_PLUGIN_DIR . 'templates/preview/page.php' : $template;
	}

	/**
	 * The browser title.
	 *
	 * @param string $title The title.
	 * @return string
	 */
	public function document_title( string $title ): string {
		return $this->is_request() ? __( 'AyeCode UI test page', 'ayecode-connect' ) : $title;
	}

	/**
	 * Mark the body, for the page's own few styles.
	 *
	 * @param array $classes Body classes.
	 * @return array
	 */
	public function body_class( array $classes ): array {
		if ( $this->is_request() ) {
			$classes[] = 'aui-preview';
		}

		return $classes;
	}

	/**
	 * Enqueue everything the sections use, and the toolbar script.
	 *
	 * @return void
	 */
	public function enqueue(): void {
		if ( ! $this->is_request() ) {
			return;
		}

		$assets = AssetManager::instance();
		$assets->load_assets();
		$assets->enqueue_select2();
		AssetManager::mark_select2_enqueued();
		$assets->enqueue_flatpickr();
		$assets->enqueue_carousel();
		$assets->enqueue_iconpicker();

		$css = '
			.aui-preview-toolbar{top:var(--wp-admin--admin-bar--height,0px);z-index:1030}
			.aui-preview-swatch{width:100%;height:3rem}
			.aui-preview-box{min-height:4rem}
			.aui-preview-section{scroll-margin-top:calc(var(--aui-preview-offset,7rem) + 1rem)}
			.aui-preview-nav{top:calc(var(--aui-preview-offset,7rem) + 1rem);max-height:calc(100vh - var(--aui-preview-offset,7rem) - 2rem);z-index:1}
		';

		// Screenshots: nothing moves, and entrance animations show at once.
		if ( '1' === $this->get_param( 'aui-static', [ '1' ] ) ) {
			$css .= '
				*,*::before,*::after{transition:none!important;animation:none!important;scroll-behavior:auto!important}
				.aui-reveal{opacity:1!important;transform:none!important}
			';
		}

		wp_add_inline_style( 'ayecode-ui', $css );

		wp_register_script( 'aui-preview', '', [], AYECODE_UI_VERSION, true );
		wp_enqueue_script( 'aui-preview' );
		wp_add_inline_script( 'aui-preview', $this->toolbar_script() );
	}

	/**
	 * The toolbar's color mode buttons and the indeterminate checkboxes, which have no
	 * HTML attribute.
	 *
	 * @return string
	 */
	private function toolbar_script(): string {
		$start = $this->get_param( 'aui-theme', [ 'light', 'dark', 'auto' ] );

		return 'document.addEventListener("DOMContentLoaded",function(){
			var root=document.documentElement,dark=window.matchMedia("(prefers-color-scheme: dark)");
			function set(mode){
				root.setAttribute("data-bs-theme",mode==="auto"?(dark.matches?"dark":"light"):mode);
				document.querySelectorAll("[data-aui-preview-theme]").forEach(function(b){
					var on=b.getAttribute("data-aui-preview-theme")===mode;
					b.classList.toggle("active",on);
					b.setAttribute("aria-pressed",on?"true":"false");
				});
			}
			document.querySelectorAll("[data-aui-preview-theme]").forEach(function(b){
				b.addEventListener("click",function(){set(b.getAttribute("data-aui-preview-theme"));});
			});
			' . ( $start ? 'set(' . wp_json_encode( $start ) . ');' : '' ) . '
			document.querySelectorAll("[data-aui-preview-indeterminate]").forEach(function(el){el.indeterminate=true;});
			var main=document.getElementById("aui-preview"),bar=main&&main.querySelector(".aui-preview-toolbar");
			function offset(){
				var h=0;
				document.querySelectorAll(".fixed-top").forEach(function(el){if(!main.contains(el)){h=Math.max(h,el.getBoundingClientRect().bottom);}});
				var admin=document.getElementById("wpadminbar"),top=admin&&getComputedStyle(admin).position==="fixed"?admin.offsetHeight:0;
				h=Math.max(0,h-top);
				main.style.paddingTop=h+"px";
				bar.style.top=(top+h)+"px";
				main.style.setProperty("--aui-preview-offset",(top+h+bar.offsetHeight)+"px");
			}
			if(bar){offset();window.addEventListener("resize",offset);}
			// Sidebar: the active section is the last one whose top has passed under the toolbar.
			var links=[].slice.call(document.querySelectorAll("#aui-preview-nav a[href^=\"#aui-preview-\"]"));
			function spy(){
				var line=parseFloat(getComputedStyle(main).getPropertyValue("--aui-preview-offset"))+32,current=links[0];
				if(window.innerHeight+window.scrollY>=document.documentElement.scrollHeight-2){current=links[links.length-1];}
				else{links.forEach(function(a){var s=document.getElementById(a.getAttribute("href").slice(1));if(s&&s.getBoundingClientRect().top<=line){current=a;}});}
				links.forEach(function(a){var on=a===current;a.classList.toggle("active",on);if(on){a.setAttribute("aria-current","true");}else{a.removeAttribute("aria-current");}});
				if(current){var nav=current.closest(".aui-preview-nav"),r=current.getBoundingClientRect(),n=nav.getBoundingClientRect();if(r.top<n.top||r.bottom>n.bottom){nav.scrollTop+=r.top-n.top-n.height/2;}}
			}
			if(links.length){spy();window.addEventListener("scroll",spy,{passive:true});window.addEventListener("resize",spy);}
		});';
	}

	/**
	 * The page's sections, in order. nav_title is an optional shorter title for the
	 * sidebar.
	 *
	 * @return array<string, array{title: string, callback: callable, nav_title?: string}>
	 */
	public function get_sections(): array {
		$core = [
			'typography' => [ __( 'Typography', 'ayecode-connect' ), '' ],
			'colors'     => [ __( 'Colors', 'ayecode-connect' ), '' ],
			'buttons'    => [ __( 'Buttons', 'ayecode-connect' ), '' ],
			'forms'      => [ __( 'Forms', 'ayecode-connect' ), '' ],
			'content'    => [ __( 'Alerts, badges, cards, lists and tables', 'ayecode-connect' ), __( 'Alerts, cards, tables', 'ayecode-connect' ) ],
			'navigation' => [ __( 'Navigation', 'ayecode-connect' ), '' ],
			'overlays'   => [ __( 'Dropdowns, modals and other overlays', 'ayecode-connect' ), __( 'Overlays', 'ayecode-connect' ) ],
			'feedback'   => [ __( 'Progress, spinners and placeholders', 'ayecode-connect' ), __( 'Progress, spinners', 'ayecode-connect' ) ],
			'components' => [ __( 'AyeCode UI components', 'ayecode-connect' ), __( 'aui() components', 'ayecode-connect' ) ],
			'widgets'    => [ __( 'Carousel, lightbox, date picker, select and icon picker', 'ayecode-connect' ), __( 'Carousel, pickers', 'ayecode-connect' ) ],
			'helpers'    => [ __( 'Block helper classes', 'ayecode-connect' ), __( 'Block helpers', 'ayecode-connect' ) ],
		];

		$sections = [];
		foreach ( $core as $id => $titles ) {
			$sections[ $id ] = [
				'title'     => $titles[0],
				'nav_title' => $titles[1],
				'callback'  => function () use ( $id ) {
					$preview = $this;
					include AYECODE_UI_PLUGIN_DIR . 'templates/preview/sections/' . $id . '.php';
				},
			];
		}

		/**
		 * Filter the test page sections, to add a plugin's own.
		 *
		 * Each section is keyed by an id (used as its anchor) and has a title and a
		 * callback that prints its markup. The page wraps the output in .bsui, so plain
		 * Bootstrap markup works in both CSS modes.
		 *
		 * @param array<string, array{title: string, callback: callable}> $sections The sections.
		 */
		$sections = (array) apply_filters( 'ayecode_ui_preview_sections', $sections );

		return array_filter(
			$sections,
			static function ( $section ) {
				return is_array( $section ) && ! empty( $section['title'] ) && ! empty( $section['callback'] ) && is_callable( $section['callback'] );
			}
		);
	}

	/**
	 * The sections to show: all, or the one asked for.
	 *
	 * @return array<string, array{title: string, callback: callable}>
	 */
	public function get_shown_sections(): array {
		$sections = $this->get_sections();
		$only     = $this->get_param( 'aui-section', array_map( 'strval', array_keys( $sections ) ) );

		return $only ? [ $only => $sections[ $only ] ] : $sections;
	}

	/**
	 * Facts for the toolbar, which also make a support screenshot useful.
	 *
	 * @return array<string, string>
	 */
	public function get_info(): array {
		$settings = Settings::instance()->get_settings();
		$theme    = wp_get_theme();

		$css_labels = [
			'compatibility' => __( 'Compatibility', 'ayecode-connect' ),
			'core'          => __( 'Full', 'ayecode-connect' ),
			''              => __( 'Disabled', 'ayecode-connect' ),
		];

		return [
			__( 'AyeCode UI', 'ayecode-connect' ) => AYECODE_UI_VERSION,
			__( 'CSS mode', 'ayecode-connect' )   => $css_labels[ $settings['css'] ] ?? $settings['css'],
			__( 'Site CSS mode', 'ayecode-connect' ) => $css_labels[ $this->site_css ] ?? $this->site_css,
			__( 'JS', 'ayecode-connect' )         => (string) $settings['js'],
			__( 'Theme', 'ayecode-connect' )      => $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) . ( wp_is_block_theme() ? ' (' . __( 'block', 'ayecode-connect' ) . ')' : ' (' . __( 'classic', 'ayecode-connect' ) . ')' ),
			__( 'WordPress', 'ayecode-connect' )  => get_bloginfo( 'version' ),
		];
	}

	/**
	 * The page URL with some parameters changed.
	 *
	 * @param array $args Parameters to set; a null value removes one.
	 * @return string
	 */
	public function url( array $args = [] ): string {
		$keep = [];
		foreach ( [ 'aui-css', 'aui-theme', 'aui-static', 'aui-section' ] as $key ) {
			$value = $this->get_param( $key, [ 'compatibility', 'core', 'light', 'dark', 'auto', '1' ] );
			if ( 'aui-section' === $key ) {
				$value = $this->get_param( $key, array_map( 'strval', array_keys( $this->get_sections() ) ) );
			}
			if ( '' !== $value ) {
				$keep[ $key ] = $value;
			}
		}

		$args = array_merge( $keep, $args );
		$url  = add_query_arg( self::QUERY_VAR, '1', home_url( '/' ) );

		foreach ( $args as $key => $value ) {
			$url = null === $value ? remove_query_arg( $key, $url ) : add_query_arg( $key, rawurlencode( (string) $value ), $url );
		}

		return $url;
	}

	/**
	 * A placeholder image as an SVG data URI, so the page needs no image files or
	 * outside requests and always looks the same.
	 *
	 * @param int    $width  Width.
	 * @param int    $height Height.
	 * @param string $label  Text in the middle.
	 * @param int    $hue    Background hue, 0-360.
	 * @return string
	 */
	public static function placeholder_image( int $width, int $height, string $label, int $hue = 220 ): string {
		$svg = sprintf(
			'<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%2$d" viewBox="0 0 %1$d %2$d"><rect width="100%%" height="100%%" fill="hsl(%3$d,45%%,55%%)"/><text x="50%%" y="50%%" fill="#fff" font-family="sans-serif" font-size="%4$d" text-anchor="middle" dominant-baseline="middle">%5$s</text></svg>',
			$width,
			$height,
			$hue,
			max( 12, (int) ( min( $width, $height ) / 6 ) ),
			esc_html( $label )
		);

		return 'data:image/svg+xml;charset=utf-8,' . rawurlencode( $svg );
	}

	/**
	 * An icon, through Font Awesome Settings when it is loaded.
	 *
	 * @param string $icon Icon classes, e.g. 'fa-solid fa-heart'.
	 * @param string $class Extra classes.
	 * @return string
	 */
	public static function icon( string $icon, string $class = '' ): string {
		if ( function_exists( 'ayecode_get_icon' ) ) {
			return \ayecode_get_icon( $icon, $class ? [ 'class' => $class ] : [] );
		}

		return '<i class="' . esc_attr( trim( $icon . ' ' . $class ) ) . '" aria-hidden="true"></i>';
	}
}
