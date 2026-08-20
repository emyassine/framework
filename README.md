# Webkernel Platform

A high-performance, zero-overhead PHP web kernel built from scratch. Webkernel eliminates full-framework bootstrapping latency by executing minimal, explicit PHP pipelines without third-party service provider discovery overhead.

## Architectural Principles

* **Zero Magic:** No magic methods, magic resolution, or dynamic package auto-discovery.
* **Direct Composition:** Standalone components imported explicitly without deep inheritance chains.
* **Pre-Render Native Caching:** Native ETag validation (`304 Not Modified`) before booting template compilers or parsing Markdown assets.
* **Controlled Scope:** Pure PHP HTTP lifecycle targeting sub-10ms server-side responses under OPcache.

## Project Structure


```

webkernel/
├── public/
│   └── index.php             # Single entry point
├── src/
│   ├── Http/
│   │   ├── Request.php       # HTTP Request capture
│   │   ├── Response.php      # Response emission
│   │   └── Router.php        # Pattern matching router
│   ├── Md/
│   │   ├── Parser.php        # YAML Frontmatter & Markdown parser
│   │   └── Cache.php         # Disk & ETag cache layer
│   ├── View/
│   │   └── Renderer.php      # Blade rendering wrapper
│   └── Kernel.php            # Request processing orchestrator
├── content/                  # Raw Markdown content files
├── cache/                    # Static compiled HTML cache
├── third_party/              # Vendor directory
├── x-webkernel-dev/          # Development packages & modules source code
│   ├── software/
│   ├── software-dev/
│   ├── software-experimental/
│   └── software-modules/
├── composer.json
└── config.php

```

## Local Development & Setup

### 1. Installation

Ensure PHP 8.2+ is installed on your host system.

```bash
composer install

```

### 2. Local HTTP Server

Run via native PHP server for testing:

```bash
php -S localhost:8000 -t public/

```

### 3. Server OPcache Verification

Verify OPcache configuration in production environment to achieve targeted performance benchmarks:

```bash
php -r "echo opcache_get_status()['opcache_enabled'] ? 'OPcache Active' : 'OPcache Disabled';"

```

## Development Modules (`x-webkernel-dev`)

All internal development components, experimental software, and modular packages reside under `x-webkernel-dev/`. Path repositories inside `composer.json` symlink these directories directly to maintain local synchronization without remote repository pulls.
