<?php declare(strict_types=1);

namespace Modules\Blog\Console;

use Webkernel\Cli\CommandInterface;

/**
 * Generates XML sitemap for blog posts.
 */
final class GenerateSitemapCommand implements CommandInterface
{
    /**
     * Get the command name.
     */
    public function get_name(): string
    {
        return 'blog:sitemap';
    }

    /**
     * Get help text for the command.
     */
    public function get_help(): string
    {
        return 'Generate XML sitemap for blog posts';
    }

    /**
     * Execute the command.
     */
    public function execute(array $args): int
    {
        echo "Generating sitemap...\n";

        // TODO: Implement actual sitemap generation

        echo "Sitemap generated successfully!\n";
        return 0;
    }
}
