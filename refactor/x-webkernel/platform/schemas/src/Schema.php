<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Schemas;

use Webkernel\Platform\Components\Component;
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
     *
     * @return string
     */
    public function render(array $state = []): string
    {
        $html = '';
        foreach ($this->components as $component) {
            $props = $component->to_props();
            $name = $props['name'] ?? null;
            $extra = ['mode' => $this->mode->value];
            if (\is_string($name) && $name !== '' && ! \array_key_exists('value', $props)) {
                $extra['value'] = $state[$name] ?? '';
            }
            $html .= $component->render($extra);
        }

        return $html;
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
