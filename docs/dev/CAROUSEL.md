# AyeCode UI Carousel

A slider built on [Embla Carousel](https://www.embla-carousel.com/) v9. Use it for any carousel in AyeCode output instead of Bootstrap's `.carousel` or the old `.carousel-multiple-items` workaround (both still work, but are no longer the recommended path).

- One script, `assets/js/aui-carousel.min.js` (about 14 KB gzipped): Embla core plus the autoplay, fade, auto-height and accessibility plugins, and our setup code.
- Nothing loads by default. The script is enqueued only when a carousel with two or more slides is rendered, and it loads deferred in the footer.
- Layout is pure Bootstrap 5.3 utilities, so the first slide shows at its final size before the script runs. There is no carousel CSS file.
- Each carousel is started lazily, when it comes within 200px of the viewport.

## PHP

```php
echo aui()->carousel(
	array(
		'slides'     => $slides,          // Array of slide HTML strings. Required.
		'aria_label' => __( 'Listing photos', 'geodirectory' ),
		'per_view'   => array( 'xs' => 1, 'md' => 2, 'lg' => 3 ),
		'loop'       => true,
	)
);
```

Pass `true` as the second argument to echo instead of return. Calling `carousel()` enqueues the script itself.

| Argument | Type | Default | Description |
|---|---|---|---|
| `slides` | array | `[]` | Slide HTML, one string per slide. Run through `wp_kses` (post tags plus iframes) unless `raw` is set. |
| `thumbnails` | array | `[]` | Thumbnail HTML, one per slide. Adds a synced thumbnail strip and replaces the dots. Use with one slide per view. |
| `id` | string | `''` | Root element id. |
| `class` | string | `''` | Extra classes on the root. |
| `slide_class` | string | `''` | Extra classes on every slide wrapper. |
| `aria_label` | string | `'Carousel'` | Accessible name of the carousel region. Give each carousel a meaningful one. |
| `arrows` | bool\|string | `true` | Previous and next buttons: `true` or `'edge'` on the slide edges, `'hover'` on the edges but only while the slides are hovered or focused (card galleries), `'end'` in a row above the slides on the end side (card rows), `false` for none. |
| `dots` | bool | `true` | Dot buttons, one per page of slides. |
| `autoplay` | bool\|int | `false` | `true` for every 5 seconds, or a delay in milliseconds. |
| `fade` | bool | `false` | Fade between slides instead of sliding. Forces one slide per view. |
| `auto_height` | bool | `false` | Resize to the slides in view. Useful for mixed image ratios. |
| `loop` | bool | `false` | Wrap around at the ends. |
| `align` | string | `'start'` | `start`, `center` or `end`. |
| `per_view` | int\|array | `1` | Slides per view, see below. |
| `scroll` | int\|string | `1` | How far the arrows, dots and swipes move: a number of slides, or `'page'` for all the slides in view. |
| `gap` | int | `3` | Space between slides as a Bootstrap gutter step, 0 to 5. |
| `lazy` | bool | `true` | Load slide images as they come into view, see below. |
| `raw` | bool | `false` | Print the slide and thumbnail HTML as given. For markup your own code built and escaped, which may carry `srcset`, inline SVG or data attributes that kses would strip. |
| `options` | array | `[]` | Any other [Embla options](https://www.embla-carousel.com/api/options/), merged last. |

A single slide is printed as static markup with no script at all.

### Lazy images

Images in the slides that show first (the largest `per_view`, or one with `fade`) are printed as given, so the browser finds them in the HTML and they count towards the page's own eager and lazy decisions. In every later slide the `src` and `srcset` of each image move to `data-src` and `data-srcset` with a transparent GIF in their place, and the script sets them back as the slide comes into view, one slide ahead on either side. Thumbnails get `loading="lazy"`. Pass `'lazy' => false` to print every image as given.

Measured against native `loading="lazy"` on a page of five sliders: the same LCP or better, and about a third fewer image bytes on mobile and half on desktop, because the browser's lazy margin fetches two or three slides it never shows.

For your own markup, enqueue the script yourself:

```php
aui()->enqueue_carousel();
```

## Slides per view

How many slides show is set in CSS with Bootstrap's `row-cols-*` classes on the slide row, never in the JS options. `per_view` takes an int for every screen size, or an array keyed by breakpoint (`xs`, `sm`, `md`, `lg`, `xl`, `xxl`), 1 to 6:

```php
'per_view' => 3                                     // row-cols-3
'per_view' => array( 'xs' => 1, 'md' => 2, 'lg' => 3 ) // row-cols-1 row-cols-md-2 row-cols-lg-3
```

By default the arrows and dots move one slide at a time. Set `'scroll' => 'page'` to move all the slides in view at once, or a number such as `2` to move that many:

```php
'per_view' => 3,
'scroll'   => 'page', // three slides per click, one dot per three slides
```

Either way the last view is never left half empty: with 8 slides at 3 per view and `scroll` at 1 there are 6 stops, and so 6 dots. In raw markup this is Embla's `slidesToScroll` option (`1`, a number, or `"auto"` for a page).

## Raw markup

```html
<div class="aui-carousel position-relative" data-aui-carousel='{"loop":true,"autoplay":5000}'>
	<div class="aui-carousel-main position-relative">
		<div class="aui-carousel-viewport overflow-hidden" role="region" aria-roledescription="carousel" aria-label="Listing photos">
			<div class="row flex-nowrap gx-3 gy-0 row-cols-1 row-cols-md-2">
				<div class="aui-carousel-slide">...</div>
				<div class="aui-carousel-slide">...</div>
			</div>
		</div>
		<button type="button" class="aui-carousel-prev btn btn-light shadow-sm rounded-circle position-absolute top-50 start-0 translate-middle-y z-1 ms-2" aria-label="Previous slide">‹</button>
		<button type="button" class="aui-carousel-next btn btn-light shadow-sm rounded-circle position-absolute top-50 end-0 translate-middle-y z-1 me-2" aria-label="Next slide">›</button>
	</div>
	<div class="aui-carousel-dots d-flex flex-wrap justify-content-center mt-2">
		<button type="button" class="btn p-1 border-0 lh-1"><span class="d-block p-1 rounded-circle bg-primary"></span></button>
	</div>
	<div class="aui-carousel-live visually-hidden"></div>
</div>
```

- `.aui-carousel-viewport` (required) is the Embla root. Its first child is the slide row, and every child of that row is a slide.
- `.aui-carousel-prev`, `.aui-carousel-next`, `.aui-carousel-dots`, `.aui-carousel-live` and `.aui-carousel-thumbs` are optional. Leave one out and that feature is skipped.
- Dots are rebuilt to match the number of pages by cloning the first one. Print one per slide (as the PHP helper does) and the row keeps its height when the script trims them. An empty dots wrapper also works, but then the dots appear when the script runs.
- Thumbnails: `<div class="aui-carousel-thumbs overflow-hidden mt-2"><div class="row flex-nowrap gx-2 gy-0 row-cols-4 row-cols-md-6"><div><button type="button" class="btn p-0 border-0 w-100">...</button></div>...</div></div>`.

## Configuration: `data-aui-carousel`

All configuration is one JSON object in `data-aui-carousel`. The attribute is also what marks an element as a carousel. These keys are ours and are removed before the rest is passed to Embla:

| Key | Value | Description |
|---|---|---|
| `autoplay` | `true` or ms | Autoplay, every 5 seconds or the given delay. |
| `fade` | `true` | Fade plugin. |
| `autoHeight` | `true` | Auto-height plugin. |
| `accessibility` | `false` | Turn the accessibility plugin off. It is on by default. |

Every other key is an Embla v9 option, for example `loop`, `align`, `containScroll`, `slidesToScroll`, `dragFree`, `duration`, `startSnap`, `axis`, `breakpoints`. The direction is picked up from the page, so RTL works without setting it.

## Accessibility and motion

- The viewport is a labelled `region`, slides get `role="group"` with "Slide 1 of 8" labels, and slides out of view are `aria-hidden` with their links and buttons taken out of the tab order.
- Arrows and dots are real buttons with labels. They come before the dots in the tab order.
- The live region announces "Showing slide 2 of 8" after each change. It stays quiet while autoplay is running.
- Autoplay pauses while the pointer is over the carousel and stops for good once the visitor drags, focuses or uses a control.
- With `prefers-reduced-motion: reduce` there is no autoplay and controls jump instantly.

All screen reader strings are translatable (`ayecode-connect`) and are passed to the script as `aui_carousel_i18n`.

## JavaScript

Each root element gets:

- `el.auiCarousel`: the Embla API (`goTo`, `goToNext`, `selectedSnap`, `on`, `plugins()`, and so on).
- `el.auiCarouselThumbs`: the thumbnail strip's Embla API, when there is one.

The engine behind the carousel may change (for example to Bootstrap 6's own carousel once AyeCode UI moves to Bootstrap 6). `aui()->carousel()`, the `data-aui-carousel` attribute and the `aui:carousel:*` events are the stable API and will be kept. `el.auiCarousel` and `el.auiCarouselThumbs` expose Embla directly and may change with the engine, so prefer the events where they cover what you need.

Events are dispatched on the root and bubble:

| Event | `event.detail` |
|---|---|
| `aui:carousel:init` | `{ carousel, index }` |
| `aui:carousel:select` | `{ carousel, index, previous }` |
| `aui:carousel:settle` | `{ carousel, index }` |
| `aui:carousel:reinit` | `{ carousel }` |
| `aui:carousel:destroy` | `{ carousel }` |

```js
document.addEventListener( 'aui:carousel:select', function ( event ) {
	console.log( event.target.id, event.detail.index );
} );
```

Carousels added to the page later (AJAX tabs, lightboxes, editor previews) are found automatically by a MutationObserver, and ones taken out of the page are destroyed. To start them yourself, call:

```js
aui_carousel_init( container ); // or aui_carousel_init() for the whole document
```

It is safe to call more than once.

## Lightbox gallery

Clicking an `.aui-lightbox-image` or `.aui-lightbox-iframe` link opens every such link inside the closest `.aui-gallery` in a modal, on this carousel, starting at the clicked one. Links outside an `.aui-gallery` open on their own.

- Nothing loads with the page. The gallery code lives at the end of `assets/js/aui-carousel.min.js`. The inline script (`includes/inc/bs5-js.php`, `aui_lightbox_embed()`) opens the modal with a spinner, then fetches that file the first time a link is clicked unless a carousel on the page already loaded it, and calls `aui_lightbox_open( link, modal )`. The URL and screen reader strings are printed in `aui_lightbox_params`.
- Images: the link `href` is the full size image. A `srcset` (or `data-srcset`) on the thumbnail inside the link is carried over with `sizes="98vw"`. Only the clicked image loads at once; the rest are lazy slides like any other.
- Captions: a `.carousel-caption` or `.figure-caption` that is a sibling of the link (a child of `link.parentElement`) is cloned under the image, with `visually-hidden` removed.
- Iframe links (video) load their `src` when selected, with autoplay, and are unloaded when another slide is selected or the modal closes.
- The arrows and the indicator lines are Bootstrap's own carousel controls (`carousel-control-prev/next`, `carousel-indicators`), so the lightbox adds no CSS of its own.
- Arrow keys move the gallery, swapped in RTL. Escape closes it.

```php
// GeoDirectory's Post Images block prints this shape.
<div class="aui-gallery">
	<div class="position-relative">
		<a href="full.jpg" class="aui-lightbox-image"><img src="thumb.jpg" srcset="..." alt="..."></a>
		<div class="carousel-caption">...</div>
	</div>
</div>
```

## Updating Embla

There is no build step in this repo. `assets/js/aui-carousel.min.js` is the prebuilt Embla files followed by `assets/js/src/aui-carousel.js` minified. To rebuild (needs `curl` and a global `terser`):

```sh
V=9.0.0-rc04   # the new Embla version
P=https://cdn.jsdelivr.net/combine/npm
curl -sfo /tmp/embla.js "$P/embla-carousel@$V/embla-carousel.umd.min.js,npm/embla-carousel-autoplay@$V/embla-carousel-autoplay.umd.min.js,npm/embla-carousel-fade@$V/embla-carousel-fade.umd.min.js,npm/embla-carousel-auto-height@$V/embla-carousel-auto-height.umd.min.js,npm/embla-carousel-accessibility@$V/embla-carousel-accessibility.umd.min.js"
terser assets/js/src/aui-carousel.js -c -m --comments false -o /tmp/aui-carousel.js
{
	printf '/*! AyeCode UI carousel | Embla Carousel %s (MIT, David Jerleke): core, autoplay, fade, auto-height, accessibility | rebuild: docs/dev/CAROUSEL.md */\n(function(define,exports,module){\n' "$V"
	sed -n '/^\/\*\*$/,/^ \*\/$/!p' /tmp/embla.js | grep -v '^//# sourceMappingURL'
	printf '\n}).call(window);\n'
	cat /tmp/aui-carousel.js
	echo
} > assets/js/aui-carousel.min.js
```

Then set `AssetManager::EMBLA_VERSION` to the same version, which is also the script's cache-busting version. The `(function(define,exports,module){...})` wrapper keeps Embla on its browser globals when a page also loads an AMD or CommonJS loader. Keep core and plugins on the same version.
