<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\View;

use Webkernel\View\Compile\Directives;
use Webkernel\View\Compile\Mode;

/**
 * Runtime for compiled views. Compiler is loaded only on compile miss.
 */
final class Engine
{

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

    /** @var array<string, string> */
    private array $compiled_map = [];

    /** @var array<string, list<string>> */
    private array $view_namespaces = [];

    /** @var array<string, list<string>> */
    private array $component_namespaces = [];

    /** @var array<string, true> */
    private array $once = [];

    /** @var list<string> */
    private array $component_stack = [];

    /** @var array<int, array<string, mixed>> */
    private array $component_data = [];

    /** @var array<int, array<string, mixed>> */
    private array $slots = [];

    /** @var array<int, list<string>> */
    private array $slot_stack = [];

    private string $echo_format = '\\'.View::class.'::echo(%s)';

    private ?Compiler $compiler = null;

    private readonly Directives $directives;

    /**
     * @param $template_path list<string>
     * @param $compile_dpath string
     * @param $mode Mode
     */
    public function __construct(
        private array $template_path,
        private readonly string $compile_dpath,
        private readonly Mode $mode = Mode::Auto,
    ) {
        $this->directives = new Directives();
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function run(string $view, array $variables = []): string
    {
        $this->once = [];
        $this->sections = [];
        $this->pushes = [];
        $this->push_stack = [];
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

    /**
     * @param $section string
     * @param $default mixed
     *
     * @return string
     */
    public function stack(string $section, mixed $default = ''): string
    {
        return self::ESCAPE_STACK_0.$section.','.$default.self::ESCAPE_STACK_1;
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
            $this->slot_stack[$i] = [];
        }
    }

    public function slot(string $name, mixed $content = null): void
    {
        $i = count($this->component_stack) - 1;
        if ($i < 0) {
            return;
        }
        if (func_num_args() === 2) {
            $this->slots[$i][$name] = $content;

            return;
        }
        if (ob_start()) {
            $this->slots[$i][$name] = '';
            $this->slot_stack[$i][] = $name;
        }
    }

    public function end_slot(): void
    {
        $i = count($this->component_stack) - 1;
        if ($i < 0) {
            return;
        }
        $name = array_pop($this->slot_stack[$i]);
        if (! is_string($name) || $name === '') {
            throw new \RuntimeException('Cannot end a slot without first starting one.');
        }
        $this->slots[$i][$name] = trim((string) ob_get_clean());
    }

    /**
     * @return string
     */
    public function render_component(): string
    {
        $name = array_pop($this->component_stack);
        $cs = count($this->component_stack);
        $data = $this->component_data[$cs] ?? [];
        $slots = $this->slots[$cs] ?? [];
        $slots['slot'] = trim((string) ob_get_clean());
        $bag = [];
        foreach ($data as $key => $value) {
            if ($key === 'slot' || $key === 'attributes' || array_key_exists($key, $slots)) {
                continue;
            }
            $bag[$key] = $value;
        }
        $cd = array_merge($data, $slots, [
            'attributes' => new AttributeBag($bag),
        ]);
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

    /**
     * First call for $id returns true and records it. Later calls return false.
     *
     * @param $id string
     *
     * @return bool
     */
    public function once(string $id): bool
    {
        if (isset($this->once[$id])) {
            return false;
        }
        $this->once[$id] = true;

        return true;
    }

    public function set_echo_format(string $format): void
    {
        $this->echo_format = $format;
    }

    /**
     * Dump-autoload map: view name => compiled absolute path. Fast include skips locate.
     *
     * @param $map array<string, string>
     *
     * @return void
     */
    public function set_compiled_map(array $map): void
    {
        $this->compiled_map = $map;
        $this->compiled_files = [];
    }

    /**
     * @param $view string
     *
     * @return string
     */
    public function compiled_path(string $view): string
    {
        return $this->compiled_file($view);
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
        if (str_contains($name, '/')) {
            return $this->template_files[$template_name] = $this->locate($name, $namespace);
        }
        $path = str_replace('.', '/', $name);
        foreach ([$path.'.view.php', $path.'/index.view.php'] as $rel) {
            $found = $this->locate($rel, $namespace);
            if ($found !== '') {
                return $this->template_files[$template_name] = $found;
            }
        }

        return $this->template_files[$template_name] = '';
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
     * @param $args mixed ...
     *
     * @return bool
     */
    public function check(string $name, mixed ...$args): bool
    {
        return $this->directives->check($name, ...$args);
    }

    /**
     * @param $view string
     *
     * @return void
     */
    public function compile(string $view): void
    {
        $this->compile_view($view, true);
    }

    /**
     * @return Compiler
     */
    public function compiler(): Compiler
    {
        return $this->compiler ??= new Compiler($this->directives, $this->echo_format);
    }

    private function include_view(string $view): void
    {
        $this->ensure_compiled($view);
        $compiled = $this->compiled_file($view);
        extract($this->variables);
        if (\function_exists('webkernel_profile_enter')) {
            \webkernel_profile_enter($compiled);
            try {
                include $compiled;
            } finally {
                \webkernel_profile_leave();
            }

            return;
        }
        include $compiled;
    }

    private function ensure_compiled(string $view): void
    {
        if ($this->mode === Mode::Fast) {
            return;
        }
        if ($this->mode === Mode::Slow || $this->expired($view)) {
            $this->compile_view($view, false);
        }
    }

    /**
     * @param $view string
     * @param $forced bool
     *
     * @return void
     */
    private function compile_view(string $view, bool $forced): void
    {
        if (! $forced && $this->mode !== Mode::Slow && ! $this->expired($view)) {
            return;
        }
        $template = $this->template_file($view);
        $compiled = $this->compiled_file($view);
        if ($template === '' || $compiled === '') {
            throw new \RuntimeException('Template not found: '.$view);
        }
        $compiler = $this->compiler();
        $module = null;
        if (str_contains($view, '::')) {
            $module = explode('::', $view, 2)[0];
            if ($module === 'webkernel') {
                $module = 'platform';
            }
        }
        $compiler->set_acl_module($module);
        $compiler->compile_file($template, $compiled);
        unset($this->compiled_files[$view], $this->template_files[$view]);
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
        if (isset($this->compiled_map[$template_name]) && $this->compiled_map[$template_name] !== '') {
            return $this->compiled_files[$template_name] = $this->compiled_map[$template_name];
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
