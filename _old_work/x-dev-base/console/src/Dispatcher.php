<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Console;

use Composer\InstalledVersions;
use Webkernel\Composables\ComposableContract;
use Webkernel\Console\Input\ArgvInput;
use Webkernel\Console\Middleware\ConsoleMiddleware;

/**
 * Runs dumped `#[ConsoleCommand]` methods and maps PHP parameters to argv.
 *
 * `webapp()->console()` is this object. `Webkernel\Console::run()` is the
 * process door: construct, handle(), exit.
 *
 * //> Command classes come from CommandsDiscovery + webkernel_commands.php.
 * //> No hardcoded command list in this class.
 *
 * @phpstan-type CommandDef array{
 *   class: class-string,
 *   method: string,
 *   description: string,
 *   middleware: list<class-string>,
 *   aliases: list<string>,
 *   hidden: bool,
 *   reflection: \ReflectionMethod
 * }
 *
 * //> Command methods take scalars only. Inject collaborators in the constructor.
 */
final class Dispatcher implements ComposableContract
{
    /** @var array<string, CommandDef>|null */
    private ?array $definitions = null;

    private readonly CommandsDiscovery $discovery;

    /**
     * @param CommandsDiscovery|null $discovery
     */
    public function __construct(?CommandsDiscovery $discovery = null)
    {
        $this->discovery = $discovery ?? new CommandsDiscovery();
    }

    /**
     * @return string
     */
    public static function api_name(): string
    {
        return 'console';
    }

    /**
     * @param ArgvInput $input
     *
     * @return ExitCode
     */
    public function handle(ArgvInput $input): ExitCode
    {
        $name = $input->command();
        if ($input->wants_help()) {
            $this->print_help($name);

            return ExitCode::SUCCESS;
        }
        if ($name === null) {
            $this->print_help(null);

            return ExitCode::ERROR;
        }

        $definitions = $this->definitions();
        $definition = $definitions[$name] ?? null;
        if ($definition === null) {
            console()->error('Unknown command: '.$name);

            return ExitCode::INVALID;
        }

        try {
            $instance = $this->instantiate($definition['class']);
            $next = function () use ($definition, $input, $instance): ExitCode {
                return $this->invoke($definition, $input, $instance);
            };
            foreach (\array_reverse($definition['middleware']) as $middleware_class) {
                $middleware = $this->instantiate($middleware_class);
                if (! $middleware instanceof ConsoleMiddleware) {
                    throw new \RuntimeException($middleware_class.' must implement ConsoleMiddleware.');
                }
                $inner = $next;
                $next = static fn (): ExitCode => $middleware->handle($input, $inner);
            }

            return $next();
        } catch (Cancelled $e) {
            console()->error($e->getMessage());

            return ExitCode::ERROR;
        } catch (\InvalidArgumentException $e) {
            console()->error($e->getMessage());

            return ExitCode::INVALID;
        }
    }

    /**
     * @return array<string, CommandDef>
     */
    public function definitions(): array
    {
        if ($this->definitions !== null) {
            return $this->definitions;
        }

        $this->definitions = $this->discovery->definitions(
            $this->discovery->classes_from_dump(),
        );

        return $this->definitions;
    }

    /**
     * @param CommandDef $definition
     * @param ArgvInput  $input
     * @param object     $instance
     *
     * @return ExitCode
     */
    private function invoke(array $definition, ArgvInput $input, object $instance): ExitCode
    {
        $args = $this->bind_parameters($definition['reflection'], $input);
        $result = $definition['reflection']->invokeArgs($instance, $args);

        return self::normalize($result);
    }

