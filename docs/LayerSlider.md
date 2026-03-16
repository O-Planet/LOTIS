[English](LayerSlider.md) | [Русский](LayerSlider.ru.md)

🚀 [Quick Start](../QuickStart.md)

# LayerSlider Class

## Purpose
Layer switching component (slider) of the LOTIS framework, inheriting from `Div`. Designed for organizing interfaces with sequential content display (layers), where only one active layer is visible at any given time. Supports programmatic switching (methods `next`, `prev`, `goto`), swipe gestures (mouse/touch), looping transitions, and a visual indicator of the current position. Used for step-by-step forms, galleries, tab switching in interfaces, and master processes.

### Main Features

* **Navigation between content layers**
* **Swipe support** for switching
* **Indicators** of the current layer
* **Display configuration** of the active layer
* **Cyclic playback**

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$layers** | `array` | `private` | Array of child identifiers recognized as layers. Filled automatically by the `add()` method. |
| **$captions** | `array` | `private` | Array of layer captions. Used for generating indicator labels. Filled from `$child->caption` when adding a layer. |
| **$loop** | `bool` | `private` | Flag for looping transitions. If `true` — transition from the last layer to the first and vice versa. Default `true`. |
| **$swipeSensitivity** | `int` | `private` | Minimum cursor/finger displacement in pixels for registering a swipe. Default `50`. |
| **$displayStyle** | `string` | `private` | CSS display style of the active layer. Default `'block'`. Valid: `'block'`, `'flex'`, `'inline-block'`, `'grid'`. |
| **$activeLayer** | `mixed` | `private` | Initially active layer. Can be a layer object, its ID (string), or number (int, 1-based). Default `1` (first layer). |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique identifier of the slider. |
| **$tagname** | `string` | `public` | Inherited from `Div`. Default `'div'`. |
| **$classname** | `string` | `public` | Inherited from `Element`. The class `'LayerSlider'` is automatically added. |
| **$storage** | `array` | `public` | Inherited from `Quark`. Storage of children and metadata. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Div` constructor, adds the `'LayerSlider'` class, sets CSS `position: relative` and `overflow: hidden`. |

### Content Addition

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **add** | `mixed $name`, `mixed $value = null` | `mixed` | Overridden method for adding a child. If an object of type `html` with tag `div` is added — automatically adds the `'Layer'` class, saves the ID in `$layers`, the caption in `$captions`. Calls parent `add()`. |

### Configuration Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **active** | `mixed $child` | `$this` | Sets the initially active layer. Accepts a layer object, ID string, or number (1-based). Saves in `$activeLayer`. Supports fluent interface. |
| **loop** | `bool $enable = true` | `$this` | Enables or disables looping of layer transitions. Sets `$loop`. Supports fluent interface. |
| **sensitivity** | `int $px` | `$this` | Sets swipe sensitivity in pixels. Sets `$swipeSensitivity`. Supports fluent interface. |
| **setdisplay** | `string $style = 'block'` | `$this` | Sets the CSS display style of the active layer (`display`). Checks valid values. Sets `$displayStyle`. Supports fluent interface. |
| **createIndicator** | `string $css = null` | `Div` | Creates a visual indicator (dots) for layer switching. Creates a `Div` object with class `'layer-indicator'`, generates dot-buttons for each layer. Generates the JS method `updateIndicator()` for highlighting the active dot. Returns the indicator object. |

### Lifecycle

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **compile** | — | — | Generation of client-side JavaScript methods. Determines the index of the active layer. Hides inactive layers via CSS (`display: none`), the active one — via `$displayStyle`. Generates methods: `next()`, `prev()`, `goto(index)`, `_fadeTransition(from, to)`. Adds event handlers `mousedown`/`touchstart` and `mouseup`/`touchend` for implementing swipe. Blocks text selection (`selectstart`). Calls `parent::compile()`. |

## LTS Object Client Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **LTS(slider).next()** | — | — | Switches to the next layer. If `$loop == true` — after the last comes the first. Launches the `_fadeTransition()` animation. Calls `updateIndicator()`. |
| **LTS(slider).prev()** | — | — | Switches to the previous layer. If `$loop == true` — before the first comes the last. Launches the `_fadeTransition()` animation. Calls `updateIndicator()`. |
| **LTS(slider).goto(index)** | `int index` | — | Switches to the layer by index (0-based). Checks array bounds. Launches the `_fadeTransition()` animation. Calls `updateIndicator()`. |
| **LTS(slider)._fadeTransition(fromIndex, toIndex)** | `int fromIndex`, `int toIndex` | — | Internal animation method. Smoothly changes opacity (`opacity`) from 1 to 0 for the old layer and from 0 to 1 for the new one. Uses jQuery `animate()` with 300ms duration. |
| **LTS(slider).updateIndicator()** | — | — | Updates the indicator state (if created). Highlights the active dot (changes `background`). Called automatically on switching. |

## Usage Examples

```php
// Creating a slider
$slider = LTS::LayerSlider();

