<?php
/**
 * Test page section: typography.
 *
 * @package AyeCode\UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="row g-4">
	<div class="col-lg-6">
		<?php foreach ( [ 1, 2, 3, 4, 5, 6 ] as $aui_preview_h ) : ?>
			<?php
			printf(
				'<h%1$d>%2$s</h%1$d>',
				(int) $aui_preview_h,
				/* translators: %d: heading level */
				esc_html( sprintf( __( 'Heading %d', 'ayecode-connect' ), $aui_preview_h ) )
			);
			?>
		<?php endforeach; ?>
		<p class="display-6"><?php esc_html_e( 'Display 6', 'ayecode-connect' ); ?></p>
		<p class="lead"><?php esc_html_e( 'Lead paragraph that stands out from the body text.', 'ayecode-connect' ); ?></p>
	</div>
	<div class="col-lg-6">
		<p><?php esc_html_e( 'Body text with a', 'ayecode-connect' ); ?> <a href="#aui-preview-typography"><?php esc_html_e( 'link', 'ayecode-connect' ); ?></a>, <strong><?php esc_html_e( 'bold', 'ayecode-connect' ); ?></strong>, <em><?php esc_html_e( 'italic', 'ayecode-connect' ); ?></em>, <mark><?php esc_html_e( 'highlight', 'ayecode-connect' ); ?></mark>, <small><?php esc_html_e( 'small', 'ayecode-connect' ); ?></small>, <code>code</code> <?php esc_html_e( 'and', 'ayecode-connect' ); ?> <kbd>Ctrl + K</kbd>.</p>
		<p class="text-body-secondary"><?php esc_html_e( 'Secondary text.', 'ayecode-connect' ); ?> <span class="text-body-tertiary"><?php esc_html_e( 'Tertiary text.', 'ayecode-connect' ); ?></span></p>
		<p class="fs-xs mb-1">fs-xs</p>
		<p class="fs-sm mb-1">fs-sm</p>
		<p class="fs-lg mb-1">fs-lg</p>
		<p class="fs-xl">fs-xl</p>
		<blockquote class="blockquote">
			<p><?php esc_html_e( 'A well-known quote, contained in a blockquote element.', 'ayecode-connect' ); ?></p>
			<footer class="blockquote-footer"><?php esc_html_e( 'Someone famous', 'ayecode-connect' ); ?></footer>
		</blockquote>
		<div class="row">
			<div class="col">
				<ul>
					<li><?php esc_html_e( 'List item', 'ayecode-connect' ); ?></li>
					<li><?php esc_html_e( 'List item', 'ayecode-connect' ); ?>
						<ul><li><?php esc_html_e( 'Nested item', 'ayecode-connect' ); ?></li></ul>
					</li>
				</ul>
			</div>
			<div class="col">
				<ol>
					<li><?php esc_html_e( 'First', 'ayecode-connect' ); ?></li>
					<li><?php esc_html_e( 'Second', 'ayecode-connect' ); ?></li>
				</ol>
			</div>
		</div>
		<pre><code>&lt;div class="bsui"&gt;...&lt;/div&gt;</code></pre>
	</div>
</div>
