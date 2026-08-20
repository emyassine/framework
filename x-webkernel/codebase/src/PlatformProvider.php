<?php declare(strict_types=1);

namespace Webkernel;

/**
 * Boot-time declarations (view paths, component dirs, route files, bindings).
 * Cheap. Always listed. Not a fluent API segment — those are composables.
 */
abstract class PlatformProvider
{
    abstract public function register(WebApp $app): void;
}
