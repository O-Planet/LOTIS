[English](Ether.md) | [Русский](Ether.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Ether Interface

## Purpose
Base interface of the LOTIS framework, defining the lifecycle contract for all system objects. All framework classes implement this interface, which guarantees uniformity of initialization processes, metadata compilation, HTML rendering, and object registration in the universe (Space). The interface describes five key stages of object existence: construction, preliminary processing, metadata creation, adding metadata to the universe, and recursive processing of children.

## Object Lifecycle

All objects in the LOTIS universe go through the following lifecycle:

1. **compile()** — preliminary processing
2. **shine()** — metadata creation
3. **addinspace()** — registration of metadata in the universe
4. **childs()** — processing of child objects

## Methods

| Method | Parameters | Description |
|-------|-----------|----------|
| **__construct** | `string $id = ''` | Object constructor. Accepts a unique identifier. Initializes the object's basic properties before the lifecycle begins. |
| **compile** | — | Preliminary processing stage before creating object metadata and placing it in the universe. Performs validation, preparation of internal data structures, and calculation of derived values before rendering. |
| **create** | `Space $space`, `Element $parent = null` | Full object creation cycle. Coordinates the sequential execution of methods `shine()`, `childs()`, and `addinspace()`. Accepts the Space object for rendering and optionally a reference to the parent element in the hierarchy. |
| **shine** | `Element $parent = null` | Direct creation of object metadata. Does not process child objects. |
| **addinspace** | — | Placement of metadata in the universe. |
| **childs** | — | Recursive processing of children. Initiates creation and registration of all child elements of the current object. Called after `shine()` to maintain the order of nesting in the object tree. |

## Notes
*   All UI component classes (Div, Form, Button, etc.) and functional objects (JS, CSS, Events) implement this interface.
*   The `create()` method is a facade encapsulating the standard sequence: `shine()` → `childs()` → `addinspace()`.
*   Violation of the lifecycle method call order may lead to incorrect client-server binding operation.
