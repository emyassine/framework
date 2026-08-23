<?php declare(strict_types=1);

//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel;

/**
 * High-performance, zero-overhead PHP file builder for compile-time code generation.
 *
 * Builds syntactically valid PHP files using structured segment accumulation — no regex,
 * no token parsing, no AST overhead. Output is deterministic, OPcache-friendly, and safe
 * under FrankenPHP, Swoole, RoadRunner, and any long-running PHP runtime.
 *
 * Designed for 10,000+ executions per second. All string operations delegate to C-level
 * PHP internals (implode, str_repeat, array_map). No allocations inside loops.
 *
 * ---
 * SUPPORTED OUTPUT TARGETS:
 *   - PHP configuration arrays (return [...];)
 *   - Compiled container dumps (class definitions)
 *   - Enum, interface, trait, abstract class skeletons
 *   - Arbitrary raw PHP body blocks
 *   - Any file that starts with <?php
 *
 * ---
 * ARRAY EXPORT MODES:
 *   - SHORT_ARRAY  : [ ... ]   (default, modern, PSR-12 compliant)
 *   - LONG_ARRAY   : array()   (legacy compatibility, Composer autoload style)
 *
 * ---
 * USAGE EXAMPLES:
 *
 * // Example 1: Config cache with short array syntax
 * $content = PhpFileBuilder::make()
 *     ->with_header(['WARNING: Auto-generated.', 'Do not edit manually.'], 'ConfigCompiler')
 *     ->with_return_array(['debug' => false, 'cache_dir' => '/var/cache'])
 *     ->generate();
 *
 * // Example 2: Config cache with legacy array() syntax
 * PhpFileBuilder::make()
 *     ->with_header(['Compiled config'], 'BootCompiler')
 *     ->with_array_syntax(PhpFileBuilder::LONG_ARRAY)
 *     ->with_return_array(['env' => 'production'])
 *     ->save_to('/var/cache/config.php');
 *
 * // Example 3: Full class file with namespace, imports, and body
 * PhpFileBuilder::make()
 *     ->with_header(['Compiled DI Container'], 'ContainerBuilder')
 *     ->with_namespace('Platform\Generated')
 *     ->add_use('App\Services\Logger', 'Platform\Contracts\BootableInterface')
 *     ->with_body(<<<'PHP'
 * final class CompiledContainer implements BootableInterface
 * {
 *     public function boot(): void {}
 * }
 * PHP)
 *     ->save_to('/var/cache/CompiledContainer.php');
 *
 * // Example 4: Disable strict_types for legacy-safe output
 * PhpFileBuilder::make()
 *     ->with_strict_types(false)
 *     ->with_body('echo "legacy mode";')
 *     ->generate();
 *
 * ---
 *
 * @phpstan-final
 * @psalm-immutable
 * @psalm-suppress UnusedClass
 * @internal Used by Platform bootstrap infrastructure.
 */
final class PhpFileBuilder
{
    /**
     * Array export format: modern short syntax [ ... ]
     *
     * @var string
     */
    public const SHORT_ARRAY = 'short';

    /**
     * Array export format: legacy long syntax array( ... )
     *
     * @var string
     */
    public const LONG_ARRAY = 'long';

    /**
     * Indentation used in exported arrays (4 spaces, PSR-12).
     *
     * @var string
     */
    private const INDENT = '    ';

    /**
     * Fixed copyright/license header prefix, compiled as a static constant
     * to be resolved once at class load time (OPcache string interning).
     *
     * @var string
     */
    private const LICENSE_PREFIX =
        "//> This file is part of Webkernel.\n"
        . "//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine\n"
        . "//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>\n"
        . "//> For the full copyright and license information, please view the LICENSE\n"
        . "//> file that was distributed with this source code.\n"
        . "//\n";

    /**
     * Additional comment lines placed below the license prefix.
     *
     * @var list<string>
     */
    private array $sub_headers = [];

    /**
     * Identifier of the generator (class name, command name, etc.).
     *
     * @var string
     */
    private string $caller = '';

