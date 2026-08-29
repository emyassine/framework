<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Composables;

/**
 * Public API segment: webapp()->{api_name()}().
 * Dump-autoload maps api_name => FQCN.
 */
interface ComposableContract
{
    public static function api_name(): string;
}