    /**
     * @param \ReflectionMethod $method
     * @param ArgvInput         $input
     *
     * @return list<mixed>
     */
    private function bind_parameters(\ReflectionMethod $method, ArgvInput $input): array
    {
        $used_options = [];
        $positional = $input->arguments();
        $position = 0;
        $args = [];

        foreach ($method->getParameters() as $param) {
            $type = self::scalar_type($param);
            $kebab = \str_replace('_', '-', $param->getName());
            $is_bool = $type === 'bool';
            $required = ! $is_bool && ! $param->isDefaultValueAvailable();

            if ($required) {
                if (! \array_key_exists($position, $positional)) {
                    throw new \InvalidArgumentException('Missing argument <'.$param->getName().'>.');
                }
                $args[] = self::cast($type, $positional[$position], $param->allowsNull());
                $position++;
                continue;
            }

            $value = $this->option_value($param, $input, $kebab, $used_options);
            if ($value !== null) {
                if ($is_bool) {
                    $args[] = $value === true || $value === '1' || $value === 'true';
                    continue;
                }
                $args[] = self::cast($type, $value, $param->allowsNull());
                continue;
            }

            if (! $is_bool && \array_key_exists($position, $positional)) {
                $args[] = self::cast($type, $positional[$position], $param->allowsNull());
                $position++;
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }
            if ($param->allowsNull()) {
                $args[] = null;
                continue;
            }
            throw new \InvalidArgumentException('Missing option --'.$kebab.'.');
        }

        if (isset($positional[$position])) {
            throw new \InvalidArgumentException('Unexpected argument ['.$positional[$position].'].');
        }
        foreach ($input->options() as $name => $_) {
            if (! isset($used_options[$name])) {
                throw new \InvalidArgumentException('Unknown option --'.$name.'.');
            }
        }

        return $args;
    }

    /**
     * @param \ReflectionParameter $param
     * @param ArgvInput            $input
     * @param string               $kebab
     * @param array<string, true>  $used_options
     *
     * @return string|bool|null
     */
    private function option_value(\ReflectionParameter $param, ArgvInput $input, string $kebab, array &$used_options): string|bool|null
    {
        $names = [$kebab];
        if (\str_starts_with($param->getName(), 'with_')) {
            $names[] = \str_replace('_', '-', \substr($param->getName(), 5));
        }

        foreach ($names as $name) {
            if ($input->has_option($name)) {
                $used_options[$name] = true;
                foreach ($names as $alias) {
                    if ($input->has_option($alias)) {
                        $used_options[$alias] = true;
                    }
                }

                return $input->option($name);
            }
        }

        foreach ($names as $name) {
            $used_options[$name] = true;
        }

        return null;
    }