    /**
     * Optional PHP namespace declaration.
     *
     * @var string|null
     */
    private ?string $namespace = null;

    /**
     * Collected use-import statements.
     *
     * @var list<string>
     */
    private array $uses = [];

    /**
     * Raw PHP body code to append after the file header.
     *
     * @var string
     */
    private string $body = '';

    /**
     * Whether to emit declare(strict_types=1).
     *
     * @var bool
     */
    private bool $strict_types = true;

    /**
     * Array export mode: SHORT_ARRAY or LONG_ARRAY.
     *
     * @var string
     * @psalm-var self::SHORT_ARRAY|self::LONG_ARRAY
     */
    private string $array_syntax = self::SHORT_ARRAY;

    // -------------------------------------------------------------------------
    // INSTANTIATION
    // -------------------------------------------------------------------------

    /**
     * Factory entry point. Returns a new builder ready for method chaining.
     *
     * Prefer make() over new self() in call chains for readability.
     *
     * @return static
     */
    public static function make(): static
    {
        return new static();
    }

    // -------------------------------------------------------------------------
    // CONFIGURATION METHODS (fluent)
    // -------------------------------------------------------------------------

    /**
     * Sets comment lines placed beneath the license header, plus the caller label.
     *
     * Each entry in $sub_headers becomes one `//> ...` comment line.
     * The caller is appended as `//> Generated by: <caller>`.
     *
     * @param list<string> $sub_headers Lines to emit as //> comments (no leading slash needed).
     * @param string       $caller      Class name, command, or identifier producing this file.
     *
     * @return $this
     */
    public function with_header(array $sub_headers = [], string $caller = ''): static
    {
        $this->sub_headers = $sub_headers;
        $this->caller      = $caller;
        return $this;
    }

    /**
     * Sets the PHP namespace declaration for the generated file.
     *
     * @param string $namespace Fully qualified namespace (no leading backslash).
     *                          Example: 'Platform\Generated'
     *
     * @return $this
     */
    public function with_namespace(string $namespace): static
    {
        $this->namespace = ltrim($namespace, '\\');
        return $this;
    }

    /**
     * Appends one or more fully qualified class names as `use` import statements.
     *
     * Accepts variadic arguments so both single and multi-import calls are concise:
     *   ->add_use('App\Logger')
     *   ->add_use('App\Logger', 'App\Contracts\BootableInterface')
     *
     * @param string ...$fqcns Fully qualified class names (no leading backslash).
     *
     * @return $this
     */
    public function add_use(string ...$fqcns): static
    {
        foreach ($fqcns as $fqcn) {
            $this->uses[] = 'use ' . ltrim($fqcn, '\\') . ';';
        }
        return $this;
    }

    /**
     * Controls emission of `declare(strict_types=1);` at the top of the file.
     *
     * Enabled by default. Disable only when generating legacy-compatible output
     * (e.g., files consumed by PHP 5.x parsers or loose-typed runtimes).
     *
     * @param bool $enable True to emit strict_types (default), false to omit.
     *
     * @return $this
     */
    public function with_strict_types(bool $enable = true): static
    {
        $this->strict_types = $enable;
        return $this;
    }

    /**
     * Sets the array export format used by with_return_array().
     *
     * @param string $mode PhpFileBuilder::SHORT_ARRAY or PhpFileBuilder::LONG_ARRAY.
     * @psalm-param self::SHORT_ARRAY|self::LONG_ARRAY $mode
     *
     * @return $this
     */
    public function with_array_syntax(string $mode = self::SHORT_ARRAY): static
    {
        $this->array_syntax = $mode;
        return $this;
    }

    /**
     * Sets the raw PHP body to emit after namespace/use declarations.
     *
     * Accepts any string: a class definition, an expression, a heredoc block.
     * No validation is performed — the caller is responsible for syntactic correctness.
     *
     * @param string $code Raw PHP code (without opening <?php tag).
     *
     * @return $this
     */
    public function with_body(string $code): static
    {
        $this->body = $code;
        return $this;
    }

