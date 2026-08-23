<?php declare(strict_types=1);

namespace Webkernel\View;

/**
 * Runtime for compiled views. Compiler.php is loaded only when a
 * template is missing or newer than its compiled file.
 */
final class Engine
{
    public const MODE_AUTO = 0;

    public const MODE_SLOW = 1;

    public const MODE_FAST = 2;

    private const PARENT_KEY = '@parentXYZABC';

    private const ESCAPE_STACK_0 = '-#1Z#-#2B#';

    private const ESCAPE_STACK_1 = '#3R#-#4X#-';

    /** @var array<string, mixed> */
    public array $variables = [];

    /** @var array<string, mixed> */
    private array $variables_global = [];

    /** @var array<string, string> */
    private array $sections = [];

    /** @var list<string> */
    private array $section_stack = [];

    /** @var array<string, array<int, string>> */
    private array $pushes = [];

    /** @var list<string> */
    private array $push_stack = [];

    private int $render_count = 0;

    /** @var array<string, string> */
    private array $template_files = [];

    /** @var array<string, string> */
    private array $compiled_files = [];

    /** @var array<string, list<string>> */
    private array $view_namespaces = [];

    /** @var array<string, list<string>> */
    private array $component_namespaces = [];

    /** @var array<string, string> */
    private array $alias_classes = [];

    /** @var list<string> */
    private array $component_stack = [];

    /** @var array<int, array<string, mixed>> */
    private array $component_data = [];

    /** @var array<int, array<string, mixed>> */
    private array $slots = [];

    private string $echo_format = '\\'.View::class.'::echo(%s)';

    private ?Compiler $compiler = null;

