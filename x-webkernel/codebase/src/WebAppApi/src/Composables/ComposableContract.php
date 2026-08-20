<?php declare(strict_types=1);

namespace Webkernel\WebAppApi\Composables;

/**
 * Public API segment: webapp()->{api_name()}(). Class file is not loaded until
 * that segment is first called. Dump-autoload maps api_name => FQCN.
 */
interface ComposableContract
{
    /** Public API segment. Example: 'auth'. */
    public static function api_name(): string;

    /** @return 'singleton'|'bind'|'scoped' */
    public static function container_lifetime(): string;
}
