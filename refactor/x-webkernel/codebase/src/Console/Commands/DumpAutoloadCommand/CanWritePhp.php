<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Console\Commands\DumpAutoloadCommand;

use Webkernel\DevEnv\IdeHelper;

trait CanWritePhp
{
    use _DumpAutoloadCommand;

    private function write_php(string $path, mixed $data): void
    {
        $export = var_export($data, true);
        $header = IdeHelper::generated_header();
        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//> Generated. Do not edit.
//> Host moved? Run: composer dump-autoload

return {$export};

PHP;
        file_put_contents($path, $body, LOCK_EX);
    }

    /**
     * @param array<string, string> $map
     */
    private function write_classmap(string $path, array $map, string $vendor_dir, string $root): void
    {
        $vendor_dir = rtrim(str_replace('\\', '/', $vendor_dir), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $header = IdeHelper::generated_header();
        $lines = [];

        foreach ($map as $class => $file) {
            $file = str_replace('\\', '/', $file);
            $key = var_export($class, true);
            $lines[] = '    '.$key.' => '.$this->path_code($file, $vendor_dir, $root).',';
        }

        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//>
//> Generated. Do not edit.

{$this->dump_path_prefix($vendor_dir, $root)}

return array(
PHP;
        $body .= ($lines === [] ? "\n" : "\n".implode("\n", $lines)."\n").");\n";
        file_put_contents($path, $body, LOCK_EX);
    }

    /**
     * @param list<string> $files
     */
    private function write_files(string $path, array $files, string $vendor_dir, string $root): void
    {
        $vendor_dir = rtrim(str_replace('\\', '/', $vendor_dir), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $header = IdeHelper::generated_header();
        $items = [];
        foreach ($files as $file) {
            $items[] = '    '.$this->path_code(str_replace('\\', '/', $file), $vendor_dir, $root).',';
        }

        $list = $items === [] ? '' : "\n".implode("\n", $items)."\n";

        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//>
//> Generated. Do not edit.

{$this->dump_path_prefix($vendor_dir, $root)}

\$files = [{$list}];

foreach (\$files as \$file) {
    \$loaded = \\function_exists('webkernel_include') ? \\webkernel_include(\$file) : @include \$file;
    if (\$loaded === false) {
        throw new \\RuntimeException('Unable to load required file: '.\$file);
    }
}

PHP;
        file_put_contents($path, $body, LOCK_EX);
    }

    /**
     * @param list<string> $paths
     */
    private function write_path_list(string $path, array $paths, string $vendor_dir, string $root): void
    {
        $vendor_dir = rtrim(str_replace('\\', '/', $vendor_dir), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $header = IdeHelper::generated_header();
        $items = [];
        foreach ($paths as $item) {
            $items[] = '    '.$this->path_code(str_replace('\\', '/', $item), $vendor_dir, $root).',';
        }
        $list = $items === [] ? '' : "\n".implode("\n", $items)."\n";

        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//>
//> Generated. Do not edit.

{$this->dump_path_prefix($vendor_dir, $root)}

return [{$list}];

PHP;
        file_put_contents($path, $body, LOCK_EX);
    }

    /**
     * @param array<string, list<string>> $namespaces
     */
    private function write_namespaced_paths(string $path, array $namespaces, string $vendor_dir, string $root): void
    {
        $vendor_dir = rtrim(str_replace('\\', '/', $vendor_dir), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $header = IdeHelper::generated_header();
        $ns_lines = [];
        $dir_lines = [];
        foreach ($namespaces as $namespace => $dirs) {
            $items = [];
            foreach ($dirs as $dir) {
                $code = $this->path_code(str_replace('\\', '/', $dir), $vendor_dir, $root);
                $items[] = '            '.$code.',';
                $dir_lines[$dir] = '        '.$code.',';
            }
            $list = $items === [] ? '' : "\n".implode("\n", $items)."\n        ";
            $ns_lines[] = '        '.var_export($namespace, true).' => ['.$list.'],';
        }
        $ns_body = $ns_lines === [] ? '' : "\n".implode("\n", $ns_lines)."\n    ";
        $dirs_body = $dir_lines === [] ? '' : "\n".implode("\n", $dir_lines)."\n    ";

        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//>
//> Generated. Do not edit.

{$this->dump_path_prefix($vendor_dir, $root)}

return [
    'dirs' => [{$dirs_body}],
    'namespaces' => [{$ns_body}],
];

PHP;
        file_put_contents($path, $body, LOCK_EX);
    }

    /**
     * @param list<string> $classes
     */
    private function write_class_list(string $path, array $classes): void
    {
        $header = IdeHelper::generated_header();
        $lines = [];
        foreach ($classes as $class) {
            $lines[] = '    \\'.$class.'::class,';
        }
        $list = $lines === [] ? '' : "\n".implode("\n", $lines)."\n";

        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//>
//> Generated. Do not edit.

return [{$list}];

PHP;
        file_put_contents($path, $body, LOCK_EX);
    }

    /**
     * @param array<string, class-string> $map
     */
    private function write_composables(string $path, array $map): void
    {
        $header = IdeHelper::generated_header();
        $lines = [];
        foreach ($map as $name => $class) {
            $lines[] = '    '.var_export($name, true).' => \\'.$class.'::class,';
        }
        $list = $lines === [] ? '' : "\n".implode("\n", $lines)."\n";

        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//>
//> Generated. Do not edit.

return [{$list}];

PHP;
        file_put_contents($path, $body, LOCK_EX);
    }

    /**
     * @param array<string, class-string> $map
     */
    private function write_webapp_ide(array $map): void
    {
        $path = $this->codebase_root().'/_ide_helpers/_ide_webapp.php';
        $header = IdeHelper::generated_header();
        $methods = [
            '     * @method \Webkernel\Composables\ConfigComposable|mixed config(?string $key = null, mixed $default = null)',
        ];
        ksort($map);
        foreach ($map as $name => $class) {
            if ($name === 'config') {
                continue;
            }
            $methods[] = '     * '.$this->composable_phpdoc($name, $class);
        }
        $block = implode("\n", $methods);
        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//>
//> Generated. Do not edit. IDE / PHPStan stub for webapp() composables.

namespace Webkernel;

if (false) {
    /**
{$block}
     */
    final class WebApp
    {
    }
}

PHP;
        file_put_contents($path, $body, LOCK_EX);
    }

    /**
     * @param class-string $class
     */
    private function composable_phpdoc(string $name, string $class): string
    {
        if (method_exists($class, '__invoke')) {
            try {
                $ref = new \ReflectionMethod($class, '__invoke');
            } catch (\ReflectionException) {
                return '@method \\'.$class.' '.$name.'()';
            }
            $params = [];
            foreach ($ref->getParameters() as $parameter) {
                $params[] = $this->phpdoc_parameter($parameter, $class);
            }
            $return = $this->phpdoc_type($ref->getReturnType(), $class) ?? ('\\'.$class);

            return '@method '.$return.' '.$name.'('.implode(', ', $params).')';
        }

        return '@method \\'.$class.' '.$name.'()';
    }

    /**
     * @param class-string $class
     */
    private function phpdoc_parameter(\ReflectionParameter $parameter, string $class): string
    {
        $type = $this->phpdoc_type($parameter->getType(), $class);
        $piece = ($type !== null ? $type.' ' : '').'$'.$parameter->getName();
        if ($parameter->isDefaultValueAvailable()) {
            $default = $parameter->getDefaultValue();
            if ($default === []) {
                $piece .= ' = []';
            } elseif ($default === null) {
                $piece .= ' = null';
            } else {
                $piece .= ' = '.var_export($default, true);
            }
        } elseif ($parameter->isOptional() || $parameter->allowsNull()) {
            $piece .= ' = null';
        }

        return $piece;
    }

    /**
     * @param class-string $class
     */
    private function phpdoc_type(?\ReflectionType $type, string $class): ?string
    {
        if ($type instanceof \ReflectionNamedType) {
            $name = $type->getName();
            if ($name === 'self' || $name === 'static') {
                $name = '\\'.$class;
            } elseif (! $type->isBuiltin()) {
                $name = '\\'.$name;
            }
            if ($type->allowsNull() && $name !== 'mixed' && $name !== 'null') {
                return '?'.$name;
            }

            return $name;
        }

        return null;
    }
}
