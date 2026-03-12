[English](Span.md) | [Русский](Span.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Span Class

## Purpose
Inline container representation class of the LOTIS framework, inheriting from `Element`. Designed for creating the HTML element `<span>`, which is used for grouping inline text elements, applying styles to individual content fragments, or adding attributes to part of the text without breaking the flow layout. Unlike `Div`, it does not create a block container and does not move content to a new line.

## Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique object identifier, used as HTML `id` attribute. |
| **$type** | `string` | `public` | Inherited from `Element`. Set to `'html'`. Defines the object type for filtering and processing. |
| **$tagname** | `string` | `public` | Inherited from `Element`. Set to `'span'` in the constructor. Defines the HTML tag of the element during rendering. |
| **$classname** | `string` | `public` | Inherited from `Element`. CSS classes of the element, managed by methods `addclass()`, `removeclass()`, `hasclass()`. |
| **$caption** | `string` | `public` | Inherited from `Element`. Text content of the element. Displayed as text inside the `<span>` tag. |
| **$storage** | `array` | `public` | Inherited from `Quark`. Storage contains sections `'childs'` (children), `'element'` (DOM element), `'attr'` (HTML attributes). |

## Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Element` constructor, sets `tagname` to `'span'`. |

### Usage Examples

```php
// Creating a simple span element
$span = LTS::Span();

// Adding text
$span->capt('This is text inside span');

// Adding classes and attributes
$span->addclass('highlight')
     ->attr('title', 'Hover for tooltip');

// Creating a span with text color
$span->css()->add('color: red; font-weight: bold;');

// Nesting in another element
$div = LTS::Div();
$div->add($span);
```

## Notes
*   Class extends `Element`, therefore inherits all methods for managing children, styles, attributes, and events (`css()`, `attr()`, `add()`, `on()`, etc.).
*   Class extends `Quark` and `Construct`, therefore supports full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks.
*   **Inline element:** By default does not create line breaks — used for highlighting part of the text inside a paragraph or other block element.
*   **Nesting:** Can contain other elements via the `add()` method — e.g., `Span`, `Html`, `Input` for complex text structure.
*   **Styling:** CSS properties are added via `css()` — for changing color, font, spacing, and other text parameters.
*   **Attributes:** Additional attributes are added via `attr()` — `title`, `data-*`, `style`, etc.
*   **Rendering:** During `shine()`, an HTML `<span>` tag is created with attributes and content (`caption` or children).
*   **Hooks:** Supports lifecycle hooks — `check`, `before`, `on` for injecting creation logic.
