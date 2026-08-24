<?php declare(strict_types=1);

namespace Webkernel;

use Webkernel\Container\Container;
use Webkernel\Cache\CompilationStore;
use Webkernel\Http\RequestClassifier;
use Webkernel\Console\Input\ArgvInput;

/**
 * Entry point abstractions for Webkernel.
 * Provides ultra-fast static API for starting HTTP and terminal applications.
 */
final class Index
{
    /**
     * Shared RequestClassifier instance.
     *
     * @var RequestClassifier|null
     */
    private static ?RequestClassifier $classifier = null;

    /**
     * Start the HTTP request handling.
     *
     * @return void
     */
    public static function start_http(): void
    {
        $c = Container::get_instance();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = $_SERVER['REQUEST_URI'] ?? '/';
        $path   = strtok($uri, '?') ?: '/';

        $routes = CompilationStore::get('webkernel.global.routes', $c) ?? [];

        if (self::$classifier === null) {
            self::$classifier = new RequestClassifier();
        }

        self::$classifier->classify($path, $method)->handle($routes, $c)->emit();
    }

    /**
     * Start the terminal command handling.
     *
     * @param object $webapp Instance of the application implementing handle_command().
     *
     * @return never
     */
    public static function start_terminal(object $webapp): void
    {
        exit($webapp->handle_command(new ArgvInput()));
    }
}
