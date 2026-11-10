# AyeCode UI Colors

How the theme colors (primary, secondary, success, danger, warning, info, light, dark) and any colors the user adds reach the page, in light and dark mode.

The short version: the stylesheet mixes every shade from one CSS variable per color, and PHP only writes that variable, plus a readable text color, when a color differs from the compiled default.

## Where colors come from

| Site | Source of the colors | Where they are edited |
|---|---|---|
| BlockStrap theme | The Global Styles palette (`theme.json`, layered with the design kit's and the user's) | Site Editor > Styles > Colors > Palette |
| Any other theme | The `aui_options` option, `color_primary` and `color_secondary` | Customizer > AyeCode UI |
| Neither changed | The compiled stylesheet | `assets/scss/_user-variables.scss` |

The Customizer section is only registered when the BlockStrap theme is not active (`Loader::register_theme_hooks()`, at `after_setup_theme`, because `BLOCKSTRAP_VERSION` is defined by the theme). Hooking `customize_register` is also what makes WordPress show the Customize menu for a block theme.

There are no separate dark mode colors to edit. Dark mode is derived from the same values (see [Dark mode](#dark-mode)).

### The defaults must agree in three places

The eight default values live in:

1. `assets/scss/_user-variables.scss` (and Bootstrap's defaults for the ones not set there), compiled into the CSS;
2. the `AUI_*_COLOR_ORIGINAL` constants in `SettingsOrchestrator::define_color_constants()`;
3. the BlockStrap theme's `theme.json` and `styles/dark-mode.json` palettes.

PHP compares the palette with the constants to decide whether a color needs writing out. If the three disagree, the front end writes CSS for colors nobody changed, and the editor and front end can show different colors. Change all three together.

Primary is `#2775b0` and `$min-contrast-ratio` is `4.5`, so white button text meets WCAG AA (4.9:1).

## What the stylesheet does

Two partials, both compiled into all three bundles:

- **`_color-runtime.scss`** (imported last in each entry file) sets, on `:root,[data-bs-theme]`, the shades of the brand colors mixed from the base variable with `color-mix()`: `--bs-{color}-text-emphasis`, `--bs-{color}-bg-subtle` and `--bs-{color}-border-subtle`. It also maps the link variables to info and sets `--bs-text-tint`.
- **`partials/_colors.scss`** rebuilds `.text-*`, `.link-*`, `.bg-*`, `.text-bg-*` and `.border-*` for the brand colors, and the `a` color, to read the color variable instead of the `--bs-{color}-rgb` triplet.

The brand button loop in `components/_buttons.scss` does the same for `.btn-*` and `.btn-outline-*`: hover and active are the base mixed with 20% black.

"Brand colors" here means primary, success, danger, warning and info. Secondary, light and dark keep their hand picked gray scale shades and are not mixed at runtime.

Why it matters: Bootstrap bakes every shade in at compile time, so changing `--bs-primary` alone only changes the few rules that read it. Mixing from the variable means a palette change, from PHP or live in the editor, reaches every shade.

### Why `_color-runtime.scss` is imported last

The compatibility (`.bsui`) and FSE (`.editor-styles-wrapper`) bundles print Bootstrap's `:root` block again at the end, outside the wrapper. Anything set on `:root` before that is reset to the compiled values. That is how links once ended up on the compiled primary in wp-admin. Root variables that must win go in `_color-runtime.scss`.

### Dark mode

Brand colors are tuned for a light page. As text on the dark background, primary, info and danger fell to about 3.6:1 to 3.9:1.

`--bs-text-tint` is `0%` in light mode and `30%` in dark mode, and every use of a brand color as text (`.text-*`, `.link-*`, outline buttons, links, emphasis) mixes it with that much white. Fills (`.bg-*`, solid buttons) do not take the tint. In dark mode, subtle backgrounds and borders are mixed with `$gray-900` instead of white.

## What PHP writes

`CSS_Generator::custom_css()` builds the inline CSS added to the `ayecode-ui` (front end) or `ayecode-ui-fse` (block editor) stylesheet. For each color:

| Color | Front end | Editor |
|---|---|---|
| Brand color equal to its default | nothing | the variable, pointing at the preset |
| Brand color that differs | `CSS_Generator::css_overwrite()`: the variable and its `-rgb`, plus `contrast_text_css()` | same, with `var(--wp--preset--color--{slug})` as the value |
| Secondary, light or dark that differs | `css_overwrite()`: the variable plus the older per-class rules, plus contrast text | same |
| Color added by the user or a kit | `custom_color_css()`: the variable and every class a picker can produce | same |
| Theme palette extras (purple, gray, ...) | nothing | nothing |

Variables are written on `:root,[data-bs-theme]`, so the runtime shades read them and they survive a nested color mode section. Class rules are scoped to `.bsui` in compatibility mode, `.editor-styles-wrapper` in the editor, or nothing.

### Text on a colored fill

CSS cannot yet choose white or black text for a color it only knows at runtime (`contrast-color()` is not widely supported). So `CSS_Generator::contrast_text_color()` does it in PHP from the saved hex, the way Bootstrap's `color-contrast()` does: white if it reaches 4.5:1, else black if that does, else whichever is higher. It is used for solid buttons, outline button hover and active, and `.text-bg-*`.

For a color equal to its compiled default, nothing is written and the compiled text color applies.

### Added palette colors

`SettingsOrchestrator::get_added_palette_colors()` returns the colors the user added (the `custom` origin) and the colors a design kit added. A kit's `theme.json` is merged into the `theme` origin, so a kit color is any theme palette slug that the parent theme's own `theme.json` does not define. The eight theme colors are always excluded.

These colors:

- get the full set of classes from `custom_color_css()` (`btn-`, `btn-outline-`, `text-`, `link-`, `bg-`, `text-bg-`, `border-`), built the same way as the compiled brand colors, with the dark mode tint and contrast picked text;
- are offered in the Super Duper color pickers, as a "Custom Colors" group and as outline options, through the `sd_get_aui_colors` and `sd_get_aui_colors_flat` filters (`Customizer::add_palette_colors()`).

The theme's own extra palette colors (purple, salmon, gray and the like) get no AyeCode classes, as no picker offers them. WordPress's `has-*-color` classes still work for them.

`btn-white`, `btn-black` and `btn-outline-white` are compiled, because the pickers offer them but they are not theme colors.

### Global Styles link color

The Styles > Elements > Links color is written to `--bs-link-color` and `--bs-link-hover-color` on `body`, which beats the info mapping on `:root`. A reference to a palette color that no longer exists is skipped (`is_defined_preset_color()`), because an undefined variable would make every link inherit the text color.

## Design kits

A kit's `theme.json` palette should list only the colors the kit changes. A full copy of the palette pins every color to the values at export time, so a later change to the theme defaults never reaches sites using the kit.

The kit exporter in BlockStrap Blocks (`src/Dev/Exporter.php`) drops palette entries equal to the parent theme's `theme.json`, and the kit port script does the same.

## Known limits

- **Live editing.** The generated CSS is built once when the editor loads. Changing a palette color live updates the color and its mixed shades at once, because they read the preset variable, but:
  - the text color on a fill stays the one picked for the saved value until the editor is reloaded;
  - a color newly added to the palette has no classes until the editor is reloaded.
- **Hover shades** are a 20% black mix in sRGB, which goes a little muddy on warning and danger. Relative color syntax in oklch would darken more evenly.

## Working on the colors

- The compiled CSS must be rebuilt after any SCSS change: all three bundles, `ayecode-ui.css`, `ayecode-ui-compatibility.css` and `ayecode-ui-fse.css`.
- The stylesheet URL keeps the same `?ver=` across rebuilds, so the browser may serve the old file. Reload without cache when checking a change.
- Buttons transition `background-color`, so a computed style read right after a change can catch it halfway. Disable transitions when measuring.
- Links are info colored by design, not primary.
- Check a change in both color modes, with BlockStrap and with a classic theme (compatibility bundle), and in wp-admin.
