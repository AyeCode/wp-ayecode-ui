/**
 * AyeCode UI lightbox gallery.
 *
 * Fills the modal that aui_lightbox_embed() (includes/inc/bs5-js.php) opened with every
 * .aui-lightbox-image and .aui-lightbox-iframe link of the closest .aui-gallery, on the
 * AyeCode UI carousel, starting at the clicked link. Loaded on demand after
 * aui-carousel.min.js. Source of assets/js/aui-lightbox.min.js, rebuild: docs/dev/CAROUSEL.md.
 */
( function ( window, document ) {
	'use strict';

	var i18n = window.aui_carousel_i18n || {};

	function esc( text ) {
		return String( text || '' ).replace( /&/g, '&amp;' ).replace( /"/g, '&quot;' ).replace( /</g, '&lt;' );
	}

	// A caption printed beside the link (as the Post Images block does) is shown under the image.
	function caption( link ) {
		var cap = link.parentElement && link.parentElement.querySelector( '.carousel-caption, .figure-caption' );

		if ( ! cap ) {
			return '';
		}

		cap = cap.cloneNode( true );
		cap.classList.remove( 'visually-hidden', 'sr-only', 'figure-caption' );
		cap.classList.add( 'carousel-caption' );

		return cap.outerHTML;
	}

	function slide( link, current ) {
		var href = esc( link.getAttribute( 'href' ) );
		var img = link.querySelector( 'img' );
		var srcset = img && ( img.getAttribute( 'srcset' ) || img.getAttribute( 'data-srcset' ) );
		var inner;

		if ( link.classList.contains( 'aui-lightbox-iframe' ) ) {
			// Videos get their src once their slide is selected, see activate().
			inner = '<div class="ratio ratio-16x9 mx-auto" style="max-width:min(98vw,160vh)"><iframe class="aui-carousel-iframe" src="" data-src="' + href + ( href.indexOf( '?' ) < 0 ? '?' : '&amp;' ) + 'rel=0&amp;modestbranding=1&amp;autoplay=1" allow="autoplay; fullscreen" allowfullscreen></iframe></div>';
		} else {
			inner = '<img src="' + href + '"' + ( srcset ? ' srcset="' + esc( srcset ) + '" sizes="98vw"' : '' ) + ' alt="' + esc( img && img.alt ) + '" class="mx-auto d-block w-auto mw-100 rounded" style="max-height:90vh"' + ( current ? '' : ' loading="lazy"' ) + '>';
		}

		return '<div class="aui-carousel-slide position-relative">' + inner + caption( link ) + '</div>';
	}

	function control( direction ) {
		return '<button type="button" class="aui-carousel-' + direction + ' carousel-control-' + direction + '"><span class="carousel-control-' + direction + '-icon" aria-hidden="true"></span><span class="visually-hidden">' + esc( i18n[ direction ] ) + '</span></button>';
	}

	// Start the selected video and stop the others.
	function activate( root, index ) {
		Array.prototype.forEach.call( root.querySelectorAll( '.aui-carousel-slide' ), function ( el, i ) {
			var iframe = el.querySelector( '.aui-carousel-iframe' );

			if ( iframe && ( i === index ) !== !! iframe.getAttribute( 'src' ) ) {
				iframe.src = i === index ? iframe.getAttribute( 'data-src' ) : '';
			}
		} );
	}

	window.aui_lightbox_open = function ( link, modal ) {
		var gallery = link.closest( '.aui-gallery' );
		var links = gallery ? gallery.querySelectorAll( '.aui-lightbox-image[href], .aui-lightbox-iframe[href]' ) : [ link ];
		var index = Math.max( 0, Array.prototype.indexOf.call( links, link ) );
		var body = modal.querySelector( '.modal-body' );
		var slides = '';
		var dots = '';
		var root;

		Array.prototype.forEach.call( links, function ( el, i ) {
			slides += slide( el, i === index );
			dots += '<button type="button" data-bs-target="" class="bg-white" aria-label="' + esc( String( i18n.dot || '' ).replace( '%1$s', i + 1 ).replace( '%2$s', links.length ) ) + '"></button>';
		} );

		body.innerHTML = '<div class="aui-carousel position-relative" data-aui-carousel=\'' + JSON.stringify( { loop: true, startSnap: index } ) + '\'>'
			+ '<div class="aui-carousel-main position-relative">'
			+ '<div class="aui-carousel-viewport overflow-hidden" role="region" aria-roledescription="' + esc( i18n.carousel_role ) + '" aria-label="' + esc( i18n.lightbox ) + '">'
			+ '<div class="row flex-nowrap gx-0 gy-0 row-cols-1 align-items-center">' + slides + '</div></div>'
			+ ( links.length > 1 ? control( 'prev' ) + control( 'next' ) : '' )
			+ '</div>'
			+ ( links.length > 1 ? '<div class="aui-carousel-dots carousel-indicators position-static m-0 mt-2">' + dots + '</div>' : '' )
			+ '<div class="aui-carousel-live visually-hidden"></div></div>';

		root = body.firstElementChild;

		root.addEventListener( 'aui:carousel:init', function ( e ) {
			activate( root, e.detail.index );
		} );
		root.addEventListener( 'aui:carousel:select', function ( e ) {
			activate( root, e.detail.index );
		} );

		modal.addEventListener( 'keydown', function ( e ) {
			var api = root.auiCarousel;

			if ( api && /^Arrow(Left|Right)$/.test( e.key ) ) {
				e.preventDefault();
				// In RTL the next slide sits to the left.
				if ( ( 'ArrowRight' === e.key ) !== ( 'rtl' === document.documentElement.dir ) ) {
					api.goToNext();
				} else {
					api.goToPrev();
				}
			}
		} );

		modal.addEventListener( 'hidden.bs.modal', function () {
			modal.remove();
		} );

		if ( window.aui_carousel_init ) {
			window.aui_carousel_init( root );
		}
	};
}( window, document ) );
