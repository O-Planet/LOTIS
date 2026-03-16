[English](Input.md) | [Русский](Input.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Input Class

## Purpose
Form input element representation class of the LOTIS framework, inheriting from `Element`. Designed for creating all types of HTML form elements: text fields, passwords, checkboxes, radio buttons, dropdown lists, text areas, buttons, and HTML5 input types (email, tel, url, date, color, time, number, range). The class automatically generates associated label elements for field captions, supports setting and getting values, validation through attributes (required, min, max, pattern), and client methods for working with form data. All input elements integrate with the Form system through a common value access mechanism.

### Input Element Types

The class supports creating the following field types:

* **Text fields** (text, email, tel, url)
* **Numeric fields** (number, range)
* **Date and time** (date, time)
* **Special types** (color, file, hidden)
* **Buttons** (submit, reset, button)
* **Checkboxes and radio** (checkbox, radio)
* **Passwords** (password)
* **Lists** (select)
* **Text areas** (textarea)

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$form** | `Form/null` | `public` | Reference to the parent form. Set when adding Input to Form. Used for accessing form data through client methods. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique object identifier, used as HTML `id` attribute and for linking with the client object. |
| **$type** | `string` | `public` | Inherited from `Element`. Set to `'html'`. Defines the object type for filtering and processing. |
| **$tagname** | `string` | `public` | Inherited from `Element`. Default `'input'`. Changed by methods `select()`, `textarea()`, `button()` to `'select'`, `'textarea'`, `'button'` respectively. |
| **$classname** | `string` | `public` | Inherited from `Element`. CSS classes of the element, managed by methods `addclass()`, `removeclass()`, `hasclass()`. |
| **$caption** | `string` | `public` | Inherited from `Element`. Text content of the element. For input used as button value, for textarea — as area text. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Element` constructor, sets `tagname` to `'input'`, attribute `type` to `'text'`. |

### Basic Configuration

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **setinput** | `string $type = null`, `string $name = ''`, `string $caption = ''` | `$this` | Universal method for configuring an input element. Sets input type via `attr('type')`, field name via `attr('name')`, creates a label with caption if `$caption` is not empty. Supports fluent interface. |
| **value** | `mixed $val` | `$this` | Sets the element value. For `textarea` — sets `$caption`. For `select` — sets the `value` attribute and marks the corresponding `option` as `selected`. For other types — sets the `value` attribute. Supports fluent interface. |
| **label** | `string $caption = null` | `Html/$this/false` | Manages the associated label element. If `$caption === null` — returns the existing label or `false`. If `$caption` is passed — creates a new label with `for` attribute equal to `$this->id` and text `$caption`, adds to children. Returns the label object. |

### Field Typing

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **select** | `string $name`, `string $caption = ''`, `mixed $values = null` | `$this` | Transforms the element into a dropdown list. Sets `tagname` to `'select'`, attribute `name`. If `$caption` is not empty — creates a label. If `$values` is passed — creates `option` elements for each value (key = value, value = caption). Adds JS code for setting the value after DOM load. Supports fluent interface. |
| **textarea** | `string $name`, `string $caption = ''` | `$this` | Transforms the element into a text area. Sets `tagname` to `'textarea'`, attribute `name`, `rows` to `3`. If `$caption` is not empty — creates a label. Supports fluent interface. |
| **button** | `string $name`, `string $caption = ''` | `$this` | Transforms the element into a button. Sets `tagname` to `'button'`, calls `setinput('button', $name)`, sets `$caption`. Supports fluent interface. |
| **checkbox** | `string $name`, `string $caption = ''` | `$this` | Creates a checkbox. Calls `setinput('checkbox', $name, $caption)`. Supports fluent interface. |
| **file** | `string $name`, `string $caption = ''` | `$this` | Creates a file upload field. Calls `setinput('file', $name, $caption)`. Supports fluent interface. |
| **hidden** | `string $name`, `string $caption = ''` | `$this` | Creates a hidden field. Calls `setinput('hidden', $name, $caption)`. Supports fluent interface. |
| **image** | `string $name`, `string $caption = ''` | `$this` | Creates an image button. Calls `setinput('image', $name, $caption)`. Supports fluent interface. |
| **password** | `string $name`, `string $caption = ''` | `$this` | Creates a password field. Calls `setinput('password', $name, $caption)`. Supports fluent interface. |
| **radio** | `string $name`, `string $caption = ''` | `$this` | Creates a radio button. Calls `setinput('radio', $name, $caption)`. Supports fluent interface. |
| **reset** | `string $name`, `string $caption = ''` | `$this` | Creates a form reset button. Calls `setinput('reset', $name)`, sets `$caption` as the value. Supports fluent interface. |
| **submit** | `string $name`, `string $caption = ''` | `$this` | Creates a form submit button. Calls `setinput('submit', $name)`, sets `$caption` as the value. Supports fluent interface. |
| **text** | `string $name`, `string $caption = ''` | `$this` | Creates a text field. Calls `setinput('text', $name, $caption)`. Supports fluent interface. |
| **email** | `string $name`, `string $caption = ''` | `$this` | Creates an email field (HTML5). Calls `setinput('email', $name, $caption)`. Supports fluent interface. |
| **tel** | `string $name`, `string $caption = ''` | `$this` | Creates a phone field (HTML5). Calls `setinput('tel', $name, $caption)`. Supports fluent interface. |
| **url** | `string $name`, `string $caption = ''` | `$this` | Creates a URL field (HTML5). Calls `setinput('url', $name, $caption)`. Supports fluent interface. |
| **date** | `string $name`, `string $caption = ''` | `$this` | Creates a date field (HTML5). Calls `setinput('date', $name, $caption)`. Supports fluent interface. |
| **color** | `string $name`, `string $caption = ''` | `$this` | Creates a color picker field (HTML5). Calls `setinput('color', $name, $caption)`. Supports fluent interface. |
| **time** | `string $name`, `string $caption = ''` | `$this` | Creates a time field (HTML5). Calls `setinput('time', $name, $caption)`. Supports fluent interface. |
| **number** | `string $name`, `string $caption = ''` | `$this` | Creates a number field (HTML5). Calls `setinput('number', $name, $caption)`. Supports fluent interface. |
| **range** | `string $name`, `string $caption = ''`, `int $min = 1`, `int $max = 100` | `$this` | Creates a range slider (HTML5). Calls `setinput('range', $name, $caption)`, sets `min` and `max` attributes. Supports fluent interface. |

### Validation Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **required** | `bool $r = true` | `$this` | Sets or removes the `required` attribute. If `$r === true` — adds `required="required"`, otherwise — removes via `removeattr()`. Supports fluent interface. |
| **min** | `mixed $min` | `$this` | Sets the `min` attribute for numeric fields and dates. Supports fluent interface. |
| **max** | `mixed $max` | `$this` | Sets the `max` attribute for numeric fields and dates. Supports fluent interface. |
| **pattern** | `string $pattern` | `$this` | Sets the `pattern` attribute for validation by regular expression. Supports fluent interface. |

### Lifecycle

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **compile** | — | — | Generation of client-side JavaScript methods. For types `submit` and `reset` — compiles a `click()` method with event trigger. For other types — compiles a `value(val)` method which: (1) Finds the nearest form via `jQuery('#{$this->id}').closest('form')`. (2) Extracts the field name via `attr('name')`. (3) Calls `LTS.get(formId).value(name, val)` for setting/getting the value through the form. Calls `parent::compile()`. |
| **shine** | `Element $parent = null` | — | Rendering the element. For each child of type `html` with `tagname == 'label'` — calls `create()` to create the label in the DOM, sets `type` to `'none'` so the label is not rendered separately. Then calls `parent::shine()` for rendering the main input element. |

## Client Methods Available via LTS Object

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **LTS(input).click()** | — | — | For `submit` and `reset` buttons — triggers the click event on the element. Generated automatically during compilation. |
| **LTS(input).value(val)** | `mixed val` | `mixed` | For all other types — gets or sets the field value through the parent form. If the form is found and the field name is defined — calls `LTS.get(formId).value(name, val)`. Otherwise — returns an empty string. |

## Usage Examples

```php
// Creating a text field
$input = LTS::Input()
    ->text('username', 'Login')
    ->required(true);

// Creating an email field
$email = LTS::Input()
    ->email('email', 'Email')
    ->required(true);

// Creating a dropdown list
$select = LTS::Input()
    ->select('country', 'Country', [
        'RU' => 'Russia',
        'US' => 'USA'
    ]);

// Creating a text area
$textarea = LTS::Input()
    ->textarea('description', 'Description');

// Creating a submit button
$submit = LTS::Input()
    ->submit('submit', 'Submit');
```

## Notes
*   Class extends `Element`, therefore inherits all methods for managing children, styles, attributes, and events.
*   Class extends `Quark` and `Construct`, therefore supports full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks (`$check`, `$before`, `$on`, etc.).
*   All input type creation methods return `$this` to support fluent interface and method chaining.
*   The `setinput()` method is universal — specialized methods (`text()`, `password()`, `checkbox()`, etc.) are convenient wrappers.
*   Label elements are created automatically when `$caption` is specified — linked to input via `for` attribute equal to `$this->id`.
*   The `value()` method works differently for different element types: for `textarea` sets `$caption`, for `select` marks `option` as `selected`, for others — sets the `value` attribute.
*   The `select()` method automatically creates `option` elements from the `$values` array — array key becomes the value, array value becomes the option text.
*   For `select`, after rendering JS code `jQuery('#{$this->id}').val(jQuery('#{$this->id}').attr('value'))` is executed — ensures correct value setting after DOM load.
*   The `label()` method allows managing the caption after element creation — can get an existing label or create a new one.
*   Validation attributes (`required`, `min`, `max`, `pattern`) are natively supported by the browser — do not require additional client logic.
*   The `compile()` method generates different client methods for buttons (`click()`) and input fields (`value()`) — depending on input type.
*   The client `value()` method integrates with the form — uses `LTS.get(formId).value()` to access data through the form, not directly to the field.
*   The `shine()` method processes label elements specially — creates them in the DOM but sets `type` to `'none'` to avoid duplicate rendering.
*   HTML5 input types (`email`, `tel`, `url`, `date`, `color`, `time`, `number`, `range`) are fully supported — browser provides validation and UI.
*   For `range`, `min=1` and `max=100` are set by default — can be changed via method parameters.
*   For `textarea`, `rows=3` is set by default — can be changed via `attr('rows', ...)`.
*   `submit` and `reset` buttons have special behavior — trigger standard browser form events.
*   Checkboxes and radio buttons require grouping by name for correct operation — multiple elements with the same `attr('name')`.
*   The `required()` method adds/removes the `required` attribute — browser blocks form submission if the field is empty.
*   The `pattern` attribute accepts JavaScript regular expression — validation happens on the client before submission.
*   For numeric fields `min` and `max` can be numbers or date strings — depending on input type.
*   Class does not implement server-side validation — responsibility is on the developer or Form.
*   During compilation, the `value()` method finds the nearest form via `closest('form')` — works only if Input is inside Form.
*   To access field value outside the form, direct jQuery methods should be used: `$(field).val()`.
*   To create radio button groups, multiple Inputs with the same `attr('name')` but different `attr('value')` need to be created.
*   The `select()` method with `$values` in string format splits values via `\LTS::explodestr()` — format `"key1:caption1,key2:caption2"`.
*   For fields `date`, `time`, `datetime-local` values must be in ISO format (`YYYY-MM-DD`, `HH:MM:SS`) for correct browser operation.
*   The `color` field returns value in HEX format (`#RRGGBB`) — can be used for interface color customization.
*   The `range()` method creates a slider — value can be gotten/set via `value()` as a number.
*   For `email` and `url` fields browser performs basic format validation — can be supplemented via `pattern`.
*   `submit` and `reset` buttons do not require explicit handlers — work through standard browser form behavior.
*   The `label()` method returns `false` if the label does not exist and `$caption === null` — allows checking for caption presence.
*   When creating a label via `label($caption)`, the `for` attribute is automatically set to `$this->id` — ensures link with input.
*   For `textarea`, the `value()` method sets `$caption` — content is displayed between `<textarea>` and `</textarea>` tags.
*   For `select`, the `value()` method finds `option` with matching `attr('value')` — adds `selected` attribute to the found one and removes from others.
*   The `setinput()` method with empty `$caption` does not create a label — useful for fields without visible captions.
*   Class does not implement autocomplete — for this the `autocomplete` attribute should be used via `attr()`.
*   For `password` fields the value is not displayed in the browser — but is available via `value()` on client and server.
*   The `compile()` method for `submit`/`reset` generates a `click()` method — allows assigning handlers via Events.
*   Class supports all standard HTML attributes via `attr()` — placeholder, disabled, readonly, autofocus, etc.
*   For disabled fields `attr('disabled', 'disabled')` should be used — browser blocks interaction.
*   For mass field creation, the `Form::generate()` method with an array of configurations is recommended.
*   For `number` fields browser shows increase/decrease arrows — can be hidden via CSS.
*   The `range()` method with `$min` and `$max` sets the range — value outside the range is corrected by the browser.
