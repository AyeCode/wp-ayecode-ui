<?php
/**
 * Plugin Name: WP AyeCode UI
 * Plugin URI: https://ayecode.io/
 * Description: A Bootstrap 5.3+ UI component library for AyeCode WordPress plugins and themes.
 * Version: 3.0.0-beta
 * Author: AyeCode Ltd
 * Author URI: https://ayecode.io/
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: ayecode-connect
 * Requires at least: 5.0
 * Requires PHP: 7.4
 *
 * @package WP_AyeCode_UI
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Boot the package loader so the framework works as a standalone plugin.
require_once __DIR__ . '/package-loader.php';

// Update version:
// 1. Here
// 2. pacakge-loader.php
// 3. composer.json
