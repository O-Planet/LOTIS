[English](Html.md) | [Русский](Html.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Html Class

## Purpose
Arbitrary HTML element representation class of the LOTIS framework, inheriting from `Element`. Designed for creating HTML tags with dynamically determined names based on the constructor parameter. Supports two modes of operation: creating a standard HTML element (div, span, label, p, etc.) or connecting an external content file. The class is used for generating semantic HTML tags, wrappers, containers, and inserting external HTML content into the page structure.

### Main Features

* **Creating HTML elements** via constructor
* **Working with external files** of HTML
* **Flexible configuration** of element type
* **Integration** with LTS objects

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$filename** | `string/null` | `public` | Path to the external file for connecting content. Set in the constructor if `$id` contains a dot. Used by the `shine()` method for file loading. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique object identifier, used as HTML `id` attribute. |
| **$type** | `string` | `public` | Inherited from `Element`. Set to `'file'` if `$id` contains a dot, otherwise `'html'`. Defines the element processing type. |
| **$tagname** | `string` | `public` | Inherited from `Element`. Set in the constructor based on `$id`. Default `'div'` if `$id` is empty. Defines the HTML tag of the element during rendering. |
| **$caption** | `string` | `public` | Inherited from `Element`. Text content of the element. Displayed as text inside the tag or ignored for files. |
| **$classname** | `string` | `public` | Inherited from `Element`. CSS classes of the element, managed by methods `addclass()`, `removeclass()`, `hasclass()`. |
| **$storage** | `array` | `public` | Inherited from `Quark`. Storage contains sections `'childs'` (children), `'element'` (DOM element), `'attr'` (HTML attributes). |

## Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Element` constructor. If `$id` contains a dot (`.`) — sets `$type` to `'file'` and saves `$id` as `$filename` for loading an external file. Otherwise — sets `$tagname` to the value of `$id` or `'div'` if `$id` is empty. |
| **shine** | `Element $parent = null` | — | Rendering the element. Calls parent `shine()` for standard processing. If `$type == 'file'` — sets the filename in the `'element'` section via `set('element', 'filename', $this->filename)` for subsequent file content loading by the `Space` namespace. |

## Usage Examples

```php
// Creating a ul element
$ul = LTS::Html('ul');
$ul->add(LTS::Html('li')->capt('Item 1'))
    ->add(LTS::Html('li')->capt('Item 2'))
    ->add(LTS::Html('li')->capt('Item 4'));
```

Working with files

```php
// Loading HTML from a file
$html = new Html('path/to/file.html');
```

## Notes
*   Class extends `Element`, therefore inherits all methods for managing children, styles, attributes, and events.
*   Class extends `Quark` and `Construct`, therefore supports full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks.
*   **Tag mode:** If `$id` does not contain a dot — an HTML tag with name `$id` is created (e.g., `'p'`, `'span'`, `'label'`, `'section'`).
*   **File mode:** If `$id` contains a dot (e.g., `'header.html'`) — content is loaded from a file via the `Space::processFile()` mechanism.
*   **Default tag:** If `$id` is empty and is not a file — a `'div'` tag is created.
*   **Attributes:** Additional attributes are added via `attr()` — `class`, `style`, `data-*`, etc.
*   **Children:** The element can contain children via `add()` — rendered inside the tag.
*   **Rendering:** During `shine()`, an HTML tag with attributes and content is created, or a file is loaded.
*   **Namespace:** Registered via `addinspace()` — available in the `Space` namespace for page assembly.
*   **Events:** Supports assigning event handlers via `on()` or `click()` — generate client-side JavaScript code during compilation.
*   **Hooks:** Supports lifecycle hooks — `check`, `before`, `on` for injecting logic.
*   **Styles:** CSS properties are added via `css()` — for customizing appearance.
*   **Classes:** CSS classes are added via `addclass()` — for styling via external files.
*   **ID:** Unique identifier is used for linking with the client object via `LTS.get('{id}')`.
*   **Type:** Set to `'html'` or `'file'` — for rendering in `Space`.
*   **Compilation:** Supports `compile()` for generating client-side JavaScript code when necessary.
*   **Content:** Text is set via `$caption` or the `capt()` method — displayed inside the tag.
*   **Files:** For file mode, content is loaded via `file_get_contents()` in `Space::processFile()`.
*   **File path:** Relative path from project root — file must exist before rendering.
*   **Caching:** File content is not cached — loaded on each rendering.
*   **Security:** File path is not validated — responsibility is on the developer.
*   **Nesting:** Can contain other elements via `add()` — for complex structure.
*   **Inheritance:** All `Element` methods are available — `css()`, `attr()`, `add()`, `on()`, etc.
*   **Usage:** Suitable for semantic tags, wrappers, containers, and inserting external HTML.
