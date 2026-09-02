# JavaScript & Front-End Ecosystem Specifications in Webkernel

## 1. Architectural Principles

Webkernel delivers full architectural parity between its high-performance PHP backend and its front-end JavaScript ecosystem.
Its front-end architecture provides a first-class, fully equipped development layer centered around Locality of Behavior (LoB),
utility-first styling, offline-first execution, and extensible module autonomy.

Cross-platform native binary compilation serves as a planned future roadmap target.

---

## 2. Styling Pipeline & Utility-First Engine (Tailwind CSS)

Webkernel standardizes UI styling using Tailwind CSS across all core views, administrative panels, and extensible modules.

### 2.1 CSS Compilation and PostCSS Processing
- Tailwind CSS is compiled via Vite using PostCSS (`@tailwindcss/vite` plugin or PostCSS pipeline).
- Core assets generate an optimized, purged CSS distribution output per application profile (`public/build/assets/platform.css`).

### 2.2 Per-Module Styling Isolation
- Each module retains independent Tailwind scanning scopes defined in its local `vite.config.js` or Tailwind configuration file.
- Module utility classes are purged against module template files (`modules/{module_name}/resources/views/**/*.view.php`,
`modules/{module_name}/resources/js/**/*.vue`, etc.) to guarantee zero unused CSS in production bundles.

---

## 3. Server-Driven Dynamics via HTMX

HTMX acts as the primary transport engine for server-driven UI updates, form submissions, and real-time data streaming.

### 3.1 Form Handling and Server Validation
- Server-side validation flows execute via standard HTMX verb triggers (`hx-post`, `hx-put`, `hx-patch`).
- Validation errors and success states are returned directly as HTML fragments and swapped into target containers via `hx-target` and `hx-swap`.

### 3.2 Event Streaming and Real-Time Push
- Server-Sent Events (`hx-sse`) and WebSockets (`hx-ws`) handle live push notifications, activity feeds, and asynchronous task status updates.
- Out-Of-Band (`hx-swap="outerHTML swap:oob"`) updates allow targeting disjointed DOM elements in a single event payload.

### 3.3 Media Delivery Distinction
- HTMX handles the dynamic loading, swapping, and lifecycle management of media DOM elements (such as `<video>` or `<audio>` containers).
- Streaming video data protocols (such as HLS or MPEG-DASH) are managed natively by the browser media runtime or dedicated media players, isolated from HTMX attribute processing.

---

## 4. Client-Side Interactivity Layer

Client-side execution is divided into declarative component behavior and low-level DOM execution based on complexity requirements.

### 4.1 Alpine.js Execution Scope
Alpine.js handles declarative, lightweight UI state management without server round-trips:
- Dropdown menus, modal dialogs, tab switching, and accordion toggles.
- Client-side visibility filtering and inline UI toggles.
- Binding reactive form field inputs to immediate DOM state updates.

### 4.2 Vanilla JavaScript Execution Scope
Vanilla JavaScript handles low-level browser APIs and high-performance operations:
- Direct HTML5 Canvas and WebGL rendering operations.
- Advanced Drag-and-Drop (DnD) interaction engines and pointer tracking.
- Native browser integration hooks (Web Audio API, File System Access API, MutationObserver).

---

## 5. Offline Persistence, PWA & Service Worker Architecture

Webkernel manages offline resilience through application-scoped Service Workers paired with client-side relational storage and Web App Manifests.

### 5.1 Service Worker Scope Allocation
- Service Workers are registered per application scope or administrative panel (`/app`, `/admin`), rather than per individual component.
- The Service Worker pre-caches application shell assets (CSS, core JS bundles, static SVG iconography) and handles network routing strategies (Stale-While-Revalidate or Cache-First).

### 5.2 Offline Data Storage via IndexedDB
- Dynamic document data, offline drafts, and user preferences are stored in browser IndexedDB instances using wrappers such as Dexie.js.
- Service Workers intercept network request failures and queue outbound actions to IndexedDB for background synchronization once connectivity is restored.

### 5.3 Web App Manifest (PWA) Integration
- Each Webkernel instance generates a dynamic `manifest.json` defining application icons, theme colors, display modes (`standalone`), and offline start URLs.

---

## 6. Mobile Binary Runtime & Hybrid Application Packaging (Future Roadmap)

Webkernel plans to introduce CLI binary compilation utilities to bundle applications into native standalone mobile packages (Android `.apk` / `.aab` and iOS `.ipa`).

### 6.1 Native Wrapper Container Architecture
- Future CLI tooling will compile a lightweight native webview container (utilizing Capacitor or embedded WebEngine wrappers) wrapping compiled application assets.
- Embedded local HTTP servers or local file protocol handlers will load cached application shells directly on the device with zero startup latency.

### 6.2 Native Device Bridge API
- JavaScript runtimes will access native device hardware features through a standardized Webkernel Native Bridge interface:
  - Push notification registration and handling (`WebkernelNative.push`).
  - Device camera, barcode scanning, and biometrics (`WebkernelNative.camera`, `WebkernelNative.biometrics`).
  - Geolocation tracking and background sync (`WebkernelNative.geolocation`).
- In desktop web environments, bridge methods degrade gracefully to native Browser Web APIs.

---

## 7. Specialized Application Architecture

Webkernel distinguishes between document-based layout engines and canvas-based pixel editors.

### 7.1 Website Builder Architecture
- Website and page layout builders utilize DOM-based structures manipulated directly via Vanilla JS and Alpine.js.
- Elements are represented as structured HTML/CSS nodes within an editable DOM container. WebAssembly is explicitly not required for standard HTML page builders.

### 7.2 Vector and Pixel Editing Architecture
- High-performance, pixel-level graphical tools (such as vector canvas editors or image processing suites) utilize WebGL and WebAssembly (Wasm).
- The rendering engine compiles low-level Rust or C/C++ graphics routines to WebAssembly, drawing directly to an HTML5 Canvas to bypass DOM overhead.

---

## 8. Modular Front-End Extension Framework

Webkernel provides a polyglot front-end build chain, allowing third-party module developers to build interfaces using any modern JavaScript framework. All modules reside under the root `modules/` directory.

### 8.1 Per-Module Package Isolation
- Each Webkernel module maintains its own isolated `package.json` file inside its root module directory (`modules/{module_name}/package.json`).
- Dependencies are scoped to the specific module without forcing global project-wide dependency lock-in.

### 8.2 Asset Compilation via Vite
- Modules configure build assets using an independent `vite.config.js`.
- Webkernel includes a Vite asset discovery bridge that parses individual module build manifests (`manifest.json`) and injects compiled bundles into PHP backend template views.

### 8.3 Framework Support
Module creators can build interface components using any front-end stack:
- React
- Vue.js
- Svelte
- Angular
- Alpine.js / Vanilla JS

### 8.4 Directory Layout for Module Front-End Assets


```

modules/custom_module/
├── package.json
├── vite.config.js
├── tailwind.config.js
├── resources/
│   ├── js/
│   │   ├── main.js
│   │   └── components/
│   └── css/
│       └── module.css
└── dist/
└── manifest.json

```
