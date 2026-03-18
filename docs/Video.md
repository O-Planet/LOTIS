[English](Accordion.md) | [Русский](Accordion.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Video Class

## Purpose
A video player presentation class for the LOTIS framework, inheriting from `Element`. Designed for creating HTML5 video elements with support for multiple sources, automatic MIME type detection by file extension, and playback parameter configuration. The class provides methods for managing autoplay, preload, controls, looping, poster preview, and other video player attributes. Supports MP4, WebM, OGV, and MP3 formats.

### Key Features

* **Video and audio file playback**
* **Automatic MIME type detection**
* **Multiple sources** for cross-browser compatibility
* **Playback control** via methods
* **Appearance and behavior customization**

### Supported Formats

* **MP4** — video/audio with H.264 and AAC codecs
* **WebM** — video with VP8 and Vorbis codecs
* **OGV** — video with Theora and Vorbis codecs
* **MP3** — audio files

## Inherited Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique object identifier, used as HTML `id` attribute. |
| **$type** | `string` | `public` | Inherited from `Element`. Set to `'html'`. Defines object type for filtering and processing. |
| **$tagname** | `string` | `public` | Inherited from `Element`. Set to `'video'` in constructor. Defines HTML tag of element during rendering. |
| **$classname** | `string` | `public` | Inherited from `Element`. CSS classes of element, managed via `addclass()`, `removeclass()`, `hasclass()` methods. |
| **$caption** | `string` | `public` | Inherited from `Element`. Text content of element. For video, used as alternative text. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Element` constructor, sets `tagname` to `'video'`. |

### Source Addition Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **src** | `string $src` | `$this` | Adds media file source. Creates `<source>` element with `src` attribute. Automatically determines MIME type by file extension (mp4, webm, ogv, mp3). For mp3 sets `audio/mpeg`, for mp4 — `video/mp4` with codecs, for webm — `video/webm`, for ogv — `video/ogg`. For other formats, type not specified. Supports fluent interface. |
| **sources** | `array $sources` | `$this` | Adds multiple video sources. Accepts array of URLs, calls `src()` for each. Useful for providing alternative formats for different browsers. Supports fluent interface. |

### Playback Configuration Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **autoplay** | — | `$this` | Enables video autoplay. Sets attribute `autoplay="autoplay"`. Supports fluent interface. |
| **preload** | — | `$this` | Enables video preload. Sets attribute `preload="auto"`. Supports fluent interface. |
| **loop** | — | `$this` | Enables infinite playback. Sets attribute `loop="loop"`. Supports fluent interface. |
| **muted** | — | `$this` | Sets default sound to muted mode. Sets attribute `muted="muted"`. Required for autoplay in most browsers. Supports fluent interface. |
| **playsinline** | — | `$this` | Allows playback without entering fullscreen mode on mobile devices. Sets attribute `playsinline="playsinline"`. Supports fluent interface. |

### Appearance Configuration Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **poster** | `string $src = null` | `$this` | Sets preview image (poster) displayed before playback starts. Sets `poster` attribute. Supports fluent interface. |
| **controls** | — | `$this` | Displays player controls (play, pause, volume, etc.). Sets attribute `controls="controls"`. Supports fluent interface. |
| **width** | `mixed $val = null` | `$this` | Sets video width. Can be number (pixels) or string (percentages, `auto`). Sets `width` attribute. Supports fluent interface. |
| **height** | `mixed $val = null` | `$this` | Sets video height. Can be number (pixels) or string (percentages, `auto`). Sets `height` attribute. Supports fluent interface. |

### Security and Compatibility Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **crossorigin** | `string $value = 'anonymous'` | `$this` | Sets video loading mode from other domains (CORS). Default `'anonymous'`. Sets `crossorigin` attribute. Supports fluent interface. |
| **disablepictureinpicture** | — | `$this` | Disables picture-in-picture mode. Sets attribute `disablepictureinpicture="true"`. Supports fluent interface. |

## Usage Examples

```php
// Creating video element
$video = LTS::Video();

// Adding sources
$video->src('video.mp4')
      ->src('video.webm')
      ->src('video.ogv');

// Setting parameters
$video->controls()
      ->autoplay()
      ->loop()
      ->width(640)
      ->height(480)
      ->poster('preview.jpg');

// Additional settings for mobile devices
$video->playsinline()
      ->crossorigin();
```

## Notes
*   Class extends `Element`, therefore inherits all methods for managing children, styles, attributes, and events.
*   Class extends `Quark` and `Construct`, therefore supports full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks (`$check`, `$before`, `$on`, etc.).
*   **MIME types:** `src()` method automatically determines content type by file extension: mp4 → `video/mp4`, webm → `video/webm`, ogv → `video/ogg`, mp3 → `audio/mpeg`.
*   **Multiple sources:** `sources()` method allows adding multiple video formats for cross-browser compatibility — browser selects first supported format.
*   **Autoplay:** For `autoplay` to work in modern browsers, `muted()` must also be set — most browsers block autoplay with sound.
*   **Controls:** `controls` attribute adds standard player interface (play/pause, volume, progress, fullscreen mode).
*   **Poster:** `poster` image displayed before playback starts — useful for preview and improving UX.
*   **Mobile devices:** `playsinline` attribute prevents automatic fullscreen mode on iOS and Android.
*   **CORS:** `crossorigin` attribute required for loading video from other domains — default `'anonymous'`.
*   **Picture-in-Picture:** `disablepictureinpicture` attribute disables floating window mode — useful for custom interfaces.
*   **Sources:** Each `src()` call creates separate `<source>` element inside `<video>` — browser selects first supported.
*   **Attributes:** All methods set HTML attributes via `attr()` — values escaped during rendering.
*   **Fluent interface:** All methods return `$this` to support chained calls.
*   **Children:** `<source>` elements added to children via `add()` — rendered inside `<video>` tag.
*   **Rendering:** On `shine()`, creates HTML `<video>` tag with attributes and nested `<source>` elements.
*   **Namespace:** Registered via `addinspace()` — available in `Space` context.
*   **Events:** Can assign event handlers via `on()` — `play`, `pause`, `ended`, `error`, etc.
*   **Styles:** CSS properties added via `css()` — for customizing player appearance.
*   **Classes:** CSS classes added via `addclass()` — for styling via external files.
*   **ID:** Unique identifier used for linking with client object via `LTS.get('{id}')`.
*   **Preload:** `preload="auto"` starts video loading immediately — may affect performance.
*   **Looping:** `loop` replays video after completion — useful for background videos.
*   **Width/Height:** `width` and `height` attributes set player dimensions — recommended to prevent layout shift.
*   **Muted mode:** `muted` disables sound by default — required for autoplay.
*   **Cross-domain:** `crossorigin` required for accessing video via JavaScript — e.g., for custom controls.
*   **Picture-in-Picture:** Disabled via `disablepictureinpicture` — for custom interfaces.
*   **Formats:** Supports mp4 (H.264), webm (VP8), ogv (Theora), mp3 (audio).
*   **Codecs:** For mp4, codecs `avc1.42E01E, mp4a.40.2` specified — for compatibility.
*   **Alternative formats:** Recommended to add multiple sources via `sources()` — for maximum compatibility.
*   **Accessibility:** Video elements support `track` elements for subtitles — can be added via child elements.
*   **Responsive design:** Combine with CSS for adaptive video sizing across devices.
*   **Error handling:** Use `on('error', callback)` to handle loading/playback errors gracefully.
*   **Custom controls:** Hide native `controls` and build custom UI via JavaScript and CSS.
*   **Performance:** Lazy-load video sources or use `preload="none"` for bandwidth optimization.
*   **Streaming:** For large videos, consider using streaming protocols (HLS, DASH) with appropriate source configuration.
*   **Fallback content:** Add text content inside `<video>` tag as fallback for unsupported browsers.
*   **Thumbnail generation:** Use `poster` attribute with dynamically generated thumbnails for better UX.
*   **Volume control:** Native controls include volume slider; custom controls require JavaScript Volume API.
*   **Fullscreen API:** Native fullscreen available via controls; programmatic control via JavaScript Fullscreen API.
*   **Playback rate:** Can be controlled via JavaScript `playbackRate` property for speed adjustment.
*   **Current time:** Access and modify via JavaScript `currentTime` property for seeking functionality.
*   **Event propagation:** Video events bubble through DOM — can be captured at parent level for analytics.
*   **Memory management:** Remove event listeners and release video resources when component unmounts.
*   **Lazy initialization:** Defer video element creation until needed to improve initial page load time.
*   **Progressive enhancement:** Ensure content accessible without JavaScript by providing fallback links.
