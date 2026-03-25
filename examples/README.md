 [English](README.md) | [Русский](README.ru.md)

## 🚀 **LOTIS — Usage Examples**

> Demo applications on the PHP framework LOTIS  
> Clean code. Minimalist architecture. Maximum flexibility.

---

### 📂 Repository Structure

```bash
examples/
├── 1-hello.php             # Simple output
├── 2-formevent.php         # Form with hooks
├── 3-charttest.php         # Interactive chart
├── 4-converter.php         # Currency converter
├── 5-gridtest.php          # Responsive grid
├── 6-dashboard.php         # Live dashboard
├── 7-lookuptest.php        # Lookup field from table
├── 8-tabletest.php         # Table + edit form
└── 9-payment.php           # Full application: document
```

---

## 📘 Detailed Description of Each Example

---

### 1. `hello.php` 

> **Simple "Hello World" output via `Div`**

Shows basic framework usage:
- Creating a container via `LTS::Div()`
- Adding text via `capt('<h2>...')`
- Building via `Space`

🔹 Uses: `Div`, `capt`, `Space`  
🎯 Goal: start with the simplest — make sure everything works.

---

### 2. `formevent.php` 

> **Form with events and hooks: check, before, on**

Demonstrates action handling mechanism:
- Button triggers an event
- `checksave()` validates correctness
- `beforesave()` modifies data
- `onsave()` reacts to the result

🔹 Uses: `Form`, `Button`, `Events`, `method`  
💡 Feature: full event lifecycle chain  
🎯 Goal: show how to control data flow

---

### 3. `charttest.php` 

> **Interactive chart with type switching (line, bar, pie)**

Allows the user to dynamically change the chart type.

🔹 Uses: `SimpleChart`, `Button`, `Div`, `rowbox`  
💡 Feature: `LTS(chart).chart.setType('bar')` — direct chart control  
🎯 Goal: data visualization with interaction capability

---

### 4. `converter.php` 

> **Currency converter with external API (Central Bank of Russia)**

Calculates amounts in different currencies based on current exchange rates.

🔹 Uses: `Input`, `Select`, `JS`, `Element`, `add('recount()')`  
🌐 Data source: `cbr-xml-daily.ru`  
💡 Feature: server-side XML parsing + client-side logic  
🎯 Goal: integration with external data and instant calculation

---

### 5. `gridtest.php` 

> **Responsive grid with multiple display modes**

Shows the power of `Grid` with support for different devices: desktop, mobile, watch.

🔹 Uses: `Grid`, `device(...)`, `setMode(...)`, `priority()`  
💡 Feature: `deviceQuery` determines screen size  
🎯 Goal: creating adaptive UI without CSS media queries

---

### 6. `dashboard.php` 

> **Live control panel with auto-refresh**

Displays key metrics: revenue, tasks, temperature, exchange rates.

🔹 Uses: `Vars`, `Events`, `Span`, `Grid`, `setInterval`  
⏱️ Auto-refresh: `LTS(events).refreshData()` every 30 seconds  
🎯 Goal: simulating real-time in an administrative panel

---

### 7. `lookuptest.php` 

> **Lookup field from directory (e.g., "Services")**

User selects an item from a dropdown list with search.

On first run — specify actual MySQL connection parameters, uncomment database creation code  
On subsequent runs after the database is created, this code needs to be commented out again.

🔹 Uses: `DataTable`, `LookupField`, `search`, `out`  
💡 Feature: `loadrowclick` loads the selected object  
🎯 Goal: working with related data (many-to-one)

---

### 8. `tabletest.php` 

> **Table with row editing form**

Click on a row — form opens. Has add and delete functionality.

🔹 Uses: `DataTable`, `Form`, `rowclick`, `cells`, `signal`  
🛠 Hooks: `checkrowsave`, `before`, `on`  
🎯 Goal: standard CRUD interface in a minimalist form

---

### 9. `payment.php` 

> **Full application: "Payment" document**

Powerful `DataView` with tabular section, saving, database binding.

On first run — specify actual MySQL connection parameters, uncomment database creation code  
On subsequent runs after the database is created, this code needs to be commented out again.

🔹 Uses: `DataView`, `subtable`, `bindtodb`, `Stock`, `collector`  
🔐 Checks: `checkSave`, `beforeSave`, `onSave`  
📊 Relations: `kassatable` → `users`, `money`  
🎨 Layout: `setmodes`, `areas`, `columns`  
🎯 Goal: show the full power of the framework in one application

---

## 🛠 Usage Recommendations

| What | How |
|------|-----|
| ✅ Launch | Place the `/examples/` directory next to `/newlotis/`, use your web server |
| ✅ Learning order | From 1 to 9 — from simple to complex |
| ✅ Design changes | Edit CSS files |
| ✅ Extension | Add new examples |

---
