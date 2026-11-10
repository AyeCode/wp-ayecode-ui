# Changelog

All notable changes to the AyeCode UI will be documented in this file.

## [3.0.3-beta] - 2026-TBD
- Unused vendor styles dropped from the stylesheets: the Swiper, GLightbox, SimpleBar and noUiSlider skins (the slider and lightbox use Embla, ranges use the native form-range), the old Font Awesome icon picker and, from the compatibility and FSE bundles, the Select2 theme; also the Select2 and icon picker scripts and the stale framework CSS copies. About 9 KB less on the front end and 45 KB less in the compatibility and FSE bundles - REMOVED
- The Choices and flatpickr theme skins are compiled into choices.css and a new flatpickr.css (vendor styles plus skin, replacing flatpickr.min.css) instead of every bundle, so they load only with their scripts; about 14 KB less in each of the three main stylesheets - CHANGED
- Spacing steps 6 to 12 are generated only where the block margin and padding pickers use them: single-side margins and paddings (mt, me, mb, ms, pt, pe, pb, ps) at base, md and lg. The spacer scale is back to Bootstrap's 0 to 5, so every stock Bootstrap spacing, gap and gutter class is unchanged, while 6 to 12 on shorthands, gaps, gutters and the sm, xl and xxl sizes, and negative margins -6 to -12, are gone (a carousel arrow offset of -6 to -12 saved before now falls back to the default). About 43 KB less on the front end and 49 to 66 KB less in the compatibility and FSE bundles - REMOVED
- btn-*-subtle buttons for every theme color, built from Bootstrap's subtle background, border and emphasis text, so a subtle color picked for a button gets matching text and a hover state in light and dark mode - ADDED
- Carousel with autoplay or auto scroll whose slides all fit in view threw on start (the Embla plugins skip their setup on a single snap but still throw from play), and as the carousels start from one observer callback the throw left every carousel after it on the page dead until the observer fired again; the motor is not started with one snap, and a carousel that fails to start no longer stops the others - FIXED
- Developer note on the color system in docs/dev/COLORS.md: where colors come from, what the stylesheet mixes and what PHP writes, dark mode, added palette colors and design kits - ADDED
- In the compatibility and FSE stylesheets links took the compiled primary color instead of the info color, as the Bootstrap :root block those bundles print again at the end reset the link variables; the info mapping now sits in the runtime color partial, which loads after it - FIXED
- A custom secondary, light or dark color changed the button background but kept the compiled text color, so a dark custom secondary from the Customizer gave gray text on green at about 1.9:1; the button and text-bg text is picked by contrast as for the brand colors - FIXED
- Colors a design kit adds to the palette reach WordPress as theme colors, so they got no classes and were missing from the block color pickers; any palette color the BlockStrap theme does not define itself is now treated like a user added color, and added colors are also offered as outline options - FIXED
- Colors added to the Global Styles palette get the full set of classes a block color picker can produce (btn-, btn-outline-, text-, link-, bg-, text-bg-, border-), mixed from the color at runtime with contrast picked button text and the dark mode text tint, instead of buttons, links and alerts only - CHANGED
- Theme palette colors other than the eight theme colors (purple, gray, orange and the like) no longer have classes written for them, as no block color picker offers them; WordPress's own has-*-color classes still work. About 7.7 KB less inline CSS on a BlockStrap page - REMOVED
- btn-white, btn-black and btn-outline-white, offered by the block color pickers, had no styling on the front end; they are compiled into the stylesheet - FIXED
- css_overwrite() called with only a hex color wrote 0,0,0 as the RGB value - FIXED
- The generated CSS for primary, success, danger, warning and info is just the color variable plus, for a color other than the compiled default, white or dark button and text-bg text picked by contrast the way Bootstrap does; the stylesheet mixes every other shade. A custom brand color now gets the runtime hover, subtle and dark mode shades, the editor output for the default palette is 17 KB smaller, and a palette change in the editor previews live on every primary element - CHANGED
- A custom primary color no longer recolors the navbar hover and active links, the active breadcrumb, dropdown hover and a colored button hover shadow, which the default palette does not do - FIXED
- Brand color shades (button hover and active, subtle backgrounds and borders, emphasis text) are mixed from the base color in the browser with color-mix() instead of fixed at compile time, so a palette change, live in the editor or from the generated CSS, reaches every shade; secondary, light and dark keep their gray scale shades - CHANGED
- Brand colors used as text (text-*, link-*, outline buttons, links, emphasis) are lightened 30% in dark mode, where the base colors fell below 4.5:1 on the dark background (primary 3.6:1, info 3.6:1, danger 3.9:1; now 6:1 or more) - FIXED
- text-*, bg-*, text-bg-*, border-* and link-* for the brand colors, and links, read the color instead of the --bs-*-rgb triplet, so a Global Styles link color now applies to links - FIXED
- Generated color variables are set on :root and every [data-bs-theme] scope instead of html body, so the runtime shades read them and a custom color survives a nested color mode section - CHANGED
- A Global Styles link color pointing at a palette color that no longer exists is skipped instead of making every link inherit the text color - FIXED
- Default primary color darkened from #3993d5 to #2775b0, as white button text on it measured 3.3:1, below the WCAG AA 4.5:1 for button text; it now measures 4.9:1 - CHANGED
- The contrast threshold Bootstrap uses to pick white or dark text on a colored fill was lowered to 2, so success and warning buttons and text-bg classes had white text at 2.7:1 and 2.3:1; it is back to Bootstrap's 4.5, which gives them dark text - FIXED
- The default theme colors used to decide which palette colors to write out were still the Bootstrap 4 values while the stylesheet uses the v3 ones, so the editor and front end could show different colors; they now match the compiled stylesheet and the BlockStrap palette, compared case-insensitively - FIXED
- A changed primary color pulled every link to primary, through an `a` color rule and link variables on `html body` that outranked everything else, while the v3 design takes links from the info color; the link variables now follow info, and a Global Styles link color still takes precedence - FIXED
- Colors added to the Global Styles palette did not show in the block color pickers: the filter was added on a hook Super Duper v3 no longer fires, and only when BLOCKSTRAP_VERSION was defined, which it never is at plugins_loaded; they are added through sd_get_aui_colors and sd_get_aui_colors_flat once the theme has loaded - FIXED
- The Customizer section is no longer registered with the BlockStrap theme, which takes its colors from the Global Styles palette; hooking customize_register is what made WordPress show the Customize menu for the block theme - CHANGED
- Generated .link-{color} rules set color to the bare RGB triplet and button rules set --bs-btn-focus-shadow-rgb to a variable name, both invalid and dropped by the browser - FIXED
- The greedy nav "more" button, the time-ago clock and the icon picker placeholder were raw icon tags written by the inline script, so they rendered blank in Font Awesome SVG mode; they go through ayecode_get_icon() like the toast icons - FIXED
- The Bootstrap bundle loads with `defer` on the front end, taking 137 KB of script off the critical path (Lighthouse mobile first paint 0.75 s sooner on the bench landing page). Its inline set-up code moves from after the bundle to before it, which WordPress needs to honour the strategy; the code is all DOMContentLoaded listeners, so nothing changes for it. Attaching an "after" script or a blocking dependant to `bootstrap-js-bundle` makes WordPress fall back to a blocking tag, so inline code for the bundle should use the `before` position - CHANGED
- Checked checkboxes and radios in wp-admin rendered with no tick or dot: Bootstrap 5.3 draws the mark from a variable on .form-check-input, and wp-admin's `background` shorthand on input[type=checkbox]:checked outranked it and cleared the image - FIXED
- Performance section on the settings page, shown only when the BlockStrap theme and BlockStrap Blocks are active, with two front end switches, both off by default. Front end widgets: skip WordPress's 18 built-in widgets on visitor pages of the block theme, about 120 KB a page. Front end core blocks: register only the ticked core blocks, defaulting to the 53 the theme, plugin, kits and GeoDirectory use, about 560 KB a page; a block met on the front end that is not ticked renders its saved HTML, is recorded, and the settings page and an admin notice offer a one click enable. Admin, REST, AJAX, WP-CLI and Customizer previews always register everything - ADDED
- The Bootstrap 4 to 5 output conversion ran over every Super Duper block's output including the output of its children, which an enclosing block converts again, so a detail page scanned about 950 KB of HTML for a 280 KB page; a block with a Super Duper ancestor rendering is skipped and the outermost block converts the subtree once, 237 runs down to 9 on a detail page, output identical - CHANGED
- The icon picker configuration, which asks every icon library for its settings and scans the custom icons folder, was built on every visitor page; it is built in wp-admin, and on the front end only when a form has enqueued the picker - CHANGED
- Overlay classes are back: img-overlay, img-gradient-overlay and content-overlay, with the tint on a --bs-img-overlay-rgb custom property, and new mask-fade-x, mask-fade-start and mask-fade-end classes that fade an element's inline edges through a mask, direction aware, with the width on --bs-mask-fade - ADDED
- Carousel arrows arg takes bottom, a centred row below the slides with the dots between the two buttons - ADDED
- Carousel inside a carousel slide, such as an image slider in a listing card, takes its own swipes: the outer carousel ignores a drag that starts in a nested viewport - FIXED
- Carousel arrow_offset arg sets the margin between the edge arrows and the slide edges as a spacer step, negative to push them outside the carousel - ADDED
- Carousel edge_fade arg fades the slides into the background at the edges with the mask classes, across the peek strip or at both edges of a plain or scrolling carousel - ADDED
- Carousel peek with a centered slide: the first slide sat one gutter off center until it was scrolled past, as the row's negative margin started it before Embla's container; the margin now sits on the container - FIXED
- Carousel auto_scroll takes a negative speed to run the continuous scroll backward - ADDED
- Carousel arrows are round icon buttons on a body coloured disc, and the arrows arg takes edge, hover or end for their placement - CHANGED
- Carousel raw arg prints slides built and escaped by the caller without running them through kses - ADDED
- Carousel arrows can be printed anywhere on the page with Carousel::arrows() and 'arrows' => 'external', for a block's title row - ADDED
- Carousel peek arg shows the next slide in a strip of the given percent at the end, or split on both sides with align center, with no CSS of its own - ADDED
- Carousel auto_scroll arg for continuous scrolling, with Embla's auto scroll plugin added to the bundle - ADDED
- Carousels added to the block editor canvas after load were never started, as the editor rebuilds its iframe body under the script; the watcher now observes the document - FIXED
- Carousel slides past the first view hand their image sources to the script, which loads them one slide ahead as they come into view (lazy arg, on by default), and thumbnails use the browser's lazy loading - ADDED
- Lightbox gallery now runs on the Embla carousel (swipe, arrow keys) with Bootstrap's own arrows and indicators, lives in the carousel script and loads on demand on the first click, so pages no longer carry the gallery builder inline - CHANGED
- WP 7.0 breaks backend switches styles - FIXED
- Updatged the toasts to be light/dark - CHANGED
- Updated the toast icons to use new AUI functions - CHANGED
- Escaped component output (button href/onclick/style, textarea and pagination classes, select option labels) - FIXED
- Added a capability check to the fix-admin settings handler - FIXED
- Added a sanitize callback to the settings registration - FIXED
- Validated theme colors before writing them into generated CSS - FIXED
- Package loader now returns instead of exiting when WordPress is absent, so it no longer kills composer CLI tooling in host repos - FIXED
- Embla Carousel v9 slider component, aui()->carousel(), loaded only when a carousel is rendered - ADDED
- Dynamic data picker loads its tags from the ayecode/v3/dynamic-data endpoint and falls back to a built-in list when it is missing - CHANGED
- Dynamic data picker configure pane for tag args and modifiers (date formats, meta keys, taxonomies, word limit, link, fallback) with a live result - ADDED
- Dynamic data picker can reopen an existing tag through the initialTag prop, and search now covers every group - ADDED
- dynamic-data-grammar.js, the JS twin of the Super Duper tag parser, with tests against the shared fixture file - ADDED
- Dynamic data picker shows a live preview of the tag rendered against the post being edited - ADDED
- Dynamic data button takes a getInitialTag prop so a field can open the tag at its caret, and no longer steals focus from that field - ADDED

## [3.0.2-beta] - 2026-05-18
- .text-justify class was removed in BS5.3, we added it back in - ADDED
- Several small style bug fixes - FIXED

## [3.0.1-beta] - 2026-04-22
- Refactored to meet the new AyeCode package standards - CHANGED

## [3.0.0-beta] - 2026-04-08
- Initial beta release

## General V3 changes
- Removed support for Bootstrap < 5.3
- Removed the use of jQuery for speed
- Full dark mode support
- Updated to Bootstrap 5.3
- Settings page now uses our new Settings Framework package
- Added a new frontend loading option for faster page loads



