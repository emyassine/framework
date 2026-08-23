<?php declare(strict_types=1);

namespace Webkernel\Route\Compile;

/**
 * Opcache-friendly compiled dispatcher file. Closures are dumped as source
 * so the hot path does not re-require route files or run Pattern/Generator.
 *
 * @internal
 *
 * @phpstan-import-type RouteData from Generator
 * @phpstan-type Payload array{compiled_at: int, host: string, files: array<string, int>, data: RouteData}
 */
final class Cache
{
    private const DIRECTORY_PERMISSIONS = 0775;

    private const FILE_PERMISSIONS = 0664;

    public static function path(): string
    {
        return self::directory().'/compiled_routes.php';
    }

    public static function directory(): string
    {
        if (function_exists('webapp_path')) {
            return webapp_path('platform/storage/framework/cache');
        }

        return dirname(__DIR__, 5).'/platform/storage/framework/cache';
    }

    /**
     * @return Payload|null
     */
    public static function payload(): ?array
    {
        $path = self::path();
        if (! is_file($path)) {
            return null;
        }
        $value = include $path;
        if (! is_array($value) || ! isset($value['data']) || ! is_array($value['data'])) {
            return null;
        }
        /** @var Payload $value */
        return $value;
    }

    /**
     * @return RouteData|null
     */
    public static function read(?string $path = null): ?array
    {
        if ($path !== null && $path !== self::path()) {
            if (! is_file($path)) {
                return null;
            }
            $value = include $path;

            return is_array($value) ? $value : null;
        }
        $payload = self::payload();

        return $payload['data'] ?? null;
    }

