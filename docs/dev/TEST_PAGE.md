# AyeCode UI test page

A page with every Bootstrap component and AyeCode UI extra, rendered inside the active theme, for checking regressions after a CSS or JS change. It ships with the package, so it can also be opened on a customer site when something looks wrong there.

Open it at `/?preview-aui=1`, or from **Settings > AyeCode UI > Tools**.

Only administrators (`manage_options`) can open it. For anyone else the parameter is ignored and the site renders as normal. The page is sent with `noindex` and no-cache headers.

## Options

All of these apply to the one request only and never change a setting.

| Parameter | Values | Effect |
|---|---|---|
| `aui-css` | `compatibility`, `core` | CSS mode to preview. Defaults to the site setting. |
| `aui-theme` | `light`, `dark`, `auto` | Starting color mode. The toolbar also switches it live. |
| `aui-static` | `1` | No transitions or animations, and entrance animations show at once. Use it for screenshots. |
| `aui-section` | a section id | Show one section only, e.g. `forms`. |

The page always loads the CSS and JS, plus Choices, flatpickr, the carousel and the icon picker, even when the site's load mode is Auto or its CSS is switched off.

On wide screens a sidebar lists the sections and highlights the one in view; on small screens the toolbar's Sections menu does the same job.

The toolbar shows the AyeCode UI version, the CSS mode (previewed and site), the JS setting, the theme and the WordPress version, so a screenshot from a customer says what it was taken with.

## Sections

`typography`, `colors`, `buttons`, `forms`, `content` (alerts, badges, cards, list groups, tables), `navigation`, `overlays` (dropdowns, modal, offcanvas, accordion, collapse, toasts, tooltips, popovers), `feedback` (progress, spinners, placeholders), `components` (the `aui()` PHP components), `widgets` (carousel, lightbox, flatpickr, Choices, icon picker) and `helpers` (the classes the block settings output: entrance and hover animations, show on hover, background opacity, backdrop blur, border radius, spacing 6-12, icon boxes).

The content is fixed (no random values, dates or outside images; images are inline SVG) so screenshots can be compared between runs.

## Adding a section from another plugin

```php
add_filter( 'ayecode_ui_preview_sections', function ( array $sections ): array {
	$sections['geodirectory'] = [
		'title'     => __( 'GeoDirectory', 'geodirectory' ),
		'nav_title' => __( 'GeoDirectory', 'geodirectory' ), // Optional, shorter title for the sidebar.
		'callback'  => function () {
			// Print the markup to check, e.g. a listing card or a search form.
		},
	];

	return $sections;
} );
```

The output sits inside `.bsui`, so plain Bootstrap markup works in both CSS modes. The section id is its anchor (`#aui-preview-{id}`) and its `aui-section` value.

## Not covered

wp-admin: the page renders on the front end only, so clashes with WordPress admin CSS (such as the checked checkbox styles) do not show here.
