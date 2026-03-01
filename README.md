 [Русский](README.ru.md) | [English](README.md)

🚀 [Quick Start in 5 minutes](QuickStart.md)

💻 [Code Examples](Examples.md)

# LOTIS — PHP Framework for Fast Business Apps

"Less code - more meaning"

LOTIS (Low Time Script) is a framework for rapid creation of interactive WEB applications and business systems in PHP.
You build applications like in desktop development, using OOP principles without binding to markup.

## Key Advantages

| Advantage | Implementation |
|:---|:---|
| **Zero-config** | Works immediately after connection |
| **Single language** | PHP for server and client generation without manual markup |
| **Single namespace** | Access to objects through the same variables on server and client |
| **Domain orientation** | Work with business entities, not DOM: documents with tabular parts, data stocks and collectors, database synchronization "on the fly" |
| **Event model** | Client-server interaction logic without writing AJAX requests |
| **Session state** | Automatic synchronization of global variables between server and client |
| **Extensibility** | Hooks, signals, user methods |
| **Minimal dependencies** | Only PHP, jQuery, MySQL |

## Minimal Working Example

```php
include_once 'newlotis/lotis.php';

$div = LTS::Div('main')
    ->columnbox();
$div->capt("Hello from LOTIS!")
  ->CSS()->add('width', '100%')
    ->add('height', '100vh');
LTS::Space()->build($div);
```

## How it works?

- PHP classes form metadata.
- Space class transforms metadata into HTML.

## Installation

```bash
git clone https://github.com/o-planet/lotis.git
# or copy src/newlotis folder to your project
```

## Book

"WEB Application Development for Business in PHP"  
Author: Oleg Ponomarenko  
Coming soon.

A practical guide to engineering thinking in development, based on 25 years of experience creating commercial WEB applications.
The author offers a unique approach: instead of studying ready-made solutions, you will create your own framework, understanding every line of code "from the inside".

## For:

- WEB applications of any complexity
- ERP, CRM, API, websites
- Document management
- Any business project where speed and reliability matter

## Features

- ✅ PHP 7+
- ✅ No Composer (supported)
- ✅ Lightweight (~50 KB)
- ✅ Up to 10,000 rows in tables
- ✅ Intuitive working tool
- ✅ Minimal requirements
- ✅ For business automation

## License

MIT — freely use, modify, distribute.

## Author

Oleg Ponomarenko (O-Planet)  
olegspost@list.com  
TG: @OPlanet  
http://www.o-planet.ru

Today is the day to start!

---