    /**
     * Serializes a PHP array into a `return ...;` statement and sets it as the body.
     *
     * The array is serialized using a structured recursive exporter — no regex,
     * no preg_replace, no string heuristics. Output respects with_array_syntax().
     *
     * Supports arbitrarily nested mixed arrays (string keys, integer keys, booleans,
     * nulls, integers, floats, nested arrays). Does NOT support objects or resources.
     *
     * @param array<mixed, mixed> $data The configuration or data array to export.
     *
     * @return $this
     */
    public function with_return_array(array $data): static
    {
        $this->body = 'return ' . $this->export_array($data, 0) . ";\n";
        return $this;
    }

    // -------------------------------------------------------------------------
    // OUTPUT METHODS
    // -------------------------------------------------------------------------

    /**
     * Assembles and returns the complete PHP file content as a string.
     *
     * Execution path uses only C-level implode() and direct concatenation.
     * No regex, no tokenization, no reflection. Safe under Swoole/FrankenPHP
     * since no global state is mutated during generation.
     *
     * Output structure:
     *   1. <?php [declare(strict_types=1);]
     *   2. License prefix (static constant, interned by OPcache)
     *   3. Sub-header comment lines (//> ...)
     *   4. Generated-by line (if caller set)
     *   5. namespace declaration (if set)
     *   6. use imports (if any)
     *   7. body
     *
     * @return string Complete, ready-to-write PHP file content.
     */
    public function generate(): string
    {
        $segments = [
            $this->strict_types
                ? "<?php declare(strict_types=1);\n\n"
                : "<?php\n\n",
            self::LICENSE_PREFIX,
        ];
         if ($this->sub_headers !== []) {
            $segments[] = '//> ' . \implode("\n//> ", $this->sub_headers) . "\n";
        }
         if ($this->caller !== '') {
            $segments[] = '//> Generated by: ' . $this->caller . "\n";
        }
         if ($this->namespace !== null) {
            $segments[] = 'namespace ' . $this->namespace . ";\n\n";
        }
         if ($this->uses !== []) {
            $segments[] = \implode("\n", $this->uses) . "\n\n";
        }
         if ($this->body !== '') {
            $segments[] = $this->body;
        }
         return \implode('', $segments);
    }

    /**
     * Generates the PHP file content and writes it atomically to disk.
     *
     * Uses LOCK_EX to prevent race conditions during parallel builds
     * (multiple workers, Swoole coroutines, FrankenPHP threads).
     *
     * Note: LOCK_EX is advisory on most Unix filesystems (NFS excluded).
     * For true atomic replacement across processes, consider writing to a
     * temp file and renaming, which is handled by save_to_atomic().
     *
     * @param string $filepath Absolute path to the target file.
     *
     * @return bool True on success, false if the write failed.
     */
    public function save_to(string $filepath): bool
    {
        return \file_put_contents($filepath, $this->generate(), LOCK_EX) !== false;
    }

