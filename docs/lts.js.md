[English](lts.js.md) | [Русский](lts.js.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Client Core (lts.js)

## Purpose
The main JavaScript module of the LOTIS framework, automatically connected on all application pages. Provides the client-side part of the LOTIS object model, implementing interaction between the browser and PHP backend. Responsible for managing the lifecycle of client objects, data transfer (AJAX/Fetch), global state synchronization, signal system (Pub/Sub), and interface localization.

## Properties
*   **objects** — Storage of client representations of objects (LTS objects) by their unique IDs.
*   **varsstorage** — Local storage of global variables synchronized with server session.
*   **requests** — Registry of callback functions for handling successful server responses.
*   **errors** — Registry of callback functions for handling request errors.
*   **busi** — Flags for blocking repeated sending of identical requests.
*   **signals** — Subscriber registry for signal system (event bus).
*   **async** — Default async mode flag for requests.
*   **isonerequest** — Flag limiting execution to only one active request at a time.
*   **languages** — Dictionaries for client-side text localization.
*   **isfetch** — Flag indicating browser support for `fetch` API.
*   **preloaderopen / preloaderclose** — References to functions for controlling loading indicator.

## Methods
*   **get(id)** — Returns LTS object — representation of object with its client properties and methods from storage by ID. If object is absent, creates stub with hook methods (`check`, `before`, `on`), access to jQuery wrapper, event subscription, and signals.
*   **vars(name, values)** — Global variables management. Allows getting and setting values stored within session scope.
*   **signal(name, data)** — Initiates signal with specified name and transmits data to all subscribers.
*   **onSignal(name, obj, handler)** — Subscribes object and handler to listen for specified signal.
*   **post(name, script, args)** — Sends POST request to server. Handles duplicate blocking, preloader management, data serialization (JSON or FormData), and response routing to corresponding `requests` or `errors` handlers. Supports synchronous and asynchronous modes (via `fetch` or `XMLHttpRequest`).
*   **asyncpost(name, script, args)** — Asynchronous version of `post` method using `await/fetch`.
*   **page(url)** — Performs navigation to new page via POST request mechanism with current global variables state transmission.
*   **lang(lng, words)** — Registers translation dictionary for specified language.
*   **say(word, key, lng)** — Returns localized string. If translation not found, returns original word.
*   **request(name, func)** — Registers successful response handler function for request with specified name.
*   **error(name, func)** — Registers error handler function for request with specified name.
*   **inic()** — Initializes basic handlers (by default configured to react to `page` response for redirect). Called automatically when script loads.

## Notes
*   **Automatic initialization:** `inic()` called on script load — sets up default `page` handler for navigation.
*   **Object stubs:** `get()` creates minimal object with hooks if not found — ensures consistent API even for server-only objects.
*   **jQuery integration:** Each LTS object has `$` property — jQuery wrapper for DOM element by ID.
*   **Event delegation:** Objects support `on(event, handler)` — binds jQuery events with automatic cleanup.
*   **Signal system:** Pub/Sub pattern via `signal()`/`onSignal()` — decouples components communication.
*   **Request deduplication:** `busi` flags prevent identical concurrent requests — avoids race conditions.
*   **Preloader integration:** `preloaderopen`/`preloaderclose` hooks — customizable loading indicators.
*   **Fetch/XHR fallback:** `isfetch` detects browser capability — uses `fetch` if available, falls back to `XMLHttpRequest`.
*   **Async by default:** `async` flag controls request mode — most operations non-blocking.
*   **Single request mode:** `isonerequest` flag — serializes requests when enabled for strict ordering.
*   **Global variables:** `vars()` syncs with PHP session — state persists across page transitions.
*   **Localization:** `lang()`/`say()` provide client-side i18n — reduces server roundtrips for static texts.
*   **Error handling:** `error()` handlers registered per request name — centralized error management.
*   **Response routing:** `request()` handlers match by name — clean separation of concerns.
*   **FormData support:** `post()` detects File objects — automatically uses `FormData` for multipart uploads.
*   **JSON serialization:** Non-file data serialized via `JSON.stringify()` — efficient payload encoding.
*   **Navigation via POST:** `page()` sends state with URL — enables complex transitions with context preservation.
*   **Promise-based async:** `asyncpost()` returns Promise — integrates with modern async/await patterns.
*   **Hook system:** Objects support `check`, `before`, `on` — lifecycle interception for validation and side effects.
*   **Memory management:** Objects stored by ID — predictable lifecycle, no automatic garbage collection.
*   **Thread safety:** Request flags and queues — prevents concurrent modification of shared state.
*   **Extensibility:** Core methods can be overridden — framework adaptable to custom requirements.
*   **Debugging:** Request/response logging via handlers — facilitates development and troubleshooting.
*   **Performance:** Deduplication and async defaults — optimized for responsive user experience.
*   **Compatibility:** Works with jQuery 3.x+ — leverages mature DOM manipulation library.
*   **Security:** Input sanitized server-side — client focuses on UX, backend enforces validation.
*   **Offline support:** Not built-in — requires custom caching strategy for offline scenarios.
*   **WebSocket ready:** Signal system architecture — can be extended for real-time bidirectional communication.
*   **Module pattern:** IIFE wrapper — avoids global namespace pollution.
*   **Minification ready:** No dynamic property names — safe for aggressive JS minifiers.
*   **Testing:** Methods designed for mocking — facilitates unit testing of client logic.
*   **Documentation:** Method signatures self-descriptive — reduces need for external references.
