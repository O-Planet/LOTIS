[English](Button.md) | [Русский](Button.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Button Class

## Purpose
Button class of the LOTIS framework, inheriting from `Element`. Designed for creating HTML buttons with the ability to assign event handlers and styling. The class automatically sets the `<button>` tag and `type="button"` attribute, which distinguishes it from standard `Input` buttons. Supports integration with jQuery UI via the `ui()` method for applying standard themes. Used in forms, toolbars, and dialog boxes to initiate user actions.

## Properties

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique object identifier, used as HTML `id` attribute. |
| **$tagname** | `string` | `public` | Inherited from `Element`. Set to `'button'` in the constructor. Defines the HTML tag of the element during rendering. |
| **$caption** | `string` | `public` | Inherited from `Element`. Text content of the button. Displayed as text inside the `<button>` tag. |
| **$type** | `string` | `public` | Inherited from `Element`. Set to `'html'`. Defines the object type for filtering and processing. |
| **$classname** | `string` | `public` | Inherited from `Element`. CSS classes of the element, managed by methods `addclass()`, `removeclass()`, `hasclass()`. |
| **$storage** | `array` | `public` | Inherited from `Quark`. Storage contains sections `'childs'` (children), `'element'` (DOM element), `'attr'` (HTML attributes). |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Element` constructor, sets `tagname` to `'button'`, adds `type` attribute with value `'button'`. |

### Applying Styles

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **ui** | — | `$this` | Adds jQuery UI classes for button styling. Adds classes `'ui-button'`, `'ui-corner-all'`, `'ui-widget'` via `addclass()`. Returns `$this` to support fluent interface. |

## Usage Example

```php
$mybutton = LTS::Button()
    ->ui()
    ->capt('Press')
    ->attr('disabled', false)
    ->click(
<<<JS
        alert('Hello, world!');
JS
);
```

### Client-side Click Simulation

```JS
LTS(mybutton).click();
```

## Notes
*   Class extends `Element`, therefore inherits all methods for managing children, styles, attributes, and events.
*   Class extends `Quark` and `Construct`, therefore supports full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks (`$check`, `$before`, `$on`, etc.).
*   **jQuery UI:** Classes `ui-button`, `ui-corner-all`, `ui-widget` are applied via `ui()` — for standard styling.
*   **Method ui():** Requires jQuery UI CSS (`jquery-ui.css`) to be loaded for correct style display.
*   **Button Text:** Set via `$caption` property or `capt()` method — displayed between `<button>` and `</button>` tags.
*   **Event Handlers:** Assigned via `click()` or `on()` methods — generate client-side JavaScript code during compilation.
*   **Identifier:** Button ID is used to link with LTS object via `LTS.get('{id}')` or LTS(var), where var is a variable referencing the Button class object.
*   **Attributes:** Additional attributes added via `attr()` — `disabled`, `data-*`, `title`, etc.
*   **Styles:** CSS properties added via `css()` — `width`, `height`, `margin`, `padding`, etc.
*   **Children:** Button can contain children via `add()` — icons, images, etc.
*   **Classes:** CSS classes added via `addclass()` — for customizing appearance.
