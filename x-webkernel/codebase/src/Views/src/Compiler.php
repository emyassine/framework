<?php declare(strict_types=1);

namespace Webkernel\Views;

/**
 * Compile a template to PHP. Cached by Engine. Not a Blade clone.
 *
 *   {{ $x }}              escaped echo
 *   {!! $x !!}            raw echo
 *   @if () @elseif () @else @endif
 *   @foreach () @endforeach
 *   @include('name')
 */
final class Compiler
{
    public static function compile(string $source): string
    {
        $php = file_get_contents($source);
        if ($php === false) {
            throw new \RuntimeException('Unable to read template: '.$source);
        }

        $php = preg_replace(
            '/\{!!\s*(.+?)\s*!!\}/s',
            '<?= $1 ?>',
            $php,
        ) ?? $php;

        $php = preg_replace(
            '/\{\{\s*(.+?)\s*\}\}/s',
            '<?= htmlspecialchars((string) ($1), ENT_QUOTES | ENT_SUBSTITUTE, \'UTF-8\') ?>',
            $php,
        ) ?? $php;

        $php = preg_replace('/@if\s*\((.*)\)/', '<?php if ($1): ?>', $php) ?? $php;
        $php = preg_replace('/@elseif\s*\((.*)\)/', '<?php elseif ($1): ?>', $php) ?? $php;
        $php = str_replace('@else', '<?php else: ?>', $php);
        $php = str_replace('@endif', '<?php endif; ?>', $php);
        $php = preg_replace('/@foreach\s*\((.*)\)/', '<?php foreach ($1): ?>', $php) ?? $php;
        $php = str_replace('@endforeach', '<?php endforeach; ?>', $php);
        $php = preg_replace(
            '/@include\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            '<?= \\Webkernel\\Views\\Engine::render(\'$1\', $__data) ?>',
            $php,
        ) ?? $php;

        return $php;
    }
}
