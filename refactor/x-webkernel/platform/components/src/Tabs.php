<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Components;

use Closure;
use Webkernel\Platform\Schemas\Schema;
use Webkernel\Platform\Schemas\SchemaMode;

/**
 * Tab list plus panels. Same view for the tag and `Tabs::make()`.
 *
 * //> First argument of `make()` is the label, same as Filament `Tabs::make()`.
 */
final class Tabs extends Component
{
    /** @var list<Tab> */
    private array $child_tabs = [];

    /**
     * @param $label string
     *
     * @return static
     */
    public static function make(string $label = ''): static
    {
        $self = new static();
        $self->name = $label;
        $self->props['contained'] = true;
        $self->props['scrollable'] = true;
        $self->props['vertical'] = false;
        $self->props['active_tab'] = 1;
        $self->props['persist_tab'] = false;
        if ($label !== '') {
            $self->props['label'] = $label;
        }

        return $self;
    }

    /**
     * @return string
     */
    public function view(): string
    {
        return 'webkernel::tabs';
    }

    /**
     * @param $contained bool|Closure
     *
     * @return static
     */
    public function contained(bool|Closure $contained = true): static
    {
        $this->props['contained'] = $contained;

        return $this;
    }

    /**
     * @param $vertical bool|Closure
     *
     * @return static
     */
    public function vertical(bool|Closure $vertical = true): static
    {
        $this->props['vertical'] = $vertical;

        return $this;
    }

    /**
     * @param $scrollable bool|Closure
     *
     * @return static
     */
    public function scrollable(bool|Closure $scrollable = true): static
    {
        $this->props['scrollable'] = $scrollable;

        return $this;
    }

    /**
     * @param $active_tab int|Closure
     *
     * @return static
     */
    public function active_tab(int|Closure $active_tab): static
    {
        $this->props['active_tab'] = $active_tab;

        return $this;
    }

    /**
     * @param $condition bool|Closure
     *
     * @return static
     */
    public function persist_tab(bool|Closure $condition = true): static
    {
        $this->props['persist_tab'] = $condition;

        return $this;
    }

    /**
     * @param $key string|Closure|null
     *
     * @return static
     */
    public function persist_tab_in_query_string(string|Closure|null $key = 'tab'): static
    {
        $this->props['persist_query'] = $key;

        return $this;
    }

    /**
     * @param $id string
     *
     * @return static
     */
    public function id(string $id): static
    {
        $this->props['id'] = $id;

        return $this;
    }

    /**
     * @param $key string
     *
     * @return static
     */
    public function key(string $key): static
    {
        $this->props['key'] = $key;

        return $this;
    }

    /**
     * @param $html string
     *
     * @return static
     */
    public function list(string $html): static
    {
        $this->props['list'] = $html;

        return $this;
    }

    /**
     * @param $tabs list<Tab>
     *
     * @return static
     */
    public function tabs(array $tabs): static
    {
        $out = [];
        foreach ($tabs as $tab) {
            if (! $tab instanceof Tab) {
                throw new \InvalidArgumentException('Tabs::tabs() expects Tab instances.');
            }
            $out[] = $tab;
        }
        $this->child_tabs = $out;

        return $this;
    }

    /**
     * @return list<\Webkernel\Platform\Schemas\Schema>
     */
    public function nested_schemas(): array
    {
        $out = [];
        foreach ($this->child_tabs as $tab) {
            $out[] = $tab->child_schema();
        }

        return $out;
    }

