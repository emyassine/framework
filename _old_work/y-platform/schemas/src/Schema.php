<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Schemas;

use Webkernel\Platform\Actions\Action;
use Webkernel\Platform\Actions\Actions;
use Webkernel\Platform\Schemas\Enums\SchemaMode;
use Webkernel\Component\Component;
use Webkernel\Platform\Schemas\Grid;
use Webkernel\Platform\Components\TextInput;
use Webkernel\View\Htmlable;
use Webkernel\View\View;

/**
 * Component tree. Editable form and readonly view are the same schema, different mode.
 *
 * //> The page View only echoes the schema. Form, CSRF, and actions live here.
 */
final class Schema implements Htmlable, \Stringable
{
    /** @var list<Component> */
    private array $components = [];

    private SchemaMode $mode = SchemaMode::Editable;

    private bool $as_form = false;

    private bool $autosave = false;

    private string $form_action = '';

    /** @var array<string, string> */
    private array $hidden = [];

    /** @var array<string, mixed> */
    private array $state = [];

    /** @var array<string, string> */
    private array $errors = [];

    /** @var int|array<string, int>|null */
    private int|array|null $columns = null;

    /** @var list<Action> */
    private array $header_actions = [];

    /** @var list<Action> */
    private array $footer_actions = [];

    /**
     * @return self
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * @param $components list<Component>
     *
     * @return self
     */
    public function components(array $components): self
    {
        $out = [];
        foreach ($components as $component) {
            if (! $component instanceof Component) {
                throw new \InvalidArgumentException('Schema components must be Component instances.');
            }
            $out[] = $component;
        }
        $this->components = $out;

        return $this;
    }

