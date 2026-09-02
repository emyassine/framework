<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Config\Enums;

/**
 * Standard configuration file and section path identifiers.
 *
 * All values are relative to platform_path() (composer project root).
 * The vendor-relative paths (e.g. ProvidersManifest) are intentionally
 * expressed WITHOUT a hardcoded vendor directory prefix: the actual
 * vendor path is resolved at runtime via vendor_path() or the injected
 * vendor_path argument, so changing "vendor-dir" in composer.json
 * requires zero changes here.
 */
enum ConfigPath: string
{
    /** Foundation path definitions — loaded first to bootstrap the path registry. */
    case PlatformPaths = 'config/platform-paths.php';

    /** Core platform settings (framework-level). */
    case PlatformConfig = 'config/platform.php';

    /** Application-level settings (user land). */
    case AppConfig = 'config/app.php';

    /** Runtime-mutable overrides written by Config::set(). */
    case RuntimeConfig = 'internal/platform-runtime.php';

    /**
     * Composer-generated provider manifest.
     *
     * This path is relative to vendor_path(), NOT to platform_path().
     * Do NOT prepend the vendor directory here — PlatformConfig resolves it.
     */
    case ProvidersManifest = 'composer/webkernel_providers.php';

    /** Discovered module manifest (temporary, rebuilt on each boot). */
    case ModulesManifest = 'internal/temporary/modules_manifest.php';
}
