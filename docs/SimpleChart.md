[English](SimpleChart.md) | [Русский](SimpleChart.ru.md)

🚀 [Quick Start](../QuickStart.md)

# SimpleChart Class

## Purpose
A data visualization class for the LOTIS framework, inheriting from `Element`. Designed for creating interactive charts and diagrams based on HTML5 Canvas. Supports multiple visualization types: line charts, bar charts, pie charts, doughnut charts, and stacked pie charts. The class automatically generates client-side JavaScript code for rendering charts, provides methods for dynamic data updates, changing chart types, and managing datasets. Integrates with the LOTIS event system for reacting to data changes.

### Key Features

* **Chart types**: line, bar, pie, doughnut
* **Dynamic data updates**
* **Automatic color generation**
* **Scaling and adaptation**
* **Legend with dataset descriptions**

### Chart Types

* **line** — line chart
* **bar** — bar chart
* **pie** — pie chart
* **doughnut** — doughnut chart
* **stacked-pie** — stacked pie chart

## Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| **$config** | `array` | `private` | Chart configuration. Contains keys: `type` (chart type), `data` (data: labels, datasets), `options` (additional settings). Initialized in constructor with type `'line'` and empty data. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique chart identifier, used for linking with client object in `ltsSimpleChart`. |
| **$tagname** | `string` | `public` | Inherited from `Element`. Set to `'canvas'` in constructor. Defines HTML tag of element during rendering. |
| **$type** | `string` | `public` | Inherited from `Element`. Set to `'html'`. Defines object type for filtering and processing. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Element` constructor, sets `tagname` to `'canvas'`, initializes `$config` with type `'line'`, empty labels and datasets. |

### Initialization Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **type** | `string $type` | `$this` | Sets chart type. Allowed values: `'line'`, `'bar'`, `'pie'`, `'doughnut'`, `'stacked-pie'`. Saves to `$config['type']`. Supports fluent interface. |
| **label** | `string $label` | `$this` | Adds one label to labels array. Adds value to `$config['data']['labels']`. Supports fluent interface. |
| **labels** | `array $array` | `$this` | Sets labels array. Replaces `$config['data']['labels']` with passed array. Uses `array_values()` for key normalization. Supports fluent interface. |
| **autolabels** | `int $min`, `int $max`, `int $step = 1` | `$this` | Generates numeric labels from min to max with specified step. Creates string array via for loop. Calls `labels()` to set. Supports fluent interface. |

### Data Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **dataset** | `mixed $data`, `array $options = []` | `$this` | Adds a dataset. Supports: (1) Numeric array — `[10, 20, 30]`. (2) Associative array — `['Mon' => 10, 'Tue' => 20]` (automatically pads with zeros by labels). Creates dataset object with `data` and `borderWidth`. Colors generated on client if not set. Saves to `$config['data']['datasets']`. Supports fluent interface. |

### Lifecycle

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **compile** | — | — | Generates client JavaScript methods. Encodes `$config` to JSON. Creates JS object with methods: `render()`, `update(data)`, `setType(type)`, `addDataset(dataset)`, `destroy()`. Adds automatic `render()` call after creation. Calls `parent::compile()` at end. |

## Client-Side LTS Object Methods

These methods become available on client via `LTS.get('{id}').chart` or `LTS(var).chart`, where `var` is a PHP variable referencing a `SimpleChart` object instance.

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **LTS(chart).chart.render()** | — | — | Renders chart with current data. Calls `ltsSimpleChart.create()` with ID and configuration. |
| **LTS(chart).chart.update(data)** | `object data` | — | Updates chart data. Calls `ltsSimpleChart.update()` with new data. |
| **LTS(chart).chart.setType(type)** | `string type` | — | Changes chart type. Calls `ltsSimpleChart.setType()` with new type. |
| **LTS(chart).chart.addDataset(dataset)** | `object dataset` | — | Adds new dataset. Calls `ltsSimpleChart.addDataset()`. |
| **LTS(chart).chart.destroy()** | — | — | Destroys chart. Calls `ltsSimpleChart.destroy()`, removes canvas from DOM. |

## ltsChart Object Methods

This object is used as client-side storage for all charts. Its methods integrate with LTS `SimpleChart` objects.

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **ltsSimpleChart.create** | `string id`, `object config` | — | Creates new chart or updates existing. Finds canvas by ID, gets 2D context. Saves config, ctx, canvas to `charts[id]`. Calls `render(id)` for drawing. |
| **ltsSimpleChart.render** | `string id` | — | Renders chart. Clears canvas via `clearRect()`. Computes drawing area size considering padding. Depending on type calls: `drawLine()`, `drawBars()`, `drawPie()`, `drawStackedPie()`. Finally calls `drawLegend()` for all types. |
| **ltsSimpleChart.update** | `string id`, `object newData` | — | Updates chart data. Uses `Object.assign()` to merge new data with existing. Calls `render(id)` for redraw. |
| **ltsSimpleChart.setType** | `string id`, `string type` | — | Changes chart type. Updates `config.type` in storage. Calls `render(id)` for redraw with new type. |
| **ltsSimpleChart.addDataset** | `string id`, `object dataset` | — | Adds new dataset. Adds dataset to `config.data.datasets` via `push()`. Calls `render(id)` for redraw. |
| **ltsSimpleChart.destroy** | `string id` | — | Destroys chart. Finds canvas, removes from DOM via `parentNode.removeChild()`. Removes record from `charts[id]`. |
| **ltsSimpleChart.drawLine** | `ctx`, `data`, `xScale`, `yScale`, `padding`, `chartHeight` | — | Draws line chart. Iterates through data, builds lines via `moveTo()`/`lineTo()`. Adds points at each value via `arc()`. |
| **ltsSimpleChart.drawBars** | `ctx`, `data`, `xScale`, `yScale`, `padding`, `chartWidth`, `chartHeight`, `index`, `total` | — | Draws bar chart. Computes group width and single bar width. Draws rectangles via `fillRect()` and `strokeRect()`. |
| **ltsSimpleChart.drawPie** | `ctx`, `datasets`, `centerX`, `centerY`, `radius`, `totalRadius` | — | Draws pie or doughnut chart. Computes total sum, ring thickness proportional to data. Draws sectors via `arc()`. After drawing, draws white borders between sectors. |
| **ltsSimpleChart.drawStackedPie** | `ctx`, `datasets`, `centerX`, `centerY`, `radius` | — | Draws stacked pie chart. One sector per label, inside sector — parts per each dataset. Computes total sum per label. Draws sector parts via `arc()` with white borders. |
| **ltsSimpleChart.drawLegend** | `ctx`, `datasets`, `padding`, `width` | — | Draws legend for all chart types. Draws colored 15x15px squares with labels. Automatically wraps to new line if space insufficient. |
| **ltsSimpleChart.getColor** | `int index` | `string` | Generates unique color based on index. Uses "golden angle" (137.508°) for maximum diversity. Varies saturation (70-85%) and lightness (55-75%). Returns RGBA string via `hslToRgba()`. |
| **ltsSimpleChart.hslToRgba** | `int h`, `int s`, `int l`, `float a = 0.7` | `string` | Converts HSL to RGBA string. Uses standard conversion algorithm via intermediate p, q values. Returns string like `rgba(r, g, b, a)`. |
| **ltsSimpleChart.hue2rgb** | `float p`, `float q`, `float t` | `float` | Helper function for hue to RGB conversion. Handles ranges t < 0, t > 1, t < 1/6, t < 1/2, t < 2/3. Returns channel value 0-1. |

## Usage Examples

```php
// Creating line chart
$chart = new SimpleChart('myChart');
$chart->type('line')
    ->autolabels(1, 12, 1) // labels from 1 to 12 with step 1
    ->dataset([25, 30, 35, 40], ['borderColor' => 'red'])
    ->dataset([20, 22, 24, 26], ['borderColor' => 'blue']);

