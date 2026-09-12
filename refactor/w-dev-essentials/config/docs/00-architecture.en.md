# Config Architecture & Fingerprint Engine

Webkernel rejects request-path framework overhead. Traditional configuration systems parse dot-notation strings, allocate temporary arrays, and perform recursive loop lookups on every single request. Webkernel Config eliminates this bottleneck through compile-time pre-flattening and static memory hash maps.

---

## Technical Overview

### 1. Dual-State Architecture

Webkernel Config maintains two complementary representations:

1. **Pre-Flattened Hash Dictionary (`Config::$items`)**:
   - Maps every dot-notation key directly to its scalar value or array branch (e.g. `'telemetry.structure.metrics.counters.path' => 'platform/telemetry/metrics/counters'`).
   - Lookups execute in $O(1)$ time using PHP's internal Zend VM hash table array fetch (`ZEND_FETCH_DIM_R`).

2. **Canonical Multi-Dimensional Tree (`ConfigRepository::$tree`)**:
   - Preserves the full nested array structure for whole-branch inspection (`Config::all()`).

---

## Automatic Fingerprint & Recompilation Engine

Manual CLI commands (`config:cache`, `optimize`, `cache:clear`) introduce friction and risk stale configuration states during development. Webkernel solves this by evaluating fingerprints automatically on boot.

```
 ┌────────────────────────────────────────────────────────┐
 │ Config::boot()                                         │
 └──────────────────────────┬─────────────────────────────┘
                            │
                            ▼
 ┌────────────────────────────────────────────────────────┐
 │ ConfigFingerprint::is_stale()                          │
 │  ├── Scans base_path('config/**/*.php') mtime & size   │
 │  ├── Scans package PlatformProvider::CONFIG paths      │
 │  ├── Scans internal/platform-runtime.php               │
 │  └── Checks directory modification times               │
 └──────────────────────────┬─────────────────────────────┘
                            │
            ┌───────────────┴───────────────┐
            ▼                               ▼
     Cache Valid                      Cache Stale
  Require compiled array        Compile & Atomic Write
```

### Stale Criteria

A cache file is marked stale and recompiled immediately if:
- The cache file does not exist.
- Any source `.php` configuration file in `base_path('config')` or subdirectories has a modification time (`filemtime`) newer than the cache file.
- Any package provider configuration file declared in `PlatformProvider::CONFIG` has been edited or touched.
- The runtime override file (`internal/platform-runtime.php`) has changed.
- Any tracked directory has been modified (indicating file additions, deletions, or renames).

---

## Atomic Cache Persistence & OPcache Invalidation

Compiled configuration files are written using atomic double-step renaming to prevent read-during-write corruption under heavy concurrency:

```php
// 1. Write payload to unique temporary file with exclusive lock
file_put_contents($tmp_file, $code, LOCK_EX);

// 2. Atomic filesystem rename
rename($tmp_file, $cache_file);

// 3. Invalidate OPcache memory cache
opcache_invalidate($cache_file, true);
```

This guarantees zero request failure during configuration reloads.
