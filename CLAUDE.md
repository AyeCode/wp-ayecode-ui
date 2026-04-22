# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Code Style

Run the WordPress coding standards linter against all PHP files:

```bash
vendor/bin/phpcs
```

Auto-fix correctable violations:

```bash
vendor/bin/phpcbf
```

Config is in `phpcs.xml.dist`. The text domain enforced for all i18n calls is `ayecode-connect`. The namespace for all classes is `AyeCode\UI`.

## Architecture

This package ships as both a standalone WordPress plugin (`wp-ayecode-ui.php`) and a Composer library dependency. The loading sequence is:

```
wp-ayecode-ui.php
  └── package-loader.php   (version negotiation + PSR-4 autoloader + instantiates Loader)
        └── src/Loader.php  (registers all WordPress hooks, no logic)
              ├── src/SettingsOrchestrator.php  (singleton, coordinates Settings + CSS_Generator + Admin + Customizer)
              ├── src/AssetManager.php          (singleton, registers + conditionally enqueues CSS/JS)
              ├── src/Admin.php                 (admin settings page)
              └── src/Customizer.php            (WordPress Customizer integration)
```

`package-loader.php` implements the **Double Negotiation** pattern: when multiple AyeCode plugins each bundle this package, only the highest version wins. Do not modify the negotiation logic — only edit the configuration block at the top of the closure.

### Public API

The only public entry point is the `aui()` singleton helper in `src/functions.php`, which returns the `AUI` class. Components are rendered via:

```php
echo aui()->input( $args );
echo aui()->button( $args );
echo aui()->alert( $args );
// etc.
```

Component classes live in `src/Components/` and must only use Bootstrap 5.3 utility classes for styling.

### Asset Loading Modes

Controlled by the `AUI_ASSETS_MODE` constant or the `aui_assets_mode` option:

| Mode | Behavior |
|------|----------|
| `auto` (default) | Lazy — enqueues only when AyeCode blocks are detected via `render_block` filter |
| `always` | Always enqueued (for themes) |
| `manual` | Never auto-enqueued; developer calls `wp_enqueue_style('ayecode-ui')` manually |

### SCSS

Source files are in `assets/scss/`. The compiled output is `assets/css/ayecode-ui.css`. There is no automated build step checked into this repo — compile manually with your preferred Sass tool targeting `assets/scss/ayecode-ui.scss`.

### Constants Defined by the Package Loader

- `AYECODE_UI_VERSION` — current loaded version string
- `AYECODE_UI_PLUGIN_DIR` — absolute path to the package root (trailing slash)
- `AYECODE_UI_PLUGIN_FILE` — absolute path to `wp-ayecode-ui.php`
