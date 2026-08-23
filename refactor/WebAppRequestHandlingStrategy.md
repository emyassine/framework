# Web Application & Webkernel Request Handling Strategy

## Overview

A high-performance enterprise application must handle diverse request contexts with predictable, sub-millisecond execution target efficiency (< 1 ms CPU budget for kernel execution). Traffic is categorized into distinct paths:

* **HTTP Web Requests** (Server-rendered HTML pages)
* **API Requests** (Stateless, programmatic JSON endpoints)
* **Machine & AI Endpoints** (`/llm.txt`, `/md` or `.md` variants for LLM crawlers and headless clients)
* **Syndication Endpoints** (`/rss`, `/atom` XML feeds)
* **Real-Time Protocols** (Persistent connections via WebSockets / SSE)
* **CLI Commands** (Local execution, cron tasks, queue workers)

Each category enforces dedicated routing, lazy memory allocation, and tailored security pipelines.

---

## Request Segmentation & Early-Exit Architecture

To prevent loading unused classes (e.g., avoiding 48 file inclusions on lightweight or non-matched requests), request classification and early-exits occur at the front-controller / web server layer:

* **Static Assets (`/static/`):** Served directly by Nginx/Apache without invoking PHP.
* **Machine Context (`/llm.txt`, `/*.md`):** Dispatched directly to a raw text/markdown renderer. Bypasses sessions, CSRF, and template engine boots.
* **Syndication (`/rss`, `/atom`):** Dispatched to a lightweight XML serializer kernel with aggressive APCu edge caching.
* **API Endpoints (`/api/`):** Handled by a stateless kernel. Bypasses session storage, CSRF middleware, and HTML view provider initialization.
* **Dynamic Web Routes:** Dispatched to the full web kernel for session resolution, CSRF validation, and view rendering.

### Fixing the Hot-Path File Leak (Lazy Boot Principles)

1. **Zero Runtime Route Compilation:** `Route\Compile\Generator.php` must **never** run on the HTTP request path. Routes must be pre-compiled at build/deployment time into a flat array in APCu or OPcache (`Cache.php`).
2. **Deferred PSR-7 Instantiation:** Route matching should execute against raw `$_SERVER['REQUEST_URI']` strings. Instantiation of heavy PSR-7 objects (`ServerRequest`, `Uri`, `Stream`) must only occur *after* a route matches.
3. **Lazy Composable / Provider Boot:** `ViewProvider` or `PlatformProvider` must not register or load unless the matched controller explicitly requests a view rendering pipeline.

---

## Pipeline & Middleware Lifecycle

Requests move through a strictly ordered pipeline:

1. **Request Identification:** Injection of a unique `X-Request-ID` header across HTTP, API, Machine, and worker logs for end-to-end tracing.
2. **Early Route Match (Flat Array):** Instant lookup via APCu pre-compiled route map. If non-matched, exit immediately with a lightweight 404 response (< 0.1 ms execution, ~5 files loaded).
3. **Payload & Input Filtering:**
* Global payload size restrictions to prevent memory exhaustion DoS.
* `Content-Type` validation (`application/json`, `text/plain`, etc.).


4. **Context-Specific Middleware:**
* *Web:* Session start, CSRF verification.
* *API / Machine:* Token auth, CORS, strict rate-limiting.



---

## Sub-Millisecond Caching & Memory Strategy

To guarantee sub-millisecond execution, network-bound caches (Redis/Memcached) are restricted to full I/O operations (< 10 ms budget). The kernel path operates exclusively on local shared memory.

| Caching Tier | Technology | Target Data | Memory Overhead |
| --- | --- | --- | --- |
| **OPcache Bytecode** | Shared Memory | Compiled PHP scripts, config arrays | Zero file read overhead |
| **APCu (Local RAM)** | Shared Memory | Flattened route maps, ACL trees, compiled config | Zero network/serialization cost |
| **HTTP / CDN** | Cloudflare / Nginx | Static assets, `/rss`, `/llm.txt`, public API responses | Zero application boot |

---

## Security Strategy & Threat Mitigation

Security checks are embedded directly into the request pipeline before invoking business logic:

### 1. CSRF & Session Isolation

* **Web Context (Stateful):** Requires a cryptographically secure token passed via `<meta name="csrf-token">` and sent in the `X-CSRF-TOKEN` header for mutative AJAX requests (`POST`, `PUT`, `DELETE`). Cookies enforce `SameSite=Lax/Strict` and `HttpOnly; Secure`.
* **API / Machine / RSS Contexts (Stateless):** Cookie authentication is completely disabled. Requests utilize HTTP `Authorization: Bearer <token>` or public access, eliminating CSRF vulnerabilities by design.

### 2. Injection Prevention (SQLi, XSS, Shell)

* **SQL Injection:** Mandatory use of prepared statements and parameterized queries via PDO/Query Builder.
* **XSS Protection:** Input data remains raw in the database. HTML escaping is performed strictly at render time. API and machine endpoints enforce explicit content types (`application/json`, `text/markdown`, `text/plain`).
* **Shell Injections:** Direct execution functions (`exec`, `system`) are prohibited on user input. Arguments are strictly passed via array escaping primitives.

### 3. API & AI Endpoint Rate Limiting

* Enforces a Token Bucket algorithm stored in APCu/Redis per IP or API token.
* Strict limits applied to AI crawlers scraping `/llm.txt` or `/md` to prevent resource harvesting attacks.
* Standard `429 Too Many Requests` responses with `Retry-After` headers.

---

## Asynchronous Processing & Task Offloading

Endpoints requiring heavy I/O or computations must never hold the HTTP response thread:

* **Background Queues:** Offload email delivery, RSS feed generation, vector indexing, and PDF rendering to asynchronous background processes.
* **Immediate Response:** HTTP controllers dispatch job payloads to the queue and instantly respond with `202 Accepted`.

---

## Observability & Error Standards

* **Health Probes:** Exposes ultra-fast `/healthz` (liveness) and `/ready` (readiness) endpoints that bypass framework overhead.
* **Standardized Errors:** APIs return RFC 7807 *Problem Details* JSON. Machine endpoints return structured plain text errors.

```json
{
  "type": "https://webkernelphp.com/errors/route-not-found",
  "title": "Not Found",
  "status": 404,
  "detail": "The requested endpoint '/api/v1/missing' does not exist.",
  "instance": "/api/v1/missing"
}

```

---

## CLI Execution

Command-line execution runs via the dedicated host CLI binary (`webkernel`), bypassing HTTP parsing, web server interfaces, and middleware stacks entirely for minimal overhead.