    /**
     * @param list<string> $template_path
     */
    public function __construct(
        private array $template_path,
        private readonly string $compile_dpath,
        private readonly int $mode = self::MODE_AUTO,
    ) {
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function run(string $view, array $variables = []): string
    {
        $this->sections = [];
        $this->variables = $this->variables_global === []
            ? $variables
            : array_merge($variables, $this->variables_global);
        ob_start();
        try {
            $this->include_view($view);
        } catch (\Throwable $e) {
            ob_get_clean();
            throw $e;
        }

        return $this->post_run(ltrim((string) ob_get_clean()));
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function run_child(string $view, array $variables = []): string
    {
        $backup = $this->variables;
        if ($variables !== []) {
            $this->variables = array_merge($this->variables, $variables);
        }
        $this->include_view($view);
        $this->variables = $backup;

        return '';
    }

    public function start_section(string $section, string $content = ''): void
    {
        if ($content === '') {
            ob_start() && $this->section_stack[] = $section;

            return;
        }
        $this->extend_section($section, $content);
    }

    public function stop_section(bool $overwrite = false): string
    {
        $last = array_pop($this->section_stack);
        if (! is_string($last) || $last === '') {
            throw new \RuntimeException('Cannot end a section without first starting one.');
        }
        $chunk = (string) ob_get_clean();
        if ($overwrite) {
            $this->sections[$last] = $chunk;
        } else {
            $this->extend_section($last, $chunk);
        }

        return $last;
    }

    public function yield_content(string $section, string $default = ''): string
    {
        if (! isset($this->sections[$section])) {
            return $default;
        }

        return str_replace(self::PARENT_KEY, $default, $this->sections[$section]);
    }

    public function start_push(string $section, string $content = ''): void
    {
        if ($content === '') {
            if (ob_start()) {
                $this->push_stack[] = $section;
            }

            return;
        }
        $this->extend_push($section, $content);
    }

    public function stop_push(): string
    {
        $last = array_pop($this->push_stack);
        if (! is_string($last) || $last === '') {
            throw new \RuntimeException('Cannot end a section without first starting one.');
        }
        $this->extend_push($last, (string) ob_get_clean());

        return $last;
    }

    public function compile_stack_final(mixed $a = null, mixed $b = null): string
    {
        return self::ESCAPE_STACK_0.$a.','.$b.self::ESCAPE_STACK_1;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function start_component(string $name, array $data = []): void
    {
        if (ob_start()) {
            $this->component_stack[] = $name;
            $i = count($this->component_stack) - 1;
            $this->component_data[$i] = $data;
            $this->slots[$i] = [];
        }
    }

    public function render_component(): string
    {
        $name = array_pop($this->component_stack);
        $cs = count($this->component_stack);
        $cd = array_merge(
            $this->component_data[$cs] ?? [],
            ['slot' => trim((string) ob_get_clean())],
            $this->slots[$cs] ?? [],
        );
        $html = $this->run_child((string) $name, $cd);
        unset($this->component_data[$cs], $this->slots[$cs]);
        foreach (array_keys($cd) as $key) {
            unset($this->variables[$key]);
        }

        return $html;
    }

    /**
     * @param string|array<string, mixed> $varname
     */
    public function share(string|array $varname, mixed $value = null): self
    {
        if (is_array($varname)) {
            $this->variables_global = array_merge($this->variables_global, $varname);
        } else {
            $this->variables_global[$varname] = $value;
        }

        return $this;
    }

    public function add_template_path(string $path): void
    {
        $this->template_path[] = rtrim($path, '/\\');
        $this->template_files = [];
        $this->compiled_files = [];
    }

    public function add_view_namespace(string $namespace, string $path): void
    {
        $this->view_namespaces[$namespace][] = rtrim($path, '/\\');
        $this->template_files = [];
        $this->compiled_files = [];
    }

    public function add_component_namespace(string $namespace, string $path): void
    {
        $this->component_namespaces[$namespace][] = rtrim($path, '/\\');
        $this->template_files = [];
        $this->compiled_files = [];
    }

    public function set_echo_format(string $format): void
    {
        $this->echo_format = $format;
    }

    public function add_alias_classes(string $alias_name, string $class_with_ns): void
    {
        $this->alias_classes[$alias_name] = $class_with_ns;
    }

    public function template_file(string $template_name): string
    {
        if (isset($this->template_files[$template_name])) {
            return $this->template_files[$template_name];
        }
        $namespace = '';
        $name = $template_name;
        $sep = strpos($template_name, '::');
        if ($sep !== false) {
            $namespace = substr($template_name, 0, $sep);
            $name = substr($template_name, $sep + 2);
        }
        $rel = str_contains($name, '/')
            ? $name
            : str_replace('.', '/', $name).'.view.php';

        return $this->template_files[$template_name] = $this->locate($rel, $namespace);
    }

    public function compiler(): Compiler
    {
        if ($this->compiler instanceof Compiler) {
            return $this->compiler;
        }
        $compiler = new Compiler($this->template_path, $this->compile_dpath, $this->mode);
        $compiler->throw_on_error = true;
        $compiler->set_echo_format($this->echo_format);
        foreach ($this->alias_classes as $alias => $class) {
            $compiler->add_alias_classes($alias, $class);
        }
        foreach ($this->view_namespaces as $namespace => $dirs) {
            foreach ($dirs as $dir) {
                $compiler->add_view_namespace($namespace, $dir);
            }
        }
        foreach ($this->component_namespaces as $namespace => $dirs) {
            foreach ($dirs as $dir) {
                $compiler->add_component_namespace($namespace, $dir);
            }
        }

        return $this->compiler = $compiler;
    }

    private function include_view(string $view): void
    {
        $this->ensure_compiled($view);
        $compiled = $this->compiled_file($view);
        extract($this->variables);
        include $compiled;
    }

    private function ensure_compiled(string $view): void
    {
        if ($this->mode === self::MODE_FAST) {
            return;
        }
        if ($this->mode === self::MODE_SLOW || $this->expired($view)) {
            if (! is_dir($this->compile_dpath) && ! mkdir($this->compile_dpath, 0775, true) && ! is_dir($this->compile_dpath)) {
                throw new \RuntimeException('Unable to create '.$this->compile_dpath);
            }
            $compiler = $this->compiler();
            $module = null;
            if (str_contains($view, '::')) {
                $module = explode('::', $view, 2)[0];
                if ($module === 'webkernel') {
                    $module = 'platform';
                }
            }
            $compiler->acl_view_module = $module;
            $compiler->compile($view);
            unset($this->compiled_files[$view], $this->template_files[$view]);
        }
    }

    private function expired(string $view): bool
    {
        $template = $this->template_file($view);
        $compiled = $this->compiled_file($view);
        if ($template === '' || $compiled === '' || ! is_file($compiled)) {
            return true;
        }

        return filemtime($compiled) < filemtime($template);
    }

    private function compiled_file(string $template_name): string
    {
        if (isset($this->compiled_files[$template_name])) {
            return $this->compiled_files[$template_name];
        }
        $full = $this->template_file($template_name);
        if ($full === '') {
            return $this->compiled_files[$template_name] = '';
        }

        return $this->compiled_files[$template_name] = $this->compile_dpath.'/'.basename($template_name).'_'.sha1($full).'.view.php.compiled';
    }

    private function locate(string $rel, string $namespace): string
    {
        foreach ($this->template_dirs($namespace) as $dir) {
            $path = $dir.'/'.$rel;
            if (is_file($path)) {
                return $path;
            }
        }

        return '';
    }

    /**
     * @return list<string>
     */
    private function template_dirs(string $namespace): array
    {
        if ($namespace === '') {
            return $this->template_path;
        }
        $dirs = $this->view_namespaces[$namespace] ?? [];
        foreach ($this->component_namespaces[$namespace] ?? [] as $dir) {
            if (! in_array($dir, $dirs, true)) {
                $dirs[] = $dir;
            }
        }

        return $dirs;
    }

    private function extend_section(string $section, string $content): void
    {
        if (isset($this->sections[$section])) {
            $content = str_replace(self::PARENT_KEY, $content, $this->sections[$section]);
        }
        $this->sections[$section] = $content;
    }

    private function extend_push(string $section, string $content): void
    {
        $this->pushes[$section][$this->render_count] = ($this->pushes[$section][$this->render_count] ?? '').$content;
    }

    private function yield_push_content(string $section, mixed $default = ''): string
    {
        if ($section === '') {
            return is_string($default) ? $default : '';
        }
        if (! isset($this->pushes[$section])) {
            return is_string($default) ? $default : '';
        }

        return implode(array_reverse($this->pushes[$section]));
    }

    private function post_run(string $html): string
    {
        if (! str_contains($html, self::ESCAPE_STACK_0)) {
            return $html;
        }
        $replaced = preg_replace_callback(
            '/'.preg_quote(self::ESCAPE_STACK_0, '/').'\s?([A-Za-z0-9_:() ,*.@$]+)\s?'.preg_quote(self::ESCAPE_STACK_1, '/').'/u',
            function (array $matches): string {
                $items = explode(',', trim($matches[1]));

                return $this->yield_push_content($items[0], $items[1] ?? '');
            },
            $html,
        );

        return is_string($replaced) ? $replaced : $html;
    }
}
