[English](Element.md) | [Русский](Element.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Element Class

## Purpose
Base class of all UI elements of the LOTIS framework, inheriting from `Quark`. Represents a wrapper over HTML elements with extended capabilities for managing styles, attributes, events, and client-side logic. Is the parent for all visual components (Div, Span, Form, Button, Input, etc.). Implements a mechanism for generating HTML markup metadata via the `shine()` method, supports CSS class management, inline styles, HTML attributes, and provides methods for assigning event handlers and signals. The class includes a hook system for injecting custom logic into object methods.

## Properties

| Property | Type | Description |
|----------|-----|----------|
| **$tagname** | `string` | Name of the HTML tag that will be created by the `shine()` method. Default `'div'`. Overridden in child classes (e.g., `'span'`, `'button'`, `'input'`). |
| **$classname** | `string` | CSS classes of the object. Supports multiple classes separated by space. Managed by methods `addclass()`, `removeclass()`, `hasclass()`. |
| **$caption** | `string` | Text content of the element. Displayed as text inside the tag or as the value of the `value` attribute for form elements. |
| **$type** | `string` | Set to `'html'` in the constructor. Defines the object type for filtering and processing. |
| **$hucksenable** | `bool` | Flag activating the hook system for predefined client methods of the object. If `true` — hooks `check`, `before`, `on` are generated for all methods. Default `false`. To change the flag, use the `openhucks` method. |

### Inherited Properties

| Property | Type | Description |
|----------|-----|----------|
| **$id** | `string` | Inherited from `Construct`. Unique identifier, used as HTML `id` attribute and for linking with the client LTS object in `lts.js`. |
| **$storage** | `array` | Inherited from `Quark`. Storage contains sections: `'childs'` (children), `'element'` (DOM element), `'attr'` (HTML attributes), `'options'` (options), `'openedhucks'` (allowed hooks). |
| **$jscreate** | `JS/null` | Inherited from `Quark`. Reference to the JavaScript object for client-side logic of the create section. |
| **$space** | `Space` | Inherited from `Construct`. Reference to the namespace where generated elements are added. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. If ID starts with `'.'` — extracts the class from the ID, sets an empty ID, and adds the class via `addclass()`. Otherwise — uses the ID as the identifier. Sets `$tagname` to `'div'`, `$type` to `'html'`. Calls parent `Quark` constructor. |

### Element Creation and Configuration

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **capt** | `string $capt` | `$this` | Sets the text content of the element (`$caption`). Supports fluent interface. |
| **tag** | `string $tagname` | `$this` | Changes the HTML tag name of the element. Allows dynamically changing the element type before rendering. Supports fluent interface. |
| **css** | `string $name = ''`, `mixed $value = null` | `CSS/$this` | Manages element styles. If both parameters are passed — adds a CSS property via the CSS object. If only the name is passed — returns the existing CSS object or creates a new one. Default selector — `#{$this->id}`. |
| **gridarea** | `string $name` | `$this` | Sets the CSS property `grid-area` for the element. Used when placing objects in Grid containers. Supports fluent interface. |
| **display** | `string $name = 'block'` | `$this` | Sets the CSS property `display`. Default `'block'`. Supports fluent interface. |

### Attribute Management

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **option** | `string $name`, `mixed $val = null` | `mixed/$this` | Manages element options. If `$val` is not passed — returns the option value. If passed — sets the value in the `'options'` section. |
| **attr** | `string $name`, `mixed $val = null` | `mixed/$this` | Manages HTML attributes. If `$val` is not passed — returns the attribute value. If passed — sets the attribute in the `'attr'` section. Supports fluent interface. |
| **removeattr** | `string $name` | `$this` | Removes an HTML attribute by name. Finds the attribute in the `'attr'` section and removes it via `unset()`. Supports fluent interface. |

### Dimensions and Positioning

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **left** | `mixed $l` | `$this` | Sets the CSS property `left`. Supports fluent interface. |
| **top** | `mixed $t` | `$this` | Sets the CSS property `top`. Supports fluent interface. |
| **width** | `mixed $w` | `$this` | Sets the CSS property `width`. Supports fluent interface. |
| **height** | `mixed $h` | `$this` | Sets the CSS property `height`. Supports fluent interface. |

### Events and Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **click** | `string $body` | `$this` | Registers a `click` event handler. Is a wrapper over the `on('click', $body)` method. Supports fluent interface. |
| **on** | `string $func`, `string $body` | `$this` | Registers an event handler. Generates JavaScript code for the event trigger via jQuery. Adds code to the JS object with the `'ready'` area. Supports fluent interface. |
| **openhucks** | `string $names = null` | `$this` | Activates the hook system for predefined client methods of the object. If `$names` is not passed — enables hooks for all predefined client methods. If passed — enables hooks only for the specified names (comma-separated list). Supports fluent interface. |
| **method** | `string $func`, `string $body` | `$this` | Adds a custom method on the client. Parses the function signature (name and parameters), generates JavaScript code with support for `check`, `before`, `on` hooks. The method becomes available via `LTS.get('{id}').{funcname}()` or via `LTS({var}).{funcname}()`, where var is a PHP variable referencing the current object. |
| **compilemethod** | `string $func`, `string $body = null`, `JS $js = null` | `JS` | Internal method for compiling predefined client methods of the object considering the hook system. Checks the `$hucksenable` flag and the `$openedhucks` list. If hooks are enabled — generates code with checks and hooks. Otherwise — generates a simple method without hooks. |
| **signal** | `string $name`, `string $handler`, `Element $sender = null` | `$this` | Subscribes the object to a signal. If `$sender` is not specified — subscribes to the signal with name `$name`. If specified — subscribes to the signal `{$sender->id}_{$name}`. Generates JavaScript code via `LTS.get('{id}').signal()`. Supports fluent interface. |

### Class Management

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **addclass** | `string $classname`, `bool $toend = true` | `$this` | Adds CSS classes to the element. Splits the string into individual classes, checks the presence of each via `hasclass()`, adds missing ones. If `$toend == true` — adds to the end of the list, otherwise — to the beginning. Supports multiple classes separated by space. |
| **hasclass** | `string $classname` | `bool` | Checks if the element has a CSS class. Splits `$classname` into an array and checks inclusion via `in_array()`. Returns `true` if the class is found. |
| **removeclass** | `string $classname` | `$this` | Removes CSS classes from the element. Splits the string into individual classes, finds each in the `$classes` array and removes via `unset()`. After removal, assembles the array back into a string. Supports multiple classes separated by space. |

### Helper Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **shine** | `Element $parent = null` | — | Generates HTML element metadata. Fills the `'element'` section of the `storage` with data for rendering: type (`type`), attributes (`attr`), tag (`tagname`), ID (`id`), parent (`parent`), classes (`class`), content (`caption`). Attributes are processed with escaping via `htmlspecialchars()`. Boolean attributes (`true`/`false`) are handled in a special way. |
| **_new** | `string $type`, `string $id = ''` | `Construct` | Factory method for creating a child object of the current type. Calls the global function `\LTS::_new()` passing the current object as the parent. |

## Usage Example

```php
// Creating and configuring a basic element
$myelement = LTS::Element()
    ->tag('ul')
    ->width('300px')
    ->height('200px')
    ->addclass('my-class important');

// Adding children
$myelement->add(LTS::Element()
        ->tag('li')
        ->capt('About Us')
        ->click(
<<<JS
          window.location = 'about.php';
JS
        )
    )
    ->add(LTS::Element()
        ->tag('li')
        ->capt('Help')
        ->click(
<<<JS
          window.location = 'help.php';
JS
        )
    );

// Setting styles
$myelement->css()->add('background-color', '#ffffff')
    ->add('border', 'solid 1px #777777');

// Adding a method
$myelement->method('show(param)',
<<<JS
    $(myelement).css('display', param ? 'block', 'none');
JS
);

// Subscribing to a signal and using the client method
$myelement->signal('hideAll',
<<<JS
    LTS(myelement).show(false);
JS
);
```

## Notes
*   Class extends `Quark`, therefore supports all child management methods: `add()`, `get()`, `set()`, `del()`, `addin()`, `move()`, `getchilds()`, etc.
*   Class extends `Construct`, therefore supports all lifecycle hooks: `$check`, `$before`, `$on`, `$checkchilds`, `$beforechilds`, `$onchilds`.
*   The `css()` method automatically creates a `CSS` object with the selector `#{$this->id}` if it doesn't exist yet — this allows adding styles to the element in a chain.
*   The `signal()` method uses the LOTIS client-side signal system via `LTS.onSignal()` and `LTS.signal()`.
*   Class management methods (`addclass`, `removeclass`, `hasclass`) work with the `$classname` string — multiple classes are separated by space.
*   The `shine()` method does not create a DOM element directly — it fills the `'element'` section with metadata, which is then processed by the `Space` class during HTML rendering.
*   Attributes with value `false` are ignored when generating HTML.
*   Attributes with value `true` are added as boolean (without a value), e.g., `disabled`, `readonly`, `checked`.
*   All style, attribute, and class management methods return `$this` to support fluent interface.
*   Child classes (Div, Span, Button, Form, etc.) override `$tagname` and add specific methods, but inherit all base functionality of `Element`.
*   The `_new()` method allows creating child objects in the context of the current parent without explicitly specifying the namespace.
*   `Element` objects are automatically registered in the namespace via the inheritance mechanism of `addinspace()` from the `Quark` class.
*   Class does not implement `compile()` and `childs()` methods — they are inherited from `Quark` without changes.
*   During rendering, attributes are escaped via `htmlspecialchars()` to protect against XSS attacks.