// Creating pie chart
$chart = new SimpleChart('pieChart');
$chart->type('pie')
    ->dataset([30, 40, 20, 10]);

// Creating bar chart
$chart = new SimpleChart('barChart');
$chart->type('bar')
    ->labels(['January', 'February', 'March'])
    ->dataset(['January' => 100, 'February' => 150, 'March' => 200]);
```

## Notes
*   Class extends `Element`, therefore inherits all methods for managing children, styles, attributes, and events.
*   Class extends `Quark` and `Construct`, therefore supports full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks (`$check`, `$before`, `$on`, etc.).
*   **SimpleChart.js dependency:** For proper client method functionality, `SimpleChart.js` file must be included, which contains `ltsSimpleChart` object implementation. PHP class `SimpleChart` only generates configuration and method calls.
*   All chart configuration methods return `$this` to support fluent interface and chained calls.
*   Chart types: `'line'` (line), `'bar'` (bar), `'pie'` (pie), `'doughnut'` (doughnut), `'stacked-pie'` (stacked pie).
*   `dataset()` method automatically maps associative arrays to labels — if key not found in labels, value padded with zero.
*   Dataset colors auto-generated via `getColor()` if not set in `$options` — uses "golden angle" for maximum diversity.
*   `autolabels()` method useful for numeric axes — generates sequence with specified step (e.g., 0 to 100 with step 10).
*   `compile()` method generates 5 client methods via `compilemethod()` — all available via `LTS.get('{id}').chart`.
*   During compilation, automatic `render()` call added — chart renders immediately after DOM load.
*   `update()` method uses `Object.assign()` for data merging — does not fully replace configuration, only updates specified fields.
*   `destroy()` method removes canvas from DOM — after call, chart cannot be restored without recreating object.
*   Legend drawn for all chart types — automatically positioned at top of canvas with line wrapping.
*   For line and bar charts, X and Y axis scales auto-computed — max value rounded up to nearest 10.
*   For pie charts, ring thickness proportional to sum of data in each dataset — visualizes each dataset's contribution.
*   For stacked pie charts, one sector per label — inside sector, parts proportional to dataset values.
*   `getColor()` method generates colors via HSL — ensures even distribution across color wheel.
*   HSL to RGBA conversion via `hslToRgba()` — supports standard algorithm with intermediate p, q values.
*   Canvas cleared before each redraw via `clearRect()` — prevents chart overlay.
*   Default padding 50px — provides space for axes, labels, and legend.
*   For bar charts, group width computed as 80% of available width — 20% remains for margins.
*   For line charts, points drawn with 4px radius — filled with line color.
*   Borders between pie chart sectors drawn with white lines 2px thick — for clarity.
*   Borders between stacked chart sectors drawn with black lines 1px thick — for part separation.
*   Legend wraps to new line if `x > width - 100` — shifts down 30px via `translate()`.
*   `render()` method checks for chart in `charts[id]` — prevents errors on non-existent ID.
*   When creating chart, canvas must exist in DOM — otherwise error logged to console.
*   2D context obtained via `getContext('2d')` — requires HTML5 Canvas support.
*   `setType()` method allows dynamic chart type change — useful for switching between visualization types.
*   `addDataset()` method adds dataset to end of array — order affects legend display.
*   For stacked charts, total sum computed per label — sector proportional to label's contribution to total.
*   `drawPie()` method computes sector boundaries before drawing — then draws white lines for clarity.
*   For doughnut charts, inner radius computed as `radius - totalRadius` — creates ring effect.
*   `drawLegend()` method uses 14px sans-serif font — can be overridden via CSS.
*   Legend colors taken from `dataset.borderColor` or generated via `getColor()` — ensures matching.
*   For line charts, empty values treated as 0 — can be modified in `drawLine()`.
*   For bar charts, multiple datasets displayed side-by-side in group — do not overlap.
*   `update()` method does not validate data — responsibility lies with developer.
*   When canvas size changes, chart does not auto-redraw — requires explicit `render()` call.
*   `dataset()` method with `$options` allows color override — `borderColor`, `backgroundColor`, `borderWidth`.
*   For background transparency, `backgroundColor` with alpha channel used — generated via `hslToRgba()` with `a = 0.7`.
*   Class does not implement animations — for animated transitions, use custom logic in `update()`.
*   `render()` method called automatically on creation — no explicit call required for initial draw.
