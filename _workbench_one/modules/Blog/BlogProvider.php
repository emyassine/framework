<?php declare(strict_types=1);

namespace Modules\Blog;

use Webkernel\Provider\PlatformProvider;
use Webkernel\Container\Container;

/**
 * Blog module provider.
 * Demonstrates the module provider pattern with static declarations.
 */
final class BlogProvider extends PlatformProvider
{
    // Static declarations — resolved at compile time, zero method overhead
    public const ROUTES      = [__DIR__ . '/routes.php'];
    public const VIEWS       = [__DIR__ . '/resources/views'];
    public const COMMANDS    = [\Modules\Blog\Console\GenerateSitemapCommand::class];
    public const CONFIG      = [
        'blog.posts_per_page' => 20,
        'blog.cache_ttl' => 300,
    ];

    /**
     * Register container bindings.
     */
    public function register(Container $container): void
    {
        // Example: Bind repository interface to implementation
        // $container->bind(BlogRepository::class, EloquentBlogRepository::class);
    }

    /**
     * Boot the provider.
     */
    public function boot(Container $container): void
    {
        // Runs after all providers are registered.
        // Safe to resolve services here if needed.
    }

    /**
     * Dynamic declarations — override when values can't be known at definition time.
     */
    public function files(): array
    {
        return [
            __DIR__ . '/database/migrations',
            __DIR__ . '/resources/stubs',
        ];
    }

    /**
     * Access control list rules.
     */
    public function acl(): array
    {
        return [
            'blog.create' => ['admin', 'editor'],
            'blog.delete' => ['admin'],
            'blog.view' => ['admin', 'editor', 'user'],
        ];
    }
}