    /**
     * @param class-string $class
     *
     * @return object
     */
    private function instantiate(string $class): object
    {
        $ref = new \ReflectionClass($class);
        $ctor = $ref->getConstructor();
        if ($ctor === null || $ctor->getNumberOfParameters() === 0) {
            return $ref->newInstance();
        }

        $args = [];
        foreach ($ctor->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
                $args[] = $this->instantiate($type->getName());
                continue;
            }
            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }
            throw new \RuntimeException('Unable to resolve '.$class.'::$'.$param->getName());
        }

        return $ref->newInstanceArgs($args);
    }

    /**
     * @param string|null $name
     *
     * @return void
     */
    private function print_help(?string $name): void
    {
        if ($name === null) {
            $this->print_list();

            return;
        }
        $definition = $this->definitions()[$name] ?? null;
        if ($definition === null) {
            console()->error('Unknown command: '.$name);
            $this->print_list();

            return;
        }
        $this->print_command($name, $definition);
    }

    /**
     * @return void
     */
    private function print_list(): void
    {
        $definitions = $this->definitions();
        $version = $this->framework_version();

        echo "\n";
        echo '  '.Terminal::BOLD.'Webkernel'.Terminal::RESET;
        if ($version !== '') {
            echo ' '.Terminal::muted($version);
        }
        echo "\n\n";
        echo '  '.Terminal::BOLD.'Usage:'.Terminal::RESET."\n";
        echo '    webkernel '.Terminal::muted('<command> [options] [arguments]')."\n\n";
        echo '  '.Terminal::BOLD.'Available commands:'.Terminal::RESET."\n";

        $width = 4;
        foreach (\array_keys($definitions) as $name) {
            $width = \max($width, \strlen($name));
        }

        $groups = $this->discovery->groups($definitions);
        foreach ($groups as $group => $names) {
            if ($group !== '') {
                echo "\n  ".Terminal::YELLOW.$group.Terminal::RESET."\n";
            } else {
                echo "\n";
            }
            foreach ($names as $name) {
                $definition = $definitions[$name];
                $pad = \str_repeat(' ', $width - \strlen($name) + 3);
                $desc = $definition['description'] !== ''
                    ? $definition['description']
                    : $this->signature_hint($definition['reflection']);
                $indent = $group !== '' ? '    ' : '  ';
                echo $indent.Terminal::CYAN.$name.Terminal::RESET.$pad.Terminal::muted($desc)."\n";
            }
        }
        echo "\n";
    }

    /**
     * @param string     $name
     * @param CommandDef $definition
     *
     * @return void
     */
    private function print_command(string $name, array $definition): void
    {
        $primary = $name;
        foreach ($this->definitions() as $candidate => $candidate_definition) {
            if (
                $candidate_definition['class'] === $definition['class']
                && $candidate_definition['method'] === $definition['method']
                && ! \in_array($candidate, $definition['aliases'], true)
            ) {
                $primary = $candidate;
                break;
            }
        }
        $hint = $this->signature_hint($definition['reflection']);
        echo "\n  ".Terminal::BOLD.'Usage:'.Terminal::RESET."\n";
        echo '    webkernel '.Terminal::CYAN.$primary.Terminal::RESET;
        if ($hint !== '') {
            echo ' '.Terminal::muted($hint);
        }
        echo "\n";
        if ($definition['description'] !== '') {
            echo "\n  ".$definition['description']."\n";
        }
        if ($definition['aliases'] !== []) {
            echo "\n  ".Terminal::BOLD.'Aliases:'.Terminal::RESET.' '
                .\implode(', ', $definition['aliases'])."\n";
        }
        echo "\n";
    }

    /**
     * @param \ReflectionMethod $method
     *
     * @return string
     */
    private function signature_hint(\ReflectionMethod $method): string
    {
        $parts = [];
        foreach ($method->getParameters() as $param) {
            $kebab = \str_replace('_', '-', $param->getName());
            $type = self::scalar_type($param);
            if ($type === 'bool') {
                $parts[] = '[--'.$kebab.']';
                if (\str_starts_with($param->getName(), 'with_')) {
                    $parts[] = '[--no-'.\str_replace('_', '-', \substr($param->getName(), 5)).']';
                }
                continue;
            }
            if ($param->isDefaultValueAvailable()) {
                $default = $param->getDefaultValue();
                $shown = \is_scalar($default) ? (string) $default : '';
                $parts[] = '['.$param->getName().($shown !== '' ? '='.$shown : '').']';
                continue;
            }
            $parts[] = '<'.$param->getName().'>';
        }

        return \implode(' ', $parts);
    }

     /**
      * @return string
      */
     private function framework_version(): string
     {
     	return InstalledVersions::getPrettyVersion('webkernel/codebase') ?? '';
     }

    /**
     * @param \ReflectionParameter $param
     *
     * @return string
     */
    private static function scalar_type(\ReflectionParameter $param): string
    {
        $type = $param->getType();
        if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
            throw new \RuntimeException('Command parameter $'.$param->getName().' must be a scalar; inject '.$type->getName().' in the constructor.');
        }
        if ($type instanceof \ReflectionNamedType) {
            return $type->getName();
        }

        return 'string';
    }

    /**
     * @param string $type
     * @param mixed  $value
     * @param bool   $nullable
     *
     * @return mixed
     */
    private static function cast(string $type, mixed $value, bool $nullable): mixed
    {
        if ($value === null || $value === true || $value === false) {
            if ($nullable && $value === null) {
                return null;
            }
            if ($type === 'bool') {
                return $value === true;
            }
            throw new \InvalidArgumentException('Expected '.$type.' value.');
        }
        $raw = (string) $value;

        return match ($type) {
            'int' => (int) $raw,
            'float' => (float) $raw,
            'bool' => $raw === '1' || $raw === 'true',
            default => $raw,
        };
    }

    /**
     * @param mixed $result
     *
     * @return ExitCode
     */
    private static function normalize(mixed $result): ExitCode
    {
        if ($result instanceof ExitCode) {
            return $result;
        }
        if (\is_int($result)) {
            return ExitCode::tryFrom($result) ?? ExitCode::ERROR;
        }
        if ($result === null) {
            return ExitCode::SUCCESS;
        }

        throw new \RuntimeException('Console command must return ExitCode, int, or void.');
    }
}
