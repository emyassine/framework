<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\View;

use Webkernel\View\Compile\Directives;
use Webkernel\View\Compile\Pipeline;
use Webkernel\View\Compile\State;

/**
 * Compile-time only. Engine includes the compiled PHP. This class is not
 * loaded on a cache hit.
 */
final class Compiler
{
    /**
     * @param $directives Directives
     * @param $echo_format string
     * @param $acl_module string|null
     */
    public function __construct(
        private readonly Directives $directives,
        private string $echo_format = '\\'.View::class.'::echo(%s)',
        private ?string $acl_module = null,
    ) {
    }

    /**
     * @param $format string
     *
     * @return void
     */
    public function set_echo_format(string $format): void
    {
        $this->echo_format = $format;
    }

    /**
     * @param $module string|null
     *
     * @return void
     */
    public function set_acl_module(?string $module): void
    {
        $this->acl_module = $module;
    }

    /**
     * @return Directives
     */
    public function directives(): Directives
    {
        return $this->directives;
    }

    /**
     * @param $name string
     * @param $handler callable(string): string
     *
     * @return void
     */
    public function directive(string $name, callable $handler): void
    {
        $this->directives->handler($name, $handler);
    }

    /**
     * @param $name string
     * @param $callback callable(mixed...): bool
     *
     * @return void
     */
    public function register_if_statement(string $name, callable $callback): void
    {
        $this->directives->condition($name, $callback);
    }

    /**
     * @param $source string
     *
     * @return string
     */
    public function compile_string(string $source): string
    {
        return Pipeline::compile($source, new State(
            $this->echo_format,
            $this->directives,
            $this->acl_module,
        ));
    }

    /**
     * @param $source_path string
     * @param $compiled_path string
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function compile_file(string $source_path, string $compiled_path): void
    {
        $source = file_get_contents($source_path);
        if (! is_string($source)) {
            throw new \RuntimeException('Unable to read '.$source_path);
        }
        $dir = dirname($compiled_path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new \RuntimeException('Unable to create '.$dir);
        }
        if (file_put_contents($compiled_path, $this->compile_string($source), LOCK_EX) === false) {
            throw new \RuntimeException('Unable to write '.$compiled_path);
        }
    }
}
