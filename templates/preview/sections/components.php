<?php
/**
 * Test page section: the aui() PHP components, as plugins output them.
 *
 * @package AyeCode\UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- aui() components escape their own output.
?>
<div class="row g-4">
	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Inputs', 'ayecode-connect' ); ?></h3>
		<?php
		echo aui()->input(
			[
				'type'        => 'text',
				'id'          => 'aui-preview-aui-text',
				'name'        => 'aui_preview_text',
				'label'       => __( 'Text, label on top', 'ayecode-connect' ),
				'label_type'  => 'top',
				'placeholder' => __( 'Placeholder', 'ayecode-connect' ),
				'help_text'   => __( 'Help text from aui()->input().', 'ayecode-connect' ),
			]
		);
		echo aui()->input(
			[
				'type'       => 'text',
				'id'         => 'aui-preview-aui-floating',
				'name'       => 'aui_preview_floating',
				'label'      => __( 'Floating label', 'ayecode-connect' ),
				'label_type' => 'floating',
			]
		);
		echo aui()->input(
			[
				'type'       => 'text',
				'id'         => 'aui-preview-aui-horizontal',
				'name'       => 'aui_preview_horizontal',
				'label'      => __( 'Horizontal', 'ayecode-connect' ),
				'label_type' => 'horizontal',
			]
		);
		echo aui()->input(
			[
				'type'       => 'password',
				'id'         => 'aui-preview-aui-password',
				'name'       => 'aui_preview_password',
				'label'      => __( 'Password with show/hide', 'ayecode-connect' ),
				'label_type' => 'top',
				'value'      => 'example-password',
			]
		);
		echo aui()->input(
			[
				'type'       => 'password',
				'id'         => 'aui-preview-aui-password-floating',
				'name'       => 'aui_preview_password_floating',
				'label'      => __( 'Password, floating label', 'ayecode-connect' ),
				'label_type' => 'floating',
			]
		);
		echo aui()->input(
			[
				'type'             => 'text',
				'id'               => 'aui-preview-aui-group',
				'name'             => 'aui_preview_group',
				'label'            => __( 'Input group', 'ayecode-connect' ),
				'label_type'       => 'top',
				'input_group_left' => '$',
				'input_group_right' => '.00',
			]
		);
		echo aui()->input(
			[
				'type'       => 'checkbox',
				'id'         => 'aui-preview-aui-checkbox',
				'name'       => 'aui_preview_checkbox',
				'label'      => __( 'aui() checkbox', 'ayecode-connect' ),
				'value'      => '1',
				'checked'    => true,
			]
		);
		echo aui()->input(
			[
				'type'    => 'checkbox',
				'id'      => 'aui-preview-aui-switch',
				'name'    => 'aui_preview_switch',
				'label'   => __( 'aui() switch', 'ayecode-connect' ),
				'value'   => '1',
				'switch'  => true,
				'checked' => true,
			]
		);
		echo aui()->radio(
			[
				'id'      => 'aui-preview-aui-radio',
				'name'    => 'aui_preview_radio',
				'label'   => __( 'aui() radio', 'ayecode-connect' ),
				'options' => [
					'one' => __( 'One', 'ayecode-connect' ),
					'two' => __( 'Two', 'ayecode-connect' ),
				],
				'value'   => 'one',
				'inline'  => true,
			]
		);
		echo aui()->select(
			[
				'id'         => 'aui-preview-aui-select',
				'name'       => 'aui_preview_select',
				'label'      => __( 'aui() select', 'ayecode-connect' ),
				'label_type' => 'top',
				'options'    => [
					''    => __( 'Choose one', 'ayecode-connect' ),
					'one' => __( 'One', 'ayecode-connect' ),
					'two' => __( 'Two', 'ayecode-connect' ),
				],
			]
		);
		echo aui()->textarea(
			[
				'id'         => 'aui-preview-aui-textarea',
				'name'       => 'aui_preview_textarea',
				'label'      => __( 'aui() textarea', 'ayecode-connect' ),
				'label_type' => 'top',
				'rows'       => 3,
			]
		);
		?>
	</div>

	<div class="col-lg-6">
		<h3 class="h5"><?php esc_html_e( 'Alerts', 'ayecode-connect' ); ?></h3>
		<?php
		echo aui()->alert(
			[
				'type'    => 'info',
				'heading' => __( 'Heading', 'ayecode-connect' ),
				'content' => __( 'aui()->alert() with a heading and an icon.', 'ayecode-connect' ),
				'icon'    => true,
			]
		);
		echo aui()->alert(
			[
				'type'        => 'warning',
				'content'     => __( 'Dismissible aui() alert.', 'ayecode-connect' ),
				'dismissible' => true,
			]
		);
		?>

		<h3 class="h5"><?php esc_html_e( 'Buttons and badges', 'ayecode-connect' ); ?></h3>
		<div class="d-flex flex-wrap align-items-center gap-2 mb-4">
			<?php
			echo aui()->button(
				[
					'type'    => 'button',
					'content' => __( 'aui() button', 'ayecode-connect' ),
					'icon'    => 'fa-solid fa-check',
				]
			);
			echo aui()->button(
				[
					'type'          => 'button',
					'class'         => 'btn btn-outline-primary',
					'content'       => __( 'Hover me', 'ayecode-connect' ),
					'hover_content' => __( 'Hovered', 'ayecode-connect' ),
				]
			);
			echo aui()->badge(
				[
					'content' => __( 'aui() badge', 'ayecode-connect' ),
					'class'   => 'badge text-bg-success',
				]
			);
			?>
		</div>

		<h3 class="h5"><?php esc_html_e( 'Dropdown', 'ayecode-connect' ); ?></h3>
		<div class="mb-4">
			<?php
			echo aui()->dropdown(
				[
					'content'        => __( 'aui() dropdown', 'ayecode-connect' ),
					'dropdown_items' => [
						[
							'render'  => 'button',
							'class'   => 'dropdown-item',
							'content' => __( 'First item', 'ayecode-connect' ),
						],
						[
							'render'  => 'button',
							'class'   => 'dropdown-item',
							'content' => __( 'Second item', 'ayecode-connect' ),
						],
					],
				]
			);
			?>
		</div>

		<h3 class="h5"><?php esc_html_e( 'Icon text', 'ayecode-connect' ); ?></h3>
		<div class="mb-4">
			<div>
				<?php
				echo aui()->icon_text(
					[
						'icon' => 'fa-solid fa-location-dot',
						'text' => __( '123 Example Street', 'ayecode-connect' ),
					]
				);
				?>
			</div>
			<div>
				<?php
				echo aui()->icon_text(
					[
						'icon'  => 'fa-solid fa-phone',
						'text'  => '+1 555 0100',
						'color' => 'primary',
					]
				);
				?>
			</div>
		</div>

		<h3 class="h5"><?php esc_html_e( 'Pagination', 'ayecode-connect' ); ?></h3>
		<?php
		echo aui()->pagination(
			[
				'total'   => 8,
				'current' => 3,
				'base'    => '#aui-preview-page-%#%',
				'format'  => '',
			]
		);
		echo aui()->pagination(
			[
				'total'         => 8,
				'current'       => 3,
				'base'          => '#aui-preview-page-%#%',
				'format'        => '',
				'rounded_style' => true,
				'class'         => 'mt-3',
			]
		);
		?>
	</div>
</div>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
