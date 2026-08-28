<?php declare(strict_types=1);

namespace Webkernel\Composables;

/**
 * Public API segment: webapp()->{api_name()}().
 * Dump-autoload maps api_name => FQCN.
 */
interface ComposableContract
{
    public static function api_name(): string;
}
