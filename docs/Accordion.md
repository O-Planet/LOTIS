[English](Accordion.md) | [Русский](Accordion.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Accordion Class

## Purpose
Accordion class inheriting from `Div`. Designed for creating collapsible panels using the jQuery UI Accordion widget. Allows grouping content into sections where only one panel can be open at a time. The class automatically transforms child elements with the `AccordionSection` class into accordion headers and content, generates client-side JavaScript code for widget initialization, and provides methods for programmatic panel state management.

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$vertical** | `bool` | `public` | Flag for vertical accordion mode. Controlled via jQuery UI options. |
| **$ul** | `Element` | `public` | Container object for accordion headers and sections. Created in constructor, added to children. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$tagname** | `string` | `public` | Inherited from `Div`. Default `'div'`. Defines the HTML tag of the element during rendering. |
| **$classname** | `string` | `public` | Inherited from `Element`. The class `'Accordion'` is automatically added in the constructor. |
| **$storage** | `array` | `public` | Inherited from `Quark`. Storage contains sections `'childs'` (child sections), `'element'` (DOM element), `'options'` (accordion parameters). |
| **$id** | `string` | `public` | Inherited from `Construct`. Unique object identifier, used as HTML `id` attribute. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Div` constructor, adds CSS class `'Accordion'` via `addclass()`, creates `$ul` object of type `Element` and adds it to children. |

### Section Management Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **newsection** | `string $caption` | `Div` | Creates a new accordion section. Creates a `Div` object with class `'AccordionSection'`, sets `$caption`, and returns the section object for adding content. |
| **collaps** | — | `$this` | Enables collapsible mode for all panels. Sets the `'collapsible'` option to `true`. Supports fluent interface. |
| **active** | `int $numb = 1` | `$this` | Sets the active panel by number. Number is passed as 1-based, internally converted to 0-based for jQuery UI. Sets the `'active'` option. Supports fluent interface. |
| **heightstyle** | `string $style = 'auto'` | `$this` | Sets the height style for panels. Valid values: `'auto'`, `'fill'`, `'content'`. Sets the `'heightStyle'` option. Supports fluent interface. |

### Lifecycle Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **compile** | — | — | Generates client-side JavaScript methods. Finds all sections with class `'AccordionSection'`, creates an `<h3>` header for each with text from `$caption`, moves the section to `$ul`. Encodes options via `json_encode()`. Creates a JS object with methods: `open(numb)`, `disable`, `enable`. Adds JS initialization code to the `'ready'` area: `jQuery('#{$this->id}').accordion({options})`. Calls `parent::compile()` at the end. |

### Client Methods (generated during compile)

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **LTS(accordion).open(numb)** | `int numb` | — | Opens panel by number. Calls `jQuery('#{id}').accordion('option', 'active', numb - 1)`. Number is 1-based. |
| **LTS(accordion).disable()** | — | — | Disables the accordion. Calls `jQuery('#{id}').accordion('disable')`. |
| **LTS(accordion).enable()** | — | — | Enables the accordion. Calls `jQuery('#{id}').accordion('enable')`. |

### Usage Example

```php
// Creating an accordion
$myaccordion = LTS::Accordion();

// Adding sections
$section1 = $myaccordion->newsection('Section 1');
$section2 = $myaccordion->newsection('Section 2');

// Adding content
$section1->add(LTS::Div()->capt('Content of the first section'));
$section2->add(LTS::Div()->capt('Content of the second section'));

// Setting parameters
$myaccordion->collaps()
    ->active(1)
    ->heightstyle('auto');

// Hook on predefined client-side panel change method
$myaccordion->openhucks('open');
$myaccordion->js()->add(
<<<JS
    LTS(myaccordion).onopen = function (numb) {
        console.log('Panel #' + numb + ' opened');
    };
JS
);
```

## Notes
*   Class extends `Div`, therefore inherits all Flexbox, child management, and styling methods.
*   Class extends `Element`, `Quark`, `Construct`, therefore supports full object lifecycle and hooks.
*   **jQuery UI Dependency:** Accordion uses jQuery UI Accordion — requires `jquery-ui.css` and `jquery-ui.js` to be loaded in the `Space`.
*   All configuration methods return `$this` to support fluent interface and method chaining.
*   Sections are created via `newsection()` — each section receives the `'AccordionSection'` class for identification during compilation.
*   During compilation, an `<h3>` header is created for each section — text is taken from `$section->caption`, after which `$caption` is cleared.
*   Headers and sections are added to `$ul` — this is the container for the accordion structure.
*   The `compile()` method generates 3 client methods: `open()`, `disable()`, `enable()` — available via `LTS.get('{id}')`.
*   Accordion options are encoded via `json_encode()` with `JSON_UNESCAPED_UNICODE` — Cyrillic characters in headers are preserved without escaping.
*   Panel numbers in `active()` and `open()` are specified as 1-based — internally converted to 0-based for jQuery UI.
*   The `'collapsible'` mode allows collapsing all panels — by default in jQuery UI one panel is always open.
*   Height style `'auto'` — panels adjust to the tallest content, `'fill'` — fill available space, `'content'` — height based on content.
*   For customizing appearance — use CSS classes `.Accordion`, `.AccordionSection`, `.ui-accordion`.
*   The `open()` method on the client calls `accordion('option', 'active', ...)` — changes active panel without recreation.
*   Methods `disable()` and `enable()` block/unblock interaction — useful for temporary disabling.
*   Class does not implement open/close animations — uses standard jQuery UI animations.
*   Accordion can contain any child elements — `Div`, `Form`, `Input`, `DataTable`, etc.
*   During compilation, sections with empty `$caption` — header is created empty.
