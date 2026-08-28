<?php declare(strict_types=1);

namespace Webkernel;

use Webkernel\Console\Commands\DumpAutoloadCommand;
use Webkernel\Platform\System\SystemPanelProvider;

final class CodebaseProvider extends PlatformProvider
{
    public const ROUTES = [__DIR__.'/../routes.php'];
    public const VIEWS = [__DIR__.'/View/views'];
    public const COMPONENTS = [__DIR__.'/View/views/components'];
    public const COMMANDS = [DumpAutoloadCommand::class];
    public const PANELS = [SystemPanelProvider::class];
}
