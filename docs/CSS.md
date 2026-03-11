[English](CSS.md) | [Русский](CSS.ru.md)

🚀 [Quick Start](../QuickStart.md)

# CSS Class

## Purpose
Style management class of the LOTIS framework, inheriting from `Quark`. Designed for dynamic generation and connection of CSS rules to application objects. Supports two modes of operation: embedding inline styles directly into the HTML document and connecting external CSS files via the `<link>` tag. All styles defined through the CSS object are automatically added to the namespace (Space) during rendering and applied to elements with the corresponding ID-class. The class works as a container — style rules are added to its storage as children.

## Properties

### Inherited Properties

| Property | Type | Description |
|----------|-----|----------|
| **$id** | `string` | Inherited from `Construct`. Unique identifier, used as CSS class for style selector. |
| **$type** | `string` | Set to `'CSS'` in the constructor. Defines the object type for filtering and processing. |
| **$tagname** | `string` | Inherited from `Quark`. Not used for CSS objects (empty string). |
| **$classname** | `string` | Inherited from `Quark`. CSS class of the object, can be used as a selector for styles. |
| **$caption** | `string` | Inherited from `Quark`. Not used for CSS objects. |
| **$storage** | `array` | Inherited from `Quark`. Storage contains sections `'childs'` (style rules) and `'element'` (DOM element). |

## Methods

The class has no own methods. For assigning styles, the `add` method inherited from Quark is used. For assigning styles to a specific object, a child of Element, the built-in `css()` method is used, returning a reference to an instance of the CSS class bound to that object.

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Quark` constructor, sets `$type` property to `'CSS'`. Initializes storage with sections `'childs'` and `'element'`. `$id` is used for setting styles for the class |
| **shine** | `Element $parent = null` | — | Generation of CSS elements. Iterates through children in the `'childs'` section. If value contains `'.css'` — creates an entry for the `<link>` tag with an external file. Otherwise — forms an array of inline styles. Adds generated metadata to `$space->storage['elements']` for subsequent rendering in Space. |
| **compile** | — | — | Empty implementation. Inherited from `Construct`, overridden without logic — CSS objects do not require preliminary metadata compilation. |
| **childs** | — | — | Empty implementation. Inherited from `Construct`, overridden without logic — CSS objects do not create child DOM elements in the traditional sense. |

## Usage Examples

```php
// Connecting styles
$myobject->css()->add('styles.css');

// Adding styles to a specific object:
// Option 1
$myobject->css('background-color', '#777777')
  ->css('border', '1px #ffffff solid')
  ->css('float', 'left');

// Option 2
$myobject->css()->add('background-color', '#777777')
  ->add('border', '1px #ffffff solid')
  ->add('float', 'left');

// Option 3
$myobject->css()->add(
<<<CSS
  background-color: #777777;
  border: 1px #ffffff solid;
  float: left;
CSS
);

// Defining styles by selector
$myobject->css('input .myclass[type=text]')->add('color', '#ff0000')
  ->add('font-weight', 'bold');
```

## Notes
*   Class extends `Quark`, therefore supports all child management methods: `add()`, `get()`, `set()`, `del()`, `getchilds()`, etc.
*   Style rules are added via the `add()` method as key-value pairs, where key is the CSS property name, value is its value.
*   External CSS files are determined by the presence of the `'.css'` substring in the value — such entries are processed separately and generate `<link rel="stylesheet">` tags.
*   For defining styles of specific objects, the `css` method inherited from the `Element` class must be used.
*   If the object `$id` is specified — it is used as a selector in the style definition.
*   When a selector is specified in the style definition, the style is not bound to the object in whose composition it is defined, but is bound only to the specified selector.
*   CSS objects have no visual representation in the DOM — they only generate entries in the namespace for insertion into `<head>` or `<style>`.
*   Methods `compile()` and `childs()` are overridden as empty — the CSS object lifecycle is limited to the `shine()` stage.
*   All styles added through the CSS object are applied globally within the current space (Space).
*   Class does not implement client-side style manipulation logic — only server-side HTML/CSS generation. For dynamic style changes on the client, the `JS` class is used.
