<?php
/**
 * Global helper functions for AyeCode UI.
 *
 * The aui() function is the primary public API consumed by other plugins.
 * It is defined here and required at package init time so it is always
 * available regardless of which copy of the package wins version negotiation.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aui' ) ) {
	/**
	 * Return the AUI singleton for rendering components.
	 *
	 * @return \AyeCode\UI\AUI|null
	 */
	function aui(): ?\AyeCode\UI\AUI {
		return \AyeCode\UI\AUI::instance();
	}
}
