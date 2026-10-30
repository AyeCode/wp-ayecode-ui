/**
 * AyeCode UI carousel, built on Embla Carousel v9.
 *
 * This is the readable source. The file that loads is assets/js/aui-carousel.min.js,
 * which is the prebuilt Embla files followed by this file minified. See
 * docs/dev/CAROUSEL.md for how to rebuild it.
 *
 * Markup is configured through a single JSON attribute on the root:
 * data-aui-carousel='{"loop":true,"autoplay":5000,"fade":false,"autoHeight":false}'
 * Our own keys are removed before the rest is handed to Embla as options.
 */
( function ( window, document ) {
	'use strict';

	if ( window.aui_carousel_init ) {
		return;
	}

	var ROOT = '[data-aui-carousel]';
	var OWN_KEYS = [ 'autoplay', 'fade', 'autoHeight', 'accessibility' ];
	var i18n = window.aui_carousel_i18n || {};
	var reduced = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' );
	var observer = null;

	/**
	 * Replace %1$s style placeholders with the given values.
	 */
	function format( text, values ) {
		return String( text || '' ).replace( /%(\d)\$s/g, function ( match, n ) {
			return values[ n - 1 ];
		} );
	}

	/**
	 * Build an aria text callback for the accessibility plugin from two i18n strings.
	 */
	function aria_text( single, group ) {
		if ( ! i18n[ single ] ) {
			return undefined;
		}
		return function ( grouped, first, last, total ) {
			return first === last
				? format( i18n[ single ], [ first + 1, total ] )
				: format( i18n[ group ], [ first + 1, last + 1, total ] );
		};
	}

	/**
	 * Find the first matching element that belongs to this carousel and not to a nested one.
	 */
	function find( root, selector ) {
		var nodes = root.querySelectorAll( selector );
		for ( var i = 0; i < nodes.length; i++ ) {
			if ( nodes[ i ].closest( ROOT ) === root ) {
				return nodes[ i ];
			}
		}
		return null;
	}

	function emit( root, name, detail ) {
		root.dispatchEvent( new CustomEvent( 'aui:carousel:' + name, { bubbles: true, detail: detail } ) );
	}

	function parse_config( root ) {
		try {
			return JSON.parse( root.getAttribute( 'data-aui-carousel' ) || '{}' ) || {};
		} catch ( e ) {
			return {};
		}
	}

	/**
	 * Rebuild the dot buttons so there is one per snap. Existing dots are reused,
	 * so server rendered dots keep their place and do not shift the layout.
	 */
	function sync_dots( api, dots, instant ) {
		var count = api.snapList().length;
		var template = dots.firstElementChild;

		while ( dots.children.length > count ) {
			dots.removeChild( dots.lastElementChild );
		}
		while ( dots.children.length < count ) {
			var dot = template ? template.cloneNode( true ) : document.createElement( 'button' );
			if ( ! template ) {
				dot.type = 'button';
			}
			dots.appendChild( dot );
		}

		Array.prototype.forEach.call( dots.children, function ( dot, index ) {
			dot.onclick = function () {
				api.goTo( index, instant );
			};
		} );

		dots.classList.toggle( 'd-none', count < 2 );
	}

	function toggle_dots( api, dots ) {
		var selected = api.selectedSnap();
		Array.prototype.forEach.call( dots.children, function ( dot, index ) {
			var dot_inner = dot.firstElementChild || dot;
			dot_inner.classList.toggle( 'bg-primary', index === selected );
			dot_inner.classList.toggle( 'bg-secondary', index !== selected );
			dot_inner.classList.toggle( 'bg-opacity-25', index !== selected );
		} );
	}

	/**
	 * Set up the optional thumbnail strip as its own Embla instance kept in sync with the main one.
	 */
	function setup_thumbs( root, api, viewport, instant, direction ) {
		var thumbs = window.EmblaCarousel( viewport, {
			containScroll: 'keepSnaps',
			dragFree: true,
			direction: direction || 'ltr'
		} );
		var buttons = thumbs.slideNodes();

		function on_select() {
			var selected = api.selectedSnap();
			thumbs.goTo( selected, instant );
			buttons.forEach( function ( slide, index ) {
				var button = slide.querySelector( 'button' ) || slide;
				button.classList.toggle( 'opacity-50', index !== selected );
				if ( index === selected ) {
					button.setAttribute( 'aria-current', 'true' );
				} else {
					button.removeAttribute( 'aria-current' );
				}
			} );
		}

		buttons.forEach( function ( slide, index ) {
			var button = slide.querySelector( 'button' ) || slide;
			button.addEventListener( 'click', function () {
				api.goTo( index, instant );
			} );
		} );

		api.on( 'select', on_select ).on( 'reinit', on_select );
		on_select();

		root.auiCarouselThumbs = thumbs;
		return thumbs;
	}

	/**
	 * Start Embla on one carousel root.
	 */
	function create( root ) {
		var viewport = find( root, '.aui-carousel-viewport' );
		if ( ! viewport || ! window.EmblaCarousel ) {
			return;
		}

		var config = parse_config( root );
		var is_reduced = reduced && reduced.matches;
		var instant = !! is_reduced;
		var plugins = [];
		var options = {};

		Object.keys( config ).forEach( function ( key ) {
			if ( OWN_KEYS.indexOf( key ) === -1 ) {
				options[ key ] = config[ key ];
			}
		} );

		// Embla needs to be told about RTL, it does not read it from the page.
		if ( ! options.direction && 'rtl' === window.getComputedStyle( root ).direction ) {
			options.direction = 'rtl';
		}

		if ( is_reduced ) {
			options.duration = 10;
		}

		if ( config.fade && window.EmblaCarouselFade ) {
			plugins.push( window.EmblaCarouselFade() );
		}

		if ( config.autoHeight && window.EmblaCarouselAutoHeight ) {
			plugins.push( window.EmblaCarouselAutoHeight() );
		}

		if ( config.autoplay && ! is_reduced && window.EmblaCarouselAutoplay ) {
			plugins.push( window.EmblaCarouselAutoplay( {
				delay: true === config.autoplay ? 5000 : parseInt( config.autoplay, 10 ) || 5000,
				rootNode: function () {
					return root;
				}
			} ) );
		}

		if ( false !== config.accessibility && window.EmblaCarouselAccessibility ) {
			var a11y = {
				announceChanges: true,
				rootNode: function () {
					return root;
				},
				carouselAriaLabel: viewport.getAttribute( 'aria-label' ) || root.getAttribute( 'aria-label' ) || i18n.carousel,
				carouselAriaRoleDescription: i18n.carousel_role,
				slideAriaRoleDescription: i18n.slide_role,
				previousButtonAriaLabel: i18n.prev,
				nextButtonAriaLabel: i18n.next,
				slideAriaLabel: aria_text( 'slide', 'slides' ),
				dotButtonAriaLabel: aria_text( 'dot', 'dots' ),
				liveRegionContent: aria_text( 'live', 'lives' )
			};
			// Drop anything we have no translation for so the plugin defaults apply.
			Object.keys( a11y ).forEach( function ( key ) {
				if ( undefined === a11y[ key ] ) {
					delete a11y[ key ];
				}
			} );
			plugins.push( window.EmblaCarouselAccessibility( a11y ) );
		}

		// Slides the server hid to keep the first paint at the first slide's height.
		Array.prototype.forEach.call( viewport.querySelectorAll( '[data-aui-carousel-deferred]' ), function ( slide ) {
			slide.classList.remove( 'd-none' );
			slide.removeAttribute( 'data-aui-carousel-deferred' );
		} );

		viewport.firstElementChild.style.touchAction = 'axis' in options && 'y' === options.axis ? 'pan-x pinch-zoom' : 'pan-y pinch-zoom';

		var api = window.EmblaCarousel( viewport, options, plugins );
		var prev = find( root, '.aui-carousel-prev' );
		var next = find( root, '.aui-carousel-next' );
		var dots = find( root, '.aui-carousel-dots' );
		var live = find( root, '.aui-carousel-live' );
		var thumbs = find( root, '.aui-carousel-thumbs' );

		function update_controls() {
			var has_snaps = api.snapList().length > 1;
			[ prev, next ].forEach( function ( button ) {
				if ( button ) {
					button.classList.toggle( 'd-none', ! has_snaps );
				}
			} );
			if ( prev ) {
				prev.classList.toggle( 'disabled', ! api.canGoToPrev() );
			}
			if ( next ) {
				next.classList.toggle( 'disabled', ! api.canGoToNext() );
			}
			if ( dots ) {
				toggle_dots( api, dots );
			}
		}

		/**
		 * Embla re-runs plugin init on every reInit, which drops the accessibility
		 * plugin's links to our controls, so they are connected again each time.
		 */
		function setup_a11y() {
			var plugin = api.plugins();
			var a11y = plugin.accessibility;
			if ( ! a11y ) {
				return;
			}
			if ( prev && next ) {
				a11y.setupPrevAndNextButtons( prev, next );
			}
			if ( dots ) {
				a11y.setupDotButtons( dots );
			}
			if ( live ) {
				a11y.setupLiveRegion( live );
			}
			// A live region that reads out every autoplay step is noise, so only announce while stopped.
			if ( plugin.autoplay ) {
				a11y.setUpdateLiveRegion( ! plugin.autoplay.isPlaying() );
			}
		}

		function on_reinit() {
			if ( dots ) {
				sync_dots( api, dots, instant );
			}
			setup_a11y();
			update_controls();
			emit( root, 'reinit', { carousel: api } );
		}

		if ( prev ) {
			prev.addEventListener( 'click', function () {
				api.goToPrev( instant );
			} );
		}
		if ( next ) {
			next.addEventListener( 'click', function () {
				api.goToNext( instant );
			} );
		}
		if ( dots ) {
			sync_dots( api, dots, instant );
		}

		api.on( 'autoplay:play', function () {
			var a11y = api.plugins().accessibility;
			if ( a11y ) {
				a11y.setUpdateLiveRegion( false );
			}
		} ).on( 'autoplay:stop', function () {
			var a11y = api.plugins().accessibility;
			if ( a11y ) {
				a11y.setUpdateLiveRegion( true );
			}
		} );

		setup_a11y();

		// v9 autoplay no longer starts itself. Pause while hovered, and stop for good once the
		// visitor drags, focuses or uses a control, as the WAI carousel pattern asks.
		var user_stopped = false;

		function play() {
			var autoplay = api.plugins().autoplay;
			if ( autoplay && ! user_stopped ) {
				autoplay.play();
			}
		}

		function stop( for_good ) {
			var autoplay = api.plugins().autoplay;
			user_stopped = user_stopped || for_good;
			if ( autoplay ) {
				autoplay.stop();
			}
		}

		if ( config.autoplay && api.plugins().autoplay ) {
			api.on( 'pointerdown', function () {
				stop( true );
			} ).on( 'reinit', play );
			root.addEventListener( 'focusin', function () {
				stop( true );
			} );
			root.addEventListener( 'mouseenter', function () {
				stop( false );
			} );
			root.addEventListener( 'mouseleave', play );
			play();
		}

		if ( config.autoHeight && ! is_reduced ) {
			viewport.style.transition = 'height .2s';
		}

		api
			.on( 'select', function () {
				update_controls();
				emit( root, 'select', { carousel: api, index: api.selectedSnap(), previous: api.previousSnap() } );
			} )
			.on( 'settle', function () {
				emit( root, 'settle', { carousel: api, index: api.selectedSnap() } );
			} )
			.on( 'reinit', on_reinit )
			.on( 'destroy', function () {
				emit( root, 'destroy', { carousel: api } );
			} );

		root.auiCarousel = api;

		if ( thumbs ) {
			setup_thumbs( root, api, thumbs, instant, options.direction );
		}

		update_controls();
		root.setAttribute( 'data-aui-carousel-ready', '' );
		emit( root, 'init', { carousel: api, index: api.selectedSnap() } );
	}

	var lazy = 'IntersectionObserver' in window
		? new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					lazy.unobserve( entry.target );
					create( entry.target );
				}
			} );
		}, { rootMargin: '200px' } )
		: null;

	/**
	 * Initialise every carousel inside the given element, or the document.
	 * Safe to call more than once, carousels that are queued or running are skipped.
	 *
	 * @param {Element|Document} [root]
	 */
	function aui_carousel_init( root ) {
		var scope = root || document;
		var nodes = Array.prototype.slice.call( scope.querySelectorAll( ROOT ) );

		if ( scope.matches && scope.matches( ROOT ) ) {
			nodes.unshift( scope );
		}

		nodes.forEach( function ( node ) {
			if ( node.auiCarouselQueued ) {
				return;
			}
			node.auiCarouselQueued = true;
			if ( lazy ) {
				lazy.observe( node );
			} else {
				create( node );
			}
		} );
	}

	/**
	 * Pick up carousels added after load (AJAX tabs, lightboxes, editor previews)
	 * and destroy the ones that are taken out of the page.
	 */
	function watch() {
		if ( ! window.MutationObserver || observer ) {
			return;
		}
		observer = new MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				Array.prototype.forEach.call( mutation.addedNodes, function ( node ) {
					if ( 1 === node.nodeType && ( node.matches( ROOT ) || node.querySelector( ROOT ) ) ) {
						aui_carousel_init( node );
					}
				} );
				Array.prototype.forEach.call( mutation.removedNodes, function ( node ) {
					if ( 1 !== node.nodeType || node.isConnected ) {
						return;
					}
					var nodes = node.matches( ROOT ) ? [ node ] : node.querySelectorAll( ROOT );
					Array.prototype.forEach.call( nodes, function ( el ) {
						if ( lazy ) {
							lazy.unobserve( el );
						}
						if ( el.auiCarouselThumbs ) {
							el.auiCarouselThumbs.destroy();
						}
						if ( el.auiCarousel ) {
							el.auiCarousel.destroy();
						}
						el.auiCarousel = el.auiCarouselThumbs = el.auiCarouselQueued = undefined;
						el.removeAttribute( 'data-aui-carousel-ready' );
					} );
				} );
			} );
		} );
		observer.observe( document.body, { childList: true, subtree: true } );
	}

	window.aui_carousel_init = aui_carousel_init;

	function start() {
		aui_carousel_init();
		watch();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}( window, document ) );
