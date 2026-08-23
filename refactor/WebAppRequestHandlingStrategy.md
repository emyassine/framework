# Web Application Request Handling Strategy

## Overview

A high-performance web application must serve distinct request contexts with predictable, sub-millisecond execution target efficiency. The system categorizes incoming traffic into four primary execution paths:

* **HTTP Requests** (Server-rendered HTML pages)
* **API Requests** (Stateless, programmatic endpoints)
* **Real-Time Protocols** (Persistent connections via WebSockets or Server-Sent Events)
* **CLI Commands** (Local asynchronous scripts and background workers)

Each path enforces dedicated routing, memory allocation, and security pipelines.

---

## Request Segmentation & Routing Architecture

To eliminate routing overhead, request classification occurs at the web server layer (e.g., Nginx) prior to framework boot:

* **Static Assets (`/static/`):** Served directly by the reverse proxy/web server without invoking the application runtime.
* **API Endpoints (`/api/`):** Immediately dispatched to a lean API kernel that bypasses session state and template rendering engine initialization.
* **Dynamic Web Routes:** Sent to the web kernel for session resolution, CSRF validation, and HTML rendering.
* **Real-Time Connections (`/ws/` or `/events/`):** Handled by non-blocking event loops (WebSockets/SSE) designed for long-lived, concurrent I/O.

---

## Pipeline & Middleware Lifecycle

Requests move through a strictly ordered, low-overhead middleware pipeline:

1. **Request Identification:** Injection of a unique `X-Request-ID` header across HTTP, API, and worker logs for end-to-end tracing.
2. **Payload & Input Filtering:**
* Global size restrictions to prevent buffer exhaustion DoS.
* `Content-Type` validation (rejecting malformed JSON/Form payloads).


3. **Rate Limiting:** In-memory check (via Redis) before route matching or database interaction.
4. **Router Execution:** Route dispatch via pre-compiled static lookup maps.

---

## Sub-Millisecond Caching & Performance Strategy

Delivering consistent response times in the microsecond-to-millisecond range requires a multi-tier caching strategy:

| Tier | Technology | Purpose |
| --- | --- | --- |
| **Edge / CDN** | Cloudflare / Fastly | Caches static assets and publicly cacheable API outputs close to the user. |
| **HTTP Headers** | `Cache-Control`, `ETag` | Instructs browsers and proxies to reuse client-side cached data. |
| **In-Memory Cache** | Redis / Memcached / APCu | Bypasses database queries for frequent application data lookups. |
| **Opcode Cache** | OPcache / JIT | Stores compiled bytecode in shared memory to eliminate runtime file parsing. |

### Framework Optimization Rules

* **Lazy Class Loading:** Classes, services, and database connections are instantiated strictly on-demand.
* **Pre-Compiled Metadata:** Routes, configurations, and Dependency Injection containers are compiled into optimized, single-file array structures during deployment.

---

## Security Strategy & Threat Mitigation

Security checks are embedded directly into the request pipeline to catch threats before invoking application logic:

### 1. CSRF & Session Security

* **Web Context (Stateful):** Requires a cryptographically secure token passed via `<meta name="csrf-token">` and sent in the `X-CSRF-TOKEN` header for mutative AJAX requests (`POST`, `PUT`, `DELETE`). Session cookies enforce `SameSite=Lax` or `Strict` and `HttpOnly; Secure` flags.
* **API Context (Stateless):** Operates without cookies. Authentication relies exclusively on HTTP Authorization headers (`Bearer <token>`), eliminating CSRF exposure.

### 2. Injection Prevention (SQLi, XSS, Command Injection)

* **SQL Injection:** Mandatory use of prepared statements and parameterized queries. Dynamic identifiers are restricted via strict white-listing.
* **Cross-Site Scripting (XSS):** Input data remains raw; contextual HTML escaping is enforced automatically at the view template layer. API outputs strictly enforce `Content-Type: application/json`.
* **Shell Injections:** System execution calls (`exec`, `system`) are forbidden on raw user inputs. Command arguments must be passed as escaped array parameters.

### 3. API CORS & Rate Limiting

* **CORS:** Wildcard origins (`*`) are disallowed in production environments. Explicit origin, header, and method white-lists are enforced.
* **Rate Limiting:** Enforces a token bucket algorithm per IP or API key, returning standard HTTP `429 Too Many Requests` when limits are exceeded.

---

## Asynchronous Processing & Task Offloading

To maintain low latency on HTTP endpoints, time-heavy processing must never block the main execution thread:

* **Background Queues:** Offloads email generation, file processing, third-party API integration, and heavy calculations to asynchronous workers (e.g., RabbitMQ, Redis Queues).
* **Immediate Response Acknowledgment:** Endpoints dispatch job payloads to the queue and instantly return a `202 Accepted` response containing a job ID for status polling.

---

## Observability, Resilience & Error Standards

* **Health Probes:** Exposes lightweight `/healthz` (liveness) and `/ready` (readiness) endpoints for load balancers and orchestrators.
* **Standardized Error Formatting:** API errors strictly follow the **RFC 7807 Problem Details** standard:

```json
{
  "type": "https://api.example.com/errors/invalid-input",
  "title": "Invalid Request Payload",
  "status": 400,
  "detail": "The 'email' field must be a valid email address.",
  "instance": "/api/v1/users"
}

```

---

## CLI Execution

Command-line operations (cron jobs, queue consumers, CLI commands) execute via a dedicated CLI kernel. By bypassing HTTP parsing, web middleware, and network interface overhead, CLI scripts execute with minimal memory overhead and zero latency penalties.