    /**
     * @param $extra array<string, mixed>
     *
     * @return string
     */
    public function render(array $extra = []): string
    {
        $extra['contained'] = $this->bool_prop('contained', true);
        $extra['vertical'] = $this->bool_prop('vertical', false);
        $extra['scrollable'] = $this->bool_prop('scrollable', true);
        $extra['persist_tab'] = $this->bool_prop('persist_tab', false);
        $extra['persist_query'] = $this->string_prop('persist_query');
        $extra['id'] = $this->html_id();
        $extra['key'] = $this->string_prop('key');
        if ($this->child_tabs !== []) {
            /** @var array<string, mixed> $state */
            $state = \is_array($extra['state'] ?? null) ? $extra['state'] : $extra;
            /** @var array<string, string> $errors */
            $errors = \is_array($extra['errors'] ?? null) ? $extra['errors'] : [];
            $mode = isset($extra['mode']) && \is_string($extra['mode'])
                ? SchemaMode::tryFrom($extra['mode'])
                : null;
            $active = $this->active_index();
            $list = '';
            $panels = '';
            $keys = [];
            foreach ($this->child_tabs as $i => $tab) {
                $is_active = ($i + 1) === $active;
                $id = $tab->tab_id();
                $keys[] = $id;
                $list .= TabsItem::make()
                    ->tab($id)
                    ->active($is_active)
                    ->icon($tab->get_icon())
                    ->icon_position($tab->get_icon_position())
                    ->badge($tab->get_badge())
                    ->badge_color($tab->get_badge_color())
                    ->defer_badge($tab->is_badge_deferred())
                    ->slot($tab->tab_label())
                    ->render();
                $schema = $tab->child_schema();
                if ($mode instanceof SchemaMode) {
                    $schema->mode($mode);
                }
                $panel = TabsPanel::make()
                    ->tab($id)
                    ->active($is_active)
                    ->slot($schema->render_tree($state, $errors));
                $cols = $tab->get_columns();
                if ($cols !== null) {
                    $panel->columns($cols);
                }
                $panels .= $panel->render();
            }
            $extra['list'] = $list;
            $extra['slot'] = $panels;
            $extra['tab_keys'] = $keys;
        }

        return parent::render($extra);
    }

    /**
     * @return array{component: class-string, view: string, props: array<string, mixed>, tabs?: list<array<string, mixed>>}
     */
    public function to_array(): array
    {
        $dump = parent::to_array();
        $dump['props']['contained'] = $this->bool_prop('contained', true);
        $dump['props']['vertical'] = $this->bool_prop('vertical', false);
        $dump['props']['scrollable'] = $this->bool_prop('scrollable', true);
        $dump['props']['persist_tab'] = $this->bool_prop('persist_tab', false);
        $dump['props']['persist_query'] = $this->string_prop('persist_query');
        $dump['props']['active_tab'] = $this->active_index();
        $tabs = [];
        foreach ($this->child_tabs as $tab) {
            $tabs[] = $tab->to_array();
        }
        $dump['tabs'] = $tabs;

        return $dump;
    }

    /**
     * @return int
     */
    private function active_index(): int
    {
        $query_key = $this->string_prop('persist_query');
        if ($query_key !== '' && $this->child_tabs !== []) {
            $query = $_GET[$query_key] ?? null;
            if (\is_string($query) && $query !== '') {
                foreach ($this->child_tabs as $i => $tab) {
                    if ($tab->tab_id() === $query) {
                        return $i + 1;
                    }
                }
            }
        }
        $active = $this->props['active_tab'] ?? 1;
        if ($active instanceof Closure) {
            $active = $active();
        }

        return \is_int($active) && $active > 0 ? $active : 1;
    }

    /**
     * @return string
     */
    private function html_id(): string
    {
        $id = $this->string_prop('id');
        if ($id !== '') {
            return $id;
        }
        $label = (string) ($this->props['label'] ?? $this->name);

        return $label !== '' ? \strtolower(\preg_replace('/[^a-zA-Z0-9]+/', '-', $label) ?? $label) : '';
    }

    /**
     * @param $key string
     * @param $default bool
     *
     * @return bool
     */
    private function bool_prop(string $key, bool $default): bool
    {
        $value = $this->props[$key] ?? $default;
        if ($value instanceof Closure) {
            $value = $value();
        }

        return (bool) $value;
    }

    /**
     * @param $key string
     *
     * @return string
     */
    private function string_prop(string $key): string
    {
        $value = $this->props[$key] ?? '';
        if ($value instanceof Closure) {
            $value = $value();
        }

        return \is_string($value) ? $value : '';
    }
}
