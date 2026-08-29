<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\View\Compile;

/**
 * Per-compile bag. One instance per compile_string() call.
 */
final class State
{
    /** @var list<string> */
    public array $footer = [];

    public int $forelse = 0;

    public int $extend_id = 0;

    /**
     * @param $echo_format string
     * @param $directives Directives
     * @param $acl_module string|null
     */
    public function __construct(
        public readonly string $echo_format,
        public readonly Directives $directives,
        public readonly ?string $acl_module = null,
    ) {
    }
}