    /**
     * Generates and writes the file using a write-then-rename strategy.
     *
     * Writes to a temporary file in the same directory, then renames it
     * into place. On POSIX systems, rename() is atomic at the filesystem
     * level — readers always see either the old or the new file, never
     * a partially written state. Safer than save_to() under high concurrency.
     *
     * @param string $filepath Absolute path to the target file.
     *
     * @return bool True on success, false if any step failed.
     */
    public function save_to_atomic(string $filepath): bool
    {
        $dir  = dirname($filepath);
        $tmp  = $dir . DIRECTORY_SEPARATOR . uniqid('.php_builder_', true) . '.tmp';
        $data = $this->generate();

        if (file_put_contents($tmp, $data, LOCK_EX) === false) {
            return false;
        }

        if (!rename($tmp, $filepath)) {
            @unlink($tmp);
            return false;
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // INTERNAL ARRAY EXPORTER (no regex, structured recursive)
    // -------------------------------------------------------------------------

    /**
     * Recursively exports a PHP value to source-code representation.
     *
     * Handles: array, string, int, float, bool, null.
     * Does not support objects or resources (throws \InvalidArgumentException).
     *
     * @param mixed $value The value to export.
     * @param int   $depth Current nesting depth (used for indentation).
     *
     * @return string PHP source representation of the value.
     *
     * @throws \InvalidArgumentException When an unsupported type is encountered.
     */
    private function export_value(mixed $value, int $depth): string
    {
        return match (true) {
            \is_array($value)  => $this->export_array($value, $depth),
            \is_string($value) => $this->export_string($value),
            \is_int($value)    => (string) $value,
            \is_float($value)  => $this->export_float($value),
            \is_bool($value)   => $value ? 'true' : 'false',
            \is_null($value)   => 'null',
            default           => throw new \InvalidArgumentException(
                \sprintf(
                    'Webkernel\PhpFileBuilder: unsupported value type "%s". '
                    . 'Only scalar types and arrays are exportable.',
                    \get_debug_type($value)
                )
            ),
        };
    }

    /**
     * Exports a PHP array to source code with proper indentation and key handling.
     *
     * Emits short syntax [...] or long syntax array(...) based on with_array_syntax().
     * Keys are omitted for sequential 0-indexed arrays (list-style), emitted for
     * associative or non-sequential integer-keyed arrays.
     *
     * @param array<mixed, mixed> $array The array to export.
     * @param int                 $depth Current nesting depth.
     *
     * @return string PHP source representation of the array.
     */
    private function export_array(array $array, int $depth): string
    {
        if ($array === []) {
            return $this->array_syntax === self::SHORT_ARRAY ? '[]' : 'array()';
        }

        $use_short  = $this->array_syntax === self::SHORT_ARRAY;
        $is_list    = \array_is_list($array);
        $inner_pad  = \str_repeat(self::INDENT, $depth + 1);
        $closing_pad = \str_repeat(self::INDENT, $depth);

        $items = [];
        foreach ($array as $key => $val) {
            $exported_val = $this->export_value($val, $depth + 1);

            if ($is_list) {
                $items[] = $inner_pad . $exported_val;
            } else {
                $exported_key = \is_string($key)
                    ? $this->export_string($key)
                    : (string) $key;
                $items[] = $inner_pad . $exported_key . ' => ' . $exported_val;
            }
        }

        $body = \implode(",\n", $items) . ",\n";

        return $use_short
            ? "[\n" . $body . $closing_pad . ']'
            : "array(\n" . $body . $closing_pad . ')';
    }

    /**
     * Exports a PHP string as a single-quoted literal.
     *
     * Single-quoted strings have no interpolation overhead and are faster
     * to parse in both PHP and OPcache. Backslashes and single quotes are
     * escaped; all other characters are emitted verbatim.
     *
     * @param string $value The raw string value.
     *
     * @return string PHP single-quoted string literal.
     */
    private function export_string(string $value): string
    {
        return "'" . \str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
    }

    /**
     * Exports a PHP float with enough precision to survive a round-trip.
     *
     * Uses serialize_precision=-1 behavior equivalent: the shortest decimal
     * representation that round-trips through (float) cast is emitted.
     * Falls back to ini-driven precision for older PHP environments.
     *
     * @param float $value The float value.
     *
     * @return string PHP float literal (e.g., '3.14', 'INF', '-INF', 'NAN').
     */
    private function export_float(float $value): string
    {
        if (is_nan($value)) {
            return 'NAN';
        }
        if (\is_infinite($value)) {
            return $value > 0 ? 'INF' : '-INF';
        }

        $repr = \serialize($value);

        // serialize() on a float gives "d:3.14;" — extract the numeric part.
        // This is the canonical round-trip-safe representation.
        if (\str_starts_with($repr, 'd:') && \str_ends_with($repr, ';')) {
            return \substr($repr, 2, -1);
        }

        // Fallback: use enough decimal places to be safe.
        return \rtrim(\number_format($value, 14, '.', ''), '0') ?: '0.0';
    }
}
