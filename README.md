# Webkernel Platform

A high-performance, zero-dependency PHP web kernel and enterprise application builder built from scratch. Webkernel replaces generic framework overhead with explicit, unabstracted PHP primitives designed specifically for complex business environments.

## Vision & Product Philosophy

The primary objective of Webkernel is to serve as an **application builder with zero third-party library dependencies**.

Rather than adding layers of abstraction on top of generic third-party packages, Webkernel directly integrates and re-adapts core framework primitives to fit enterprise realities. By eliminating unnecessary broad-purpose generalizations, it provides developers with absolute control, instant execution speeds, and complete independence from external package ecosystems.

---

## Domain & UI Hierarchy

Webkernel structures applications using a multi-panel, modular architecture governed by a fine-grained authorization and permission engine.

```mermaid
graph TD
    subgraph Platform ["Platform Level"]
        AO["App Owner(s)"]
        SAP["System Admin Panel (Global Management)"]
        MOD["Modules (1..N)"]
    end

    subgraph Module ["Module Domain"]
        AP["Admin Panels (1..N)"]
    end

    subgraph Panel ["Panel Domain"]
        CL["Clusters"]
    end

    subgraph Cluster ["Cluster Domain"]
        RES["Resources"]
    end

    subgraph Resource ["Resource Domain"]
        PG["Pages"]
    end

    subgraph Page ["Page Domain"]
        CMP["Components (Tables, Forms, Widgets, Custom Views)"]
    end

    AO --> Platform
    SAP --> MOD
    MOD --> AP
    AP --> CL
    CL --> RES
    RES --> PG
    PG --> CMP

    AUTH["Granular Permission & Authorization Layer"] -.- AP
    AUTH -.- RES
    AUTH -.- PG
    AUTH -.- CMP

```

### Hierarchy Breakdown

* **Platform:** The root level managed by at least one **App Owner**. It holds global configuration and contains at least one **System Admin Panel** for platform-wide administration.
* **Modules:** Functional domains residing inside the platform. A single module can encapsulate one or multiple **Admin Panels**.
* **Admin Panels:** Operational workspaces containing organized **Clusters**.
* **Clusters:** Logical groupings used to aggregate related resources within a panel.
* **Resources:** Core business entities managed within a cluster.
* **Pages:** Individual functional views constituting a resource (e.g., List, Create, Edit, Custom View).
* **Components:** UI building blocks inside pages, including data tables, forms, metric widgets, and custom developer-defined views.
* **Granular Permissions:** A unified security layer enforcing strict authorization down to panels, resources, pages, and individual components/actions.

---

## Architectural Principles

* **Zero External Dependencies:** Native PHP execution without vendor lock-in or third-party package bloat.
* **Zero Magic:** Explicit wiring without magic methods, dynamic auto-discovery, or hidden service provider resolution.
* **Domain-Centric Abstractions:** Components designed specifically for enterprise business rules, avoiding bloated generic wrappers.
* **Controlled Scope:** Sub-10ms server-side responses optimized for native OPcache execution.

---

## Local Development & Setup

### 1. Installation

Ensure PHP 8.4+ is installed on your host system.

```bash
composer install
```

### 2. Local HTTP Server
Run via the Webkernel development server CLI runner:

```bash
php webkernel server
```

*Options:*

* `--host=127.0.0.1` : Set binding host (default: `127.0.0.1`).
* `--port=8000` : Set target port (default: `8000`, auto-increments if port is in use).
* `--profile-lifecycle` : Enable request profiling for include execution costs, file paths, and memory usage.

### 3. Server OPcache Verification
Verify OPcache status in production environment to guarantee targeted performance benchmarks:

```bash
php -r "echo opcache_get_status()['opcache_enabled'] ? 'OPcache Active' : 'OPcache Disabled';"
```
