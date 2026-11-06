# Changelog

All notable changes to the AyeCode UI will be documented in this file.

## [3.0.3-beta] - 2026-TBD
- Overlay classes are back: img-overlay, img-gradient-overlay and content-overlay, with the tint on a --bs-img-overlay-rgb custom property, and new mask-fade-x, mask-fade-start and mask-fade-end classes that fade an element's inline edges through a mask, direction aware, with the width on --bs-mask-fade - ADDED
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



