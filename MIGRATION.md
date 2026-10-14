# AyeCode UI v2.0 Migration Guide

## Overview

AyeCode UI v2.0 is a major refactor that modernizes the codebase and removes legacy Bootstrap support. This version only supports **Bootstrap 5.3+ with dark mode capability**.

## Breaking Changes

### 1. Bootstrap Support
- **REMOVED**: Bootstrap 4 support
- **REMOVED**: Bootstrap 5 (non-dark mode) support
- **REQUIRED**: Bootstrap 5.3+ (with dark mode support)

### 2. PHP Requirements
- **Minimum PHP Version**: 7.4+
- Uses modern PHP features: typed properties, return types, null coalescing operator

### 3. Asset Structure
- **Removed folders**: `assets/` (BS4), `assets-v5/` (BS5 non-DM)
- **New structure**: `assets/` (BS 5.3+ with dark mode)
- All asset paths updated automatically

### 4. File Changes
- `includes/inc/bs4-js.php` - REMOVED
- New modular class structure (see below)

## New Features

### 1. Lazy Loading (Performance Optimization)
Assets are now loaded on-demand based on block detection:

**Load Modes:**
- `auto` (recommended): Only loads when AyeCode blocks (GeoDirectory, UsersWP) detected
- `always`: Loads on all pages (use in themes)
- `manual`: Developer-controlled loading

**For Themes:**
```php
// In your theme's functions.php
define( 'AUI_ALWAYS_LOAD', true );
```

### 2. Modular Architecture
The codebase has been refactored into specialized classes:

- `AUI_Settings` - Settings management
- `AUI_Asset_Manager` - Asset loading and lazy loading
- `AUI_CSS_Generator` - CSS generation and minification
- `AUI_Customizer` - WordPress Customizer integration
- `AUI_Admin` - Admin interface and settings page

### 3. Modern PHP 7.4+ Code
- Typed properties
- Return type declarations
- Null coalescing operators
- Arrow functions

## API Compatibility

### ✅ Backwards Compatible
The public API remains unchanged:

```php
// These still work exactly the same
aui()->alert( $args );
aui()->button( $args );
aui()->input( $args );
// etc...
```

### ✅ Constants
All color constants remain unchanged:
```php
AUI_PRIMARY_COLOR
AUI_SECONDARY_COLOR
// etc...
```

### ✅ Filters
Existing filters still work:
```php
add_filter( 'ayecode-ui-settings', ... );
add_filter( 'ayecode-ui-default-settings', ... );
```

## Settings Changes

### New Load Mode Setting
A new `load_mode` setting has been added:

```php
$settings = [
    'load_mode' => 'auto', // auto, always, manual
    // ... other settings unchanged
];
```

### Bootstrap Version Setting
The `bs_ver` setting now only accepts:
- `5dm` - Bootstrap 5.3+ with dark mode (default and only option)

Legacy options (`4`, `5`) are no longer available.

## For Plugin Developers

### If You Use AyeCode UI in Your Plugin

**No changes required** if you:
- Use the `aui()` function for components
- Don't directly access internal classes
- Support PHP 7.4+

**Action required** if you:
- Directly instantiated old classes (use new modular classes)
- Hardcoded asset paths (use `AUI_Asset_Manager::instance()` methods)
- Relied on BS4-specific code (update to BS5.3 markup)

### Manual Asset Loading

If you need to manually load assets:

```php
// Force load assets
AUI_Asset_Manager::instance()->load_assets();

// Or enqueue specific components
AUI_Asset_Manager::instance()->enqueue_select2();
AUI_Asset_Manager::instance()->enqueue_flatpickr();
AUI_Asset_Manager::instance()->enqueue_iconpicker();
```

## For Theme Developers

### Always Load Mode

For themes that use AUI throughout:

```php
// In your theme's functions.php
define( 'AUI_ALWAYS_LOAD', true );
```

This ensures assets load on every page.

### Dark Mode Support

BS 5.3 includes built-in dark mode support via `data-bs-theme` attribute:

```html
<html data-bs-theme="dark">
  <!-- Your theme content -->
</html>
```

Or dynamically:
```php
add_filter( 'body_class', function( $classes ) {
    if ( get_theme_mod( 'dark_mode_enabled' ) ) {
        $classes[] = 'dark-mode';
    }
    return $classes;
} );
```

## File Size Comparison

**Before v2.0:**
- Total assets: ~14.7MB (BS4 + BS5 + BS5.3)
- Code: 3,452 lines in main file

**After v2.0:**
- Total assets: ~5.0MB (BS5.3 only)
- Code: ~600 lines orchestrator + 5 modular classes
- **67% reduction in assets**
- **Improved maintainability**

## Testing Checklist

After upgrading to v2.0, test:

- [ ] Plugin/theme loads without errors
- [ ] Components render correctly (buttons, forms, alerts, etc.)
- [ ] Admin screens display properly
- [ ] Block editor functionality works
- [ ] Color customizations apply correctly
- [ ] Dark mode (if applicable) works
- [ ] Asset loading mode (`auto`, `always`, `manual`) works as expected

## Troubleshooting

### Assets Not Loading

**Check load mode:**
```php
// In wp-config.php for testing
define( 'AUI_ALWAYS_LOAD', true );
```

### Styling Issues

**Verify settings:**
1. Go to Settings → AyeCode UI
2. Ensure Backend CSS is set to "Compatibility Mode"
3. Ensure Backend JS is set to "Core + Popper"

### PHP Errors

**Check PHP version:**
```bash
php -v
```
Must be 7.4 or higher.

## Support

- **Documentation**: https://ayecode.io/ayecode-ui/
- **GitHub**: https://github.com/AyeCode/wp-ayecode-ui
- **Issues**: https://github.com/AyeCode/wp-ayecode-ui/issues

## Changelog

### [2.0.0] - 2026-02-03

#### Added
- Lazy loading with 3 modes: auto, always, manual
- Modular class architecture for better maintainability
- PHP 7.4+ modern syntax (typed properties, return types)
- New `load_mode` setting
- Minification for inline CSS/JS

#### Changed
- Complete refactor from monolithic to modular architecture
- Updated to Bootstrap 5.3+ only
- Asset folder structure simplified
- Settings file reduced from 3,452 to ~600 lines

#### Removed
- Bootstrap 4 support
- Bootstrap 5 (non-DM) support
- Deprecated functions (bs3_compat, get_url_old, etc.)
- BS version switching (now always BS 5.3+)
- ~9.7MB of legacy assets

#### Fixed
- Asset loading performance with lazy loading
- Code maintainability with modular design
- Memory usage with on-demand loading

---

**Note**: This is a major version. Test thoroughly before deploying to production. A backup of the old code is available at `includes/ayecode-ui-settings.php.backup` if needed.
