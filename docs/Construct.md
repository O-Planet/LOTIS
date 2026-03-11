[English](Construct.md) | [Русский](Construct.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Construct Class

## Purpose
Base class of the LOTIS framework, implementing the `Ether` interface. Is the parent for all system objects (UI components, functional classes, domain objects). Implements the full object lifecycle: from metadata construction to registration in the universe (Space). Provides a hook mechanism for injecting custom logic at key stages of object creation and its children. All framework classes inherit from `Construct`, ensuring architectural uniformity and rendering processes.

## Properties

### Main Properties

| Property | Type | Description |
|----------|-----|----------|
| **$space** | `Space` | Reference to the universe object (Space) in which the current object is created. Provides access to the global application context. |
| **$id** | `string` | Unique object identifier. Used for generating the HTML `id` attribute, CSS selectors, and linking with the client-side representation: `LTS-object`. **IMPORTANT!** If the object id is not explicitly set in the constructor, it will be assigned a unique id automatically. |
| **$type** | `string` | Object type. Determines behavior during rendering. The value `'none'` indicates an abstract object without visual representation. |
| **$shineobject** | `bool` | Flag indicating the necessity of creating the object via the create method with all its children and placing it in the universe. |
| **$owner** | `string/null` | Identifier of the overridden owner. Allows changing the parent in the DOM hierarchy compared to the creation context. |
| **$spaceid** | `static int` | Static counter for automatic generation of unique object IDs. |

### Lifecycle Events

| Property | Type | Description |
|----------|-----|----------|
| **$check** | `callable/null` | Hook-validator. Called before creating the element via the create method. If returns `false`, the creation process is interrupted. |
| **$before** | `callable/null` | Pre-processing hook. Executed before rendering the element (method `shine()`). |
| **$on** | `callable/null` | Post-processing hook. Executed after rendering the element, before registration in the universe. |
| **$checkchilds** | `callable/null` | Hook-validator for children. If returns `false`, creation of child objects is skipped. |
| **$beforechilds** | `callable/null` | Pre-processing hook for children. Executed before recursive creation of child objects. |
| **$onchilds** | `callable/null` | Completion hook. Executed after adding all children to the universe. |

## Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. If ID is not passed, generates a unique identifier of the form `'O' + number` based on the static counter `$spaceid`. Initializes default properties. |
| **compile** | — | — | Preliminary metadata compilation stage. Empty in the base implementation, overridden in child classes for preparing data before rendering. |
| **create** | `Space $space`, `Element $parent = null` | `$this` | Main lifecycle method. Coordinates the sequence: check `$check` → hook `$before` → `shine()` → hook `$on` → `addinspace()` → check `$checkchilds` → hook `$beforechilds` → `childs()` → hook `$onchilds`. Returns `$this` to support fluent interface. |
| **shine** | `Element $parent = null` | — | Creation of object metadata. Empty in the base implementation, overridden in child classes. |
| **childs** | — | — | Recursive creation and registration of child objects. Empty in the base implementation, overridden in containers (Div, Form, Grid, etc.). |
| **addinspace** | — | — | Registration of the object in the universe. |

## Usage Recommendations

When overriding the class, it is necessary to:
* Implement the shine() method for creating a specific element
* Override childs() if necessary for working with children
* Set up lifecycle hooks through handler properties
* Consider the order of method execution

## Typical Usage Scenarios

```php
class MyComponent extends Construct
{
    public __construct($id = '')
    {
        // Code before calling the base class constructor
        parent::__construct($id);
        // Code after calling the base class constructor
    } 
  
    public function shine($parent)
    {
        // Logic for creating object metadata
    }

    public function childs()
    {
        // Logic for processing child objects
    }

    public function addinspace()
    {
        // Logic for placing metadata in the universe
    }
}
```

## Notes
*   Class uses the `TypeChecker` trait for data type validation.
*   The `create()` method is the entry point for launching the object lifecycle. Direct calls to `shine()`, `childs()`, or `addinspace()` are not recommended.
*   Hooks (`$check`, `$before`, `$on`, etc.) can be assigned as PHP functions or anonymous functions (closures).
*   All hooks execute without parameters — data is passed through the object context (`$this`).
*   When `$type == 'none'`, the object is not rendered, but can perform functional logic (e.g., `JS`, `CSS`, `Events`).
*   The `$owner` property allows implementing complex nesting scenarios when the physical parent in the DOM differs from the logical owner in the code.
