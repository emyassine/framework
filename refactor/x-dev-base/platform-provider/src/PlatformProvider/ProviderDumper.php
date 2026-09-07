<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\PlatformProvider;

use Composer\Script\Event;
use Webkernel\PlatformProvider\Actions\CommandsFileAction;
use Webkernel\PlatformProvider\Actions\ProvidersFileAction;

final readonly class ProviderDumper
{
    public function __invoke(Event $event): void
    {
        $context = ProviderDumpContext::from_event($event);

        foreach ([new ProvidersFileAction(), new CommandsFileAction()] as $action) {
            $action->handle($context);
        }
    }
}
