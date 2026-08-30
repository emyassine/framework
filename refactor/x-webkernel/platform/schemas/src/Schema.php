<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Schemas;

use Webkernel\Platform\Components\Component;
use Webkernel\Platform\Components\Tabs;
use Webkernel\Platform\Components\TextInput;

/**
 * Component tree. Editable form and readonly view are the same schema, different mode.
 */
final class Schema
{
    /** @var list<Component> */
    private array $components = [];

    private SchemaMode $mode = SchemaMode::Editable;

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
            $props = $component->to_props();
            $name = (string) ($props['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $out[] = [
                'name' => $name,
                'label' => (string) ($props['label'] ?? $name),
                'type' => (string) ($props['type'] ?? 'text'),
            ];
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
        $html = '';
        foreach ($this->components as $component) {
            $html .= $this->render_component($component, $state, $errors);
        }

        return $html;
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
        $extra = ['mode' => $this->mode->value];
        if ($component instanceof Tabs) {
            return $component->render(\array_merge($extra, ['state' => $state, 'errors' => $errors]));
        }
        if (\is_string($name) && $name !== '') {
            if (! \array_key_exists('value', $props) && \array_key_exists($name, $state)) {
                $value = $state[$name];
                $extra['value'] = \is_scalar($value) ? (string) $value : '';
            }
            if (isset($state[$name]) && \is_bool($state[$name]) && ! \array_key_exists('checked', $props)) {
                $extra['checked'] = $state[$name];
            }
            if (isset($errors[$name])) {
                $extra['error'] = $errors[$name];
            }
        }

        return $component->render($extra);
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
}
