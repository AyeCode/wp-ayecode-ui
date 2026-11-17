<?php
/**
 * AyeCode UI test page, inside the active theme.
 *
 * The content is built before the theme's header so everything it enqueues is in the
 * head, as a block theme's own template canvas does.
 *
 * @package AyeCode\UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aui_preview = \AyeCode\UI\Preview::instance();

ob_start();
include __DIR__ . '/content.php';
$aui_preview_content = ob_get_clean();

if ( wp_is_block_theme() ) {
	$aui_preview_header = do_blocks( '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->' );
	$aui_preview_footer = do_blocks( '<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->' );
	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
<div class="wp-site-blocks">
	<?php
	echo $aui_preview_header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the theme's own template part.
	echo $aui_preview_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped templates.
	echo $aui_preview_footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the theme's own template part.
	?>
</div>
	<?php wp_footer(); ?>
</body>
</html>
	<?php
} else {
	get_header();
	echo $aui_preview_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped templates.
	get_footer();
}
