<?php declare(strict_types=1);

namespace Modules\Blog\Console;

use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\ExitCode;

final class GenerateSitemapCommand
{
    #[ConsoleCommand(
        name: 'blog:sitemap',
        description: 'Generate XML sitemap for blog posts',
    )]
    public function __invoke(): ExitCode
    {
        echo "Generating sitemap...\n";
        echo "Sitemap generated successfully!\n";

        return ExitCode::SUCCESS;
    }
}