    /**
     * @param RouteData $data
     * @param array{compiled_at?: int, host?: string, files?: array<string, int>} $meta
     */
    public static function write(string $path, array $data, array $meta = []): void
    {
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, self::DIRECTORY_PERMISSIONS, true) && ! is_dir($directory)) {
            return;
        }
        if (! is_writable($directory)) {
            return;
        }

        $payload = [
            'compiled_at' => $meta['compiled_at'] ?? time(),
            'host' => $meta['host'] ?? '',
            'files' => $meta['files'] ?? [],
            'data' => $data,
        ];
        $tmp = $path.'.'.getmypid().'.tmp';
        $body = "<?php declare(strict_types=1);\n\nreturn ".self::export($payload).";\n";
        if (file_put_contents($tmp, $body) === false) {
            return;
        }
        chmod($tmp, self::FILE_PERMISSIONS);
        if (! rename($tmp, $path)) {
            @unlink($tmp);

            return;
        }
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($path, true);
        }
    }

    private static function export(mixed $value): string
    {
        if ($value instanceof \Closure) {
            return self::export_closure($value);
        }
        if (is_object($value)) {
            return self::export_object($value);
        }
        if (is_array($value)) {
            if ($value === []) {
                return '[]';
            }
            $is_list = array_is_list($value);
            $parts = [];
            foreach ($value as $key => $item) {
                $exported = self::export($item);
                $parts[] = $is_list ? $exported : var_export($key, true).' => '.$exported;
            }

            return '['.implode(', ', $parts).']';
        }

        return var_export($value, true);
    }

    private static function export_object(object $value): string
    {
        if (! method_exists($value, '__set_state')) {
            throw new \RuntimeException('Cannot compile route handler of type '.$value::class.'.');
        }
        $vars = get_object_vars($value);

        return '\\'.$value::class.'::__set_state('.self::export($vars).')';
    }

    private static function export_closure(\Closure $closure): string
    {
        $ref = new \ReflectionFunction($closure);
        if ($ref->getStaticVariables() !== []) {
            throw new \RuntimeException('Cannot compile route closures that bind variables.');
        }
        $file = $ref->getFileName();
        $start = $ref->getStartLine();
        $end = $ref->getEndLine();
        if (! is_string($file) || ! is_file($file) || $start < 1 || $end < $start) {
            throw new \RuntimeException('Cannot compile eval route closures.');
        }
        $lines = file($file);
        if ($lines === false) {
            throw new \RuntimeException('Cannot read route file for compilation: '.$file);
        }
        $chunk = implode('', array_slice($lines, $start - 1, $end - $start + 1));
        $source = self::extract_closure_source($chunk);
        if ($source === '') {
            throw new \RuntimeException('Cannot extract route closure source from '.$file.':'.$start);
        }

        return $source;
    }

    private static function extract_closure_source(string $chunk): string
    {
        $tokens = token_get_all('<?php '.$chunk);
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (! is_array($token)) {
                continue;
            }
            $static = $token[0] === T_STATIC;
            $fn_index = $static ? self::next_code_token($tokens, $i + 1) : $i;
            if ($fn_index === null) {
                continue;
            }
            $fn = $tokens[$fn_index];
            if (! is_array($fn) || ($fn[0] !== T_FN && $fn[0] !== T_FUNCTION)) {
                continue;
            }
            if ($fn[0] === T_FUNCTION && self::closure_has_use($tokens, $fn_index)) {
                throw new \RuntimeException('Cannot compile route closures that bind variables.');
            }
            $end = $fn[0] === T_FN
                ? self::end_of_arrow($tokens, $fn_index)
                : self::end_of_function($tokens, $fn_index);
            if ($end === null) {
                return '';
            }

            return self::join_tokens($tokens, $static ? $i : $fn_index, $end);
        }

        return '';
    }

    /**
     * @param list<array{int, string, int}|string> $tokens
     */
    private static function next_code_token(array $tokens, int $from): ?int
    {
        $count = count($tokens);
        for ($i = $from; $i < $count; $i++) {
            $token = $tokens[$i];
            if (is_array($token) && ($token[0] === T_WHITESPACE || $token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT)) {
                continue;
            }

            return $i;
        }

        return null;
    }

    /**
     * @param list<array{int, string, int}|string> $tokens
     */
    private static function closure_has_use(array $tokens, int $fn_index): bool
    {
        $count = count($tokens);
        for ($i = $fn_index + 1; $i < $count; $i++) {
            $token = $tokens[$i];
            if ($token === '{' || (is_array($token) && $token[0] === T_CURLY_OPEN)) {
                return false;
            }
            if (is_array($token) && $token[0] === T_USE) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array{int, string, int}|string> $tokens
     */
    private static function end_of_arrow(array $tokens, int $fn_index): ?int
    {
        $count = count($tokens);
        $seen_arrow = false;
        $paren = 0;
        $bracket = 0;
        $brace = 0;
        for ($i = $fn_index + 1; $i < $count; $i++) {
            $token = $tokens[$i];
            if (! $seen_arrow) {
                if (is_array($token) && $token[0] === T_DOUBLE_ARROW) {
                    $seen_arrow = true;
                }
                continue;
            }
            if (is_array($token)) {
                if ($token[0] === T_WHITESPACE || $token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
                    continue;
                }
                continue;
            }
            if ($token === '(') {
                $paren++;
                continue;
            }
            if ($token === '[') {
                $bracket++;
                continue;
            }
            if ($token === '{') {
                $brace++;
                continue;
            }
            if ($token === ')') {
                if ($paren === 0 && $bracket === 0 && $brace === 0) {
                    return $i - 1;
                }
                $paren--;
                continue;
            }
            if ($token === ']') {
                if ($paren === 0 && $bracket === 0 && $brace === 0) {
                    return $i - 1;
                }
                $bracket--;
                continue;
            }
            if ($token === '}') {
                if ($paren === 0 && $bracket === 0 && $brace === 0) {
                    return $i - 1;
                }
                $brace--;
                continue;
            }
            if (($token === ',' || $token === ';') && $paren === 0 && $bracket === 0 && $brace === 0) {
                return $i - 1;
            }
        }

        return $seen_arrow ? $count - 1 : null;
    }

    /**
     * @param list<array{int, string, int}|string> $tokens
     */
    private static function end_of_function(array $tokens, int $fn_index): ?int
    {
        $count = count($tokens);
        $brace = 0;
        $started = false;
        for ($i = $fn_index + 1; $i < $count; $i++) {
            $token = $tokens[$i];
            $char = is_array($token) ? '' : $token;
            if ($char === '{') {
                $brace++;
                $started = true;
                continue;
            }
            if ($char === '}') {
                $brace--;
                if ($started && $brace === 0) {
                    return $i;
                }
            }
        }

        return null;
    }

    /**
     * @param list<array{int, string, int}|string> $tokens
     */
    private static function join_tokens(array $tokens, int $from, int $to): string
    {
        $out = '';
        for ($i = $from; $i <= $to; $i++) {
            $token = $tokens[$i];
            $out .= is_array($token) ? $token[1] : $token;
        }

        return trim($out);
    }
}
