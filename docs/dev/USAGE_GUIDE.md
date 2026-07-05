# AyeCode UI — PHP Component API

The AyeCode UI PHP API renders Bootstrap 5.3 components from server-side PHP. All components are accessible through the `aui()` singleton helper, which is available on any page after the plugin is loaded. Assets (CSS/JS) are enqueued automatically the first time a component renders.

## Contents

- [`render()`](#render) — batch-render multiple components
- [`alert()`](#alert) — dismissible alert banners
- [`input()`](#input) — text, checkbox, datepicker, and other inputs
- [`textarea()`](#textarea) — multi-line text and WYSIWYG fields
- [`select()`](#select) — dropdowns with optional Select2
- [`radio()`](#radio) — radio button groups
- [`button()`](#button) — buttons and anchor links
- [`badge()`](#badge) — inline badge labels
- [`dropdown()`](#dropdown) — button with a dropdown menu
- [`pagination()`](#pagination) — Bootstrap pagination from WP_Query
- [`wrap()`](#wrap) — generic wrapper element
- [Helper utilities](#helper-utilities) — attribute builders and sanitizers

---

## `render()`

Renders multiple components in sequence from a single array. Each item must include a `render` key whose value matches a public method name on `AUI` (e.g. `'alert'`, `'input'`). All other keys in the item are passed as `$args` to that method.

```php
$items = array(
    array(
        'render'  => 'alert',
        'type'    => 'success',
        'content' => __( 'Profile saved.', 'ayecode-connect' ),
    ),
    array(
        'render'  => 'button',
        'content' => __( 'Continue', 'ayecode-connect' ),
        'href'    => home_url( '/dashboard/' ),
    ),
);

echo aui()->render( $items );
```

| Parameter | Type | Default | Description |
|---|---|---|---|
| `$items` | array | `[]` | Array of component argument arrays, each with a `render` key. |
| `$echo` | bool | `false` | When `true`, echoes output instead of returning it. |

---

## `alert()`

Renders a Bootstrap alert with an optional icon, heading, footer, and dismiss button.

```php
echo aui()->alert( array(
    'type'    => 'warning',
    'content' => __( 'Your session will expire in 5 minutes.', 'ayecode-connect' ),
) );
```

Dismissible alert with a custom heading:

```php
echo aui()->alert( array(
    'type'        => 'danger',
    'heading'     => __( 'Error', 'ayecode-connect' ),
    'content'     => __( 'Could not save your changes.', 'ayecode-connect' ),
    'footer'      => __( 'Please try again or contact support.', 'ayecode-connect' ),
    'dismissible' => true,
) );
```

| Key | Type | Default | Description |
|---|---|---|---|
| `type` | string | `'info'` | Alert colour: `'info'`, `'success'`, `'warning'`, `'danger'` / `'error'`. |
| `content` | string | `''` | Alert body (required — nothing renders without it). HTML is passed through `wp_kses_post`. |
| `heading` | string | `''` | Optional `<h4>` heading above the content. |
| `footer` | string | `''` | Optional footer paragraph below a `<hr>`. |
| `icon` | string\|false | auto | Icon class (e.g. `'fas fa-star'`). Each type has a default icon. Pass `false` to suppress it entirely. |
| `dismissible` | bool | `false` | Adds a close button and Bootstrap fade/show classes. |
| `class` | string | `''` | Extra classes appended to the `.alert` element. |
| `data` | string | `''` | Raw data attributes string (passed through to the element). |

---

## `input()`

Renders a Bootstrap form input. Supports all standard HTML input types plus `'datepicker'`, `'timepicker'`, and `'iconpicker'` for enhanced UI.

```php
// Basic text input
echo aui()->input( array(
    'type'        => 'text',
    'id'          => 'site-name',
    'name'        => 'site_name',
    'label'       => __( 'Site name', 'ayecode-connect' ),
    'placeholder' => __( 'My site', 'ayecode-connect' ),
    'value'       => get_option( 'blogname' ),
) );
```

Horizontal label layout:

```php
echo aui()->input( array(
    'type'       => 'email',
    'id'         => 'user-email',
    'name'       => 'user_email',
    'label'      => __( 'Email', 'ayecode-connect' ),
    'label_type' => 'horizontal',
    'label_col'  => '3',
    'required'   => true,
) );
```

Toggle switch (checkbox rendered as a switch):

```php
echo aui()->input( array(
    'type'        => 'checkbox',
    'id'          => 'enable-notifications',
    'name'        => 'enable_notifications',
    'label'       => __( 'Enable email notifications', 'ayecode-connect' ),
    'switch'      => true,
    'checked'     => (bool) get_option( 'my_notifications' ),
    'with_hidden' => true,
) );
```

Date picker:

```php
echo aui()->input( array(
    'type'  => 'datepicker',
    'id'    => 'event-date',
    'name'  => 'event_date',
    'label' => __( 'Event date', 'ayecode-connect' ),
    'value' => '2026-01-01',
) );
```

| Key | Type | Default | Description |
|---|---|---|---|
| `type` | string | `'text'` | Input type. Standard HTML types plus `'datepicker'`, `'timepicker'`, `'iconpicker'`. |
| `name` | string | `''` | `name` attribute. |
| `id` | string | `''` | `id` attribute (also used by the label `for`). |
| `value` | string | `''` | Input value. |
| `placeholder` | string | `''` | Placeholder text. |
| `title` | string | `''` | `title` / tooltip attribute. |
| `required` | bool | `false` | Adds the `required` attribute. |
| `checked` | bool | `false` | Initial checked state for `checkbox` / `radio` inputs. |
| `with_hidden` | bool | `false` | Prepends a hidden input with value `0` for checkboxes (ensures the field submits when unchecked). |
| `switch` | bool\|string | `false` | Renders a checkbox as a toggle switch. Pass a string (`'sm'`, `'md'`, `'lg'`) to control size. |
| `size` | string | `''` | `'lg'` / `'large'` or `'sm'` / `'small'` — applies `form-control-lg` / `form-control-sm`. |
| `step` | string | `''` | `step` attribute for `number` inputs. |
| `clear_icon` | bool | `''` | Show a clear (×) button inside the input. Auto-enabled for `datepicker` / `timepicker`. |
| `password_toggle` | bool | `true` | Show eye icon to toggle password visibility on `type="password"` inputs. |
| `label` | string | `''` | Label text. |
| `label_type` | string | `''` | `'horizontal'` — label and input side by side; `'floating'` — Bootstrap floating label; `'hidden'` — visually hidden. |
| `label_col` | string | `'2'` | Column width for the label in horizontal layouts (1–11). |
| `label_class` | string | `''` | Extra classes on the `<label>`. |
| `label_after` | bool | `false` | Place the label after the input (auto-set for `checkbox`, `file`, `floating`). |
| `label_force_left` | bool | `false` | For checkboxes: moves the label to the left side rather than the right. |
| `help_text` | string | `''` | Small muted help text rendered below the input. |
| `validation_text` | string | `''` | Custom browser validation message (`setCustomValidity`). |
| `validation_pattern` | string | `''` | Regex `pattern` attribute for browser-side validation. |
| `class` | string | `''` | Extra classes on the `<input>`. |
| `wrap_class` | string | `''` | Extra classes on the outer form-group wrapper. |
| `form_group_class` | string | `''` | Replaces the default `mb-3` class on the form group wrapper. |
| `no_wrap` | bool | `false` | Skip the form-group wrapper entirely. |
| `input_group_left` | string | `''` | Prepended addon HTML (or plain text, auto-wrapped in `input-group-text`). |
| `input_group_right` | string | `''` | Appended addon HTML. |
| `input_group_left_inside` | bool | `false` | Positions the left addon inside the input (overlay style). |
| `input_group_right_inside` | bool | `false` | Positions the right addon inside the input (overlay style). |
| `element_require` | string | `''` | Conditional display expression (see [Helper utilities](#element_require)). |
| `extra_attributes` | array | `[]` | Key/value pairs added as HTML attributes on `<input>`. |
| `wrap_attributes` | array | `[]` | Key/value pairs added as HTML attributes on the wrapper `<div>`. |

---

## `textarea()`

Renders a Bootstrap `<textarea>` or, optionally, the WordPress block editor (`wp_editor`).

```php
echo aui()->textarea( array(
    'id'    => 'my-bio',
    'name'  => 'bio',
    'label' => __( 'Bio', 'ayecode-connect' ),
    'rows'  => 5,
    'value' => get_user_meta( get_current_user_id(), 'description', true ),
) );
```

Floating label with help text:

```php
echo aui()->textarea( array(
    'id'         => 'message',
    'name'       => 'message',
    'label'      => __( 'Message', 'ayecode-connect' ),
    'label_type' => 'floating',
    'help_text'  => __( 'Maximum 500 characters.', 'ayecode-connect' ),
    'rows'       => 4,
) );
```

WYSIWYG (WordPress block editor):

```php
echo aui()->textarea( array(
    'id'     => 'post-content',
    'name'   => 'post_content',
    'label'  => __( 'Content', 'ayecode-connect' ),
    'wysiwyg' => true,
    'value'  => $post->post_content,
    'rows'   => 8,
) );
```

| Key | Type | Default | Description |
|---|---|---|---|
| `name` | string | `''` | `name` attribute. |
| `id` | string | `''` | `id` attribute. |
| `value` | string | `''` | Textarea content. |
| `placeholder` | string | `''` | Placeholder text. |
| `title` | string | `''` | `title` attribute. |
| `rows` | string | `''` | Number of visible rows. |
| `required` | bool | `false` | Adds the `required` attribute. |
| `wysiwyg` | bool\|array | `false` | Render a `wp_editor` instead of a plain textarea. Pass an array to override `wp_editor` settings. |
| `allow_tags` | bool | `false` | When `true`, the value is sanitized with `kses` (preserving HTML) instead of stripping all tags. |
| `label` | string | `''` | Label text. |
| `label_type` | string | `''` | `'horizontal'`, `'floating'`, or `'hidden'`. `'floating'` is disabled when `wysiwyg` is set. |
| `label_col` | string | `''` | Column width for horizontal labels (1–11). |
| `label_class` | string | `''` | Extra classes on the `<label>`. |
| `label_after` | bool | `false` | Place the label after the textarea. |
| `help_text` | string | `''` | Small muted text below the field. |
| `validation_text` | string | `''` | Custom browser validation message. |
| `validation_pattern` | string | `''` | `pattern` attribute for validation. |
| `class` | string | `''` | Extra classes on `<textarea>`. |
| `wrap_class` | string | `''` | Extra classes on the wrapper. |
| `form_group_class` | string | `''` | Replaces the default `mb-3` on the form group. |
| `no_wrap` | bool | `false` | Skip the form-group wrapper. |
| `input_group_left` | string | `''` | Prepended addon HTML. |
| `input_group_right` | string | `''` | Appended addon HTML. |
| `input_group_right_inside` | bool | `false` | Position right addon inside (overlay). |
| `element_require` | string | `''` | Conditional display expression. |
| `extra_attributes` | array | `[]` | Extra HTML attributes on `<textarea>`. |
| `wrap_attributes` | array | `[]` | Extra HTML attributes on the wrapper. |

---

## `select()`

Renders a Bootstrap `<select>`. Supports Select2 for searchable dropdowns and a tagging mode.

```php
echo aui()->select( array(
    'id'      => 'country',
    'name'    => 'country',
    'label'   => __( 'Country', 'ayecode-connect' ),
    'value'   => 'IE',
    'options' => array(
        'DE' => __( 'Germany', 'ayecode-connect' ),
        'FR' => __( 'France', 'ayecode-connect' ),
        'IE' => __( 'Ireland', 'ayecode-connect' ),
    ),
) );
```

Searchable Select2 with a placeholder:

```php
echo aui()->select( array(
    'id'          => 'category',
    'name'        => 'category',
    'label'       => __( 'Category', 'ayecode-connect' ),
    'placeholder' => __( 'Select a category…', 'ayecode-connect' ),
    'select2'     => true,
    'options'     => $category_options,
) );
```

Multiple selection with Select2 tags:

```php
echo aui()->select( array(
    'id'      => 'tags',
    'name'    => 'tags',
    'label'   => __( 'Tags', 'ayecode-connect' ),
    'select2' => 'tags',
    'value'   => array( 'php', 'javascript' ),
    'options' => array(
        'php'        => 'PHP',
        'javascript' => 'JavaScript',
        'python'     => 'Python',
    ),
) );
```

Option groups:

```php
$options = array(
    array( 'optgroup' => 'start', 'label' => 'Europe' ),
    array( 'value' => 'IE', 'label' => 'Ireland' ),
    array( 'value' => 'DE', 'label' => 'Germany' ),
    array( 'optgroup' => 'end' ),
    array( 'optgroup' => 'start', 'label' => 'Americas' ),
    array( 'value' => 'US', 'label' => 'United States' ),
    array( 'optgroup' => 'end' ),
);

echo aui()->select( array(
    'id'      => 'region',
    'name'    => 'region',
    'options' => $options,
) );
```

| Key | Type | Default | Description |
|---|---|---|---|
| `name` | string | `''` | `name` attribute. |
| `id` | string | `''` | `id` attribute. |
| `value` | string\|array | `''` | Selected value(s). Pass an array when `multiple` is `true`. |
| `options` | array\|string | `[]` | Options as a `value => label` array, a complex array (see above), or a raw HTML string of `<option>` tags. |
| `placeholder` | string | `''` | Placeholder option shown when nothing is selected. |
| `multiple` | bool | `false` | Enables multiple selection; `name` gets `[]` appended automatically. |
| `select2` | bool\|string | `false` | Enables Select2. Pass `'tags'` to allow free-text tag entry. |
| `required` | bool | `false` | Adds the `required` attribute. |
| `label` | string | `''` | Label text. |
| `label_type` | string | `''` | `'horizontal'` or `'hidden'`. (`'floating'` is treated as `'hidden'` for selects.) |
| `label_col` | string | `''` | Column width for horizontal labels (1–11). |
| `label_class` | string | `''` | Extra classes on the `<label>`. |
| `label_after` | bool | `false` | Place the label after the select. |
| `help_text` | string | `''` | Small muted text below the field. |
| `class` | string | `''` | Extra classes on `<select>`. |
| `wrap_class` | string | `''` | Extra classes on the wrapper. |
| `form_group_class` | string | `''` | Replaces the default `mb-3` on the form group. |
| `no_wrap` | bool | `false` | Skip the form-group wrapper. |
| `input_group_left` | string | `''` | Prepended addon HTML. |
| `input_group_right` | string | `''` | Appended addon HTML. |
| `input_group_left_inside` | bool | `false` | Position left addon inside (overlay). |
| `input_group_right_inside` | bool | `false` | Position right addon inside (overlay). |
| `data-placeholder` | string | `''` | Select2 placeholder (set automatically from `placeholder` when Select2 is active). |
| `data-allow-clear` | bool | `true` | Show a clear button in Select2 (auto-enabled when a placeholder is set). |
| `data-tags` | string | `''` | Select2 tag mode (`'true'`). Set automatically when `select2 = 'tags'`. |
| `data-token-separators` | string | `''` | Select2 tag separators. Set automatically in tag mode. |
| `element_require` | string | `''` | Conditional display expression. |
| `extra_attributes` | array | `[]` | Extra HTML attributes on `<select>`. |
| `wrap_attributes` | array | `[]` | Extra HTML attributes on the wrapper. |

---

## `radio()`

Renders a group of Bootstrap radio buttons.

```php
echo aui()->radio( array(
    'id'      => 'plan',
    'name'    => 'plan',
    'label'   => __( 'Plan', 'ayecode-connect' ),
    'value'   => 'pro',
    'options' => array(
        'free' => __( 'Free', 'ayecode-connect' ),
        'pro'  => __( 'Pro', 'ayecode-connect' ),
    ),
) );
```

Horizontal layout with stacked options:

```php
echo aui()->radio( array(
    'id'         => 'theme',
    'name'       => 'theme',
    'label'      => __( 'Theme', 'ayecode-connect' ),
    'label_type' => 'horizontal',
    'label_col'  => '3',
    'inline'     => false,
    'value'      => 'light',
    'options'    => array(
        'light' => __( 'Light', 'ayecode-connect' ),
        'dark'  => __( 'Dark', 'ayecode-connect' ),
        'auto'  => __( 'System default', 'ayecode-connect' ),
    ),
) );
```

| Key | Type | Default | Description |
|---|---|---|---|
| `name` | string | `''` | Shared `name` attribute for all radio inputs. |
| `id` | string | `''` | Base `id`; each option appends an index to ensure uniqueness. |
| `value` | string | `''` | The value of the currently selected option. |
| `options` | array | `[]` | Radio options as `value => label` pairs. |
| `inline` | bool | `true` | Display options side by side (`form-check-inline`). Set `false` to stack vertically. |
| `required` | bool | `false` | Adds `required` to each radio input. |
| `label` | string | `''` | Group label shown above the options. |
| `label_type` | string | `''` | `'horizontal'`, `'top'`, or `'hidden'`. (`'floating'` is treated as `'horizontal'`.) |
| `label_col` | string | `''` | Column width for horizontal labels (1–11). |
| `label_class` | string | `''` | Extra classes on the group `<label>`. |
| `help_text` | string | `''` | Small muted text below the group. |
| `class` | string | `''` | Extra classes on each `<input>`. |
| `wrap_class` | string | `''` | Extra classes on the outer wrapper. |
| `no_wrap` | bool | `false` | Skip the outer `mb-3` wrapper. |
| `element_require` | string | `''` | Conditional display expression. |
| `extra_attributes` | array | `[]` | Extra HTML attributes on each `<input type="radio">`. |
| `wrap_attributes` | array | `[]` | Extra HTML attributes on the outer wrapper. |

---

## `button()`

Renders a Bootstrap button (`<button>`), anchor link (`<a>`), or inline badge (`<span>`).

```php
// Anchor link (default)
echo aui()->button( array(
    'content' => __( 'View profile', 'ayecode-connect' ),
    'href'    => get_author_posts_url( get_current_user_id() ),
    'class'   => 'btn btn-outline-primary',
) );
```

Submit button with icon:

```php
echo aui()->button( array(
    'type'    => 'submit',
    'content' => __( 'Save changes', 'ayecode-connect' ),
    'icon'    => 'fas fa-save',
    'class'   => 'btn btn-primary',
) );
```

Link that opens in a new tab:

```php
echo aui()->button( array(
    'content'    => __( 'Documentation', 'ayecode-connect' ),
    'href'       => 'https://docs.ayecode.io',
    'new_window' => true,
    'class'      => 'btn btn-link',
) );
```

| Key | Type | Default | Description |
|---|---|---|---|
| `type` | string | `'a'` | `'a'` for an anchor link, `'badge'` for a `<span>`, or any valid `<button>` type (`'button'`, `'submit'`, `'reset'`). |
| `href` | string | `'#'` | URL for anchor links. Ignored for `<button>` types. |
| `new_window` | bool | `false` | Adds `target="_blank"` to anchor links. |
| `content` | string | `''` | Button label text (HTML allowed). |
| `icon` | string | `''` | Icon class rendered before `content` (e.g. `'fas fa-save'`). |
| `hover_content` | string | `''` | Alternate label shown on hover (requires CSS support). |
| `hover_icon` | string | `''` | Alternate icon shown on hover. |
| `class` | string | `'btn btn-primary'` | CSS classes on the element. |
| `id` | string | `''` | `id` attribute. |
| `name` | string | `''` | `name` attribute (for `<button>` elements). |
| `value` | string | `''` | `value` attribute. |
| `title` | string | `''` | `title` / tooltip attribute. |
| `onclick` | string | `''` | Inline `onclick` handler. |
| `style` | string | `''` | Inline CSS `style` attribute. |
| `new_line_after` | bool | `true` | Append a newline character after the element. |
| `no_wrap` | bool | `true` | Skip the `Input::wrap()` form-group wrapper (default is to skip it). |
| `extra_attributes` | array | `[]` | Key/value pairs added as additional HTML attributes. |
| `icon_extra_attributes` | array | `[]` | Key/value pairs added as HTML attributes on the `<i>` icon element. |

---

## `badge()`

Renders an inline `<span>` badge. Accepts the same keys as [`button()`](#button). Defaults `type` to `'badge'` and `class` to `'badge badge-primary align-middle'` (pass `href` to render as an anchor instead).

```php
echo aui()->badge( array(
    'content' => __( 'New', 'ayecode-connect' ),
    'class'   => 'badge bg-success',
) );
```

```php
// Linked badge
echo aui()->badge( array(
    'content' => __( '12 comments', 'ayecode-connect' ),
    'href'    => get_comments_link(),
    'class'   => 'badge bg-secondary',
) );
```

---

## `dropdown()`

Renders a Bootstrap dropdown: a trigger button plus a menu. Supply either `dropdown_menu` (pre-built HTML) or `dropdown_items` (an array processed through `render()`).

```php
echo aui()->dropdown( array(
    'content' => __( 'Actions', 'ayecode-connect' ),
    'id'      => 'actions-menu',
    'class'   => 'btn btn-secondary dropdown-toggle',
    'dropdown_items' => array(
        array(
            'render'  => 'button',
            'type'    => 'a',
            'content' => __( 'Edit', 'ayecode-connect' ),
            'href'    => admin_url( 'post.php?action=edit&post=1' ),
            'class'   => 'dropdown-item',
        ),
        array(
            'render'  => 'button',
            'type'    => 'a',
            'content' => __( 'Delete', 'ayecode-connect' ),
            'href'    => '#',
            'class'   => 'dropdown-item text-danger',
        ),
    ),
) );
```

Pre-rendered menu HTML:

```php
$menu_html = '<div class="dropdown-menu">'
    . '<a class="dropdown-item" href="#">' . esc_html__( 'Profile', 'ayecode-connect' ) . '</a>'
    . '<a class="dropdown-item" href="#">' . esc_html__( 'Settings', 'ayecode-connect' ) . '</a>'
    . '</div>';

echo aui()->dropdown( array(
    'content'       => __( 'Account', 'ayecode-connect' ),
    'dropdown_menu' => $menu_html,
) );
```

| Key | Type | Default | Description |
|---|---|---|---|
| `type` | string | `'button'` | Trigger button type. |
| `href` | string | `'#'` | Trigger href (for anchor-style triggers). |
| `content` | string | `''` | Trigger button label. |
| `icon` | string | `''` | Icon class for the trigger button. |
| `class` | string | `'btn btn-primary dropdown-toggle'` | Classes on the trigger button. |
| `id` | string | `''` | `id` on the trigger; used as `aria-labelledby` for the menu. |
| `wrapper_class` | string | `''` | Extra classes on the outer `.dropdown` wrapper `<div>`. |
| `dropdown_menu_class` | string | `''` | Extra classes on the `.dropdown-menu` `<div>`. |
| `dropdown_menu` | string | `''` | Pre-rendered dropdown menu HTML. Takes priority over `dropdown_items`. |
| `dropdown_items` | array | `[]` | Array of component argument arrays (same format as `render()`) to build the menu. |
| `hover_content` | string | `''` | Alternate trigger label on hover. |
| `hover_icon` | string | `''` | Alternate trigger icon on hover. |
| `title` | string | `''` | `title` attribute on the trigger. |
| `value` | string | `''` | `value` attribute on the trigger. |
| `aria-haspopup` | string | `'true'` | ARIA attribute on the trigger. |
| `aria-expanded` | string | `'false'` | ARIA attribute on the trigger. |

---

## `pagination()`

Renders Bootstrap pagination from the current `WP_Query` or a custom page count and links array.

```php
// Uses $wp_query automatically
echo aui()->pagination();
```

Custom total with rounded pill style:

```php
echo aui()->pagination( array(
    'total'         => $custom_query->max_num_pages,
    'mid_size'      => 1,
    'rounded_style' => true,
) );
```

Custom prev/next button text:

```php
echo aui()->pagination( array(
    'total'            => $query->max_num_pages,
    'custom_prev_text' => '← ' . __( 'Newer', 'ayecode-connect' ),
    'custom_next_text' => __( 'Older', 'ayecode-connect' ) . ' →',
) );
```

| Key | Type | Default | Description |
|---|---|---|---|
| `total` | int | `$wp_query->max_num_pages` | Total number of pages. |
| `links` | array | `[]` | Pre-generated array of page link HTML strings. When empty, `paginate_links()` is called. |
| `mid_size` | int | `2` | Number of page number links to show either side of the current page. |
| `prev_text` | string | chevron-left icon | Previous page button content. |
| `next_text` | string | chevron-right icon | Next page button content. |
| `custom_prev_text` | string | `''` | When set, renders a separate styled prev button alongside the pagination. |
| `custom_next_text` | string | `''` | When set, renders a separate styled next button alongside the pagination. |
| `screen_reader_text` | string | `'Posts navigation'` | Accessible label for the `<nav>` element. |
| `rounded_style` | bool | `false` | Renders page numbers as rounded pills. |
| `before_paging` | string | `''` | HTML prepended before the `<section>` wrapper. |
| `after_paging` | string | `''` | HTML appended after the `<section>` wrapper. |
| `class` | string | `''` | Extra classes on the `<ul class="pagination">` element. |

---

## `wrap()`

Wraps arbitrary content in an HTML element. Used internally by other components but available directly when you need a labelled form-group container around custom HTML.

```php
echo aui()->wrap( array(
    'content' => '<p>' . esc_html__( 'Custom content here.', 'ayecode-connect' ) . '</p>',
    'class'   => 'mb-3 p-3 border rounded',
) );
```

With left/right input group addons:

```php
echo aui()->wrap( array(
    'content'            => $my_input_html,
    'class'              => 'input-group',
    'input_group_left'   => '$',
    'input_group_right'  => '.00',
) );
```

| Key | Type | Default | Description |
|---|---|---|---|
| `type` | string | `'div'` | HTML tag for the wrapper element. |
| `class` | string | `'mb-3'` | Classes on the wrapper element. |
| `content` | string | `''` | HTML content to wrap. |
| `input_group_left` | string | `''` | Prepended addon. Plain text is auto-wrapped in `<span class="input-group-text">`. |
| `input_group_right` | string | `''` | Appended addon. Plain text is auto-wrapped in `<span class="input-group-text">`. |
| `input_group_left_inside` | bool | `false` | Positions the left addon inside the field (overlay). |
| `input_group_right_inside` | bool | `false` | Positions the right addon inside the field (overlay). |
| `element_require` | string | `''` | Conditional display expression. |
| `wrap_attributes` | array | `[]` | Extra HTML attributes on the wrapper element. |

---

## Helper Utilities

Static utility methods in `AyeCode\UI\Components\Helper`. Call them statically, or via `aui()->helpers()->method()` for fluent chaining:

```php
$help_html = aui()->helpers()->help_text( __( 'Enter your username.', 'ayecode-connect' ) );
```

---

### `icon()`

Builds an icon tag. On the **frontend** (public-facing output), delegates to `ayecode_get_icon()` for JIT/SVG rendering when the Font Awesome settings package is present. In wp-admin or when that function is absent, falls back to a plain `<i>` tag.

```php
$icon = Helper::icon( 'fas fa-user', true );
// <i class="fas fa-user me-2"></i>
```

| Parameter | Type | Default | Description |
|---|---|---|---|
| `$class` | string | — | Icon identifier/class string (e.g. `'fas fa-star'`). |
| `$space_after` | bool | `false` | When `true`, adds `me-2` (right margin) to the icon. |
| `$extra_attributes` | array | `[]` | Additional HTML attributes keyed by attribute name. |

---

### `help_text()`

Renders a small muted `<small>` element for inline form help.

```php
echo Helper::help_text( __( 'Used for login only — never shown publicly.', 'ayecode-connect' ) );
```

| Parameter | Type | Description |
|---|---|---|
| `$text` | string | Help text (HTML allowed; passed through `wp_kses_post`). |

---

### `element_require()`

Converts a conditional expression into a `data-element-require` attribute. Components that carry this attribute are shown/hidden by AyeCode UI's JS according to the value of another field.

```php
// Show this field only when 'plan' equals 'pro'
$attr = Helper::element_require( "[%plan%]=='pro'" );
// data-element-require="jQuery(form).find('[data-argument=&quot;plan&quot;]')..."
```

The expression syntax: `[%field_id%]` expands to the current value of the field whose `id` matches. Append `:checked` for checkbox state: `[%my_checkbox%:checked]=='1'`.

---

### `data_attributes()` / `aria_attributes()`

Extract and render all `data-*` or `aria-*` keys from a component args array as HTML attributes.

```php
$attrs = Helper::data_attributes( array( 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Hello' ) );
// data-bs-toggle="tooltip" data-bs-title="Hello"
```

---

### `extra_attributes()`

Renders an arbitrary array of key/value pairs as HTML attributes. Pass a string to inject raw attribute text.

```php
$attrs = Helper::extra_attributes( array( 'readonly' => 'readonly', 'autocomplete' => 'off' ) );
```

---

### `get_column_class()`

Returns a Bootstrap grid column class for use in horizontal label layouts.

```php
Helper::get_column_class( 3, 'label' );  // 'col-sm-3'
Helper::get_column_class( 3, 'input' );  // 'col-sm-9'
```

| Parameter | Type | Default | Description |
|---|---|---|---|
| `$label_number` | int | `2` | Label column width (1–11). |
| `$type` | string | `'label'` | `'label'` returns `col-sm-{n}`; `'input'` returns the complementary `col-sm-{12-n}`. |

---

### `sanitize_html_field()`

Sanitizes a value (string, array, or object) using WordPress `wp_kses` while preserving allowable HTML, including iframes. Use this when a field's `allow_tags` is `true`.

```php
$clean = Helper::sanitize_html_field( $raw_html );
```

Applies the `ayecode_ui_sanitize_html_field` filter after sanitization.

---

### `sanitize_textarea_field()`

Sanitizes a multiline string, stripping tags while preserving newlines. Mirrors WordPress `sanitize_textarea_field` behaviour.

```php
$clean = Helper::sanitize_textarea_field( $raw );
```

---

### `kses_allowed_html()`

Returns the allowed HTML tag array for a given context, extended with `<iframe>` support for the `'post'` context.

```php
$allowed = Helper::kses_allowed_html( 'post' );
```

Applies the `ayecode_ui_kses_allowed_html` filter.

---

### Attribute helpers

Small helpers that generate individual HTML attribute strings with proper escaping:

| Method | Returns |
|---|---|
| `Helper::name( $text, $multiple )` | `name="…"` — appends `[]` when `$multiple` is `true` and brackets are absent. |
| `Helper::id( $text )` | `id="…"` — sanitized with `sanitize_html_class`. |
| `Helper::title( $text )` | `title="…"` — escaped with `esc_attr`. |
| `Helper::value( $text )` | `value="…"` — unslashed then `esc_attr`. |
| `Helper::class_attr( $text )` | `class="…"` — each token sanitized via `sanitize_html_class`. |
| `Helper::esc_classes( $text )` | Sanitizes a space-separated class string; returns the string only (no `class=`). |
