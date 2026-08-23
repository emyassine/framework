<?php declare(strict_types=1);

namespace Webkernel\Cli;

/**
 * Interface for all CLI commands.
 * Ensures consistent discovery and execution.
 */
interface CommandInterface
{
    /**
     * Execute the command with given arguments.
     * Returns exit code (0 = success, non-zero = failure).
     */
    public function execute(array $args): int;

    /**
     * Get the command name (e.g., 'migrate', 'cache:clear').
     */
    public function get_name(): string;

    /**
     * Get help text for the command.
     */
    public function get_help(): string;
}