// Adding layers
$slider->add(LTS::Div('layer1')->capt('Layer 1 content'))
       ->add(LTS::Div('layer2')->capt('Layer 2 content'))
       ->add(LTS::Div('layer3')->capt('Layer 3 content'));

// Setting parameters
$slider->active('layer2')
       ->loop(true)
       ->sensitivity(50)
       ->setdisplay('flex');

// Creating an indicator
$slider->createIndicator();
```

## Notes
*   Class extends `Div`, therefore inherits all Flexbox methods, child management, and styling.
*   Class extends `Element`, `Quark`, `Construct`, therefore supports full object lifecycle and hooks.
*   **Layer recognition:** Only children of type `html` with tag `'div'` are automatically considered layers (the `'Layer'` class is added, ID is saved in `$layers`). Other children are ignored by the slider.
*   **Positioning:** All layers automatically get CSS `position: absolute`, `top: 0`, `left: 0`, `width: 100%` — overlaid on top of each other.
*   **Animation:** Switching is implemented through changing `opacity` (fade effect) with 300ms duration via jQuery `animate()`.
*   **Swipe:** Handlers `mousedown`/`touchstart` and `mouseup`/`touchend` are registered on the slider container. A swipe is registered if `deltaX > sensitivity` and `deltaY < 50`.
*   **Indicator:** The `createIndicator()` method creates a separate `Div` with dots. Each dot generates a click calling `LTS(slider).goto(index)`.
*   **Looping:** Controlled by the `$loop` flag. If `false` — the `next()` and `prev()` methods stop at array boundaries.
*   **Active layer:** The index of the active layer is calculated in `compile()` based on `$activeLayer` (object, ID, or number). By default, the first layer is active (index 0).
*   **Styles:** The base `'LayerSlider'` class gets `position: relative`, `overflow: hidden`. The `'Layer'` class is automatically added to all layers.
*   **Dependency:** jQuery is required for animation (`animate()`) and event handling.
*   **JS Generation:** Initialization code and methods are generated in the `'ready'` area via `js('ready')`.
*   **Selection blocking:** A `selectstart` handler with return `false` is added — prevents text selection during swipe.
*   **goto() method:** Accepts a 0-based index. If the index is out of range — switching does not occur.
*   **updateIndicator() method:** Generated only if `createIndicator()` was called. Checks for the presence of element `#{$id}_indicator`.
*   **Hidden layers:** Inactive layers get `display: none` during compilation. The active one — `$displayStyle` (default `block`).
*   **JS Context:** Methods `next`, `prev`, `goto` are available via `LTS(layerslider)`.
*   **Sensitivity:** The `$swipeSensitivity` parameter defaults to 50px — can be changed via the `sensitivity()` method.
*   **Display:** The `$displayStyle` parameter controls the CSS `display` of the active layer — important for flex/grid layers.
*   **Identification:** Layers are identified by IDs, which are saved in the `$layers` array in the order of addition.
*   **Captions:** Layer captions (`$caption`) are used for generating `title` or indicator labels (in the current implementation — only for the `$captions` array).