    /**
     * @param $mode SchemaMode
     *
     * @return self
     */
    public function mode(SchemaMode $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @param $action string
     *
     * @return self
     */
    public function form(string $action = ''): self
    {
        $this->as_form = true;
        $this->form_action = $action;

        return $this;
    }

    /**
     * @param $condition bool
     *
     * @return self
     */
    public function autosave(bool $condition = true): self
    {
        $this->autosave = $condition;

        return $this;
    }

    /**
     * @param $fields array<string, string>
     *
     * @return self
     */
    public function hidden(array $fields): self
    {
        $this->hidden = $fields;

        return $this;
    }

    /**
     * @param $state array<string, mixed>
     *
     * @return self
     */
    public function state(array $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @param $errors array<string, string>
     *
     * @return self
     */
    public function errors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @param $columns int|array<string, int>
     *
     * @return self
     */
    public function columns(int|array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @param $actions list<Action>
     *
     * @return self
     */
    public function header_actions(array $actions): self
    {
        $this->header_actions = $this->action_list($actions);

        return $this;
    }

    /**
     * @param $actions list<Action>
     *
     * @return self
     */
    public function footer_actions(array $actions): self
    {
        $this->footer_actions = $this->action_list($actions);

        return $this;
    }

    /**
     * @param list<array{name: string, label?: string, type?: string}|string> $fields
     *
     * @return self
     */
    public function fields(array $fields): self
    {
        $components = [];
        foreach ($fields as $field) {
            if (\is_string($field)) {
                $components[] = TextInput::make($field)->label($field);

                continue;
            }
            if (! isset($field['name'])) {
                continue;
            }
            $input = TextInput::make((string) $field['name'])
                ->label((string) ($field['label'] ?? $field['name']));
            if (isset($field['type'])) {
                $input->type((string) $field['type']);
            }
            $components[] = $input;
        }

        return $this->components($components);
    }

    /**
     * @return list<Component>
     */
    public function get_components(): array
    {
        return $this->components;
    }

    /**
     * @return list<array{name: string, label: string, type: string}>
     */
    public function get_fields(): array
    {
        $out = [];
        foreach ($this->components as $component) {
            foreach ($this->collect_fields($component) as $field) {
                $out[] = $field;
            }
        }

        return $out;
    }

    /**
     * @return SchemaMode
     */
    public function get_mode(): SchemaMode
    {
        return $this->mode;
    }

    /**
     * @param $state array<string, mixed>
     * @param $errors array<string, string>
     *
     * @return string
     */
    public function render(array $state = [], array $errors = []): string
    {
        if ($state === []) {
            $state = $this->state;
        }
        if ($errors === []) {
            $errors = $this->errors;
        }
        $html = $this->render_tree($state, $errors);
        if ($this->columns !== null) {
            $html = Grid::make()->columns($this->columns)->render(['slot' => $html]);
        }
        if (! $this->as_form || $this->mode === SchemaMode::Readonly) {
            return $html;
        }
        $action = $this->form_action !== ''
            ? $this->form_action
            : (string) (\parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), \PHP_URL_PATH) ?: '');

        return View::make('webkernel::schema', [
            'action' => $action,
            'autosave' => $this->autosave,
            'hidden' => $this->hidden,
            'header' => $this->render_actions($this->header_actions),
            'footer' => $this->render_actions($this->footer_actions),
            'slot' => $html,
            'grid_class' => '',
            'grid_style' => '',
        ])->html();
    }

    /**
     * Nested HTML only. Layouts call this so they do not wrap another form.
     *
     * @param $state array<string, mixed>
     * @param $errors array<string, string>
     *
     * @return string
     */
    public function render_tree(array $state = [], array $errors = []): string
    {
        $html = '';
        foreach ($this->components as $component) {
            $html .= $this->render_component($component, $state, $errors);
        }

        return $html;
    }

    /**
     * @return string
     */
    public function to_html(): string
    {
        return $this->render();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->to_html();
    }

    /**
     * @return array{mode: string, components: list<array{component: class-string, view: string, props: array<string, mixed>}>}
     */
    public function to_array(): array
    {
        $components = [];
        foreach ($this->components as $component) {
            $components[] = $component->to_array();
        }

        return [
            'mode' => $this->mode->value,
            'components' => $components,
        ];
    }

    /**
     * @param $component Component
     * @param $state array<string, mixed>
     * @param $errors array<string, string>
     *
     * @return string
     */
    private function render_component(Component $component, array $state, array $errors): string
    {
        $props = $component->to_props();
        $name = $props['name'] ?? null;
        $extra = [
            'mode' => $this->mode->value,
            'state' => $state,
            'errors' => $errors,
        ];
        if (\is_string($name) && $name !== '' && \array_key_exists($name, $state)) {
            $value = $state[$name];
            if (\is_bool($value)) {
                if (! \array_key_exists('checked', $props)) {
                    $extra['checked'] = $value;
                }
            } elseif (! \array_key_exists('value', $props)) {
                $extra['value'] = \is_scalar($value) ? (string) $value : '';
            }
            if (isset($errors[$name])) {
                $extra['error'] = $errors[$name];
            }
        } elseif (\is_string($name) && $name !== '' && isset($errors[$name])) {
            $extra['error'] = $errors[$name];
        }

        return $component->render($extra);
    }

    /**
     * @param $component Component
     *
     * @return list<array{name: string, label: string, type: string}>
     */
    private function collect_fields(Component $component): array
    {
        $out = [];
        if ($component instanceof Action || $component instanceof Actions) {
            return $out;
        }
        if (\method_exists($component, 'nested_schemas')) {
            $nested = $component->nested_schemas();
            if (\is_array($nested)) {
                foreach ($nested as $schema) {
                    if ($schema instanceof self) {
                        foreach ($schema->get_fields() as $field) {
                            $out[] = $field;
                        }
                    }
                }
            }
        }
        $props = $component->to_props();
        $name = $props['name'] ?? '';
        if (\is_string($name) && $name !== '' && $out === []) {
            $out[] = [
                'name' => $name,
                'label' => (string) ($props['label'] ?? $name),
                'type' => (string) ($props['type'] ?? 'text'),
            ];
        }

        return $out;
    }

    /**
     * @param $actions list<Action>
     *
     * @return list<Action>
     */
    private function action_list(array $actions): array
    {
        $out = [];
        foreach ($actions as $action) {
            if (! $action instanceof Action) {
                throw new \InvalidArgumentException('Schema actions must be Action instances.');
            }
            $out[] = $action;
        }

        return $out;
    }

    /**
     * @param $actions list<Action>
     *
     * @return string
     */
    private function render_actions(array $actions): string
    {
        $html = '';
        foreach ($actions as $action) {
            $html .= $action->render();
        }

        return $html;
    }
}
