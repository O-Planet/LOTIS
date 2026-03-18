[English](README.md) | [Русский](README.ru.md)

🚀 [Quick Start in 5 Minutes](QuickStart.md) 

💻 [Code Examples](Examples.md)

# LOTIS — Framework for Rapid WEB Application Development in PHP

## What is LOTIS?
LOTIS (Low Time Script) is a fast and compact framework for creating web applications in PHP. 
It focuses on performance and ease of development, reducing manual effort when building interfaces and logic.
You build applications like desktop development, using OOP principles without being tied to markup.

## Minimal Working Example
```php
include_once 'newlotis/lotis.php';
$div = LTS::Div('main')->capt("Hello from LOTIS!");
LTS::Space()->build($div);
```

## Features
  - Zero-config: works immediately after connection, no additional configuration required.  
  - Single programming language: everything in PHP without manual markup.  
  - Desktop-like development: work in a single variable space without AJAX separation.
  - Business entities support: tables, documents with tabular parts, data stocks and collectors, multi-user synchronization.
  - Easy integration with MySQL and jQuery.  

## Core Concepts
  - **Objects and Classes**  
    All development revolves around classes like `Div`, `Button`, `Form`, `DataTable`, which form UI interfaces and manage data.
    
  - **Metadata, Not Markup**  
    Classes don't generate direct HTML, but only collect metadata that is later used to form the final page.
    
  - **Desktop Approach**  
    The application is not divided into client and server parts. All development is sequential, allowing focus on logic.
    
  - **Unified Namespace**  
    PHP variable names referencing application objects can be used in client script code to access their properties and methods.
    
  - **Events and Global Variables**  
    Enable interaction between clients and servers, supporting data synchronization throughout the session.
    
  - **Extensibility and Control**  
    Developers have access to hooks, signals, and custom methods.
    
  - **Application Assembly**  
    All elements are assembled into a whole via the `Space::build()` method, which transforms metadata into real HTML.

## Getting Started

1. **Installation:**  
   Download the GitHub repository or add the `src/newlotis` folder to your project.

2. **Entry Point:**  
   Connect `lotis.php` and create your first object.

   ```php
   include_once 'newlotis/lotis.php';
   $div = LTS::Div('main')->capt('Hello from LOTIS!');
   ```

3. **Interface Building:**  
   Use class constructors to create elements and combine them into compositions.

   ```php
   $loginform = LTS::Form()
       ->text('username', 'Login')
       ->password('password', 'Password')
       ->button('login', 'Sign In')
       ->button('close', 'Close');
   $div->add($loginform);
   ```

4. **Style Management:**  
   Connect CSS files or manage styles directly from PHP.

   ```php
   $div->css()->add('index.css');
   $loginform->css('border', 'solid 1px');
   ```

5. **Events and Data Handling:**  
   Create events and work with them without AJAX.

   ```php
   $loginform->event('login', function($args) {
       return $args['username'] == 'Brad Pitt' && $args['password'] == '123';
   });
   $loginform->event('login',
   <<<JS
       if(result) alert('Hello!'); else console.log('Go to ...');
   JS
   );
   ```

6. **Flexible Logic Management:**  
   Create custom client methods, implement hooks.

   ```php
   $loginform->method('mymethod',
   <<<JS
       alert('Everything works!');
   JS
       );
   $loginform->add('cheklogin', function ($args) {
        if($args['username'] == '' || $args['password'] == '') return false;
        return true;
   });
   ```
   
7. **Desktop Approach, Unified Namespace:**  
   Use the same variable names in both PHP code and JS scripts, without worrying about object IDs.

   ```php
   $loginform->button('close', 'Close')->click(
       <<<JS
           $(loginform).hide();
       JS
   );
   ```

8. **Application Assembly:**  
   The assembly point is the Space class. Every object must have a parent. It doesn't matter when you assign it. But Space is the common parent for all.

   ```php
   LTS::Space()->build($div);
   ```

## Book

"Developing Business WEB Applications in PHP"  
Author: Oleg Ponomarenko  
Coming Soon.

A practical guide to engineering thinking in development, based on 25 years of experience creating commercial WEB applications. 
The author offers a unique approach: instead of studying ready-made solutions, you will create your own framework, understanding every line of code "from the inside."

## For Whom:

- WEB applications of any complexity
- ERP, CRM, API, websites
- Document management
- Any business project where speed and reliability matter

## License

MIT — freely use, modify, distribute.

## Author

Oleg Ponomarenko (O-Planet)  
olegspost@list.com  
TG: @OPlanet  
http://www.o-planet.ru 

Today is the day to start!

---
💰 Support the project with crypto:

🔹 **Bitcoin (BTC):** `bc1q0f8vwtstaevw542gn7uzvt5j69v75fp5ky927h`

🔹 **Ethereum (ETH):** `0x9843cC2985B2fCc995852fe1956DC799be5967d3`

🔹 **USDT (TRC-20):** `TFGXe8NfNv2KzkDEpuekZ9p91AU8ommjTM`

🔹 **TRX:** `TFGXe8NfNv2KzkDEpuekZ9p91AU8ommjTM`

🔹 **TON:** `UQDt433lyVgotQQW0Hj2VecJqXpXRoyR7spTl0A4idEziO99`

🔹 **Polygon (MATIC):** `0x9843cC2985B2fCc995852fe1956DC799be5967d3`

⚠️ **Double-check the network before sending!**
