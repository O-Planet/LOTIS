[English](Examples.md) | [Русский](Examples.ru.md)

🚀 [Quick start in 5 minutes](QuickStart.md) 

# LOTIS: Code Examples

## Example 1: Reading a file and displaying content

```php
// Include LOTIS
include_once 'newlotis/lotis.php';

// Main container
$maindiv = LTS::Div(); 

// Container for file content
$content = LTS::Div();

// Button to trigger file reading from server
$button = LTS::Button()
    ->capt('Click me')
    ->click(
<<<JS
        LTS(events).loadreadme();
JS
);

// Event: reads file from server and displays it in $content container on client
$events = LTS::Events();
$events->client('loadreadme',
<<<JS
   $(content).text(result); $(button).hide();
JS
);
$events->server('loadreadme', function ($args) { return file_get_contents('readme.md'); });

// Attach styles from index.css
$maindiv->CSS()->add('index.css');

// Build the page
$maindiv->addmany($events, $content, $button);
LTS::Space()->build($maindiv);
```

## Example 2: Fetching new messages from database

```php
// Include LOTIS
include_once 'newlotis/lotis.php';

// Describe database tables
$base = LTS::MySql('mybase', 'localhost', 'root', 'root');

$users = $base->table('users');
$users->string('name', 100);

$messages = $base->table('messages');
$messages->table('recipient', $users);
$messages->table('sender', $users);
$messages->date('date');
$messages->text('info');
$messages->bool('newmessage');

// Form for entering username
$form = LTS::Form();
$username = $form->text('name', 'Username');
$form->button('enter', 'Get messages')->click(
<<<JS
    const user = LTS(username).value();
    if(! user) {
        alert('Please enter username!');
        return;
    } 
    LTS(events).getinfo(user); 
JS
);

// Event: reads data from database and returns it to table
$events = LTS::Events();
$events->client('getinfo(name)',
<<<JS
    if(result.ok)
        LTS(table).create(result.data);
    else
        alert(result.error);
JS
);
$events->server('getinfo', function ($args) {
    global $messages;
    // Username entered in form
    $name = $args['name'];
    // Database query
    $allmessages = $messages->all(['recipient.name' => $name, 
        'newmessages' => 1, 
        'ORDER' => '-date',
        'FIELDS' => 'date, sender.name as author, info']);
    // If no new messages found
    if(count($allmessages) == 0)
        return ['ok' => false, 'error' => 'User has no new messages'];
    // Collect IDs of all fetched messages
    $ids = array_map(function ($item) { return $item->id; }, $allmessages);
    // Mark messages as read
    $messages->value('newmessage', 0)
        ->setall(['id' => $ids]);    
    // Return results to client
    return ['ok' => true, 'data' => $allmessages];
});

// Table for displaying messages
$table = LTS::DataTable();
$table->head(['date' => 'Date', 'author' => 'Author', 'info' => 'Message']);

// Main container
$maindiv = LTS::Div(); 

// Attach styles
$maindiv->CSS()->add('index.css');

// Build the page
$maindiv->addmany($events, $form, $table);
LTS::Space()->build($maindiv);
```

## Example 3: Employee payment document

```php
// Include LOTIS
include_once 'newlotis/lotis.php';

// Describe database tables
$base = LTS::MySql('mybase', 'localhost', 'root', 'root');

$users = $base->table('users');
$users->string('name', 100);
$users->float('total');

$kassa = $base->table('kassa');
$kassa->date('date');

$kassatable = $base->table('kassatable');
$kassatable->parent($kassa);
$kassatable->table('user', $users);
$kassatable->string('message', 100);
$kassatable->float('pay');

$money = $base->table('money');
$money->int('doc');
$money->date('date');
$money->table('user', $users);
$money->float('pay');

// Create document
$maindiv = LTS::DataView();

// Bind DataView to kassa table
$maindiv->bindtodb($kassa, [
    // Document table columns
    'head' => ['sel' => '', 'date' => 'Date'],
    // Document header editor fields
    'inputs' => [
        ['name' => 'id',  'type' => 'hidden'],
        ['name' => 'date', 'type' => 'date', 'caption' => 'Date'],
        ['name' => 'save', 'caption' => 'Save', 'type' => 'button'],
        ['name' => 'close', 'caption' => 'Cancel', 'type' => 'button']
    ],
    // Field grouping in editor
    'cells' => ['save, close', 'date'],
    // Document selection window fields
    'filter' => [['name' => 'date', 'type' => 'date', 'caption' => 'Date']],
    // Sortable columns
    'sort' => ['date as date']
]);

// Custom row rendering for document table
$maindiv->table->out(
<<<JS
    function (row, obj) { 
        // If row is selected
        row.find('td.Column_sel').text(obj.sel ? '✅' : '☐'); 
        // Format date display, remove time
        row.find('td.Column_date').text(obj.date.substr(0, 10)); 
    }
JS
);

// Define document line items (subtable)
$subtable = $maindiv->subtable('kassasubtable', $kassatable, [
    // Line item columns
    'head' => [
        'sel' => '',
        'name' => 'Employee',
        'pay' => 'Received',
        'del' => ''
    ],
    // Explicitly defined fields from kassatable
    'fields' => 'user, user.name as name, message, pay',
    // Grid area for subtable
    'area' => 'element',
    // Line item editor fields
    'inputs' => [
        ['name' => 'ltsDataId', 'type' => 'hidden'],
        ['name' => 'user', 'type' => 'table', 'dbtable' => $users, 'caption' => 'Employee'],
        ['name' => 'message', 'caption' => 'Purpose'],
        ['name' => 'pay', 'type' => 'numeric', 'caption' => 'Amount'],
        ['name' => 'save', 'type' => 'button', 'caption' => 'OK'],
        ['name' => 'close', 'type' => 'button', 'caption' => 'Cancel']
    ],
    // Editor field grouping
    'cells' => ['save, close', 'user', 'message', 'pay'],
    // Disable filter
    'filter' => null
]);

// Link employee selection field to line item row
$userfield = $subtable->element->field('user');
$userfield->head(['name' => 'Employee', 'total' => 'Total received']);
$userfield->fieldmap(['id' => 'user', 'name' => 'name']);

// Custom row rendering for line items
$subtable->table->out(
<<<JS
function (row, obj) {
    row.find('td.Column_sel').text(obj.sel ? '✅' : '☐'); 
    row.find('td.Column_del').html('<input type="button" class="ltsRowDelbutton" value="x">');
} 
JS
);

// Validation before saving line item
$subtable->method('checkrowsave(values)',
<<<JS
    if(! LTS(userfield).selected) {
        alert('Employee not selected!');
        return false;
    }
    if(values.pay == 0) {
        alert('Amount cannot be zero!');
        return false;
    }
    values.name = LTS(userfield).selected.name; 
    return true;
JS
);

// Update data stocks on document save
$maindiv->onsave(function ($args, $result) {
    global $money, $users;
    
    if(! $result['result'])
        return $result;

    // Get line items
    $paytable = $args['subtables']['kassasubtable'];
    // Get document date
    $date = $args['date'];
    // Add date to each row
    $paytable = array_map(function ($item) use ($date) { 
        $item['date'] = $date; 
        return $item; }, 
        $paytable);

    // Open money stock
    $stock = LTS::Stock($money);
    // Update users.total field from line items
    $stock->collector($users, 'user', ['total' => 'pay']);
    // Update stock records
    $stock->update(['doc' => $result['data']['id']], $paytable); 

    return $result;
});

// Attach styles
$maindiv->CSS()->add('index.css');

// Build the page
LTS::Space()->build($maindiv);
```

---

