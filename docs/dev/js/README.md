# AyeCode UI — JavaScript Helpers

A small set of global JavaScript helper functions ship with AyeCode UI (see `includes/inc/bs5-js.php`). They are available on any page where the AyeCode UI assets are loaded, with no imports or build step required.

This document covers only the helpers intended for use in your own code. The file contains many other functions, but those are internal initializers and editor/FSE utilities — do not call them directly.

## Contents

- [`aui_modal()`](#aui_modal) — build and show a modal
- [`aui_modal_iframe()`](#aui_modal_iframe) — show a modal containing an iframe
- [`aui_confirm()`](#aui_confirm) — promise-based confirm dialog
- [`aui_toast()`](#aui_toast) — show a toast notification
- [`aui_time_ago()`](#aui_time_ago) — render relative "time ago" text
- [`aui_init()`](#aui_init) — (re)initialize AyeCode UI components

---

## `aui_modal()`

Builds a modal from the given parts, appends it to `<body>`, and shows it immediately. Any existing AyeCode modal and backdrop are removed first, so only one is ever on screen at a time.

```js
aui_modal(title, body, footer, dismissible, class, dialog_class, body_class)
```

| Parameter | Type | Default | Description |
|---|---|---|---|
| `title` | string | `''` | Header title (HTML allowed). If empty, no header is rendered. |
| `body` | string | spinner | Body HTML. If empty, a centered loading spinner is shown. |
| `footer` | string | `''` | Footer HTML. If empty, no footer is rendered. |
| `dismissible` | bool | `false` | When `true` (and a title is set), shows a close (×) button. |
| `class` | string | `''` | Extra classes for the `.modal` element (e.g. `modal-lg` sizing on the dialog uses `dialog_class`). |
| `dialog_class` | string | `''` | Extra classes for `.modal-dialog` (e.g. `modal-lg`, `modal-sm`, `modal-xl`). |
| `body_class` | string | `''` | Extra classes for `.modal-body`. |

```js
// Simple confirmation-style message
aui_modal(
    'Saved',
    '<p>Your changes were saved successfully.</p>',
    '<button class="btn btn-primary" data-bs-dismiss="modal">Close</button>',
    true,
    '',
    'modal-sm'
);
```

---

## `aui_modal_iframe()`

Shows a modal whose body is an `<iframe>` pointing at `url`. A loading spinner is displayed until the iframe finishes loading. Useful for previews and embedded external content.

```js
aui_modal_iframe(title, url, footer, dismissible, class, dialog_class, body_class, responsive)
```

| Parameter | Type | Default | Description |
|---|---|---|---|
| `title` | string | `''` | Header title (HTML allowed). |
| `url` | string | — | The URL to load in the iframe. |
| `footer` | string | `''` | Footer HTML. |
| `dismissible` | bool | `false` | Show a close (×) button when a title is set. |
| `class` | string | `''` | Extra classes for the `.modal` element. |
| `dialog_class` | string | `''` | Extra classes for `.modal-dialog` (e.g. `modal-lg`). |
| `body_class` | string | `'p-0'` | Extra classes for `.modal-body`. Defaults to `p-0`. |
| `responsive` | bool | `false` | When `true`, wraps the iframe in a 16:9 responsive ratio box; otherwise it fills ~90vh. |

```js
aui_modal_iframe(
    'Preview',
    'https://example.com/embed/123',
    '',
    true,
    '',
    'modal-lg',
    'p-0',
    true
);
```

---

## `aui_confirm()`

A promise-based replacement for the native `confirm()`. Shows a small modal with OK/Cancel buttons and returns a `Promise` that resolves to `true` (confirmed) or `false` (cancelled).

```js
aui_confirm(message, okButtonText, cancelButtonText, isDelete, large)
```

| Parameter | Type | Default | Description |
|---|---|---|---|
| `message` | string | `'Are you sure?'` | The message shown to the user (HTML allowed). |
| `okButtonText` | string | `'Yes'` | Label for the confirm button. |
| `cancelButtonText` | string | `'Cancel'` | Label for the cancel button. |
| `isDelete` | bool | `false` | When `true`, styles the confirm button as `btn-danger`. |
| `large` | bool | `false` | When `false` (default) the modal uses `modal-sm`; pass `true` for a regular-width modal. |

**Returns:** `Promise<boolean>`

```js
const ok = await aui_confirm('Delete this item?', 'Delete', 'Cancel', true);
if (ok) {
    // proceed with deletion
}

// Or with .then()
aui_confirm('Are you sure?').then(function (confirmed) {
    if (confirmed) { /* ... */ }
});
```

---

## `aui_toast()`

Shows a toast notification in the bottom-right of the screen. The toast container is created automatically on first use. Calls are lightly throttled, so rapid successive toasts are queued.

```js
aui_toast(id, type, title, title_small, body, time, can_close)
```

| Parameter | Type | Default | Description |
|---|---|---|---|
| `id` | string | timestamp | Unique id. If a toast with this id already exists, it is replaced. Pass an empty string for an auto-generated id. |
| `type` | string | — | One of `success`, `error`, `danger`, `info`, `warning`. Controls color and icon. |
| `title` | string | — | Bold header title (HTML allowed). |
| `title_small` | string | — | Smaller muted text in the header. |
| `body` | string | — | Toast body content (HTML allowed). |
| `time` | int | `3000` | Auto-hide delay in milliseconds. Pass `0` to disable auto-hide (stays until closed). |
| `can_close` | bool | `false` | When `true`, shows a close button in the header. |

```js
aui_toast(
    'save-notice',
    'success',
    'Saved',
    '',
    'Your changes have been saved.',
    4000,
    true
);
```

---

## `aui_time_ago()`

Converts timestamps into human-readable relative text (e.g. "5 minutes ago"), prefixed with a clock icon and refreshed automatically every 60 seconds.

You normally **do not call this function yourself** — it is fired inside [`aui_init()`](#aui_init) for the `timeago` class. To use it, just give your element the `timeago` class and provide the timestamp; `aui_init()` handles the rest.

```html
<!-- Preferred: use a datetime attribute in ISO 8601 format -->
<span class="timeago" datetime="2022-12-13T15:10:04+00:00"></span>
```

- The element **must** have the `timeago` class for it to be picked up.
- The timestamp is read from the `datetime` attribute (preferred) or, failing that, the `title` attribute.
- Use full ISO 8601 with a timezone offset, e.g. `datetime="2022-12-13T15:10:04+00:00"`.

```js
aui_time_ago(selector)
```

| Parameter | Type | Description |
|---|---|---|
| `selector` | string | A **class name** (without a leading `.`) identifying the elements to update. Defaults to `timeago` when called via `aui_init()`. |

---

## `aui_init()`

Runs all AyeCode UI component initializers: counters, tooltips/popovers, Choices.js selects, flatpickr date pickers, icon pickers, greedy nav, "time ago" labels, multiple-item carousels, lightbox embeds, and modal iframes.

```js
aui_init()
```

This runs automatically on window `load`. Call it again after injecting new markup into the page (for example, content loaded via AJAX) so that any AyeCode UI components inside it are initialized.

The individual initializers are internal — always call `aui_init()` rather than them directly. Initializers are guarded against double-initialization, so re-running `aui_init()` is safe.

```js
// After inserting AJAX-loaded HTML that contains AUI components:
container.innerHTML = response;
aui_init();
```
