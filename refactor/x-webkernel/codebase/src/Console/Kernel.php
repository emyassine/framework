<?php declare(strict_types=1);

namespace Webkernel\Console;

use Webkernel\Composables\ComposableContract;
use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\Commands\DumpAutoloadCommand;
use Webkernel\Console\Commands\ServerCommand;
use Webkernel\Console\Input\ArgvInput;
use Webkernel\Console\Middleware\ConsoleMiddleware;

/**
 * Discovers `#[ConsoleCommand]` methods and maps PHP parameters to argv.
 *
 * @phpstan-type CommandDef array{
 *   class: class-string,
 *   method: string,
 *   description: string,
 *   middleware: list<class-string>,
 *   reflection: \ReflectionMethod
 * }
 */
final class Kernel implements ComposableContract
{
    /** @var list<class-string> */
    private const BUILTIN = [
        DumpAutoloadCommand::class,
        ServerCommand::class,
    ];

    /** @var array<string, CommandDef>|null */
    private ?array $definitions = null;

    public static function api_name(): string
    {
        return 'console';
    }

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
            webterminal()->error('Unknown command: '.$name);

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
            webterminal()->error($e->getMessage());

            return ExitCode::ERROR;
        } catch (\InvalidArgumentException $e) {
            webterminal()->error($e->getMessage());

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

        $out = [];
        foreach ($this->command_classes() as $class) {
            if (! \class_exists($class)) {
                continue;
            }
            $ref = new \ReflectionClass($class);
            foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $attrs = $method->getAttributes(ConsoleCommand::class);
                if ($attrs === []) {
                    continue;
                }
                /** @var ConsoleCommand $attribute */
                $attribute = $attrs[0]->newInstance();
                $command_name = self::command_name($ref, $method, $attribute);
                if (isset($out[$command_name])) {
                    throw new \RuntimeException('Duplicate console command ['.$command_name.'].');
                }
                $out[$command_name] = [
                    'class' => $class,
                    'method' => $method->getName(),
                    'description' => $attribute->description,
                    'middleware' => $attribute->middleware,
                    'reflection' => $method,
                ];
            }
        }
        \ksort($out);
        $this->definitions = $out;

        return $out;
    }

    /**
     * @param CommandDef $definition
     */
    private function invoke(array $definition, ArgvInput $input, object $instance): ExitCode
    {
        $args = $this->bind_parameters($definition['reflection'], $input);
        $result = $definition['reflection']->invokeArgs($instance, $args);

        return self::normalize($result);
    }

    /**
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
            $is_argument = ! $is_bool && ! $param->isDefaultValueAvailable();

            if ($is_argument) {
                if (! \array_key_exists($position, $positional)) {
                    throw new \InvalidArgumentException('Missing argument <'.$param->getName().'>.');
                }
                $args[] = self::cast($type, $positional[$position], $param->allowsNull());
                $position++;
                continue;
            }

            $value = $this->option_value($param, $input, $kebab, $used_options);
            if ($value === null) {
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
            if ($is_bool) {
                $args[] = $value === true || $value === '1' || $value === 'true';
                continue;
            }
            $args[] = self::cast($type, $value, $param->allowsNull());
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
     * @param array<string, true> $used_options
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
     * @return list<class-string>
     */
    private function command_classes(): array
    {
        $classes = $this->dumped_commands();
        if ($classes !== []) {
            return $classes;
        }

        return self::BUILTIN;
    }

    /**
     * @return list<class-string>
     */
    private function dumped_commands(): array
    {
        $file = vendor_dir('composer/webkernel_commands.php');
        if (! \is_file($file)) {
            return [];
        }
        $loaded = require $file;
        if (! \is_array($loaded)) {
            return [];
        }
        $out = [];
        foreach ($loaded as $class) {
            if (\is_string($class) && $class !== '') {
                $out[] = $class;
            }
        }

        return $out;
    }

    private function print_help(?string $name): void
    {
        if ($name === null) {
            $this->print_list();

            return;
        }
        $definition = $this->definitions()[$name] ?? null;
        if ($definition === null) {
            webterminal()->error('Unknown command: '.$name);
            $this->print_list();

            return;
        }
        $this->print_command($name, $definition);
    }

    private function print_list(): void
    {
        echo "\n  ".Terminal::BOLD.'webkernel'.Terminal::RESET.' '.Terminal::muted('<command>')."\n\n";
        $definitions = $this->definitions();
        $width = 4;
        foreach (\array_keys($definitions) as $name) {
            $width = \max($width, \strlen($name));
        }
        foreach ($definitions as $name => $definition) {
            $pad = \str_repeat(' ', $width - \strlen($name) + 3);
            $desc = $definition['description'] !== '' ? $definition['description'] : $this->signature_hint($definition['reflection']);
            echo '  '.Terminal::CYAN.$name.Terminal::RESET.$pad.Terminal::muted($desc)."\n";
        }
        echo "\n";
    }

    /**
     * @param CommandDef $definition
     */
    private function print_command(string $name, array $definition): void
    {
        $hint = $this->signature_hint($definition['reflection']);
        echo "\n  ".Terminal::BOLD.'webkernel'.Terminal::RESET.' '.Terminal::CYAN.$name.Terminal::RESET;
        if ($hint !== '') {
            echo ' '.Terminal::muted($hint);
        }
        echo "\n";
        if ($definition['description'] !== '') {
            echo "\n  ".Terminal::muted($definition['description'])."\n";
        }
        echo "\n";
    }

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
                $parts[] = '[--'.$kebab.($shown !== '' ? '='.$shown : '').']';
                continue;
            }
            $parts[] = '<'.$param->getName().'>';
        }

        return \implode(' ', $parts);
    }

    private static function command_name(\ReflectionClass $class, \ReflectionMethod $method, ConsoleCommand $attribute): string
    {
        if ($attribute->name !== null && $attribute->name !== '') {
            return $attribute->name;
        }
        $short = $class->getShortName();
        if (\str_ends_with($short, 'Command')) {
            $short = \substr($short, 0, -7);
        }
        $class_part = self::kebab($short);
        if ($method->getName() === '__invoke') {
            return $class_part;
        }

        return $class_part.':'.self::kebab($method->getName());
    }

    private static function kebab(string $name): string
    {
        $kebab = \preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $name);
        $kebab = \str_replace('_', '-', \is_string($kebab) ? $kebab : $name);

        return \strtolower($kebab);
    }

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
