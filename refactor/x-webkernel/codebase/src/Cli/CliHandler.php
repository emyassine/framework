<?php declare(strict_types=1);

namespace Webkernel\Cli;

use Webkernel\Container\Container;
use Webkernel\Cache\CompilationStore;

/**
 * Handles CLI command execution with auto-discovery.
 */
final class CliHandler
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Handle CLI command execution.
     * Returns exit code.
     */
    public function handle(array $argv): int
    {
        $command_name = $argv[1] ?? 'list';
        $args = array_slice($argv, 2);

        // Load compiled commands
        $compiled_commands = CompilationStore::get('webkernel.global.commands', $this->container);

        // Build command map
        $command_map = $this->build_command_map($compiled_commands);

        if ($command_name === 'list') {
            return $this->list_commands($command_map);
        }

        if ($command_name === 'help') {
            $target = $args[0] ?? 'list';
            return $this->show_help($command_map, $target);
        }

        if (!isset($command_map[$command_name])) {
            echo "Unknown command: {$command_name}\n";
            return 1;
        }

        $command_class = $command_map[$command_name];
        $command = new $command_class();

        return $command->execute($args);
    }

    /**
     * Build a map of command names to class names.
     */
    private function build_command_map(array $compiled_commands): array
    {
        $map = [];

        foreach ($compiled_commands as $command_class) {
            if (class_exists($command_class)) {
                try {
                    $command = new $command_class();
                    if ($command instanceof CommandInterface) {
                        $map[$command->get_name()] = $command_class;
                    }
                } catch (\Throwable $e) {
                    error_log("Failed to instantiate command {$command_class}: " . $e->getMessage());
                }
            }
        }

        return $map;
    }

    /**
     * List all available commands.
     */
    private function list_commands(array $command_map): int
    {
        echo "Available commands:\n";
        foreach ($command_map as $name => $class) {
            try {
                $command = new $class();
                echo "  {$name} - " . $command->get_help() . "\n";
            } catch (\Throwable $e) {
                echo "  {$name} - [error: " . $e->getMessage() . "]\n";
            }
        }
        return 0;
    }

    /**
     * Show help for a specific command.
     */
    private function show_help(array $command_map, string $command_name): int
    {
        if (!isset($command_map[$command_name])) {
            echo "Unknown command: {$command_name}\n";
            return 1;
        }

        $command_class = $command_map[$command_name];
        $command = new $command_class();

        echo "Usage: webkernel {$command_name}\n";
        echo "\n" . $command->get_help() . "\n";

        return 0;
    }
}
