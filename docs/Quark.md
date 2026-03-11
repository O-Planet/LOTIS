[English](Quark.md) | [Русский](Quark.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Quark Class

## Purpose
Base class of the LOTIS framework, inheriting from `Construct`. Represents a container object with extended capabilities for managing child elements and internal data storage. Is the parent for all UI components capable of containing other objects (Div, Form, Grid, Tabs, etc.). Implements a mechanism for archiving children by sections, provides methods for searching, filtering, moving, and managing child elements. Also responsible for generating and managing client-side JavaScript code associated with the object.

## Properties

| Property | Type | Description |
|----------|-----|----------|
| **$tagname** | `string` | Name of the HTML tag that will be created by the `shine()` method. Default is an empty string — overridden in child classes. |
| **$classname** | `string` | CSS class of the object. Used for styling the element during rendering. |
| **$caption** | `string` | Text label of the object. May be displayed as a title, label, or element content. |
| **$storage** | `array` | Multi-dimensional array for storing the object's internal data. Contains sections: `'childs'` (children), `'element'` (DOM element), as well as user-defined sections. |
| **$jscreate** | `JS/null` | Reference to a JS object whose scripts should be placed in the ready section of the client application's scripts, guaranteed after the creation and initialization of the current object. Initialized lazily upon first access via the `js('create')` method. |

## Methods

### Object Management Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Construct` constructor, initializes storage sections `'childs'` and `'element'` as empty arrays. |

#### Basic Operations  

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **setid** | `string $id` | `$this` | Sets a new object identifier. Supports fluent interface. |
| **set** | `string $section`, `string $name`, `mixed $value = null` | `$this` | Adds data to the specified storage section. If `$name` is specified but `$value` is not passed — adds `$name` as an array element. If both are passed — creates a key-value pair. If the section does not exist — creates it. |
| **get** | `string $section`, `string $name = null` | `mixed/false` | Gets data from storage. If `$name` is not specified — returns the entire section (array). If specified — returns the value by key. Returns `false` if the section or key is not found. |
| **add** | `string $name`, `mixed $value = null` | `$this` | Adds an object to the `'childs'` section. Is a wrapper over `set('childs', ...)`. Supports fluent interface. |
| **addmany** | `...$values` | `$this` | Adds multiple children simultaneously. Accepts a variable number of arguments, each of which is added via `add()`. |
| **del** | `mixed $child` | `$this` | Removes a child from the `'childs'` section. Accepts an ID (string) or an object. If an object is passed — extracts its ID. Finds the object in the array and removes it by key. |
| **addin** | `mixed $pos`, `string $name`, `mixed $value = null` | `$this` | Adds a child to a specific position. `$pos` can be: an integer (1-based index), an object (insertion before it), or a string (object ID). If the position is incorrect — adds to the end. Rebuilds the children array accounting for the new position. |

### Child Element Management

#### Search and Filtering

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **child** | `string $id` | `Construct/false` | Searches for and returns the first child with the specified ID. Performs a linear search through the `'childs'` section. Returns `false` if not found. |
| **getchild** | `mixed $type`, `mixed $par = null` | `Construct/false` | Returns the first child of the specified type with an optional filter. Is a wrapper over `getchilds()` with the result limited to 1 element. |
| **getchilds** | `mixed $type`, `mixed $par = null`, `int $kol = 0` | `array/false` | Returns an array of children of the specified type with filtering. `$type` — string or array of types (checked by the `$type` property). `$par` — filter: `null` (no filter), array (checking properties/attributes), callable (user-defined function). `$kol` — quantity limit (0 — no limit). |
| **move** | `Quark $newparent`, `mixed $obj` | `$this` | Moves a child to a new parent. Accepts the parent object and the child (ID or object). Adds the child to `$newparent`, removes from the current storage. If the object is not found in the children but is passed as an object — simply adds it to the new parent. |

### JavaScript Integration

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **js** | `string $area = 'script'` | `JS` | Returns or creates a JavaScript object for the specified area. Checks for existing JS objects among children. For the `'create'` area — lazily creates `$jscreate` with the `'ready'` area. For other areas — creates a new `JS` object, adds it to children, and returns it. |

### Lifecycle

#### Main Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **childs** | — | — | Recursive creation of children. Iterates through the `'childs'` section, for each object of type `Construct` with the flag `$shineobject == true` calls `create()` passing the current space and parent ID. |
| **compile** | — | — | Preliminary compilation of children. Calls `compile()` for all children having this method. If `$jscreate` exists — adds it to children, sets the `'ready'` area, and compiles. |
| **addinspace** | — | — | Registration of the DOM element in the namespace. Gets the element from the `'element'` section, if the array is not empty — registers it in `$space` in the `'elements'` section with the key `$this->id`. |

### Storage Structure

**$storage** contains the following sections:

* **childs** — collection of child elements
* **element** — information about the element being created
* User-defined sections for data storage
 
## Usage Example

```php
// Simple stack. Values placed in it on the server
// are transferred to the corresponding LTS object on the client in the stack property
class Stack extends Quark
{
    public top = 0;  // Pointer to the top of the stack
    public function __construct($id = '')
    {
        parent::__construct($id);
        // Create storage for stack elements, the items section in storage
        $this->set('items');
        // Set a separate type for our object
        $this->type = 'Stack';
    }

    // Add value to the top of the stack
    public function push($item)
    {
        $this->set('items', ++$this->top, $item);
        return $this;
    }

    // Return value from the top of the stack
    public function pop()
    {
        if($this->top === 0)
          return null;
        return $this->get('items', $this->top--); 
    }

    public function compile()
    {
        // Form the string of stack values
        if($this->top === 0)
          $stack = '[]';
        else
          $stack = json_encode(array_slice($this->get('items'), 0, $this->top));
        
        // Initialize the LTS object on the client.
        // Place in the stack property an array of values added to the stack on the server.
        // Create two client methods for working with the stack.
        $this->js()->add(
<<<JS
        LTS({$this->id}).stack = {$stack};
        LTS({$this->id}).push = function (item) { this.stack.push(item); return this; }; 
        LTS({$this->id}).pop = function () { return this.stack.pop(); }; 
JS
        );
        parent::compile();
    }
}
```

## Notes
*   Class extends the functionality of `Construct`, adding the ability to store and manage collections of child objects.
*   Class is recommended as a parent for non-visual components requiring storage.
*   The `$storage` is organized by sections — this allows separating children, DOM elements, and user data.
*   The `getchilds()` method supports three types of filtering: by object type, by properties/attributes, and by a user-defined predicate function.
*   The `addin()` method implements complex insertion logic with array rebuilding — allows precise control over the order of children in the DOM.
*   JavaScript objects created via `js()` are automatically added to children and compiled together with the parent.
*   The special `$jscreate` object for the `'create'` area is created lazily — only upon first access, which optimizes memory usage.
*   All child management methods (`add`, `del`, `addin`, `move`) return `$this` to support fluent interface.
*   The `addinspace()` method registers only DOM elements, not all children — registration of children occurs during their own `addinspace()` call.
*   Class does not implement the `shine()` method — this responsibility is delegated to child classes (Div, Form, Button, etc.).
*   When moving an object via `move()`, the reference to the object itself is preserved — only the ownership in the storage changes.
