<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Console;

/**
 * Extra.webkernel.post_autoload_dump FQCN. Dump-autoload injects and runs these.
 *
 * //> DumpAutoloadCommand is the orchestrator, not a DumpHook.
 */
interface DumpHook
{
    /**
     * @param $vendor_dir string
     *
     * @return void
     */
    public function run(string $vendor_dir): void;
}
