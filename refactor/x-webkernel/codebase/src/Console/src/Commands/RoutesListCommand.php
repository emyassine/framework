<?php declare(strict_types=1);

namespace Webkernel\Console\Commands;

use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\ExitCode;
use Webkernel\Console\Terminal;
use Webkernel\Route\Route;

final readonly class RoutesListCommand
{
    #[ConsoleCommand(
        name: 'routes:list',
        description: 'List registered HTTP routes',
    )]
    public function __invoke(): ExitCode
    {
        $routes = Route::list();
        if ($routes === []) {
            echo '  '.Terminal::muted('No routes defined.')."\n";

            return ExitCode::SUCCESS;
        }

        $method_w = 7;
        $uri_w = 3;
        $name_w = 4;
        $action_w = 6;
        $rows = [];
        foreach ($routes as $route) {
            $methods = implode('|', $route['methods']);
            $uri = $route['uri'] === '' ? '/' : $route['uri'];
            $name = $route['name'] !== '' ? $route['name'] : '-';
            $rows[] = [
                'methods' => $methods,
                'uri' => $uri,
                'name' => $name,
                'action' => $route['action'],
            ];
            $method_w = max($method_w, strlen($methods));
            $uri_w = max($uri_w, strlen($uri));
            $name_w = max($name_w, strlen($name));
            $action_w = max($action_w, strlen($route['action']));
        }

        echo "\n";
        echo '  '.Terminal::muted(sprintf('%-'.$method_w.'s  %-'.$uri_w.'s  %-'.$name_w.'s  %s', 'METHOD', 'URI', 'NAME', 'ACTION'))."\n";
        echo '  '.Terminal::muted(str_repeat('-', $method_w + $uri_w + $name_w + $action_w + 6))."\n";
        foreach ($rows as $row) {
            echo sprintf(
                "  %s%-{$method_w}s%s  %s%-{$uri_w}s%s  %-{$name_w}s  %s\n",
                Terminal::CYAN,
                $row['methods'],
                Terminal::RESET,
                Terminal::BOLD,
                $row['uri'],
                Terminal::RESET,
                $row['name'],
                Terminal::muted($row['action']),
            );
        }
        echo "\n  ".Terminal::muted(count($rows).' '.(count($rows) === 1 ? 'route' : 'routes'))."\n\n";

        return ExitCode::SUCCESS;
    }
}
