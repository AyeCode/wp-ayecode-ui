<?php
/**
 * AyeCode UI Input Component
 *
 * Renders Bootstrap input, textarea, select, and radio components.
 *
 * @package AyeCode\UI\Components
 */

namespace AyeCode\UI\Components;

use AyeCode\UI\SettingsOrchestrator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Input class.
 *
 * Renders Bootstrap 5 form fields: text inputs, textareas, selects, and radio groups.
 */
class Input {

	/**
	 * Whether choices/select2 has been initialised on this page load.
	 *
	 * @var bool
	 */
	private static bool $has_select2 = false;

	/**
	 * Build and return an input field.
	 *
	 * @param array $args Component arguments.
	 * @return string
	 */
	public static function input( array $args = [] ): string {
		$defaults = [
			'type'                     => 'text',
			'name'                     => '',
			'class'                    => '',
			'wrap_class'               => '',
			'id'                       => '',
			'placeholder'              => '',
			'title'                    => '',
			'value'                    => '',
			'required'                 => false,
			'size'                     => '',
			'clear_icon'               => '',
			'with_hidden'              => false,
			'label'                    => '',
			'label_after'              => false,
			'label_class'              => '',
			'label_col'                => '2',
			'label_type'               => '',
			'label_force_left'         => false,
			'help_text'                => '',
			'validation_text'          => '',
			'validation_pattern'       => '',
			'no_wrap'                  => false,
			'input_group_right'        => '',
			'input_group_left'         => '',
			'input_group_right_inside' => false,
			'input_group_left_inside'  => false,
			'form_group_class'         => '',
			'step'                     => '',
			'switch'                   => false,
			'checked'                  => false,
			'password_toggle'          => true,
			'element_require'          => '',
			'extra_attributes'         => [],
			'wrap_attributes'          => [],
		];

		$args   = wp_parse_args( $args, $defaults );
		$output = '';

		if ( ! empty( $args['type'] ) ) {
			$args['label_type'] = $args['label_type'] === 'hidden' ? '' : $args['label_type'];

			$type        = sanitize_html_class( $args['type'] );
			$help_text   = '';
			$label       = '';
			$label_after = $args['label_after'];
			$label_args  = [
				'title'      => $args['label'],
				'for'        => $args['id'],
				'class'      => $args['label_class'] . ' ',
				'label_type' => $args['label_type'],
				'label_col'  => $args['label_col'],
			];

			if ( $args['label_type'] === 'floating' && $type !== 'checkbox' ) {
				$label_after         = true;
				$args['placeholder'] = ' ';
			}

			$size = '';
			if ( $args['size'] === 'lg' || $args['size'] === 'large' ) {
				$size = 'lg';
				$args['class'] .= ' form-control-lg';
			} elseif ( $args['size'] === 'sm' || $args['size'] === 'small' ) {
				$size = 'sm';
				$args['class'] .= ' form-control-sm';
			}

			$clear_function = 'jQuery(this).parent().parent().find(\'input\').val(\'\');';

			if ( $type === 'file' ) {
				$label_after    = true;
				$args['class'] .= ' custom-file-input ';
			} elseif ( $type === 'checkbox' ) {
				$label_after    = true;
				$args['class'] .= ' form-check-input c-pointer ';
			} elseif ( $type === 'datepicker' || $type === 'timepicker' ) {
				$type           = 'text';
				$args['class'] .= ' bg-initial ';
				$clear_function .= "jQuery(this).parent().parent().find('input[name=\'" . esc_attr( $args['name'] ) . "\']').trigger('change');";

				$args['extra_attributes']['data-aui-init'] = 'flatpickr';

				$disable_mobile_attr = $args['extra_attributes']['data-disable-mobile'] ?? 'true';
				$disable_mobile_attr = apply_filters( 'aui_flatpickr_disable_disable_mobile_attr', $disable_mobile_attr, $args );
				$args['extra_attributes']['data-disable-mobile'] = $disable_mobile_attr;

				if ( $args['input_group_right'] === '' && $args['clear_icon'] !== false ) {
					$args['input_group_right_inside'] = true;
					$args['clear_icon']               = true;
				}

				SettingsOrchestrator::instance()->enqueue_flatpickr();
			} elseif ( $type === 'iconpicker' ) {
				$type = 'text';

				$args['extra_attributes']['data-aui-init'] = 'iconpicker';
				$args['extra_attributes']['data-placement'] = 'bottomRight';
				$args['input_group_right'] = '<span class="input-group-addon input-group-text c-pointer"></span>';

				SettingsOrchestrator::instance()->enqueue_iconpicker();
			}

			if ( $type === 'checkbox' && ( ( ! empty( $args['name'] ) && strpos( $args['name'], '[' ) === false ) || ! empty( $args['with_hidden'] ) ) ) {
				$output .= '<input type="hidden" name="' . esc_attr( $args['name'] ) . '" value="0" />';
			}

			if ( $args['input_group_right'] === '' && $args['clear_icon'] ) {
				$font_size             = $size === 'sm' ? '1.3' : ( $size === 'lg' ? '1.65' : '1.5' );
				$args['input_group_right_inside'] = true;
				$args['input_group_right'] = '<span class="input-group-text aui-clear-input c-pointer bg-initial border-0 px-2 d-none h-100 py-0" onclick="' . $clear_function . '"><span style="font-size: ' . $font_size . 'rem" aria-hidden="true" class="btn-close"></span></span>';
			}

			$output .= '<input type="' . $type . '" ';

			if ( ! empty( $args['name'] ) ) {
				$output .= ' name="' . esc_attr( $args['name'] ) . '" ';
			}
			if ( ! empty( $args['id'] ) ) {
				$output .= ' id="' . sanitize_html_class( $args['id'] ) . '" ';
			}
			if ( isset( $args['placeholder'] ) && '' !== $args['placeholder'] ) {
				$output .= ' placeholder="' . esc_attr( $args['placeholder'] ) . '" ';
			}
			if ( ! empty( $args['title'] ) ) {
				$output .= ' title="' . esc_attr( $args['title'] ) . '" ';
			}
			if ( ! empty( $args['value'] ) ) {
				$output .= Helper::value( $args['value'] );
			}
			if ( ( $type === 'checkbox' || $type === 'radio' ) && $args['checked'] ) {
				$output .= ' checked ';
			}
			if ( ! empty( $args['validation_text'] ) ) {
				$output .= ' oninvalid="setCustomValidity(\'' . esc_attr( addslashes( $args['validation_text'] ) ) . '\')" ';
				$output .= ' onchange="try{setCustomValidity(\'\')}catch(e){}" ';
			}
			if ( ! empty( $args['validation_pattern'] ) ) {
				$output .= ' pattern="' . esc_attr( $args['validation_pattern'] ) . '" ';
			}
			if ( ! empty( $args['step'] ) ) {
				$output .= ' step="' . $args['step'] . '" ';
			}
			if ( ! empty( $args['required'] ) ) {
				$output .= ' required ';
			}

			$class   = ! empty( $args['class'] ) ? Helper::esc_classes( $args['class'] ) : '';
			$output .= $type === 'checkbox' ? ' class="' . $class . '" ' : ' class="form-control ' . $class . '" ';

			$output .= Helper::data_attributes( $args );

			if ( ! empty( $args['extra_attributes'] ) ) {
				$output .= Helper::extra_attributes( $args['extra_attributes'] );
			}

			$output .= ' >';

			if ( ! empty( $args['help_text'] ) ) {
				$help_text = Helper::help_text( $args['help_text'] );
			}

			if ( ! empty( $args['label'] ) ) {
				$label_base_class = '';
				if ( $type === 'file' ) {
					$label_base_class = ' custom-file-label';
				} elseif ( $type === 'checkbox' ) {
					if ( ! empty( $args['label_force_left'] ) ) {
						$label_args['title'] = wp_kses_post( $args['help_text'] );
						$help_text = '';
						$args['wrap_class'] .= ' align-items-center ';
					}
					$label_base_class = ' form-check-label';
				}
				$label_args['class'] .= $label_base_class;
				$temp_label_args = $label_args;
				if ( ! empty( $args['label_force_left'] ) ) {
					$temp_label_args['class'] = $label_base_class . ' text-muted';
				}
				$label = self::label( $temp_label_args, $type );
			}

			if ( $label_after ) {
				$output .= $label . $help_text;
			}

			if ( $type === 'file' ) {
				$output = self::wrap( [
					'content' => $output,
					'class'   => 'mb-3 custom-file',
				] );
			} elseif ( $type === 'checkbox' ) {
				$label_args['title'] = $args['label'];
				$label_col           = Helper::get_column_class( $args['label_col'], 'label' );
				$label               = ! empty( $args['label_force_left'] ) ? self::label( $label_args, 'cb' ) : '<div class="' . $label_col . ' col-form-label"></div>';
				$switch_size_class   = $args['switch'] && ! is_bool( $args['switch'] ) ? ' custom-switch-' . esc_attr( $args['switch'] ) : '';
				$wrap_class          = $args['switch'] ? 'form-check form-switch' . $switch_size_class : 'form-check';

				if ( ! empty( $args['label_force_left'] ) ) {
					$label = str_replace( 'form-check-label', '', self::label( $label_args, 'cb' ) );
				}
				$output = self::wrap( [
					'content' => $output,
					'class'   => $wrap_class,
				] );

				if ( $args['label_type'] === 'horizontal' ) {
					$input_col = Helper::get_column_class( $args['label_col'], 'input' );
					$output    = $label . '<div class="' . $input_col . '">' . $output . '</div>';
				}
			} elseif ( $type === 'password' && $args['password_toggle'] && ! $args['input_group_right'] ) {
				$args['input_group_right'] = '<span class="input-group-text c-pointer px-3"
onclick="var $el = jQuery(this).find(\'i\');$el.toggleClass(\'fa-eye fa-eye-slash\');
var $eli = jQuery(this).parent().parent().find(\'input\');
if($el.hasClass(\'fa-eye\'))
{$eli.attr(\'type\',\'text\');}
else{$eli.attr(\'type\',\'password\');}"
><i class="far fa-fw fa-eye-slash"></i></span>';
			}

			if ( $args['input_group_left'] || $args['input_group_right'] ) {
				$w100       = strpos( $args['class'], 'w-100' ) !== false ? ' w-100' : '';
				$group_size = $size === 'lg' ? ' input-group-lg' : '';
				$group_size = ! $group_size && $size === 'sm' ? ' input-group-sm' : $group_size;

				if ( $args['input_group_left'] ) {
					$output = self::wrap( [
						'content'                 => $output,
						'class'                   => $args['input_group_left_inside'] ? 'input-group-inside position-relative' . $w100 . $group_size : 'input-group' . $group_size,
						'input_group_left'        => $args['input_group_left'],
						'input_group_left_inside' => $args['input_group_left_inside'],
					] );
				}
				if ( $args['input_group_right'] ) {
					$output = self::wrap( [
						'content'                  => $output,
						'class'                    => $args['input_group_right_inside'] ? 'input-group-inside position-relative' . $w100 . $group_size : 'input-group' . $group_size,
						'input_group_right'        => $args['input_group_right'],
						'input_group_right_inside' => $args['input_group_right_inside'],
					] );
				}
			}

			if ( ! $label_after ) {
				$output .= $help_text;
			}

			if ( $args['label_type'] === 'horizontal' && $type !== 'checkbox' ) {
				$output = self::wrap( [
					'content' => $output,
					'class'   => Helper::get_column_class( $args['label_col'], 'input' ),
				] );
			}

			if ( ! $label_after ) {
				$output = $label . $output;
			}

			if ( ! $args['no_wrap'] ) {
				$fg_class         = ! empty( $args['form_group_class'] ) ? esc_attr( $args['form_group_class'] ) : 'mb-3';
				$form_group_class = $args['label_type'] === 'floating' && $type !== 'checkbox' ? 'form-floating' : $fg_class;
				$wrap_class       = $args['label_type'] === 'horizontal' ? $form_group_class . ' row' : $form_group_class;
				$wrap_class       = ! empty( $args['wrap_class'] ) ? $wrap_class . ' ' . $args['wrap_class'] : $wrap_class;
				$output           = self::wrap( [
					'content'         => $output,
					'class'           => $wrap_class,
					'element_require' => $args['element_require'],
					'argument_id'     => $args['id'],
					'wrap_attributes' => $args['wrap_attributes'],
				] );
			}
		}

		return $output;
	}

	/**
	 * Build and return a label element.
	 *
	 * @param array  $args Component arguments.
	 * @param string $type Field type context.
	 * @return string
	 */
	public static function label( array $args = [], string $type = '' ): string {
		$defaults = [
			'title'      => '',
			'for'        => '',
			'class'      => '',
			'label_type' => '',
			'label_col'  => '',
		];

		$args   = wp_parse_args( $args, $defaults );
		$output = '';

		if ( $args['title'] ) {
			if ( $type === 'file' || $type === 'checkbox' || $type === 'radio' || ! empty( $args['label_type'] ) ) {
				$class = $args['class'];
			} else {
				$class = 'visually-hidden ' . $args['class'];
			}

			if ( $args['label_type'] === 'horizontal' && $type !== 'checkbox' ) {
				$class .= ' ' . Helper::get_column_class( $args['label_col'], 'label' ) . ' col-form-label ' . $type;
			}

			$class .= ' form-label';

			$output .= '<label ';
			if ( ! empty( $args['for'] ) ) {
				$output .= ' for="' . esc_attr( $args['for'] ) . '" ';
			}
			$class   = $class ? Helper::esc_classes( $class ) : '';
			$output .= ' class="' . $class . '" >';

			if ( ! empty( $args['title'] ) ) {
				$output .= wp_kses_post( $args['title'] );
			}

			$output .= '</label>';
		}

		return $output;
	}

	/**
	 * Wrap content in an HTML element.
	 *
	 * @param array $args Wrapper arguments.
	 * @return string
	 */
	public static function wrap( array $args = [] ): string {
		$defaults = [
			'type'                     => 'div',
			'class'                    => 'mb-3',
			'content'                  => '',
			'input_group_left'         => '',
			'input_group_right'        => '',
			'input_group_left_inside'  => false,
			'input_group_right_inside' => false,
			'element_require'          => '',
			'argument_id'              => '',
			'wrap_attributes'          => [],
		];

		$args   = wp_parse_args( $args, $defaults );
		$output = '';

		if ( $args['type'] ) {
			$output .= '<' . sanitize_html_class( $args['type'] );

			if ( ! empty( $args['element_require'] ) ) {
				$output .= Helper::element_require( $args['element_require'] );
				$args['class'] .= ' aui-conditional-field';
			}
			if ( ! empty( $args['argument_id'] ) ) {
				$output .= ' data-argument="' . esc_attr( $args['argument_id'] ) . '"';
			}

			$class   = ! empty( $args['class'] ) ? Helper::esc_classes( $args['class'] ) : '';
			$output .= ' class="' . $class . '" ';

			if ( ! empty( $args['wrap_attributes'] ) ) {
				$output .= Helper::extra_attributes( $args['wrap_attributes'] );
			}

			$output .= ' >';

			if ( ! empty( $args['input_group_left'] ) ) {
				$input_group_left = strpos( $args['input_group_left'], '<' ) !== false ? $args['input_group_left'] : '<span class="input-group-text">' . $args['input_group_left'] . '</span>';
				$output          .= $input_group_left;
			}

			$output .= $args['content'];

			if ( ! empty( $args['input_group_right'] ) ) {
				$input_group_right = strpos( $args['input_group_right'], '<' ) !== false ? $args['input_group_right'] : '<span class="input-group-text">' . $args['input_group_right'] . '</span>';
				$output           .= str_replace( 'input-group-text', 'input-group-text top-0 end-0', $input_group_right );
			}

			$output .= '</' . sanitize_html_class( $args['type'] ) . '>';
		} else {
			$output = $args['content'];
		}

		return $output;
	}

	/**
	 * Build and return a textarea field.
	 *
	 * @param array $args Component arguments.
	 * @return string
	 */
	public static function textarea( array $args = [] ): string {
		$defaults = [
			'name'                     => '',
			'class'                    => '',
			'wrap_class'               => '',
			'id'                       => '',
			'placeholder'              => '',
			'title'                    => '',
			'value'                    => '',
			'required'                 => false,
			'label'                    => '',
			'label_after'              => false,
			'label_class'              => '',
			'label_type'               => '',
			'label_col'                => '',
			'input_group_right'        => '',
			'input_group_left'         => '',
			'input_group_right_inside' => false,
			'form_group_class'         => '',
			'help_text'                => '',
			'validation_text'          => '',
			'validation_pattern'       => '',
			'no_wrap'                  => false,
			'rows'                     => '',
			'wysiwyg'                  => false,
			'allow_tags'               => false,
			'element_require'          => '',
			'extra_attributes'         => [],
			'wrap_attributes'          => [],
		];

		$args   = wp_parse_args( $args, $defaults );
		$output = '';
		$label  = '';

		$args['label_type'] = $args['label_type'] === 'hidden' ? '' : $args['label_type'];

		if ( $args['label_type'] === 'floating' && ! empty( $args['wysiwyg'] ) ) {
			$args['label_type'] = 'top';
		}

		$label_after = $args['label_after'];

		if ( $args['label_type'] === 'floating' && empty( $args['wysiwyg'] ) ) {
			$label_after         = true;
			$args['placeholder'] = ' ';
		}

		if ( ! empty( $args['label'] ) && is_array( $args['label'] ) ) {
			// No-op: complex label handled elsewhere.
		} elseif ( ! empty( $args['label'] ) && ! $label_after ) {
			$label_args = [
				'title'      => $args['label'],
				'for'        => $args['id'],
				'class'      => $args['label_class'] . ' ',
				'label_type' => $args['label_type'],
				'label_col'  => $args['label_col'],
			];
			$label .= self::label( $label_args );
		}

		if ( $args['label_type'] === 'horizontal' ) {
			$input_col  = Helper::get_column_class( $args['label_col'], 'input' );
			$label     .= '<div class="' . $input_col . '">';
		}

		if ( ! empty( $args['wysiwyg'] ) ) {
			ob_start();
			$content   = $args['value'];
			$editor_id = ! empty( $args['id'] ) ? sanitize_html_class( $args['id'] ) : 'wp_editor';
			$settings  = [
				'textarea_rows' => ! empty( absint( $args['rows'] ) ) ? absint( $args['rows'] ) : 4,
				'quicktags'     => false,
				'media_buttons' => false,
				'editor_class'  => 'form-control',
				'textarea_name' => ! empty( $args['name'] ) ? sanitize_html_class( $args['name'] ) : sanitize_html_class( $args['id'] ),
				'teeny'         => true,
			];
			if ( is_array( $args['wysiwyg'] ) ) {
				$settings = wp_parse_args( $args['wysiwyg'], $settings );
			}
			wp_editor( $content, $editor_id, $settings );
			$output .= ob_get_clean();
		} else {
			$output .= '<textarea ';

			if ( ! empty( $args['name'] ) ) {
				$output .= ' name="' . esc_attr( $args['name'] ) . '" ';
			}
			if ( ! empty( $args['id'] ) ) {
				$output .= ' id="' . sanitize_html_class( $args['id'] ) . '" ';
			}
			if ( isset( $args['placeholder'] ) && '' !== $args['placeholder'] ) {
				$output .= ' placeholder="' . esc_attr( $args['placeholder'] ) . '" ';
			}
			if ( ! empty( $args['title'] ) ) {
				$output .= ' title="' . esc_attr( $args['title'] ) . '" ';
			}
			if ( ! empty( $args['validation_text'] ) ) {
				$output .= ' oninvalid="setCustomValidity(\'' . esc_attr( addslashes( $args['validation_text'] ) ) . '\')" ';
				$output .= ' onchange="try{setCustomValidity(\'\')}catch(e){}" ';
			}
			if ( ! empty( $args['validation_pattern'] ) ) {
				$output .= ' pattern="' . esc_attr( $args['validation_pattern'] ) . '" ';
			}
			if ( ! empty( $args['required'] ) ) {
				$output .= ' required ';
			}
			if ( ! empty( $args['rows'] ) ) {
				$output .= ' rows="' . absint( $args['rows'] ) . '" ';
			}

			$class   = ! empty( $args['class'] ) ? $args['class'] : '';
			$output .= ' class="form-control ' . $class . '" ';

			if ( ! empty( $args['extra_attributes'] ) ) {
				$output .= Helper::extra_attributes( $args['extra_attributes'] );
			}

			$output .= ' >';

			if ( ! empty( $args['value'] ) ) {
				if ( ! empty( $args['allow_tags'] ) ) {
					$output .= Helper::sanitize_html_field( $args['value'], $args );
				} else {
					$output .= Helper::sanitize_textarea_field( $args['value'] );
				}
			}

			$output .= '</textarea>';

			if ( $args['input_group_left'] || $args['input_group_right'] ) {
				$w100 = strpos( $args['class'], 'w-100' ) !== false ? ' w-100' : '';
				if ( $args['input_group_left'] ) {
					$output = self::wrap( [
						'content'                 => $output,
						'class'                   => $args['input_group_left_inside'] ? 'input-group-inside position-relative' . $w100 : 'input-group',
						'input_group_left'        => $args['input_group_left'],
						'input_group_left_inside' => $args['input_group_left_inside'],
					] );
				}
				if ( $args['input_group_right'] ) {
					$output = self::wrap( [
						'content'                  => $output,
						'class'                    => $args['input_group_right_inside'] ? 'input-group-inside position-relative' . $w100 : 'input-group',
						'input_group_right'        => $args['input_group_right'],
						'input_group_right_inside' => $args['input_group_right_inside'],
					] );
				}
			}
		}

		if ( ! empty( $args['label'] ) && $label_after ) {
			$label_args = [
				'title'      => $args['label'],
				'for'        => $args['id'],
				'class'      => $args['label_class'] . ' ',
				'label_type' => $args['label_type'],
				'label_col'  => $args['label_col'],
			];
			$output .= self::label( $label_args );
		}

		if ( ! empty( $args['help_text'] ) ) {
			$output .= Helper::help_text( $args['help_text'] );
		}

		if ( ! $label_after ) {
			$output = $label . $output;
		}

		if ( $args['label_type'] === 'horizontal' ) {
			$output .= '</div>';
		}

		if ( ! $args['no_wrap'] ) {
			$fg_class         = ! empty( $args['form_group_class'] ) ? esc_attr( $args['form_group_class'] ) : 'mb-3';
			$form_group_class = $args['label_type'] === 'floating' ? 'form-floating' : $fg_class;
			$wrap_class       = $args['label_type'] === 'horizontal' ? $form_group_class . ' row' : $form_group_class;
			$wrap_class       = ! empty( $args['wrap_class'] ) ? $wrap_class . ' ' . $args['wrap_class'] : $wrap_class;
			$output           = self::wrap( [
				'content'         => $output,
				'class'           => $wrap_class,
				'element_require' => $args['element_require'],
				'argument_id'     => $args['id'],
				'wrap_attributes' => $args['wrap_attributes'],
			] );
		}

		return $output;
	}

	/**
	 * Build and return a select field.
	 *
	 * @param array $args Component arguments.
	 * @return string
	 */
	public static function select( array $args = [] ): string {
		$defaults = [
			'class'                    => '',
			'wrap_class'               => '',
			'id'                       => '',
			'title'                    => '',
			'value'                    => '',
			'required'                 => false,
			'label'                    => '',
			'label_after'              => false,
			'label_type'               => '',
			'label_col'                => '',
			'label_class'              => '',
			'help_text'                => '',
			'placeholder'              => '',
			'options'                  => [],
			'icon'                     => '',
			'multiple'                 => false,
			'select2'                  => false,
			'no_wrap'                  => false,
			'input_group_right'        => '',
			'input_group_left'         => '',
			'input_group_right_inside' => false,
			'input_group_left_inside'  => false,
			'form_group_class'         => '',
			'element_require'          => '',
			'extra_attributes'         => [],
			'wrap_attributes'          => [],
		];

		$args = wp_parse_args( $args, $defaults );

		if ( $args['label_type'] === 'floating' ) {
			$args['label_type'] = 'hidden';
		}
		$args['label_type'] = $args['label_type'] === 'hidden' ? '' : $args['label_type'];

		$label_after = $args['label_after'];

		if ( $args['label_type'] === 'floating' ) {
			$label_after         = true;
			$args['placeholder'] = ' ';
		}

		$is_select2 = false;
		if ( ! empty( $args['select2'] ) ) {
			$args['class'] .= ' aui-select2';
			$is_select2     = true;
		} elseif ( strpos( $args['class'], 'aui-select2' ) !== false ) {
			$is_select2 = true;
		}

		if ( $is_select2 && ! self::$has_select2 ) {
			self::$has_select2 = true;
			$conditional_select2 = apply_filters( 'aui_is_conditional_select2', true );

			if ( ! \AyeCode\UI\AssetManager::is_select2_enqueued() && $conditional_select2 === true ) {
				\AyeCode\UI\AssetManager::mark_select2_enqueued();
				SettingsOrchestrator::instance()->enqueue_select2();
			}
		}

		if ( ! empty( $args['select2'] ) && $args['select2'] === 'tags' ) {
			$args['data-tags']             = 'true';
			$args['data-token-separators'] = "[',']";
			$args['multiple']              = true;
		}

		if ( $is_select2 && isset( $args['placeholder'] ) && '' !== $args['placeholder'] && empty( $args['data-placeholder'] ) ) {
			$args['data-placeholder'] = esc_attr( $args['placeholder'] );
			$args['data-allow-clear'] = isset( $args['data-allow-clear'] ) ? (bool) $args['data-allow-clear'] : true;
		}

		$output = '';

		if ( ! empty( $args['multiple'] ) && ! empty( $args['name'] ) ) {
			$output .= '<input type="hidden" ' . Helper::name( $args['name'] ) . ' value="" data-ignore-rule/>';
		}

		$output .= '<select ';

		if ( $is_select2 && ! ( $args['input_group_left'] || $args['input_group_right'] ) ) {
			$output .= " style='width:100%;' ";
		}

		if ( ! empty( $args['element_require'] ) ) {
			$output .= Helper::element_require( $args['element_require'] );
			$args['class'] .= ' aui-conditional-field';
		}

		$class   = ! empty( $args['class'] ) ? $args['class'] : '';
		$output .= Helper::class_attr( 'form-select ' . $class );

		if ( ! empty( $args['name'] ) ) {
			$output .= Helper::name( $args['name'], $args['multiple'] );
		}
		if ( ! empty( $args['id'] ) ) {
			$output .= Helper::id( $args['id'] );
		}
		if ( ! empty( $args['title'] ) ) {
			$output .= Helper::title( $args['title'] );
		}

		$output .= Helper::data_attributes( $args );
		$output .= Helper::aria_attributes( $args );

		if ( ! empty( $args['extra_attributes'] ) ) {
			$output .= Helper::extra_attributes( $args['extra_attributes'] );
		}
		if ( ! empty( $args['required'] ) ) {
			$output .= ' required ';
		}
		if ( ! empty( $args['multiple'] ) ) {
			$output .= ' multiple ';
		}

		$output .= ' >';

		if ( isset( $args['placeholder'] ) && '' !== $args['placeholder'] && ! $is_select2 ) {
			$output .= '<option value="" disabled selected hidden>' . esc_attr( $args['placeholder'] ) . '</option>';
		} elseif ( $is_select2 && ! empty( $args['placeholder'] ) ) {
			$output .= '<option></option>';
		}

		if ( ! empty( $args['options'] ) ) {
			if ( ! is_array( $args['options'] ) ) {
				$output .= $args['options'];
			} else {
				foreach ( $args['options'] as $val => $name ) {
					$selected = '';
					if ( is_array( $name ) ) {
						if ( isset( $name['optgroup'] ) && ( $name['optgroup'] === 'start' || $name['optgroup'] === 'end' ) ) {
							$option_label = $name['label'] ?? '';
							$output      .= $name['optgroup'] === 'start' ? '<optgroup label="' . esc_attr( $option_label ) . '">' : '</optgroup>';
						} else {
							$option_label     = $name['label'] ?? '';
							$option_value     = $name['value'] ?? '';
							$extra_attributes = ! empty( $name['extra_attributes'] ) ? Helper::extra_attributes( $name['extra_attributes'] ) : '';

							if ( ! empty( $args['multiple'] ) && ! empty( $args['value'] ) && is_array( $args['value'] ) ) {
								$selected = in_array( $option_value, stripslashes_deep( $args['value'] ) ) ? 'selected' : '';
							} elseif ( ! empty( $args['value'] ) ) {
								$selected = selected( $option_value, stripslashes_deep( $args['value'] ), false );
							} elseif ( empty( $args['value'] ) && $args['value'] === $option_value ) {
								$selected = selected( $option_value, $args['value'], false );
							}

							$output .= '<option value="' . esc_attr( $option_value ) . '" ' . $selected . ' ' . $extra_attributes . '>' . $option_label . '</option>';
						}
					} else {
						if ( ! empty( $args['value'] ) ) {
							if ( is_array( $args['value'] ) ) {
								$selected = in_array( $val, $args['value'] ) ? 'selected="selected"' : '';
							} else {
								$selected = selected( $args['value'], $val, false );
							}
						} elseif ( $args['value'] === $val ) {
							$selected = selected( $args['value'], $val, false );
						}
						$output .= '<option value="' . esc_attr( $val ) . '" ' . $selected . '>' . esc_attr( $name ) . '</option>';
					}
				}
			}
		}

		$output .= '</select>';

		$label     = '';
		$help_text = '';

		if ( ! empty( $args['label'] ) && is_array( $args['label'] ) ) {
			// No-op.
		} elseif ( ! empty( $args['label'] ) && ! $label_after ) {
			$label_args = [
				'title'      => $args['label'],
				'for'        => $args['id'],
				'class'      => $args['label_class'] . ' ',
				'label_type' => $args['label_type'],
				'label_col'  => $args['label_col'],
			];
			$label = self::label( $label_args );
		}

		if ( ! empty( $args['help_text'] ) ) {
			$help_text = Helper::help_text( $args['help_text'] );
		}

		if ( $args['input_group_left'] || $args['input_group_right'] ) {
			$w100 = strpos( $args['class'], 'w-100' ) !== false ? ' w-100' : '';
			if ( $args['input_group_left'] ) {
				$output = self::wrap( [
					'content'                 => $output,
					'class'                   => $args['input_group_left_inside'] ? 'input-group-inside position-relative' . $w100 : 'input-group',
					'input_group_left'        => $args['input_group_left'],
					'input_group_left_inside' => $args['input_group_left_inside'],
				] );
			}
			if ( $args['input_group_right'] ) {
				$output = self::wrap( [
					'content'                  => $output,
					'class'                    => $args['input_group_right_inside'] ? 'input-group-inside position-relative' . $w100 : 'input-group',
					'input_group_right'        => $args['input_group_right'],
					'input_group_right_inside' => $args['input_group_right_inside'],
				] );
			}
		}

		if ( ! $label_after ) {
			$output .= $help_text;
		}

		if ( $args['label_type'] === 'horizontal' ) {
			$output = self::wrap( [
				'content' => $output,
				'class'   => Helper::get_column_class( $args['label_col'], 'input' ),
			] );
		}

		if ( ! $label_after ) {
			$output = $label . $output;
		}

		if ( ! $args['no_wrap'] ) {
			$fg_class   = ! empty( $args['form_group_class'] ) ? esc_attr( $args['form_group_class'] ) : 'mb-3';
			$wrap_class = $args['label_type'] === 'horizontal' ? $fg_class . ' row' : $fg_class;
			$wrap_class = ! empty( $args['wrap_class'] ) ? $wrap_class . ' ' . $args['wrap_class'] : $wrap_class;
			$output     = self::wrap( [
				'content'         => $output,
				'class'           => $wrap_class,
				'element_require' => $args['element_require'],
				'argument_id'     => $args['id'],
				'wrap_attributes' => $args['wrap_attributes'],
			] );
		}

		return $output;
	}

	/**
	 * Build and return a radio button group.
	 *
	 * @param array $args Component arguments.
	 * @return string
	 */
	public static function radio( array $args = [] ): string {
		$defaults = [
			'class'            => '',
			'wrap_class'       => '',
			'id'               => '',
			'title'            => '',
			'horizontal'       => false,
			'value'            => '',
			'label'            => '',
			'label_class'      => '',
			'label_type'       => '',
			'label_col'        => '',
			'help_text'        => '',
			'inline'           => true,
			'required'         => false,
			'options'          => [],
			'icon'             => '',
			'no_wrap'          => false,
			'element_require'  => '',
			'extra_attributes' => [],
			'wrap_attributes'  => [],
		];

		$args = wp_parse_args( $args, $defaults );

		if ( $args['label_type'] === 'floating' ) {
			$args['label_type'] = 'horizontal';
		}

		$label_args = [
			'title'      => $args['label'],
			'class'      => $args['label_class'] . ' pt-0 ',
			'label_type' => $args['label_type'],
			'label_col'  => $args['label_col'],
		];

		if ( $args['label_type'] === 'top' || $args['label_type'] === 'hidden' ) {
			$label_args['class'] .= 'd-block ';
			if ( $args['label_type'] === 'hidden' ) {
				$label_args['class'] .= 'visually-hidden ';
			}
		}

		$output = '';

		if ( ! empty( $args['label'] ) ) {
			$output .= self::label( $label_args, 'radio' );
		}

		if ( $args['label_type'] === 'horizontal' ) {
			$input_col  = Helper::get_column_class( $args['label_col'], 'input' );
			$output    .= '<div class="' . $input_col . '">';
		}

		if ( ! empty( $args['options'] ) ) {
			$count = 0;
			foreach ( $args['options'] as $value => $label ) {
				$option_args            = $args;
				$option_args['value']   = $value;
				$option_args['label']   = $label;
				$option_args['checked'] = $value == $args['value'];
				$output .= self::radio_option( $option_args, $count );
				$count++;
			}
		}

		$help_text  = ! empty( $args['help_text'] ) ? Helper::help_text( $args['help_text'] ) : '';
		$output    .= $help_text;

		if ( $args['label_type'] === 'horizontal' ) {
			$output .= '</div>';
		}

		$fg_class   = 'mb-3';
		$wrap_class = $args['label_type'] === 'horizontal' ? $fg_class . ' row' : $fg_class;
		$wrap_class = ! empty( $args['wrap_class'] ) ? $wrap_class . ' ' . $args['wrap_class'] : $wrap_class;
		$output     = self::wrap( [
			'content'         => $output,
			'class'           => $wrap_class,
			'element_require' => $args['element_require'],
			'argument_id'     => $args['id'],
			'wrap_attributes' => $args['wrap_attributes'],
		] );

		return $output;
	}

	/**
	 * Build and return a single radio option.
	 *
	 * @param array      $args  Component arguments.
	 * @param string|int $count Option index for unique IDs.
	 * @return string
	 */
	public static function radio_option( array $args = [], $count = '' ): string {
		$defaults = [
			'class'            => '',
			'id'               => '',
			'title'            => '',
			'value'            => '',
			'required'         => false,
			'inline'           => true,
			'label'            => '',
			'options'          => [],
			'icon'             => '',
			'no_wrap'          => false,
			'extra_attributes' => [],
		];

		$args   = wp_parse_args( $args, $defaults );
		$output = '';

		$output .= '<input type="radio"';
		$output .= ' class="form-check-input" ';

		if ( ! empty( $args['name'] ) ) {
			$output .= Helper::name( $args['name'] );
		}
		if ( ! empty( $args['id'] ) ) {
			$output .= Helper::id( $args['id'] . $count );
		}
		if ( ! empty( $args['title'] ) ) {
			$output .= Helper::title( $args['title'] );
		}
		if ( isset( $args['value'] ) ) {
			$output .= Helper::value( $args['value'] );
		}
		if ( $args['checked'] ) {
			$output .= ' checked ';
		}

		$output .= Helper::data_attributes( $args );
		$output .= Helper::aria_attributes( $args );

		if ( ! empty( $args['extra_attributes'] ) ) {
			$output .= Helper::extra_attributes( $args['extra_attributes'] );
		}
		if ( ! empty( $args['required'] ) ) {
			$output .= ' required ';
		}

		$output .= ' >';

		if ( ! empty( $args['label'] ) && is_array( $args['label'] ) ) {
			// No-op.
		} elseif ( ! empty( $args['label'] ) ) {
			$output .= self::label( [
				'title' => $args['label'],
				'for'   => $args['id'] . $count,
				'class' => 'form-check-label',
			], 'radio' );
		}

		if ( ! $args['no_wrap'] ) {
			$wrap_class = $args['inline'] ? 'form-check form-check-inline' : 'form-check';

			$uniq_class = 'fwrap';
			if ( ! empty( $args['name'] ) ) {
				$uniq_class .= '-' . $args['name'];
			} elseif ( ! empty( $args['id'] ) ) {
				$uniq_class .= '-' . $args['id'];
			}

			if ( isset( $args['value'] ) || $args['value'] !== '' ) {
				$uniq_class .= '-' . $args['value'];
			} else {
				$uniq_class .= '-' . $count;
			}
			$wrap_class .= ' ' . sanitize_html_class( $uniq_class );

			$output = self::wrap( [
				'content' => $output,
				'class'   => $wrap_class,
			] );
		}

		return $output;
	}
}
